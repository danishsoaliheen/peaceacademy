<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Basic Student Info
            |--------------------------------------------------------------------------
            */

            $table->string('student_photo')->nullable();

            $table->string('b_form_no')->nullable();

            $table->string('blood_group')->nullable();

            $table->string('religion')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Parent / Guardian
            |--------------------------------------------------------------------------
            */

            $table->string('guardian_name')->nullable();

            $table->string('guardian_relation')->nullable();

            $table->string('guardian_mobile')->nullable();

            $table->string('father_occupation')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Academic
            |--------------------------------------------------------------------------
            */

            $table->date('admission_date')->nullable();

            $table->string('previous_school')->nullable();

            $table->string('previous_class')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Emergency
            |--------------------------------------------------------------------------
            */

            $table->string('emergency_contact')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Address
            |--------------------------------------------------------------------------
            */

            $table->text('permanent_address')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {

            $table->dropColumn([

                'student_photo',

                'b_form_no',

                'blood_group',

                'religion',

                'guardian_name',

                'guardian_relation',

                'guardian_mobile',

                'father_occupation',

                'admission_date',

                'previous_school',

                'previous_class',

                'emergency_contact',

                'permanent_address'

            ]);
        });
    }
};