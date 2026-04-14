<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pre_orders', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name')->nullable();
            $table->enum('payment_method', ['cash', 'card', 'insurance'])->default('cash');
            $table->string('scan_token')->unique();
            $table->enum('status', ['pending', 'fulfilled', 'cancelled'])->default('pending');
            $table->timestamp('scanned_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('fulfilled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('pre_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pre_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_order_items');
        Schema::dropIfExists('pre_orders');
    }
};
