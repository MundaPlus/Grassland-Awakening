<?php

namespace App\Services;

use App\Models\Player;
use App\Models\Skill;
use App\Models\PlayerSkill;
use App\Models\SkillExperienceSource;
use App\Models\PlayerSkillCooldown;
use Illuminate\Support\Collection;

class SkillService
{
    /**
     * Get all skills for a player with their progress
     */
    public function getPlayerSkills(Player $player): array
    {
        $allSkills = Skill::where('is_enabled', true)->get();
        $playerSkills = $player->playerSkills()->with('skill')->get()->keyBy('skill_id');
        
        $skillData = [
            'passive' => [],
            'active' => [],
            'categories' => []
        ];
        
        foreach ($allSkills as $skill) {
            $playerSkill = $playerSkills->get($skill->id);
            
            $skillInfo = [
                'skill' => $skill,
                'player_skill' => $playerSkill,
                'level' => $playerSkill ? $playerSkill->level : 0,
                'experience' => $playerSkill ? $playerSkill->experience : 0,
                'can_learn' => !$playerSkill && $skill->meetsRequirements($player),
                'progress_percentage' => $playerSkill ? $playerSkill->getProgressPercentage() : 0,
                'exp_to_next' => $playerSkill ? $playerSkill->experienceToNextLevel() : 0,
                'current_effects' => $playerSkill ? $playerSkill->getCurrentEffects() : [],
                'next_level_effects' => $skill->getEffectAtLevel(($playerSkill ? $playerSkill->level : 0) + 1),
                'is_on_cooldown' => $playerSkill ? $playerSkill->isOnCooldown() : false,
                'cooldown_remaining' => $playerSkill ? $playerSkill->getRemainingCooldown() : 0
            ];
            
            // Categorize skills
            if ($skill->type === Skill::TYPE_PASSIVE) {
                $skillData['passive'][] = $skillInfo;
            } else {
                $skillData['active'][] = $skillInfo;
            }
            
            // Group by category
            if (!isset($skillData['categories'][$skill->category])) {
                $skillData['categories'][$skill->category] = [];
            }
            $skillData['categories'][$skill->category][] = $skillInfo;
        }
        
        return $skillData;
    }

    /**
     * Learn/unlock a new skill for a player
     */
    public function learnSkill(Player $player, Skill $skill): bool
    {
        // Check if player already has this skill
        if ($this->hasSkill($player, $skill)) {
            return false;
        }
        
        // Check requirements
        if (!$skill->meetsRequirements($player)) {
            return false;
        }
        
        // Create player skill with level 1
        PlayerSkill::create([
            'player_id' => $player->id,
            'skill_id' => $skill->id,
            'level' => 1,
            'experience' => 0
        ]);
        
        return true;
    }

    /**
     * Learn a skill by spending skill points
     */
    public function learnSkillWithPoints(Player $player, Skill $skill): array
    {
        // Check if player already has this skill
        if ($this->hasSkill($player, $skill)) {
            return ['success' => false, 'message' => 'You already have this skill.'];
        }
        
        // Check requirements
        if (!$skill->meetsRequirements($player)) {
            return ['success' => false, 'message' => 'You do not meet the requirements for this skill.'];
        }
        
        // Calculate skill point cost (base cost from skill definition)
        $cost = $skill->base_cost;
        
        // Check if player has enough skill points
        if ($player->skill_points < $cost) {
            return ['success' => false, 'message' => "You need {$cost} skill points to learn this skill. You have {$player->skill_points}."];
        }
        
        // Spend skill points and learn skill
        $player->skill_points -= $cost;
        $player->save();
        
        // Create player skill with level 1
        PlayerSkill::create([
            'player_id' => $player->id,
            'skill_id' => $skill->id,
            'level' => 1,
            'experience' => 0
        ]);
        
        return ['success' => true, 'message' => "You learned {$skill->name}! Cost: {$cost} skill points."];
    }

    /**
     * Check if player has a skill
     */
    public function hasSkill(Player $player, Skill $skill): bool
    {
        return $player->playerSkills()->where('skill_id', $skill->id)->exists();
    }

    /**
     * Add experience to a skill
     */
    public function addSkillExperience(Player $player, string $sourceType, array $context = []): array
    {
        $experienceSources = SkillExperienceSource::where('source_type', $sourceType)
            ->with('skill')
            ->get();
        
        $results = [];
        
        foreach ($experienceSources as $source) {
            // Check if conditions are met
            if (!$source->conditionsMet($context)) {
                continue;
            }
            
            // Check if player has this skill
            $playerSkill = $player->playerSkills()
                ->where('skill_id', $source->skill_id)
                ->first();
            
            if (!$playerSkill) {
                // Auto-learn passive skills when they would gain XP
                if ($source->skill->type === Skill::TYPE_PASSIVE && $source->skill->meetsRequirements($player)) {
                    $this->learnSkill($player, $source->skill);
                    $playerSkill = $player->playerSkills()
                        ->where('skill_id', $source->skill_id)
                        ->first();
                } else {
                    continue;
                }
            }
            
            $experienceGain = $source->getExperienceForLevel($playerSkill->level);
            $result = $playerSkill->addExperience($experienceGain);
            
            $results[] = [
                'skill' => $source->skill,
                'experience_gained' => $result['experience_gained'],
                'levels_gained' => $result['levels_gained'],
                'new_level' => $result['new_level']
            ];
        }
        
        return $results;
    }

    /**
     * Use an active skill
     */
    public function useActiveSkill(Player $player, Skill $skill, array $context = []): array
    {
        if ($skill->type !== Skill::TYPE_ACTIVE) {
            return ['success' => false, 'message' => 'This is not an active skill'];
        }
        
        $playerSkill = $player->playerSkills()->where('skill_id', $skill->id)->first();
        if (!$playerSkill) {
            return ['success' => false, 'message' => 'You do not know this skill'];
        }
        
        // Check cooldown
        if ($playerSkill->isOnCooldown()) {
            $remaining = $playerSkill->getRemainingCooldown();
            return ['success' => false, 'message' => "Skill on cooldown for {$remaining} seconds"];
        }
        
        // Check weapon compatibility
        if (!empty($skill->weapon_types)) {
            $equippedWeapon = $this->getEquippedWeaponType($player);
            if (!$equippedWeapon || !$skill->canUseWithWeapon($equippedWeapon)) {
                return ['success' => false, 'message' => 'Incompatible weapon for this skill'];
            }
        }
        
        // Use the skill
        $playerSkill->use();
        
        // Get skill effects at current level
        $effects = $playerSkill->getCurrentEffects();
        
        // Add skill usage experience
        $this->addSkillExperience($player, 'skill_used', [
            'skill_id' => $skill->id,
            'skill_level' => $playerSkill->level
        ]);
        
        return [
            'success' => true,
            'effects' => $effects,
            'cost' => $playerSkill->getCurrentCost(),
            'cooldown' => $skill->getCooldownAtLevel($playerSkill->level)
        ];
    }

    /**
     * Get active skills available for the player's current weapon
     */
    public function getAvailableActiveSkills(Player $player): Collection
    {
        $equippedWeapon = $this->getEquippedWeaponType($player);
        if (!$equippedWeapon) {
            return collect();
        }
        
        return $player->playerSkills()
            ->with('skill')
            ->whereHas('skill', function($query) use ($equippedWeapon) {
                $query->where('type', Skill::TYPE_ACTIVE)
                      ->where(function($subQuery) use ($equippedWeapon) {
                          $subQuery->whereJsonContains('weapon_types', $equippedWeapon)
                                   ->orWhereNull('weapon_types');
                      });
            })->get();
    }

    /**
     * Get the player's equipped weapon type
     */
    private function getEquippedWeaponType(Player $player): ?string
    {
        // Check new PlayerItem system first
        $equippedWeapon = $player->equippedItems()
            ->with('item')
            ->whereHas('item', function($query) {
                $query->where('type', 'weapon');
            })->first();
            
        if ($equippedWeapon && $equippedWeapon->item) {
            return $equippedWeapon->item->subtype;
        }
        
        // Check old Equipment system
        $equipment = $player->equipment()
            ->with('item')
            ->whereHas('item', function($query) {
                $query->where('type', 'weapon');
            })->first();
            
        return $equipment?->item?->subtype;
    }

    /**
     * Calculate passive skill bonuses for a player
     */
    public function getPassiveSkillBonuses(Player $player): array
    {
        $bonuses = [
            'health_bonus' => 0,
            'damage_reduction' => 0,
            'crafting_bonus' => 0,
            'gathering_bonus' => 0,
            'stamina_bonus' => 0,
            'movement_speed' => 0
        ];
        
        $passiveSkills = $player->playerSkills()
            ->with('skill')
            ->whereHas('skill', function($query) {
                $query->where('type', Skill::TYPE_PASSIVE);
            })->get();
        
        foreach ($passiveSkills as $playerSkill) {
            $effects = $playerSkill->getCurrentEffects();
            
            foreach ($effects as $effectType => $value) {
                if (isset($bonuses[$effectType])) {
                    $bonuses[$effectType] += $value;
                }
            }
        }
        
        return $bonuses;
    }

    /**
     * Get skill leaderboard
     */
    public function getSkillLeaderboard(Skill $skill, int $limit = 10): Collection
    {
        return PlayerSkill::where('skill_id', $skill->id)
            ->with(['player:id,character_name,level'])
            ->orderByDesc('level')
            ->orderByDesc('experience')
            ->limit($limit)
            ->get();
    }

    /**
     * Get player's skill statistics
     */
    public function getPlayerSkillStats(Player $player): array
    {
        $playerSkills = $player->playerSkills()->with('skill')->get();
        
        $stats = [
            'total_skills' => $playerSkills->count(),
            'passive_skills' => $playerSkills->where('skill.type', Skill::TYPE_PASSIVE)->count(),
            'active_skills' => $playerSkills->where('skill.type', Skill::TYPE_ACTIVE)->count(),
            'max_level_skills' => 0,
            'average_level' => 0,
            'total_experience' => 0,
            'highest_level' => 0,
            'categories' => []
        ];
        
        if ($playerSkills->isEmpty()) {
            return $stats;
        }
        
        $totalLevels = 0;
        foreach ($playerSkills as $playerSkill) {
            $totalLevels += $playerSkill->level;
            $stats['total_experience'] += $playerSkill->experience;
            
            if ($playerSkill->level > $stats['highest_level']) {
                $stats['highest_level'] = $playerSkill->level;
            }
            
            if ($playerSkill->level >= $playerSkill->skill->max_level) {
                $stats['max_level_skills']++;
            }
            
            $category = $playerSkill->skill->category;
            if (!isset($stats['categories'][$category])) {
                $stats['categories'][$category] = 0;
            }
            $stats['categories'][$category]++;
        }
        
        $stats['average_level'] = $totalLevels / $playerSkills->count();
        
        return $stats;
    }

    /**
     * Clean up expired cooldowns
     */
    public function cleanupExpiredCooldowns(): int
    {
        return PlayerSkillCooldown::cleanupExpired();
    }

    /**
     * Auto-learn available passive skills
     */
    public function autoLearnPassiveSkills(Player $player): array
    {
        $availableSkills = Skill::where('type', Skill::TYPE_PASSIVE)
            ->where('is_enabled', true)
            ->get();
        
        $learnedSkills = [];
        
        foreach ($availableSkills as $skill) {
            if (!$this->hasSkill($player, $skill) && $skill->meetsRequirements($player)) {
                if ($this->learnSkill($player, $skill)) {
                    $learnedSkills[] = $skill;
                }
            }
        }
        
        return $learnedSkills;
    }
}