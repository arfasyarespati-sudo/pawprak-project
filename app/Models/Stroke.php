<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stroke extends Model
{
    use HasFactory;

    protected $table = 'strokes';

    public const UPDATED_AT = null;

    protected $fillable = [
        'round_id',
        'sequence',
        'color',
        'brush_size',
        'points',
        'started_at_ms',
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
            'sequence' => 'integer',
            'brush_size' => 'integer',
            'points' => 'array',
            'started_at_ms' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    /**
     * The round this stroke belongs to.
     */
    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class, 'round_id');
    }
}
