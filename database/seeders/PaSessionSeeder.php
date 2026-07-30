<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaSessionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pa_sessions')->insert([

            [
                'session_name' => '2025-2026',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],

            [
                'session_name' => '2026-2027',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]

        ]);
    }
}