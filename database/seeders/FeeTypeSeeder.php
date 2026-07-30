<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FeeType;

class FeeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $feeTypes = [

            [
                'name' => 'Registration Form Charges',
                'code' => 'REG_FORM',
                'category' => 'admission',
                'default_amount' => 1000,
                'is_active' => 1,
            ],

            [
                'name' => 'Admission Fee',
                'code' => 'ADMISSION_FEE',
                'category' => 'admission',
                'default_amount' => 5500,
                'is_active' => 1,
            ],

            [
                'name' => 'Annual Charges',
                'code' => 'ANNUAL_CHARGES',
                'category' => 'yearly',
                'default_amount' => 3500,
                'is_active' => 1,
            ],

            [
                'name' => 'Monthly Fee',
                'code' => 'MONTHLY_FEE',
                'category' => 'monthly',
                'default_amount' => 1200,
                'is_active' => 1,
            ],

            [
                'name' => 'June + July Fee',
                'code' => 'JUNE_JULY_FEE',
                'category' => 'seasonal',
                'default_amount' => 2400,
                'is_active' => 1,
            ],

            [
                'name' => 'Course Charges',
                'code' => 'COURSE_CHARGES',
                'category' => 'yearly',
                'default_amount' => 6500,
                'is_active' => 1,
            ],

            [
                'name' => 'Uniform',
                'code' => 'UNIFORM',
                'category' => 'optional',
                'default_amount' => 2500,
                'is_active' => 1,
            ],

            [
                'name' => 'Test / File Charges',
                'code' => 'TEST_FILE',
                'category' => 'admission',
                'default_amount' => 1000,
                'is_active' => 1,
            ],

            [
                'name' => 'Copies Charges',
                'code' => 'COPIES',
                'category' => 'yearly',
                'default_amount' => 4000,
                'is_active' => 1,
            ],

            [
                'name' => 'Previous Balance',
                'code' => 'PREVIOUS_BALANCE',
                'category' => 'system',
                'default_amount' => 0,
                'is_active' => 1,
            ],

            [
                'name' => 'Books New - Old',
                'code' => 'BOOKS',
                'category' => 'optional',
                'default_amount' => 1800,
                'is_active' => 1,
            ],

        ];

        foreach($feeTypes as $feeType){

            FeeType::updateOrCreate(

                [
                    'code' => $feeType['code']
                ],

                array_merge(
                    $feeType,
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                )
            );
        }
    }
}