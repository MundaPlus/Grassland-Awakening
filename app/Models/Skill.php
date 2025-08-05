<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'category',
        'icon',
        'max_level',
        'requirements',
        'weapon_types',
        'effects',
        'base_cost',
        'cooldown',
        'is_enabled'
    ];

    protected $casts = [
        'requirements' => 'array',
        'weapon_types' => 'array',
        'effects' => 'array',
        'max_level' => 'integer',
        'base_cost' => 'integer',
        'cooldown' => 'integer',
        'is_enabled' => 'boolean'
    ];

    // Skill types
    const TYPE_PASSIVE = 'passive';
    const TYPE_ACTIVE = 'active';

    // Skill categories
    const CATEGORY_COMBAT = 'combat';
    const CATEGORY_CRAFTING = 'crafting';
    const CATEGORY_GATHERING = 'gathering';
    const CATEGORY_MAGIC = 'magic';
    const CATEGORY_SURVIVAL = 'survival';

    public function playerSkills(): HasMany
    {
        return $this->hasMany(PlayerSkill::class);
    }

    public function experienceSources(): HasMany
    {
        return $this->hasMany(SkillExperienceSource::class);
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'player_skills')
                    ->withPivot(['level', 'experience', 'last_used', 'times_used'])
                    ->withTimestamps();
    }

    /**
     * Check if this skill can be used with the given weapon type
     */
    public function canUseWithWeapon(string $weaponType): bool
    {
        if ($this->type !== self::TYPE_ACTIVE) {
            return false;
        }

        if (empty($this->weapon_types)) {
            return true; // Universal active skill
        }

        return in_array($weaponType, $this->weapon_types);
    }

    /**
     * Get the skill effect at a specific level
     */
    public function getEffectAtLevel(int $level): array
    {
        if (empty($this->effects)) {
            return [];
        }

        $effects = $this->effects;
        $result = [];

        foreach ($effects as $effectType => $effectData) {
            if (isset($effectData['base']) && isset($effectData['per_level'])) {
                $result[$effectType] = $effectData['base'] + ($effectData['per_level'] * ($level - 1));
            } elseif (isset($effectData['levels']) && isset($effectData['levels'][$level])) {
                $result[$effectType] = $effectData['levels'][$level];
            } elseif (isset($effectData['value'])) {
                $result[$effectType] = $effectData['value'];
            }
        }

        return $result;
    }

    /**
     * Calculate experience required for a level
     */
    public static function experienceForLevel(int $level): int
    {
        if ($level <= 1) {
            return 0;
        }
        
        // Exponential progression: level^2 * 100
        return ($level - 1) * ($level - 1) * 100;
    }

    /**
     * Calculate mana/stamina cost at a specific level
     */
    public function getCostAtLevel(int $level): int
    {
        if ($this->type !== self::TYPE_ACTIVE) {
            return 0;
        }

        // Cost scales with level but at a slower rate
        return $this->base_cost + floor($level / 5);
    }

    /**
     * Get cooldown duration accounting for level
     */
    public function getCooldownAtLevel(int $level): int
    {
        if ($this->type !== self::TYPE_ACTIVE) {
            return 0;
        }

        // Higher levels reduce cooldown slightly
        $reduction = floor($level / 10);
        return max(1, $this->cooldown - $reduction);
    }

    /**
     * Check if player meets requirements for this skill
     */
    public function meetsRequirements(Player $player): bool
    {
        if (empty($this->requirements)) {
            return true;
        }

        foreach ($this->requirements as $requirement => $value) {
            switch ($requirement) {
                case 'level':
                    if ($player->level < $value) {
                        return false;
                    }
                    break;
                case 'skills':
                    foreach ($value as $skillSlug => $requiredLevel) {
                        $playerSkill = $player->playerSkills()
                            ->whereHas('skill', function($query) use ($skillSlug) {
                                $query->where('slug', $skillSlug);
                            })->first();
                        
                        if (!$playerSkill || $playerSkill->level < $requiredLevel) {
                            return false;
                        }
                    }
                    break;
                case 'stats':
                    foreach ($value as $stat => $requiredValue) {
                        if ($player->getAttribute($stat) < $requiredValue) {
                            return false;
                        }
                    }
                    break;
            }
        }

        return true;
    }

    /**
     * Get all skills by category
     */
    public static function getByCategory(string $category): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('category', $category)
                   ->where('is_enabled', true)
                   ->orderBy('name')
                   ->get();
    }

    /**
     * Get all active skills that can be used with a weapon type
     */
    public static function getActiveSkillsForWeapon(string $weaponType): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('type', self::TYPE_ACTIVE)
                   ->where('is_enabled', true)
                   ->where(function($query) use ($weaponType) {
                       $query->whereJsonContains('weapon_types', $weaponType)
                             ->orWhereNull('weapon_types');
                   })
                   ->orderBy('name')
                   ->get();
    }
}