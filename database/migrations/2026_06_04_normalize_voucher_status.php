<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Normalise any mixed-case status values to lowercase
        // Affects vouchers created by the old controller that used 'Unpaid' (capital U)
        DB::table('fee_vouchers')
            ->whereIn('status', ['Unpaid', 'Partial', 'Paid'])
            ->update(['status' => DB::raw('LOWER(status)')]);
    }

    public function down(): void
    {
        // No rollback needed — lowercase is the correct canonical value
    }
};
