<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaClass;

class ClassSeeder extends Seeder
{
    public function run(): void
    {
        PaClass::insert([

            [
                'class_name' => 'Reception',
                'class_code' => 'REC',
                'class_order' => 1,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'class_name' => 'Montessori',
                'class_code' => 'MON',
                'class_order' => 2,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'class_name' => 'KG-I',
                'class_code' => 'KG1',
                'class_order' => 3,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'class_name' => 'KG-II',
                'class_code' => 'KG2',
                'class_order' => 4,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'class_name' => 'Class 1',
                'class_code' => 'C1',
                'class_order' => 5,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}