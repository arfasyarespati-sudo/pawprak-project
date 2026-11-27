<?php

namespace App\Models;

use Database\Factories\LobbyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code',
    'host_id',
    'max_players',
    'round_duration',
    'game_mode',
    'word_packs',
    'is_public',
    'status',
])]
class Lobby extends Model
{
    /** @use HasFactory<LobbyFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'max_players' => 'integer',
            'round_duration' => 'integer',
            'word_packs' => 'array',
            'is_public' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    /**
     * @return HasMany<LobbyPlayer>
     */
    public function players(): HasMany
    {
        return $this->hasMany(LobbyPlayer::class)->orderBy('slot_number');
    }

    /**
     * @return BelongsToMany<User>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'lobby_players');
    }

    /**
     * @return HasMany<LobbyMessage>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(LobbyMessage::class)->orderBy('created_at', 'asc');
    }

    /**
     * Check if a given user is the host of this lobby.
     */
    public function isHost(User|int|null $user): bool
    {
        if (! $user) {
            return false;
        }

        $userId = $user instanceof User ? $user->id : $user;

        return (int) $this->host_id === (int) $userId;
    }

    /**
     * Check if the lobby is full.
     */
    public function isFull(): bool
    {
        return $this->players()->count() >= $this->max_players;
    }

    /**
     * Check if the lobby meets minimum requirements to start (at least 2 players).
     */
    public function canStart(): bool
    {
        return $this->players()->count() >= 2;
    }

    /**
     * Count how many players are currently ready (including host who is always ready).
     */
    public function readyCount(): int
    {
        return $this->players()->where('is_ready', true)->count();
    }

    /**
     * Find the lowest available slot number (1 to max_players).
     */
    public function availableSlot(): ?int
    {
        $takenSlots = $this->players()->pluck('slot_number')->all();

        for ($slot = 1; $slot <= $this->max_players; $slot++) {
            if (! in_array($slot, $takenSlots, true)) {
                return $slot;
            }
        }

        return null;
    }

    /**
     * Generate a unique 4-character uppercase lobby code.
     * Excludes ambiguous characters (0, O, 1, I, L).
     */
    public static function generateUniqueCode(): string
    {
        $pool = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

        do {
            $code = '';
            for ($i = 0; $i < 4; $i++) {
                $code .= $pool[random_int(0, strlen($pool) - 1)];
            }
        } while (static::where('code', $code)->exists());

        return $code;
    }
}
