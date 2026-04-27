<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // inventory_batches — queried heavily for stock sums and location lookups
        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('location_id');
            $table->index(['product_id', 'location_id']);
        });

        // sales — filtered by user_id and created_at constantly
        Schema::table('sales', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
            $table->index('created_at');
        });

        // audit_logs — sorted by created_at on every dashboard
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('user_id');
        });

        // products — reorder_level used in HAVING comparisons
        Schema::table('products', function (Blueprint $table) {
            $table->index('reorder_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['location_id']);
            $table->dropIndex(['product_id', 'location_id']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['user_id']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['reorder_level']);
        });
    }
};
