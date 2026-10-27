<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran #{{ $sale->invoice_number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.4;
            width: 280px;
            margin: 20px auto;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .flex { display: flex; justify-content: space-between; }
        .border-t { border-top: 1px dashed #000; margin-top: 6px; padding-top: 6px; }
        .border-b { border-bottom: 1px dashed #000; margin-bottom: 6px; padding-bottom: 6px; }
        .no-print { margin-bottom: 15px; text-align: center; }
        @media print {
            .no-print { display: none; }
            body { margin: 0; width: 100%; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" style="padding: 6px 16px; cursor: pointer; font-weight: bold;">Cetak Struk</button>
        <button onclick="window.close()" style="padding: 6px 16px; cursor: pointer;">Tutup</button>
    </div>

    <div class="text-center font-bold" style="font-size: 14px;">INDOMARET POS</div>
    <div class="text-center" style="font-size: 10px;">PT INDOMARCO PRISMATAMA<br>JL. JEND. SUDIRMAN NO. 45<br>TELP: 021-5551234</div>

    <div class="border-t border-b" style="font-size: 11px;">
        <div class="flex"><span>Faktur :</span><span>{{ $sale->invoice_number }}</span></div>
        <div class="flex"><span>Tanggal:</span><span>{{ $sale->sold_at->format('d/m/Y H:i') }}</span></div>
        <div class="flex"><span>Kasir  :</span><span>{{ $sale->user->name ?? 'Kasir' }}</span></div>
    </div>

    <div>
        @foreach ($sale->items as $item)
            <div style="margin-bottom: 4px;">
                <div class="font-bold">{{ $item->product->name ?? 'Produk' }}</div>
                <div class="flex" style="font-size: 11px;">
                    <span>{{ (int)$item->qty }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                    <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="border-t" style="font-size: 11px;">
        <div class="flex"><span>Subtotal:</span><span>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span></div>
        @if ($sale->discount > 0)
            <div class="flex"><span>Diskon:</span><span>- Rp {{ number_format($sale->discount, 0, ',', '.') }}</span></div>
        @endif
        <div class="flex font-bold" style="font-size: 13px; margin: 4px 0;">
            <span>TOTAL:</span><span>Rp {{ number_format($sale->total, 0, ',', '.') }}</span>
        </div>
        @php $payment = $sale->payment; @endphp
        <div class="flex"><span>Metode Bayar:</span><span>{{ strtoupper($payment->method ?? 'TUNAI') }}</span></div>
        <div class="flex"><span>Diterima:</span><span>Rp {{ number_format($payment->amount_received ?? $sale->total, 0, ',', '.') }}</span></div>
        <div class="flex font-bold"><span>Kembalian:</span><span>Rp {{ number_format($payment->change_amount ?? 0, 0, ',', '.') }}</span></div>
    </div>

    <div class="border-t text-center" style="font-size: 10px; margin-top: 10px;">
        <div>TERIMA KASIH ATAS KUNJUNGAN ANDA</div>
        <div>LAYANAN KONSUMEN: 1500-280</div>
        <div style="margin-top: 8px; letter-spacing: 4px;">||| | ||||| |||| ||</div>
    </div>

    <script>
        window.onload = function() {
            // Auto open print dialog if opened in popup
            if (window.opener) {
                window.print();
            }
        };
    </script>
</body>
</html>
