<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaEnrollment extends Model
{
    protected $table = 'pa_enrollments';

    protected $fillable = [
        'student_id',
        'class_id',
        'session_id',
        'roll_no',
        'enrollment_date',
        'is_active',
        'status',
        'monthly_fee',
        'discount_amount',
        'notes',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Belongs to a student.
     * Uses the single canonical Student model.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function class()
    {
        return $this->belongsTo(PaClass::class, 'class_id');
    }

    public function session()
    {
        return $this->belongsTo(PaSession::class, 'session_id');
    }
}
