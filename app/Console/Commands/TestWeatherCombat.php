<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CombatService;
use App\Services\WeatherService;
use App\Models\Player;
use App\Models\User;

class TestWeatherCombat extends Command
{
    protected $signature = 'game:test-weather-combat';
    protected $description = 'Test combat with various weather conditions';

    public function handle()
    {
        $combatService = app(CombatService::class);
        $weatherService = app(WeatherService::class);

        $this->info('=== WEATHER COMBAT EFFECTS TEST ===');

        // Get test player
        $player = $this->getTestPlayer();
        $this->line("Testing with player: {$player->character_name} (Level {$player->level})");

        // Test different weather conditions
        $weatherConditions = [
            ['type' => 'clear', 'name' => 'Clear Skies', 'temperature' => 22, 'season' => 'summer'],
            ['type' => 'rain', 'name' => 'Heavy Rain', 'temperature' => 15, 'season' => 'autumn'],
            ['type' => 'storm', 'name' => 'Thunderstorm', 'temperature' => 12, 'season' => 'autumn'],
            ['type' => 'snow', 'name' => 'Blizzard', 'temperature' => -5, 'season' => 'winter'],
            ['type' => 'fog', 'name' => 'Dense Fog', 'temperature' => 8, 'season' => 'winter'],
            ['type' => 'wind', 'name' => 'Strong Winds', 'temperature' => 18, 'season' => 'spring'],
            ['type' => 'sunny', 'name' => 'Bright Sunshine', 'temperature' => 28, 'season' => 'summer']
        ];

        foreach ($weatherConditions as $weather) {
            $this->line("\n--- Testing: {$weather['name']} ({$weather['temperature']}°C, {$weather['season']}) ---");
            
            // Generate modifiers for this weather
            $modifiers = $weatherService->getCombatModifiers($weather);
            $this->displayWeatherEffects($modifiers);
            
            // Create mock adventure with this weather
            $mockAdventure = new class($weather) {
                public $generated_map;
                public function __construct($weather) {
                    $this->generated_map = ['weather' => $weather];
                }
            };
            
            // Generate enemy and test combat
            $enemy = $combatService->generateRandomEnemy($player->level);
            $this->line("Enemy: {$enemy['name']} (HP: {$enemy['hp']}, AC: {$enemy['ac']})");
            
            // Run quick combat simulation
            $combatData = $combatService->initiateCombat($player, $enemy, $mockAdventure);
            $this->simulateQuickCombat($combatService, $combatData, $player);
            
            // Reset player health for next test
            $player->hp = $player->max_hp;
            $player->save();
        }

        $this->info("\n=== WEATHER COMBAT TEST COMPLETED ===");
    }

    private function displayWeatherEffects(array $modifiers): void
    {
        $effects = [];
        foreach ($modifiers as $effect => $value) {
            if ($value != 0) {
                $sign = $value > 0 ? '+' : '';
                $effects[] = "{$effect}: {$sign}{$value}";
            }
        }
        
        if (!empty($effects)) {
            $this->line("Combat Modifiers: " . implode(', ', $effects));
        } else {
            $this->line("Combat Modifiers: None");
        }
    }

    private function simulateQuickCombat(CombatService $combatService, array $combatData, Player $player): void
    {
        $turns = 0;
        $maxTurns = 10; // Limit simulation
        
        while ($combatData['status'] === 'active' && $turns < $maxTurns) {
            if ($combatData['current_actor'] === 'player') {
                // Simple AI: attack mostly, defend sometimes
                $action = rand(1, 100) <= 85 ? 'attack' : 'defend';
                $combatData = $combatService->executePlayerAction($combatData, $action);
            } else {
                $combatData = $combatService->executeEnemyTurn($combatData);
            }
            $turns++;
        }

        // Show result
        $result = match($combatData['status']) {
            'victory' => '✓ Player Victory',
            'defeat' => '✗ Player Defeat',
            'flee' => '~ Player Fled',
            default => '? Combat Ongoing (' . $combatData['turn'] . ' turns)'
        };
        
        $playerHp = $combatData['player']['current_hp'];
        $enemyHp = $combatData['enemy']['current_hp'];
        
        $this->line("Result: {$result} (Player: {$playerHp} HP, Enemy: {$enemyHp} HP)");
        
        // Show some key combat events
        $lastEvents = array_slice($combatData['combat_log'], -2, 2);
        foreach ($lastEvents as $event) {
            if (str_contains($event['message'], 'critical') || str_contains($event['message'], 'Victory') || str_contains($event['message'], 'Defeat')) {
                $this->line("  → {$event['message']}");
            }
        }
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
            'level' => 4,
            'experience' => 300,
            'persistent_currency' => 1500,
            'hp' => 42,
            'max_hp' => 42,
            'ac' => 13,
            'str' => 16,  // Stronger for better testing
            'dex' => 14,
            'con' => 15,
            'int' => 12,
            'wis' => 13,
            'cha' => 11
        ]);
    }
}