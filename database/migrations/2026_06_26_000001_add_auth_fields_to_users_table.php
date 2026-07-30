<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Step 1: Create the users table (if it does not already exist)
        |--------------------------------------------------------------------------
        | Laravel needs a "users" table for login/authentication to work.
        | This table stores admin/parent/staff accounts with email + password.
        */

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('role')->default('admin');
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Step 2: Create the default admin user
        |--------------------------------------------------------------------------
        | Email: admin@peaceacademy.com
        | Password: password   (change this after first login!)
        */

        DB::table('users')->insert([
            'name'      => 'Admin',
            'email'     => 'admin@peaceacademy.com',
            'password'  => Hash::make('password'),
            'role'      => 'admin',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Step 3: Create password_reset_tokens table (required by auth)
        |--------------------------------------------------------------------------
        | This table stores "forgot password" tokens so users can reset
        | their password via email link.
        */

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};