<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NPCSkill extends Model
{
    protected $table = 'n_p_c_skills';
    
    protected $fillable = [
        'npc_id',
        'skill_tree',
        'skill_name',
        'level',
        'abilities_json'
    ];

    protected $casts = [
        'level' => 'integer',
        'abilities_json' => 'array'
    ];

    public function npc(): BelongsTo
    {
        return $this->belongsTo(NPC::class);
    }

    public function getAbilities(): array
    {
        return $this->abilities_json ?? [];
    }

    public function hasAbility(string $ability): bool
    {
        $abilities = $this->getAbilities();
        return isset($abilities[$ability]);
    }

    public function getAbilityValue(string $ability): mixed
    {
        $abilities = $this->getAbilities();
        return $abilities[$ability] ?? null;
    }

    public function isMaxLevel(): bool
    {
        return $this->level >= 5; // Max skill level
    }

    public function canUpgrade(): bool
    {
        return !$this->isMaxLevel();
    }

    public function getUpgradeCost(): int
    {
        return $this->level * 50; // Cost scales with level
    }
}
