<?php

namespace App\Services;

use App\Models\Player;
use App\Models\NPC;
use App\Models\NPCSkill;
use App\Models\VillageSpecialization;
use Illuminate\Support\Collection;

class NPCService
{
    private array $npcPersonalities = [
        'friendly' => [
            'traits' => ['helpful', 'optimistic', 'social'],
            'relationship_modifier' => 1.2,
            'migration_preference' => 'trading_hub',
            'service_quality_bonus' => 0.1
        ],
        'gruff' => [
            'traits' => ['direct', 'reliable', 'hardworking'],
            'relationship_modifier' => 0.8,
            'migration_preference' => 'military_outpost',
            'service_quality_bonus' => 0.05
        ],
        'mysterious' => [
            'traits' => ['secretive', 'knowledgeable', 'independent'],
            'relationship_modifier' => 0.9,
            'migration_preference' => 'magical_academy',
            'service_quality_bonus' => 0.15
        ],
        'cheerful' => [
            'traits' => ['energetic', 'encouraging', 'loyal'],
            'relationship_modifier' => 1.1,
            'migration_preference' => 'trading_hub',
            'service_quality_bonus' => 0.08
        ],
        'serious' => [
            'traits' => ['disciplined', 'focused', 'methodical'],
            'relationship_modifier' => 1.0,
            'migration_preference' => 'military_outpost',
            'service_quality_bonus' => 0.12
        ]
    ];

    private array $npcProfessions = [
        'blacksmith' => [
            'skill_tree' => 'crafting',
            'base_services' => ['repair', 'basic_crafting'],
            'advanced_services' => ['enchantment', 'legendary_crafting'],
            'materials_needed' => ['metal', 'coal', 'gems'],
            'village_contribution' => 'equipment_quality'
        ],
        'healer' => [
            'skill_tree' => 'medicine',
            'base_services' => ['healing', 'status_removal'],
            'advanced_services' => ['resurrection', 'permanent_buffs'],
            'materials_needed' => ['herbs', 'crystals', 'holy_water'],
            'village_contribution' => 'health_services'
        ],
        'trader' => [
            'skill_tree' => 'commerce',
            'base_services' => ['buy_sell', 'local_goods'],
            'advanced_services' => ['rare_imports', 'bulk_trading'],
            'materials_needed' => ['gold', 'trade_goods', 'connections'],
            'village_contribution' => 'economic_growth'
        ],
        'scholar' => [
            'skill_tree' => 'knowledge',
            'base_services' => ['identify', 'lore'],
            'advanced_services' => ['spell_research', 'ancient_knowledge'],
            'materials_needed' => ['books', 'scrolls', 'research_materials'],
            'village_contribution' => 'magical_research'
        ],
        'guard' => [
            'skill_tree' => 'combat',
            'base_services' => ['patrol', 'training'],
            'advanced_services' => ['elite_training', 'village_defense'],
            'materials_needed' => ['weapons', 'armor', 'training_equipment'],
            'village_contribution' => 'security'
        ],
        'farmer' => [
            'skill_tree' => 'agriculture',
            'base_services' => ['food_production', 'animal_care'],
            'advanced_services' => ['magical_crops', 'livestock_breeding'],
            'materials_needed' => ['seeds', 'tools', 'fertilizer'],
            'village_contribution' => 'food_security'
        ]
    ];

    private array $skillTrees = [
        'crafting' => [
            'basic_repair' => ['level' => 1, 'unlocks' => ['advanced_repair'], 'cost' => 10],
            'advanced_repair' => ['level' => 3, 'unlocks' => ['master_crafting'], 'cost' => 25],
            'master_crafting' => ['level' => 5, 'unlocks' => ['enchantment'], 'cost' => 50],
            'enchantment' => ['level' => 7, 'unlocks' => ['legendary_crafting'], 'cost' => 100],
            'legendary_crafting' => ['level' => 10, 'unlocks' => [], 'cost' => 200]
        ],
        'medicine' => [
            'first_aid' => ['level' => 1, 'unlocks' => ['advanced_medicine'], 'cost' => 10],
            'advanced_medicine' => ['level' => 3, 'unlocks' => ['divine_healing'], 'cost' => 25],
            'divine_healing' => ['level' => 5, 'unlocks' => ['resurrection'], 'cost' => 50],
            'resurrection' => ['level' => 7, 'unlocks' => ['life_mastery'], 'cost' => 100],
            'life_mastery' => ['level' => 10, 'unlocks' => [], 'cost' => 200]
        ],
        'commerce' => [
            'local_goods' => ['level' => 1, 'unlocks' => ['regional_network'], 'cost' => 10],
            'regional_network' => ['level' => 3, 'unlocks' => ['international_commerce'], 'cost' => 25],
            'international_commerce' => ['level' => 5, 'unlocks' => ['trade_mastery'], 'cost' => 50],
            'trade_mastery' => ['level' => 7, 'unlocks' => ['economic_influence'], 'cost' => 100],
            'economic_influence' => ['level' => 10, 'unlocks' => [], 'cost' => 200]
        ],
        'knowledge' => [
            'basic_knowledge' => ['level' => 1, 'unlocks' => ['spell_research'], 'cost' => 10],
            'spell_research' => ['level' => 3, 'unlocks' => ['ancient_wisdom'], 'cost' => 25],
            'ancient_wisdom' => ['level' => 5, 'unlocks' => ['arcane_mastery'], 'cost' => 50],
            'arcane_mastery' => ['level' => 7, 'unlocks' => ['omniscience'], 'cost' => 100],
            'omniscience' => ['level' => 10, 'unlocks' => [], 'cost' => 200]
        ],
        'combat' => [
            'basic_training' => ['level' => 1, 'unlocks' => ['advanced_tactics'], 'cost' => 10],
            'advanced_tactics' => ['level' => 3, 'unlocks' => ['elite_training'], 'cost' => 25],
            'elite_training' => ['level' => 5, 'unlocks' => ['master_warrior'], 'cost' => 50],
            'master_warrior' => ['level' => 7, 'unlocks' => ['legendary_commander'], 'cost' => 100],
            'legendary_commander' => ['level' => 10, 'unlocks' => [], 'cost' => 200]
        ],
        'agriculture' => [
            'basic_farming' => ['level' => 1, 'unlocks' => ['advanced_agriculture'], 'cost' => 10],
            'advanced_agriculture' => ['level' => 3, 'unlocks' => ['magical_crops'], 'cost' => 25],
            'magical_crops' => ['level' => 5, 'unlocks' => ['master_farmer'], 'cost' => 50],
            'master_farmer' => ['level' => 7, 'unlocks' => ['agricultural_mastery'], 'cost' => 100],
            'agricultural_mastery' => ['level' => 10, 'unlocks' => [], 'cost' => 200]
        ]
    ];

    private array $villageSpecializations = [
        'military_outpost' => [
            'name' => 'Military Outpost',
            'required_npcs' => ['guard', 'blacksmith'],
            'min_npc_count' => 6,
            'bonuses' => [
                'combat_training_effectiveness' => 1.5,
                'weapon_shop_quality' => 1.3,
                'village_defense_rating' => 2.0,
                'guard_patrol_efficiency' => 1.4
            ],
            'unlocks' => ['advanced_combat_training', 'weapon_enchantment', 'fortress_upgrades']
        ],
        'trading_hub' => [
            'name' => 'Trading Hub',
            'required_npcs' => ['trader', 'farmer'],
            'min_npc_count' => 6,
            'bonuses' => [
                'item_prices_improvement' => 1.2,
                'rare_merchant_chance' => 2.0,
                'trade_route_access' => 1.5,
                'economic_growth_rate' => 1.3
            ],
            'unlocks' => ['international_trade', 'merchant_guild', 'auction_house']
        ],
        'magical_academy' => [
            'name' => 'Magical Academy',
            'required_npcs' => ['scholar', 'healer'],
            'min_npc_count' => 6,
            'bonuses' => [
                'spell_research_speed' => 1.4,
                'magical_item_availability' => 1.6,
                'arcane_library_access' => 1.0,
                'magical_education_quality' => 1.5
            ],
            'unlocks' => ['spell_creation', 'magical_research', 'teleportation_circle']
        ]
    ];

    public function generateRandomNPC(Player $player): NPC
    {
        $personality = array_rand($this->npcPersonalities);
        $profession = array_rand($this->npcProfessions);
        $name = $this->generateNPCName($personality, $profession);

        $npc = NPC::create([
            'player_id' => $player->id,
            'name' => $name,
            'personality' => $personality,
            'profession' => $profession,
            'relationship_score' => rand(0, 20), // Starting relationship
            'village_status' => 'migrating',
            'conversation_history' => [],
            'available_services' => $this->npcProfessions[$profession]['base_services']
        ]);

        // Initialize skill tree
        $this->initializeNPCSkills($npc);

        return $npc;
    }

    public function processNPCMigration(Player $player): Collection
    {
        $weather = app(WeatherService::class)->getCurrentSeason();
        $migrationChance = $weather['effects']['npc_migration_chance'] ?? 1.0;
        
        // Base migration rate: 1 NPC every 3-7 days
        $baseMigrationRate = 1 / (rand(3, 7) * 24); // Per hour
        $adjustedRate = $baseMigrationRate * $migrationChance;
        
        $newNPCs = collect();
        
        // Check if new NPC should migrate
        if (rand(1, 100) <= ($adjustedRate * 100)) {
            $newNPC = $this->generateRandomNPC($player);
            $newNPCs->push($newNPC);
        }

        // Process existing migrating NPCs
        $migratingNPCs = $player->npcs()->where('village_status', 'migrating')->get();
        foreach ($migratingNPCs as $npc) {
            if (rand(1, 100) <= 30) { // 30% chance to arrive per check
                $this->arriveNPCAtVillage($npc);
            }
        }

        return $newNPCs;
    }

    public function arriveNPCAtVillage(NPC $npc): void
    {
        $npc->update([
            'village_status' => 'settled',
            'arrived_at' => now()
        ]);

        // Check for village specialization opportunities
        $this->evaluateVillageSpecialization($npc->player);
    }

    public function trainNPCSkill(NPC $npc, string $skillName, int $currencyCost): bool
    {
        $profession = $this->npcProfessions[$npc->profession];
        $skillTree = $this->skillTrees[$profession['skill_tree']];
        
        if (!isset($skillTree[$skillName])) {
            return false;
        }

        $skill = $skillTree[$skillName];
        $npcSkill = $npc->skills()->where('skill_name', $skillName)->first();
        
        // Check if NPC meets level requirement
        if ($npc->getSkillLevel() < $skill['level']) {
            return false;
        }

        // Check if player has enough currency
        if ($npc->player->persistent_currency < $currencyCost || $currencyCost < $skill['cost']) {
            return false;
        }

        // Check prerequisites
        if (!$this->hasSkillPrerequisites($npc, $skillName)) {
            return false;
        }

        // Learn or upgrade skill
        if ($npcSkill) {
            $npcSkill->increment('level');
        } else {
            NPCSkill::create([
                'npc_id' => $npc->id,
                'skill_tree' => $profession['skill_tree'],
                'skill_name' => $skillName,
                'level' => 1,
                'abilities_json' => $this->getSkillAbilities($skillName)
            ]);
        }

        // Deduct currency
        $npc->player->decrement('persistent_currency', $currencyCost);

        // Update available services
        $this->updateNPCServices($npc);

        return true;
    }

    public function evaluateVillageSpecialization(Player $player): ?VillageSpecialization
    {
        $settledNPCs = $player->npcs()->where('village_status', 'settled')->get();
        $npcCount = $settledNPCs->count();
        
        if ($npcCount < 6) {
            return null; // Not enough NPCs for specialization
        }

        $professionCounts = $settledNPCs->groupBy('profession')->map->count();
        
        foreach ($this->villageSpecializations as $specKey => $specialization) {
            $hasRequiredNPCs = true;
            
            foreach ($specialization['required_npcs'] as $requiredProfession) {
                if (!isset($professionCounts[$requiredProfession]) || $professionCounts[$requiredProfession] < 1) {
                    $hasRequiredNPCs = false;
                    break;
                }
            }
            
            if ($hasRequiredNPCs && $npcCount >= $specialization['min_npc_count']) {
                $existing = $player->villageSpecializations()->where('specialization_type', $specKey)->first();
                
                if (!$existing) {
                    return VillageSpecialization::create([
                        'player_id' => $player->id,
                        'specialization_type' => $specKey,
                        'level' => 1,
                        'bonuses_json' => $specialization['bonuses']
                    ]);
                }
            }
        }

        return null;
    }

    public function upgradeVillageSpecialization(VillageSpecialization $specialization): bool
    {
        $spec = $this->villageSpecializations[$specialization->specialization_type];
        $nextLevel = $specialization->level + 1;
        
        // Calculate upgrade cost
        $upgradeCost = $nextLevel * 500; // Base cost scaling
        
        if ($specialization->player->persistent_currency < $upgradeCost) {
            return false;
        }

        $specialization->player->decrement('persistent_currency', $upgradeCost);
        $specialization->increment('level');
        
        // Enhance bonuses
        $currentBonuses = $specialization->bonuses_json ?? [];
        $enhancedBonuses = [];
        
        foreach ($spec['bonuses'] as $bonus => $value) {
            $enhancedBonuses[$bonus] = $value * (1 + ($nextLevel - 1) * 0.1); // 10% increase per level
        }
        
        $specialization->update(['bonuses_json' => $enhancedBonuses]);
        
        return true;
    }

    public function processNPCRelationshipChange(NPC $npc, int $relationshipChange): void
    {
        $personality = $this->npcPersonalities[$npc->personality];
        $adjustedChange = $relationshipChange * $personality['relationship_modifier'];
        
        $npc->increment('relationship_score', $adjustedChange);
        
        // Handle relationship thresholds
        if ($npc->relationship_score < -50) {
            $this->handleNPCDeparture($npc);
        } elseif ($npc->relationship_score > 100) {
            $this->unlockSpecialNPCServices($npc);
        }
    }

    public function getNPCServices(NPC $npc): array
    {
        $baseServices = $npc->available_services ?? [];
        $profession = $this->npcProfessions[$npc->profession];
        $personality = $this->npcPersonalities[$npc->personality];
        
        $services = [];
        foreach ($baseServices as $service) {
            $services[$service] = [
                'name' => $service,
                'quality_bonus' => $personality['service_quality_bonus'],
                'cost_modifier' => $this->getServiceCostModifier($npc, $service),
                'availability' => $this->getServiceAvailability($npc, $service)
            ];
        }
        
        return $services;
    }

    public function getVillageInfo(Player $player): array
    {
        $npcs = $player->npcs()->where('village_status', 'settled')->get();
        $specializations = $player->villageSpecializations;
        
        $villageLevel = $this->calculateVillageLevel($npcs->count());
        $villageType = $this->getVillageType($npcs->count());
        
        return [
            'level' => $villageLevel,
            'type' => $villageType,
            'npc_count' => $npcs->count(),
            'npcs' => $npcs,
            'specializations' => $specializations,
            'available_services' => $this->getAvailableVillageServices($npcs),
            'village_bonuses' => $this->calculateVillageBonuses($specializations),
            'next_milestone' => $this->getNextVillageMilestone($npcs->count())
        ];
    }

    private function initializeNPCSkills(NPC $npc): void
    {
        $profession = $this->npcProfessions[$npc->profession];
        $skillTree = $this->skillTrees[$profession['skill_tree']];
        
        // Give NPC a basic skill to start
        $basicSkills = array_filter($skillTree, fn($skill) => $skill['level'] === 1);
        $startingSkill = array_rand($basicSkills);
        
        NPCSkill::create([
            'npc_id' => $npc->id,
            'skill_tree' => $profession['skill_tree'],
            'skill_name' => $startingSkill,
            'level' => 1,
            'abilities_json' => $this->getSkillAbilities($startingSkill)
        ]);
    }

    private function hasSkillPrerequisites(NPC $npc, string $skillName): bool
    {
        $profession = $this->npcProfessions[$npc->profession];
        $skillTree = $this->skillTrees[$profession['skill_tree']];
        
        // Find skills that unlock this skill
        foreach ($skillTree as $existingSkill => $data) {
            if (in_array($skillName, $data['unlocks'] ?? [])) {
                $hasPrereq = $npc->skills()->where('skill_name', $existingSkill)->exists();
                if ($hasPrereq) {
                    return true;
                }
            }
        }
        
        // If no prerequisites found, check if it's a level 1 skill
        return $skillTree[$skillName]['level'] === 1;
    }

    private function updateNPCServices(NPC $npc): void
    {
        $profession = $this->npcProfessions[$npc->profession];
        $npcSkills = $npc->skills->pluck('skill_name')->toArray();
        
        $services = $profession['base_services'];
        
        // Add advanced services based on skills
        foreach ($profession['advanced_services'] as $advancedService) {
            if ($this->hasRequiredSkillsForService($npcSkills, $advancedService)) {
                $services[] = $advancedService;
            }
        }
        
        $npc->update(['available_services' => $services]);
    }

    private function hasRequiredSkillsForService(array $npcSkills, string $service): bool
    {
        // Define service requirements (simplified logic)
        $serviceRequirements = [
            'enchantment' => ['enchantment'],
            'legendary_crafting' => ['legendary_crafting'],
            'resurrection' => ['resurrection'],
            'permanent_buffs' => ['life_mastery'],
            'rare_imports' => ['international_commerce'],
            'bulk_trading' => ['trade_mastery'],
            'spell_research' => ['spell_research'],
            'ancient_knowledge' => ['ancient_wisdom'],
            'elite_training' => ['elite_training'],
            'village_defense' => ['master_warrior'],
            'magical_crops' => ['magical_crops'],
            'livestock_breeding' => ['master_farmer']
        ];
        
        $required = $serviceRequirements[$service] ?? [];
        return !empty(array_intersect($required, $npcSkills));
    }

    private function getSkillAbilities(string $skillName): array
    {
        // Define abilities for each skill
        $abilities = [
            'basic_repair' => ['repair_efficiency' => 1.0],
            'advanced_repair' => ['repair_efficiency' => 1.5, 'repair_cost_reduction' => 0.1],
            'master_crafting' => ['crafting_quality' => 1.3, 'rare_material_chance' => 0.05],
            'enchantment' => ['enchantment_power' => 1.0, 'enchantment_success_rate' => 0.8],
            'legendary_crafting' => ['legendary_chance' => 0.1, 'crafting_quality' => 2.0],
            // Add more abilities as needed
        ];
        
        return $abilities[$skillName] ?? [];
    }

    private function generateNPCName(string $personality, string $profession): string
    {
        $names = [
            'friendly' => ['Sunny', 'Hope', 'Joy', 'Felix', 'Merry'],
            'gruff' => ['Stone', 'Iron', 'Gruff', 'Hardy', 'Stern'],
            'mysterious' => ['Shadow', 'Mystic', 'Enigma', 'Void', 'Sage'],
            'cheerful' => ['Bright', 'Happy', 'Gleam', 'Spark', 'Cheer'],
            'serious' => ['Focus', 'Discipline', 'Order', 'Method', 'Precise']
        ];
        
        $personalityNames = $names[$personality] ?? ['Generic'];
        $baseName = $personalityNames[array_rand($personalityNames)];
        
        $professionSuffixes = [
            'blacksmith' => 'smith',
            'healer' => 'heal',
            'trader' => 'trade',
            'scholar' => 'wise',
            'guard' => 'guard',
            'farmer' => 'green'
        ];
        
        $suffix = $professionSuffixes[$profession] ?? '';
        
        return $baseName . ($suffix ? " the {$suffix}" : '');
    }

    private function handleNPCDeparture(NPC $npc): void
    {
        $npc->update(['village_status' => 'departed']);
        
        // Re-evaluate village specializations after NPC departure
        $this->evaluateVillageSpecialization($npc->player);
    }

    private function unlockSpecialNPCServices(NPC $npc): void
    {
        $specialServices = [
            'blacksmith' => ['masterwork_crafting', 'unique_enchantments'],
            'healer' => ['miracle_healing', 'life_extension'],
            'trader' => ['exclusive_goods', 'private_auctions'],
            'scholar' => ['forbidden_knowledge', 'spell_creation'],
            'guard' => ['personal_training', 'elite_equipment'],
            'farmer' => ['miracle_growth', 'legendary_ingredients']
        ];
        
        $services = $npc->available_services ?? [];
        $newServices = $specialServices[$npc->profession] ?? [];
        
        $npc->update(['available_services' => array_unique(array_merge($services, $newServices))]);
    }

    private function getServiceCostModifier(NPC $npc, string $service): float
    {
        $baseModifier = 1.0;
        $relationshipModifier = max(0.5, 1.0 - ($npc->relationship_score / 200)); // Better relationship = lower costs
        
        return $baseModifier * $relationshipModifier;
    }

    private function getServiceAvailability(NPC $npc, string $service): bool
    {
        // Some services might have time restrictions or requirements
        return true; // Simplified - all services always available
    }

    private function calculateVillageLevel(int $npcCount): int
    {
        if ($npcCount < 4) return 1; // Log house
        if ($npcCount < 9) return 2; // Hamlet
        return 3; // Village
    }

    private function getVillageType(int $npcCount): string
    {
        if ($npcCount < 4) return 'log_house';
        if ($npcCount < 9) return 'hamlet';
        return 'village';
    }

    private function getAvailableVillageServices(Collection $npcs): array
    {
        $allServices = [];
        foreach ($npcs as $npc) {
            $npcServices = $this->getNPCServices($npc);
            $allServices = array_merge($allServices, $npcServices);
        }
        return $allServices;
    }

    private function calculateVillageBonuses(Collection $specializations): array
    {
        $bonuses = [];
        foreach ($specializations as $spec) {
            $specBonuses = $spec->bonuses_json ?? [];
            foreach ($specBonuses as $bonus => $value) {
                $bonuses[$bonus] = ($bonuses[$bonus] ?? 1.0) * $value;
            }
        }
        return $bonuses;
    }

    private function getNextVillageMilestone(int $currentNPCs): array
    {
        $milestones = [
            4 => 'Hamlet status',
            6 => 'Village specialization available',
            9 => 'Full village status',
            12 => 'Major settlement',
            15 => 'City status'
        ];
        
        foreach ($milestones as $count => $milestone) {
            if ($currentNPCs < $count) {
                return [
                    'next_count' => $count,
                    'milestone' => $milestone,
                    'npcs_needed' => $count - $currentNPCs
                ];
            }
        }
        
        return ['next_count' => null, 'milestone' => 'Maximum development reached', 'npcs_needed' => 0];
    }
}