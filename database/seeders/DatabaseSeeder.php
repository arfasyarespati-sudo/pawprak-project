<?php

namespace Database\Seeders;

use App\Models\Curse;
use App\Models\Word;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed only master catalog tables (`curses` and fallback `words`).
     * No dummy players, rooms, games, rounds, chats, or fake museum artworks are seeded.
     */
    public function run(): void
    {
        $cursesData = [
            ['slug' => 'earthquake', 'name' => 'Gempa Bumi', 'energy_cost' => 30, 'duration_ms' => 5000, 'cooldown_ms' => 5000],
            ['slug' => 'invert', 'name' => 'Kuas Mabuk', 'energy_cost' => 45, 'duration_ms' => 4000, 'cooldown_ms' => 5000],
            ['slug' => 'blindfold', 'name' => 'Mati Lampu', 'energy_cost' => 60, 'duration_ms' => 3000, 'cooldown_ms' => 5000],
            ['slug' => 'sticker', 'name' => 'Sticker Bomb', 'energy_cost' => 25, 'duration_ms' => 6000, 'cooldown_ms' => 5000],
        ];

        foreach ($cursesData as $c) {
            Curse::updateOrCreate(['slug' => $c['slug']], $c);
        }

        $wordsData = [
            ['phrase' => 'Kucing Naik Helikopter', 'category' => 'Bebas', 'difficulty' => 'medium'],
            ['phrase' => 'Jerapah', 'category' => 'Flora & Fauna', 'difficulty' => 'easy'],
        ];

        foreach ($wordsData as $w) {
            Word::updateOrCreate(['phrase' => $w['phrase']], $w);
        }
    }
}
