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
        $password = env('ADMIN_PASSWORD');

        if (! is_string($password) || strlen($password) < 12) {
            throw new \RuntimeException('ADMIN_PASSWORD must be configured with at least 12 characters.');
        }

        User::query()->updateOrCreate([
            'email' => env('ADMIN_EMAIL', 'rogerlucas@rogerlab.es'),
        ], [
            'name' => env('ADMIN_NAME', 'Roger Lucas'),
            'password' => $password,
            'email_verified_at' => now(),
        ]);
    }
}
