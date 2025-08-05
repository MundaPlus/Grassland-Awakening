<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerKnownRecipe extends Model
{
    protected $fillable = [
        'player_id',
        'recipe_id',
        'learned_at',
        'discovery_method',
        'times_crafted'
    ];

    protected $casts = [
        'learned_at' => 'datetime'
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(CraftingRecipe::class, 'recipe_id');
    }
}
