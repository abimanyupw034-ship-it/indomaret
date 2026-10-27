@extends('admin.layout')

@section('title', 'Detail Transaksi ' . $sale->invoice_number)

@section('admin_content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Top Action Toolbar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.reports.sales') }}" class="inline-flex items-center text-xs font-semibold text-slate-600 hover:text-blue-600 transition">
            <i class="fa-solid fa-arrow-left mr-1.5"></i> Kembali ke Daftar Laporan Penjualan
        </a>
        <span class="text-[11px] font-mono text-slate-400">ID Faktur: #{{ $sale->id }}</span>
    </div>

    <!-- Main Receipt Breakdown Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-md p-6 md:p-8 space-y-6">
        
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-slate-100 gap-4">
            <div>
                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                    STATUS: SELESAI (COMPLETED)
                </span>
                <h1 class="text-2xl font-black text-slate-900 font-mono mt-2">{{ $sale->invoice_number }}</h1>
                <div class="text-xs text-slate-500 mt-1 flex items-center space-x-2">
                    <span><i class="fa-regular fa-calendar mr-1"></i> {{ $sale->sold_at->format('d F Y, H:i:s') }}</span>
                    <span>&bull;</span>
                    <span><i class="fa-solid fa-user-tie mr-1"></i> Kasir: <strong>{{ $sale->user->name ?? 'Kasir' }}</strong></span>
                </div>
            </div>

            <div class="text-left md:text-right">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Pembayaran</span>
                <div class="text-2xl font-black text-emerald-600 font-mono mt-0.5">
                    Rp {{ number_format($sale->total, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-slate-500 mt-0.5">
                    Metode: <strong>{{ strtoupper($sale->payment->method ?? 'TUNAI') }}</strong>
                </div>
            </div>
        </div>

        <!-- Items Table with Historical HPP & Profit Analysis -->
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Rincian Barang & Analisis Profit Per Item</h3>
            <div class="border border-slate-200 rounded-2xl overflow-hidden">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-bold text-[10px]">
                            <th class="py-3 px-4">Nama Produk & SKU</th>
                            <th class="py-3 px-3 text-center">Qty</th>
                            <th class="py-3 px-3 text-right">Harga Jual</th>
                            <th class="py-3 px-3 text-right">Snapshot HPP</th>
                            <th class="py-3 px-3 text-right">Laba Kotor</th>
                            <th class="py-3 px-4 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @php
                            $totalLineProfit = 0;
                            $totalLineCogs = 0;
                        @endphp
                        @foreach ($sale->items as $item)
                            @php
                                $lineProfit = (float)$item->subtotal - ((float)$item->unit_cost * (float)$item->qty);
                                $totalLineProfit += $lineProfit;
                                $totalLineCogs += ((float)$item->unit_cost * (float)$item->qty);
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900">{{ $item->product->name ?? 'Produk' }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $item->product->sku ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-3 text-center font-mono font-bold text-slate-800">
                                    {{ (int)$item->qty }} {{ $item->product->unit ?? 'pcs' }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono text-slate-600">
                                    Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono text-rose-500">
                                    Rp {{ number_format($item->unit_cost, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-bold text-emerald-600">
                                    Rp {{ number_format($lineProfit, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Totals & Payment Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100 text-xs">
            <!-- Left: Financial Analysis -->
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 space-y-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block mb-1">
                    Ringkasan Margin Transaksi Ini
                </span>
                <div class="flex justify-between text-slate-600">
                    <span>Total HPP / Modal:</span>
                    <strong class="font-mono text-rose-600">Rp {{ number_format($totalLineCogs, 0, ',', '.') }}</strong>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Laba Kotor Diperoleh:</span>
                    <strong class="font-mono text-emerald-600">Rp {{ number_format($totalLineProfit - $sale->discount, 0, ',', '.') }}</strong>
                </div>
                @php
                    $txMargin = $sale->total > 0 ? (($totalLineProfit - $sale->discount) / $sale->total) * 100 : 0;
                @endphp
                <div class="flex justify-between text-slate-600 pt-1 border-t border-slate-200">
                    <span>Margin Keuntungan Transaksi:</span>
                    <strong class="font-mono text-purple-700 font-bold">{{ number_format($txMargin, 1) }}%</strong>
                </div>
            </div>

            <!-- Right: Receipt Totals -->
            <div class="space-y-2 text-right">
                <div class="flex justify-between text-slate-500">
                    <span>Subtotal Barang:</span>
                    <span class="font-mono text-slate-800">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span>
                </div>
                @if ($sale->discount > 0)
                    <div class="flex justify-between text-rose-600">
                        <span>Diskon / Potongan:</span>
                        <span class="font-mono">- Rp {{ number_format($sale->discount, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-base font-bold text-slate-900 pt-2 border-t border-slate-200">
                    <span>Total Akhir:</span>
                    <span class="font-mono text-emerald-600 font-black">Rp {{ number_format($sale->total, 0, ',', '.') }}</span>
                </div>
                @php $payment = $sale->payment; @endphp
                @if ($payment)
                    <div class="flex justify-between text-slate-500 text-[11px] pt-1">
                        <span>Jumlah Diterima ({{ strtoupper($payment->method) }}):</span>
                        <span class="font-mono">Rp {{ number_format($payment->amount_received, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-slate-500 text-[11px]">
                        <span>Kembalian:</span>
                        <span class="font-mono">Rp {{ number_format($payment->change_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
