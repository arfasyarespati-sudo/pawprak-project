<?php

namespace App\Services;

class SabotageDeckService
{
    /**
     * Return all available Saboteur Deck curses and their metadata.
     *
     * @return array<string, array{
     *     key: string,
     *     name: string,
     *     short_name: string,
     *     icon: string,
     *     energy_cost: int,
     *     duration_seconds: int,
     *     cooldown_seconds: int,
     *     chaos_damage: int,
     *     description: string,
     *     summary_label: string
     * }>
     */
    public function deckCatalog(): array
    {
        return [
            'earthquake' => [
                'key' => 'earthquake',
                'name' => 'Gempa Bumi',
                'short_name' => 'Gempa',
                'icon' => '🌋',
                'energy_cost' => 30,
                'duration_seconds' => 5,
                'cooldown_seconds' => 8,
                'chaos_damage' => 35,
                'description' => 'Kanvas si pelukis bergetar hebat 8.9 SR, membuat garis lurus jadi acak-acakan!',
                'summary_label' => 'Tangan Gemetar',
            ],
            'invert' => [
                'key' => 'invert',
                'name' => 'Kuas Mabuk',
                'short_name' => 'Mabuk',
                'icon' => '🌀',
                'energy_cost' => 45,
                'duration_seconds' => 4,
                'cooldown_seconds' => 10,
                'chaos_damage' => 50,
                'description' => 'Sumbu X/Y kursor pelukis terbalik selama 4 detik (geser kanan, kuas lari ke kiri)!',
                'summary_label' => 'Layar Terbalik',
            ],
            'blindfold' => [
                'key' => 'blindfold',
                'name' => 'Mati Lampu',
                'short_name' => 'Gelap',
                'icon' => '💡',
                'energy_cost' => 60,
                'duration_seconds' => 3,
                'cooldown_seconds' => 12,
                'chaos_damage' => 65,
                'description' => 'Layar pelukis gelap total selama 3 detik sementara penonton tetap bisa melihat gambarnya!',
                'summary_label' => 'Buta Mendadak',
            ],
            'sticker' => [
                'key' => 'sticker',
                'name' => 'Sticker Bomb',
                'short_name' => 'Stiker',
                'icon' => '💣',
                'energy_cost' => 25,
                'duration_seconds' => 6,
                'cooldown_seconds' => 6,
                'chaos_damage' => 30,
                'description' => 'Menempelkan stiker meme raksasa di tengah kanvas yang menutupi gambar pelukis!',
                'summary_label' => 'Teror Stiker Meme',
            ],
        ];
    }

    /**
     * Get a single curse configuration by key.
     *
     * @return ?array{
     *     key: string,
     *     name: string,
     *     short_name: string,
     *     icon: string,
     *     energy_cost: int,
     *     duration_seconds: int,
     *     cooldown_seconds: int,
     *     chaos_damage: int,
     *     description: string,
     *     summary_label: string
     * }
     */
    public function getCurse(string $curseKey): ?array
    {
        $catalog = $this->deckCatalog();

        return $catalog[$curseKey] ?? null;
    }

    /**
     * Generate random meme sticker payload for Sticker Bomb curse.
     *
     * @return array{
     *     sticker_id: string,
     *     title: string,
     *     subtitle: string,
     *     emoji: string,
     *     x_percent: int,
     *     y_percent: int,
     *     rotation_deg: int,
     *     badge_color: string
     * }
     */
    public function generateStickerBombPayload(?int $seed = null): array
    {
        $stickers = [
            [
                'sticker_id' => 'error-404-skill',
                'title' => 'ERROR 404: SKILL NOT FOUND',
                'subtitle' => 'Apakah ini kucing atau kentang penyok?',
                'emoji' => '🐱‍🚀💥',
                'badge_color' => '#FF4D6D',
            ],
            [
                'sticker_id' => 'panik-ga',
                'title' => 'PANIK GAK? PANIK GAK?!',
                'subtitle' => 'Sisa waktu makin tipis bosku!',
                'emoji' => '😱🔥',
                'badge_color' => '#B8FF3B',
            ],
            [
                'sticker_id' => 'sensor-kpi',
                'title' => 'DISENSOR KOMISI KERUSUHAN',
                'subtitle' => 'Gambar terlalu abstrak untuk mata manusia',
                'emoji' => '🙈🚧',
                'badge_color' => '#C8B6FF',
            ],
            [
                'sticker_id' => 'crash-dump',
                'title' => 'OTAK PELUKIS LAGI BUFFERING...',
                'subtitle' => 'Mohon bersabar ini ujian nasional',
                'emoji' => '🌀🦆',
                'badge_color' => '#FACC15',
            ],
        ];

        $index = $seed !== null ? abs($seed) % count($stickers) : array_rand($stickers);
        $chosen = $stickers[$index];

        return [
            'sticker_id' => $chosen['sticker_id'],
            'title' => $chosen['title'],
            'subtitle' => $chosen['subtitle'],
            'emoji' => $chosen['emoji'],
            'x_percent' => 38 + (($index * 9) % 24),
            'y_percent' => 35 + (($index * 11) % 26),
            'rotation_deg' => ($index % 2 === 0) ? -8 : 9,
            'badge_color' => $chosen['badge_color'],
        ];
    }

    /**
     * Build a human-readable curse summary string from a list of triggered curse keys.
     *
     * @param  array<int, string>  $curseKeys
     */
    public function buildSabotageSummary(array $curseKeys): string
    {
        if ($curseKeys === []) {
            return 'Tanpa Kutukan (Damai Sentosa)';
        }

        $catalog = $this->deckCatalog();
        $labels = [];

        foreach (array_unique($curseKeys) as $key) {
            if (isset($catalog[$key])) {
                $labels[] = $catalog[$key]['summary_label'];
            }
        }

        if ($labels === []) {
            return 'Tangan Gemetar & Layar Terbalik';
        }

        return implode(' & ', array_slice($labels, 0, 3));
    }
}
