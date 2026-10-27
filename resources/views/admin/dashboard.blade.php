@extends('admin.layout')

@section('title', 'Dashboard Eksekutif Admin')

@section('admin_content')
<div class="space-y-6">

    <!-- Header & Period Date Filter -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Analitik Penjualan & Keuntungan</h2>
            <p class="text-xs text-slate-500 mt-0.5">Pemantauan performa bisnis retail berbasis data real-time</p>
        </div>

        <form action="{{ route('admin.dashboard') }}" method="GET" class="flex flex-wrap items-center gap-2 text-xs">
            <div class="flex items-center space-x-1">
                <input type="date" name="start_date" value="{{ $startDate }}"
                    class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white text-xs">
                <span class="text-slate-400">&ndash;</span>
                <input type="date" name="end_date" value="{{ $endDate }}"
                    class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white text-xs">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition shadow-sm">
                Terapkan
            </button>
        </form>
    </div>

    <!-- KPI Cards Grid (Mobile Vertical Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <!-- 1. Total Omset -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider">Total Omset</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-coins"></i>
                </div>
            </div>
            <div>
                <div class="text-xl font-black text-slate-900 font-mono">
                    Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-slate-500 mt-1">Penjualan Bersih</div>
            </div>
        </div>

        <!-- 2. Laba Kotor (HPP) -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider">Laba Kotor</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                </div>
            </div>
            <div>
                <div class="text-xl font-black text-emerald-600 font-mono">
                    Rp {{ number_format($summary['gross_profit'], 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-slate-500 mt-1">Omset &minus; Total HPP</div>
            </div>
        </div>

        <!-- 3. Margin Laba -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider">Margin Laba</span>
                <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-percent"></i>
                </div>
            </div>
            <div>
                <div class="text-xl font-black text-purple-600 font-mono">
                    {{ number_format($summary['profit_margin'], 1) }}%
                </div>
                <div class="text-[11px] text-slate-500 mt-1">Rata-rata Margin Produk</div>
            </div>
        </div>

        <!-- 4. Jumlah Transaksi -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider">Transaksi</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-receipt"></i>
                </div>
            </div>
            <div>
                <div class="text-xl font-black text-slate-900 font-mono">
                    {{ $summary['transaction_count'] }}
                </div>
                <div class="text-[11px] text-slate-500 mt-1">Struk Penjualan Kasir</div>
            </div>
        </div>

        <!-- 5. Rata-rata Belanja -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider">Rata Belanja</span>
                <div class="w-8 h-8 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-basket-shopping"></i>
                </div>
            </div>
            <div>
                <div class="text-base font-black text-slate-900 font-mono truncate" title="Rp {{ number_format($summary['average_basket'], 0, ',', '.') }}">
                    Rp {{ number_format($summary['average_basket'], 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-slate-500 mt-1">Nilai Rata-rata per Struk</div>
            </div>
        </div>

        <!-- 6. Stok Menipis -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider">Stok Menipis</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <div>
                <div class="text-xl font-black text-amber-500 font-mono">
                    {{ $summary['low_stock_count'] }}
                </div>
                <a href="{{ route('admin.reports.inventory', ['status' => 'low']) }}" class="text-[11px] text-amber-600 hover:underline font-semibold mt-1 block">
                    Lihat Produk Menipis &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Charts Section (Responsive width) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Daily Sales Trend Chart (Span 2) -->
        <div class="lg:col-span-2 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Tren Omset & Transaksi Harian</h3>
                    <p class="text-[11px] text-slate-400">Grafik pergerakan omset 14 hari terakhir</p>
                </div>
                <span class="text-[10px] font-bold px-2 py-0.5 bg-blue-50 text-blue-600 rounded">Live Agregasi</span>
            </div>
            <div class="flex-1 min-h-[280px]">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>

        <!-- Category Sales Share Chart -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col">
            <div class="mb-4">
                <h3 class="font-bold text-slate-800 text-sm">Komposisi Omset Kategori</h3>
                <p class="text-[11px] text-slate-400">Distribusi omset penjualan per kategori</p>
            </div>
            <div class="flex-1 flex items-center justify-center min-h-[280px]">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top 5 Best Selling Products -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-800 text-sm">5 Produk Terlaris (Top Sellers)</h3>
                <p class="text-[11px] text-slate-400">Berdasarkan volume penjualan pada periode yang dipilih</p>
            </div>
            <a href="{{ route('admin.reports.profit') }}" class="text-xs text-blue-600 hover:underline font-semibold">
                Lihat Analisis Laba Lengkap &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-bold text-[10px]">
                        <th class="py-3 px-4">Peringkat</th>
                        <th class="py-3 px-4">Nama Produk & SKU</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4 text-center">Qty Terjual</th>
                        <th class="py-3 px-4 text-right">Total Nilai Penjualan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($summary['top_products'] as $index => $top)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 font-bold text-slate-400">
                                <span class="w-6 h-6 rounded-full {{ $index === 0 ? 'bg-amber-100 text-amber-700 font-black' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center text-xs">
                                    #{{ $index + 1 }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $top->product->name ?? 'Produk' }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $top->product->sku ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-500">
                                {{ $top->product->category->name ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-slate-900">
                                {{ (int)$top->total_qty }} {{ $top->product->unit ?? 'pcs' }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600">
                                Rp {{ number_format($top->total_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">
                                Belum ada data penjualan pada rentang tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
    // 1. Daily Sales Trend Chart
    const dailyData = @json($summary['daily_trends']);
    const trendLabels = dailyData.map(d => {
        const parts = d.sale_date.split('-');
        return `${parts[2]}/${parts[1]}`;
    });
    const revenueValues = dailyData.map(d => parseFloat(d.daily_revenue));
    const txValues = dailyData.map(d => parseInt(d.daily_transactions));

    const ctxTrend = document.getElementById('salesTrendChart').getContext('2d');
    new Chart(ctxTrend, {
        type: 'bar',
        data: {
            labels: trendLabels,
            datasets: [
                {
                    label: 'Omset Harian (Rp)',
                    data: revenueValues,
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: '#2563eb',
                    borderWidth: 1.5,
                    borderRadius: 6,
                    yAxisID: 'y'
                },
                {
                    label: 'Jumlah Transaksi',
                    data: txValues,
                    type: 'line',
                    borderColor: '#10b981',
                    backgroundColor: '#10b981',
                    borderWidth: 2.5,
                    pointRadius: 4,
                    tension: 0.3,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11, family: 'Plus Jakarta Sans' } } },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            if (context.dataset.yAxisID === 'y') {
                                return `Omset: ${formatRupiah(context.raw)}`;
                            }
                            return `Transaksi: ${context.raw} struk`;
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: {
                    type: 'linear',
                    position: 'left',
                    ticks: {
                        callback: val => 'Rp ' + (val >= 1000000 ? (val/1000000).toFixed(1) + 'M' : (val/1000).toFixed(0) + 'k'),
                        font: { size: 10 }
                    }
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    grid: { display: false },
                    ticks: { font: { size: 10 } }
                }
            }
        }
    });

    // 2. Category Share Doughnut Chart
    const catData = @json($summary['category_sales']);
    const catLabels = catData.map(c => c.category_name || 'Lainnya');
    const catValues = catData.map(c => parseFloat(c.total_revenue));

    const ctxCat = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctxCat, {
        type: 'doughnut',
        data: {
            labels: catLabels,
            datasets: [{
                data: catValues,
                backgroundColor: [
                    '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#6366f1'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10, family: 'Plus Jakarta Sans' } } },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.label}: ${formatRupiah(context.raw)}`;
                        }
                    }
                }
            },
            cutout: '65%'
        }
    });
</script>
@endpush
@endsection
