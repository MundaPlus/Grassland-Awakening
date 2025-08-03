<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactionReputation extends Model
{
    protected $fillable = [
        'player_id',
        'faction_id',
        'faction_name',
        'reputation_score'
    ];

    protected $casts = [
        'reputation_score' => 'integer'
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }
}
