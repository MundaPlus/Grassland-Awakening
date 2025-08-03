<?php

namespace App\Services;

class AdventureGenerationService
{
    private array $roadSpecializations = [
        'north' => [
            'theme' => 'ice_winter',
            'enemies' => ['ice_elemental', 'winter_wolf', 'frost_giant', 'ice_troll', 'frozen_undead'],
            'materials' => ['ice_crystal', 'winter_fur', 'cold_resistant_metal', 'frost_essence'],
            'focus' => 'defensive_encounters',
            'weather_chance' => ['snow' => 0.6, 'fog' => 0.2, 'clear' => 0.2]
        ],
        'south' => [
            'theme' => 'desert_fire',
            'enemies' => ['fire_elemental', 'desert_raider', 'sand_worm', 'flame_spirit', 'sun_cultist'],
            'materials' => ['fire_gem', 'rare_mineral', 'heat_resistant_material', 'flame_essence'],
            'focus' => 'aggressive_encounters',
            'weather_chance' => ['clear' => 0.7, 'rain' => 0.1, 'fog' => 0.2]
        ],
        'east' => [
            'theme' => 'forest_nature',
            'enemies' => ['treant', 'forest_spirit', 'wild_beast', 'dryad', 'nature_guardian'],
            'materials' => ['rare_herb', 'magical_wood', 'nature_essence', 'enchanted_root'],
            'focus' => 'magic_heavy_encounters',
            'weather_chance' => ['rain' => 0.4, 'fog' => 0.3, 'clear' => 0.3]
        ],
        'west' => [
            'theme' => 'mountain_earth',
            'enemies' => ['stone_golem', 'mountain_troll', 'earth_elemental', 'crystal_spider', 'rock_dweller'],
            'materials' => ['precious_metal', 'rare_gem', 'rare_ore', 'earth_essence'],
            'focus' => 'crafting_material_rewards',
            'weather_chance' => ['clear' => 0.5, 'fog' => 0.3, 'rain' => 0.2]
        ],
        'forest_path' => [
            'theme' => 'forest_nature',
            'enemies' => ['treant', 'forest_spirit', 'wild_beast', 'dryad', 'nature_guardian'],
            'materials' => ['rare_herb', 'magical_wood', 'nature_essence', 'enchanted_root'],
            'focus' => 'magic_heavy_encounters',
            'weather_chance' => ['rain' => 0.4, 'fog' => 0.3, 'clear' => 0.3]
        ],
        'mountain_trail' => [
            'theme' => 'mountain_earth',
            'enemies' => ['stone_golem', 'mountain_troll', 'earth_elemental', 'crystal_spider', 'rock_dweller'],
            'materials' => ['precious_metal', 'rare_gem', 'rare_ore', 'earth_essence'],
            'focus' => 'crafting_material_rewards',
            'weather_chance' => ['clear' => 0.5, 'fog' => 0.3, 'rain' => 0.2]
        ],
        'coastal_road' => [
            'theme' => 'water_coastal',
            'enemies' => ['sea_serpent', 'coastal_raider', 'water_elemental', 'siren', 'tide_guardian'],
            'materials' => ['sea_pearl', 'coral_fragment', 'salt_crystal', 'water_essence'],
            'focus' => 'water_based_encounters',
            'weather_chance' => ['rain' => 0.5, 'fog' => 0.3, 'clear' => 0.2]
        ],
        'desert_route' => [
            'theme' => 'desert_fire',
            'enemies' => ['fire_elemental', 'desert_raider', 'sand_worm', 'flame_spirit', 'sun_cultist'],
            'materials' => ['fire_gem', 'rare_mineral', 'heat_resistant_material', 'flame_essence'],
            'focus' => 'aggressive_encounters',
            'weather_chance' => ['clear' => 0.7, 'rain' => 0.1, 'fog' => 0.2]
        ],
        'river_crossing' => [
            'theme' => 'water_river',
            'enemies' => ['river_spirit', 'water_elemental', 'marsh_troll', 'aquatic_beast', 'flood_guardian'],
            'materials' => ['fresh_water_crystal', 'river_stone', 'marsh_herb', 'flow_essence'],
            'focus' => 'water_navigation_challenges',
            'weather_chance' => ['rain' => 0.6, 'fog' => 0.2, 'clear' => 0.2]
        ],
        'ancient_highway' => [
            'theme' => 'ancient_ruins',
            'enemies' => ['undead_guardian', 'ancient_construct', 'spectral_warrior', 'ruin_dweller', 'time_wraith'],
            'materials' => ['ancient_relic', 'weathered_stone', 'old_metal', 'temporal_essence'],
            'focus' => 'lore_and_mystery_encounters',
            'weather_chance' => ['fog' => 0.4, 'clear' => 0.4, 'rain' => 0.2]
        ]
    ];

    private array $adventureModifiers = [
        'cursed_road' => [
            'name' => 'Cursed Road',
            'enemy_stat_multiplier' => 1.5,
            'loot_quality_multiplier' => 2.0,
            'healing_effectiveness' => 0.75,
            'chance' => 0.1
        ],
        'merchant_caravan' => [
            'name' => 'Merchant Caravan',
            'extra_trading_nodes' => true,
            'rare_item_chance' => 0.3,
            'protection_quests' => true,
            'chance' => 0.15
        ],
        'bandit_activity' => [
            'name' => 'Bandit Activity',
            'combat_encounter_multiplier' => 1.75,
            'peaceful_event_multiplier' => 0.5,
            'bandit_boss' => true,
            'chance' => 0.12
        ],
        'blessed_path' => [
            'name' => 'Blessed Path',
            'healing_effectiveness' => 1.25,
            'divine_events' => true,
            'holy_rewards' => true,
            'chance' => 0.08
        ],
        'ancient_magic' => [
            'name' => 'Ancient Magic',
            'wild_magic_zones' => true,
            'spell_enhancement' => true,
            'magical_anomalies' => true,
            'chance' => 0.05
        ]
    ];

    public function generateAdventure(string $seed, string $road, string $difficulty = 'normal', ?string $forcedModifier = null, ?string $location = null, bool $useRealWeather = false): array
    {
        $this->setSeed($seed);
        
        $specialization = $this->roadSpecializations[$road];
        $modifier = $forcedModifier ? $this->adventureModifiers[$forcedModifier] : $this->selectRandomModifier();
        
        // Use WeatherService for enhanced weather generation
        $weatherService = app(\App\Services\WeatherService::class);
        $weather = $weatherService->getWeatherForAdventure($road, $seed, $location, $useRealWeather);
        $season = $weatherService->getCurrentSeason();
        
        return [
            'seed' => $seed,
            'road' => $road,
            'difficulty' => $difficulty,
            'specialization' => $specialization,
            'modifier' => $modifier,
            'weather' => $weather,
            'season' => $season,
            'map' => $this->generateNodeMap(),
            'metadata' => [
                'generated_at' => now(),
                'estimated_duration' => $this->calculateDuration($difficulty, $modifier),
                'recommended_level' => $this->getRecommendedLevel($difficulty),
                'location' => $location,
                'uses_real_weather' => $useRealWeather
            ]
        ];
    }

    public function generateNodeMap(): array
    {
        $map = [];
        
        // Level 1: Single start node
        $map[1] = [
            $this->generateNode('1-1', 'start', 1)
        ];
        
        // Level 2: Three lanes
        $map[2] = [
            $this->generateNode('2-1', 'combat', 2),
            $this->generateNode('2-2', $this->randomNodeType(2), 2),
            $this->generateNode('2-3', 'combat', 2)
        ];
        
        // Levels 3-9: 2-3 nodes with intersections
        for ($level = 3; $level <= 9; $level++) {
            $nodeCount = rand(2, 3);
            $map[$level] = [];
            
            for ($i = 1; $i <= $nodeCount; $i++) {
                $map[$level][] = $this->generateNode(
                    "{$level}-{$i}",
                    $this->randomNodeType($level),
                    $level
                );
            }
        }
        
        // Level 10: Boss node
        $map[10] = [
            $this->generateNode('10-boss', 'boss', 10)
        ];
        
        // Generate connections between nodes
        $connections = $this->generateConnections($map);
        
        return [
            'nodes' => $map,
            'connections' => $connections,
            'total_levels' => 10
        ];
    }

    private function generateNode(string $nodeId, string $type, int $level): array
    {
        $baseNode = [
            'id' => $nodeId,
            'type' => $type,
            'level' => $level,
            'completed' => false
        ];

        switch ($type) {
            case 'start':
                return array_merge($baseNode, [
                    'description' => 'The beginning of your adventure'
                ]);
                
            case 'combat':
                return array_merge($baseNode, [
                    'enemy_type' => $this->selectEnemy($level),
                    'enemy_count' => $this->getEnemyCount($level),
                    'currency_reward' => $this->calculateCurrencyReward($level, false),
                    'loot_chance' => $this->getLootChance($level)
                ]);
                
            case 'treasure':
                return array_merge($baseNode, [
                    'treasure_type' => $this->selectTreasureType($level),
                    'currency_reward' => $this->calculateCurrencyReward($level, true),
                    'guaranteed_loot' => true
                ]);
                
            case 'event':
                return array_merge($baseNode, [
                    'event_type' => $this->selectEventType($level),
                    'skill_check_required' => $this->requiresSkillCheck(),
                    'outcomes' => $this->generateEventOutcomes($level)
                ]);
                
            case 'rest':
                return array_merge($baseNode, [
                    'healing_amount' => $this->calculateHealingAmount($level),
                    'special_services' => $this->getRestServices($level)
                ]);
                
            case 'boss':
                return array_merge($baseNode, [
                    'boss_type' => $this->selectBoss(),
                    'currency_reward' => $this->calculateCurrencyReward($level, false, true),
                    'guaranteed_rare_loot' => true,
                    'completion_bonus' => true
                ]);
                
            default:
                return $baseNode;
        }
    }

    private function generateConnections(array $map): array
    {
        $connections = [];
        
        for ($level = 1; $level <= 9; $level++) {
            $currentNodes = $map[$level];
            $nextNodes = $map[$level + 1];
            
            foreach ($currentNodes as $currentNode) {
                $connections[$currentNode['id']] = [];
                
                // Connect to next level nodes based on position and intersection rules
                if ($level == 1) {
                    // Start node connects to all level 2 nodes
                    foreach ($nextNodes as $nextNode) {
                        $connections[$currentNode['id']][] = $nextNode['id'];
                    }
                } else {
                    // Regular nodes connect to 1-2 nodes in next level
                    $connectionCount = rand(1, min(2, count($nextNodes)));
                    $availableNodes = array_column($nextNodes, 'id');
                    
                    for ($i = 0; $i < $connectionCount; $i++) {
                        $targetIndex = array_rand($availableNodes);
                        $connections[$currentNode['id']][] = $availableNodes[$targetIndex];
                        unset($availableNodes[$targetIndex]);
                        $availableNodes = array_values($availableNodes);
                        
                        if (empty($availableNodes)) break;
                    }
                }
            }
        }
        
        return $connections;
    }

    private function setSeed(string $seed): void
    {
        $numericSeed = crc32($seed);
        mt_srand($numericSeed);
    }

    private function selectRandomModifier(): ?array
    {
        $roll = mt_rand(1, 100) / 100;
        
        foreach ($this->adventureModifiers as $key => $modifier) {
            if ($roll <= $modifier['chance']) {
                return array_merge($modifier, ['key' => $key]);
            }
        }
        
        return null;
    }

    private function generateWeather(array $specialization): string
    {
        $roll = mt_rand(1, 100) / 100;
        $cumulative = 0;
        
        foreach ($specialization['weather_chance'] as $weather => $chance) {
            $cumulative += $chance;
            if ($roll <= $cumulative) {
                return $weather;
            }
        }
        
        return 'clear';
    }

    private function randomNodeType(int $level): string
    {
        $types = ['combat', 'treasure', 'event'];
        
        // Add rest nodes on levels 4 and 7
        if (in_array($level, [4, 7])) {
            $types[] = 'rest';
        }
        
        $weights = [
            'combat' => 50,
            'treasure' => 25,
            'event' => 20,
            'rest' => 5
        ];
        
        return $this->weightedRandom($types, $weights);
    }

    private function weightedRandom(array $options, array $weights): string
    {
        $totalWeight = array_sum($weights);
        $random = mt_rand(1, $totalWeight);
        
        foreach ($options as $option) {
            $random -= $weights[$option] ?? 0;
            if ($random <= 0) {
                return $option;
            }
        }
        
        return $options[0];
    }

    private function selectEnemy(int $level): string
    {
        // This would be enhanced to use road specialization
        $enemies = ['goblin', 'orc', 'skeleton', 'wolf', 'bandit'];
        return $enemies[array_rand($enemies)];
    }

    private function getEnemyCount(int $level): int
    {
        return min(4, 1 + intval($level / 3));
    }

    private function calculateCurrencyReward(int $level, bool $isTreasure = false, bool $isBoss = false): int
    {
        $base = 10;
        if ($isBoss) $base = 100;
        elseif ($isTreasure) $base = 25;
        
        return $base + ($level * 5);
    }

    private function getLootChance(int $level): float
    {
        return min(0.8, 0.3 + ($level * 0.05));
    }

    private function selectTreasureType(int $level): string
    {
        $types = ['chest', 'cache', 'mineral_vein', 'ancient_relic'];
        return $types[array_rand($types)];
    }

    private function selectEventType(int $level): string
    {
        $events = ['mysterious_shrine', 'trader_encounter', 'riddle_stone', 'abandoned_camp'];
        return $events[array_rand($events)];
    }

    private function requiresSkillCheck(): bool
    {
        return mt_rand(1, 100) <= 70;
    }

    private function generateEventOutcomes(int $level): array
    {
        return [
            'success' => [
                'currency' => $this->calculateCurrencyReward($level, true),
                'loot_chance' => 0.6
            ],
            'failure' => [
                'currency' => 0,
                'damage' => rand(1, 3)
            ]
        ];
    }

    private function calculateHealingAmount(int $level): int
    {
        return 5 + $level;
    }

    private function getRestServices(int $level): array
    {
        $services = ['healing'];
        
        if ($level >= 5) {
            $services[] = 'stat_boost';
        }
        
        if ($level >= 7) {
            $services[] = 'equipment_repair';
        }
        
        return $services;
    }

    private function selectBoss(): string
    {
        $bosses = ['ancient_guardian', 'corrupted_spirit', 'elemental_lord', 'bandit_king'];
        return $bosses[array_rand($bosses)];
    }

    private function calculateDuration(string $difficulty, ?array $modifier): int
    {
        $baseDuration = 30; // minutes
        
        $multipliers = [
            'easy' => 0.8,
            'normal' => 1.0,
            'hard' => 1.3,
            'nightmare' => 1.6
        ];
        
        $duration = $baseDuration * ($multipliers[$difficulty] ?? 1.0);
        
        if ($modifier) {
            $duration *= 1.2; // Modifiers generally increase duration
        }
        
        return intval($duration);
    }

    private function getRecommendedLevel(string $difficulty): int
    {
        return match($difficulty) {
            'easy' => 1,
            'normal' => 3,
            'hard' => 6,
            'nightmare' => 10,
            default => 1
        };
    }
}