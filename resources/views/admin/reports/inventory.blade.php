@extends('admin.layout')

@section('title', 'Laporan Persediaan & Valuasi Aset')

@section('admin_content')
<div class="space-y-6">

    <!-- Header & Export Toolbar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-800 flex items-center">
                <i class="fa-solid fa-boxes-stacked text-purple-600 mr-2"></i> Laporan Persediaan & Valuasi Aset
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Monitoring saldo stok barang secara live dan estimasi total nilai modal persediaan berjalan
            </p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.reports.inventory', array_merge(request()->query(), ['export' => 'csv'])) }}"
                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-xs transition shadow-sm flex items-center space-x-1.5">
                <i class="fa-solid fa-file-csv text-sm"></i>
                <span>Unduh CSV</span>
            </a>
        </div>
    </div>

    <!-- Valuation Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-gradient-to-br from-purple-900 to-indigo-950 p-6 rounded-2xl text-white shadow-lg flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-purple-300">Total Valuasi Nilai Persediaan</span>
                <div class="text-2xl md:text-3xl font-black font-mono mt-1 text-white">
                    Rp {{ number_format($totalValuation, 0, ',', '.') }}
                </div>
                <div class="text-xs text-purple-200 mt-1">Dihitung dari (Saldo Stok Fisik &times; Harga Beli HPP)</div>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center text-2xl text-purple-300">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Unit Fisik Di Seluruh Toko</span>
                <div class="text-2xl md:text-3xl font-black font-mono mt-1 text-slate-900">
                    {{ number_format($totalStockCount, 0, ',', '.') }} <span class="text-base text-slate-500 font-sans font-normal">unit</span>
                </div>
                <div class="text-xs text-slate-500 mt-1">Akumulasi kuantitas produk berstatus aktif</div>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center text-2xl text-blue-600">
                <i class="fa-solid fa-cubes-stacked"></i>
            </div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm text-xs">
        <form action="{{ route('admin.reports.inventory') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block font-bold text-slate-600 mb-1">Filter Kategori</label>
                <select name="category_id" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white text-xs">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" {{ $selectedCategory == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-bold text-slate-600 mb-1">Status Persediaan</label>
                <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white text-xs">
                    <option value="">Semua Status</option>
                    <option value="active" {{ $selectedStatus == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="low" {{ $selectedStatus == 'low' ? 'selected' : '' }}>Stok Menipis (&le; Min)</option>
                    <option value="out" {{ $selectedStatus == 'out' ? 'selected' : '' }}>Stok Habis (0)</option>
                </select>
            </div>
            <div class="flex items-end">
                @if(request()->hasAny(['category_id', 'status']))
                    <a href="{{ route('admin.reports.inventory') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold rounded-xl transition flex items-center space-x-1.5">
                        <i class="fa-solid fa-rotate-left"></i>
                        <span>Reset Filter</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Inventory Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-bold text-[10px]">
                        <th class="py-3.5 px-4">Nama Produk & Barcode</th>
                        <th class="py-3.5 px-4">Kategori</th>
                        <th class="py-3.5 px-4 text-right">Harga Beli HPP</th>
                        <th class="py-3.5 px-4 text-right">Harga Jual</th>
                        <th class="py-3.5 px-4 text-center">Stok / Min</th>
                        <th class="py-3.5 px-4 text-right">Nilai Aset Persediaan</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($products as $p)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $p->name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono flex items-center mt-0.5">
                                    <span class="text-blue-600 font-bold mr-2">{{ $p->sku }}</span>
                                    <span>{{ $p->barcode }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-500">
                                {{ $p->category->name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-slate-600">
                                Rp {{ number_format($p->purchase_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900">
                                Rp {{ number_format($p->selling_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono">
                                <span class="font-bold {{ $p->stock <= 0 ? 'text-rose-600' : ($p->stock <= $p->minimum_stock ? 'text-amber-500' : 'text-slate-900') }}">
                                    {{ (int)$p->stock }}
                                </span>
                                <span class="text-[10px] text-slate-400">/ {{ (int)$p->minimum_stock }} {{ $p->unit }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-purple-700">
                                Rp {{ number_format($p->stock * $p->purchase_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if (!$p->is_active)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">Nonaktif</span>
                                @elseif ($p->stock <= 0)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">Habis</span>
                                @elseif ($p->stock <= $p->minimum_stock)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200">Menipis</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">Aman</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                Tidak ada data stok yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($products->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $products->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
