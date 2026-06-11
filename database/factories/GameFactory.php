<?php

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    private const array TEAMS = [
        'CB Águias', 'SC Lusitânia', 'AD Galomar', 'CAB Madeira',
        'Illiabum Clube', 'Ovarense', 'Esgueira Aveiro', 'Sangalhos DC',
        'FC Porto', 'SL Benfica', 'Vitória SC', 'CD Póvoa',
    ];

    public function definition(): array
    {
        $teams = fake()->randomElements(self::TEAMS, 2);

        return [
            'session_id' => null,
            'home_team' => $teams[0],
            'away_team' => $teams[1],
            'competition' => fake()->randomElement(['Liga Betclic', 'Proliga', 'Taça de Portugal', 'CNB1']),
            'category' => fake()->randomElement(['Seniores M', 'Seniores F', 'Sub-18', 'Sub-16']),
            'venue' => fake()->randomElement(['Pavilhão Municipal', 'Dragão Arena', 'Pavilhão Fidelidade', 'Pavilhão Desportivo']),
            'tip_off_at' => fake()->dateTimeBetween('-3 months', '+2 weeks'),
            'home_score' => null,
            'away_score' => null,
        ];
    }

    public function past(): static
    {
        return $this->state(fn () => [
            'tip_off_at' => fake()->dateTimeBetween('-3 months', '-1 day'),
            'home_score' => fake()->numberBetween(55, 110),
            'away_score' => fake()->numberBetween(55, 110),
        ]);
    }

    public function upcoming(): static
    {
        return $this->state(fn () => [
            'tip_off_at' => fake()->dateTimeBetween('+1 hour', '+7 days'),
        ]);
    }
}
