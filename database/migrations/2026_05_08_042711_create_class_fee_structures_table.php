<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_fee_structures', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Class
            |--------------------------------------------------------------------------
            */

            $table->foreignId('class_id')
                  ->constrained('pa_classes')
                  ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Fee Type
            |--------------------------------------------------------------------------
            */

            $table->foreignId('fee_type_id')
                  ->constrained('fee_types')
                  ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Amount
            |--------------------------------------------------------------------------
            */

            $table->decimal('amount', 10, 2);

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')
                  ->default(true);

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_fee_structures');
    }
};