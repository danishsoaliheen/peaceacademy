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
        if (!Schema::hasColumn('pa_classes', 'class_order')) {
            Schema::table('pa_classes', function (Blueprint $table) {
                $table->integer('class_order')->default(1)->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('pa_classes', 'class_order')) {
            Schema::table('pa_classes', function (Blueprint $table) {
                $table->dropColumn('class_order');
            });
        }
    }
};