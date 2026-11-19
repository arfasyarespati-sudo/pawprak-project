<?php

namespace App\Models;

use Database\Factories\LobbyPlayerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'lobby_id',
    'user_id',
    'slot_number',
    'is_ready',
    'selected_avatar',
])]
class LobbyPlayer extends Model
{
    /** @use HasFactory<LobbyPlayerFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_ready' => 'boolean',
            'slot_number' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Lobby, $this>
     */
    public function lobby(): BelongsTo
    {
        return $this->belongsTo(Lobby::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this player is the host of their lobby.
     */
    public function isHost(): bool
    {
        return (int) $this->user_id === (int) $this->lobby?->host_id;
    }
}
