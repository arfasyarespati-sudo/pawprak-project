<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curse extends Model
{
    use HasFactory;

    protected $table = 'curses';

    protected $fillable = [
        'slug',
        'name',
        'energy_cost',
        'duration_ms',
        'cooldown_ms',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'energy_cost' => 'integer',
            'duration_ms' => 'integer',
            'cooldown_ms' => 'integer',
        ];
    }

    /**
     * Uses of this curse across rounds.
     */
    public function curseUses(): HasMany
    {
        return $this->hasMany(CurseUse::class, 'curse_id');
    }
}
