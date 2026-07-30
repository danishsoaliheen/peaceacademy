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
            | Family / Siblings
            |--------------------------------------------------------------------------
            | Students sharing the same family_code are treated as siblings. Auto
            | generated on creation (e.g. FAM-0001) but editable so staff can merge
            | students into the same family group after the fact.
            |--------------------------------------------------------------------------
            */

            $table->string('family_code')->nullable()->after('admission_no');
            $table->index('family_code');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['family_code']);
            $table->dropColumn('family_code');
        });
    }
};
