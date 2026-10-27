<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('sku')->unique();
            $table->string('barcode')->unique();
            $table->string('name');
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->decimal('stock', 12, 2)->default(0);
            $table->decimal('minimum_stock', 12, 2)->default(5);
            $table->string('unit')->default('pcs');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['sku', 'barcode', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
