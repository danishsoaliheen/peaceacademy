<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeVoucherItem extends Model
{
    protected $fillable = [
        'voucher_id',
        'fee_type_id',
        'description',
        'month',
        'months_count',
        'amount'
    ];

    public function voucher()
    {
        return $this->belongsTo(FeeVoucher::class, 'voucher_id');
    }

    public function feeType()
    {
        return $this->belongsTo(FeeType::class);
    }
}