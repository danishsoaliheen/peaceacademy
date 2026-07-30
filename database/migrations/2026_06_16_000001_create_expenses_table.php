<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_no', 30)->unique();
            $table->string('category', 80);
            $table->string('sub_category', 80)->nullable();
            $table->string('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->string('expense_month', 7)->index(); // YYYY-MM
            $table->string('payment_method', 40)->default('Cash');
            $table->string('paid_to', 100)->nullable();
            $table->string('reference_no', 60)->nullable();
            $table->string('recorded_by', 80)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('category');
        });
    }
    public function down(): void { Schema::dropIfExists('expenses'); }
};
