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
    Schema::table('fee_vouchers', function (Blueprint $table) {

        $table->text('amount_in_words')
              ->nullable()
              ->after('payable_amount');

    });
}



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_vouchers', function (Blueprint $table) {
            //
        });
    }
};
