<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Player;
use App\Services\AchievementService;

class TestAchievements extends Command
{
    protected $signature = 'game:test-achievements {player_id?}';
    protected $description = 'Test achievement system by unlocking some achievements';

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

        $achievementService = app(AchievementService::class);

        $this->info("Testing achievements for player: {$player->character_name} (ID: {$player->id})");
        $this->info("Player level: {$player->level}");

        // Get current stats
        $data = $achievementService->getPlayerAchievements($player);
        $this->info("Current unlocked achievements: {$data['achievement_count']}");
        $this->info("Current achievement points: {$data['total_points']}");

        // Manually trigger some achievement events
        $this->info("\n--- Triggering Achievement Events ---");

        // Level achievement
        $newAchievements = $achievementService->processGameEvent($player, 'level_gained');
        if (!empty($newAchievements)) {
            foreach ($newAchievements as $achievement) {
                $this->info("✅ Unlocked: {$achievement->name} (+{$achievement->points} points)");
            }
        }

        // Combat victory
        $newAchievements = $achievementService->processGameEvent($player, 'combat_victory');
        if (!empty($newAchievements)) {
            foreach ($newAchievements as $achievement) {
                $this->info("✅ Unlocked: {$achievement->name} (+{$achievement->points} points)");
            }
        }

        // Force check all achievements
        $newAchievements = $achievementService->checkAndUnlockAchievements($player);
        if (!empty($newAchievements)) {
            foreach ($newAchievements as $achievement) {
                $this->info("✅ Unlocked: {$achievement->name} (+{$achievement->points} points)");
            }
        }

        // Show final stats
        $finalData = $achievementService->getPlayerAchievements($player);
        $this->info("\n--- Final Stats ---");
        $this->info("Total unlocked achievements: {$finalData['achievement_count']}");
        $this->info("Total achievement points: {$finalData['total_points']}");

        // Show some progress achievements
        if (!empty($finalData['progress'])) {
            $this->info("\n--- Progress Towards Achievements ---");
            foreach (array_slice($finalData['progress'], 0, 10) as $progress) {
                $completion = round($progress['completion_percentage'], 1);
                $this->info("{$progress['name']}: {$completion}% complete");
                
                if (!empty($progress['requirements'])) {
                    foreach ($progress['requirements'] as $req) {
                        $this->info("  - {$req['requirement']}: {$req['current']}/{$req['required']}");
                    }
                }
            }
        }

        $this->info("\nTest completed!");
    }
}