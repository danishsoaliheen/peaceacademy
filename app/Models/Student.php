<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Student Model
 *
 * Single canonical model for the `students` table.
 * Previously the codebase had two models (Student + PaStudent)
 * both pointing at the same table — that has been consolidated here.
 *
 * All controllers and relationships now use App\Models\Student.
 */
class Student extends Model
{
    protected $table = 'students';

    protected $fillable = [

        /*
        |----------------------------------------------------------------------
        | Identification
        |----------------------------------------------------------------------
        */

        'admission_no',
        'family_code',

        /*
        |----------------------------------------------------------------------
        | Personal Information
        |----------------------------------------------------------------------
        */

        'student_name',
        'father_name',
        'mother_name',
        'gender',
        'date_of_birth',
        'blood_group',
        'religion',
        'b_form_no',

        /*
        |----------------------------------------------------------------------
        | Contact Information
        |----------------------------------------------------------------------
        */

        'mobile_no',
        'mother_mobile_no',
        'whatsapp_no',
        'mother_whatsapp_no',
        'address',
        'permanent_address',
        'emergency_contact',

        /*
        |----------------------------------------------------------------------
        | Guardian / Parent
        |----------------------------------------------------------------------
        */

        'guardian_name',
        'guardian_relation',
        'guardian_mobile',
        'father_occupation',

        /*
        |----------------------------------------------------------------------
        | Academic
        |----------------------------------------------------------------------
        */

        'admission_date',
        'previous_school',
        'previous_class',

        /*
        |----------------------------------------------------------------------
        | Photo
        |----------------------------------------------------------------------
        */

        'student_image',
        'student_photo',

        /*
        |----------------------------------------------------------------------
        | Status
        |----------------------------------------------------------------------
        */

        'is_active',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * A student can have many enrollments (one per session/class).
     */
    public function enrollments()
    {
        return $this->hasMany(PaEnrollment::class, 'student_id');
    }

    /**
     * Current active enrollment (latest active record).
     */
    public function activeEnrollment()
    {
        return $this->hasOne(PaEnrollment::class, 'student_id')
            ->where('is_active', 1)
            ->latest();
    }

    /**
     * Fee vouchers issued to this student.
     */
    public function vouchers()
    {
        return $this->hasMany(FeeVoucher::class, 'student_id');
    }

    /**
     * All fee payments made by this student.
     */
    public function payments()
    {
        return $this->hasMany(FeePayment::class, 'student_id');
    }

    /**
     * Other students sharing this student's family_code (siblings).
     * Guard against calling this when family_code is null — Eloquent would
     * otherwise translate the join into "WHERE family_code IS NULL" and
     * match every un-linked student in the table.
     */
    public function siblings()
    {
        return $this->hasMany(self::class, 'family_code', 'family_code')
            ->where('id', '!=', $this->id);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors / Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Total outstanding balance across all of this student's fee vouchers.
     */
    public function getOutstandingBalanceAttribute()
    {
        return $this->vouchers()->sum('balance_amount');
    }

    /**
     * Returns the photo URL or null.
     * Checks both 'student_image' and 'student_photo' columns for compatibility.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        $photo = $this->student_image ?? $this->student_photo;

        return $photo
            ? route('students.photo', basename($photo))
            : null;
    }

    /**
     * Returns first letter of student name for avatar fallback.
     */
    public function getInitialAttribute(): string
    {
        return strtoupper(substr($this->student_name, 0, 1));
    }
}