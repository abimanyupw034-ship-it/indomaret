<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Product;
use App\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Record stock movement atomically with lockForUpdate to prevent negative stock & race conditions.
     */
    public function recordMovement(
        int $productId,
        int $userId,
        string $movementType,
        float $qtyChange,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): StockMovement {
        // Enforce lockForUpdate inside transaction
        $product = Product::where('id', $productId)->lockForUpdate()->firstOrFail();

        $stockBefore = (float) $product->stock;
        $stockAfter = $stockBefore + $qtyChange;

        if ($qtyChange < 0 && $stockAfter < 0) {
            throw new Exception("Stok tidak mencukupi untuk {$product->name} (SKU: {$product->sku}). Stok tersisa: {$stockBefore}, diminta: " . abs($qtyChange));
        }

        $product->stock = $stockAfter;
        $product->save();

        return StockMovement::create([
            'product_id' => $productId,
            'user_id' => $userId,
            'movement_type' => $movementType,
            'qty_change' => $qtyChange,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
        ]);
    }

    /**
     * Process incoming goods receipt from suppliers
     */
    public function receiveGoods(int $userId, array $headerData, array $items): GoodsReceipt
    {
        return DB::transaction(function () use ($userId, $headerData, $items) {
            $today = now()->format('Ymd');
            $countToday = GoodsReceipt::whereDate('created_at', today())->count() + 1;
            $receiptNumber = 'GR/' . $today . '/' . str_pad((string) $countToday, 4, '0', STR_PAD_LEFT);

            $goodsReceipt = GoodsReceipt::create([
                'user_id' => $userId,
                'receipt_number' => $receiptNumber,
                'received_at' => $headerData['received_at'] ?? now(),
                'supplier_name' => $headerData['supplier_name'] ?? 'Supplier Umum',
                'status' => 'completed',
                'notes' => $headerData['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $qty = (float) $item['qty'];
                $unitCost = (float) $item['unit_cost'];
                $subtotal = $qty * $unitCost;

                GoodsReceiptItem::create([
                    'goods_receipt_id' => $goodsReceipt->id,
                    'product_id' => $item['product_id'],
                    'qty' => $qty,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ]);

                // Record atomic stock increase
                $this->recordMovement(
                    productId: (int) $item['product_id'],
                    userId: $userId,
                    movementType: 'PURCHASE_IN',
                    qtyChange: $qty,
                    referenceType: 'goods_receipt',
                    referenceId: $goodsReceipt->id,
                    notes: "Penerimaan Barang #{$receiptNumber} dari " . ($headerData['supplier_name'] ?? 'Supplier')
                );

                // Update product purchase cost if provided
                if ($unitCost > 0) {
                    Product::where('id', $item['product_id'])->update([
                        'purchase_price' => $unitCost,
                    ]);
                }
            }

            return $goodsReceipt->load('items.product');
        });
    }

    /**
     * Process stock adjustment/correction
     */
    public function adjustStock(
        int $userId,
        int $productId,
        float $physicalStock,
        string $reason,
        string $notes
    ): StockMovement {
        return DB::transaction(function () use ($userId, $productId, $physicalStock, $reason, $notes) {
            $product = Product::where('id', $productId)->lockForUpdate()->firstOrFail();
            $systemStock = (float) $product->stock;
            $difference = $physicalStock - $systemStock;

            if ($difference == 0) {
                throw new Exception("Stok fisik sama dengan stok sistem ({$systemStock}). Tidak ada penyesuaian yang perlu disimpan.");
            }

            $movementType = $difference > 0 ? 'ADJUSTMENT_IN' : 'ADJUSTMENT_OUT';
            $fullNotes = "[{$reason}] {$notes} (Fisik: {$physicalStock}, Sistem: {$systemStock}, Selisih: {$difference})";

            return $this->recordMovement(
                productId: $productId,
                userId: $userId,
                movementType: $movementType,
                qtyChange: $difference,
                referenceType: 'adjustment',
                referenceId: null,
                notes: $fullNotes
            );
        });
    }
}
