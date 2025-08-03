<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    protected $fillable = [
        'user_id',
        'character_name',
        'level',
        'experience',
        'persistent_currency',
        'hp',
        'max_hp',
        'ac',
        'str',
        'dex',
        'con',
        'int',
        'wis',
        'cha',
        'unallocated_stat_points',
        'current_road',
        'current_level',
        'current_node_id'
    ];

    protected $casts = [
        'level' => 'integer',
        'experience' => 'integer',
        'persistent_currency' => 'integer',
        'hp' => 'integer',
        'max_hp' => 'integer',
        'ac' => 'integer',
        'str' => 'integer',
        'dex' => 'integer',
        'con' => 'integer',
        'int' => 'integer',
        'wis' => 'integer',
        'cha' => 'integer',
        'unallocated_stat_points' => 'integer',
        'current_level' => 'integer'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function adventures(): HasMany
    {
        return $this->hasMany(Adventure::class);
    }

    public function activeAdventure(): ?Adventure
    {
        return $this->adventures()->where('status', 'active')->first();
    }

    public function getStatModifier(string $stat): int
    {
        $statValue = $this->getAttribute($stat);
        return floor(($statValue - 10) / 2);
    }

    public function calculateExperienceToNextLevel(): int
    {
        return $this->level * 100;
    }

    public function canLevelUp(): bool
    {
        return $this->experience >= $this->calculateExperienceToNextLevel();
    }

    public function levelUp(): void
    {
        if ($this->canLevelUp()) {
            $this->level++;
            $this->experience -= $this->calculateExperienceToNextLevel();
            $this->unallocated_stat_points += 2;
            $this->max_hp += 5 + $this->getStatModifier('con');
            $this->hp = $this->max_hp;
            $this->save();
        }
    }

    public function allocateStatPoint(string $stat): bool
    {
        if ($this->unallocated_stat_points > 0 && in_array($stat, ['str', 'dex', 'con', 'int', 'wis', 'cha'])) {
            $this->increment($stat);
            $this->decrement('unallocated_stat_points');
            
            if ($stat === 'con') {
                $this->increment('max_hp');
            }
            
            return true;
        }
        
        return false;
    }

    public function isInVillage(): bool
    {
        return is_null($this->current_road);
    }

    public function getCurrentPosition(): array
    {
        if ($this->isInVillage()) {
            return ['location' => 'village'];
        }
        
        return [
            'location' => 'adventure',
            'road' => $this->current_road,
            'level' => $this->current_level,
            'node_id' => $this->current_node_id
        ];
    }

    public function npcs(): HasMany
    {
        return $this->hasMany(NPC::class);
    }

    public function villageSpecializations(): HasMany
    {
        return $this->hasMany(VillageSpecialization::class);
    }

    public function getVillageInfo(): array
    {
        $npcService = app(\App\Services\NPCService::class);
        return $npcService->getVillageInfo($this);
    }

    public function getSettledNPCs()
    {
        return $this->npcs()->where('village_status', 'settled')->get();
    }

    public function getMigratingNPCs()
    {
        return $this->npcs()->where('village_status', 'migrating')->get();
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }

    public function factionReputations(): HasMany
    {
        return $this->hasMany(FactionReputation::class);
    }
}
