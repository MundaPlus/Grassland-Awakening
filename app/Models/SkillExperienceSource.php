<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkillExperienceSource extends Model
{
    protected $fillable = [
        'skill_id',
        'source_type',
        'conditions',
        'base_experience',
        'level_multiplier'
    ];

    protected $casts = [
        'conditions' => 'array',
        'base_experience' => 'integer',
        'level_multiplier' => 'decimal:2'
    ];

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    /**
     * Calculate experience gain for a specific level
     */
    public function getExperienceForLevel(int $level): int
    {
        return floor($this->base_experience * pow($this->level_multiplier, $level - 1));
    }

    /**
     * Check if conditions are met for this experience source
     */
    public function conditionsMet(array $context): bool
    {
        if (empty($this->conditions)) {
            return true;
        }

        foreach ($this->conditions as $condition => $value) {
            switch ($condition) {
                case 'weapon_type':
                    if (!isset($context['weapon_type']) || $context['weapon_type'] !== $value) {
                        return false;
                    }
                    break;
                case 'enemy_type':
                    if (!isset($context['enemy_type']) || $context['enemy_type'] !== $value) {
                        return false;
                    }
                    break;
                case 'item_type':
                    if (!isset($context['item_type']) || $context['item_type'] !== $value) {
                        return false;
                    }
                    break;
                case 'minimum_damage':
                    if (!isset($context['damage']) || $context['damage'] < $value) {
                        return false;
                    }
                    break;
                case 'critical_hit':
                    if (!isset($context['critical_hit']) || !$context['critical_hit']) {
                        return false;
                    }
                    break;
            }
        }

        return true;
    }
}