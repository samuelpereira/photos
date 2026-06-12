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
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DevelopmentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $competition1 = Competition::query()->create(['name' => 'Torneio Distrital']);
        $competition2 = Competition::query()->create(['name' => 'CN2']);

        $category1 = Category::query()->create(['competition_id' => $competition1->id, 'name' => 'Sub-16 M']);
        $category2 = Category::query()->create(['competition_id' => $competition1->id, 'name' => 'Sub-16 F']);
        $category3 = Category::query()->create(['competition_id' => $competition1->id, 'name' => 'Sub-14 M']);
        $category4 = Category::query()->create(['competition_id' => $competition1->id, 'name' => 'Sub-14 F']);
        $category5 = Category::query()->create(['competition_id' => $competition1->id, 'name' => 'Sub-10']);
        $category6 = Category::query()->create(['competition_id' => $competition2->id, 'name' => 'Séniores M']);

        $teamData = [
            ['AD Mealhada', 'Ventosa do Bairro', [$category1->id, $category2->id, $category3->id, $category4->id, $category5->id, $category6->id]],
            ['ACR Vale de Cambra', 'Vale de Cambra', [$category1->id]],
            ['Anadia', 'Anadia', [$category1->id]],
            ['Atómicos', 'Oliveira do Bairro', [$category1->id]],
            ['Arouca', 'Arouca', [$category1->id]],
            ['GD Gafanha', 'Gafanha da Nazaré', [$category1->id]],
            ['GICA', 'Águeda', [$category1->id]],
            ['Sangalhos DC', 'Sangalhos', [$category1->id]],
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
            ['AD Mealhada vs Anadia', 'basquetebol', '-7 days'],
            ['AD Mealhada vs Sangalhos DC', 'basquetebol', '-14 days'],
            ['AD Mealhada vs GICA', 'basquetebol', '-21 days'],
            ['Gala de Dança 2026', 'danca', '-3 weeks'],
            ['Sangalhos DC vs GICA', 'basquetebol', '-21 days'],
            ['Gala de Dança 2025', 'danca', '-5 weeks'],
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
            [$teams[0], $teams[1], $competition1, $category1],
            [$teams[1], $teams[2], $competition1, $category1],
            [$teams[2], $teams[3], $competition1, $category2],
            [$teams[3], $teams[4], $competition1, $category2],
            [$teams[4], $teams[5], $competition1, $category3],
            [$teams[5], $teams[6], $competition1, $category4],
            [$teams[6], $teams[7], $competition1, $category4],
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
            [$teams[0], $teams[2], $competition1, $category1],
            [$teams[3], $teams[0], $competition1, $category1],
            [$teams[0], $teams[1], $competition1, $category1],
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
