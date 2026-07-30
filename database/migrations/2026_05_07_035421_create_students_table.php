<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run migrations
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Basic Student Info
            |--------------------------------------------------------------------------
            */

            $table->string('admission_no')->unique();

            $table->string('student_name');

            $table->string('father_name')->nullable();

            $table->string('mother_name')->nullable();

            $table->enum('gender', [
                'Male',
                'Female'
            ])->nullable();

            $table->date('date_of_birth')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Contact Info
            |--------------------------------------------------------------------------
            */

            $table->string('mobile_no')->nullable();

            $table->string('whatsapp_no')->nullable();

            $table->text('address')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Student Image
            |--------------------------------------------------------------------------
            */

            $table->string('student_image')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};