<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'player_id',
    'email',
    'password',
    'level',
    'mmr',
    'sabotage_accuracy',
    'player_title',
    'avatar',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'level' => 'integer',
            'mmr' => 'integer',
            'sabotage_accuracy' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Player, $this>
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * @return HasMany<Lobby>
     */
    public function lobbiesHosted(): HasMany
    {
        return $this->hasMany(Lobby::class, 'host_id');
    }

    /**
     * @return HasMany<LobbyPlayer>
     */
    public function lobbyPlayers(): HasMany
    {
        return $this->hasMany(LobbyPlayer::class);
    }

    /**
     * @return HasMany<LobbyMessage>
     */
    public function lobbyMessages(): HasMany
    {
        return $this->hasMany(LobbyMessage::class);
    }

    /**
     * Get the user's display nickname.
     */
    public function getNicknameAttribute(): string
    {
        if (! empty($this->name)) {
            return $this->name;
        }

        if ($this->player && ! empty($this->player->nickname)) {
            return $this->player->nickname;
        }

        return 'Player #'.$this->id;
    }

    /**
     * Get the player's avatar url or fallback identifier.
     */
    public function getAvatarUrlAttribute(): string
    {
        if (! empty($this->avatar)) {
            return $this->avatar;
        }

        if ($this->player && ! empty($this->player->avatar)) {
            return $this->player->avatar;
        }

        // Return a default SVG or avatar slug
        return 'kucing';
    }
}
