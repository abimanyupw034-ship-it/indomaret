<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RetailPosTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected User $warehouse;
    protected User $admin;
    protected Category $category;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::firstOrCreate(
            ['email' => 'kasir@pos.com'],
            ['name' => 'Budi Kasir', 'password' => Hash::make('password'), 'role' => 'kasir', 'is_active' => true]
        );

        $this->warehouse = User::firstOrCreate(
            ['email' => 'gudang@pos.com'],
            ['name' => 'Agus Gudang', 'password' => Hash::make('password'), 'role' => 'gudang', 'is_active' => true]
        );

        $this->admin = User::firstOrCreate(
            ['email' => 'admin@pos.com'],
            ['name' => 'Dewi Admin', 'password' => Hash::make('password'), 'role' => 'admin', 'is_active' => true]
        );

        $this->category = Category::firstOrCreate(['name' => 'Kategori Uji']);

        $this->product = Product::firstOrCreate(
            ['sku' => 'TEST-SKU-99'],
            [
                'category_id' => $this->category->id,
                'barcode' => '8999999999999',
                'name' => 'Produk Testing POS',
                'purchase_price' => 10000,
                'selling_price' => 15000,
                'stock' => 50,
                'minimum_stock' => 10,
                'unit' => 'pcs',
                'is_active' => true,
            ]
        );
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');

        $response = $this->get('/cashier');
        $response->assertRedirect('/login');

        $response = $this->get('/warehouse');
        $response->assertRedirect('/login');

        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }

    public function test_login_redirects_to_correct_role_dashboard(): void
    {
        // 1. Kasir Login
        $response = $this->post('/login', [
            'email' => 'kasir@pos.com',
            'password' => 'password',
        ]);
        $response->assertRedirect(route('cashier.pos'));

        auth()->logout();

        // 2. Gudang Login
        $response = $this->post('/login', [
            'email' => 'gudang@pos.com',
            'password' => 'password',
        ]);
        $response->assertRedirect(route('warehouse.dashboard'));

        auth()->logout();

        // 3. Admin Login
        $response = $this->post('/login', [
            'email' => 'admin@pos.com',
            'password' => 'password',
        ]);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_strict_role_based_access_control(): void
    {
        // Kasir cannot access Admin or Warehouse
        $response = $this->actingAs($this->cashier)->get('/admin');
        $response->assertStatus(403);

        $response = $this->actingAs($this->cashier)->get('/warehouse');
        $response->assertStatus(403);

        // Gudang cannot access Cashier or Admin
        $response = $this->actingAs($this->warehouse)->get('/cashier');
        $response->assertStatus(403);

        $response = $this->actingAs($this->warehouse)->get('/admin');
        $response->assertStatus(403);

        // Admin cannot access operational mutation pages
        $response = $this->actingAs($this->admin)->get('/cashier');
        $response->assertStatus(403);

        $response = $this->actingAs($this->admin)->get('/warehouse');
        $response->assertStatus(403);
    }

    public function test_cashier_can_search_product_by_barcode(): void
    {
        $response = $this->actingAs($this->cashier)->getJson(
            route('cashier.products.search', ['q' => '8999999999999'])
        );

        $response->assertStatus(200);
        $response->assertJson([
            'exact' => true,
            'product' => [
                'sku' => 'TEST-SKU-99',
                'barcode' => '8999999999999',
            ]
        ]);
    }

    public function test_cashier_checkout_success_and_stock_deduction(): void
    {
        $initialStock = (float) $this->product->fresh()->stock;

        $response = $this->actingAs($this->cashier)->postJson(route('cashier.checkout'), [
            'items' => [
                ['product_id' => $this->product->id, 'qty' => 3]
            ],
            'method' => 'cash',
            'amount_received' => 50000,
            'discount' => 0,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify stock reduced by 3
        $this->assertEquals($initialStock - 3, (float) $this->product->fresh()->stock);

        // Verify stock movement ledger created
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'movement_type' => 'SALE',
            'qty_change' => -3,
        ]);

        // Verify sale record
        $this->assertDatabaseHas('sales', [
            'user_id' => $this->cashier->id,
            'subtotal' => 45000,
            'total' => 45000,
            'status' => 'completed',
        ]);
    }

    public function test_cashier_checkout_rejected_if_cash_underpaid(): void
    {
        $response = $this->actingAs($this->cashier)->postJson(route('cashier.checkout'), [
            'items' => [
                ['product_id' => $this->product->id, 'qty' => 2] // 2 x 15000 = 30000
            ],
            'method' => 'cash',
            'amount_received' => 20000, // Underpaid
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    public function test_cashier_checkout_rejected_if_stock_insufficient(): void
    {
        $currentStock = (float) $this->product->fresh()->stock;

        $response = $this->actingAs($this->cashier)->postJson(route('cashier.checkout'), [
            'items' => [
                ['product_id' => $this->product->id, 'qty' => $currentStock + 10]
            ],
            'method' => 'cash',
            'amount_received' => 5000000,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    public function test_warehouse_goods_receipt_increases_stock(): void
    {
        $stockBefore = (float) $this->product->fresh()->stock;

        $response = $this->actingAs($this->warehouse)->post(route('warehouse.receipts.store'), [
            'supplier_name' => 'PT Distributor Uji',
            'received_at' => now()->toDateString(),
            'notes' => 'Penerimaan uji',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 25,
                    'unit_cost' => 10500,
                ]
            ]
        ]);

        $response->assertRedirect(route('warehouse.movements'));

        // Verify stock increased by 25
        $this->assertEquals($stockBefore + 25, (float) $this->product->fresh()->stock);

        // Verify stock movement ledger created
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'movement_type' => 'PURCHASE_IN',
            'qty_change' => 25,
        ]);
    }

    public function test_warehouse_stock_adjustment(): void
    {
        $response = $this->actingAs($this->warehouse)->post(route('warehouse.adjustments.store'), [
            'product_id' => $this->product->id,
            'physical_stock' => 100,
            'reason' => 'Selisih Inventaris Opname',
            'notes' => 'Ditemukan stok ekstra pada rak penyimpanan B3',
        ]);

        $response->assertRedirect(route('warehouse.movements'));

        $this->assertEquals(100, (float) $this->product->fresh()->stock);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'reference_type' => 'adjustment',
        ]);
    }

    public function test_admin_can_access_read_only_reports(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.sales'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.profit'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->admin)->get(route('admin.reports.inventory'));
        $response->assertStatus(200);
    }
}
