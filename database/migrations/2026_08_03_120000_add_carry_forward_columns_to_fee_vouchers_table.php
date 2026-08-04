<?php
// Save as: database/migrations/2026_08_03_120000_add_carry_forward_columns_to_fee_vouchers_table.php
// Run with: php artisan migrate

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |----------------------------------------------------------------------
        | Widen `status` to a plain VARCHAR(20) so it accepts the new
        | 'carried_forward' value regardless of whether the column was
        | originally created as a MySQL/MariaDB ENUM or a VARCHAR.
        | Raw SQL is used deliberately — Schema::table()->change() would
        | require the doctrine/dbal package, which this project doesn't have.
        |----------------------------------------------------------------------
        */
        DB::statement("ALTER TABLE fee_vouchers MODIFY status VARCHAR(20) NOT NULL DEFAULT 'unpaid'");

        Schema::table('fee_vouchers', function (Blueprint $table) {
            // On an OLD (overdue) voucher: which NEW voucher absorbed its balance.
            $table->foreignId('carried_forward_to_voucher_id')
                ->nullable()
                ->after('status')
                ->constrained('fee_vouchers')
                ->nullOnDelete();

            // On a NEW voucher: the (latest, if several) OLD voucher its
            // "Previous outstanding balance (b/f)" line was pulled from.
            $table->foreignId('previous_balance_voucher_id')
                ->nullable()
                ->after('carried_forward_to_voucher_id')
                ->constrained('fee_vouchers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fee_vouchers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('carried_forward_to_voucher_id');
            $table->dropConstrainedForeignId('previous_balance_voucher_id');
        });

        // Note: status column is intentionally left as VARCHAR(20) on rollback
        // rather than reverted to a strict ENUM, to avoid data loss if any
        // rows already carry the 'carried_forward' value.
    }
};
