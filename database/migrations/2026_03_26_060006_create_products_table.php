<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Changes from original:
     *  - Added softDeletes() — Product model uses SoftDeletes trait but the column was missing,
     *    meaning any soft-delete call would throw a DB error.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Product details
            $table->string('name');
            $table->string('generic_name')->nullable();
            $table->string('sku')->unique();

            // Financial details
            $table->decimal('price', 10, 2);
            $table->integer('reorder_level')->default(0);

            $table->timestamps();
            $table->softDeletes(); // Required: Product model uses SoftDeletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};