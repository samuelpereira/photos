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
        User::query()->firstOrCreate([
            'name' => config('site.user.name'),
            'email' => config('site.user.email'),
            'password' => config('site.user.password'),
        ]);

        if (app()->environment('local')) {
            $this->call(DevelopmentSeeder::class);
        }
    }
}
