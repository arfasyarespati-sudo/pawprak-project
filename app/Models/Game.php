<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Game extends Model
{
    use HasFactory;

    protected $table = 'games';

    protected $fillable = [
        'room_id',
        'status',
        'started_at',
        'finished_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    /**
     * The room this game belongs to.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    /**
     * The game_players participating in this game.
     */
    public function gamePlayers(): HasMany
    {
        return $this->hasMany(GamePlayer::class, 'game_id')->orderBy('turn_order');
    }

    /**
     * The rounds of this game.
     */
    public function rounds(): HasMany
    {
        return $this->hasMany(Round::class, 'game_id')->orderBy('round_number');
    }

    /**
     * The latest round of this game.
     */
    public function activeRound(): HasOne
    {
        return $this->hasOne(Round::class, 'game_id')->latestOfMany('round_number');
    }
}
