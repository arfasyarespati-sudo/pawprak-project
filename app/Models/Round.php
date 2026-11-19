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
     * Count words in the secret phrase.
     */
    public function wordCount(): int
    {
        $phrase = $this->word?->phrase ?? 'Kucing Naik Helikopter';
        $words = preg_split('/\s+/', trim($phrase)) ?: [];

        return count(array_filter($words, fn (string $w): bool => $w !== ''));
    }
}
