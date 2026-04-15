<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            $table->integer('requested_quantity')->nullable()->after('product_id');
            $table->integer('approved_quantity')->nullable()->after('requested_quantity');
            $table->text('adjustment_reason')->nullable()->after('reason');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->timestamp('fulfilled_at')->nullable()->after('approved_at');
        });

        DB::table('stock_requests')->update([
            'requested_quantity' => DB::raw('quantity'),
            'approved_quantity' => DB::raw("CASE WHEN status = 'fulfilled' THEN quantity ELSE NULL END"),
        ]);
    }

    public function down(): void
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            $table->dropColumn([
                'requested_quantity',
                'approved_quantity',
                'adjustment_reason',
                'approved_at',
                'fulfilled_at',
            ]);
        });
    }
};
