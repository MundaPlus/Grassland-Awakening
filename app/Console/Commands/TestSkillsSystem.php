<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Player;
use App\Services\SkillService;
use App\Models\Skill;

class TestSkillsSystem extends Command
{
    protected $signature = 'game:test-skills {player_id?}';
    protected $description = 'Test the skills system functionality';

    public function handle()
    {
        $playerId = $this->argument('player_id');
        
        if ($playerId) {
            $player = Player::find($playerId);
        } else {
            $player = Player::first();
        }

        if (!$player) {
            $this->error('No player found');
            return;
        }

        $skillService = app(SkillService::class);

        $this->info("Testing skills system for player: {$player->character_name} (ID: {$player->id})");

        // Auto-learn available passive skills
        $this->info("\n--- Auto-learning Passive Skills ---");
        $learnedSkills = $skillService->autoLearnPassiveSkills($player);
        foreach ($learnedSkills as $skill) {
            $this->info("✅ Learned: {$skill->name} ({$skill->category})");
        }

        // Test skill experience gain
        $this->info("\n--- Testing Skill Experience Gain ---");
        
        // Simulate crafting a weapon (should give smithing XP)
        $smithingResults = $skillService->addSkillExperience($player, 'item_crafted', [
            'item_type' => 'weapon'
        ]);
        
        foreach ($smithingResults as $result) {
            $this->info("🔨 {$result['skill']->name}: +{$result['experience_gained']} XP -> Level {$result['new_level']}");
            if (!empty($result['levels_gained'])) {
                foreach ($result['levels_gained'] as $levelUp) {
                    $this->info("   🎉 Level up! {$levelUp['old_level']} -> {$levelUp['new_level']}");
                }
            }
        }

        // Simulate mining (should give mining XP)
        $miningResults = $skillService->addSkillExperience($player, 'resource_gathered', [
            'resource_type' => 'ore'
        ]);
        
        foreach ($miningResults as $result) {
            $this->info("⛏️ {$result['skill']->name}: +{$result['experience_gained']} XP -> Level {$result['new_level']}");
        }

        // Get player skills overview
        $this->info("\n--- Player Skills Overview ---");
        $skillData = $skillService->getPlayerSkills($player);
        
        $this->info("Passive Skills: " . count($skillData['passive']));
        foreach ($skillData['passive'] as $skillInfo) {
            $level = $skillInfo['level'];
            $progress = number_format($skillInfo['progress_percentage'], 1);
            $this->info("  {$skillInfo['skill']->icon} {$skillInfo['skill']->name}: Level {$level} ({$progress}%)");
            
            if (!empty($skillInfo['current_effects'])) {
                foreach ($skillInfo['current_effects'] as $effect => $value) {
                    $this->info("    - {$effect}: {$value}");
                }
            }
        }

        $this->info("\nActive Skills: " . count($skillData['active']));
        foreach ($skillData['active'] as $skillInfo) {
            if ($skillInfo['level'] > 0) {
                $level = $skillInfo['level'];
                $cooldown = $skillInfo['is_on_cooldown'] ? " (ON COOLDOWN)" : "";
                $this->info("  {$skillInfo['skill']->icon} {$skillInfo['skill']->name}: Level {$level}{$cooldown}");
                
                if (!empty($skillInfo['current_effects'])) {
                    foreach ($skillInfo['current_effects'] as $effect => $value) {
                        $this->info("    - {$effect}: {$value}");
                    }
                }
            }
        }

        // Test active skill usage
        $this->info("\n--- Testing Active Skills ---");
        $activeSkills = $skillService->getAvailableActiveSkills($player);
        
        if ($activeSkills->isEmpty()) {
            // Learn a basic active skill for testing
            $powerStrike = Skill::where('slug', 'power-strike')->first();
            if ($powerStrike && $skillService->learnSkill($player, $powerStrike)) {
                $this->info("✅ Learned Power Strike for testing");
                $activeSkills = $skillService->getAvailableActiveSkills($player);
            }
        }

        foreach ($activeSkills as $playerSkill) {
            $result = $skillService->useActiveSkill($player, $playerSkill->skill);
            if ($result['success']) {
                $this->info("⚔️ Used {$playerSkill->skill->name}!");
                $this->info("   Effects: " . json_encode($result['effects']));
                $this->info("   Cost: {$result['cost']}, Cooldown: {$result['cooldown']}s");
            } else {
                $this->info("❌ Could not use {$playerSkill->skill->name}: {$result['message']}");
            }
        }

        // Show passive bonuses
        $this->info("\n--- Passive Skill Bonuses ---");
        $bonuses = $skillService->getPassiveSkillBonuses($player);
        foreach ($bonuses as $bonusType => $value) {
            if ($value > 0) {
                $this->info("  {$bonusType}: +{$value}");
            }
        }

        // Show player skill statistics
        $this->info("\n--- Skill Statistics ---");
        $stats = $skillService->getPlayerSkillStats($player);
        $this->info("Total Skills: {$stats['total_skills']}");
        $this->info("Passive Skills: {$stats['passive_skills']}");
        $this->info("Active Skills: {$stats['active_skills']}");
        $this->info("Highest Level: {$stats['highest_level']}");
        $this->info("Average Level: " . number_format($stats['average_level'], 1));
        $this->info("Total Experience: {$stats['total_experience']}");

        $this->info("\nSkills test completed!");
    }
}