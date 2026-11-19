<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamePlayer extends Model
{
    use HasFactory;

    protected $table = 'game_players';

    protected $fillable = [
        'game_id',
        'player_id',
        'turn_order',
        'total_score',
        'chaos_energy',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'turn_order' => 'integer',
            'total_score' => 'integer',
            'chaos_energy' => 'integer',
        ];
    }

    /**
     * The game this record belongs to.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    /**
     * The player this record belongs to.
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }
}
