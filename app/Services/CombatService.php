<?php

namespace App\Services;

use App\Models\Player;
use App\Models\Adventure;
use App\Models\CombatLog;
use App\Models\WeatherEvent;
use Illuminate\Support\Facades\Log;

class CombatService
{
    private WeatherService $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    public function initiateCombat(Player $player, array $enemy, $adventure = null): array
    {
        $combatData = [
            'player' => $this->preparePlayerForCombat($player),
            'enemy' => $this->prepareEnemyForCombat($enemy),
            'turn' => 1,
            'status' => 'active',
            'weather_effects' => [],
            'combat_log' => [],
            'adventure_id' => ($adventure instanceof Adventure) ? $adventure->id : null
        ];

        // Apply weather effects if in adventure
        if ($adventure) {
            $weatherEffects = $this->getWeatherEffects($adventure);
            $combatData['weather_effects'] = $weatherEffects;
            $this->applyWeatherToInitiative($combatData, $weatherEffects);
        }

        // Determine turn order
        $playerInitiative = $this->rollInitiative($combatData['player'], $combatData['weather_effects']);
        $enemyInitiative = $this->rollInitiative($combatData['enemy'], $combatData['weather_effects']);
        
        $combatData['turn_order'] = $playerInitiative >= $enemyInitiative ? ['player', 'enemy'] : ['enemy', 'player'];
        $combatData['current_actor'] = $combatData['turn_order'][0];

        $this->logCombatEvent($combatData, "Combat initiated! Turn order: " . implode(' → ', $combatData['turn_order']));
        
        if (!empty($combatData['weather_effects'])) {
            $this->logCombatEvent($combatData, "Weather conditions: " . $this->formatWeatherEffects($combatData['weather_effects']));
        }

        return $combatData;
    }

    public function executePlayerAction(array $combatData, string $action, array $actionData = []): array
    {
        if ($combatData['status'] !== 'active' || $combatData['current_actor'] !== 'player') {
            return $combatData;
        }

        $result = match($action) {
            'attack' => $this->executeAttack($combatData['player'], $combatData['enemy'], $combatData['weather_effects']),
            'defend' => $this->executeDefend($combatData['player']),
            'use_item' => $this->executeUseItem($combatData['player'], $actionData['item'] ?? null),
            'special_ability' => $this->executeSpecialAbility($combatData['player'], $actionData['ability'] ?? null, $combatData['weather_effects']),
            default => ['success' => false, 'message' => 'Invalid action']
        };

        $this->logCombatEvent($combatData, $result['message']);

        // Apply damage if any
        if (isset($result['damage']) && $result['damage'] > 0) {
            $combatData['enemy']['current_hp'] -= $result['damage'];
            $combatData['enemy']['current_hp'] = max(0, $combatData['enemy']['current_hp']);
        }

        // Check for combat end
        if ($combatData['enemy']['current_hp'] <= 0) {
            return $this->endCombat($combatData, 'victory');
        }

        // Switch to enemy turn
        $combatData['current_actor'] = 'enemy';
        
        return $combatData;
    }

    public function executeEnemyTurn(array $combatData): array
    {
        if ($combatData['status'] !== 'active' || $combatData['current_actor'] !== 'enemy') {
            return $combatData;
        }

        // Simple AI: mostly attack, sometimes defend
        $action = rand(1, 100) <= 80 ? 'attack' : 'defend';
        
        $result = match($action) {
            'attack' => $this->executeAttack($combatData['enemy'], $combatData['player'], $combatData['weather_effects']),
            'defend' => $this->executeDefend($combatData['enemy']),
            default => ['success' => false, 'message' => 'Enemy confused!']
        };

        $this->logCombatEvent($combatData, $result['message']);

        // Apply damage to player if any
        if (isset($result['damage']) && $result['damage'] > 0) {
            $combatData['player']['current_hp'] -= $result['damage'];
            $combatData['player']['current_hp'] = max(0, $combatData['player']['current_hp']);
        }

        // Check for combat end
        if ($combatData['player']['current_hp'] <= 0) {
            return $this->endCombat($combatData, 'defeat');
        }

        // Reset defend bonus and advance turn
        $this->resetTurnEffects($combatData);
        $combatData['turn']++;
        $combatData['current_actor'] = 'player';

        return $combatData;
    }

    private function preparePlayerForCombat(Player $player): array
    {
        return [
            'name' => $player->character_name,
            'level' => $player->level,
            'max_hp' => $player->max_hp,
            'current_hp' => $player->hp,
            'ac' => $player->ac,
            'stats' => [
                'str' => $player->str,
                'dex' => $player->dex,
                'con' => $player->con,
                'int' => $player->int,
                'wis' => $player->wis,
                'cha' => $player->cha
            ],
            'modifiers' => [
                'str' => $player->getStatModifier('str'),
                'dex' => $player->getStatModifier('dex'),
                'con' => $player->getStatModifier('con'),
                'int' => $player->getStatModifier('int'),
                'wis' => $player->getStatModifier('wis'),
                'cha' => $player->getStatModifier('cha')
            ],
            'defending' => false,
            'temp_effects' => []
        ];
    }

    private function prepareEnemyForCombat(array $enemy): array
    {
        $baseEnemy = [
            'name' => $enemy['name'] ?? 'Unknown Enemy',
            'level' => $enemy['level'] ?? 1,
            'max_hp' => $enemy['hp'] ?? 20,
            'current_hp' => $enemy['hp'] ?? 20,
            'ac' => $enemy['ac'] ?? 12,
            'stats' => [
                'str' => $enemy['str'] ?? 12,
                'dex' => $enemy['dex'] ?? 12,
                'con' => $enemy['con'] ?? 12,
                'int' => $enemy['int'] ?? 10,
                'wis' => $enemy['wis'] ?? 10,
                'cha' => $enemy['cha'] ?? 10
            ],
            'damage_dice' => $enemy['damage_dice'] ?? '1d6',
            'damage_bonus' => $enemy['damage_bonus'] ?? 0,
            'defending' => false,
            'temp_effects' => []
        ];

        // Calculate modifiers
        $baseEnemy['modifiers'] = [];
        foreach ($baseEnemy['stats'] as $stat => $value) {
            $baseEnemy['modifiers'][$stat] = floor(($value - 10) / 2);
        }

        return $baseEnemy;
    }

    private function getWeatherEffects($adventure): array
    {
        if (!$adventure) {
            return [];
        }

        // Handle both Adventure model and generated adventure data
        if ($adventure instanceof Adventure) {
            $currentWeather = $this->weatherService->getCurrentWeather($adventure->player);
        } else {
            // For test scenarios with generated adventure data
            $currentWeather = $adventure->generated_map['weather'] ?? [];
        }
        
        return $this->weatherService->getCombatModifiers($currentWeather);
    }

    private function applyWeatherToInitiative(array &$combatData, array $weatherEffects): void
    {
        if (isset($weatherEffects['initiative_modifier'])) {
            $combatData['player']['temp_effects']['initiative_modifier'] = $weatherEffects['initiative_modifier'];
            $combatData['enemy']['temp_effects']['initiative_modifier'] = $weatherEffects['initiative_modifier'];
        }
    }

    private function rollInitiative(array $combatant, array $weatherEffects): int
    {
        $roll = rand(1, 20);
        $dexModifier = $combatant['modifiers']['dex'] ?? 0;
        $weatherModifier = $weatherEffects['initiative_modifier'] ?? 0;
        
        return $roll + $dexModifier + $weatherModifier;
    }

    private function executeAttack(array $attacker, array $target, array $weatherEffects = []): array
    {
        // Roll to hit (d20 + proficiency + stat modifier + weather)
        $attackRoll = rand(1, 20);
        $attackBonus = $this->getAttackBonus($attacker);
        $weatherBonus = $weatherEffects['attack_modifier'] ?? 0;
        $totalAttack = $attackRoll + $attackBonus + $weatherBonus;

        $targetAC = $target['ac'] + ($target['defending'] ? 2 : 0);

        if ($attackRoll === 20) {
            // Critical hit
            $damage = $this->rollDamage($attacker, true, $weatherEffects);
            return [
                'success' => true,
                'critical' => true,
                'damage' => $damage,
                'message' => "{$attacker['name']} scores a critical hit! Deals {$damage} damage!"
            ];
        } elseif ($attackRoll === 1) {
            // Critical miss
            return [
                'success' => false,
                'critical_miss' => true,
                'damage' => 0,
                'message' => "{$attacker['name']} critically misses!"
            ];
        } elseif ($totalAttack >= $targetAC) {
            // Hit
            $damage = $this->rollDamage($attacker, false, $weatherEffects);
            return [
                'success' => true,
                'damage' => $damage,
                'message' => "{$attacker['name']} hits for {$damage} damage! (Roll: {$attackRoll}, Total: {$totalAttack} vs AC {$targetAC})"
            ];
        } else {
            // Miss
            return [
                'success' => false,
                'damage' => 0,
                'message' => "{$attacker['name']} misses! (Roll: {$attackRoll}, Total: {$totalAttack} vs AC {$targetAC})"
            ];
        }
    }

    private function executeDefend(array &$defender): array
    {
        $defender['defending'] = true;
        return [
            'success' => true,
            'message' => "{$defender['name']} takes a defensive stance! (+2 AC until next turn)"
        ];
    }

    private function executeUseItem(array &$user, ?string $item): array
    {
        // Simplified item system - health potions
        if ($item === 'health_potion') {
            $healing = rand(10, 20);
            $user['current_hp'] = min($user['max_hp'], $user['current_hp'] + $healing);
            return [
                'success' => true,
                'healing' => $healing,
                'message' => "{$user['name']} drinks a health potion and recovers {$healing} HP!"
            ];
        }

        return [
            'success' => false,
            'message' => "{$user['name']} fumbles with their items!"
        ];
    }

    private function executeSpecialAbility(array $user, ?string $ability, array $weatherEffects = []): array
    {
        return match($ability) {
            'power_attack' => [
                'success' => true,
                'message' => "{$user['name']} prepares a powerful strike! (Next attack deals extra damage)"
            ],
            'dodge' => [
                'success' => true,
                'message' => "{$user['name']} focuses on evasion! (Harder to hit until next turn)"
            ],
            default => [
                'success' => false,
                'message' => "{$user['name']} attempts an unknown ability!"
            ]
        };
    }

    private function getAttackBonus(array $combatant): int
    {
        $proficiencyBonus = max(2, floor($combatant['level'] / 4) + 2);
        $statModifier = $combatant['modifiers']['str'] ?? 0; // Assuming strength-based attacks
        return $proficiencyBonus + $statModifier;
    }

    private function rollDamage(array $attacker, bool $critical = false, array $weatherEffects = []): int
    {
        $damageDice = $attacker['damage_dice'] ?? '1d6';
        $damageBonus = $attacker['damage_bonus'] ?? 0;
        $statModifier = $attacker['modifiers']['str'] ?? 0;
        $weatherModifier = $weatherEffects['damage_modifier'] ?? 0;

        // Parse damage dice (e.g., "1d6", "2d8")
        if (preg_match('/(\d+)d(\d+)/', $damageDice, $matches)) {
            $numDice = (int)$matches[1];
            $dieSize = (int)$matches[2];
            
            $damage = 0;
            for ($i = 0; $i < $numDice; $i++) {
                $damage += rand(1, $dieSize);
                if ($critical) {
                    $damage += rand(1, $dieSize); // Double dice on crit
                }
            }
            
            return $damage + $damageBonus + $statModifier + $weatherModifier;
        }

        // Fallback
        $baseDamage = rand(1, 6) + $damageBonus + $statModifier + $weatherModifier;
        return $critical ? $baseDamage * 2 : $baseDamage;
    }

    private function resetTurnEffects(array &$combatData): void
    {
        $combatData['player']['defending'] = false;
        $combatData['enemy']['defending'] = false;
    }

    private function endCombat(array $combatData, string $result): array
    {
        $combatData['status'] = $result;
        $combatData['end_turn'] = $combatData['turn'];

        $message = match($result) {
            'victory' => "Victory! {$combatData['enemy']['name']} has been defeated!",
            'defeat' => "Defeat! {$combatData['player']['name']} has fallen in combat!",
            'flee' => "{$combatData['player']['name']} successfully flees from combat!",
            default => "Combat ended."
        };

        $this->logCombatEvent($combatData, $message);

        // Save combat log to database if adventure exists
        if (isset($combatData['adventure_id'])) {
            $this->saveCombatLog($combatData);
        }

        return $combatData;
    }

    private function logCombatEvent(array &$combatData, string $message): void
    {
        $combatData['combat_log'][] = [
            'turn' => $combatData['turn'],
            'message' => $message,
            'timestamp' => now()
        ];
    }

    private function formatWeatherEffects(array $weatherEffects): string
    {
        $effects = [];
        foreach ($weatherEffects as $effect => $value) {
            if ($value > 0) {
                $effects[] = "{$effect}: +{$value}";
            } elseif ($value < 0) {
                $effects[] = "{$effect}: {$value}";
            }
        }
        return empty($effects) ? 'No weather effects' : implode(', ', $effects);
    }

    private function saveCombatLog(array $combatData): void
    {
        try {
            CombatLog::create([
                'adventure_id' => $combatData['adventure_id'],
                'combat_data' => $combatData,
                'result' => $combatData['status'],
                'turns_taken' => $combatData['turn'],
                'player_final_hp' => $combatData['player']['current_hp'],
                'enemy_final_hp' => $combatData['enemy']['current_hp']
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save combat log: ' . $e->getMessage());
        }
    }

    public function applyCombatResult(Player $player, array $combatData): void
    {
        // Update player HP
        $player->hp = $combatData['player']['current_hp'];
        
        if ($combatData['status'] === 'victory') {
            // Award experience and currency
            $expGained = ($combatData['enemy']['level'] ?? 1) * 25;
            $currencyGained = ($combatData['enemy']['level'] ?? 1) * 10;
            
            $player->experience += $expGained;
            $player->persistent_currency += $currencyGained;
            
            // Check for level up
            if ($player->canLevelUp()) {
                $player->levelUp();
            }
        }
        
        $player->save();
    }

    public function generateRandomEnemy(int $playerLevel): array
    {
        $enemyTypes = [
            'Goblin Scout' => ['hp' => 15, 'ac' => 13, 'str' => 10, 'dex' => 14, 'damage_dice' => '1d6'],
            'Orc Warrior' => ['hp' => 25, 'ac' => 14, 'str' => 16, 'dex' => 10, 'damage_dice' => '1d8+2'],
            'Dire Wolf' => ['hp' => 20, 'ac' => 12, 'str' => 15, 'dex' => 13, 'damage_dice' => '1d6+2'],
            'Skeleton' => ['hp' => 18, 'ac' => 15, 'str' => 12, 'dex' => 14, 'damage_dice' => '1d6+1'],
            'Bandit' => ['hp' => 22, 'ac' => 12, 'str' => 13, 'dex' => 15, 'damage_dice' => '1d6+1'],
            'Giant Spider' => ['hp' => 16, 'ac' => 14, 'str' => 12, 'dex' => 16, 'damage_dice' => '1d6'],
        ];

        $enemyName = array_rand($enemyTypes);
        $enemy = $enemyTypes[$enemyName];
        $enemy['name'] = $enemyName;
        $enemy['level'] = max(1, $playerLevel + rand(-1, 1));

        // Scale enemy stats to player level
        $scaleFactor = 1 + ($enemy['level'] - 1) * 0.2;
        $enemy['hp'] = (int)ceil($enemy['hp'] * $scaleFactor);
        $enemy['str'] = (int)ceil($enemy['str'] * (1 + ($enemy['level'] - 1) * 0.1));
        $enemy['dex'] = (int)ceil($enemy['dex'] * (1 + ($enemy['level'] - 1) * 0.1));

        return $enemy;
    }
}