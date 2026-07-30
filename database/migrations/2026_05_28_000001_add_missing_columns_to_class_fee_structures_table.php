<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_fee_structures', function (Blueprint $table) {

            $table->boolean('is_mandatory')
                  ->default(true)
                  ->after('amount');

            $table->boolean('allow_discount')
                  ->default(false)
                  ->after('is_mandatory');

            $table->text('notes')
                  ->nullable()
                  ->after('is_active');

        });
    }

    public function down(): void
    {
        Schema::table('class_fee_structures', function (Blueprint $table) {

            $table->dropColumn(['is_mandatory', 'allow_discount', 'notes']);

        });
    }
};
