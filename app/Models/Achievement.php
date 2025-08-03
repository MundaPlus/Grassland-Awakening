<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Achievement extends Model
{
    protected $fillable = [
        'player_id',
        'achievement_id',
        'name',
        'description',
        'category',
        'points',
        'icon',
        'unlocked_at'
    ];

    protected $casts = [
        'unlocked_at' => 'datetime',
        'points' => 'integer'
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
