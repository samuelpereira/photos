<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Competition;
use App\Models\Game;
use App\Models\Photo;
use App\Models\Session;
use App\Models\SessionType;
use App\Models\Sponsor;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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

        $liga = Competition::query()->create(['name' => 'Liga Betclic']);
        $taca = Competition::query()->create(['name' => 'Taça de Portugal']);
        $proliga = Competition::query()->create(['name' => 'Proliga']);

        $senioresM = Category::query()->create(['competition_id' => $liga->id, 'name' => 'Seniores M']);
        $senioresF = Category::query()->create(['competition_id' => $liga->id, 'name' => 'Seniores F']);
        $sub18 = Category::query()->create(['competition_id' => $taca->id, 'name' => 'Sub-18']);
        $proligaSen = Category::query()->create(['competition_id' => $proliga->id, 'name' => 'Seniores M']);

        $teamData = [
            ['CB Águias', 'Pavilhão Municipal', [$senioresM->id, $proligaSen->id]],
            ['FC Porto', 'Dragão Arena', [$senioresM->id, $senioresF->id]],
            ['SL Benfica', 'Pavilhão Fidelidade', [$senioresM->id]],
            ['Ovarense', 'Pavilhão Desportivo', [$senioresM->id, $proligaSen->id]],
            ['SC Lusitânia', 'Pavilhão Municipal', [$senioresM->id]],
            ['AD Galomar', 'Pavilhão Desportivo', [$sub18->id]],
            ['Illiabum Clube', 'Pavilhão Municipal', [$sub18->id]],
            ['Vitória SC', 'Pavilhão Desportivo', [$senioresF->id]],
        ];

        $teams = collect($teamData)->map(function (array $data) {
            [$name, $venue, $categoryIds] = $data;

            $team = Team::query()->create([
                'name' => $name,
                'venue' => $venue,
            ]);

            $team->categories()->sync($categoryIds);

            return $team;
        });

        $basquetebol = SessionType::query()->firstOrCreate(
            ['slug' => 'basquetebol'],
            ['name' => 'Basquetebol', 'color' => 'orange'],
        );

        $danca = SessionType::query()->firstOrCreate(
            ['slug' => 'danca'],
            ['name' => 'Dança', 'color' => 'fuchsia'],
        );

        $sessionNames = [
            ['CB Águias vs FC Porto', 'basquetebol', '-5 days'],
            ['Taça de Portugal: SL Benfica vs Ovarense', 'basquetebol', '-2 weeks'],
            ['Torneio de Natal Sub-18', 'basquetebol', '-1 month'],
            ['Gala de Dança 2026', 'danca', '-3 weeks'],
            ['Espetáculo "Movimento Urbano"', 'danca', '-2 months'],
            ['Liga Betclic: Jornada 20', 'basquetebol', '-6 days'],
        ];

        $sessions = collect($sessionNames)->map(function (array $data) use ($basquetebol, $danca) {
            [$name, $type, $date] = $data;

            $session = Session::factory()->create([
                'name' => $name,
                'slug' => Str::slug($name),
                'type_id' => $type === 'danca' ? $danca->id : $basquetebol->id,
                'session_date' => fake()->dateTimeBetween($date, $date),
            ]);

            Photo::factory()
                ->count(fake()->numberBetween(8, 12))
                ->sequence(fn ($sequence) => ['sort_order' => $sequence->index])
                ->create(['session_id' => $session->id]);

            $session->photos()->first()->update(['is_cover' => true]);

            return $session;
        });

        $sessions->first()->photos()->skip(1)->first()->update(['is_featured' => true]);

        $basketSessions = $sessions->filter(fn (Session $session) => $session->type_id === $basquetebol->id)->values();

        $gamePairs = [
            [$teams[0], $teams[1], $liga, $senioresM],
            [$teams[2], $teams[3], $liga, $senioresM],
            [$teams[4], $teams[0], $liga, $senioresM],
            [$teams[1], $teams[2], $taca, $senioresM],
            [$teams[5], $teams[6], $taca, $sub18],
            [$teams[3], $teams[4], $proliga, $proligaSen],
            [$teams[7], $teams[1], $liga, $senioresF],
            [$teams[0], $teams[3], $liga, $senioresM],
        ];

        foreach ($gamePairs as $index => [$home, $away, $competition, $category]) {
            Game::query()->create([
                'session_id' => $basketSessions[$index % $basketSessions->count()]->id ?? null,
                'competition_id' => $competition->id,
                'category_id' => $category->id,
                'home_team_id' => $home->id,
                'away_team_id' => $away->id,
                'home_team' => $home->name,
                'away_team' => $away->name,
                'competition' => $competition->name,
                'category' => $category->name,
                'venue' => $home->venue,
                'tip_off_at' => now()->subDays(($index + 1) * 3)->setTime(18, 0),
                'home_score' => fake()->numberBetween(55, 110),
                'away_score' => fake()->numberBetween(55, 110),
            ]);
        }

        $upcomingPairs = [
            [$teams[0], $teams[2], $liga, $senioresM],
            [$teams[1], $teams[4], $liga, $senioresM],
            [$teams[6], $teams[5], $taca, $sub18],
        ];

        foreach ($upcomingPairs as $index => [$home, $away, $competition, $category]) {
            Game::query()->create([
                'competition_id' => $competition->id,
                'category_id' => $category->id,
                'home_team_id' => $home->id,
                'away_team_id' => $away->id,
                'home_team' => $home->name,
                'away_team' => $away->name,
                'competition' => $competition->name,
                'category' => $category->name,
                'venue' => $home->venue,
                'tip_off_at' => now()->addDays($index + 1)->setTime(18, 0),
            ]);
        }

        $sponsorExamples = [
            ['Intermarché', 'https://www.intermarche.pt'],
            ['McDonald\'s', 'https://www.mcdonalds.pt'],
            ['Pingo Doce', 'https://www.pingodoce.pt'],
        ];

        foreach ($sponsorExamples as $index => [$name, $url]) {
            Sponsor::query()->firstOrCreate(
                ['name' => $name],
                [
                    'url' => $url,
                    'sort_order' => $index,
                    'is_active' => true,
                ],
            );
        }
    }
}
