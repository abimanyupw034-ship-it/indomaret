<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get aggregate KPI and chart statistics for the Admin dashboard
     */
    public function getDashboardSummary(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->subDays(30)->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfDay();

        $salesQuery = Sale::where('status', 'completed')
            ->whereBetween('sold_at', [$start, $end]);

        $totalSales = (float) $salesQuery->sum('total');
        $transactionCount = $salesQuery->count();
        $averageBasket = $transactionCount > 0 ? $totalSales / $transactionCount : 0;

        // Calculate COGS and Gross Profit using historical snapshot in sale_items
        $cogsQuery = SaleItem::whereHas('sale', function ($q) use ($start, $end) {
            $q->where('status', 'completed')
                ->whereBetween('sold_at', [$start, $end]);
        });

        $totalCost = (float) $cogsQuery->sum(DB::raw('unit_cost * qty'));
        $grossProfit = $totalSales - $totalCost;
        $profitMargin = $totalSales > 0 ? ($grossProfit / $totalSales) * 100 : 0;

        // Top 5 selling products
        $topProducts = SaleItem::select('product_id', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(subtotal) as total_amount'))
            ->whereHas('sale', function ($q) use ($start, $end) {
                $q->where('status', 'completed')
                    ->whereBetween('sold_at', [$start, $end]);
            })
            ->with('product.category')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // Low stock count
        $lowStockCount = Product::where('is_active', true)
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->count();

        // Daily trend for the chart (last 7 or 14 days)
        $dailyTrends = Sale::select(
                DB::raw('DATE(sold_at) as sale_date'),
                DB::raw('SUM(total) as daily_revenue'),
                DB::raw('COUNT(id) as daily_transactions')
            )
            ->where('status', 'completed')
            ->whereBetween('sold_at', [now()->subDays(14)->startOfDay(), now()->endOfDay()])
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get();

        // Category sales share
        $categorySales = SaleItem::select('categories.name as category_name', DB::raw('SUM(sale_items.subtotal) as total_revenue'))
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereHas('sale', function ($q) use ($start, $end) {
                $q->where('status', 'completed')
                    ->whereBetween('sold_at', [$start, $end]);
            })
            ->groupBy('categories.name')
            ->get();

        return [
            'total_sales' => $totalSales,
            'transaction_count' => $transactionCount,
            'average_basket' => $averageBasket,
            'total_cogs' => $totalCost,
            'gross_profit' => $grossProfit,
            'profit_margin' => $profitMargin,
            'low_stock_count' => $lowStockCount,
            'top_products' => $topProducts,
            'daily_trends' => $dailyTrends,
            'category_sales' => $categorySales,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
        ];
    }

    /**
     * Get Sales Report with filters
     */
    public function getSalesReport(?string $startDate = null, ?string $endDate = null, ?string $paymentMethod = null)
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfDay();

        $query = Sale::with(['user', 'payment', 'items.product'])
            ->where('status', 'completed')
            ->whereBetween('sold_at', [$start, $end]);

        if ($paymentMethod) {
            $query->whereHas('payments', function ($q) use ($paymentMethod) {
                $q->where('method', $paymentMethod);
            });
        }

        return $query->latest('sold_at')->paginate(15);
    }

    /**
     * Get Profit & Loss Report with product-level profitability
     */
    public function getProfitReport(?string $startDate = null, ?string $endDate = null)
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfDay();

        return SaleItem::select(
                'product_id',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('SUM(subtotal) as total_revenue'),
                DB::raw('SUM(unit_cost * qty) as total_cogs'),
                DB::raw('SUM(subtotal - (unit_cost * qty)) as total_profit')
            )
            ->whereHas('sale', function ($q) use ($start, $end) {
                $q->where('status', 'completed')
                    ->whereBetween('sold_at', [$start, $end]);
            })
            ->with('product.category')
            ->groupBy('product_id')
            ->orderByDesc('total_profit')
            ->paginate(15);
    }

    /**
     * Get Inventory & Stock Valuation Report
     */
    public function getInventoryReport(?int $categoryId = null, ?string $status = null)
    {
        $query = Product::with('category');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($status === 'low') {
            $query->whereColumn('stock', '<=', 'minimum_stock')->where('stock', '>', 0);
        } elseif ($status === 'out') {
            $query->where('stock', '<=', 0);
        } elseif ($status === 'active') {
            $query->where('is_active', true);
        }

        $products = $query->orderBy('name')->paginate(20);

        // Calculate total inventory valuation
        $totalValuation = Product::where('is_active', true)
            ->where('stock', '>', 0)
            ->sum(DB::raw('stock * purchase_price'));

        $totalItemsCount = Product::where('is_active', true)->sum('stock');

        return [
            'products' => $products,
            'total_valuation' => (float) $totalValuation,
            'total_stock_count' => (float) $totalItemsCount,
        ];
    }
}
