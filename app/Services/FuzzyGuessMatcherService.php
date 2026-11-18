<?php

namespace App\Services;

class FuzzyGuessMatcherService
{
    /**
     * Evaluate a player's guess against the secret word using PHP's levenshtein() distance
     * and per-word token analysis.
     *
     * @return array{
     *     status: string,
     *     match_status: string,
     *     levenshtein_distance: int,
     *     similarity_percent: int,
     *     is_private_hint: bool,
     *     private_hint: ?string,
     *     public_display_text: string,
     *     guesser_points: int,
     *     artist_bonus_points: int,
     *     matched_word_positions: array<int, int>
     * }
     */
    public function evaluateGuess(
        string $secretWord,
        string $guessText,
        float $elapsedSeconds = 4.2,
        int $sabotageCount = 0,
        int $previousCorrectGuesses = 0,
    ): array {
        $normalizedSecret = $this->normalize($secretWord);
        $normalizedGuess = $this->normalize($guessText);

        if ($normalizedSecret === '' || $normalizedGuess === '') {
            return [
                'status' => 'miss',
                'match_status' => 'miss',
                'levenshtein_distance' => 99,
                'similarity_percent' => 0,
                'is_private_hint' => false,
                'private_hint' => null,
                'public_display_text' => $guessText,
                'guesser_points' => 0,
                'artist_bonus_points' => 0,
                'matched_word_positions' => [],
            ];
        }

        $distance = levenshtein(
            substr($normalizedSecret, 0, 255),
            substr($normalizedGuess, 0, 255)
        );

        $maxLength = max(strlen($normalizedSecret), strlen($normalizedGuess), 1);
        $similarity = (int) round(max(0, (1 - ($distance / $maxLength)) * 100));

        // 1. Exact Match: Penebak tercepat +350 pts, Pelukis +120 pts base + 25 pts per sabotase
        if ($normalizedSecret === $normalizedGuess) {
            $guesserPoints = match ($previousCorrectGuesses) {
                0 => 350,
                1 => max(180, (int) round((350 - ($elapsedSeconds * 2.5)) * 0.75)),
                default => max(120, (int) round((350 - ($elapsedSeconds * 2.5)) * 0.5)),
            };

            $artistBonusPoints = 120 + ($sabotageCount * 25);

            return [
                'status' => 'exact',
                'match_status' => 'exact',
                'levenshtein_distance' => 0,
                'similarity_percent' => 100,
                'is_private_hint' => true,
                'private_hint' => 'TEPAT SEKALI! Kamu berhasil menebak kata rahasia!',
                'public_display_text' => '🎉 [BERHASIL MENEBAK KATA RAHASIA!]',
                'guesser_points' => $guesserPoints,
                'artist_bonus_points' => $artistBonusPoints,
                'matched_word_positions' => range(1, count(explode(' ', $normalizedSecret))),
            ];
        }

        $secretTokens = array_values(array_filter(explode(' ', $normalizedSecret)));
        $guessTokens = array_values(array_filter(explode(' ', $normalizedGuess)));

        $matchedWordPositions = [];
        $typoWordPositions = [];

        foreach ($secretTokens as $index => $secretToken) {
            $wordPosition = $index + 1;
            foreach ($guessTokens as $guessToken) {
                if ($secretToken === $guessToken) {
                    $matchedWordPositions[] = $wordPosition;
                    break;
                }

                $tokenDist = levenshtein($secretToken, $guessToken);
                if (strlen($secretToken) >= 4 && $tokenDist > 0 && $tokenDist <= 2) {
                    $typoWordPositions[] = $wordPosition;
                    break;
                }
            }
        }

        $matchedWordPositions = array_values(array_unique($matchedWordPositions));
        $typoWordPositions = array_values(array_unique($typoWordPositions));

        // 2. Full-phrase typo tipis (jarak Levenshtein <= 2, misal "Jerapah" -> "Jrapah" / "Jerapa")
        if ($distance > 0 && $distance <= 2) {
            return [
                'status' => 'close',
                'match_status' => 'close',
                'levenshtein_distance' => $distance,
                'similarity_percent' => $similarity,
                'is_private_hint' => true,
                'private_hint' => "🔥 DIKIT LAGI BANG! Dikit lagi / Typo! Ejaanmu hampir sempurna (Levenshtein: {$distance}, kemiripan {$similarity}%).",
                'public_display_text' => $this->maskSpoilerTokens($guessText, $secretTokens),
                'guesser_points' => 0,
                'artist_bonus_points' => 0,
                'matched_word_positions' => $matchedWordPositions,
            ];
        }

        // 3. Multi-word token typo tipis (jarak Levenshtein <= 2 pada salah satu kata)
        if ($typoWordPositions !== []) {
            $posList = implode(', ', array_map(fn (int $p): string => "ke-{$p}", $typoWordPositions));

            return [
                'status' => 'close',
                'match_status' => 'close',
                'levenshtein_distance' => $distance,
                'similarity_percent' => $similarity,
                'is_private_hint' => true,
                'private_hint' => "🔥 DIKIT LAGI BANG! Dikit lagi / Typo! Kata {$posList} nyaris benar!",
                'public_display_text' => $this->maskSpoilerTokens($guessText, $secretTokens),
                'guesser_points' => 0,
                'artist_bonus_points' => 0,
                'matched_word_positions' => $matchedWordPositions,
            ];
        }

        // 4. Multi-word partial exact token match (misal "Kucing Naik Helikopter" -> ada kata yang pas)
        if ($matchedWordPositions !== []) {
            $posList = implode(' & ', array_map(fn (int $p): string => "ke-{$p}", $matchedWordPositions));

            return [
                'status' => 'close',
                'match_status' => 'close',
                'levenshtein_distance' => $distance,
                'similarity_percent' => $similarity,
                'is_private_hint' => true,
                'private_hint' => "🔥 DIKIT LAGI BANG! Dikit lagi / Typo! Tebakanmu hampir benar (kata {$posList} pas!)",
                'public_display_text' => $this->maskSpoilerTokens($guessText, $secretTokens),
                'guesser_points' => 0,
                'artist_bonus_points' => 0,
                'matched_word_positions' => $matchedWordPositions,
            ];
        }

        // 5. Miss / Tebakan jauh
        return [
            'status' => 'miss',
            'match_status' => 'miss',
            'levenshtein_distance' => $distance,
            'similarity_percent' => $similarity,
            'is_private_hint' => false,
            'private_hint' => null,
            'public_display_text' => $guessText,
            'guesser_points' => 0,
            'artist_bonus_points' => 0,
            'matched_word_positions' => [],
        ];
    }

    /**
     * Normalize text for case-insensitive Levenshtein comparison.
     */
    public function normalize(string $input): string
    {
        $lower = mb_strtolower(trim($input));
        $cleaned = preg_replace('/[^a-z0-9\s]/u', '', $lower) ?? '';

        return trim(preg_replace('/\s+/', ' ', $cleaned) ?? '');
    }

    /**
     * Sensor kata yang pas atau typo tipis (levenshtein <= 2) di chat publik agar tidak bocor ke pemain lain.
     *
     * @param  array<int, string>  $secretTokens
     */
    private function maskSpoilerTokens(string $rawGuess, array $secretTokens): string
    {
        $words = preg_split('/(\s+)/', $rawGuess, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];

        $masked = array_map(function (string $segment) use ($secretTokens): string {
            $norm = $this->normalize($segment);
            if ($norm === '') {
                return $segment;
            }

            foreach ($secretTokens as $secretToken) {
                if (strlen($secretToken) >= 3 && levenshtein($norm, $secretToken) <= 2) {
                    return substr($segment, 0, 1).str_repeat('*', max(2, strlen($segment) - 2)).substr($segment, -1);
                }
            }

            return $segment;
        }, $words);

        return implode('', $masked);
    }
}
