<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Round extends Model
{
    use HasFactory;

    protected $table = 'rounds';

    protected $fillable = [
        'game_id',
        'artist_player_id',
        'word_id',
        'round_number',
        'status',
        'started_at',
        'ended_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'round_number' => 'integer',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    /**
     * The game this round belongs to.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    /**
     * The artist (The Victim) for this round.
     */
    public function artist(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'artist_player_id');
    }

    /**
     * The secret word for this round.
     */
    public function word(): BelongsTo
    {
        return $this->belongsTo(Word::class, 'word_id');
    }

    /**
     * Guesses submitted in this round.
     */
    public function guesses(): HasMany
    {
        return $this->hasMany(Guess::class, 'round_id')->orderBy('id');
    }

    /**
     * Curses activated in this round.
     */
    public function curseUses(): HasMany
    {
        return $this->hasMany(CurseUse::class, 'round_id')->orderBy('id');
    }

    /**
     * Canvas strokes recorded in this round.
     */
    public function strokes(): HasMany
    {
        return $this->hasMany(Stroke::class, 'round_id')->orderBy('sequence');
    }

    /**
     * Points awarded in this round.
     */
    public function roundPoints(): HasMany
    {
        return $this->hasMany(RoundPoint::class, 'round_id');
    }

    /**
     * The Hall of Shame artwork exported for this round (1-to-1).
     */
    public function artwork(): HasOne
    {
        return $this->hasOne(Artwork::class, 'round_id');
    }

    /**
     * Generate masked word hint (e.g. "_ _ _ _ _ _ / _ _ _ _ / _ _ _ _ _ _ _ _ _ _").
     */
    public function maskedPhrase(): string
    {
        $phrase = $this->word?->phrase ?? 'Kucing Naik Helikopter';
        $words = preg_split('/\s+/', trim($phrase)) ?: [];
        $maskedWords = array_map(function (string $w): string {
            $len = mb_strlen($w);

            return implode(' ', array_fill(0, max(1, $len), '_'));
        }, $words);

        return implode('   /   ', $maskedWords);
    }

    /**
     * Generate deterministic progressive letter hints based on round elapsed time.
     * Reveals 1 letter per 25% of round duration (min 10s interval), capped at half the letters (max 3).
     *
     * @return array{
     *     masked_word: string,
     *     revealed_count: int,
     *     max_reveals: int,
     *     interval_seconds: int,
     *     next_hint_in_seconds: ?int
     * }
     */
    public function maskedPhraseWithHints(int $roundDuration = 60): array
    {
        $phrase = trim($this->word?->phrase ?? 'Apel');
        $words = preg_split('/\s+/', $phrase) ?: [];
        $splitWords = [];
        $positions = [];
        $flatIndex = 0;

        foreach ($words as $wordIdx => $wordText) {
            if ($wordText === '') {
                continue;
            }
            $chars = mb_str_split($wordText);
            $splitWords[$wordIdx] = $chars;
            foreach ($chars as $charIdx => $char) {
                if ($char !== '-') {
                    $positions[] = [
                        'word' => $wordIdx,
                        'char' => $charIdx,
                        'flat' => $flatIndex,
                    ];
                }
                $flatIndex++;
            }
        }

        $letterCount = count($positions);
        $safeDuration = max(20, $roundDuration);
        $intervalSeconds = max(10, (int) floor($safeDuration / 4));
        $maxReveals = $letterCount <= 1 ? 0 : min(3, max(1, (int) floor($letterCount / 2)));

        $elapsedSeconds = ($this->status === 'drawing' && $this->started_at !== null)
            ? max(0, now()->getTimestamp() - $this->started_at->getTimestamp())
            : 0;

        $revealedCount = $maxReveals > 0
            ? min($maxReveals, (int) floor($elapsedSeconds / $intervalSeconds))
            : 0;

        $roundSeed = (int) ($this->id ?? 0);
        if ($revealedCount > 0 && $positions !== []) {
            usort($positions, static function (array $a, array $b) use ($roundSeed): int {
                return crc32("round_hint_{$roundSeed}_{$a['flat']}") <=> crc32("round_hint_{$roundSeed}_{$b['flat']}");
            });
        }

        $revealedLookup = [];
        for ($i = 0; $i < $revealedCount && $i < count($positions); $i++) {
            $pos = $positions[$i];
            $revealedLookup[$pos['word'].':'.$pos['char']] = true;
        }

        $maskedWords = [];
        foreach ($splitWords as $wordIdx => $chars) {
            $tokens = [];
            foreach ($chars as $charIdx => $char) {
                if ($char === '-' || isset($revealedLookup[$wordIdx.':'.$charIdx])) {
                    $tokens[] = mb_strtoupper($char);
                } else {
                    $tokens[] = '_';
                }
            }
            $maskedWords[] = implode(' ', $tokens);
        }

        $nextHintInSeconds = ($revealedCount < $maxReveals && $this->status === 'drawing')
            ? max(1, (($revealedCount + 1) * $intervalSeconds) - $elapsedSeconds)
            : null;

        return [
            'masked_word' => $maskedWords !== [] ? implode('   /   ', $maskedWords) : '_ _ _ _',
            'revealed_count' => $revealedCount,
            'max_reveals' => $maxReveals,
            'interval_seconds' => $intervalSeconds,
            'next_hint_in_seconds' => $nextHintInSeconds,
        ];
    }

    /**
     * Count words in the secret phrase.
     */
    public function wordCount(): int
    {
        $phrase = $this->word?->phrase ?? 'Kucing Naik Helikopter';
        $words = preg_split('/\s+/', trim($phrase)) ?: [];

        return count(array_filter($words, fn (string $w): bool => $w !== ''));
    }
}
