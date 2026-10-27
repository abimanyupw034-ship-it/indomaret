<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Exception;
use Illuminate\Support\Facades\DB;

class SalesService
{
    public function __construct(
        protected StockService $stockService
    ) {}

    /**
     * Complete POS sale with atomic stock reservation and snapshot pricing.
     */
    public function createSale(int $userId, array $items, array $paymentData): Sale
    {
        if (empty($items)) {
            throw new Exception("Keranjang belanja kosong. Silakan tambahkan barang terlebih dahulu.");
        }

        return DB::transaction(function () use ($userId, $items, $paymentData) {
            $today = now()->format('Ymd');
            $countToday = Sale::whereDate('created_at', today())->count() + 1;
            $invoiceNumber = 'INV/' . $today . '/' . str_pad((string) $countToday, 4, '0', STR_PAD_LEFT);

            $subtotal = 0;
            $validatedItems = [];

            // 1. Lock and validate each product
            foreach ($items as $item) {
                $productId = (int) $item['product_id'];
                $qty = (float) $item['qty'];

                if ($qty <= 0) {
                    throw new Exception("Kuantitas produk harus lebih besar dari 0.");
                }

                $product = Product::where('id', $productId)->lockForUpdate()->first();
                if (!$product) {
                    throw new Exception("Produk dengan ID {$productId} tidak ditemukan.");
                }

                if (!$product->is_active) {
                    throw new Exception("Produk {$product->name} saat ini nonaktif.");
                }

                if ((float) $product->stock < $qty) {
                    throw new Exception("Stok tidak mencukupi untuk {$product->name}. Stok tersedia: {$product->stock}, diminta: {$qty}.");
                }

                $unitPrice = (float) $product->selling_price;
                $unitCost = (float) $product->purchase_price;
                $lineSubtotal = $unitPrice * $qty;

                $subtotal += $lineSubtotal;

                $validatedItems[] = [
                    'product' => $product,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'unit_cost' => $unitCost,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $discount = isset($paymentData['discount']) ? (float) $paymentData['discount'] : 0;
            $total = max(0, $subtotal - $discount);

            // 2. Validate payment
            $method = $paymentData['method'] ?? 'cash';
            $amountReceived = isset($paymentData['amount_received']) ? (float) $paymentData['amount_received'] : $total;

            if ($method === 'cash' && $amountReceived < $total) {
                $shortage = $total - $amountReceived;
                throw new Exception("Pembayaran tunai kurang sebesar Rp " . number_format($shortage, 0, ',', '.'));
            }

            $changeAmount = ($method === 'cash') ? max(0, $amountReceived - $total) : 0;

            // 3. Create Sale record
            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'user_id' => $userId,
                'sold_at' => now(),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'status' => 'completed',
                'notes' => $paymentData['notes'] ?? null,
            ]);

            // 4. Create Sale Items and deduct stock atomically
            foreach ($validatedItems as $valItem) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $valItem['product']->id,
                    'qty' => $valItem['qty'],
                    'unit_price' => $valItem['unit_price'],
                    'unit_cost' => $valItem['unit_cost'],
                    'subtotal' => $valItem['subtotal'],
                ]);

                // Atomic deduction with audit ledger
                $this->stockService->recordMovement(
                    productId: $valItem['product']->id,
                    userId: $userId,
                    movementType: 'SALE',
                    qtyChange: -$valItem['qty'],
                    referenceType: 'sale',
                    referenceId: $sale->id,
                    notes: "Penjualan #{$invoiceNumber}"
                );
            }

            // 5. Create Payment record
            Payment::create([
                'sale_id' => $sale->id,
                'method' => $method,
                'amount' => $total,
                'amount_received' => $amountReceived,
                'change_amount' => $changeAmount,
                'reference' => $paymentData['reference'] ?? null,
            ]);

            return $sale->load(['items.product', 'payments', 'user']);
        });
    }
}
