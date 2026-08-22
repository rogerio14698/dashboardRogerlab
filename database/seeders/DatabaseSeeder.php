<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Subdomain;
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
        $password = config('monitoring.admin_password');

        if (! is_string($password) || strlen($password) < 12) {
            throw new \RuntimeException('ADMIN_PASSWORD must be configured with at least 12 characters.');
        }

        User::query()->updateOrCreate([
            'email' => config('monitoring.admin_email'),
        ], [
            'name' => config('monitoring.admin_name'),
            'password' => $password,
            'email_verified_at' => now(),
        ]);

        foreach (array_filter(array_map('trim', explode(',', config('monitoring.subdomains', '')))) as $hostname) {
            Subdomain::updateOrCreate(
                ['name' => $hostname],
                ['url' => 'https://' . $hostname, 'enabled' => true],
            );
        }
    }
}
