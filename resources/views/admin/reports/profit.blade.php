@extends('admin.layout')

@section('title', 'Laporan Laba Rugi & HPP')

@section('admin_content')
<div class="space-y-6">

    <!-- Header & Export Toolbar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-800 flex items-center">
                <i class="fa-solid fa-arrow-trend-up text-emerald-600 mr-2"></i> Laporan Laba Kotor & Analisis HPP
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Kalkulasi keuntungan kotor berdasarkan snapshot biaya perolehan (COGS) saat transaksi terjadi
            </p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.reports.profit', array_merge(request()->query(), ['export' => 'csv'])) }}"
                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-xs transition shadow-sm flex items-center space-x-1.5">
                <i class="fa-solid fa-file-csv text-sm"></i>
                <span>Unduh CSV</span>
            </a>
        </div>
    </div>

    <!-- Financial KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Penjualan Bersih</span>
            <div class="text-xl font-black text-slate-900 font-mono mt-1">
                Rp {{ number_format($totalSales, 0, ',', '.') }}
            </div>
            <div class="text-[10px] text-slate-500 mt-0.5">Nilai Transaksi Selesai</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total HPP (Harga Pokok)</span>
            <div class="text-xl font-black text-rose-600 font-mono mt-1">
                Rp {{ number_format($totalCost, 0, ',', '.') }}
            </div>
            <div class="text-[10px] text-slate-500 mt-0.5">Biaya Perolehan Barang</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Laba Kotor</span>
            <div class="text-xl font-black text-emerald-600 font-mono mt-1">
                Rp {{ number_format($totalProfit, 0, ',', '.') }}
            </div>
            <div class="text-[10px] text-slate-500 mt-0.5">Penjualan &minus; HPP</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Rata-rata Gross Margin</span>
            <div class="text-xl font-black text-purple-600 font-mono mt-1">
                {{ number_format($overallMargin, 1) }}%
            </div>
            <div class="text-[10px] text-slate-500 mt-0.5">Efisiensi Margin Penjualan</div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm text-xs">
        <form action="{{ route('admin.reports.profit') }}" method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block font-bold text-slate-600 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                    class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white text-xs">
            </div>
            <div>
                <label class="block font-bold text-slate-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                    class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white text-xs">
            </div>
            <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition">
                Terapkan Filter
            </button>
        </form>
    </div>

    <!-- Product Level Profitability Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-bold text-[10px]">
                        <th class="py-3.5 px-4">Nama Produk & SKU</th>
                        <th class="py-3.5 px-4">Kategori</th>
                        <th class="py-3.5 px-4 text-center">Qty Terjual</th>
                        <th class="py-3.5 px-4 text-right">Penjualan Bersih</th>
                        <th class="py-3.5 px-4 text-right">Total HPP</th>
                        <th class="py-3.5 px-4 text-right">Laba Kotor</th>
                        <th class="py-3.5 px-4 text-center">Margin %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($profitData as $item)
                        @php
                            $revenue = (float)$item->total_revenue;
                            $cogs = (float)$item->total_cogs;
                            $profit = (float)$item->total_profit;
                            $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $item->product->name ?? 'Produk' }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $item->product->sku ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-500">
                                {{ $item->product->category->name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-900">
                                {{ (int)$item->total_qty }} {{ $item->product->unit ?? 'pcs' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-slate-900">
                                Rp {{ number_format($revenue, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-rose-600">
                                Rp {{ number_format($cogs, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-emerald-600">
                                Rp {{ number_format($profit, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono
                                    {{ $margin >= 25 ? 'bg-emerald-100 text-emerald-800' : ($margin >= 10 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ number_format($margin, 1) }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                Belum ada data penjualan pada rentang tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($profitData->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $profitData->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
