@extends('warehouse.layout')

@section('title', 'Master Data Barang')
@section('page_title', 'Master Data Barang & Inventaris')
@section('page_subtitle', 'Kelola informasi SKU, barcode, harga beli HPP, harga jual, dan stok minimum')

@section('header_actions')
    <button type="button" onclick="openAddModal()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-xs transition shadow-md shadow-blue-600/20 flex items-center space-x-1.5">
        <i class="fa-solid fa-plus"></i>
        <span>Tambah Produk Baru</span>
    </button>
@endsection

@section('warehouse_content')
<div class="space-y-6">

    <!-- Filters & Search Toolbar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <form action="{{ route('warehouse.products.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="md:col-span-2 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" name="q" value="{{ request('q') }}"
                    placeholder="Cari berdasarkan Nama Barang, SKU, atau Barcode..."
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition">
            </div>

            <!-- Category Filter -->
            <div>
                <select name="category_id" onchange="this.form.submit()"
                    class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div class="flex space-x-2">
                <select name="status" onchange="this.form.submit()"
                    class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white transition">
                    <option value="">Semua Status Stok</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif Dijual</option>
                    <option value="low" {{ request('status') == 'low' ? 'selected' : '' }}>Stok Menipis (&le; Min)</option>
                    <option value="out" {{ request('status') == 'out' ? 'selected' : '' }}>Stok Habis (0)</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>

                @if(request()->hasAny(['q', 'category_id', 'status']))
                    <a href="{{ route('warehouse.products.index') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs flex items-center justify-center transition" title="Reset Filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-bold text-[10px]">
                        <th class="py-3.5 px-4">SKU & Barcode</th>
                        <th class="py-3.5 px-4">Nama Produk & Kategori</th>
                        <th class="py-3.5 px-4 text-right">Harga Beli (HPP)</th>
                        <th class="py-3.5 px-4 text-right">Harga Jual</th>
                        <th class="py-3.5 px-4 text-center">Stok / Min</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($products as $p)
                        <tr class="hover:bg-slate-50/80 transition {{ !$p->is_active ? 'opacity-60 bg-slate-50/40' : '' }}">
                            <td class="py-3.5 px-4">
                                <div class="font-mono font-bold text-blue-700 text-[11px]">{{ $p->sku }}</div>
                                <div class="text-[10px] text-slate-400 font-mono flex items-center mt-0.5">
                                    <i class="fa-solid fa-barcode mr-1"></i> {{ $p->barcode }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $p->name }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    {{ $p->category->name ?? 'Tanpa Kategori' }} · Satuan: <strong class="text-slate-600">{{ $p->unit }}</strong>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-slate-600">
                                Rp {{ number_format($p->purchase_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900">
                                Rp {{ number_format($p->selling_price, 0, ',', '.') }}
                                @php
                                    $margin = $p->purchase_price > 0 ? (($p->selling_price - $p->purchase_price) / $p->selling_price) * 100 : 0;
                                @endphp
                                <div class="text-[10px] text-emerald-600 font-normal">
                                    Margin: {{ number_format($margin, 1) }}%
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="font-bold font-mono text-sm {{ $p->stock <= 0 ? 'text-rose-600' : ($p->stock <= $p->minimum_stock ? 'text-amber-500' : 'text-slate-800') }}">
                                    {{ (int)$p->stock }}
                                </span>
                                <span class="text-slate-400 text-[10px]"> / {{ (int)$p->minimum_stock }} {{ $p->unit }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if (!$p->is_active)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        Nonaktif
                                    </span>
                                @elseif ($p->stock <= 0)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                        Habis
                                    </span>
                                @elseif ($p->stock <= $p->minimum_stock)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200">
                                        Menipis
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                        Tersedia
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <button type="button" onclick='openEditModal({{ json_encode($p) }})'
                                        class="p-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition" title="Edit Barang">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <form action="{{ route('warehouse.products.toggle', $p->id) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin mengubah status aktif barang ini?')">
                                        @csrf
                                        <button type="submit"
                                            class="p-1.5 rounded-lg {{ $p->is_active ? 'bg-amber-50 text-amber-600 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' }} transition"
                                            title="{{ $p->is_active ? 'Nonaktifkan Produk' : 'Aktifkan Produk' }}">
                                            <i class="fa-solid {{ $p->is_active ? 'fa-toggle-on text-emerald-600' : 'fa-toggle-off text-slate-400' }} text-base"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-box-open text-4xl mb-3 text-slate-300"></i>
                                <div>Tidak ada data barang yang sesuai dengan filter pencarian.</div>
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

<!-- ================= MODAL TAMBAH PRODUK ================= -->
<div id="addModal" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full overflow-hidden shadow-2xl animate-scale-up">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center">
                    <i class="fa-solid fa-box-archive text-blue-600 mr-2"></i> Tambah Master Produk Baru
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Daftarkan SKU baru dengan harga dan saldo awal</p>
            </div>
            <button onclick="closeAddModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>

        <form action="{{ route('warehouse.products.store') }}" method="POST" class="p-6 space-y-4 text-xs">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Kode SKU (Unik) *</label>
                    <input type="text" name="sku" required placeholder="Contoh: MKN-SNK-01"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white font-mono uppercase">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Barcode / QR *</label>
                    <input type="text" name="barcode" required placeholder="Contoh: 8991234567890"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white font-mono">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-2">
                    <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Nama Produk *</label>
                    <input type="text" name="name" required placeholder="Contoh: Biskuit Roma Kelapa 300g"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Kategori *</label>
                    <select name="category_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white">
                        <option value="">Pilih Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Harga Beli / HPP (Rp) *</label>
                    <input type="number" name="purchase_price" required min="0" placeholder="0" id="addPurchasePrice" oninput="calculateAddMargin()"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white font-mono">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Harga Jual Kasir (Rp) *</label>
                    <input type="number" name="selling_price" required min="0" placeholder="0" id="addSellingPrice" oninput="calculateAddMargin()"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white font-mono font-bold">
                    <div id="addMarginInfo" class="text-[10px] text-slate-400 mt-1">Margin estimasi: -</div>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Saldo Stok Awal</label>
                    <input type="number" name="initial_stock" value="0" min="0"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white font-mono">
                    <span class="text-[10px] text-slate-400">Dicatat ke ledger otomatis</span>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Batas Stok Min *</label>
                    <input type="number" name="minimum_stock" value="5" required min="0"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white font-mono">
                    <span class="text-[10px] text-slate-400">Peringatan restock</span>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Satuan *</label>
                    <input type="text" name="unit" value="pcs" required placeholder="pcs, box, botol..."
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold transition shadow-md shadow-blue-600/20">Simpan Produk</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL EDIT PRODUK ================= -->
<div id="editModal" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full overflow-hidden shadow-2xl animate-scale-up">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center">
                    <i class="fa-solid fa-pen-to-square text-blue-600 mr-2"></i> Perbarui Master Produk
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Edit informasi harga, barcode, dan konfigurasi produk</p>
            </div>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>

        <form id="editForm" method="POST" class="p-6 space-y-4 text-xs">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Kode SKU *</label>
                    <input type="text" name="sku" id="editSku" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white font-mono uppercase">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Barcode / QR *</label>
                    <input type="text" name="barcode" id="editBarcode" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white font-mono">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-2">
                    <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Nama Produk *</label>
                    <input type="text" name="name" id="editName" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Kategori *</label>
                    <select name="category_id" id="editCategoryId" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Harga Beli / HPP (Rp) *</label>
                    <input type="number" name="purchase_price" id="editPurchasePrice" required min="0"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white font-mono">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Harga Jual Kasir (Rp) *</label>
                    <input type="number" name="selling_price" id="editSellingPrice" required min="0"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white font-mono font-bold">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Batas Stok Min *</label>
                    <input type="number" name="minimum_stock" id="editMinimumStock" required min="0"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white font-mono">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Satuan *</label>
                    <input type="text" name="unit" id="editUnit" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white">
                </div>
            </div>

            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-800 text-[11px]">
                <i class="fa-solid fa-circle-info mr-1"></i> Perubahan kuantitas saldo fisik dilakukan melalui menu <strong>Penerimaan Barang</strong> atau <strong>Koreksi Stok</strong> demi menjaga keakuratan buku mutasi.
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold transition shadow-md shadow-blue-600/20">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openAddModal() {
        document.getElementById('addModal').classList.remove('hidden');
    }
    function closeAddModal() {
        document.getElementById('addModal').classList.add('hidden');
    }

    function calculateAddMargin() {
        const buy = parseFloat(document.getElementById('addPurchasePrice').value) || 0;
        const sell = parseFloat(document.getElementById('addSellingPrice').value) || 0;
        const info = document.getElementById('addMarginInfo');
        if (sell > 0) {
            const margin = ((sell - buy) / sell) * 100;
            info.textContent = `Margin kotor: ${margin.toFixed(1)}% (Laba: Rp ${(sell - buy).toLocaleString('id-ID')})`;
            info.className = margin >= 0 ? 'text-[10px] text-emerald-600 font-medium mt-1' : 'text-[10px] text-rose-600 font-medium mt-1';
        } else {
            info.textContent = 'Margin estimasi: -';
        }
    }

    function openEditModal(product) {
        document.getElementById('editForm').action = `{{ url('warehouse/products') }}/${product.id}`;
        document.getElementById('editSku').value = product.sku;
        document.getElementById('editBarcode').value = product.barcode;
        document.getElementById('editName').value = product.name;
        document.getElementById('editCategoryId').value = product.category_id;
        document.getElementById('editPurchasePrice').value = product.purchase_price;
        document.getElementById('editSellingPrice').value = product.selling_price;
        document.getElementById('editMinimumStock').value = product.minimum_stock;
        document.getElementById('editUnit').value = product.unit;

        document.getElementById('editModal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
