<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AchievementService;
use App\Services\ReputationService;
use App\Models\Player;
use App\Models\User;

class TestAchievementSystem extends Command
{
    protected $signature = 'game:test-achievements {--reputation} {--simulate-events} {--leaderboard}';
    protected $description = 'Test achievement and reputation systems';

    public function handle()
    {
        $achievementService = app(AchievementService::class);
        $reputationService = app(ReputationService::class);

        $this->info('=== ACHIEVEMENT & REPUTATION SYSTEM TEST ===');

        // Get test player
        $player = $this->getTestPlayer();
        $this->line("Testing with player: {$player->character_name} (Level {$player->level})");

        if ($this->option('reputation') || !$this->hasCustomOption('reputation')) {
            $this->testReputationSystem($reputationService, $player);
        }

        if ($this->option('simulate-events') || !$this->hasCustomOption('simulate-events')) {
            $this->testAchievementSystem($achievementService, $reputationService, $player);
        }

        if ($this->option('leaderboard')) {
            $this->testLeaderboard($achievementService);
        }

        $this->info('\n=== ACHIEVEMENT & REPUTATION TEST COMPLETED ===');
    }

    private function testReputationSystem(ReputationService $reputationService, Player $player): void
    {
        $this->line("\n=== REPUTATION SYSTEM TEST ===");

        // Show initial reputation status
        $reputations = $reputationService->getAllPlayerReputations($player);
        $this->line("Initial reputation status:");
        
        foreach ($reputations as $rep) {
            $this->line("  {$rep['faction_icon']} {$rep['faction_name']}: {$rep['current_score']} ({$rep['level']['name']})");
        }

        // Test various reputation events
        $events = [
            ['event' => 'npc_recruited', 'data' => [], 'description' => 'Recruited a new villager'],
            ['event' => 'adventure_completed', 'data' => ['road' => 'east'], 'description' => 'Completed forest adventure'],
            ['event' => 'trade_completed', 'data' => ['value' => 500], 'description' => 'Completed trade worth 500 gold'],
            ['event' => 'combat_victory', 'data' => ['enemy' => 'bandit'], 'description' => 'Defeated a bandit'],
            ['event' => 'specialization_unlocked', 'data' => [], 'description' => 'Unlocked village specialization'],
            ['event' => 'environmental_damage', 'data' => ['severity' => 'minor'], 'description' => 'Caused minor environmental damage'],
            ['event' => 'village_milestone', 'data' => ['milestone' => 'small_village'], 'description' => 'Village grew to small village'],
            ['event' => 'currency_milestone', 'data' => ['amount' => 2000], 'description' => 'Earned 2000 gold milestone']
        ];

        $this->line("\nSimulating reputation events:");
        foreach ($events as $event) {
            $changes = $reputationService->processGameEvent($player, $event['event'], $event['data']);
            $this->line("  📝 {$event['description']}");
            
            foreach ($changes as $change) {
                $color = $change['change'] > 0 ? 'info' : 'error';
                $sign = $change['change'] > 0 ? '+' : '';
                $this->line("    {$change['faction_name']}: {$sign}{$change['change']} ({$change['new_score']}) - {$change['new_level']['name']}", $color);
                
                if ($change['level_changed']) {
                    $this->line("    🎉 Level changed: {$change['old_level']['name']} → {$change['new_level']['name']}", 'comment');
                }
            }
        }

        // Show final reputation status
        $this->line("\nFinal reputation status:");
        $reputations = $reputationService->getAllPlayerReputations($player);
        
        foreach ($reputations as $rep) {
            $progress = $rep['progress_to_next'];
            $progressText = $progress ? " ({$progress['progress']}/{$progress['needed']} to {$progress['next_level_name']})" : " (MAX LEVEL)";
            
            $this->line("  {$rep['faction_icon']} {$rep['faction_name']}: {$rep['current_score']} ({$rep['level']['name']}){$progressText}");
            
            if (!empty($rep['benefits'])) {
                $this->line("    Benefits: " . implode(', ', $rep['benefits']), 'info');
            }
            if (!empty($rep['penalties'])) {
                $this->line("    Penalties: " . implode(', ', $rep['penalties']), 'error');
            }
        }

        // Show reputation bonuses
        $bonuses = $reputationService->getReputationBonuses($player);
        $this->line("\nActive reputation bonuses:");
        foreach ($bonuses as $bonus => $value) {
            if ($value > 0 || $value !== 1.0) {
                if (is_array($value)) {
                    if (!empty($value)) {
                        $this->line("  {$bonus}: " . implode(', ', $value));
                    }
                } else {
                    $this->line("  {$bonus}: {$value}");
                }
            }
        }
    }

    private function testAchievementSystem(AchievementService $achievementService, ReputationService $reputationService, Player $player): void
    {
        $this->line("\n=== ACHIEVEMENT SYSTEM TEST ===");

        // Show initial achievements
        $achievements = $achievementService->getPlayerAchievements($player);
        $this->line("Initial achievements: {$achievements['achievement_count']} unlocked, {$achievements['total_points']} points");

        // Simulate some game events that trigger achievements
        $events = [
            ['event' => 'npc_recruited', 'data' => [], 'description' => 'Recruited first NPC'],
            ['event' => 'combat_victory', 'data' => [], 'description' => 'Won first combat'],
            ['event' => 'level_gained', 'data' => [], 'description' => 'Gained a level'],
            ['event' => 'adventure_completed', 'data' => [], 'description' => 'Completed first adventure'],
            ['event' => 'specialization_unlocked', 'data' => [], 'description' => 'Unlocked specialization']
        ];

        $this->line("\nSimulating achievement events:");
        $totalUnlocked = 0;
        
        foreach ($events as $event) {
            $this->line("  📝 {$event['description']}");
            
            // Process achievement event
            $unlockedAchievements = $achievementService->processGameEvent($player, $event['event'], $event['data']);
            
            // Also process reputation event
            $reputationService->processGameEvent($player, $event['event'], $event['data']);
            
            foreach ($unlockedAchievements as $achievement) {
                $totalUnlocked++;
                $currencyBonus = $achievement->points * 10;
                $this->line("    🏆 {$achievement->icon} {$achievement->name} (+{$achievement->points} points, +{$currencyBonus} gold)", 'info');
                $this->line("       {$achievement->description}", 'comment');
            }
            
            if (empty($unlockedAchievements)) {
                $this->line("    No achievements unlocked", 'error');
            }
        }

        // Show updated achievements summary
        $achievements = $achievementService->getPlayerAchievements($player);
        $this->line("\nFinal achievements: {$achievements['achievement_count']} unlocked, {$achievements['total_points']} points");
        
        if (!empty($achievements['unlocked'])) {
            $this->line("\nUnlocked achievements:");
            foreach ($achievements['unlocked'] as $achievement) {
                $this->line("  {$achievement['icon']} {$achievement['name']} ({$achievement['points']} pts) - {$achievement['category']}");
            }
        }

        // Show progress towards locked achievements
        if (!empty($achievements['progress'])) {
            $this->line("\nProgress towards achievements:");
            $topProgress = array_slice($achievements['progress'], 0, 5); // Show top 5
            
            foreach ($topProgress as $progress) {
                $percentage = round($progress['completion_percentage'], 1);
                $this->line("  {$progress['icon']} {$progress['name']} - {$percentage}% complete");
                
                foreach ($progress['requirements'] as $req) {
                    $reqPercentage = round($req['progress'], 1);
                    $this->line("    {$req['requirement']}: {$req['current']}/{$req['required']} ({$reqPercentage}%)", 'comment');
                }
            }
        }

        // Show achievement categories
        $categories = $achievementService->getAchievementCategories();
        $this->line("\nAchievement categories:");
        foreach ($categories as $categoryId => $category) {
            $achievementCount = count($category['achievements']);
            $this->line("  {$category['name']}: {$achievementCount} achievements, {$category['total_points']} total points");
        }
    }

    private function testLeaderboard(AchievementService $achievementService): void
    {
        $this->line("\n=== ACHIEVEMENT LEADERBOARD ===");

        $leaderboard = $achievementService->getLeaderboard('all', 5);
        
        if (empty($leaderboard)) {
            $this->line("No players found on leaderboard.");
            return;
        }

        $this->line("Top achievement earners:");
        foreach ($leaderboard as $entry) {
            $this->line("  #{$entry['rank']} {$entry['player_name']} (Level {$entry['player_level']}) - {$entry['total_points']} points, {$entry['achievement_count']} achievements");
        }

        // Show category leaderboards
        $categories = ['combat', 'exploration', 'village', 'character', 'social'];
        foreach ($categories as $category) {
            $categoryLeaderboard = $achievementService->getLeaderboard($category, 3);
            if (!empty($categoryLeaderboard)) {
                $this->line("\nTop {$category} achievers:");
                foreach ($categoryLeaderboard as $entry) {
                    $this->line("  #{$entry['rank']} {$entry['player_name']} - {$entry['total_points']} points");
                }
            }
        }
    }

    private function hasCustomOption(string $option): bool
    {
        return $this->input->hasParameterOption("--{$option}");
    }

    private function getTestPlayer(): Player
    {
        $user = User::firstOrCreate(
            ['email' => 'test@grassland.com'],
            [
                'name' => 'Test Player',
                'password' => bcrypt('password'),
                'email_verified_at' => now()
            ]
        );

        $player = $user->player;
        if (!$player) {
            $player = Player::create([
                'user_id' => $user->id,
                'character_name' => 'Test Hero',
                'level' => 4,
                'experience' => 400,
                'persistent_currency' => 1200,
                'hp' => 40,
                'max_hp' => 40,
                'ac' => 13,
                'str' => 15,
                'dex' => 14,
                'con' => 14,
                'int' => 12,
                'wis' => 13,
                'cha' => 11
            ]);
        }

        return $player;
    }
}