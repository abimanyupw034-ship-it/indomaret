<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Users for 3 roles
        $kasir = User::create([
            'name' => 'Budi Santoso (Kasir)',
            'email' => 'kasir@pos.com',
            'password' => Hash::make('password'),
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $gudang = User::create([
            'name' => 'Agus Setiawan (Gudang)',
            'email' => 'gudang@pos.com',
            'password' => Hash::make('password'),
            'role' => 'gudang',
            'is_active' => true,
        ]);

        $admin = User::create([
            'name' => 'Dewi Lestari (Admin)',
            'email' => 'admin@pos.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // 2. Categories
        $categories = [
            'Makanan Ringan' => 'Aneka biskuit, keripik, cokelat, dan snack ringan',
            'Minuman Dingin & Susu' => 'Air mineral, teh kemasan, susu cair, jus buah, dan minuman isotonik',
            'Kebutuhan Pokok & Dapur' => 'Beras, minyak goreng, gula, tepung, dan bumbu masak',
            'Perawatan Tubuh & Mandi' => 'Sabun, pasta gigi, sampo, dan detergen',
            'Obat & Kesehatan' => 'Obat bebas, suplemen vitamin, plester, dan minyak angin',
        ];

        $categoryMap = [];
        foreach ($categories as $name => $desc) {
            $cat = Category::create([
                'name' => $name,
                'description' => $desc,
            ]);
            $categoryMap[$name] = $cat->id;
        }

        // 3. Realistic Products with genuine barcodes
        $productsData = [
            [
                'name' => 'Indomie Goreng Spesial 85g',
                'sku' => 'MKN-IND-01',
                'barcode' => '8998866200224',
                'category_id' => $categoryMap['Makanan Ringan'],
                'purchase_price' => 2700,
                'selling_price' => 3500,
                'stock' => 150,
                'minimum_stock' => 25,
                'unit' => 'pcs',
            ],
            [
                'name' => 'Ultra Milk Cokelat 250ml',
                'sku' => 'MNM-ULT-01',
                'barcode' => '8992753112211',
                'category_id' => $categoryMap['Minuman Dingin & Susu'],
                'purchase_price' => 5400,
                'selling_price' => 7000,
                'stock' => 90,
                'minimum_stock' => 20,
                'unit' => 'pcs',
            ],
            [
                'name' => 'Aqua Air Mineral 600ml',
                'sku' => 'MNM-AQU-01',
                'barcode' => '8886008101053',
                'category_id' => $categoryMap['Minuman Dingin & Susu'],
                'purchase_price' => 2800,
                'selling_price' => 4000,
                'stock' => 200,
                'minimum_stock' => 40,
                'unit' => 'botol',
            ],
            [
                'name' => 'Chitato Sapi Panggang 68g',
                'sku' => 'MKN-CHT-01',
                'barcode' => '8992775110012',
                'category_id' => $categoryMap['Makanan Ringan'],
                'purchase_price' => 9500,
                'selling_price' => 12500,
                'stock' => 60,
                'minimum_stock' => 15,
                'unit' => 'pcs',
            ],
            [
                'name' => 'Pepsodent Fresh Cool 190g',
                'sku' => 'PWT-PEP-01',
                'barcode' => '8999999052028',
                'category_id' => $categoryMap['Perawatan Tubuh & Mandi'],
                'purchase_price' => 13500,
                'selling_price' => 17500,
                'stock' => 40,
                'minimum_stock' => 10,
                'unit' => 'pcs',
            ],
            [
                'name' => 'Sari Roti Tawar Spesial',
                'sku' => 'MKN-ROT-01',
                'barcode' => '8994005111003',
                'category_id' => $categoryMap['Makanan Ringan'],
                'purchase_price' => 12000,
                'selling_price' => 15500,
                'stock' => 30,
                'minimum_stock' => 8,
                'unit' => 'pcs',
            ],
            [
                'name' => 'Pocari Sweat Can 330ml',
                'sku' => 'MNM-POC-01',
                'barcode' => '8993077110115',
                'category_id' => $categoryMap['Minuman Dingin & Susu'],
                'purchase_price' => 6200,
                'selling_price' => 8000,
                'stock' => 80,
                'minimum_stock' => 15,
                'unit' => 'kaleng',
            ],
            [
                'name' => 'Teh Botol Sosro 450ml',
                'sku' => 'MNM-THB-01',
                'barcode' => '8998888110014',
                'category_id' => $categoryMap['Minuman Dingin & Susu'],
                'purchase_price' => 5000,
                'selling_price' => 6500,
                'stock' => 110,
                'minimum_stock' => 20,
                'unit' => 'botol',
            ],
            [
                'name' => 'SilverQueen Cashew 62g',
                'sku' => 'MKN-SLV-01',
                'barcode' => '8991001100018',
                'category_id' => $categoryMap['Makanan Ringan'],
                'purchase_price' => 14000,
                'selling_price' => 18500,
                'stock' => 45,
                'minimum_stock' => 10,
                'unit' => 'pcs',
            ],
            [
                'name' => 'Minyak Goreng Sania 2L Pouch',
                'sku' => 'KBP-MGO-01',
                'barcode' => '8991002100021',
                'category_id' => $categoryMap['Kebutuhan Pokok & Dapur'],
                'purchase_price' => 32000,
                'selling_price' => 37500,
                'stock' => 55,
                'minimum_stock' => 12,
                'unit' => 'pouch',
            ],
            [
                'name' => 'Beras Setra Ramos 5kg',
                'sku' => 'KBP-BRS-01',
                'barcode' => '8991003100038',
                'category_id' => $categoryMap['Kebutuhan Pokok & Dapur'],
                'purchase_price' => 67000,
                'selling_price' => 76500,
                'stock' => 25,
                'minimum_stock' => 8,
                'unit' => 'sak',
            ],
            [
                'name' => 'Sabun Mandi Lifebuoy Red 85g',
                'sku' => 'PWT-LFB-01',
                'barcode' => '8991004100045',
                'category_id' => $categoryMap['Perawatan Tubuh & Mandi'],
                'purchase_price' => 3400,
                'selling_price' => 4800,
                'stock' => 85,
                'minimum_stock' => 20,
                'unit' => 'pcs',
            ],
            [
                'name' => 'Tolak Angin Cair Box 5x15ml',
                'sku' => 'OBT-TLK-01',
                'barcode' => '8991005100052',
                'category_id' => $categoryMap['Obat & Kesehatan'],
                'purchase_price' => 17500,
                'selling_price' => 22000,
                'stock' => 40,
                'minimum_stock' => 10,
                'unit' => 'box',
            ],
            [
                'name' => 'Panadol Extra Merah Strip 10s',
                'sku' => 'OBT-PND-01',
                'barcode' => '8991006100069',
                'category_id' => $categoryMap['Obat & Kesehatan'],
                'purchase_price' => 12500,
                'selling_price' => 16000,
                'stock' => 6, // Low stock product for testing alerts!
                'minimum_stock' => 10,
                'unit' => 'strip',
            ],
            [
                'name' => 'Mizone Apple Guava 500ml',
                'sku' => 'MNM-MIZ-01',
                'barcode' => '8991007100076',
                'category_id' => $categoryMap['Minuman Dingin & Susu'],
                'purchase_price' => 4200,
                'selling_price' => 5500,
                'stock' => 0, // Out of stock product for testing alerts!
                'minimum_stock' => 15,
                'unit' => 'botol',
            ],
        ];

        $createdProducts = [];
        foreach ($productsData as $data) {
            $product = Product::create([
                'category_id' => $data['category_id'],
                'sku' => $data['sku'],
                'barcode' => $data['barcode'],
                'name' => $data['name'],
                'purchase_price' => $data['purchase_price'],
                'selling_price' => $data['selling_price'],
                'stock' => $data['stock'],
                'minimum_stock' => $data['minimum_stock'],
                'unit' => $data['unit'],
                'is_active' => true,
            ]);

            $createdProducts[] = $product;

            // Audit ledger movement for initial stock
            if ($data['stock'] > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $gudang->id,
                    'movement_type' => 'PURCHASE_IN',
                    'qty_change' => $data['stock'],
                    'stock_before' => 0,
                    'stock_after' => $data['stock'],
                    'reference_type' => 'goods_receipt',
                    'reference_id' => null,
                    'notes' => 'Saldo awal stok masuk saat instalasi sistem',
                    'created_at' => now()->subDays(7),
                    'updated_at' => now()->subDays(7),
                ]);
            }
        }

        // 4. Sample Goods Receipt
        $gr = GoodsReceipt::create([
            'user_id' => $gudang->id,
            'receipt_number' => 'GR/' . now()->subDays(3)->format('Ymd') . '/0001',
            'received_at' => now()->subDays(3),
            'supplier_name' => 'PT Indomarco Prismatama Distribution',
            'status' => 'completed',
            'notes' => 'Penerimaan stok mingguan batch regular',
        ]);

        GoodsReceiptItem::create([
            'goods_receipt_id' => $gr->id,
            'product_id' => $createdProducts[0]->id,
            'qty' => 50,
            'unit_cost' => 2700,
            'subtotal' => 135000,
        ]);

        GoodsReceiptItem::create([
            'goods_receipt_id' => $gr->id,
            'product_id' => $createdProducts[2]->id,
            'qty' => 100,
            'unit_cost' => 2800,
            'subtotal' => 280000,
        ]);

        // 5. Seed Realistic Historical Sales for the past 5 days
        $paymentMethods = ['cash', 'qris', 'card'];
        $invoiceCounter = 1;

        for ($day = 5; $day >= 0; $day--) {
            $date = Carbon::now()->subDays($day);
            $txCount = rand(4, 8);

            for ($t = 0; $t < $txCount; $t++) {
                $soldAt = (clone $date)->setHour(rand(8, 21))->setMinute(rand(0, 59));
                $invNum = 'INV/' . $soldAt->format('Ymd') . '/' . str_pad((string) $invoiceCounter++, 4, '0', STR_PAD_LEFT);

                // Select 1 to 4 random products
                $itemCount = rand(1, 4);
                $selectedIndexes = (array) array_rand($createdProducts, $itemCount);

                $subtotal = 0;
                $itemsToCreate = [];

                foreach ($selectedIndexes as $idx) {
                    $prod = $createdProducts[$idx];
                    $qty = rand(1, 3);
                    $linePrice = (float) $prod->selling_price;
                    $lineCost = (float) $prod->purchase_price;
                    $lineTotal = $linePrice * $qty;

                    $subtotal += $lineTotal;

                    $itemsToCreate[] = [
                        'product_id' => $prod->id,
                        'qty' => $qty,
                        'unit_price' => $linePrice,
                        'unit_cost' => $lineCost,
                        'subtotal' => $lineTotal,
                    ];
                }

                $discount = ($subtotal > 50000 && rand(1, 4) === 1) ? 5000 : 0;
                $total = $subtotal - $discount;

                $sale = Sale::create([
                    'invoice_number' => $invNum,
                    'user_id' => $kasir->id,
                    'sold_at' => $soldAt,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'total' => $total,
                    'status' => 'completed',
                    'notes' => null,
                    'created_at' => $soldAt,
                    'updated_at' => $soldAt,
                ]);

                foreach ($itemsToCreate as $it) {
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $it['product_id'],
                        'qty' => $it['qty'],
                        'unit_price' => $it['unit_price'],
                        'unit_cost' => $it['unit_cost'],
                        'subtotal' => $it['subtotal'],
                        'created_at' => $soldAt,
                        'updated_at' => $soldAt,
                    ]);

                    StockMovement::create([
                        'product_id' => $it['product_id'],
                        'user_id' => $kasir->id,
                        'movement_type' => 'SALE',
                        'qty_change' => -$it['qty'],
                        'stock_before' => 50,
                        'stock_after' => 50 - $it['qty'],
                        'reference_type' => 'sale',
                        'reference_id' => $sale->id,
                        'notes' => "Penjualan #{$invNum}",
                        'created_at' => $soldAt,
                        'updated_at' => $soldAt,
                    ]);
                }

                $method = $paymentMethods[array_rand($paymentMethods)];
                $received = $total;
                $change = 0;

                if ($method === 'cash') {
                    // Round up to nearest 10,000 or 50,000
                    $received = ceil($total / 10000) * 10000;
                    if ($received < $total) {
                        $received += 10000;
                    }
                    $change = $received - $total;
                }

                Payment::create([
                    'sale_id' => $sale->id,
                    'method' => $method,
                    'amount' => $total,
                    'amount_received' => $received,
                    'change_amount' => $change,
                    'reference' => $method !== 'cash' ? 'REF-' . strtoupper(bin2hex(random_bytes(4))) : null,
                    'created_at' => $soldAt,
                    'updated_at' => $soldAt,
                ]);
            }
        }
    }
}
