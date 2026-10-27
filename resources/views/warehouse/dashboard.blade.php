@extends('warehouse.layout')

@section('title', 'Dashboard Gudang & Logistik')
@section('page_title', 'Dashboard Ringkasan Gudang')
@section('page_subtitle', 'Monitoring status persediaan barang, mutasi stok, dan logistik toko')

@section('header_actions')
    <a href="{{ route('warehouse.receipts.create') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-xs transition shadow-md shadow-emerald-600/20 flex items-center space-x-1.5">
        <i class="fa-solid fa-plus"></i>
        <span>Penerimaan Barang</span>
    </a>
    <a href="{{ route('warehouse.adjustments.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-xs transition shadow-md shadow-blue-600/20 flex items-center space-x-1.5">
        <i class="fa-solid fa-sliders"></i>
        <span>Koreksi Stok</span>
    </a>
@endsection

@section('warehouse_content')
<div class="space-y-8">
    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total SKU</span>
                <div class="text-2xl font-black text-slate-800 font-mono mt-1">{{ $totalSku }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Item Terdaftar</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-barcode"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">SKU Aktif</span>
                <div class="text-2xl font-black text-emerald-600 font-mono mt-1">{{ $activeSku }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Siap Dijual Kasir</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Stok Menipis</span>
                <div class="text-2xl font-black text-amber-500 font-mono mt-1">{{ $lowStockCount }}</div>
                <div class="text-[11px] text-amber-600 mt-1 font-medium">&le; Batas Minimum</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Stok Habis (0)</span>
                <div class="text-2xl font-black text-rose-600 font-mono mt-1">{{ $outOfStockCount }}</div>
                <div class="text-[11px] text-rose-500 mt-1 font-medium">Perlu Segera PO</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-ban"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Nilai Persediaan</span>
                <div class="text-lg font-black text-slate-800 font-mono mt-1">Rp {{ number_format($totalValuation, 0, ',', '.') }}</div>
                <div class="text-[11px] text-slate-500 mt-1">Valuasi HPP Aset</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
        </div>
    </div>

    <!-- Quick Actions Banner -->
    <div class="bg-gradient-to-r from-blue-700 via-blue-800 to-indigo-900 rounded-2xl p-6 text-white shadow-xl flex items-center justify-between">
        <div>
            <h3 class="text-lg font-bold">Operasional Cepat Inventaris</h3>
            <p class="text-xs text-blue-200 mt-1 max-w-xl">
                Semua penerimaan dan koreksi stok wajib diverifikasi. Sistem secara otomatis mencatat audit trail (ledger) untuk setiap perubahan kuantitas.
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('warehouse.receipts.create') }}" class="px-4 py-2.5 bg-white text-blue-900 hover:bg-blue-50 font-bold rounded-xl text-xs transition shadow-md">
                <i class="fa-solid fa-truck-fast mr-1.5"></i> Penerimaan Supplier
            </a>
            <a href="{{ route('warehouse.adjustments.create') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl text-xs transition border border-white/20">
                <i class="fa-solid fa-scale-balanced mr-1.5"></i> Koreksi Opname
            </a>
            <a href="{{ route('warehouse.products.index') }}" class="px-4 py-2.5 bg-blue-950/60 hover:bg-blue-950 text-white font-bold rounded-xl text-xs transition border border-white/20">
                <i class="fa-solid fa-plus mr-1.5"></i> Tambah Master Barang
            </a>
        </div>
    </div>

    <!-- Two Columns: Recent Stock Movements & Recent Goods Receipts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Recent Stock Movements -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-list-check text-blue-600"></i>
                    <h3 class="font-bold text-slate-800 text-sm">Mutasi Stok Terbaru</h3>
                </div>
                <a href="{{ route('warehouse.movements') }}" class="text-xs text-blue-600 hover:underline font-semibold">
                    Lihat Semua Buku Mutasi &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100 flex-1 overflow-y-auto max-h-[420px]">
                @forelse ($recentMovements as $move)
                    <div class="p-4 hover:bg-slate-50 transition flex items-center justify-between text-xs">
                        <div class="space-y-0.5">
                            <div class="flex items-center space-x-2">
                                <span class="font-bold text-slate-900">{{ $move->product->name ?? 'Produk' }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold 
                                    @if($move->movement_type === 'PURCHASE_IN') bg-emerald-100 text-emerald-800
                                    @elseif($move->movement_type === 'SALE') bg-blue-100 text-blue-800
                                    @elseif($move->movement_type === 'ADJUSTMENT_IN') bg-purple-100 text-purple-800
                                    @else bg-rose-100 text-rose-800 @endif">
                                    {{ $move->movement_label }}
                                </span>
                            </div>
                            <div class="text-[11px] text-slate-500">
                                {{ $move->created_at->format('d/m/Y H:i') }} · Oleh: <strong>{{ $move->user->name ?? 'Sistem' }}</strong>
                            </div>
                            @if ($move->notes)
                                <div class="text-[10px] text-slate-400 italic">{{ $move->notes }}</div>
                            @endif
                        </div>

                        <div class="text-right">
                            <div class="font-mono font-bold text-sm {{ $move->qty_change > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $move->qty_change > 0 ? '+' : '' }}{{ (int)$move->qty_change }} {{ $move->product->unit ?? 'pcs' }}
                            </div>
                            <div class="text-[10px] text-slate-400 font-mono">
                                Saldo: {{ (int)$move->stock_after }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-slate-400">Belum ada riwayat mutasi stok.</div>
                @endforelse
            </div>
        </div>

        <!-- Recent Goods Receipts -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-truck-ramp-box text-emerald-600"></i>
                    <h3 class="font-bold text-slate-800 text-sm">Penerimaan Barang Terakhir</h3>
                </div>
                <a href="{{ route('warehouse.receipts.create') }}" class="text-xs text-emerald-600 hover:underline font-semibold">
                    + Input Penerimaan Baru
                </a>
            </div>

            <div class="divide-y divide-slate-100 flex-1 overflow-y-auto max-h-[420px]">
                @forelse ($recentReceipts as $rcpt)
                    <div class="p-4 hover:bg-slate-50 transition space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200 text-[11px]">
                                {{ $rcpt->receipt_number }}
                            </span>
                            <span class="text-slate-400 text-[11px]">{{ $rcpt->received_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-700">
                            <div>
                                <i class="fa-solid fa-building text-slate-400 mr-1"></i>
                                <span>Pemasok: <strong>{{ $rcpt->supplier_name ?? 'Supplier Umum' }}</strong></span>
                            </div>
                            <span class="text-slate-500 font-medium">{{ $rcpt->items->count() }} jenis barang</span>
                        </div>
                        <div class="bg-slate-50 p-2.5 rounded-xl text-[11px] space-y-1">
                            @foreach ($rcpt->items->take(3) as $it)
                                <div class="flex justify-between text-slate-600">
                                    <span>{{ $it->product->name ?? 'Produk' }}</span>
                                    <strong class="text-slate-800">+{{ (int)$it->qty }} {{ $it->product->unit ?? 'pcs' }}</strong>
                                </div>
                            @endforeach
                            @if ($rcpt->items->count() > 3)
                                <div class="text-[10px] text-slate-400 text-right">+{{ $rcpt->items->count() - 3 }} barang lainnya...</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-slate-400">Belum ada dokumen penerimaan barang.</div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection
