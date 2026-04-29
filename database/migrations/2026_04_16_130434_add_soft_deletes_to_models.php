<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Note: products and sales already have softDeletes in their original create migrations.
     */
    public function up(): void
    {
        // Skip products and sales - they already have softDeletes in create migrations
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('prescribers', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('stock_requests', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only drop for tables that were modified in up()
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('prescribers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('stock_requests', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
