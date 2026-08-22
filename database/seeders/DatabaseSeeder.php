<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate([
            'email' => env('ADMIN_EMAIL', 'rogerlucas@rogerlab.es'),
        ], [
            'name' => env('ADMIN_NAME', 'Roger Lucas'),
            'password' => env('ADMIN_PASSWORD', '123456789'),
            'email_verified_at' => now(),
        ]);
    }
}
