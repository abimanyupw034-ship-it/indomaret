@extends('warehouse.layout')

@section('title', 'Penerimaan Barang Masuk')
@section('page_title', 'Form Penerimaan Barang (Goods Receipt)')
@section('page_subtitle', 'Catat barang masuk dari pemasok, update HPP perolehan, dan tambah saldo stok otomatis')

@section('warehouse_content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Information Card -->
    <div class="bg-blue-50 border border-blue-200 text-blue-900 rounded-2xl p-4 flex items-start space-x-3 text-xs">
        <i class="fa-solid fa-circle-info text-blue-600 text-base mt-0.5"></i>
        <div>
            <div class="font-bold">Prinsip Audit Mutasi Stok (PURCHASE_IN)</div>
            <p class="text-blue-700 mt-0.5 leading-relaxed">
                Setiap item yang diterima akan secara atomik menambah saldo stok di database dan dicatat ke dalam buku mutasi dengan referensi dokumen penerimaan. Jika harga beli diubah, master produk akan diperbarui dengan HPP perolehan terbaru.
            </p>
        </div>
    </div>

    <form action="{{ route('warehouse.receipts.store') }}" method="POST" id="receiptForm" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 space-y-6 text-xs">
        @csrf

        <!-- Document Header Section -->
        <div>
            <h3 class="font-bold text-slate-800 text-sm mb-4 flex items-center">
                <i class="fa-solid fa-file-invoice text-emerald-600 mr-2"></i> Dokumen & Informasi Pemasok
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Nama Pemasok / Distributor *</label>
                    <input type="text" name="supplier_name" required value="PT Indomarco Prismatama"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white text-xs">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">Tanggal Penerimaan *</label>
                    <input type="date" name="received_at" required value="{{ date('Y-m-d') }}"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white text-xs">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider text-[11px] mb-1.5">No. Surat Jalan / Catatan</label>
                    <input type="text" name="notes" placeholder="Contoh: DO/2026/10/0045"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600 focus:bg-white text-xs">
                </div>
            </div>
        </div>

        <!-- Items Table Section -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-slate-800 text-sm flex items-center">
                    <i class="fa-solid fa-boxes-packing text-blue-600 mr-2"></i> Rincian Barang Diterima
                </h3>
                <button type="button" onclick="addRow()"
                    class="px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 font-bold rounded-lg text-xs border border-blue-200 transition flex items-center space-x-1">
                    <i class="fa-solid fa-plus"></i>
                    <span>Tambah Baris</span>
                </button>
            </div>

            <div class="border border-slate-200 rounded-xl overflow-hidden">
                <table class="w-full text-left border-collapse" id="itemsTable">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-bold text-[10px]">
                            <th class="py-3 px-3">Produk / Barang</th>
                            <th class="py-3 px-3 w-32">Kuantitas Masuk</th>
                            <th class="py-3 px-3 w-40 text-right">Biaya Satuan / HPP (Rp)</th>
                            <th class="py-3 px-3 w-40 text-right">Subtotal</th>
                            <th class="py-3 px-3 w-12 text-center"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="itemsTableBody">
                        <!-- Default 1 Row -->
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50/80 font-bold text-slate-800">
                            <td colspan="3" class="py-3 px-3 text-right uppercase text-[11px]">Total Nilai Barang Masuk:</td>
                            <td class="py-3 px-3 text-right font-mono text-emerald-600 text-sm" id="grandTotalDisplay">Rp 0</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
            <a href="{{ route('warehouse.dashboard') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition shadow-lg shadow-emerald-600/20 flex items-center space-x-2">
                <i class="fa-solid fa-check"></i>
                <span>Konfirmasi Penerimaan & Simpan</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const availableProducts = @json($products);
    let rowCount = 0;

    function addRow() {
        const tbody = document.getElementById('itemsTableBody');
        const rowId = rowCount++;

        const tr = document.createElement('tr');
        tr.id = `row-${rowId}`;
        tr.className = 'hover:bg-slate-50/80 transition';

        tr.innerHTML = `
            <td class="py-3 px-3">
                <select name="items[${rowId}][product_id]" required onchange="onProductSelect(${rowId}, this.value)"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:ring-2 focus:ring-blue-600 focus:bg-white">
                    <option value="">-- Pilih Barang --</option>
                    ${availableProducts.map(p => `
                        <option value="${p.id}" data-cost="${p.purchase_price}" data-unit="${p.unit}">
                            ${p.name} (${p.sku}) [Stok saat ini: ${parseInt(p.stock)} ${p.unit}]
                        </option>
                    `).join('')}
                </select>
            </td>
            <td class="py-3 px-3">
                <div class="flex items-center space-x-1">
                    <input type="number" name="items[${rowId}][qty]" required min="1" step="1" value="10"
                        oninput="calculateRow(${rowId})" id="qty-${rowId}"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono font-bold focus:ring-2 focus:ring-blue-600 focus:bg-white text-center">
                    <span id="unit-${rowId}" class="text-[10px] text-slate-400 font-mono">pcs</span>
                </div>
            </td>
            <td class="py-3 px-3 text-right">
                <input type="number" name="items[${rowId}][unit_cost]" required min="0" step="1" value="0"
                    oninput="calculateRow(${rowId})" id="cost-${rowId}"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono text-right focus:ring-2 focus:ring-blue-600 focus:bg-white">
            </td>
            <td class="py-3 px-3 text-right font-mono font-bold text-slate-800" id="subtotal-${rowId}">
                Rp 0
            </td>
            <td class="py-3 px-3 text-center">
                <button type="button" onclick="removeRow(${rowId})" class="text-slate-400 hover:text-rose-500 p-1 transition" title="Hapus Baris">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);

        // If products exist, default select the first one
        if (availableProducts.length > rowId) {
            const selectEl = tr.querySelector('select');
            selectEl.value = availableProducts[rowId].id;
            onProductSelect(rowId, availableProducts[rowId].id);
        }
    }

    function onProductSelect(rowId, productId) {
        const prod = availableProducts.find(p => p.id == productId);
        if (prod) {
            document.getElementById(`cost-${rowId}`).value = prod.purchase_price;
            document.getElementById(`unit-${rowId}`).textContent = prod.unit;
            calculateRow(rowId);
        }
    }

    function calculateRow(rowId) {
        const qty = parseFloat(document.getElementById(`qty-${rowId}`).value) || 0;
        const cost = parseFloat(document.getElementById(`cost-${rowId}`).value) || 0;
        const subtotal = qty * cost;

        document.getElementById(`subtotal-${rowId}`).textContent = formatRupiah(subtotal);
        calculateGrandTotal();
    }

    function removeRow(rowId) {
        const rows = document.querySelectorAll('#itemsTableBody tr');
        if (rows.length <= 1) {
            alert('Minimal harus ada 1 barang dalam dokumen penerimaan.');
            return;
        }
        document.getElementById(`row-${rowId}`).remove();
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let grandTotal = 0;
        const rows = document.querySelectorAll('#itemsTableBody tr');
        rows.forEach(r => {
            const id = r.id.replace('row-', '');
            const qty = parseFloat(document.getElementById(`qty-${id}`)?.value) || 0;
            const cost = parseFloat(document.getElementById(`cost-${id}`)?.value) || 0;
            grandTotal += (qty * cost);
        });

        document.getElementById('grandTotalDisplay').textContent = formatRupiah(grandTotal);
    }

    // Initialize with 2 rows on load
    addRow();
    addRow();
</script>
@endpush
@endsection
