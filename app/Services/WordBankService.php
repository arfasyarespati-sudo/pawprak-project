<?php

namespace App\Services;

use App\Models\Curse;
use App\Models\Word;

class WordBankService
{
    /**
     * Profanity filter list for custom secret words and nicknames.
     *
     * @var array<int, string>
     */
    private const PROFANITY_LIST = [
        'anjing', 'anjir', 'babi', 'bangsat', 'bajingan', 'kontol',
        'memek', 'jembut', 'ngentot', 'tolol', 'goblok', 'idiot',
        'asu', 'jancok', 'cok', 'kampret', 'keparat', 'perek', 'lonte',
    ];

    /**
     * Curated simple, universal everyday words that are easy to draw and guess.
     *
     * @var array<int, array{phrase: string, category: string, difficulty: string}>
     */
    private const SIMPLE_WORD_BANK = [
        // Buah & Makanan
        ['phrase' => 'Apel', 'category' => 'Buah & Makanan', 'difficulty' => 'easy'],
        ['phrase' => 'Pisang', 'category' => 'Buah & Makanan', 'difficulty' => 'easy'],
        ['phrase' => 'Semangka', 'category' => 'Buah & Makanan', 'difficulty' => 'easy'],
        ['phrase' => 'Anggur', 'category' => 'Buah & Makanan', 'difficulty' => 'easy'],
        ['phrase' => 'Jeruk', 'category' => 'Buah & Makanan', 'difficulty' => 'easy'],
        ['phrase' => 'Stroberi', 'category' => 'Buah & Makanan', 'difficulty' => 'easy'],
        ['phrase' => 'Wortel', 'category' => 'Buah & Makanan', 'difficulty' => 'easy'],
        ['phrase' => 'Roti', 'category' => 'Buah & Makanan', 'difficulty' => 'easy'],
        ['phrase' => 'Telur', 'category' => 'Buah & Makanan', 'difficulty' => 'easy'],
        ['phrase' => 'Pizza', 'category' => 'Buah & Makanan', 'difficulty' => 'easy'],
        ['phrase' => 'Kue', 'category' => 'Buah & Makanan', 'difficulty' => 'easy'],
        ['phrase' => 'Es Krim', 'category' => 'Buah & Makanan', 'difficulty' => 'easy'],
        // Hewan
        ['phrase' => 'Kucing', 'category' => 'Hewan', 'difficulty' => 'easy'],
        ['phrase' => 'Anjing', 'category' => 'Hewan', 'difficulty' => 'easy'],
        ['phrase' => 'Ikan', 'category' => 'Hewan', 'difficulty' => 'easy'],
        ['phrase' => 'Burung', 'category' => 'Hewan', 'difficulty' => 'easy'],
        ['phrase' => 'Bebek', 'category' => 'Hewan', 'difficulty' => 'easy'],
        ['phrase' => 'Kelinci', 'category' => 'Hewan', 'difficulty' => 'easy'],
        ['phrase' => 'Gajah', 'category' => 'Hewan', 'difficulty' => 'easy'],
        ['phrase' => 'Jerapah', 'category' => 'Hewan', 'difficulty' => 'easy'],
        ['phrase' => 'Kupu-kupu', 'category' => 'Hewan', 'difficulty' => 'easy'],
        ['phrase' => 'Ular', 'category' => 'Hewan', 'difficulty' => 'easy'],
        ['phrase' => 'Kura-kura', 'category' => 'Hewan', 'difficulty' => 'easy'],
        ['phrase' => 'Ayam', 'category' => 'Hewan', 'difficulty' => 'easy'],
        // Umum & Benda
        ['phrase' => 'Rumah', 'category' => 'Umum & Benda', 'difficulty' => 'easy'],
        ['phrase' => 'Kursi', 'category' => 'Umum & Benda', 'difficulty' => 'easy'],
        ['phrase' => 'Meja', 'category' => 'Umum & Benda', 'difficulty' => 'easy'],
        ['phrase' => 'Buku', 'category' => 'Umum & Benda', 'difficulty' => 'easy'],
        ['phrase' => 'Pensil', 'category' => 'Umum & Benda', 'difficulty' => 'easy'],
        ['phrase' => 'Jam', 'category' => 'Umum & Benda', 'difficulty' => 'easy'],
        ['phrase' => 'Lampu', 'category' => 'Umum & Benda', 'difficulty' => 'easy'],
        ['phrase' => 'Payung', 'category' => 'Umum & Benda', 'difficulty' => 'easy'],
        ['phrase' => 'Topi', 'category' => 'Umum & Benda', 'difficulty' => 'easy'],
        ['phrase' => 'Sepatu', 'category' => 'Umum & Benda', 'difficulty' => 'easy'],
        ['phrase' => 'Kacamata', 'category' => 'Umum & Benda', 'difficulty' => 'easy'],
        ['phrase' => 'Tas', 'category' => 'Umum & Benda', 'difficulty' => 'easy'],
        ['phrase' => 'Kunci', 'category' => 'Umum & Benda', 'difficulty' => 'easy'],
        ['phrase' => 'Gunting', 'category' => 'Umum & Benda', 'difficulty' => 'easy'],
        ['phrase' => 'Gelas', 'category' => 'Umum & Benda', 'difficulty' => 'easy'],
        ['phrase' => 'Sendok', 'category' => 'Umum & Benda', 'difficulty' => 'easy'],
        ['phrase' => 'Bola', 'category' => 'Umum & Benda', 'difficulty' => 'easy'],
        ['phrase' => 'Gitar', 'category' => 'Umum & Benda', 'difficulty' => 'easy'],
        ['phrase' => 'Kamera', 'category' => 'Umum & Benda', 'difficulty' => 'easy'],
        ['phrase' => 'Balon', 'category' => 'Umum & Benda', 'difficulty' => 'easy'],
        // Alam & Kendaraan
        ['phrase' => 'Matahari', 'category' => 'Alam & Kendaraan', 'difficulty' => 'easy'],
        ['phrase' => 'Bulan', 'category' => 'Alam & Kendaraan', 'difficulty' => 'easy'],
        ['phrase' => 'Bintang', 'category' => 'Alam & Kendaraan', 'difficulty' => 'easy'],
        ['phrase' => 'Awan', 'category' => 'Alam & Kendaraan', 'difficulty' => 'easy'],
        ['phrase' => 'Pohon', 'category' => 'Alam & Kendaraan', 'difficulty' => 'easy'],
        ['phrase' => 'Bunga', 'category' => 'Alam & Kendaraan', 'difficulty' => 'easy'],
        ['phrase' => 'Gunung', 'category' => 'Alam & Kendaraan', 'difficulty' => 'easy'],
        ['phrase' => 'Mobil', 'category' => 'Alam & Kendaraan', 'difficulty' => 'easy'],
        ['phrase' => 'Sepeda', 'category' => 'Alam & Kendaraan', 'difficulty' => 'easy'],
        ['phrase' => 'Pesawat', 'category' => 'Alam & Kendaraan', 'difficulty' => 'easy'],
        ['phrase' => 'Kapal', 'category' => 'Alam & Kendaraan', 'difficulty' => 'easy'],
        ['phrase' => 'Kereta', 'category' => 'Alam & Kendaraan', 'difficulty' => 'easy'],
    ];

    public function __construct(
        public SabotageDeckService $sabotageDeck,
    ) {}

    /**
     * Check if text contains profanity.
     */
    public function containsProfanity(string $text): bool
    {
        $lower = mb_strtolower($text);
        foreach (self::PROFANITY_LIST as $badWord) {
            if (preg_match('/\b'.preg_quote($badWord, '/').'\b/u', $lower) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Censor profanity words in chat or nicknames.
     */
    public function sanitizeText(string $text): string
    {
        $cleaned = trim($text);
        foreach (self::PROFANITY_LIST as $badWord) {
            $cleaned = (string) preg_replace(
                '/\b'.preg_quote($badWord, '/').'\b/iu',
                str_repeat('*', strlen($badWord)),
                $cleaned
            );
        }

        return $cleaned;
    }

    /**
     * Clean a custom secret word (2-40 chars).
     */
    public function sanitizeSecretWord(string $word): string
    {
        $cleaned = preg_replace('/[^\p{L}\p{N}\s\-]/u', '', trim($word)) ?? '';
        $cleaned = preg_replace('/\s+/', ' ', $cleaned) ?? '';

        return mb_substr($cleaned, 0, 40);
    }

    /**
     * Ensure base `words` and `curses` rows exist in the database.
     */
    public function ensureBaseWordsAndCursesExist(): void
    {
        $existingPhrases = Word::pluck('phrase')->flip()->all();
        $now = now();
        $rowsToInsert = [];

        foreach (self::SIMPLE_WORD_BANK as $entry) {
            if (! isset($existingPhrases[$entry['phrase']])) {
                $rowsToInsert[] = [
                    'phrase' => $entry['phrase'],
                    'category' => $entry['category'],
                    'difficulty' => $entry['difficulty'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($rowsToInsert !== []) {
            Word::insert($rowsToInsert);
        }

        foreach ($this->sabotageDeck->deckCatalog() as $curse) {
            Curse::firstOrCreate(
                ['slug' => $curse['key']],
                [
                    'name' => $curse['name'],
                    'energy_cost' => $curse['energy_cost'],
                    'duration_ms' => $curse['duration_seconds'] * 1000,
                    'cooldown_ms' => 5000,
                ]
            );
        }
    }

    /**
     * Pick a random simple, universal word from the curated bank.
     *
     * @param  array<int, string>|null  $preferredCategories
     */
    public function pickRandomSimpleWord(?string $excludePhrase = null, ?array $preferredCategories = null): Word
    {
        $pool = self::SIMPLE_WORD_BANK;

        if ($preferredCategories !== null && $preferredCategories !== []) {
            $filtered = array_values(array_filter(
                $pool,
                fn (array $item): bool => in_array($item['category'], $preferredCategories, true)
            ));
            if ($filtered !== []) {
                $pool = $filtered;
            }
        }

        if ($excludePhrase !== null && $excludePhrase !== '' && count($pool) > 1) {
            $withoutExcluded = array_values(array_filter(
                $pool,
                fn (array $item): bool => mb_strtolower($item['phrase']) !== mb_strtolower($excludePhrase)
            ));
            if ($withoutExcluded !== []) {
                $pool = $withoutExcluded;
            }
        }

        $chosen = $pool[array_rand($pool)];

        return Word::firstOrCreate(
            ['phrase' => $chosen['phrase']],
            ['category' => $chosen['category'], 'difficulty' => $chosen['difficulty']]
        );
    }
}
