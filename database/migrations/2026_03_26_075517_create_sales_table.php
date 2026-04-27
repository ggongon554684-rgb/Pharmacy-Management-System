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
     *  - Added softDeletes() — Sale model uses SoftDeletes trait but the column was missing.
     *  - Added payment_tendered, payment_change_due, payment_reference — used by Sale::$fillable
     *    and SalesController but absent from the original schema (would cause DB errors on every sale).
     *  - Added insurance_provider, insurance_policy_number, insurance_authorization_code — same reason.
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('prescription_id')->nullable()->constrained()->nullOnDelete();

            $table->decimal('total_amount', 10, 2);
            $table->enum('payment_method', ['cash', 'card', 'insurance'])->default('cash');

            // Cash payment fields
            $table->decimal('payment_tendered', 10, 2)->default(0);
            $table->decimal('payment_change_due', 10, 2)->default(0);

            // Card payment fields
            $table->string('payment_reference')->nullable();

            // Insurance payment fields
            $table->string('insurance_provider')->nullable();
            $table->string('insurance_policy_number')->nullable();
            $table->string('insurance_authorization_code')->nullable();

            $table->timestamps();
            $table->softDeletes(); // Required: Sale model uses SoftDeletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};