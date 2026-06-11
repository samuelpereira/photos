<?php

namespace Database\Factories;

use App\Models\Session;
use App\Models\SessionType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Session>
 */
class SessionFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->sentence(3);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'type_id' => fn () => SessionType::query()->inRandomOrder()->value('id')
                ?? SessionType::factory()->create()->id,
            'description' => fake()->paragraph(),
            'session_date' => fake()->dateTimeBetween('-6 months', 'now'),
            'is_published' => true,
        ];
    }
}
