<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Player;
use App\Models\NPC;
use App\Models\Adventure;
use App\Models\Achievement;
use App\Models\Faction;
use App\Models\FactionReputation;
use App\Models\PlayerAchievement;

class AdminTools extends Command
{
    protected $signature = 'game:admin 
                           {action : The admin action to perform}
                           {--player= : Player ID for player-specific actions}
                           {--value= : Value for actions that require parameters}
                           {--force : Force action without confirmation}';
    
    protected $description = 'Administrative tools for managing the Grassland Awakening game';

    public function handle()
    {
        $action = $this->argument('action');
        $playerId = $this->option('player');
        $value = $this->option('value');
        $force = $this->option('force');

        switch ($action) {
            case 'stats':
                return $this->showGameStats();
            case 'reset-player':
                return $this->resetPlayer($playerId, $force);
            case 'add-gold':
                return $this->addGold($playerId, $value);
            case 'set-level':
                return $this->setLevel($playerId, $value);
            case 'unlock-achievement':
                return $this->unlockAchievement($playerId, $value);
            case 'clear-adventures':
                return $this->clearAdventures($force);
            case 'seed-achievements':
                return $this->seedAchievements();
            case 'seed-factions':
                return $this->seedFactions();
            case 'cleanup':
                return $this->cleanup($force);
            case 'help':
                return $this->showHelp();
            default:
                $this->error("Unknown action: {$action}");
                $this->line('Use "game:admin help" to see available actions.');
                return 1;
        }
    }

    private function showGameStats()
    {
        $this->info('🎮 Grassland Awakening - Game Statistics');
        $this->newLine();

        // Player Statistics
        $playerCount = Player::count();
        $activePlayerCount = Player::where('updated_at', '>=', now()->subDays(7))->count();
        $averageLevel = Player::avg('level');
        $totalGold = Player::sum('gold');

        $this->line('👥 Player Statistics:');
        $this->line("   Total Players: {$playerCount}");
        $this->line("   Active (7 days): {$activePlayerCount}");
        $this->line("   Average Level: " . round($averageLevel, 1));
        $this->line("   Total Gold: " . number_format($totalGold));
        $this->newLine();

        // NPC Statistics
        $npcCount = NPC::count();
        $npcByProfession = NPC::selectRaw('profession, COUNT(*) as count')
            ->groupBy('profession')
            ->pluck('count', 'profession')
            ->toArray();

        $this->line('👥 NPC Statistics:');
        $this->line("   Total NPCs: {$npcCount}");
        foreach ($npcByProfession as $profession => $count) {
            $this->line("   {$profession}: {$count}");
        }
        $this->newLine();

        // Adventure Statistics
        $adventureCount = Adventure::count();
        $completedAdventures = Adventure::where('status', 'completed')->count();
        $activeAdventures = Adventure::where('status', 'active')->count();
        $averageDuration = Adventure::avg('estimated_duration');

        $this->line('📍 Adventure Statistics:');
        $this->line("   Total Adventures: {$adventureCount}");
        $this->line("   Completed: {$completedAdventures}");
        $this->line("   Active: {$activeAdventures}");
        $this->line("   Average Duration: " . round($averageDuration, 1) . " minutes");
        $this->newLine();

        // Achievement Statistics
        $achievementCount = Achievement::count();
        $unlockedCount = PlayerAchievement::count();
        $uniqueUnlockers = PlayerAchievement::distinct('player_id')->count('player_id');

        $this->line('🏆 Achievement Statistics:');
        $this->line("   Total Achievements: {$achievementCount}");
        $this->line("   Total Unlocked: {$unlockedCount}");
        $this->line("   Players with Achievements: {$uniqueUnlockers}");
        if ($achievementCount > 0 && $playerCount > 0) {
            $completionRate = round(($unlockedCount / ($achievementCount * $playerCount)) * 100, 1);
            $this->line("   Completion Rate: {$completionRate}%");
        }
        $this->newLine();

        // Faction Statistics
        $factionCount = Faction::count();
        $reputationCount = FactionReputation::count();
        $averageReputation = FactionReputation::avg('points');

        $this->line('🤝 Faction Statistics:');
        $this->line("   Total Factions: {$factionCount}");
        $this->line("   Player-Faction Relations: {$reputationCount}");
        $this->line("   Average Reputation: " . round($averageReputation, 1));

        return 0;
    }

    private function resetPlayer($playerId, $force)
    {
        if (!$playerId) {
            $this->error('Player ID is required for reset-player action.');
            return 1;
        }

        $player = Player::find($playerId);
        if (!$player) {
            $this->error("Player with ID {$playerId} not found.");
            return 1;
        }

        if (!$force && !$this->confirm("Are you sure you want to reset player '{$player->name}' (ID: {$playerId})? This will delete all their progress.")) {
            $this->info('Reset cancelled.');
            return 0;
        }

        // Delete related data
        $player->npcs()->delete();
        $player->adventures()->delete();
        $player->achievements()->detach();
        $player->factionReputations()->delete();

        // Reset player stats
        $player->update([
            'level' => 1,
            'experience' => 0,
            'gold' => 100,
            'health' => 50,
            'max_health' => 50,
            'strength' => 10,
            'intelligence' => 10,
            'wisdom' => 10,
            'village_data' => json_encode([
                'name' => 'New Village',
                'level' => 1,
                'description' => 'A fresh start in the grasslands'
            ])
        ]);

        $this->info("Player '{$player->name}' has been reset to starting conditions.");
        return 0;
    }

    private function addGold($playerId, $value)
    {
        if (!$playerId) {
            $this->error('Player ID is required for add-gold action.');
            return 1;
        }

        if (!$value || !is_numeric($value)) {
            $this->error('Valid gold amount is required (use --value=amount).');
            return 1;
        }

        $player = Player::find($playerId);
        if (!$player) {
            $this->error("Player with ID {$playerId} not found.");
            return 1;
        }

        $oldGold = $player->gold;
        $player->gold += (int)$value;
        $player->save();

        $this->info("Added {$value} gold to '{$player->name}'. Gold: {$oldGold} → {$player->gold}");
        return 0;
    }

    private function setLevel($playerId, $value)
    {
        if (!$playerId) {
            $this->error('Player ID is required for set-level action.');
            return 1;
        }

        if (!$value || !is_numeric($value) || $value < 1 || $value > 100) {
            $this->error('Valid level (1-100) is required (use --value=level).');
            return 1;
        }

        $player = Player::find($playerId);
        if (!$player) {
            $this->error("Player with ID {$playerId} not found.");
            return 1;
        }

        $oldLevel = $player->level;
        $player->level = (int)$value;
        $player->experience = $player->level * 100; // Set appropriate XP
        $player->save();

        $this->info("Set '{$player->name}' level: {$oldLevel} → {$player->level}");
        return 0;
    }

    private function unlockAchievement($playerId, $value)
    {
        if (!$playerId) {
            $this->error('Player ID is required for unlock-achievement action.');
            return 1;
        }

        if (!$value) {
            $this->error('Achievement ID is required (use --value=achievement_id).');
            return 1;
        }

        $player = Player::find($playerId);
        if (!$player) {
            $this->error("Player with ID {$playerId} not found.");
            return 1;
        }

        $achievement = Achievement::find($value);
        if (!$achievement) {
            $this->error("Achievement with ID {$value} not found.");
            return 1;
        }

        if ($player->achievements()->where('achievement_id', $achievement->id)->exists()) {
            $this->warn("Player already has achievement '{$achievement->name}'.");
            return 0;
        }

        $player->achievements()->attach($achievement->id, [
            'unlocked_at' => now(),
            'progress' => $achievement->target_value ?? 1
        ]);

        $this->info("Unlocked achievement '{$achievement->name}' for '{$player->name}'.");
        return 0;
    }

    private function clearAdventures($force)
    {
        $adventureCount = Adventure::count();
        
        if ($adventureCount === 0) {
            $this->info('No adventures to clear.');
            return 0;
        }

        if (!$force && !$this->confirm("Are you sure you want to delete all {$adventureCount} adventures?")) {
            $this->info('Clear cancelled.');
            return 0;
        }

        Adventure::truncate();
        $this->info("Cleared {$adventureCount} adventures from the database.");
        return 0;
    }

    private function seedAchievements()
    {
        $existingCount = Achievement::count();
        
        if ($existingCount > 0) {
            if (!$this->confirm("There are already {$existingCount} achievements. Continue seeding?")) {
                return 0;
            }
        }

        $achievements = [
            // Adventure Achievements
            ['name' => 'First Steps', 'description' => 'Complete your first adventure', 'category' => 'exploration', 'event_type' => 'adventure_completed', 'conditions' => json_encode(['adventure_count' => 1]), 'rewards' => '50 gold', 'points' => 10],
            ['name' => 'Seasoned Explorer', 'description' => 'Complete 10 adventures', 'category' => 'exploration', 'event_type' => 'adventure_completed', 'conditions' => json_encode(['adventure_count' => 10]), 'rewards' => '200 gold', 'points' => 25],
            ['name' => 'Legendary Adventurer', 'description' => 'Complete 50 adventures', 'category' => 'exploration', 'event_type' => 'adventure_completed', 'conditions' => json_encode(['adventure_count' => 50]), 'rewards' => '1000 gold', 'points' => 100],
            
            // Combat Achievements
            ['name' => 'First Blood', 'description' => 'Win your first combat encounter', 'category' => 'combat', 'event_type' => 'combat_won', 'conditions' => json_encode(['combat_wins' => 1]), 'rewards' => '25 gold', 'points' => 5],
            ['name' => 'Battle Hardened', 'description' => 'Win 25 combat encounters', 'category' => 'combat', 'event_type' => 'combat_won', 'conditions' => json_encode(['combat_wins' => 25]), 'rewards' => '500 gold', 'points' => 50],
            
            // Social Achievements
            ['name' => 'First Recruit', 'description' => 'Recruit your first NPC', 'category' => 'social', 'event_type' => 'npc_recruited', 'conditions' => json_encode(['npc_count' => 1]), 'rewards' => '100 gold', 'points' => 15],
            ['name' => 'Village Leader', 'description' => 'Have 10 NPCs in your village', 'category' => 'social', 'event_type' => 'npc_recruited', 'conditions' => json_encode(['npc_count' => 10]), 'rewards' => '750 gold', 'points' => 75],
            
            // Collection Achievements
            ['name' => 'Wealthy Merchant', 'description' => 'Accumulate 1000 gold', 'category' => 'collection', 'event_type' => 'gold_accumulated', 'conditions' => json_encode(['gold_amount' => 1000]), 'rewards' => 'Special merchant access', 'points' => 30],
            ['name' => 'Master Trainer', 'description' => 'Train NPCs 50 times', 'category' => 'social', 'event_type' => 'npc_trained', 'conditions' => json_encode(['training_count' => 50]), 'rewards' => 'Training efficiency boost', 'points' => 60],
        ];

        $created = 0;
        foreach ($achievements as $achievementData) {
            if (!Achievement::where('name', $achievementData['name'])->exists()) {
                Achievement::create($achievementData);
                $created++;
            }
        }

        $this->info("Seeded {$created} new achievements.");
        return 0;
    }

    private function seedFactions()
    {
        $existingCount = Faction::count();
        
        if ($existingCount > 0) {
            if (!$this->confirm("There are already {$existingCount} factions. Continue seeding?")) {
                return 0;
            }
        }

        $factions = [
            [
                'name' => 'Grassland Rangers',
                'description' => 'Protectors of the natural world and guardians of the grasslands.',
                'benefits' => json_encode([
                    1 => ['Access to ranger outposts', '+5% nature adventure success'],
                    2 => ['Discounted healing potions', 'Animal companion summoning'],
                    3 => ['Advanced tracking abilities', '+10% exploration XP'],
                    4 => ['Elite ranger training', 'Weather immunity'],
                    5 => ['Legendary ranger status', 'Command nature spirits']
                ])
            ],
            [
                'name' => 'Merchant Guild',
                'description' => 'A powerful organization of traders and craftsmen seeking profit and prosperity.',
                'benefits' => json_encode([
                    1 => ['10% better shop prices', 'Basic trade routes access'],
                    2 => ['20% better shop prices', 'Caravan protection services'],
                    3 => ['30% better shop prices', 'Exclusive merchant quests'],
                    4 => ['Private vault access', 'Investment opportunities'],
                    5 => ['Guild leadership position', 'City trading monopolies']
                ])
            ],
            [
                'name' => 'Scholars of the Ancient Ways',
                'description' => 'Keepers of knowledge and seekers of magical understanding.',
                'benefits' => json_encode([
                    1 => ['Library access', '+5% magic damage'],
                    2 => ['Spell research assistance', 'Magical item identification'],
                    3 => ['Advanced spell scrolls', '+10% mana regeneration'],
                    4 => ['Artifact creation rights', 'Portal network access'],
                    5 => ['Archmage council membership', 'Reality manipulation']
                ])
            ],
            [
                'name' => 'Iron Brotherhood',
                'description' => 'A fellowship of warriors and smiths dedicated to martial excellence.',
                'benefits' => json_encode([
                    1 => ['Weapon maintenance', '+5% physical damage'],
                    2 => ['Combat training access', 'Armor repair services'],
                    3 => ['Master-crafted weapons', '+10% critical hit chance'],
                    4 => ['Legendary weapon forging', 'Battle formation tactics'],
                    5 => ['Warlord status', 'Command elite armies']
                ])
            ]
        ];

        $created = 0;
        foreach ($factions as $factionData) {
            if (!Faction::where('name', $factionData['name'])->exists()) {
                Faction::create($factionData);
                $created++;
            }
        }

        $this->info("Seeded {$created} new factions.");
        return 0;
    }

    private function cleanup($force)
    {
        if (!$force && !$this->confirm('This will clean up old/incomplete data. Continue?')) {
            return 0;
        }

        $cleaned = 0;

        // Remove adventures older than 30 days with no activity
        $oldAdventures = Adventure::where('updated_at', '<', now()->subDays(30))
            ->where('status', '!=', 'completed')
            ->count();
        
        if ($oldAdventures > 0) {
            Adventure::where('updated_at', '<', now()->subDays(30))
                ->where('status', '!=', 'completed')
                ->delete();
            $cleaned += $oldAdventures;
            $this->line("Removed {$oldAdventures} stale adventures");
        }

        // Remove NPCs with 0 health for more than 7 days
        $deadNPCs = NPC::where('health', '<=', 0)
            ->where('updated_at', '<', now()->subDays(7))
            ->count();
        
        if ($deadNPCs > 0) {
            NPC::where('health', '<=', 0)
                ->where('updated_at', '<', now()->subDays(7))
                ->delete();
            $cleaned += $deadNPCs;
            $this->line("Removed {$deadNPCs} dead NPCs");
        }

        if ($cleaned > 0) {
            $this->info("Cleanup completed. Removed {$cleaned} records.");
        } else {
            $this->info('No cleanup needed.');
        }

        return 0;
    }

    private function showHelp()
    {
        $this->info('🎮 Grassland Awakening - Admin Tools');
        $this->newLine();
        
        $this->line('Available Actions:');
        $this->line('  stats              - Show game statistics');
        $this->line('  reset-player       - Reset a player to starting conditions (requires --player)');
        $this->line('  add-gold           - Add gold to a player (requires --player and --value)');
        $this->line('  set-level          - Set player level (requires --player and --value)');
        $this->line('  unlock-achievement - Unlock achievement for player (requires --player and --value)');
        $this->line('  clear-adventures   - Delete all adventures (use --force to skip confirmation)');
        $this->line('  seed-achievements  - Create default achievements');
        $this->line('  seed-factions      - Create default factions');
        $this->line('  cleanup            - Clean up old/stale data (use --force to skip confirmation)');
        $this->line('  help               - Show this help message');
        $this->newLine();
        
        $this->line('Options:');
        $this->line('  --player=ID        - Player ID for player-specific actions');
        $this->line('  --value=VALUE      - Value parameter for actions that need it');
        $this->line('  --force            - Skip confirmation prompts');
        $this->newLine();
        
        $this->line('Examples:');
        $this->line('  php artisan game:admin stats');
        $this->line('  php artisan game:admin add-gold --player=1 --value=500');
        $this->line('  php artisan game:admin set-level --player=1 --value=10');
        $this->line('  php artisan game:admin seed-achievements');
        $this->line('  php artisan game:admin cleanup --force');

        return 0;
    }
}