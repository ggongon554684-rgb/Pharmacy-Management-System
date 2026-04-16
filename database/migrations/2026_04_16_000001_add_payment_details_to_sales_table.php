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
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('payment_tendered', 10, 2)->default(0)->after('payment_method');
            $table->decimal('payment_change_due', 10, 2)->default(0)->after('payment_tendered');
            $table->string('payment_reference')->nullable()->after('payment_change_due');
            $table->string('insurance_provider')->nullable()->after('payment_reference');
            $table->string('insurance_policy_number')->nullable()->after('insurance_provider');
            $table->string('insurance_authorization_code')->nullable()->after('insurance_policy_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'payment_tendered',
                'payment_change_due',
                'payment_reference',
                'insurance_provider',
                'insurance_policy_number',
                'insurance_authorization_code',
            ]);
        });
    }
};
