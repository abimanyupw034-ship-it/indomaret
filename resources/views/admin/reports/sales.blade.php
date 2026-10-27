@extends('admin.layout')

@section('title', 'Laporan Penjualan Retail')

@section('admin_content')
<div class="space-y-6">

    <!-- Header & Filter Toolbar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-800 flex items-center">
                <i class="fa-solid fa-receipt text-blue-600 mr-2"></i> Laporan Transaksi Penjualan
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Daftar seluruh transaksi yang diselesaikan oleh kasir</p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.reports.sales', array_merge(request()->query(), ['export' => 'csv'])) }}"
                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-xs transition shadow-sm flex items-center space-x-1.5">
                <i class="fa-solid fa-file-csv text-sm"></i>
                <span>Unduh CSV</span>
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm text-xs">
        <form action="{{ route('admin.reports.sales') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block font-bold text-slate-600 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white text-xs">
            </div>
            <div>
                <label class="block font-bold text-slate-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white text-xs">
            </div>
            <div>
                <label class="block font-bold text-slate-600 mb-1">Metode Pembayaran</label>
                <select name="method" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white text-xs">
                    <option value="">Semua Metode</option>
                    <option value="cash" {{ $paymentMethod == 'cash' ? 'selected' : '' }}>Tunai (Cash)</option>
                    <option value="qris" {{ $paymentMethod == 'qris' ? 'selected' : '' }}>QRIS</option>
                    <option value="card" {{ $paymentMethod == 'card' ? 'selected' : '' }}>Kartu Debit/EDC</option>
                    <option value="transfer" {{ $paymentMethod == 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                </select>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="flex-1 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition">
                    Filter Data
                </button>
                @if(request()->hasAny(['start_date', 'end_date', 'method']))
                    <a href="{{ route('admin.reports.sales') }}" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl transition flex items-center justify-center" title="Reset">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Sales Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-bold text-[10px]">
                        <th class="py-3.5 px-4">No. Faktur</th>
                        <th class="py-3.5 px-4">Waktu & Tanggal</th>
                        <th class="py-3.5 px-4">Kasir</th>
                        <th class="py-3.5 px-4">Metode Bayar</th>
                        <th class="py-3.5 px-4 text-center">Item</th>
                        <th class="py-3.5 px-4 text-right">Subtotal</th>
                        <th class="py-3.5 px-4 text-right">Diskon</th>
                        <th class="py-3.5 px-4 text-right">Total Transaksi</th>
                        <th class="py-3.5 px-4 text-center">Rincian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($sales as $sale)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-blue-700">
                                <a href="{{ route('admin.reports.sale-detail', $sale->invoice_number) }}" class="hover:underline">
                                    {{ $sale->invoice_number }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap">
                                {{ $sale->sold_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-900">
                                {{ $sale->user->name ?? 'Kasir' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    @if(($sale->payment->method ?? '') === 'cash') bg-emerald-100 text-emerald-800
                                    @elseif(($sale->payment->method ?? '') === 'qris') bg-blue-100 text-blue-800
                                    @elseif(($sale->payment->method ?? '') === 'card') bg-purple-100 text-purple-800
                                    @else bg-slate-100 text-slate-800 @endif">
                                    {{ $sale->payment->method ?? 'TUNAI' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono">
                                {{ $sale->items->sum('qty') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-slate-600">
                                Rp {{ number_format($sale->subtotal, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-rose-500">
                                {{ $sale->discount > 0 ? '- Rp ' . number_format($sale->discount, 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900">
                                Rp {{ number_format($sale->total, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('admin.reports.sale-detail', $sale->invoice_number) }}"
                                    class="p-1.5 rounded-lg bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-600 transition inline-block" title="Lihat Struk">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-receipt text-4xl mb-3 text-slate-300"></i>
                                <div>Tidak ada transaksi yang cocok dengan kriteria filter.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($sales->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $sales->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
