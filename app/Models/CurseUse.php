<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurseUse extends Model
{
    use HasFactory;

    protected $table = 'curse_uses';

    public $timestamps = false;

    protected $fillable = [
        'round_id',
        'player_id',
        'curse_id',
        'payload',
        'activated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'activated_at' => 'datetime',
        ];
    }

    /**
     * The round this curse was used in.
     */
    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class, 'round_id');
    }

    /**
     * The saboteur player who triggered this curse.
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    /**
     * The curse definition.
     */
    public function curse(): BelongsTo
    {
        return $this->belongsTo(Curse::class, 'curse_id');
    }
}
