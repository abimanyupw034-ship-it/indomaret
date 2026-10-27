<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->string('method'); // cash, qris, card, transfer
            $table->decimal('amount', 15, 2);
            $table->decimal('amount_received', 15, 2);
            $table->decimal('change_amount', 15, 2)->default(0);
            $table->string('reference')->nullable();
            $table->timestamps();

            $table->index(['sale_id', 'method']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
