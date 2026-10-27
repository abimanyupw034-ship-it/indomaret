@extends('warehouse.layout')

@section('title', 'Koreksi & Opname Stok')
@section('page_title', 'Koreksi Stok Fisik (Stock Adjustment)')
@section('page_subtitle', 'Penyesuaian saldo sistem berdasarkan hasil stock opname fisik toko')

@section('warehouse_content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Policy Banner -->
    <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-2xl p-4 flex items-start space-x-3 text-xs">
        <i class="fa-solid fa-triangle-exclamation text-amber-600 text-base mt-0.5"></i>
        <div>
            <div class="font-bold">Ketentuan Koreksi Stok (Audit Integrity)</div>
            <p class="text-amber-700 mt-0.5 leading-relaxed">
                Pengguna gudang tidak dapat mengubah saldo stok secara diam-diam. Setiap koreksi akan membandingkan stok fisik dengan stok sistem, menghasilkan mutasi bertipe <code>ADJUSTMENT_IN</code> atau <code>ADJUSTMENT_OUT</code>, serta mewajibkan alasan dan catatan tertulis.
            </p>
        </div>
    </div>

    <form action="{{ route('warehouse.adjustments.store') }}" method="POST" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-5 text-xs">
        @csrf

        <!-- Product Selector -->
        <div>
            <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Pilih Produk Yang Disesuaikan *</label>
            <select name="product_id" id="productSelect" required onchange="onProductChange()"
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white text-xs">
                <option value="">-- Cari atau Pilih Produk --</option>
                @foreach ($products as $p)
                    <option value="{{ $p->id }}" data-stock="{{ $p->stock }}" data-unit="{{ $p->unit }}" data-sku="{{ $p->sku }}">
                        {{ $p->name }} (SKU: {{ $p->sku }}) · [Stok Sistem: {{ (int)$p->stock }} {{ $p->unit }}]
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Current vs Physical Comparison Grid -->
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Saldo Stok Sistem Saat Ini</span>
                <div class="text-2xl font-black text-slate-800 font-mono mt-1" id="systemStockDisplay">0 pcs</div>
                <div class="text-[10px] text-slate-400 mt-1" id="skuDisplay">SKU: -</div>
            </div>

            <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-200">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-blue-800 mb-1">Hasil Hitung Fisik Sebenarnya *</label>
                <div class="flex items-center space-x-2">
                    <input type="number" name="physical_stock" id="physicalStockInput" required min="0" step="1"
                        placeholder="0" oninput="calculateDifference()"
                        class="w-full px-3 py-1.5 bg-white border border-blue-300 rounded-lg text-lg font-mono font-bold text-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <span id="unitDisplay" class="text-xs font-bold text-blue-800 font-mono">pcs</span>
                </div>
                <div class="text-[10px] text-blue-600 mt-1">Masukkan hasil opname fisik</div>
            </div>
        </div>

        <!-- Difference Result Box -->
        <div id="diffBox" class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Selisih Penyesuaian</span>
                <div class="text-lg font-black font-mono text-slate-400 mt-0.5" id="diffValueDisplay">0 pcs</div>
            </div>
            <div id="diffBadge" class="text-xs font-bold px-3 py-1 rounded-full bg-slate-200 text-slate-600">
                Menunggu Input
            </div>
        </div>

        <!-- Reason Dropdown -->
        <div>
            <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Alasan Koreksi Stok *</label>
            <select name="reason" required
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white text-xs">
                <option value="">-- Pilih Alasan Resmi --</option>
                <option value="Selisih Inventaris Opname">Selisih Inventaris Opname Rutin</option>
                <option value="Barang Rusak / Kadaluarsa">Barang Rusak / Pecah / Kadaluarsa</option>
                <option value="Salah Input">Koreksi Salah Input Data Sebelumnya</option>
                <option value="Lainnya">Alasan Lainnya (Jelaskan di catatan)</option>
            </select>
        </div>

        <!-- Mandatory Notes -->
        <div>
            <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Catatan & Keterangan Rinci *</label>
            <textarea name="notes" required minlength="5" rows="3"
                placeholder="Jelaskan secara spesifik temuan selisih, nomor berita acara opname, atau penyebab barang rusak..."
                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white text-xs"></textarea>
            <span class="text-[10px] text-slate-400">Wajib diisi minimal 5 karakter untuk audit trail.</span>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
            <a href="{{ route('warehouse.dashboard') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold transition">
                Batal
            </a>
            <button type="submit" id="btnSubmitAdjust" disabled
                class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-bold transition shadow-lg shadow-blue-600/20 flex items-center space-x-2">
                <i class="fa-solid fa-check"></i>
                <span>Simpan Koreksi Stok</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    let currentSystemStock = 0;
    let currentUnit = 'pcs';

    function onProductChange() {
        const select = document.getElementById('productSelect');
        const opt = select.options[select.selectedIndex];

        if (opt && opt.value) {
            currentSystemStock = parseFloat(opt.getAttribute('data-stock')) || 0;
            currentUnit = opt.getAttribute('data-unit') || 'pcs';
            const sku = opt.getAttribute('data-sku') || '-';

            document.getElementById('systemStockDisplay').textContent = `${currentSystemStock} ${currentUnit}`;
            document.getElementById('skuDisplay').textContent = `SKU: ${sku}`;
            document.getElementById('unitDisplay').textContent = currentUnit;

            calculateDifference();
        } else {
            currentSystemStock = 0;
            document.getElementById('systemStockDisplay').textContent = '0 pcs';
            document.getElementById('skuDisplay').textContent = 'SKU: -';
        }
    }

    function calculateDifference() {
        const input = document.getElementById('physicalStockInput');
        const diffValueEl = document.getElementById('diffValueDisplay');
        const diffBadgeEl = document.getElementById('diffBadge');
        const btn = document.getElementById('btnSubmitAdjust');

        if (input.value === '') {
            diffValueEl.textContent = '0 pcs';
            diffValueEl.className = 'text-lg font-black font-mono text-slate-400 mt-0.5';
            diffBadgeEl.textContent = 'Menunggu Input';
            diffBadgeEl.className = 'text-xs font-bold px-3 py-1 rounded-full bg-slate-200 text-slate-600';
            btn.disabled = true;
            return;
        }

        const physical = parseFloat(input.value) || 0;
        const diff = physical - currentSystemStock;

        if (diff === 0) {
            diffValueEl.textContent = `0 ${currentUnit} (Tidak Ada Perubahan)`;
            diffValueEl.className = 'text-lg font-black font-mono text-slate-600 mt-0.5';
            diffBadgeEl.textContent = 'Stok Sama (Tidak Perlu Koreksi)';
            diffBadgeEl.className = 'text-xs font-bold px-3 py-1 rounded-full bg-slate-200 text-slate-700';
            btn.disabled = true;
        } else if (diff > 0) {
            diffValueEl.textContent = `+${diff} ${currentUnit}`;
            diffValueEl.className = 'text-lg font-black font-mono text-emerald-600 mt-0.5';
            diffBadgeEl.textContent = 'ADJUSTMENT_IN (Tambah Stok)';
            diffBadgeEl.className = 'text-xs font-bold px-3 py-1 rounded-full bg-emerald-100 text-emerald-800';
            btn.disabled = false;
        } else {
            diffValueEl.textContent = `${diff} ${currentUnit}`;
            diffValueEl.className = 'text-lg font-black font-mono text-rose-600 mt-0.5';
            diffBadgeEl.textContent = 'ADJUSTMENT_OUT (Kurang Stok)';
            diffBadgeEl.className = 'text-xs font-bold px-3 py-1 rounded-full bg-rose-100 text-rose-800';
            btn.disabled = false;
        }
    }
</script>
@endpush
@endsection
