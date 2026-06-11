<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Competition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'competition_id' => Competition::factory(),
            'name' => fake()->randomElement(['Seniores M', 'Seniores F', 'Sub-18', 'Sub-16']),
        ];
    }
}
