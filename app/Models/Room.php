<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';

    protected $fillable = [
        'code',
        'host_player_id',
        'status',
        'settings',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    /**
     * The host player of this room.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'host_player_id');
    }

    /**
     * The room_players records for this room.
     */
    public function roomPlayers(): HasMany
    {
        return $this->hasMany(RoomPlayer::class, 'room_id');
    }

    /**
     * The games played in this room.
     */
    public function games(): HasMany
    {
        return $this->hasMany(Game::class, 'room_id');
    }

    /**
     * The latest active game in this room.
     */
    public function activeGame(): HasOne
    {
        return $this->hasOne(Game::class, 'room_id')->latestOfMany();
    }
}
