<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaSession extends Model
{
    protected $table = 'pa_sessions';

    protected $fillable = [
        'session_name',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function enrollments()
    {
        return $this->hasMany(PaEnrollment::class, 'session_id');
    }

    public function activeEnrollments()
    {
        return $this->hasMany(PaEnrollment::class, 'session_id')
            ->where('is_active', 1);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getEnrolledStudentCountAttribute(): int
    {
        return $this->activeEnrollments()->count();
    }
}
