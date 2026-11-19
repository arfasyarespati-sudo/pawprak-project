<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guess extends Model
{
    use HasFactory;

    protected $table = 'guesses';

    public const UPDATED_AT = null;

    protected $fillable = [
        'round_id',
        'player_id',
        'content',
        'is_correct',
        'created_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    /**
     * The round this guess belongs to.
     */
    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class, 'round_id');
    }

    /**
     * The player who submitted this guess.
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }
}
