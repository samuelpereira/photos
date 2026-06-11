<?php

namespace Database\Factories;

use App\Models\SessionType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SessionType>
 */
class SessionTypeFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'color' => fake()->randomElement(['orange', 'fuchsia', 'zinc']),
        ];
    }
}
