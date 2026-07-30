<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassFeeStructure extends Model
{
    protected $fillable = [

        'class_id',
        'fee_type_id',
        'amount',
        'is_mandatory',
        'allow_discount',
        'is_active',
        'notes'

    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function class()
    {
        return $this->belongsTo(PaClass::class, 'class_id');
    }

    public function feeType()
    {
        return $this->belongsTo(FeeType::class, 'fee_type_id');
    }
}