@extends('warehouse.layout')

@section('title', 'Buku Mutasi Stok (Audit Trail)')
@section('page_title', 'Buku Mutasi Stok & Riwayat Audit')
@section('page_subtitle', 'Catatan ledger lengkap pergerakan persediaan barang, penjualan kasir, penerimaan, dan koreksi')

@section('warehouse_content')
<div class="space-y-6">

    <!-- Filters & Search Toolbar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <form action="{{ route('warehouse.movements') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
            <!-- Product Filter -->
            <div>
                <label class="block font-bold text-slate-600 mb-1">Filter Produk</label>
                <select name="product_id" onchange="this.form.submit()"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white text-xs">
                    <option value="">Semua Produk</option>
                    @foreach ($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} ({{ $p->sku }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Movement Type Filter -->
            <div>
                <label class="block font-bold text-slate-600 mb-1">Jenis Mutasi</label>
                <select name="type" onchange="this.form.submit()"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white text-xs">
                    <option value="">Semua Jenis Mutasi</option>
                    <option value="SALE" {{ request('type') == 'SALE' ? 'selected' : '' }}>Penjualan Kasir (SALE)</option>
                    <option value="PURCHASE_IN" {{ request('type') == 'PURCHASE_IN' ? 'selected' : '' }}>Penerimaan Supplier (PURCHASE_IN)</option>
                    <option value="ADJUSTMENT_IN" {{ request('type') == 'ADJUSTMENT_IN' ? 'selected' : '' }}>Koreksi Tambah (ADJUSTMENT_IN)</option>
                    <option value="ADJUSTMENT_OUT" {{ request('type') == 'ADJUSTMENT_OUT' ? 'selected' : '' }}>Koreksi Kurang (ADJUSTMENT_OUT)</option>
                </select>
            </div>

            <!-- Date Range -->
            <div class="md:col-span-2 flex space-x-2 items-end">
                <div class="flex-1">
                    <label class="block font-bold text-slate-600 mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-600 focus:bg-white">
                </div>
                <div class="flex-1">
                    <label class="block font-bold text-slate-600 mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-600 focus:bg-white">
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold transition">
                    Filter
                </button>
                @if(request()->hasAny(['product_id', 'type', 'start_date', 'end_date']))
                    <a href="{{ route('warehouse.movements') }}" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl transition" title="Reset Filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Movement Ledger Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-bold text-[10px]">
                        <th class="py-3.5 px-4">Waktu & Tanggal</th>
                        <th class="py-3.5 px-4">Jenis Mutasi</th>
                        <th class="py-3.5 px-4">Nama Produk & SKU</th>
                        <th class="py-3.5 px-4">Pengguna (Aktor)</th>
                        <th class="py-3.5 px-4 text-center">Perubahan Qty</th>
                        <th class="py-3.5 px-4 text-center">Sebelum &rarr; Sesudah</th>
                        <th class="py-3.5 px-4">Catatan & Referensi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($movements as $m)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $m->created_at->format('d/m/Y') }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $m->created_at->format('H:i:s') }}</div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold tracking-wide
                                    @if($m->movement_type === 'PURCHASE_IN') bg-emerald-100 text-emerald-800 border border-emerald-200
                                    @elseif($m->movement_type === 'SALE') bg-blue-100 text-blue-800 border border-blue-200
                                    @elseif($m->movement_type === 'ADJUSTMENT_IN') bg-purple-100 text-purple-800 border border-purple-200
                                    @else bg-rose-100 text-rose-800 border border-rose-200 @endif">
                                    {{ $m->movement_label }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $m->product->name ?? 'Produk' }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $m->product->sku ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-medium text-slate-800">{{ $m->user->name ?? 'Sistem' }}</span>
                                <div class="text-[10px] text-slate-400 uppercase tracking-wider">{{ $m->user->role ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold whitespace-nowrap">
                                <span class="{{ $m->qty_change > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $m->qty_change > 0 ? '+' : '' }}{{ (int)$m->qty_change }} {{ $m->product->unit ?? 'pcs' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono text-slate-600 whitespace-nowrap">
                                <span>{{ (int)$m->stock_before }}</span>
                                <span class="text-slate-400 mx-1">&rarr;</span>
                                <strong class="text-slate-900">{{ (int)$m->stock_after }}</strong>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 max-w-xs">
                                <div class="truncate" title="{{ $m->notes }}">{{ $m->notes ?? '-' }}</div>
                                @if($m->reference_type)
                                    <div class="text-[10px] text-slate-400 mt-0.5">Ref: {{ strtoupper($m->reference_type) }} #{{ $m->reference_id ?? '-' }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-book-open text-4xl mb-3 text-slate-300"></i>
                                <div>Tidak ada riwayat mutasi stok yang ditemukan.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($movements->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
