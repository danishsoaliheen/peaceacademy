<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaSession;

class SessionSeeder extends Seeder
{
    public function run(): void
    {
        PaSession::insert([

            [
                'session_name' => '2025-2026',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'session_name' => '2026-2027',
                'is_active' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}