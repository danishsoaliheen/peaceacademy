<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaClass extends Model
{
    protected $table = 'pa_classes';

    protected $fillable = [
        'class_name',
        'class_code',
        'class_order',
        'is_active',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function enrollments()
    {
        return $this->hasMany(PaEnrollment::class, 'class_id');
    }

    public function activeEnrollments()
    {
        return $this->hasMany(PaEnrollment::class, 'class_id')
            ->where('is_active', 1);
    }

    public function feeStructures()
    {
        return $this->hasMany(ClassFeeStructure::class, 'class_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Count of currently active students in this class.
     */
    public function getActiveStudentCountAttribute(): int
    {
        return $this->activeEnrollments()->count();
    }
}
