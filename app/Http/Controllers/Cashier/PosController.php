<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SalesService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    public function __construct(
        protected SalesService $salesService
    ) {}

    public function index()
    {
        $categories = Category::withCount(['products' => function ($q) {
            $q->where('is_active', true);
        }])->get();

        $quickProducts = Product::where('is_active', true)
            ->where('stock', '>', 0)
            ->latest()
            ->limit(12)
            ->get();

        $recentSales = Sale::where('user_id', Auth::id())
            ->whereDate('sold_at', today())
            ->latest('sold_at')
            ->limit(10)
            ->with(['payment', 'items.product'])
            ->get();

        $todayStats = [
            'count' => Sale::where('user_id', Auth::id())->whereDate('sold_at', today())->count(),
            'total' => Sale::where('user_id', Auth::id())->whereDate('sold_at', today())->sum('total'),
        ];

        return view('cashier.pos', compact('categories', 'quickProducts', 'recentSales', 'todayStats'));
    }

    /**
     * Search product by barcode, SKU, or name
     */
    public function searchProduct(Request $request): JsonResponse
    {
        $query = trim($request->input('q', ''));
        $categoryId = $request->input('category_id');

        if (empty($query) && !$categoryId) {
            $products = Product::where('is_active', true)
                ->where('stock', '>', 0)
                ->limit(20)
                ->get();

            return response()->json([
                'exact' => false,
                'products' => $products,
            ]);
        }

        // 1. Check exact barcode match first
        if (!empty($query)) {
            $exactBarcode = Product::where('barcode', $query)
                ->where('is_active', true)
                ->first();

            if ($exactBarcode) {
                return response()->json([
                    'exact' => true,
                    'product' => $exactBarcode,
                ]);
            }

            // Check exact SKU match
            $exactSku = Product::where('sku', $query)
                ->where('is_active', true)
                ->first();

            if ($exactSku) {
                return response()->json([
                    'exact' => true,
                    'product' => $exactSku,
                ]);
            }
        }

        // 2. Fallback to broad search
        $productsQuery = Product::where('is_active', true);

        if (!empty($query)) {
            $productsQuery->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('barcode', 'LIKE', "%{$query}%")
                    ->orWhere('sku', 'LIKE', "%{$query}%");
            });
        }

        if ($categoryId) {
            $productsQuery->where('category_id', $categoryId);
        }

        $products = $productsQuery->limit(24)->get();

        return response()->json([
            'exact' => false,
            'products' => $products,
        ]);
    }

    /**
     * Process checkout transaction
     */
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'method' => 'required|string|in:cash,qris,card,transfer',
            'amount_received' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $sale = $this->salesService->createSale(
                userId: Auth::id(),
                items: $validated['items'],
                paymentData: [
                    'method' => $validated['method'],
                    'amount_received' => $validated['amount_received'] ?? null,
                    'discount' => $validated['discount'] ?? 0,
                    'notes' => $validated['notes'] ?? null,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diselesaikan!',
                'sale' => $sale,
                'receipt_url' => route('cashier.receipt', $sale->invoice_number),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Show thermal receipt printable view
     */
    public function receipt(string $invoiceNumber)
    {
        $sale = Sale::with(['user', 'items.product', 'payment'])
            ->where('invoice_number', $invoiceNumber)
            ->firstOrFail();

        return view('cashier.receipt', compact('sale'));
    }

    /**
     * Get recent sales for reprint modal
     */
    public function recentSales(): JsonResponse
    {
        $sales = Sale::where('user_id', Auth::id())
            ->whereDate('sold_at', today())
            ->latest('sold_at')
            ->limit(15)
            ->with(['payment', 'items.product'])
            ->get();

        return response()->json($sales);
    }
}
