<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('purchase_orders', 'delivery_cost')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->decimal('delivery_cost', 10, 2)->default(0)->after('notes');
            });
        }

        if (! Schema::hasColumn('purchase_orders', 'insurance_cost')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->decimal('insurance_cost', 10, 2)->default(0)->after('delivery_cost');
            });
        }

        if (! Schema::hasColumn('purchase_orders', 'other_cost')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->decimal('other_cost', 10, 2)->default(0)->after('insurance_cost');
            });
        }

        if (! Schema::hasColumn('purchase_orders', 'total_cost')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->decimal('total_cost', 12, 2)->default(0)->after('other_cost');
            });
        }
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['delivery_cost', 'insurance_cost', 'other_cost', 'total_cost'] as $column) {
                if (Schema::hasColumn('purchase_orders', $column)) {
                    $columnsToDrop[] = $column;
                }
            }

            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
