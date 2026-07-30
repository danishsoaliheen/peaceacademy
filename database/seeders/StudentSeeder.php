<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        Student::insert([

            [
                'student_name' => 'Ali Raza',
                'father_name' => 'Muhammad Raza',
                'class_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'student_name' => 'Ahmed Khan',
                'father_name' => 'Saeed Khan',
                'class_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'student_name' => 'Sara Ahmed',
                'father_name' => 'Ahmed Ali',
                'class_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'student_name' => 'Usman Tariq',
                'father_name' => 'Tariq Mehmood',
                'class_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}