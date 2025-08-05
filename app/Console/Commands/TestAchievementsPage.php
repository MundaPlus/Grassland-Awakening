<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Player;
use App\Services\AchievementService;
use App\Http\Controllers\Web\GameController;

class TestAchievementsPage extends Command
{
    protected $signature = 'game:test-achievements-page {player_id?}';
    protected $description = 'Test the achievements page data structure';

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

        $this->info("Testing achievements page data for player: {$player->character_name} (ID: {$player->id})");

        $achievementService = app(AchievementService::class);
        
        // Get player achievements from the service (same as controller)
        $achievementData = $achievementService->getPlayerAchievements($player, false);
        
        $this->info("Raw achievement data:");
        $this->info("- Unlocked count: " . count($achievementData['unlocked']));
        $this->info("- Progress count: " . count($achievementData['progress']));
        $this->info("- Total points: " . $achievementData['total_points']);

        // Test the data structure that would be passed to the view
        $allAchievementsForView = collect();
        
        // Add unlocked achievements
        foreach ($achievementData['unlocked'] as $unlockedAchievement) {
            $achievement = (object) $unlockedAchievement;
            $achievement->id = $unlockedAchievement['id']; 
            $achievement->pivot = (object) [
                'unlocked_at' => \Carbon\Carbon::parse($unlockedAchievement['unlocked_at'])
            ];
            $achievement->is_progress_based = false;
            $achievement->target_value = null;
            $achievement->hints = null;
            $achievement->rewards = null;
            $allAchievementsForView->push($achievement);
        }
        
        // Add progress achievements (not unlocked yet)
        foreach ($achievementData['progress'] as $progressAchievement) {
            $achievement = (object) $progressAchievement;
            $achievement->id = $progressAchievement['id'];
            $achievement->pivot = null; // Not unlocked
            
            $achievement->is_progress_based = true;
            
            $targetValue = 100; // default
            $currentProgress = 0;
            if (!empty($progressAchievement['requirements'])) {
                $targetValue = max(array_column($progressAchievement['requirements'], 'required'));
                $currentProgress = max(array_column($progressAchievement['requirements'], 'current'));
            }
            $achievement->target_value = $targetValue;
            $achievement->current_progress = $currentProgress;
            $achievement->completion_percentage = $progressAchievement['completion_percentage'];
            
            $achievement->hints = null;
            $achievement->rewards = null;
            $allAchievementsForView->push($achievement);
        }

        $this->info("\nProcessed achievements for view:");
        $this->info("- Total achievements: " . $allAchievementsForView->count());
        $this->info("- Unlocked achievements: " . $allAchievementsForView->where('pivot', '!=', null)->count());
        $this->info("- Progress achievements: " . $allAchievementsForView->where('pivot', '==', null)->count());

        $this->info("\nSample unlocked achievement:");
        $unlockedSample = $allAchievementsForView->where('pivot', '!=', null)->first();
        if ($unlockedSample) {
            $this->info("- Name: {$unlockedSample->name}");
            $this->info("- Category: {$unlockedSample->category}");
            $this->info("- Points: {$unlockedSample->points}");
            $this->info("- Unlocked at: {$unlockedSample->pivot->unlocked_at}");
        }

        $this->info("\nSample progress achievement:");
        $progressSample = $allAchievementsForView->where('pivot', '==', null)->first();
        if ($progressSample) {
            $this->info("- Name: {$progressSample->name}");
            $this->info("- Category: {$progressSample->category}");
            $this->info("- Progress: {$progressSample->current_progress}/{$progressSample->target_value}");
            $this->info("- Percentage: {$progressSample->completion_percentage}%");
        }

        $this->info("\nTest completed successfully!");
    }
}