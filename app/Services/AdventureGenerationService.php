<?php

namespace App\Services;

use App\Models\AdventureNode;

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

    public function generateAdventure(string $seed, string $road, string $difficulty = 'normal', ?string $forcedModifier = null, ?string $location = null, bool $useRealWeather = false, ?\App\Models\Adventure $adventure = null): array
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
            'title' => $this->generateAdventureTitle($road, $difficulty, $modifier),
            'description' => $this->generateAdventureDescription($specialization, $weather, $modifier),
            'specialization' => $specialization,
            'modifier' => $modifier,
            'weather' => $weather,
            'season' => $season,
            'map' => $this->generateNodeMap($adventure),
            'metadata' => [
                'generated_at' => now(),
                'estimated_duration' => $this->calculateDuration($difficulty, $modifier),
                'recommended_level' => $this->getRecommendedLevel($difficulty),
                'location' => $location,
                'uses_real_weather' => $useRealWeather
            ]
        ];
    }

    public function generateNodeMap(?\App\Models\Adventure $adventure = null): array
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
        
        // Levels 3-13: 2-3 nodes with intersections, with guaranteed rest areas
        $restAreaLevels = $this->selectRestAreaLevels(); // Select 3 random levels for rest areas
        
        for ($level = 3; $level <= 13; $level++) {
            $nodeCount = rand(2, 3);
            $map[$level] = [];
            
            for ($i = 1; $i <= $nodeCount; $i++) {
                // Force rest areas on selected levels
                if (in_array($level, $restAreaLevels) && $i === 1) {
                    $nodeType = 'rest';
                } else {
                    $nodeType = $this->randomNodeType($level);
                }
                
                $map[$level][] = $this->generateNode(
                    "{$level}-{$i}",
                    $nodeType,
                    $level
                );
            }
        }
        
        // Level 14: Rest area before boss (mandatory rest before final battle)
        $map[14] = [
            $this->generateNode('14-1', 'rest', 14),
            $this->generateNode('14-2', 'rest', 14)
        ];
        
        // Level 15: Boss node
        $map[15] = [
            $this->generateNode('15-boss', 'boss', 15)
        ];
        
        // Generate connections between nodes
        $connections = $this->generateConnections($map);
        
        return [
            'nodes' => $map,
            'connections' => $connections,
            'total_levels' => 15
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
                $itemGenerationService = app(\App\Services\ItemGenerationService::class);
                $hasItemDrop = $itemGenerationService->getCombatDropChance($level) >= rand(1, 100);
                
                return array_merge($baseNode, [
                    'enemy_type' => $this->selectEnemy($level),
                    'enemy_count' => $this->getEnemyCount($level),
                    'currency_reward' => $this->calculateCurrencyReward($level, $hasItemDrop),
                    'loot_chance' => $this->getLootChance($level),
                    'has_item_drop' => $hasItemDrop,
                    'item_type' => $hasItemDrop ? 'combat_loot' : null
                ]);
                
            case 'treasure':
                $itemGenerationService = app(\App\Services\ItemGenerationService::class);
                $hasItemDrop = $itemGenerationService->getTreasureDropChance($level) >= rand(1, 100);
                
                return array_merge($baseNode, [
                    'treasure_type' => $this->selectTreasureType($level),
                    'currency_reward' => $this->calculateCurrencyReward($level, $hasItemDrop),
                    'guaranteed_loot' => true,
                    'has_item_drop' => $hasItemDrop,
                    'item_type' => $hasItemDrop ? 'treasure_loot' : null
                ]);
                
            case 'event':
                $itemGenerationService = app(\App\Services\ItemGenerationService::class);
                $hasItemDrop = $itemGenerationService->getEventDropChance($level) >= rand(1, 100);
                
                return array_merge($baseNode, [
                    'event_type' => $this->selectEventType($level),
                    'skill_check_required' => $this->requiresSkillCheck(),
                    'outcomes' => $this->generateEventOutcomes($level),
                    'has_item_drop' => $hasItemDrop,
                    'item_type' => $hasItemDrop ? 'event_loot' : null
                ]);
                
            case 'npc_encounter':
                return array_merge($baseNode, [
                    'npc_type' => $this->selectNPCType($level),
                    'npc_data' => $this->generateNPCData($level),
                    'dialogue_options' => $this->generateDialogueOptions($level),
                    'skill_checks' => $this->generateSkillChecks($level),
                    'rewards' => $this->generateNPCRewards($level)
                ]);
                
            case 'rest':
                return array_merge($baseNode, [
                    'healing_amount' => $this->calculateHealingAmount($level),
                    'special_services' => $this->getRestServices($level)
                ]);
                
            case 'resource_gathering':
                return array_merge($baseNode, [
                    'resource_type' => $this->selectResourceType($level),
                    'resource_amount' => $this->calculateResourceAmount($level),
                    'gathering_difficulty' => $this->getGatheringDifficulty($level),
                    'currency_reward' => $this->calculateCurrencyReward($level, false) * 0.7, // Slightly less gold than treasure
                    'special_materials' => $this->hasSpecialMaterials($level)
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
        $maxLevel = count($map) - 1; // Dynamic max level based on map size
        
        for ($level = 1; $level <= $maxLevel; $level++) {
            $currentNodes = $map[$level];
            $nextNodes = $map[$level + 1];
            
            if ($level == 1) {
                // Start node connects to all level 2 nodes
                $connections[$currentNodes[0]['id']] = array_column($nextNodes, 'id');
            } else {
                // Create logical forward connections
                $currentCount = count($currentNodes);
                $nextCount = count($nextNodes);
                
                foreach ($currentNodes as $index => $currentNode) {
                    $connections[$currentNode['id']] = [];
                    
                    if ($currentCount == 1) {
                        // Single node connects to all next nodes
                        $connections[$currentNode['id']] = array_column($nextNodes, 'id');
                    } elseif ($nextCount == 1) {
                        // All current nodes connect to single next node
                        $connections[$currentNode['id']] = [$nextNodes[0]['id']];
                    } else {
                        // Position-based connections
                        if ($index == 0) {
                            // Top node connects to top 1-2 nodes
                            $connections[$currentNode['id']][] = $nextNodes[0]['id'];
                            if ($nextCount > 1) {
                                $connections[$currentNode['id']][] = $nextNodes[1]['id'];
                            }
                        } elseif ($index == $currentCount - 1) {
                            // Bottom node connects to bottom 1-2 nodes
                            $connections[$currentNode['id']][] = $nextNodes[$nextCount - 1]['id'];
                            if ($nextCount > 1 && $nextCount > 1) {
                                $connections[$currentNode['id']][] = $nextNodes[$nextCount - 2]['id'];
                            }
                        } else {
                            // Middle nodes connect to middle or all nodes
                            if ($nextCount == 2) {
                                $connections[$currentNode['id']] = array_column($nextNodes, 'id');
                            } else {
                                // Connect to center nodes
                                $centerIndex = intval($nextCount / 2);
                                $connections[$currentNode['id']][] = $nextNodes[$centerIndex]['id'];
                                if ($centerIndex > 0 && $centerIndex < $nextCount - 1) {
                                    if (mt_rand(0, 1)) {
                                        $connections[$currentNode['id']][] = $nextNodes[$centerIndex - 1]['id'];
                                    } else {
                                        $connections[$currentNode['id']][] = $nextNodes[$centerIndex + 1]['id'];
                                    }
                                }
                            }
                        }
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
        $types = ['combat', 'treasure', 'event', 'npc_encounter', 'resource_gathering'];
        
        // Add rest nodes on levels 4 and 7
        if (in_array($level, [4, 7])) {
            $types[] = 'rest';
        }
        
        $weights = [
            'combat' => 40,
            'treasure' => 18,
            'event' => 12,
            'npc_encounter' => 12,
            'resource_gathering' => 13,
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

    private function calculateCurrencyReward(int $level, bool $hasItemDrop = false, bool $isBoss = false): int
    {
        $base = 10;
        if ($isBoss) $base = 100;
        else $base = 25;
        
        $reward = $base + ($level * 5);
        
        // Reduce gold reward if node has item drop
        if ($hasItemDrop && !$isBoss) {
            $reward = intval($reward * 0.6); // 40% reduction
        }
        
        return $reward;
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

    private function generateAdventureTitle(string $road, string $difficulty, ?array $modifier): string
    {
        $roadNames = [
            'forest_path' => 'Forest Path',
            'mountain_trail' => 'Mountain Trail',
            'coastal_road' => 'Coastal Road',
            'desert_route' => 'Desert Route',
            'river_crossing' => 'River Crossing',
            'ancient_highway' => 'Ancient Highway',
            'north' => 'Northern Route',
            'south' => 'Southern Route',
            'east' => 'Eastern Route',
            'west' => 'Western Route'
        ];

        $difficultyAdjectives = [
            'easy' => 'Peaceful',
            'normal' => 'Mysterious',
            'hard' => 'Dangerous',
            'nightmare' => 'Cursed'
        ];

        $baseName = $roadNames[$road] ?? ucfirst(str_replace('_', ' ', $road));
        $adjective = $difficultyAdjectives[$difficulty] ?? 'Unknown';
        
        if ($modifier) {
            return "{$modifier['name']} {$baseName}";
        }

        return "{$adjective} {$baseName}";
    }

    private function generateAdventureDescription(array $specialization, array $weather, ?array $modifier): string
    {
        $themeDescriptions = [
            'forest_nature' => 'venture through ancient woodlands filled with mystical creatures',
            'mountain_earth' => 'climb treacherous peaks and navigate rocky terrain',
            'water_coastal' => 'journey along dangerous coastlines and briny waters',
            'desert_fire' => 'traverse scorching dunes under the blazing sun',
            'water_river' => 'navigate rushing waters and marshy wetlands',
            'ancient_ruins' => 'explore forgotten ruins and uncover ancient secrets',
            'ice_winter' => 'brave the frozen wastes and icy winds'
        ];

        $baseDescription = $themeDescriptions[$specialization['theme']] ?? 'embark on an unknown adventure';
        
        $weatherDesc = '';
        if ($weather['type'] !== 'clear') {
            $weatherDesc = " The journey is complicated by {$weather['description']}.";
        }

        $modifierDesc = '';
        if ($modifier) {
            $modifierDesc = " This path bears the mark of {$modifier['name']}.";
        }

        return "A procedurally generated adventure where you {$baseDescription}.{$weatherDesc}{$modifierDesc}";
    }

    /**
     * Select 3 random levels between 3-13 to place guaranteed rest areas
     */
    private function selectRestAreaLevels(): array
    {
        $availableLevels = range(3, 13);
        shuffle($availableLevels);
        return array_slice($availableLevels, 0, 3);
    }

    /**
     * NPC Encounter Generation Methods
     */
    private function selectNPCType(int $level): string
    {
        $npcTypes = [
            'traveler', 'merchant', 'scholar', 'guard', 'refugee', 
            'hermit', 'pilgrim', 'lost_child', 'wounded_soldier',
            'mysterious_stranger', 'artifact_hunter', 'local_guide'
        ];
        
        // Higher level encounters have more exotic NPCs
        if ($level >= 8) {
            $npcTypes = array_merge($npcTypes, [
                'ancient_spirit', 'cursed_noble', 'rogue_mage', 'exiled_knight'
            ]);
        }
        
        return $npcTypes[array_rand($npcTypes)];
    }

    private function generateNPCData(int $level): array
    {
        $names = [
            'Aldric', 'Brenna', 'Castor', 'Delia', 'Edwin', 'Fiona',
            'Gareth', 'Helena', 'Ivan', 'Jora', 'Kael', 'Luna',
            'Magnus', 'Nora', 'Oscar', 'Petra', 'Quinn', 'Raven'
        ];
        
        return [
            'name' => $names[array_rand($names)],
            'level' => max(1, $level + rand(-2, 2)),
            'disposition' => $this->generateDisposition(),
            'background' => $this->generateNPCBackground(),
            'current_situation' => $this->generateSituation($level)
        ];
    }

    private function generateDialogueOptions(int $level): array
    {
        return [
            'greet' => [
                'text' => 'Greet the stranger politely',
                'requirements' => [],
                'outcomes' => ['positive_reaction', 'neutral_reaction']
            ],
            'help_offer' => [
                'text' => 'Offer assistance',
                'requirements' => [],
                'outcomes' => ['grateful_response', 'suspicious_response', 'grateful_reward']
            ],
            'intimidate' => [
                'text' => 'Demand information',
                'requirements' => ['str' => 12],
                'outcomes' => ['intimidated_compliance', 'defiant_resistance', 'combat_provoked']
            ],
            'persuade' => [
                'text' => 'Use charm and persuasion',
                'requirements' => ['cha' => 12],
                'outcomes' => ['charmed_cooperation', 'useful_information', 'special_reward']
            ],
            'investigate' => [
                'text' => 'Ask probing questions',
                'requirements' => ['int' => 12],
                'outcomes' => ['hidden_truth_revealed', 'additional_context', 'quest_opportunity']
            ]
        ];
    }

    private function generateSkillChecks(int $level): array
    {
        $checks = [];
        
        // Random skill check based on level
        if (rand(1, 100) <= 60) { // 60% chance of skill check
            $skills = ['str', 'dex', 'con', 'int', 'wis', 'cha'];
            $skill = $skills[array_rand($skills)];
            $difficulty = 10 + $level + rand(0, 4);
            
            $checks[] = [
                'skill' => $skill,
                'difficulty' => $difficulty,
                'description' => $this->getSkillCheckDescription($skill),
                'success_outcome' => 'skill_success',
                'failure_outcome' => 'skill_failure'
            ];
        }
        
        return $checks;
    }

    private function generateNPCRewards(int $level): array
    {
        return [
            'currency' => rand(20, 60) * $level,
            'experience' => rand(10, 30) * $level,
            'reputation' => rand(1, 3),
            'information' => $this->generateInformation($level),
            'potential_recruitment' => rand(1, 100) <= 20 // 20% chance NPC can be recruited
        ];
    }

    private function generateDisposition(): string
    {
        $dispositions = ['friendly', 'neutral', 'cautious', 'desperate', 'suspicious', 'grateful'];
        return $dispositions[array_rand($dispositions)];
    }

    private function generateNPCBackground(): string
    {
        $backgrounds = [
            'A traveling merchant seeking new trade routes',
            'A scholar researching ancient mysteries',
            'A refugee fleeing from distant troubles',
            'A guard separated from their patrol',
            'A pilgrim on a spiritual journey',
            'A local guide who knows secret paths'
        ];
        return $backgrounds[array_rand($backgrounds)];
    }

    private function generateSituation(int $level): string
    {
        $situations = [
            'Lost and seeking directions to the nearest town',
            'Injured and in need of healing assistance',
            'Being hunted by bandits and seeking protection',
            'Guarding a valuable cargo shipment',
            'Searching for a missing family member',
            'Investigating strange occurrences in the area'
        ];
        return $situations[array_rand($situations)];
    }

    private function getSkillCheckDescription(string $skill): string
    {
        $descriptions = [
            'str' => 'Help move heavy obstacles blocking their path',
            'dex' => 'Navigate treacherous terrain to reach them safely',
            'con' => 'Endure harsh conditions to assist them',
            'int' => 'Solve a puzzle or riddle they present',
            'wis' => 'Perceive hidden dangers or true intentions',
            'cha' => 'Convince them to trust you with sensitive information'
        ];
        
        return $descriptions[$skill] ?? 'Face an unknown challenge';
    }

    private function generateInformation(int $level): array
    {
        $infoTypes = [
            'Hidden treasure location nearby',
            'Shortcut to avoid dangerous enemies',
            'Warning about upcoming hazards',
            'Local lore and legends',
            'Information about the road ahead',
            'Rumors from other travelers'
        ];
        
        return [
            'type' => $infoTypes[array_rand($infoTypes)],
            'value' => rand(1, $level),
            'description' => 'Valuable information about the area'
        ];
    }

    /**
     * Resource Gathering Methods
     */
    private function selectResourceType(int $level): string
    {
        $resourceTypes = [
            'mining' => ['metal', 'gem'],
            'herbalism' => ['herb_health', 'herb_mana'],
            'logging' => ['wood'],
            'foraging' => ['herb_health', 'herb_mana', 'wood']
        ];
        
        $gatheringType = array_rand($resourceTypes);
        return $gatheringType;
    }

    private function calculateResourceAmount(int $level): int
    {
        // Base amount increases with level
        $baseAmount = 2 + intval($level / 3);
        return $baseAmount + rand(0, 2);
    }

    private function getGatheringDifficulty(int $level): int
    {
        // DC for skill checks, scales with level
        return 10 + $level + rand(0, 3);
    }

    private function hasSpecialMaterials(int $level): bool
    {
        // Higher level nodes have better chance for rare materials
        $chance = min(0.3, 0.1 + ($level * 0.015));
        return mt_rand(1, 100) / 100 <= $chance;
    }
}