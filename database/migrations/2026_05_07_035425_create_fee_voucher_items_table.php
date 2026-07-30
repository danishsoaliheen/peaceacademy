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
        Schema::create('fee_voucher_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('voucher_id')
                  ->constrained('fee_vouchers')
                  ->cascadeOnDelete();

            $table->foreignId('fee_type_id')
                  ->constrained('fee_types')
                  ->restrictOnDelete();

            $table->string('description')->nullable();

            $table->date('month')->nullable();

            $table->integer('months_count')->default(1);

            $table->decimal('amount', 10, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_voucher_items');
    }
};