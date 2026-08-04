<?php
// Save as: app/Models/FeeVoucher.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FeeVoucher extends Model
{
    protected $fillable = [
        'voucher_no',
        'student_id',
        'voucher_type',
        'period_from',
        'period_to',
        'total_amount',
        'amount_in_words',
        'discount',
        'payable_amount',
        'paid_amount',
        'balance_amount',
        'due_date',
        'status',
        'notes',
        // Carry-forward linking (added for previous-balance workflow)
        'carried_forward_to_voucher_id',
        'previous_balance_voucher_id',
    ];

    protected $casts = [
        'total_amount'   => 'decimal:2',
        'discount'       => 'decimal:2',
        'payable_amount' => 'decimal:2',
        'paid_amount'    => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'period_from'    => 'date',
        'period_to'      => 'date',
        'due_date'       => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Uses the single canonical Student model.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function items()
    {
        return $this->hasMany(FeeVoucherItem::class, 'voucher_id');
    }

    public function payments()
    {
        return $this->hasMany(FeePayment::class, 'voucher_id');
    }

    /**
     * On an OLD voucher: the NEW voucher that absorbed its balance
     * (set once this voucher has been carried forward).
     */
    public function carriedForwardTo()
    {
        return $this->belongsTo(FeeVoucher::class, 'carried_forward_to_voucher_id');
    }

    /**
     * On a NEW voucher: every OLD voucher that was closed out and
     * rolled into this one.
     */
    public function carriedForwardFrom()
    {
        return $this->hasMany(FeeVoucher::class, 'carried_forward_to_voucher_id');
    }

    /**
     * On a NEW voucher: the single (latest, if several contributed)
     * OLD voucher its "Previous outstanding balance (b/f)" line
     * references — used for display only.
     */
    public function previousBalanceVoucher()
    {
        return $this->belongsTo(FeeVoucher::class, 'previous_balance_voucher_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    public function scopeCarriedForward($query)
    {
        return $query->where('status', 'carried_forward');
    }

    public function scopeOverdue($query)
    {
        return $query->whereIn('status', ['unpaid', 'partial'])
                     ->where('due_date', '<', now()->toDateString());
    }

    public function scopeOutstanding($query)
    {
        return $query->whereIn('status', ['unpaid', 'partial'])
                     ->where('balance_amount', '>', 0);
    }

    /*
    |--------------------------------------------------------------------------
    | Balance Recalculation (single source of truth)
    |--------------------------------------------------------------------------
    |
    | Called after every payment create, update, or delete.
    | Eliminates duplicated recalculation logic across controllers.
    |--------------------------------------------------------------------------
    */

    /**
     * Recalculate paid/balance amounts and status from all linked payments.
     * Uses a fresh DB query so it always reflects the current state.
     */
    public function recalculateBalance(): void
    {
        $totalPaid = $this->payments()->sum('amount_paid');

        $this->paid_amount    = $totalPaid;
        $this->balance_amount = $this->payable_amount - $totalPaid;

        if ($totalPaid <= 0) {
            $this->status = 'unpaid';
        } elseif ($totalPaid >= $this->payable_amount) {
            $this->status = 'paid';
        } else {
            $this->status = 'partial';
        }

        $this->save();
    }

    /**
     * Close this voucher out because its remaining balance has been rolled
     * into $newVoucher (via the Monthly Fee Generator or the Previous
     * Balance carry-forward screens). Marks status as 'carried_forward',
     * zeroes the balance so it can never be double-counted in the ledger,
     * dashboard, or defaulter reports, links back to the new voucher, and
     * leaves a human-readable trail in notes.
     *
     * Deliberately does NOT touch paid_amount — that stays as the true
     * historical record of what was actually paid against this voucher.
     */
    public function markAsCarriedForwardTo(FeeVoucher $newVoucher): void
    {
        $this->status                        = 'carried_forward';
        $this->balance_amount                = 0;
        $this->carried_forward_to_voucher_id = $newVoucher->id;
        $this->notes = trim(
            ($this->notes ? $this->notes . ' | ' : '')
            . 'Carried forward to voucher ' . $newVoucher->voucher_no . ' on ' . now()->format('Y-m-d')
        );

        $this->save();
    }

    /**
     * Generate the next sequential receipt number.
     * Uses a shared lock to prevent duplicate receipt numbers
     * when two payments are recorded simultaneously.
     */
    public static function nextReceiptNo(): string
    {
        return DB::transaction(function () {
            $lastReceipt = FeePayment::lockForUpdate()
                ->orderByDesc('id')
                ->value('receipt_no');

            $nextNum = 1;

            if ($lastReceipt && preg_match('/(\d+)$/', $lastReceipt, $matches)) {
                $nextNum = (int) $matches[1] + 1;
            }

            return 'RCPT-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
        });
    }
}