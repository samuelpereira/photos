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
        $email = config('site.user.email');

        if (is_string($email) && $email !== '') {
            User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => (string) config('site.user.name'),
                    'password' => (string) config('site.user.password'),
                ],
            );
        }

        if (app()->environment('local')) {
            $this->call(DevelopmentSeeder::class);
        }
    }
}
