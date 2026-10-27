@extends('layouts.base')

@section('title', 'Kasir POS Desktop')

@section('content')
<div class="h-screen flex flex-col bg-slate-900 text-slate-100 overflow-hidden select-none">
    
    <!-- Top Bar: Cashier Shift & Device Status -->
    <header class="bg-slate-950 border-b border-slate-800 px-6 py-3 flex items-center justify-between shadow-md shrink-0">
        <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-1.5 font-black text-xl tracking-wider">
                <span class="text-blue-500">INDO</span><span class="text-red-500">POS</span>
                <span class="text-[10px] font-bold px-2 py-0.5 bg-blue-600/30 text-blue-400 rounded border border-blue-500/30 ml-2">DESKTOP POS</span>
            </div>
            <div class="h-5 w-px bg-slate-800"></div>
            <div class="flex items-center space-x-3 text-xs text-slate-400">
                <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span> Scanner: Siap (HID)</span>
                <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5"></span> Printer: Online</span>
                <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-blue-500 mr-1.5"></span> DB: Sinkron</span>
            </div>
        </div>

        <!-- Shift & User Stats -->
        <div class="flex items-center space-x-6">
            <div class="flex items-center space-x-4 text-xs">
                <div class="bg-slate-900 px-3 py-1.5 rounded-lg border border-slate-800">
                    <span class="text-slate-400">Transaksi Shift Ini:</span>
                    <strong class="text-white ml-1 font-mono text-sm" id="shiftCount">{{ $todayStats['count'] }}</strong>
                </div>
                <div class="bg-slate-900 px-3 py-1.5 rounded-lg border border-slate-800">
                    <span class="text-slate-400">Total Penjualan:</span>
                    <strong class="text-emerald-400 ml-1 font-mono text-sm" id="shiftTotal">Rp {{ number_format($todayStats['total'], 0, ',', '.') }}</strong>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <div class="text-right">
                    <div class="text-xs font-bold text-white">{{ Auth::user()->name }}</div>
                    <div class="text-[10px] text-slate-400">Kasir Shift Pagi</div>
                </div>
                <button type="button" onclick="openReprintModal()" title="Cetak Ulang Struk Terakhir [F8]" class="p-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-xs border border-slate-700 transition flex items-center space-x-1">
                    <i class="fa-solid fa-receipt"></i>
                    <span>Struk [F8]</span>
                </button>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="p-2 bg-rose-950/60 hover:bg-rose-900 text-rose-300 rounded-lg text-xs border border-rose-800/40 transition" title="Logout Kasir">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Shortcut Ribbon -->
    <div class="bg-slate-950/60 border-b border-slate-800/60 px-6 py-1.5 flex items-center justify-between text-[11px] text-slate-400 font-mono shrink-0">
        <div class="flex items-center space-x-4">
            <span><kbd class="px-1.5 py-0.5 bg-slate-800 rounded border border-slate-700 text-slate-200">F2</kbd> Scan / Cari</span>
            <span><kbd class="px-1.5 py-0.5 bg-slate-800 rounded border border-slate-700 text-slate-200">F4</kbd> Bayar</span>
            <span><kbd class="px-1.5 py-0.5 bg-slate-800 rounded border border-slate-700 text-slate-200">F8</kbd> Riwayat Struk</span>
            <span><kbd class="px-1.5 py-0.5 bg-slate-800 rounded border border-slate-700 text-slate-200">F9</kbd> Bersihkan Keranjang</span>
            <span><kbd class="px-1.5 py-0.5 bg-slate-800 rounded border border-slate-700 text-slate-200">Esc</kbd> Batal</span>
        </div>
        <div class="text-slate-500">
            <span id="liveClock"></span>
        </div>
    </div>

    <!-- Main Workspace (Split View) -->
    <div class="flex-1 flex overflow-hidden">
        
        <!-- Left Panel: Product Catalogue & Barcode Scanner -->
        <div class="w-7/12 flex flex-col border-r border-slate-800 bg-slate-900/60 overflow-hidden">
            <!-- Search & Scan Header -->
            <div class="p-4 border-b border-slate-800 space-y-3 bg-slate-950/30">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-blue-400">
                        <i class="fa-solid fa-barcode text-lg"></i>
                    </div>
                    <input type="text" id="barcodeInput" autofocus
                        placeholder="Scan Barcode EAN-13 atau ketik SKU / Nama Produk (Tekan Enter)..."
                        class="w-full pl-11 pr-24 py-3 bg-slate-800 border-2 border-blue-500/50 rounded-xl text-white placeholder-slate-400 text-sm focus:outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/20 font-mono transition">
                    <button type="button" onclick="handleScanSubmit()"
                        class="absolute right-2 top-2 bottom-2 px-4 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-lg text-xs transition flex items-center space-x-1">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span>Cari</span>
                    </button>
                </div>

                <!-- Category Filter Chips -->
                <div class="flex items-center space-x-2 overflow-x-auto pb-1 text-xs">
                    <button type="button" onclick="filterCategory(null)" class="category-chip px-3 py-1.5 rounded-lg bg-blue-600 text-white font-medium border border-blue-500 shrink-0">
                        Semua Kategori
                    </button>
                    @foreach ($categories as $cat)
                        <button type="button" onclick="filterCategory({{ $cat->id }})" class="category-chip px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white border border-slate-700 shrink-0 transition">
                            {{ $cat->name }} ({{ $cat->products_count }})
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Product Quick Grid -->
            <div class="flex-1 overflow-y-auto p-4">
                <div id="productGrid" class="grid grid-cols-3 xl:grid-cols-4 gap-3">
                    @foreach ($quickProducts as $product)
                        <div onclick="addToCartById({{ $product->id }})"
                            class="product-card bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 hover:border-blue-500/60 rounded-xl p-3 cursor-pointer transition transform active:scale-95 flex flex-col justify-between group shadow-sm">
                            <div>
                                <div class="flex items-center justify-between text-[11px] text-slate-400 mb-1">
                                    <span class="font-mono text-blue-400">{{ $product->sku }}</span>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] {{ $product->stock <= $product->minimum_stock ? 'bg-amber-900/50 text-amber-300 border border-amber-800' : 'bg-slate-700 text-slate-300' }}">
                                        Stok: {{ (int)$product->stock }}
                                    </span>
                                </div>
                                <h3 class="text-sm font-semibold text-white line-clamp-2 leading-tight group-hover:text-blue-300 transition">
                                    {{ $product->name }}
                                </h3>
                            </div>
                            <div class="mt-3 pt-2 border-t border-slate-700/50 flex items-center justify-between">
                                <span class="font-bold text-emerald-400 text-sm font-mono">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</span>
                                <button type="button" onclick="event.stopPropagation(); addToCartById({{ $product->id }})"
                                    class="w-7 h-7 rounded-full bg-blue-600/30 text-blue-400 flex items-center justify-center text-xs hover:bg-blue-600 hover:text-white transition" title="Tambah ke Keranjang">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Panel: Cart & Checkout Payment -->
        <div class="w-5/12 flex flex-col bg-slate-950 overflow-hidden">
            
            <!-- Cart Header -->
            <div class="p-4 border-b border-slate-800 flex items-center justify-between bg-slate-900/40">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-cart-shopping text-blue-400"></i>
                    <h2 class="font-bold text-white text-base">Keranjang Belanja</h2>
                    <span id="cartBadge" class="px-2 py-0.5 bg-blue-600 text-white rounded-full text-xs font-bold">0 item</span>
                </div>
                <button type="button" onclick="clearCart()" class="text-xs text-rose-400 hover:text-rose-300 hover:underline flex items-center space-x-1">
                    <i class="fa-solid fa-trash-can"></i>
                    <span>Kosongkan [F9]</span>
                </button>
            </div>

            <!-- Cart Table Items -->
            <div class="flex-1 overflow-y-auto p-4 space-y-2" id="cartContainer">
                <div id="emptyCartMessage" class="h-full flex flex-col items-center justify-center text-slate-500 py-12">
                    <i class="fa-solid fa-basket-shopping text-5xl mb-3 text-slate-600"></i>
                    <p class="font-medium text-sm">Keranjang masih kosong</p>
                    <p class="text-xs text-slate-600 mt-1">Scan barcode atau pilih barang di katalog kiri</p>
                </div>
                <div id="cartItemList" class="space-y-2 hidden"></div>
            </div>

            <!-- Cart Total Summary & Checkout Button -->
            <div class="p-5 border-t border-slate-800 bg-slate-900/90 space-y-4">
                <div class="space-y-1.5 text-xs">
                    <div class="flex justify-between text-slate-400">
                        <span>Subtotal Barang:</span>
                        <span class="font-mono text-slate-200" id="cartSubtotal">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-slate-400">
                        <span>Potongan / Diskon:</span>
                        <span class="font-mono text-emerald-400" id="cartDiscount">- Rp 0</span>
                    </div>
                    <div class="pt-2 border-t border-slate-800 flex justify-between items-baseline">
                        <span class="text-sm font-bold text-white uppercase tracking-wider">Total Tagihan:</span>
                        <span class="text-2xl font-black text-emerald-400 font-mono" id="cartGrandTotal">Rp 0</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button type="button" onclick="clearCart()"
                        class="py-3 px-4 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-xl text-xs border border-slate-700 transition">
                        Batal [Esc]
                    </button>
                    <button type="button" onclick="openPaymentModal()" id="btnCheckout" disabled
                        class="py-3.5 px-4 bg-emerald-600 hover:bg-emerald-500 disabled:bg-slate-800 disabled:text-slate-600 disabled:cursor-not-allowed text-white font-bold rounded-xl text-sm shadow-lg shadow-emerald-600/30 transition transform active:scale-95 flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-wallet"></i>
                        <span>BAYAR [F4]</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL PEMBAYARAN ================= -->
<div id="paymentModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-xl w-full overflow-hidden shadow-2xl animate-scale-up">
        <div class="p-6 border-b border-slate-800 flex items-center justify-between bg-slate-950/60">
            <div>
                <h3 class="text-lg font-bold text-white flex items-center">
                    <i class="fa-solid fa-credit-card text-blue-500 mr-2.5"></i> Pembayaran Transaksi
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Pilih metode bayar dan masukkan nominal uang</p>
            </div>
            <button type="button" onclick="closePaymentModal()" class="text-slate-400 hover:text-white p-1 text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="p-6 space-y-6">
            <!-- Total Banner -->
            <div class="bg-slate-950 p-4 rounded-2xl border border-slate-800 flex items-center justify-between">
                <div>
                    <div class="text-xs uppercase font-bold tracking-wider text-slate-400">Total Yang Harus Dibayar</div>
                    <div class="text-3xl font-black text-emerald-400 font-mono mt-1" id="modalTotalDisplay">Rp 0</div>
                </div>
                <div class="text-right text-xs text-slate-500">
                    <span id="modalItemCount">0 item</span>
                </div>
            </div>

            <!-- Payment Method Tabs -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Metode Pembayaran</label>
                <div class="grid grid-cols-4 gap-2">
                    <button type="button" onclick="setPaymentMethod('cash')" id="btnMethodCash"
                        class="p-3 rounded-xl border border-blue-500 bg-blue-600/20 text-blue-400 text-xs font-bold flex flex-col items-center justify-center space-y-1 transition">
                        <i class="fa-solid fa-money-bill-wave text-base"></i>
                        <span>Tunai (Cash)</span>
                    </button>
                    <button type="button" onclick="setPaymentMethod('qris')" id="btnMethodQris"
                        class="p-3 rounded-xl border border-slate-800 bg-slate-950 text-slate-400 hover:text-white text-xs font-bold flex flex-col items-center justify-center space-y-1 transition">
                        <i class="fa-solid fa-qrcode text-base"></i>
                        <span>QRIS</span>
                    </button>
                    <button type="button" onclick="setPaymentMethod('card')" id="btnMethodCard"
                        class="p-3 rounded-xl border border-slate-800 bg-slate-950 text-slate-400 hover:text-white text-xs font-bold flex flex-col items-center justify-center space-y-1 transition">
                        <i class="fa-solid fa-credit-card text-base"></i>
                        <span>Debit / EDC</span>
                    </button>
                    <button type="button" onclick="setPaymentMethod('transfer')" id="btnMethodTransfer"
                        class="p-3 rounded-xl border border-slate-800 bg-slate-950 text-slate-400 hover:text-white text-xs font-bold flex flex-col items-center justify-center space-y-1 transition">
                        <i class="fa-solid fa-building-columns text-base"></i>
                        <span>Transfer</span>
                    </button>
                </div>
            </div>

            <!-- Cash Input Section -->
            <div id="cashInputSection" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Uang Tunai Diterima (Rp)</label>
                    <input type="number" id="cashReceivedInput"
                        placeholder="Contoh: 100000"
                        oninput="calculateChange()"
                        class="w-full px-4 py-3 bg-slate-950 border-2 border-slate-700 rounded-xl text-white text-xl font-mono font-bold focus:outline-none focus:border-blue-500">
                </div>

                <!-- Quick Cash Presets -->
                <div>
                    <div class="text-[11px] font-semibold text-slate-400 mb-1.5">Preset Cepat:</div>
                    <div class="grid grid-cols-4 gap-2">
                        <button type="button" onclick="setPresetCash('exact')" class="py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-xs font-semibold border border-slate-700">Uang Pas</button>
                        <button type="button" onclick="setPresetCash(50000)" class="py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-xs font-semibold border border-slate-700">Rp 50.000</button>
                        <button type="button" onclick="setPresetCash(100000)" class="py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-xs font-semibold border border-slate-700">Rp 100.000</button>
                        <button type="button" onclick="setPresetCash(200000)" class="py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-xs font-semibold border border-slate-700">Rp 200.000</button>
                    </div>
                </div>

                <!-- Change Display -->
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Kembalian:</span>
                    <span class="text-2xl font-black text-emerald-400 font-mono" id="cashChangeDisplay">Rp 0</span>
                </div>
            </div>

            <!-- Non-Cash Info Section (Hidden by default) -->
            <div id="nonCashSection" class="hidden text-center p-6 bg-slate-950 rounded-2xl border border-slate-800 space-y-3">
                <div class="w-32 h-32 mx-auto bg-white p-2 rounded-xl flex items-center justify-center">
                    <!-- Dynamic mock QRIS -->
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=INDOMARET-POS-PAYMENT" alt="QRIS Code" class="w-full h-full object-contain">
                </div>
                <div class="text-xs text-slate-400">Silakan scan kode QRIS atau gesek kartu pada mesin EDC.</div>
            </div>
        </div>

        <div class="p-6 border-t border-slate-800 bg-slate-950/80 flex items-center justify-end space-x-3">
            <button type="button" onclick="closePaymentModal()" class="px-5 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">
                Batal
            </button>
            <button type="button" onclick="submitTransaction()" id="btnSubmitTx"
                class="px-6 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold shadow-lg shadow-emerald-600/30 transition flex items-center space-x-2">
                <i class="fa-solid fa-check"></i>
                <span>Selesaikan Transaksi (Enter)</span>
            </button>
        </div>
    </div>
</div>

<!-- ================= MODAL STRUK CETAK ================= -->
<div id="receiptModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white text-slate-900 rounded-3xl max-w-sm w-full overflow-hidden shadow-2xl animate-scale-up font-mono">
        <div class="p-4 bg-slate-100 border-b flex justify-between items-center text-xs font-sans">
            <span class="font-bold text-slate-700">Preview Struk Thermal</span>
            <button onclick="closeReceiptModal()" class="text-slate-500 hover:text-slate-800"><i class="fa-solid fa-xmark text-base"></i></button>
        </div>

        <!-- Thermal Receipt Body (58mm/80mm style) -->
        <div class="p-6 text-xs text-slate-800 space-y-3" id="printableReceipt">
            <div class="text-center space-y-1">
                <div class="text-base font-bold tracking-widest">INDOMARET POS</div>
                <div class="text-[10px] text-slate-600 leading-tight">PT INDOMARCO PRISMATAMA<br>JL. JEND. SUDIRMAN NO. 45<br>TELP: 021-5551234</div>
            </div>

            <div class="border-t border-b border-dashed border-slate-400 py-1.5 text-[11px] space-y-0.5">
                <div class="flex justify-between"><span>No. Faktur:</span><strong id="rcptInvoice">-</strong></div>
                <div class="flex justify-between"><span>Tanggal:</span><span id="rcptDate">-</span></div>
                <div class="flex justify-between"><span>Kasir:</span><span id="rcptCashier">-</span></div>
            </div>

            <!-- Items -->
            <div class="space-y-1.5 text-[11px]" id="rcptItemList">
                <!-- Dynamically populated -->
            </div>

            <div class="border-t border-dashed border-slate-400 pt-2 space-y-1 text-[11px]">
                <div class="flex justify-between"><span>Subtotal:</span><span id="rcptSubtotal">Rp 0</span></div>
                <div class="flex justify-between"><span>Diskon:</span><span id="rcptDiscount">Rp 0</span></div>
                <div class="flex justify-between font-bold text-sm"><span>TOTAL:</span><span id="rcptTotal">Rp 0</span></div>
                <div class="flex justify-between"><span>Metode:</span><span id="rcptMethod">TUNAI</span></div>
                <div class="flex justify-between"><span>Uang Diterima:</span><span id="rcptReceived">Rp 0</span></div>
                <div class="flex justify-between font-bold"><span>Kembalian:</span><span id="rcptChange">Rp 0</span></div>
            </div>

            <div class="text-center pt-3 border-t border-dashed border-slate-400 text-[10px] space-y-1">
                <div>TERIMA KASIH ATAS KUNJUNGAN ANDA</div>
                <div class="text-[9px] text-slate-500">BARANG YANG SUDAH DIBELI DAPAT DITUKAR MAKS. 1X24 JAM BESERTA STRUK</div>
                <div class="font-mono text-center tracking-widest pt-2">||| | ||||| |||| || ||||| |||</div>
            </div>
        </div>

        <div class="p-4 bg-slate-100 border-t flex space-x-2 font-sans">
            <button type="button" onclick="printReceipt()" class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center space-x-1.5">
                <i class="fa-solid fa-print"></i>
                <span>Cetak Struk</span>
            </button>
            <button type="button" onclick="closeReceiptModal()" class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold transition">
                Transaksi Baru
            </button>
        </div>
    </div>
</div>

<!-- ================= MODAL CETAK ULANG STRUK ================= -->
<div id="reprintModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-xl w-full overflow-hidden shadow-2xl animate-scale-up">
        <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/60">
            <div>
                <h3 class="text-base font-bold text-white flex items-center">
                    <i class="fa-solid fa-clock-rotate-left text-blue-500 mr-2"></i> Riwayat Transaksi Shift Ini
                </h3>
                <p class="text-xs text-slate-400">Pilih faktur untuk cetak ulang tanpa memotong stok</p>
            </div>
            <button type="button" onclick="closeReprintModal()" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
        </div>
        <div class="p-4 max-h-96 overflow-y-auto space-y-2" id="reprintListContainer">
            <div class="text-center py-8 text-xs text-slate-500">Memuat riwayat transaksi...</div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Global Catalog Map to avoid HTML attribute quoting issues
    const productsCatalog = new Map();

    // Populate initial products
    const initialQuickProducts = @json($quickProducts);
    initialQuickProducts.forEach(p => productsCatalog.set(Number(p.id), p));

    // State Keranjang Belanja
    let cart = [];
    let activePaymentMethod = 'cash';
    let currentTotal = 0;

    // Helper: Add to cart by ID
    function addToCartById(productId) {
        const id = Number(productId);
        const product = productsCatalog.get(id);
        if (!product) {
            console.error('Produk tidak ditemukan di katalog:', id);
            return;
        }
        addToCart(product);
    }

    // Live Clock
    function updateClock() {
        const now = new Date();
        document.getElementById('liveClock').textContent = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric' }) + ' ' + now.toLocaleTimeString('id-ID');
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Hotkey handler (F2, F4, F8, F9, Esc)
    window.addEventListener('keydown', function(e) {
        if (e.key === 'F2') {
            e.preventDefault();
            document.getElementById('barcodeInput').focus();
            document.getElementById('barcodeInput').select();
        } else if (e.key === 'F4') {
            e.preventDefault();
            if (cart.length > 0) openPaymentModal();
        } else if (e.key === 'F8') {
            e.preventDefault();
            openReprintModal();
        } else if (e.key === 'F9') {
            e.preventDefault();
            clearCart();
        } else if (e.key === 'Escape') {
            closePaymentModal();
            closeReceiptModal();
            closeReprintModal();
        } else if (e.key === 'Enter' && !document.getElementById('paymentModal').classList.contains('hidden')) {
            submitTransaction();
        }
    });

    // Barcode input Enter listener
    document.getElementById('barcodeInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleScanSubmit();
        }
    });

    // Handle Barcode Scan or Text Search
    async function handleScanSubmit() {
        const input = document.getElementById('barcodeInput');
        const query = input.value.trim();
        if (!query) return;

        try {
            const res = await fetch(`{{ route('cashier.products.search') }}?q=${encodeURIComponent(query)}`);
            const data = await res.json();

            if (data.exact && data.product) {
                // Exact Barcode / SKU found -> register and directly add to cart
                productsCatalog.set(Number(data.product.id), data.product);
                addToCart(data.product);
                input.value = '';
                input.focus();
            } else if (data.products && data.products.length > 0) {
                // Render search results in the grid
                renderProductGrid(data.products);
                if (data.products.length === 1) {
                    addToCart(data.products[0]);
                    input.value = '';
                    input.focus();
                }
            } else {
                alert(`Barang dengan barcode/kode "${query}" tidak ditemukan atau nonaktif.`);
                input.select();
            }
        } catch (err) {
            console.error(err);
            alert('Gagal mencari barang: ' + err.message);
        }
    }

    // Filter Category Chips
    async function filterCategory(categoryId) {
        document.querySelectorAll('.category-chip').forEach(btn => {
            btn.classList.remove('bg-blue-600', 'text-white', 'border-blue-500');
            btn.classList.add('bg-slate-800', 'text-slate-300', 'border-slate-700');
        });
        event.target.classList.add('bg-blue-600', 'text-white', 'border-blue-500');
        event.target.classList.remove('bg-slate-800', 'text-slate-300', 'border-slate-700');

        try {
            const url = categoryId ? `{{ route('cashier.products.search') }}?category_id=${categoryId}` : `{{ route('cashier.products.search') }}`;
            const res = await fetch(url);
            const data = await res.json();
            renderProductGrid(data.products);
        } catch (err) {
            console.error(err);
        }
    }

    // Render products in left grid
    function renderProductGrid(products) {
        const grid = document.getElementById('productGrid');
        if (!products || products.length === 0) {
            grid.innerHTML = '<div class="col-span-full text-center py-8 text-slate-500 text-xs">Tidak ada produk ditemukan</div>';
            return;
        }

        // Register each in catalog map
        products.forEach(p => productsCatalog.set(Number(p.id), p));

        grid.innerHTML = products.map(p => `
            <div onclick="addToCartById(${p.id})"
                class="product-card bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 hover:border-blue-500/60 rounded-xl p-3 cursor-pointer transition transform active:scale-95 flex flex-col justify-between group shadow-sm">
                <div>
                    <div class="flex items-center justify-between text-[11px] text-slate-400 mb-1">
                        <span class="font-mono text-blue-400">${p.sku}</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] ${parseFloat(p.stock) <= parseFloat(p.minimum_stock) ? 'bg-amber-900/50 text-amber-300 border border-amber-800' : 'bg-slate-700 text-slate-300'}">
                            Stok: ${parseInt(p.stock)}
                        </span>
                    </div>
                    <h3 class="text-sm font-semibold text-white line-clamp-2 leading-tight group-hover:text-blue-300 transition">
                        ${p.name}
                    </h3>
                </div>
                <div class="mt-3 pt-2 border-t border-slate-700/50 flex items-center justify-between">
                    <span class="font-bold text-emerald-400 text-sm font-mono">${formatRupiah(p.selling_price)}</span>
                    <button type="button" onclick="event.stopPropagation(); addToCartById(${p.id})"
                        class="w-7 h-7 rounded-full bg-blue-600/30 text-blue-400 flex items-center justify-center text-xs hover:bg-blue-600 hover:text-white transition" title="Tambah ke Keranjang">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }

    // Add product to Cart
    function addToCart(product) {
        if (!product) return;
        const stock = parseFloat(product.stock);
        if (stock <= 0) {
            alert(`Stok produk "${product.name}" habis.`);
            return;
        }

        const productId = Number(product.id);
        const existing = cart.find(item => Number(item.product_id) === productId);
        if (existing) {
            if (existing.qty + 1 > stock) {
                alert(`Tidak dapat menambah lagi. Stok ${product.name} tersisa ${stock} ${product.unit}.`);
                return;
            }
            existing.qty += 1;
            existing.subtotal = existing.qty * existing.unit_price;
        } else {
            cart.push({
                product_id: productId,
                name: product.name,
                sku: product.sku,
                unit: product.unit,
                unit_price: parseFloat(product.selling_price),
                stock: stock,
                qty: 1,
                subtotal: parseFloat(product.selling_price)
            });
        }

        renderCart();
    }

    // Update Cart Qty
    function updateQty(productId, change) {
        const item = cart.find(i => i.product_id === productId);
        if (!item) return;

        const newQty = item.qty + change;
        if (newQty <= 0) {
            removeFromCart(productId);
            return;
        }

        if (newQty > item.stock) {
            alert(`Maksimal stok tercapai (${item.stock} ${item.unit}).`);
            return;
        }

        item.qty = newQty;
        item.subtotal = item.qty * item.unit_price;
        renderCart();
    }

    // Set custom Qty
    function setItemQty(productId, val) {
        const item = cart.find(i => i.product_id === productId);
        if (!item) return;

        let num = parseFloat(val);
        if (isNaN(num) || num <= 0) num = 1;
        if (num > item.stock) {
            alert(`Stok hanya ada ${item.stock}`);
            num = item.stock;
        }

        item.qty = num;
        item.subtotal = item.qty * item.unit_price;
        renderCart();
    }

    // Remove from cart
    function removeFromCart(productId) {
        cart = cart.filter(i => i.product_id !== productId);
        renderCart();
    }

    // Clear cart
    function clearCart() {
        if (cart.length === 0) return;
        if (confirm('Kosongkan keranjang belanja?')) {
            cart = [];
            renderCart();
        }
    }

    // Render Cart HTML
    function renderCart() {
        const emptyMsg = document.getElementById('emptyCartMessage');
        const list = document.getElementById('cartItemList');
        const badge = document.getElementById('cartBadge');
        const subtotalEl = document.getElementById('cartSubtotal');
        const grandTotalEl = document.getElementById('cartGrandTotal');
        const btnCheckout = document.getElementById('btnCheckout');

        if (cart.length === 0) {
            emptyMsg.classList.remove('hidden');
            list.classList.add('hidden');
            badge.textContent = '0 item';
            subtotalEl.textContent = 'Rp 0';
            grandTotalEl.textContent = 'Rp 0';
            btnCheckout.disabled = true;
            currentTotal = 0;
            return;
        }

        emptyMsg.classList.add('hidden');
        list.classList.remove('hidden');

        let total = 0;
        let totalCount = 0;

        list.innerHTML = cart.map(item => {
            total += item.subtotal;
            totalCount += item.qty;

            return `
                <div class="p-3 bg-slate-900 border border-slate-800 rounded-xl flex items-center justify-between text-xs transition">
                    <div class="flex-1 pr-3">
                        <div class="font-bold text-white line-clamp-1">${item.name}</div>
                        <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                            ${formatRupiah(item.unit_price)} / ${item.unit}
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center bg-slate-950 rounded-lg border border-slate-800">
                            <button onclick="updateQty(${item.product_id}, -1)" class="w-7 h-7 flex items-center justify-center text-slate-300 hover:text-white hover:bg-slate-800 rounded-l-lg transition">
                                <i class="fa-solid fa-minus text-[10px]"></i>
                            </button>
                            <input type="text" value="${item.qty}" onchange="setItemQty(${item.product_id}, this.value)"
                                class="w-10 text-center font-bold font-mono bg-transparent text-white focus:outline-none">
                            <button onclick="updateQty(${item.product_id}, 1)" class="w-7 h-7 flex items-center justify-center text-slate-300 hover:text-white hover:bg-slate-800 rounded-r-lg transition">
                                <i class="fa-solid fa-plus text-[10px]"></i>
                            </button>
                        </div>
                        <div class="w-24 text-right font-mono font-bold text-emerald-400">
                            ${formatRupiah(item.subtotal)}
                        </div>
                        <button onclick="removeFromCart(${item.product_id})" class="text-slate-500 hover:text-rose-400 p-1 transition" title="Hapus">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>
                </div>
            `;
        }).join('');

        currentTotal = total;
        badge.textContent = `${totalCount} item`;
        subtotalEl.textContent = formatRupiah(total);
        grandTotalEl.textContent = formatRupiah(total);
        btnCheckout.disabled = false;
    }

    // Modal Payment functions
    function openPaymentModal() {
        if (cart.length === 0) return;
        document.getElementById('modalTotalDisplay').textContent = formatRupiah(currentTotal);
        document.getElementById('modalItemCount').textContent = `${cart.length} jenis barang`;
        document.getElementById('cashReceivedInput').value = '';
        document.getElementById('cashChangeDisplay').textContent = 'Rp 0';
        setPaymentMethod('cash');

        document.getElementById('paymentModal').classList.remove('hidden');
        setTimeout(() => document.getElementById('cashReceivedInput').focus(), 100);
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
        document.getElementById('barcodeInput').focus();
    }

    function setPaymentMethod(method) {
        activePaymentMethod = method;
        const methods = ['cash', 'qris', 'card', 'transfer'];
        methods.forEach(m => {
            const btn = document.getElementById(`btnMethod${m.charAt(0).toUpperCase() + m.slice(1)}`);
            if (m === method) {
                btn.className = 'p-3 rounded-xl border border-blue-500 bg-blue-600/20 text-blue-400 text-xs font-bold flex flex-col items-center justify-center space-y-1 transition';
            } else {
                btn.className = 'p-3 rounded-xl border border-slate-800 bg-slate-950 text-slate-400 hover:text-white text-xs font-bold flex flex-col items-center justify-center space-y-1 transition';
            }
        });

        if (method === 'cash') {
            document.getElementById('cashInputSection').classList.remove('hidden');
            document.getElementById('nonCashSection').classList.add('hidden');
            document.getElementById('cashReceivedInput').focus();
        } else {
            document.getElementById('cashInputSection').classList.add('hidden');
            document.getElementById('nonCashSection').classList.remove('hidden');
            document.getElementById('cashReceivedInput').value = currentTotal;
        }
    }

    function setPresetCash(val) {
        const input = document.getElementById('cashReceivedInput');
        if (val === 'exact') {
            input.value = currentTotal;
        } else {
            input.value = val;
        }
        calculateChange();
    }

    function calculateChange() {
        const input = document.getElementById('cashReceivedInput');
        const changeEl = document.getElementById('cashChangeDisplay');
        const received = parseFloat(input.value) || 0;
        const diff = received - currentTotal;

        if (diff < 0) {
            changeEl.textContent = `Kurang Rp ${Math.abs(diff).toLocaleString('id-ID')}`;
            changeEl.className = 'text-2xl font-black text-rose-500 font-mono';
        } else {
            changeEl.textContent = formatRupiah(diff);
            changeEl.className = 'text-2xl font-black text-emerald-400 font-mono';
        }
    }

    // Submit Checkout Transaction to backend
    async function submitTransaction() {
        const btn = document.getElementById('btnSubmitTx');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Menyimpan...';

        const receivedVal = parseFloat(document.getElementById('cashReceivedInput').value) || (activePaymentMethod === 'cash' ? 0 : currentTotal);

        const payload = {
            items: cart.map(i => ({ product_id: i.product_id, qty: i.qty })),
            method: activePaymentMethod,
            amount_received: receivedVal,
            discount: 0
        };

        try {
            const res = await fetch(`{{ route('cashier.checkout') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (!res.ok || !data.success) {
                alert(data.message || 'Terjadi kesalahan saat memproses transaksi.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-check"></i> <span>Selesaikan Transaksi (Enter)</span>';
                return;
            }

            // Success!
            closePaymentModal();
            showReceiptModal(data.sale);

            // Update shift summary
            const shiftCountEl = document.getElementById('shiftCount');
            const shiftTotalEl = document.getElementById('shiftTotal');
            shiftCountEl.textContent = parseInt(shiftCountEl.textContent) + 1;

            // Reset cart
            cart = [];
            renderCart();

        } catch (err) {
            console.error(err);
            alert('Kesalahan jaringan: ' + err.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> <span>Selesaikan Transaksi (Enter)</span>';
        }
    }

    let currentReceiptInvoice = '';

    // Show thermal receipt preview
    function showReceiptModal(sale) {
        currentReceiptInvoice = sale.invoice_number;
        document.getElementById('rcptInvoice').textContent = sale.invoice_number;
        document.getElementById('rcptDate').textContent = new Date(sale.sold_at).toLocaleString('id-ID');
        document.getElementById('rcptCashier').textContent = sale.user ? sale.user.name : '{{ Auth::user()->name }}';
        
        const pmt = (sale.payments && sale.payments[0]) ? sale.payments[0] : (sale.payment || {});
        document.getElementById('rcptSubtotal').textContent = formatRupiah(sale.subtotal);
        document.getElementById('rcptDiscount').textContent = formatRupiah(sale.discount || 0);
        document.getElementById('rcptTotal').textContent = formatRupiah(sale.total);
        document.getElementById('rcptMethod').textContent = (pmt.method || 'CASH').toUpperCase();
        document.getElementById('rcptReceived').textContent = formatRupiah(pmt.amount_received || sale.total);
        document.getElementById('rcptChange').textContent = formatRupiah(pmt.change_amount || 0);

        // Populate items
        const listEl = document.getElementById('rcptItemList');
        listEl.innerHTML = sale.items.map(it => `
            <div>
                <div class="font-bold">${it.product ? it.product.name : 'Produk'}</div>
                <div class="flex justify-between text-slate-600">
                    <span>${it.qty} x ${formatRupiah(it.unit_price)}</span>
                    <span class="font-bold text-slate-900">${formatRupiah(it.subtotal)}</span>
                </div>
            </div>
        `).join('');

        document.getElementById('receiptModal').classList.remove('hidden');
    }

    function closeReceiptModal() {
        document.getElementById('receiptModal').classList.add('hidden');
        document.getElementById('barcodeInput').focus();
    }

    function printReceipt() {
        if (!currentReceiptInvoice) return;
        const printUrl = `{{ url('cashier/receipt') }}/${encodeURIComponent(currentReceiptInvoice)}`;
        const win = window.open(printUrl, '_blank', 'width=380,height=600');
        if (win) {
            win.focus();
        }
    }

    // Modal Reprint
    async function openReprintModal() {
        const container = document.getElementById('reprintListContainer');
        container.innerHTML = '<div class="text-center py-6 text-xs text-slate-400"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Memuat daftar transaksi...</div>';
        document.getElementById('reprintModal').classList.remove('hidden');

        try {
            const res = await fetch(`{{ route('cashier.recent-sales') }}`);
            const sales = await res.json();

            if (!sales || sales.length === 0) {
                container.innerHTML = '<div class="text-center py-8 text-xs text-slate-500">Belum ada transaksi pada shift ini.</div>';
                return;
            }

            container.innerHTML = sales.map(s => `
                <div class="p-3 bg-slate-950 border border-slate-800 rounded-xl flex items-center justify-between text-xs">
                    <div>
                        <div class="font-bold text-white font-mono">${s.invoice_number}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">
                            ${new Date(s.sold_at).toLocaleTimeString('id-ID')} · ${s.items.length} item · 
                            <span class="text-emerald-400 font-mono font-bold">${formatRupiah(s.total)}</span>
                        </div>
                    </div>
                    <button onclick='reprintSale(${JSON.stringify(s)})' class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-semibold flex items-center space-x-1">
                        <i class="fa-solid fa-print"></i>
                        <span>Cetak</span>
                    </button>
                </div>
            `).join('');

        } catch (err) {
            console.error(err);
            container.innerHTML = '<div class="text-center py-6 text-xs text-rose-400">Gagal memuat transaksi</div>';
        }
    }

    function closeReprintModal() {
        document.getElementById('reprintModal').classList.add('hidden');
    }

    function reprintSale(sale) {
        closeReprintModal();
        showReceiptModal(sale);
    }
</script>
@endpush
@endsection
