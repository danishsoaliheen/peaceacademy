<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeType extends Model
{
    protected $fillable = [

    'name',
    'code',
    'category',
    'default_amount',
    'is_active'

];

public function classFeeStructures()
{
    return $this->hasMany(
        ClassFeeStructure::class,
        'fee_type_id'
    );
}



    public function voucherItems()
    {
        return $this->hasMany(FeeVoucherItem::class);
    }
}