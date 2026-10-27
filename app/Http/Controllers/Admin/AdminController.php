<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Sale;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function dashboard(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $summary = $this->reportService->getDashboardSummary($startDate, $endDate);

        return view('admin.dashboard', compact('summary', 'startDate', 'endDate'));
    }

    public function salesReport(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $paymentMethod = $request->input('method');

        if ($request->input('export') === 'csv') {
            return $this->exportSalesCsv($startDate, $endDate, $paymentMethod);
        }

        $sales = $this->reportService->getSalesReport($startDate, $endDate, $paymentMethod);

        return view('admin.reports.sales', compact('sales', 'startDate', 'endDate', 'paymentMethod'));
    }

    public function profitReport(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        if ($request->input('export') === 'csv') {
            return $this->exportProfitCsv($startDate, $endDate);
        }

        $profitData = $this->reportService->getProfitReport($startDate, $endDate);

        // Aggregate summary metrics
        $totalSales = 0;
        $totalCost = 0;
        $totalProfit = 0;

        foreach ($profitData as $item) {
            $totalSales += (float) $item->total_revenue;
            $totalCost += (float) $item->total_cogs;
            $totalProfit += (float) $item->total_profit;
        }

        $overallMargin = $totalSales > 0 ? ($totalProfit / $totalSales) * 100 : 0;

        return view('admin.reports.profit', compact(
            'profitData',
            'startDate',
            'endDate',
            'totalSales',
            'totalCost',
            'totalProfit',
            'overallMargin'
        ));
    }

    public function inventoryReport(Request $request)
    {
        $categoryId = $request->input('category_id');
        $status = $request->input('status');
        $categories = Category::all();

        if ($request->input('export') === 'csv') {
            return $this->exportInventoryCsv($categoryId, $status);
        }

        $report = $this->reportService->getInventoryReport(
            categoryId: $categoryId ? (int) $categoryId : null,
            status: $status
        );

        return view('admin.reports.inventory', [
            'products' => $report['products'],
            'totalValuation' => $report['total_valuation'],
            'totalStockCount' => $report['total_stock_count'],
            'categories' => $categories,
            'selectedCategory' => $categoryId,
            'selectedStatus' => $status,
        ]);
    }

    public function saleDetail(string $invoiceNumber)
    {
        $sale = Sale::with(['user', 'items.product', 'payment'])
            ->where('invoice_number', $invoiceNumber)
            ->firstOrFail();

        return view('admin.reports.sale_detail', compact('sale'));
    }

    /**
     * CSV Export implementations for Read-Only Admin
     */
    protected function exportSalesCsv(string $start, string $end, ?string $method): StreamedResponse
    {
        $fileName = 'laporan-penjualan-' . now()->format('Ymd-His') . '.csv';

        $query = Sale::with(['user', 'payment', 'items'])
            ->where('status', 'completed')
            ->whereBetween('sold_at', [$start . ' 00:00:00', $end . ' 23:59:59']);

        if ($method) {
            $query->whereHas('payments', function ($q) use ($method) {
                $q->where('method', $method);
            });
        }

        $sales = $query->latest('sold_at')->get();

        return response()->streamDownload(function () use ($sales) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['No. Invoice', 'Tanggal & Waktu', 'Kasir', 'Metode Bayar', 'Total Item', 'Subtotal', 'Diskon', 'Total Pembayaran']);

            foreach ($sales as $sale) {
                fputcsv($handle, [
                    $sale->invoice_number,
                    $sale->sold_at->format('d/m/Y H:i'),
                    $sale->user->name ?? 'Kasir',
                    strtoupper($sale->payment->method ?? '-'),
                    $sale->items->sum('qty'),
                    $sale->subtotal,
                    $sale->discount,
                    $sale->total,
                ]);
            }
            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    protected function exportProfitCsv(string $start, string $end): StreamedResponse
    {
        $fileName = 'laporan-laba-rugi-' . now()->format('Ymd-His') . '.csv';
        $items = $this->reportService->getProfitReport($start, $end);

        return response()->streamDownload(function () use ($items) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['SKU', 'Nama Produk', 'Kategori', 'Kuantitas Terjual', 'Penjualan Bersih (Rp)', 'Total HPP (Rp)', 'Laba Kotor (Rp)', 'Margin (%)']);

            foreach ($items as $item) {
                $revenue = (float) $item->total_revenue;
                $cogs = (float) $item->total_cogs;
                $profit = (float) $item->total_profit;
                $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

                fputcsv($handle, [
                    $item->product->sku ?? '-',
                    $item->product->name ?? '-',
                    $item->product->category->name ?? '-',
                    $item->total_qty,
                    $revenue,
                    $cogs,
                    $profit,
                    number_format($margin, 2) . '%',
                ]);
            }
            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    protected function exportInventoryCsv(?int $catId, ?string $status): StreamedResponse
    {
        $fileName = 'laporan-persediaan-' . now()->format('Ymd-His') . '.csv';
        $report = $this->reportService->getInventoryReport($catId, $status);

        return response()->streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['SKU', 'Barcode', 'Nama Produk', 'Kategori', 'Harga Beli (HPP)', 'Harga Jual', 'Stok Saat Ini', 'Stok Min', 'Satuan', 'Nilai Persediaan', 'Status']);

            foreach ($report['products'] as $p) {
                fputcsv($handle, [
                    $p->sku,
                    $p->barcode,
                    $p->name,
                    $p->category->name ?? '-',
                    $p->purchase_price,
                    $p->selling_price,
                    $p->stock,
                    $p->minimum_stock,
                    $p->unit,
                    $p->stock * $p->purchase_price,
                    $p->is_active ? ($p->stock <= 0 ? 'Habis' : ($p->stock <= $p->minimum_stock ? 'Menipis' : 'Tersedia')) : 'Nonaktif',
                ]);
            }
            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }
}
