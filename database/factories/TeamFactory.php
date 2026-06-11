<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'CB Águias', 'SC Lusitânia', 'AD Galomar', 'CAB Madeira',
                'Illiabum Clube', 'Ovarense', 'Esgueira Aveiro', 'Sangalhos DC',
                'FC Porto', 'SL Benfica', 'Vitória SC', 'CD Póvoa',
            ]),
            'venue' => fake()->randomElement([
                'Pavilhão Municipal',
                'Dragão Arena',
                'Pavilhão Fidelidade',
                'Pavilhão Desportivo',
            ]),
        ];
    }
}
