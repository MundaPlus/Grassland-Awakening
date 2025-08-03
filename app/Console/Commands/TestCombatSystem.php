<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CombatService;
use App\Services\WeatherService;
use App\Services\AdventureGenerationService;
use App\Models\Player;
use App\Models\User;

class TestCombatSystem extends Command
{
    protected $signature = 'game:test-combat {--weather} {--auto-battle} {--multiple=1}';
    protected $description = 'Test combat system with D&D mechanics and weather effects';

    public function handle()
    {
        $combatService = app(CombatService::class);
        $weatherService = app(WeatherService::class);
        $adventureService = app(AdventureGenerationService::class);

        $this->info('=== COMBAT SYSTEM TEST ===');

        // Get or create test player
        $player = $this->getTestPlayer();
        $this->line("Testing with player: {$player->character_name} (Level {$player->level})");
        $this->line("Stats: STR {$player->str} DEX {$player->dex} CON {$player->con} HP {$player->hp}/{$player->max_hp} AC {$player->ac}");

        $multiple = (int)$this->option('multiple');

        for ($i = 1; $i <= $multiple; $i++) {
            if ($multiple > 1) {
                $this->line("\n--- Combat {$i} ---");
            }

            // Generate random enemy
            $enemy = $combatService->generateRandomEnemy($player->level);
            $this->line("Enemy: {$enemy['name']} (Level {$enemy['level']}, HP {$enemy['hp']}, AC {$enemy['ac']})");

            // Create adventure if weather effects requested
            $adventure = null;
            if ($this->option('weather')) {
                $adventureData = $adventureService->generateAdventure('test-combat-' . time(), 'east', 'normal');
                
                // Create a simple adventure record for testing
                $adventure = new \App\Models\Adventure([
                    'player_id' => $player->id,
                    'seed' => $adventureData['seed'],
                    'road' => $adventureData['road'],
                    'difficulty' => $adventureData['difficulty'],
                    'generated_map' => $adventureData,
                    'current_level' => 1,
                    'current_node_id' => 'start',
                    'status' => 'active'
                ]);
                
                $weather = $adventureData['weather'];
                $temp = $weather['real_weather_data']['temperature'] ?? $weather['temperature'] ?? 'unknown';
                $this->line("Weather: {$weather['name']} in {$adventureData['season']['name']} (Temperature: {$temp}°C)");
            }

            // Initiate combat
            $combatData = $combatService->initiateCombat($player, $enemy, $adventure);
            
            if ($this->option('auto-battle')) {
                $this->runAutoCombat($combatService, $combatData, $player);
            } else {
                $this->runInteractiveCombat($combatService, $combatData, $player);
            }

            // Refresh player data for next combat
            $player->refresh();
            
            if ($player->hp <= 0) {
                $this->error("Player defeated! Healing to continue tests...");
                $player->hp = $player->max_hp;
                $player->save();
            }
        }

        $this->info('Combat system test completed!');
    }

    private function runAutoCombat(CombatService $combatService, array $combatData, Player $player): void
    {
        $this->line("\n=== AUTO COMBAT ===");
        
        while ($combatData['status'] === 'active' && $combatData['turn'] <= 20) {
            if ($combatData['current_actor'] === 'player') {
                // Simple AI for player: mostly attack, sometimes defend
                $action = rand(1, 100) <= 85 ? 'attack' : 'defend';
                $combatData = $combatService->executePlayerAction($combatData, $action);
            } else {
                $combatData = $combatService->executeEnemyTurn($combatData);
            }
        }

        // Show combat results
        $this->displayCombatLog($combatData);
        $this->displayCombatSummary($combatData);

        // Apply results to player
        $combatService->applyCombatResult($player, $combatData);
    }

    private function runInteractiveCombat(CombatService $combatService, array $combatData, Player $player): void
    {
        $this->line("\n=== INTERACTIVE COMBAT ===");
        
        while ($combatData['status'] === 'active') {
            $this->displayCombatStatus($combatData);
            
            if ($combatData['current_actor'] === 'player') {
                $action = $this->choice(
                    'Choose your action:',
                    ['attack', 'defend', 'use_item', 'flee'],
                    'attack'
                );

                if ($action === 'flee') {
                    $combatData['status'] = 'flee';
                    $this->line("{$player->character_name} flees from combat!");
                    break;
                }

                $actionData = [];
                if ($action === 'use_item') {
                    $actionData['item'] = 'health_potion';
                }

                $combatData = $combatService->executePlayerAction($combatData, $action, $actionData);
            } else {
                $this->line("\nEnemy's turn...");
                $combatData = $combatService->executeEnemyTurn($combatData);
                $this->line("Press Enter to continue...");
                $this->ask('');
            }

            // Show last combat message
            $lastLog = end($combatData['combat_log']);
            if ($lastLog) {
                $this->line($lastLog['message']);
            }
        }

        $this->displayCombatSummary($combatData);
        $combatService->applyCombatResult($player, $combatData);
    }

    private function displayCombatStatus(array $combatData): void
    {
        $this->line("\n--- Turn {$combatData['turn']} ---");
        $this->line("Player: {$combatData['player']['current_hp']}/{$combatData['player']['max_hp']} HP");
        $this->line("Enemy: {$combatData['enemy']['current_hp']}/{$combatData['enemy']['max_hp']} HP");
        
        if (!empty($combatData['weather_effects'])) {
            $this->line("Weather effects active: " . $this->formatWeatherEffects($combatData['weather_effects']));
        }
    }

    private function displayCombatLog(array $combatData): void
    {
        $this->line("\n=== COMBAT LOG ===");
        foreach ($combatData['combat_log'] as $entry) {
            $this->line("Turn {$entry['turn']}: {$entry['message']}");
        }
    }

    private function displayCombatSummary(array $combatData): void
    {
        $this->line("\n=== COMBAT SUMMARY ===");
        $this->line("Result: " . ucfirst($combatData['status']));
        $this->line("Turns taken: {$combatData['turn']}");
        $this->line("Final HP - Player: {$combatData['player']['current_hp']}/{$combatData['player']['max_hp']}, Enemy: {$combatData['enemy']['current_hp']}/{$combatData['enemy']['max_hp']}");
        
        if ($combatData['status'] === 'victory') {
            $expGained = ($combatData['enemy']['level'] ?? 1) * 25;
            $currencyGained = ($combatData['enemy']['level'] ?? 1) * 10;
            $this->line("Rewards: {$expGained} XP, {$currencyGained} gold");
        }
    }

    private function formatWeatherEffects(array $weatherEffects): string
    {
        $effects = [];
        foreach ($weatherEffects as $effect => $value) {
            if ($value != 0) {
                $sign = $value > 0 ? '+' : '';
                $effects[] = "{$effect}: {$sign}{$value}";
            }
        }
        return empty($effects) ? 'None' : implode(', ', $effects);
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

        return $user->player ?? Player::create([
            'user_id' => $user->id,
            'character_name' => 'Test Hero',
            'level' => 3,
            'experience' => 200,
            'persistent_currency' => 1000,
            'hp' => 35,
            'max_hp' => 35,
            'ac' => 14,
            'str' => 15,
            'dex' => 13,
            'con' => 14,
            'int' => 12,
            'wis' => 13,
            'cha' => 11
        ]);
    }
}
