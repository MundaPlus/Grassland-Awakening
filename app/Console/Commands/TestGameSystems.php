<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Player;
use App\Models\NPC;
use App\Models\Adventure;
use App\Models\Achievement;
use App\Models\Faction;
use App\Services\AdventureService;
use App\Services\WeatherService;
use App\Services\NPCService;
use App\Services\CombatService;
use App\Services\AchievementService;
use App\Services\ReputationService;

class TestGameSystems extends Command
{
    protected $signature = 'game:test-systems {--player=1 : Player ID to test with} {--verbose : Show detailed output}';
    protected $description = 'Test all game systems to ensure they are working correctly';

    public function handle()
    {
        $playerId = $this->option('player');
        $verbose = $this->option('verbose');

        $this->info('🎮 Starting Grassland Awakening System Tests');
        $this->newLine();

        // Get or create test player
        $player = Player::find($playerId);
        if (!$player) {
            $this->warn("Player {$playerId} not found. Creating test player...");
            $player = Player::create([
                'user_id' => 1,
                'name' => 'Test Hero',
                'level' => 5,
                'experience' => 500,
                'gold' => 1000,
                'health' => 100,
                'max_health' => 100,
                'strength' => 15,
                'intelligence' => 12,
                'wisdom' => 10,
                'village_data' => json_encode([
                    'name' => 'Test Village',
                    'level' => 3,
                    'description' => 'A test village for system validation'
                ])
            ]);
            $this->info("✅ Created test player: {$player->name} (ID: {$player->id})");
        } else {
            $this->info("🎭 Using existing player: {$player->name} (Level {$player->level})");
        }

        $this->newLine();
        $passed = 0;
        $failed = 0;

        // Test 1: Adventure Service
        $this->line('📍 Testing Adventure Generation Service...');
        try {
            $adventureService = app(AdventureService::class);
            $adventure = $adventureService->generateAdventure($player, 'test-seed-123');
            
            if ($adventure && $adventure->exists) {
                $this->info("  ✅ Adventure generated: '{$adventure->title}' ({$adventure->difficulty})");
                if ($verbose) {
                    $this->line("     Road Type: {$adventure->road_type}");
                    $this->line("     Duration: {$adventure->estimated_duration} minutes");
                    $this->line("     Rewards: {$adventure->gold_reward} gold, {$adventure->experience_reward} XP");
                }
                $passed++;
            } else {
                $this->error('  ❌ Failed to generate adventure');
                $failed++;
            }
        } catch (\Exception $e) {
            $this->error("  ❌ Adventure Service Error: {$e->getMessage()}");
            $failed++;
        }

        // Test 2: Weather Service
        $this->line('🌤️  Testing Weather Service...');
        try {
            $weatherService = app(WeatherService::class);
            $weather = $weatherService->getCurrentWeather();
            
            if ($weather && isset($weather['condition'])) {
                $this->info("  ✅ Weather data retrieved: {$weather['condition']}");
                if ($verbose) {
                    $this->line("     Temperature: {$weather['temperature']}°C");
                    $this->line("     Season: {$weather['season']}");
                    if (isset($weather['effects'])) {
                        $this->line("     Combat Effects: {$weather['effects']}");
                    }
                }
                $passed++;
            } else {
                $this->error('  ❌ Invalid weather data structure');
                $failed++;
            }
        } catch (\Exception $e) {
            $this->error("  ❌ Weather Service Error: {$e->getMessage()}");
            $failed++;
        }

        // Test 3: NPC Service
        $this->line('👥 Testing NPC Service...');
        try {
            $npcService = app(NPCService::class);
            $npc = $npcService->recruitNPC($player, 'Test Warrior', 'warrior');
            
            if ($npc && $npc->exists) {
                $this->info("  ✅ NPC recruited: {$npc->name} ({$npc->profession})");
                if ($verbose) {
                    $this->line("     Stats: STR:{$npc->strength} INT:{$npc->intelligence} WIS:{$npc->wisdom}");
                    $this->line("     Health: {$npc->health}/{$npc->max_health}");
                }
                
                // Test NPC training
                $trainingResult = $npcService->trainNPC($npc);
                if ($trainingResult['success']) {
                    $this->info("  ✅ NPC training successful");
                    if ($verbose && isset($trainingResult['skill_learned'])) {
                        $this->line("     New skill learned: {$trainingResult['skill_learned']}");
                    }
                } else {
                    $this->warn("  ⚠️  NPC training failed: {$trainingResult['message']}");
                }
                $passed++;
            } else {
                $this->error('  ❌ Failed to recruit NPC');
                $failed++;
            }
        } catch (\Exception $e) {
            $this->error("  ❌ NPC Service Error: {$e->getMessage()}");
            $failed++;
        }

        // Test 4: Combat Service
        $this->line('⚔️  Testing Combat Service...');
        try {
            $combatService = app(CombatService::class);
            
            // Create a test combat scenario
            $enemy = (object) [
                'name' => 'Test Goblin',
                'health' => 30,
                'max_health' => 30,
                'strength' => 10,
                'intelligence' => 8,
                'wisdom' => 6
            ];
            
            $combatResult = $combatService->processPlayerAction($player, 'attack', $enemy);
            
            if (isset($combatResult['success'])) {
                $this->info("  ✅ Combat action processed successfully");
                if ($verbose) {
                    $this->line("     Action: attack");
                    $this->line("     Result: " . ($combatResult['success'] ? 'Hit' : 'Miss'));
                    if (isset($combatResult['damage'])) {
                        $this->line("     Damage dealt: {$combatResult['damage']}");
                    }
                }
                $passed++;
            } else {
                $this->error('  ❌ Invalid combat result structure');
                $failed++;
            }
        } catch (\Exception $e) {
            $this->error("  ❌ Combat Service Error: {$e->getMessage()}");
            $failed++;
        }

        // Test 5: Achievement Service
        $this->line('🏆 Testing Achievement Service...');
        try {
            $achievementService = app(AchievementService::class);
            
            // Test achievement checking
            $achievementService->checkAchievements($player, 'adventure_completed', [
                'adventure_count' => 1,
                'difficulty' => 'easy'
            ]);
            
            $playerAchievements = $player->achievements()->count();
            $this->info("  ✅ Achievement system processed (Player has {$playerAchievements} achievements)");
            
            // Test specific achievement unlock
            $firstAdventure = Achievement::where('event_type', 'adventure_completed')->first();
            if ($firstAdventure) {
                if ($verbose) {
                    $this->line("     Available achievement: {$firstAdventure->name}");
                }
            }
            $passed++;
        } catch (\Exception $e) {
            $this->error("  ❌ Achievement Service Error: {$e->getMessage()}");
            $failed++;
        }

        // Test 6: Reputation Service
        $this->line('🤝 Testing Reputation Service...');
        try {
            $reputationService = app(ReputationService::class);
            
            // Ensure we have at least one faction
            $faction = Faction::first();
            if (!$faction) {
                Faction::create([
                    'name' => 'Test Guild',
                    'description' => 'A test faction for system validation',
                    'benefits' => json_encode([
                        1 => ['Basic guild access'],
                        2 => ['Discount on guild services'],
                        3 => ['Special guild missions']
                    ])
                ]);
                $faction = Faction::first();
            }
            
            $reputationService->addReputation($player, $faction, 50, 'System test');
            $reputation = $player->factionReputations()->where('faction_id', $faction->id)->first();
            
            if ($reputation) {
                $this->info("  ✅ Reputation system working (Player has {$reputation->points} points with {$faction->name})");
                if ($verbose) {
                    $this->line("     Faction: {$faction->name}");
                    $this->line("     Points: {$reputation->points}");
                    $this->line("     Level: {$reputation->getLevel()}");
                }
                $passed++;
            } else {
                $this->error('  ❌ Failed to add reputation');
                $failed++;
            }
        } catch (\Exception $e) {
            $this->error("  ❌ Reputation Service Error: {$e->getMessage()}");
            $failed++;
        }

        // Test 7: Database Integrity
        $this->line('🗄️  Testing Database Integrity...');
        try {
            $playerCount = Player::count();
            $npcCount = NPC::count();  
            $adventureCount = Adventure::count();
            $achievementCount = Achievement::count();
            $factionCount = Faction::count();
            
            $this->info("  ✅ Database tables accessible");
            if ($verbose) {
                $this->line("     Players: {$playerCount}");
                $this->line("     NPCs: {$npcCount}");
                $this->line("     Adventures: {$adventureCount}");
                $this->line("     Achievements: {$achievementCount}");
                $this->line("     Factions: {$factionCount}");
            }
            $passed++;
        } catch (\Exception $e) {
            $this->error("  ❌ Database Error: {$e->getMessage()}");
            $failed++;
        }

        // Summary
        $this->newLine();
        $total = $passed + $failed;
        $this->line('📊 Test Results Summary:');
        $this->info("  ✅ Passed: {$passed}/{$total}");
        if ($failed > 0) {
            $this->error("  ❌ Failed: {$failed}/{$total}");
        }
        
        $percentage = $total > 0 ? round(($passed / $total) * 100) : 0;
        $this->line("  📈 Success Rate: {$percentage}%");
        
        if ($failed === 0) {
            $this->newLine();
            $this->info('🎉 All systems are functioning correctly!');
            $this->line('The Grassland Awakening game is ready for players.');
        } else {
            $this->newLine();
            $this->warn('⚠️  Some systems need attention. Please review the failed tests above.');
        }

        return $failed === 0 ? 0 : 1;
    }
}