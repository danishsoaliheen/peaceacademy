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
        Schema::create('pa_enrollments', function (Blueprint $table) {

            $table->id();


$table->foreignId('student_id')->constrained('students')->onDelete('cascade');

$table->foreignId('class_id')->constrained('pa_classes')->onDelete('cascade');

$table->foreignId('session_id')->constrained('pa_sessions')->onDelete('cascade');

$table->string('roll_no')->nullable();

$table->date('enrollment_date')->nullable();

$table->boolean('is_active')->default(1);

$table->date('admission_date')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Financial Fields
            |--------------------------------------------------------------------------
            */

            $table->decimal('monthly_fee', 10, 2)
                ->default(0);

            $table->decimal('discount_amount', 10, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Student Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'active',
                'inactive',
                'left',
                'passed_out'

            ])->default('active');

            $table->text('notes')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pa_enrollments');
    }
};