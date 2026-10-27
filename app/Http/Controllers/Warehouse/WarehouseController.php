<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WarehouseController extends Controller
{
    public function __construct(
        protected StockService $stockService
    ) {}

    public function dashboard()
    {
        $totalSku = Product::count();
        $activeSku = Product::where('is_active', true)->count();
        $lowStockCount = Product::where('is_active', true)
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->where('stock', '>', 0)
            ->count();
        $outOfStockCount = Product::where('is_active', true)
            ->where('stock', '<=', 0)
            ->count();
        $totalValuation = (float) Product::where('is_active', true)
            ->where('stock', '>', 0)
            ->sum(DB::raw('stock * purchase_price'));

        $recentMovements = StockMovement::with(['product.category', 'user'])
            ->latest()
            ->limit(10)
            ->get();

        $recentReceipts = GoodsReceipt::with(['user', 'items.product'])
            ->latest('received_at')
            ->limit(5)
            ->get();

        return view('warehouse.dashboard', compact(
            'totalSku',
            'activeSku',
            'lowStockCount',
            'outOfStockCount',
            'totalValuation',
            'recentMovements',
            'recentReceipts'
        ));
    }

    public function products(Request $request)
    {
        $categories = Category::all();
        $query = Product::with('category');

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%")
                    ->orWhere('barcode', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            switch ($request->status) {
                case 'active':
                    $query->where('is_active', true);
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
                case 'low':
                    $query->where('is_active', true)
                        ->whereColumn('stock', '<=', 'minimum_stock')
                        ->where('stock', '>', 0);
                    break;
                case 'out':
                    $query->where('is_active', true)
                        ->where('stock', '<=', 0);
                    break;
            }
        }

        $products = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('warehouse.products.index', compact('products', 'categories'));
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sku' => 'required|string|max:50|unique:products,sku',
            'barcode' => 'required|string|max:50|unique:products,barcode',
            'name' => 'required|string|max:255',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0|gte:purchase_price',
            'minimum_stock' => 'required|numeric|min:0',
            'unit' => 'required|string|max:20',
            'initial_stock' => 'nullable|numeric|min:0',
        ], [
            'selling_price.gte' => 'Harga jual harus lebih besar atau sama dengan harga beli.',
            'sku.unique' => 'SKU produk sudah terdaftar.',
            'barcode.unique' => 'Barcode produk sudah terdaftar.',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $initialStock = isset($validated['initial_stock']) ? (float) $validated['initial_stock'] : 0;

                $product = Product::create([
                    'category_id' => $validated['category_id'],
                    'sku' => strtoupper(trim($validated['sku'])),
                    'barcode' => trim($validated['barcode']),
                    'name' => trim($validated['name']),
                    'purchase_price' => $validated['purchase_price'],
                    'selling_price' => $validated['selling_price'],
                    'stock' => 0, // start at 0, updated through ledger
                    'minimum_stock' => $validated['minimum_stock'],
                    'unit' => trim($validated['unit']),
                    'is_active' => true,
                ]);

                if ($initialStock > 0) {
                    $this->stockService->recordMovement(
                        productId: $product->id,
                        userId: Auth::id(),
                        movementType: 'PURCHASE_IN',
                        qtyChange: $initialStock,
                        referenceType: 'adjustment',
                        referenceId: null,
                        notes: 'Saldo stok awal saat produk baru didaftarkan'
                    );
                }
            });

            return redirect()->route('warehouse.products.index')
                ->with('success', 'Produk baru berhasil ditambahkan ke inventaris.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal menambahkan produk: ' . $e->getMessage());
        }
    }

    public function updateProduct(Request $request, int $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sku' => ['required', 'string', 'max:50', Rule::unique('products')->ignore($product->id)],
            'barcode' => ['required', 'string', 'max:50', Rule::unique('products')->ignore($product->id)],
            'name' => 'required|string|max:255',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0|gte:purchase_price',
            'minimum_stock' => 'required|numeric|min:0',
            'unit' => 'required|string|max:20',
        ]);

        $product->update([
            'category_id' => $validated['category_id'],
            'sku' => strtoupper(trim($validated['sku'])),
            'barcode' => trim($validated['barcode']),
            'name' => trim($validated['name']),
            'purchase_price' => $validated['purchase_price'],
            'selling_price' => $validated['selling_price'],
            'minimum_stock' => $validated['minimum_stock'],
            'unit' => trim($validated['unit']),
        ]);

        return redirect()->route('warehouse.products.index')
            ->with('success', "Informasi produk {$product->name} berhasil diperbarui.");
    }

    public function toggleProductStatus(int $id)
    {
        $product = Product::findOrFail($id);
        $product->is_active = !$product->is_active;
        $product->save();

        $statusText = $product->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';

        return redirect()->route('warehouse.products.index')
            ->with('success', "Produk {$product->name} berhasil {$statusText}.");
    }

    public function goodsReceiptForm()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('warehouse.receipts.create', compact('products'));
    }

    public function storeGoodsReceipt(Request $request)
    {
        $validated = $request->validate([
            'supplier_name' => 'nullable|string|max:255',
            'received_at' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        try {
            $receipt = $this->stockService->receiveGoods(
                userId: Auth::id(),
                headerData: [
                    'supplier_name' => $validated['supplier_name'] ?? 'Supplier Umum',
                    'received_at' => $validated['received_at'],
                    'notes' => $validated['notes'] ?? null,
                ],
                items: $validated['items']
            );

            return redirect()->route('warehouse.movements')
                ->with('success', "Penerimaan barang #{$receipt->receipt_number} berhasil dicatat dan stok telah bertambah.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memproses penerimaan: ' . $e->getMessage());
        }
    }

    public function adjustmentForm()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('warehouse.adjustments.create', compact('products'));
    }

    public function storeAdjustment(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'physical_stock' => 'required|numeric|min:0',
            'reason' => 'required|string|in:Salah Input,Barang Rusak / Kadaluarsa,Selisih Inventaris Opname,Lainnya',
            'notes' => 'required|string|min:5|max:500',
        ], [
            'notes.required' => 'Catatan alasan penyesuaian stok wajib diisi.',
            'notes.min' => 'Catatan koreksi harus berisi minimal 5 karakter.',
        ]);

        try {
            $movement = $this->stockService->adjustStock(
                userId: Auth::id(),
                productId: (int) $validated['product_id'],
                physicalStock: (float) $validated['physical_stock'],
                reason: $validated['reason'],
                notes: $validated['notes']
            );

            return redirect()->route('warehouse.movements')
                ->with('success', "Koreksi stok untuk {$movement->product->name} berhasil disimpan. Saldo baru: {$movement->stock_after}");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal melakukan koreksi: ' . $e->getMessage());
        }
    }

    public function movements(Request $request)
    {
        $query = StockMovement::with(['product.category', 'user']);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('type')) {
            $query->where('movement_type', $request->type);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        $movements = $query->latest()->paginate(20)->withQueryString();
        $products = Product::orderBy('name')->get();

        return view('warehouse.movements.index', compact('movements', 'products'));
    }
}
