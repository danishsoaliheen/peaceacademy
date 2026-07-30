<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeePayment extends Model
{
    protected $fillable = [
        'voucher_id',
        'student_id',
        'receipt_no',
        'amount_paid',
        'payment_date',
        'payment_method',
        'reference_no',
        'received_by',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount_paid'  => 'decimal:2',
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

    public function voucher()
    {
        return $this->belongsTo(FeeVoucher::class, 'voucher_id');
    }
}