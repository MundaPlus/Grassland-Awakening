<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faction extends Model
{
    protected $fillable = [
        'name',
        'description',
        'icon',
        'category'
    ];

    public function factionReputations(): HasMany
    {
        return $this->hasMany(FactionReputation::class);
    }
}
