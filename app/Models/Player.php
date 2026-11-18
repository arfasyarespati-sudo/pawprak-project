<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    use HasFactory;

    protected $table = 'players';

    protected $fillable = [
        'nickname',
        'avatar',
        'guest_token_hash',
    ];

    /**
     * Rooms hosted by this player.
     */
    public function hostedRooms(): HasMany
    {
        return $this->hasMany(Room::class, 'host_player_id');
    }

    /**
     * Room memberships for this player.
     */
    public function roomMemberships(): HasMany
    {
        return $this->hasMany(RoomPlayer::class, 'player_id');
    }

    /**
     * Game participations for this player.
     */
    public function gamePlayers(): HasMany
    {
        return $this->hasMany(GamePlayer::class, 'player_id');
    }

    /**
     * Rounds where this player was the artist.
     */
    public function artistRounds(): HasMany
    {
        return $this->hasMany(Round::class, 'artist_player_id');
    }

    /**
     * Guesses made by this player.
     */
    public function guesses(): HasMany
    {
        return $this->hasMany(Guess::class, 'player_id');
    }

    /**
     * Curses used by this player.
     */
    public function curseUses(): HasMany
    {
        return $this->hasMany(CurseUse::class, 'player_id');
    }

    /**
     * Round points earned by this player.
     */
    public function roundPoints(): HasMany
    {
        return $this->hasMany(RoundPoint::class, 'player_id');
    }
}
