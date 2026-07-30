<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\PaClass;

class PaClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = [

            [
                'class_name' => 'Reception',
                'class_code' => 'Rec',
                'class_order' => 1,
                'is_active' => 1,
            ],

            [
                'class_name' => 'Mont',
                'class_code' => 'Mont',
                'class_order' => 2,
                'is_active' => 1,
            ],

            [
                'class_name' => 'KG-I',
                'class_code' => 'KG1',
                'class_order' => 3,
                'is_active' => 1,
            ],

            [
                'class_name' => 'KG-II',
                'class_code' => 'KG2',
                'class_order' => 4,
                'is_active' => 1,
            ],
            [
                'class_name' => 'Class 1',
                'class_code' => 'C1',
                'class_order' => 5,
                'is_active' => 1,
            ],

            [
                'class_name' => 'Class 2',
                'class_code' => 'C2',
                'class_order' => 6,
                'is_active' => 1,
            ],

            [
                'class_name' => 'Class 3',
                'class_code' => 'C3',
                'class_order' => 7,
                'is_active' => 1,
            ],

            [
                'class_name' => 'Class 4',
                'class_code' => 'C4',
                'class_order' => 8,
                'is_active' => 1,
            ],

            [
                'class_name' => 'Class 5',
                'class_code' => 'C5',
                'class_order' => 9,
                'is_active' => 1,
            ],


            [
                'class_name' => 'Class 6',
                'class_code' => 'C6',
                'class_order' => 10,
                'is_active' => 1,
            ],

            [
                'class_name' => 'Class 7',
                'class_code' => 'C7',
                'class_order' => 11,
                'is_active' => 1,
            ],

            [
                'class_name' => 'Class 8',
                'class_code' => 'C8',
                'class_order' => 12,
                'is_active' => 1,
            ],
        ];

        foreach($classes as $class){

            PaClass::updateOrCreate(

                [
                    'class_code' => $class['class_code']
                ],

                $class

            );
        }
    }
}