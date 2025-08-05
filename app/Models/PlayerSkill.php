<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerSkill extends Model
{
    protected $fillable = [
        'player_id',
        'skill_id',
        'level',
        'experience',
        'last_used',
        'times_used'
    ];

    protected $casts = [
        'level' => 'integer',
        'experience' => 'integer',
        'times_used' => 'integer',
        'last_used' => 'datetime'
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    /**
     * Add experience to this skill
     */
    public function addExperience(int $amount): array
    {
        $oldLevel = $this->level;
        $this->experience += $amount;
        
        $levelsGained = [];
        
        // Check for level ups
        while ($this->canLevelUp()) {
            $this->level++;
            $levelsGained[] = [
                'old_level' => $oldLevel,
                'new_level' => $this->level,
                'skill_name' => $this->skill->name
            ];
            $oldLevel = $this->level;
        }
        
        $this->save();
        
        return [
            'experience_gained' => $amount,
            'levels_gained' => $levelsGained,
            'new_level' => $this->level,
            'total_experience' => $this->experience
        ];
    }

    /**
     * Check if this skill can level up
     */
    public function canLevelUp(): bool
    {
        if ($this->level >= $this->skill->max_level) {
            return false;
        }
        
        $experienceNeeded = Skill::experienceForLevel($this->level + 1);
        return $this->experience >= $experienceNeeded;
    }

    /**
     * Get experience needed for next level
     */
    public function experienceToNextLevel(): int
    {
        if ($this->level >= $this->skill->max_level) {
            return 0;
        }
        
        $experienceNeeded = Skill::experienceForLevel($this->level + 1);
        return max(0, $experienceNeeded - $this->experience);
    }

    /**
     * Get progress percentage to next level
     */
    public function getProgressPercentage(): float
    {
        if ($this->level >= $this->skill->max_level) {
            return 100.0;
        }
        
        $currentLevelExp = Skill::experienceForLevel($this->level);
        $nextLevelExp = Skill::experienceForLevel($this->level + 1);
        $experienceInLevel = $this->experience - $currentLevelExp;
        $experienceNeededForLevel = $nextLevelExp - $currentLevelExp;
        
        if ($experienceNeededForLevel <= 0) {
            return 100.0;
        }
        
        return min(100.0, ($experienceInLevel / $experienceNeededForLevel) * 100);
    }

    /**
     * Use this skill (for active skills)
     */
    public function use(): bool
    {
        if ($this->skill->type !== Skill::TYPE_ACTIVE) {
            return false;
        }
        
        $this->last_used = now();
        $this->times_used++;
        $this->save();
        
        // Set cooldown
        $cooldownDuration = $this->skill->getCooldownAtLevel($this->level);
        if ($cooldownDuration > 0) {
            PlayerSkillCooldown::updateOrCreate(
                [
                    'player_id' => $this->player_id,
                    'skill_id' => $this->skill_id
                ],
                [
                    'available_at' => now()->addSeconds($cooldownDuration)
                ]
            );
        }
        
        return true;
    }

    /**
     * Check if this skill is on cooldown
     */
    public function isOnCooldown(): bool
    {
        if ($this->skill->type !== Skill::TYPE_ACTIVE) {
            return false;
        }
        
        $cooldown = PlayerSkillCooldown::where('player_id', $this->player_id)
            ->where('skill_id', $this->skill_id)
            ->first();
            
        return $cooldown && $cooldown->available_at->isFuture();
    }

    /**
     * Get remaining cooldown time in seconds
     */
    public function getRemainingCooldown(): int
    {
        if (!$this->isOnCooldown()) {
            return 0;
        }
        
        $cooldown = PlayerSkillCooldown::where('player_id', $this->player_id)
            ->where('skill_id', $this->skill_id)
            ->first();
            
        return $cooldown ? max(0, $cooldown->available_at->diffInSeconds(now())) : 0;
    }

    /**
     * Get current effects of this skill
     */
    public function getCurrentEffects(): array
    {
        return $this->skill->getEffectAtLevel($this->level);
    }

    /**
     * Get skill cost at current level
     */
    public function getCurrentCost(): int
    {
        return $this->skill->getCostAtLevel($this->level);
    }
}