<?php

namespace Database\Factories;

use App\Models\Photo;
use App\Models\Session;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Photo>
 */
class PhotoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'session_id' => Session::factory(),
            'path' => 'https://picsum.photos/seed/'.fake()->unique()->numberBetween(1, 100000).'/1600/1067',
            'caption' => fake()->optional()->sentence(),
            'sort_order' => 0,
            'is_cover' => false,
            'is_featured' => false,
        ];
    }
}
