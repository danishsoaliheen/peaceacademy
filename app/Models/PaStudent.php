<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaStudent extends Model
{
    protected $table = 'students';

protected $fillable = [

    'admission_no',
    'student_name',
    'father_name',
    'mother_name',
    'gender',
    'date_of_birth',
    'mobile_no',
    'whatsapp_no',
    'address',
    'student_image',
    'student_photo',
    'b_form_no',
    'blood_group',
    'religion',
    'guardian_name',
    'guardian_relation',
    'guardian_mobile',
    'father_occupation',
    'admission_date',
    'previous_school',
    'previous_class',
    'is_active'

];

public function enrollments()
{
    return $this->hasMany(PaEnrollment::class, 'student_id');
}  

}