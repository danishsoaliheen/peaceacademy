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
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('voucher_id')
                  ->constrained('fee_vouchers')
                  ->cascadeOnDelete();

            $table->foreignId('student_id')
                  ->constrained('students')
                  ->cascadeOnDelete();

            $table->string('receipt_no')->unique();

            $table->decimal('amount_paid', 10, 2);

            $table->date('payment_date');

            $table->string('payment_method')->nullable();

            $table->string('reference_no')->nullable();

            $table->string('received_by')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['student_id', 'voucher_id']);
            $table->index(['payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
    }
};