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
            | Mother's Contact
            |--------------------------------------------------------------------------
            | Existing `mobile_no` / `whatsapp_no` columns are now treated as the
            | FATHER's numbers (all existing data already belongs there). These two
            | new columns hold the MOTHER's numbers.
            |--------------------------------------------------------------------------
            */

            $table->string('mother_mobile_no')->nullable()->after('mobile_no');
            $table->string('mother_whatsapp_no')->nullable()->after('whatsapp_no');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['mother_mobile_no', 'mother_whatsapp_no']);
        });
    }
};
