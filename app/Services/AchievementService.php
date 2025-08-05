<?php

namespace App\Services;

use App\Models\Player;
use App\Models\Achievement;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;

class AchievementService
{
    private array $achievementDefinitions = [
        // Combat Achievements
        'first_victory' => [
            'name' => 'First Victory',
            'description' => 'Win your first combat encounter',
            'category' => 'combat',
            'points' => 10,
            'icon' => '⚔️',
            'requirements' => ['combat_victories' => 1],
            'hidden' => false
        ],
        'combat_novice' => [
            'name' => 'Combat Novice',
            'description' => 'Win 10 combat encounters',
            'category' => 'combat',
            'points' => 25,
            'icon' => '🗡️',
            'requirements' => ['combat_victories' => 10],
            'hidden' => false
        ],
        'seasoned_fighter' => [
            'name' => 'Seasoned Fighter',
            'description' => 'Win 25 combat encounters',
            'category' => 'combat',
            'points' => 50,
            'icon' => '⚔️',
            'requirements' => ['combat_victories' => 25],
            'hidden' => false
        ],
        'veteran_warrior' => [
            'name' => 'Veteran Warrior',
            'description' => 'Win 50 combat encounters',
            'category' => 'combat',
            'points' => 100,
            'icon' => '🛡️',
            'requirements' => ['combat_victories' => 50],
            'hidden' => false
        ],
        'combat_master' => [
            'name' => 'Combat Master',
            'description' => 'Win 100 combat encounters',
            'category' => 'combat',
            'points' => 200,
            'icon' => '👑',
            'requirements' => ['combat_victories' => 100],
            'hidden' => false
        ],
        'legendary_warrior' => [
            'name' => 'Legendary Warrior',
            'description' => 'Win 250 combat encounters',
            'category' => 'combat',
            'points' => 400,
            'icon' => '⚡',
            'requirements' => ['combat_victories' => 250],
            'hidden' => false
        ],
        'combat_god' => [
            'name' => 'God of War',
            'description' => 'Win 500 combat encounters',
            'category' => 'combat',
            'points' => 750,
            'icon' => '🌟',
            'requirements' => ['combat_victories' => 500],
            'hidden' => false
        ],
        'first_critical' => [
            'name' => 'Lucky Strike',
            'description' => 'Score your first critical hit',
            'category' => 'combat',
            'points' => 15,
            'icon' => '✨',
            'requirements' => ['critical_hits' => 1],
            'hidden' => false
        ],
        'critical_master' => [
            'name' => 'Critical Master',
            'description' => 'Score 25 critical hits in combat',
            'category' => 'combat',
            'points' => 75,
            'icon' => '💥',
            'requirements' => ['critical_hits' => 25],
            'hidden' => false
        ],
        'critical_legend' => [
            'name' => 'Critical Legend',
            'description' => 'Score 100 critical hits in combat',
            'category' => 'combat',
            'points' => 150,
            'icon' => '💫',
            'requirements' => ['critical_hits' => 100],
            'hidden' => false
        ],
        'survivor' => [
            'name' => 'Survivor',
            'description' => 'Win a combat with only 1 HP remaining',
            'category' => 'combat',
            'points' => 50,
            'icon' => '💀',
            'requirements' => ['close_call_victories' => 1],
            'hidden' => false
        ],
        'boss_slayer' => [
            'name' => 'Boss Slayer',
            'description' => 'Defeat your first boss',
            'category' => 'combat',
            'points' => 100,
            'icon' => '👹',
            'requirements' => ['bosses_defeated' => 1],
            'hidden' => false
        ],
        'boss_hunter' => [
            'name' => 'Boss Hunter',
            'description' => 'Defeat 10 bosses',
            'category' => 'combat',
            'points' => 300,
            'icon' => '🏆',
            'requirements' => ['bosses_defeated' => 10],
            'hidden' => false
        ],

        // Adventure & Exploration Achievements
        'first_steps' => [
            'name' => 'First Steps',
            'description' => 'Complete your first adventure',
            'category' => 'exploration',
            'points' => 15,
            'icon' => '👣',
            'requirements' => ['adventures_completed' => 1],
            'hidden' => false
        ],
        'adventuring_novice' => [
            'name' => 'Adventuring Novice',
            'description' => 'Complete 5 adventures',
            'category' => 'exploration',
            'points' => 50,
            'icon' => '🎒',
            'requirements' => ['adventures_completed' => 5],
            'hidden' => false
        ],
        'seasoned_adventurer' => [
            'name' => 'Seasoned Adventurer',
            'description' => 'Complete 10 adventures',
            'category' => 'exploration',
            'points' => 100,
            'icon' => '🗺️',
            'requirements' => ['adventures_completed' => 10],
            'hidden' => false
        ],
        'veteran_explorer' => [
            'name' => 'Veteran Explorer',
            'description' => 'Complete 25 adventures',
            'category' => 'exploration',
            'points' => 200,
            'icon' => '🧭',
            'requirements' => ['adventures_completed' => 25],
            'hidden' => false
        ],
        'master_explorer' => [
            'name' => 'Master Explorer',
            'description' => 'Complete 50 adventures',
            'category' => 'exploration',
            'points' => 400,
            'icon' => '🏔️',
            'requirements' => ['adventures_completed' => 50],
            'hidden' => false
        ],
        'legendary_adventurer' => [
            'name' => 'Legendary Adventurer',
            'description' => 'Complete 100 adventures',
            'category' => 'exploration',
            'points' => 750,
            'icon' => '⭐',
            'requirements' => ['adventures_completed' => 100],
            'hidden' => false
        ],
        'path_master' => [
            'name' => 'Path Master',
            'description' => 'Complete 250 adventures',
            'category' => 'exploration',
            'points' => 1500,
            'icon' => '🌟',
            'requirements' => ['adventures_completed' => 250],
            'hidden' => false
        ],
        'road_master' => [
            'name' => 'Master of Roads',
            'description' => 'Complete adventures on all 10 roads',
            'category' => 'exploration',
            'points' => 300,
            'icon' => '🛤️',
            'requirements' => ['roads_explored' => 10],
            'hidden' => false
        ],
        'weather_walker' => [
            'name' => 'Weather Walker',
            'description' => 'Complete adventures in 5 different weather conditions',
            'category' => 'exploration',
            'points' => 100,
            'icon' => '🌦️',
            'requirements' => ['weather_conditions_experienced' => 5],
            'hidden' => false
        ],
        'node_explorer' => [
            'name' => 'Node Explorer',
            'description' => 'Complete 100 adventure nodes',
            'category' => 'exploration',
            'points' => 150,
            'icon' => '🔍',
            'requirements' => ['nodes_completed' => 100],
            'hidden' => false
        ],
        'node_master' => [
            'name' => 'Node Master',
            'description' => 'Complete 500 adventure nodes',
            'category' => 'exploration',
            'points' => 400,
            'icon' => '🗂️',
            'requirements' => ['nodes_completed' => 500],
            'hidden' => false
        ],
        'treasure_hunter' => [
            'name' => 'Treasure Hunter',
            'description' => 'Complete 25 treasure nodes',
            'category' => 'exploration',
            'points' => 125,
            'icon' => '💎',
            'requirements' => ['treasure_nodes_completed' => 25],
            'hidden' => false
        ],
        'resource_gatherer' => [
            'name' => 'Resource Gatherer',
            'description' => 'Complete 25 resource gathering nodes',
            'category' => 'exploration',
            'points' => 100,
            'icon' => '🌿',
            'requirements' => ['resource_nodes_completed' => 25],
            'hidden' => false
        ],

        // Village Management Achievements
        'village_founder' => [
            'name' => 'Village Founder',
            'description' => 'Attract your first NPC to the village',
            'category' => 'village',
            'points' => 20,
            'icon' => '🏘️',
            'requirements' => ['npcs_recruited' => 1],
            'hidden' => false
        ],
        'community_builder' => [
            'name' => 'Community Builder',
            'description' => 'Have 20 NPCs living in your village',
            'category' => 'village',
            'points' => 200,
            'icon' => '🏛️',
            'requirements' => ['npcs_recruited' => 20],
            'hidden' => false
        ],
        'specialist' => [
            'name' => 'Specialist',
            'description' => 'Unlock your first village specialization',
            'category' => 'village',
            'points' => 100,
            'icon' => '⭐',
            'requirements' => ['specializations_unlocked' => 1],
            'hidden' => false
        ],
        'master_planner' => [
            'name' => 'Master Planner',
            'description' => 'Unlock all 3 village specializations',
            'category' => 'village',
            'points' => 500,
            'icon' => '👑',
            'requirements' => ['specializations_unlocked' => 3],
            'hidden' => false
        ],

        // Crafting Achievements
        'first_recipe' => [
            'name' => 'Recipe Discoverer',
            'description' => 'Discover your first crafting recipe',
            'category' => 'crafting',
            'points' => 25,
            'icon' => '📜',
            'requirements' => ['recipes_discovered' => 1],
            'hidden' => false
        ],
        'recipe_collector' => [
            'name' => 'Recipe Collector',
            'description' => 'Discover 10 crafting recipes',
            'category' => 'crafting',
            'points' => 100,
            'icon' => '📚',
            'requirements' => ['recipes_discovered' => 10],
            'hidden' => false
        ],
        'recipe_master' => [
            'name' => 'Recipe Master',
            'description' => 'Discover 25 crafting recipes',
            'category' => 'crafting',
            'points' => 250,
            'icon' => '🏆',
            'requirements' => ['recipes_discovered' => 25],
            'hidden' => false
        ],
        'all_recipes' => [
            'name' => 'Master Cookbook',
            'description' => 'Discover all available crafting recipes',
            'category' => 'crafting',
            'points' => 500,
            'icon' => '📖',
            'requirements' => ['recipes_discovered' => 50], // Assuming 50 total recipes
            'hidden' => false
        ],
        'first_craft' => [
            'name' => 'First Craft',
            'description' => 'Craft your first item',
            'category' => 'crafting',
            'points' => 15,
            'icon' => '🔨',
            'requirements' => ['items_crafted' => 1],
            'hidden' => false
        ],
        'first_weapon_craft' => [
            'name' => 'Weapon Smith',
            'description' => 'Craft your first weapon',
            'category' => 'crafting',
            'points' => 50,
            'icon' => '⚒️',
            'requirements' => ['weapons_crafted' => 1],
            'hidden' => false
        ],
        'first_armor_craft' => [
            'name' => 'Armor Smith',
            'description' => 'Craft your first piece of armor',
            'category' => 'crafting',
            'points' => 50,
            'icon' => '🛡️',
            'requirements' => ['armor_crafted' => 1],
            'hidden' => false
        ],
        'first_potion_craft' => [
            'name' => 'Alchemist',
            'description' => 'Craft your first potion',
            'category' => 'crafting',
            'points' => 30,
            'icon' => '🧪',
            'requirements' => ['potions_crafted' => 1],
            'hidden' => false
        ],
        'prolific_crafter' => [
            'name' => 'Prolific Crafter',
            'description' => 'Craft 50 items',
            'category' => 'crafting',
            'points' => 150,
            'icon' => '⚙️',
            'requirements' => ['items_crafted' => 50],
            'hidden' => false
        ],
        'master_crafter' => [
            'name' => 'Master Crafter',
            'description' => 'Craft 100 items',
            'category' => 'crafting',
            'points' => 300,
            'icon' => '🏗️',
            'requirements' => ['items_crafted' => 100],
            'hidden' => false
        ],
        'legendary_crafter' => [
            'name' => 'Legendary Crafter',
            'description' => 'Craft 250 items',
            'category' => 'crafting',
            'points' => 600,
            'icon' => '👑',
            'requirements' => ['items_crafted' => 250],
            'hidden' => false
        ],

        // Equipment Achievements
        'first_equipment' => [
            'name' => 'First Equipment',
            'description' => 'Equip your first item',
            'category' => 'equipment',
            'points' => 10,
            'icon' => '👕',
            'requirements' => ['items_equipped' => 1],
            'hidden' => false
        ],
        'fully_equipped' => [
            'name' => 'Fully Equipped',
            'description' => 'Fill all equipment slots',
            'category' => 'equipment',
            'points' => 100,
            'icon' => '⚔️',
            'requirements' => ['all_slots_filled' => 1],
            'hidden' => false
        ],
        'uncommon_set' => [
            'name' => 'Uncommon Warrior',
            'description' => 'Equip uncommon or better items in all slots',
            'category' => 'equipment',
            'points' => 150,
            'icon' => '🟢',
            'requirements' => ['all_slots_uncommon_plus' => 1],
            'hidden' => false
        ],
        'rare_set' => [
            'name' => 'Rare Champion',
            'description' => 'Equip rare or better items in all slots',
            'category' => 'equipment',
            'points' => 300,
            'icon' => '🔵',
            'requirements' => ['all_slots_rare_plus' => 1],
            'hidden' => false
        ],
        'epic_set' => [
            'name' => 'Epic Hero',
            'description' => 'Equip epic or better items in all slots',
            'category' => 'equipment',
            'points' => 500,
            'icon' => '🟣',
            'requirements' => ['all_slots_epic_plus' => 1],
            'hidden' => false
        ],
        'legendary_set' => [
            'name' => 'Legendary Champion',
            'description' => 'Equip legendary items in all slots',
            'category' => 'equipment',
            'points' => 1000,
            'icon' => '🟠',
            'requirements' => ['all_slots_legendary' => 1],
            'hidden' => false
        ],
        'equipment_hoarder' => [
            'name' => 'Equipment Hoarder',
            'description' => 'Own 100 pieces of equipment',
            'category' => 'equipment',
            'points' => 100,
            'icon' => '📦',
            'requirements' => ['equipment_owned' => 100],
            'hidden' => false
        ],

        // Character Development Achievements
        'first_level' => [
            'name' => 'Level Up',
            'description' => 'Reach character level 2',
            'category' => 'character',
            'points' => 25,
            'icon' => '📊',
            'requirements' => ['player_level' => 2],
            'hidden' => false
        ],
        'level_up' => [
            'name' => 'Growing Stronger',
            'description' => 'Reach character level 5',
            'category' => 'character',
            'points' => 50,
            'icon' => '📈',
            'requirements' => ['player_level' => 5],
            'hidden' => false
        ],
        'hero_status' => [
            'name' => 'Hero Status',
            'description' => 'Reach character level 10',
            'category' => 'character',
            'points' => 150,
            'icon' => '🦸',
            'requirements' => ['player_level' => 10],
            'hidden' => false
        ],
        'legendary_hero' => [
            'name' => 'Legendary Hero',
            'description' => 'Reach character level 20',
            'category' => 'character',
            'points' => 400,
            'icon' => '🌟',
            'requirements' => ['player_level' => 20],
            'hidden' => false
        ],
        'max_level' => [
            'name' => 'Ultimate Power',
            'description' => 'Reach the maximum character level',
            'category' => 'character',
            'points' => 750,
            'icon' => '👑',
            'requirements' => ['player_level' => 50],
            'hidden' => false
        ],

        // Economy Achievements
        'first_coin' => [
            'name' => 'First Coin',
            'description' => 'Earn your first 100 gold',
            'category' => 'economy',
            'points' => 10,
            'icon' => '🪙',
            'requirements' => ['total_currency_earned' => 100],
            'hidden' => false
        ],
        'wealthy' => [
            'name' => 'Wealthy',
            'description' => 'Accumulate 10,000 gold',
            'category' => 'economy',
            'points' => 100,
            'icon' => '💰',
            'requirements' => ['total_currency_earned' => 10000],
            'hidden' => false
        ],
        'rich' => [
            'name' => 'Rich Adventurer',
            'description' => 'Accumulate 50,000 gold',
            'category' => 'economy',
            'points' => 250,
            'icon' => '💎',
            'requirements' => ['total_currency_earned' => 50000],
            'hidden' => false
        ],
        'millionaire' => [
            'name' => 'Millionaire',
            'description' => 'Accumulate 1,000,000 gold',
            'category' => 'economy',
            'points' => 1000,
            'icon' => '🏆',
            'requirements' => ['total_currency_earned' => 1000000],
            'hidden' => false
        ],
        'merchant' => [
            'name' => 'Merchant',
            'description' => 'Sell 50 items',
            'category' => 'economy',
            'points' => 75,
            'icon' => '🛒',
            'requirements' => ['items_sold' => 50],
            'hidden' => false
        ],
        'trader' => [
            'name' => 'Master Trader',
            'description' => 'Sell 200 items',
            'category' => 'economy',
            'points' => 200,
            'icon' => '💼',
            'requirements' => ['items_sold' => 200],
            'hidden' => false
        ],

        // Social Achievements
        'friend_maker' => [
            'name' => 'Friend Maker',
            'description' => 'Have 5 NPCs with friendly relationship status',
            'category' => 'social',
            'points' => 75,
            'icon' => '😊',
            'requirements' => ['friendly_npcs' => 5],
            'hidden' => false
        ],
        'beloved_leader' => [
            'name' => 'Beloved Leader',
            'description' => 'Have 10 NPCs with devoted relationship status',
            'category' => 'social',
            'points' => 200,
            'icon' => '❤️',
            'requirements' => ['devoted_npcs' => 10],
            'hidden' => false
        ],

        // Hidden/Secret Achievements
        'secret_perfectionist' => [
            'name' => 'Perfectionist',
            'description' => 'Complete an adventure without taking any damage',
            'category' => 'secret',
            'points' => 250,
            'icon' => '✨',
            'requirements' => ['perfect_adventures' => 1],
            'hidden' => true
        ],
        'secret_unlucky' => [
            'name' => 'Unlucky',
            'description' => 'Roll 5 critical misses in a single combat',
            'category' => 'secret',
            'points' => 25,
            'icon' => '🍀',
            'requirements' => ['critical_misses_single_combat' => 5],
            'hidden' => true
        ],

        // Skill-based Achievements
        'skill_novice' => [
            'name' => 'Skill Novice',
            'description' => 'Learn your first skill',
            'category' => 'character',
            'points' => 25,
            'icon' => '📖',
            'requirements' => ['skills_learned' => 1],
            'hidden' => false
        ],
        'skill_apprentice' => [
            'name' => 'Skill Apprentice', 
            'description' => 'Learn 5 different skills',
            'category' => 'character',
            'points' => 75,
            'icon' => '🎓',
            'requirements' => ['skills_learned' => 5],
            'hidden' => false
        ],
        'skill_master' => [
            'name' => 'Skill Master',
            'description' => 'Learn 10 different skills',
            'category' => 'character',
            'points' => 150,
            'icon' => '🧙‍♂️',
            'requirements' => ['skills_learned' => 10],
            'hidden' => false
        ],
        'first_max_skill' => [
            'name' => 'Mastery Achieved',
            'description' => 'Reach maximum level in any skill',
            'category' => 'character',
            'points' => 200,
            'icon' => '⭐',
            'requirements' => ['max_level_skills' => 1],
            'hidden' => false
        ],
        'smithing_adept' => [
            'name' => 'Smithing Adept',
            'description' => 'Reach level 25 in Smithing',
            'category' => 'crafting',
            'points' => 100,
            'icon' => '🔨',
            'requirements' => ['smithing_level' => 25],
            'hidden' => false
        ],
        'mining_expert' => [
            'name' => 'Mining Expert',
            'description' => 'Reach level 50 in Mining',
            'category' => 'gathering',
            'points' => 150,
            'icon' => '⛏️',
            'requirements' => ['mining_level' => 50],
            'hidden' => false
        ]
    ];

    public function checkAndUnlockAchievements(Player $player, array $eventData = []): array
    {
        $unlockedAchievements = [];
        $playerStats = $this->calculatePlayerStats($player);
        
        foreach ($this->achievementDefinitions as $achievementId => $definition) {
            // Skip if player already has this achievement
            if ($this->hasAchievement($player, $achievementId)) {
                continue;
            }

            // Check if requirements are met
            if ($this->checkRequirements($definition['requirements'], $playerStats, $eventData)) {
                $achievement = $this->unlockAchievement($player, $achievementId);
                $unlockedAchievements[] = $achievement;
            }
        }

        return $unlockedAchievements;
    }

    public function unlockAchievement(Player $player, string $achievementId): ?Achievement
    {
        $definition = $this->achievementDefinitions[$achievementId] ?? null;
        if (!$definition) {
            return null;
        }

        // Check if already unlocked
        if ($this->hasAchievement($player, $achievementId)) {
            return null;
        }

        $achievement = Achievement::create([
            'player_id' => $player->id,
            'achievement_id' => $achievementId,
            'name' => $definition['name'],
            'description' => $definition['description'],
            'category' => $definition['category'],
            'points' => $definition['points'],
            'icon' => $definition['icon'],
            'unlocked_at' => now()
        ]);

        // Award currency bonus
        $currencyBonus = $definition['points'] * 10;
        $player->persistent_currency += $currencyBonus;
        $player->save();

        // Log::info("Achievement unlocked", [
        //     'player_id' => $player->id,
        //     'achievement' => $achievementId,
        //     'points' => $definition['points'],
        //     'currency_bonus' => $currencyBonus
        // ]);

        return $achievement;
    }

    private function hasAchievement(Player $player, string $achievementId): bool
    {
        return Achievement::where('player_id', $player->id)
            ->where('achievement_id', $achievementId)
            ->exists();
    }

    private function calculatePlayerStats(Player $player): array
    {
        $npcs = $player->npcs;
        $settledNPCs = $npcs->where('village_status', 'settled');
        
        // Get player equipment stats
        $equipmentStats = $this->getEquipmentStats($player);
        
        return [
            // Character stats
            'player_level' => $player->level,
            'total_currency_earned' => $player->persistent_currency,
            
            // Combat stats
            'combat_victories' => $this->getCombatStats($player, 'victories'),
            'critical_hits' => $this->getCombatStats($player, 'critical_hits'),
            'close_call_victories' => $this->getCombatStats($player, 'close_calls'),
            'bosses_defeated' => $this->getCombatStats($player, 'bosses'),
            
            // Adventure & Exploration stats
            'adventures_completed' => $player->adventures()->where('status', 'completed')->count(),
            'roads_explored' => $this->getRoadsExplored($player),
            'weather_conditions_experienced' => $this->getWeatherConditionsExperienced($player),
            'nodes_completed' => $this->getNodesCompleted($player),
            'treasure_nodes_completed' => $this->getSpecificNodesCompleted($player, 'treasure'),
            'resource_nodes_completed' => $this->getSpecificNodesCompleted($player, 'resource_gathering'),
            'perfect_adventures' => $this->getPerfectAdventures($player),
            
            // Crafting & Recipe stats
            'recipes_discovered' => $this->getRecipesDiscovered($player),
            'items_crafted' => $this->getCraftingStats($player, 'total'),
            'weapons_crafted' => $this->getCraftingStats($player, 'weapons'),
            'armor_crafted' => $this->getCraftingStats($player, 'armor'),
            'potions_crafted' => $this->getCraftingStats($player, 'potions'),
            
            // Equipment stats
            'items_equipped' => $this->getEquipmentStats($player, 'total_equipped'),
            'all_slots_filled' => $equipmentStats['all_slots_filled'],
            'all_slots_uncommon_plus' => $equipmentStats['all_slots_uncommon_plus'],
            'all_slots_rare_plus' => $equipmentStats['all_slots_rare_plus'],
            'all_slots_epic_plus' => $equipmentStats['all_slots_epic_plus'],
            'all_slots_legendary' => $equipmentStats['all_slots_legendary'],
            'equipment_owned' => $this->getEquipmentOwned($player),
            
            // Economy stats
            'items_sold' => $this->getItemsSold($player),
            
            // Village stats
            'npcs_recruited' => $settledNPCs->count(),
            'specializations_unlocked' => $player->villageSpecializations->count(),
            'friendly_npcs' => $settledNPCs->where('relationship_score', '>=', 15)->count(),
            'devoted_npcs' => $settledNPCs->where('relationship_score', '>=', 25)->count(),
            
            // Special stats (for hidden achievements)
            'critical_misses_single_combat' => 0, // This would be tracked in real-time during combat
            
            // Skill stats
            'skills_learned' => $player->playerSkills()->count(),
            'max_level_skills' => $player->playerSkills()->whereRaw('level >= (SELECT max_level FROM skills WHERE skills.id = player_skills.skill_id)')->count(),
            'smithing_level' => $this->getSkillLevel($player, 'smithing'),
            'mining_level' => $this->getSkillLevel($player, 'mining'),
            'alchemy_level' => $this->getSkillLevel($player, 'alchemy'),
            'herbalism_level' => $this->getSkillLevel($player, 'herbalism'),
            'toughness_level' => $this->getSkillLevel($player, 'toughness'),
            'athletics_level' => $this->getSkillLevel($player, 'athletics'),
            'active_combat_skills' => $player->playerSkills()->whereHas('skill', function($query) {
                $query->where('type', 'active')->where('category', 'combat');
            })->count()
        ];
    }

    private function getSkillLevel(Player $player, string $skillSlug): int
    {
        $playerSkill = $player->playerSkills()
            ->whereHas('skill', function($query) use ($skillSlug) {
                $query->where('slug', $skillSlug);
            })->first();
            
        return $playerSkill ? $playerSkill->level : 0;
    }

    private function getCombatStats(Player $player, string $statType): int
    {
        // In a real implementation, this would query combat logs
        // For now, estimate based on level and adventures
        $completedAdventures = $player->adventures()->where('status', 'completed')->count();
        
        return match($statType) {
            'victories' => $completedAdventures * 3, // Assume 3 combats per adventure
            'critical_hits' => (int)($completedAdventures * 0.5), // 1 crit per 2 adventures
            'close_calls' => (int)($completedAdventures * 0.1), // Rare occurrences
            'bosses' => $completedAdventures, // 1 boss per adventure
            default => 0
        };
    }

    private function getNodesCompleted(Player $player): int
    {
        // In a real implementation, this would sum up all completed nodes from adventures
        // For now, estimate based on completed adventures (assume 15 nodes per adventure)
        return $player->adventures()->where('status', 'completed')->count() * 15;
    }

    private function getSpecificNodesCompleted(Player $player, string $nodeType): int
    {
        // In a real implementation, this would query specific node types from adventure data
        // For now, estimate based on completed adventures
        $completedAdventures = $player->adventures()->where('status', 'completed')->count();
        
        return match($nodeType) {
            'treasure' => (int)($completedAdventures * 2), // Assume 2 treasure nodes per adventure
            'resource_gathering' => (int)($completedAdventures * 1.5), // Assume 1-2 resource nodes per adventure
            default => 0
        };
    }

    private function getRecipesDiscovered(Player $player): int
    {
        // This would query the player's discovered recipes
        // For now, estimate based on adventures and level
        $adventuresCompleted = $player->adventures()->where('status', 'completed')->count();
        return min(50, (int)($adventuresCompleted * 0.8) + ($player->level * 0.5));
    }

    private function getCraftingStats(Player $player, string $craftType): int
    {
        // In a real implementation, this would query crafting logs
        // For now, estimate based on player level and recipes discovered
        $recipesDiscovered = $this->getRecipesDiscovered($player);
        
        return match($craftType) {
            'total' => (int)($recipesDiscovered * 1.5), // Total items crafted
            'weapons' => (int)($recipesDiscovered * 0.3), // 30% are weapons
            'armor' => (int)($recipesDiscovered * 0.4), // 40% are armor pieces
            'potions' => (int)($recipesDiscovered * 0.3), // 30% are potions
            default => 0
        };
    }

    private function getEquipmentStats(Player $player, ?string $statType = null): array|int
    {
        // Get all equipped items for the player
        $equippedItems = \App\Models\PlayerItem::where('player_id', $player->id)
            ->where('is_equipped', true)
            ->with('item')
            ->get();

        if ($statType === 'total_equipped') {
            return $equippedItems->count();
        }

        // Define equipment slots (adjust based on your game's equipment system)
        $requiredSlots = ['weapon', 'helmet', 'chest', 'legs', 'boots', 'gloves', 'accessory1', 'accessory2'];
        $filledSlots = [];
        
        foreach ($equippedItems as $equippedItem) {
            $slot = $equippedItem->item->equipment_slot ?? 'unknown';
            $filledSlots[$slot] = $equippedItem->item->rarity ?? 'common';
        }

        $allSlotsFilled = count($filledSlots) >= count($requiredSlots) ? 1 : 0;
        
        // Check rarity requirements
        $rarityHierarchy = ['common' => 1, 'uncommon' => 2, 'rare' => 3, 'epic' => 4, 'legendary' => 5];
        
        $allUncommonPlus = $allSlotsFilled && collect($filledSlots)->every(fn($rarity) => ($rarityHierarchy[$rarity] ?? 1) >= 2) ? 1 : 0;
        $allRarePlus = $allSlotsFilled && collect($filledSlots)->every(fn($rarity) => ($rarityHierarchy[$rarity] ?? 1) >= 3) ? 1 : 0;
        $allEpicPlus = $allSlotsFilled && collect($filledSlots)->every(fn($rarity) => ($rarityHierarchy[$rarity] ?? 1) >= 4) ? 1 : 0;
        $allLegendary = $allSlotsFilled && collect($filledSlots)->every(fn($rarity) => ($rarityHierarchy[$rarity] ?? 1) >= 5) ? 1 : 0;

        return [
            'all_slots_filled' => $allSlotsFilled,
            'all_slots_uncommon_plus' => $allUncommonPlus,
            'all_slots_rare_plus' => $allRarePlus,
            'all_slots_epic_plus' => $allEpicPlus,
            'all_slots_legendary' => $allLegendary,
        ];
    }

    private function getEquipmentOwned(Player $player): int
    {
        // Count all equipment items in inventory
        return \App\Models\PlayerItem::where('player_id', $player->id)
            ->whereHas('item', function($query) {
                $query->whereIn('type', ['weapon', 'armor']);
            })
            ->count();
    }

    private function getItemsSold(Player $player): int
    {
        // In a real implementation, this would query transaction logs
        // For now, estimate based on player level and adventures
        return (int)($player->level * 2 + $player->adventures()->where('status', 'completed')->count() * 1.5);
    }

    private function getRoadsExplored(Player $player): int
    {
        return $player->adventures()
            ->where('status', 'completed')
            ->distinct('road')
            ->count('road');
    }

    private function getWeatherConditionsExperienced(Player $player): int
    {
        // In a real implementation, track unique weather conditions
        // For now, estimate based on adventures completed
        return min(5, $player->adventures()->where('status', 'completed')->count());
    }

    private function getPerfectAdventures(Player $player): int
    {
        // This would require tracking damage taken during adventures
        // For now, return 0 as it requires real-time tracking
        return 0;
    }

    private function checkRequirements(array $requirements, array $playerStats, array $eventData): bool
    {
        foreach ($requirements as $requirement => $threshold) {
            $currentValue = $playerStats[$requirement] ?? $eventData[$requirement] ?? 0;
            if ($currentValue < $threshold) {
                return false;
            }
        }
        return true;
    }

    public function getPlayerAchievements(Player $player, bool $includeHidden = false): array
    {
        $query = Achievement::where('player_id', $player->id)
            ->orderBy('unlocked_at', 'desc');

        $achievements = $query->get()->toArray();

        // Add progress for locked achievements
        $lockedAchievements = $this->getProgressTowardsAchievements($player, $includeHidden);
        
        return [
            'unlocked' => $achievements,
            'progress' => $lockedAchievements,
            'total_points' => collect($achievements)->sum('points'),
            'achievement_count' => count($achievements)
        ];
    }

    private function getProgressTowardsAchievements(Player $player, bool $includeHidden): array
    {
        $playerStats = $this->calculatePlayerStats($player);
        $progress = [];

        foreach ($this->achievementDefinitions as $achievementId => $definition) {
            // Skip hidden achievements unless explicitly requested
            if ($definition['hidden'] && !$includeHidden) {
                continue;
            }

            // Skip if already unlocked
            if ($this->hasAchievement($player, $achievementId)) {
                continue;
            }

            $achievementProgress = [
                'id' => $achievementId,
                'name' => $definition['name'],
                'description' => $definition['description'],
                'category' => $definition['category'],
                'points' => $definition['points'],
                'icon' => $definition['icon'],
                'requirements' => [],
                'completion_percentage' => 0
            ];

            $totalProgress = 0;
            $requirementCount = count($definition['requirements']);

            foreach ($definition['requirements'] as $requirement => $threshold) {
                $currentValue = $playerStats[$requirement] ?? 0;
                $progress_percent = min(100, ($currentValue / $threshold) * 100);
                $totalProgress += $progress_percent;

                $achievementProgress['requirements'][] = [
                    'requirement' => $requirement,
                    'current' => $currentValue,
                    'required' => $threshold,
                    'progress' => $progress_percent
                ];
            }

            $achievementProgress['completion_percentage'] = $totalProgress / $requirementCount;
            $progress[] = $achievementProgress;
        }

        // Sort by completion percentage (closest to completion first)
        usort($progress, fn($a, $b) => $b['completion_percentage'] <=> $a['completion_percentage']);

        return $progress;
    }

    public function getLeaderboard(string $category = 'all', int $limit = 10): array
    {
        $query = Achievement::selectRaw('player_id, SUM(points) as total_points, COUNT(*) as achievement_count')
            ->with('player:id,character_name,level')
            ->groupBy('player_id');

        if ($category !== 'all') {
            $query->where('category', $category);
        }

        $leaderboard = $query->orderBy('total_points', 'desc')
            ->orderBy('achievement_count', 'desc')
            ->limit($limit)
            ->get();

        return $leaderboard->map(function ($entry, $index) {
            return [
                'rank' => $index + 1,
                'player_name' => $entry->player->character_name,
                'player_level' => $entry->player->level,
                'total_points' => $entry->total_points,
                'achievement_count' => $entry->achievement_count
            ];
        })->toArray();
    }

    public function getAchievementCategories(): array
    {
        $categories = [];
        foreach ($this->achievementDefinitions as $definition) {
            $category = $definition['category'];
            if (!isset($categories[$category])) {
                $categories[$category] = [
                    'name' => ucfirst($category),
                    'achievements' => [],
                    'total_points' => 0
                ];
            }
            $categories[$category]['achievements'][] = $definition;
            $categories[$category]['total_points'] += $definition['points'];
        }
        return $categories;
    }

    public function processGameEvent(Player $player, string $eventType, array $eventData = []): array
    {
        // Map game events to achievement checks
        $eventMap = [
            // Combat events
            'combat_victory' => ['combat_victories'],
            'critical_hit' => ['critical_hits'],
            'close_call_victory' => ['close_call_victories'],
            'boss_defeated' => ['bosses_defeated'],
            
            // Adventure & Exploration events
            'adventure_completed' => ['adventures_completed'],
            'node_completed' => ['nodes_completed'],
            'treasure_node_completed' => ['treasure_nodes_completed'],
            'resource_node_completed' => ['resource_nodes_completed'],
            
            // Crafting events
            'recipe_discovered' => ['recipes_discovered'],
            'item_crafted' => ['items_crafted'],
            'weapon_crafted' => ['weapons_crafted'],
            'armor_crafted' => ['armor_crafted'],
            'potion_crafted' => ['potions_crafted'],
            
            // Equipment events
            'item_equipped' => ['items_equipped'],
            'equipment_slot_filled' => ['all_slots_filled'],
            'equipment_upgrade' => ['all_slots_uncommon_plus', 'all_slots_rare_plus', 'all_slots_epic_plus', 'all_slots_legendary'],
            
            // Economy events
            'item_sold' => ['items_sold'],
            'currency_earned' => ['total_currency_earned'],
            
            // Character progression events
            'level_gained' => ['player_level'],
            
            // Village events
            'npc_recruited' => ['npcs_recruited'],
            'specialization_unlocked' => ['specializations_unlocked']
        ];

        if (isset($eventMap[$eventType])) {
            return $this->checkAndUnlockAchievements($player, $eventData);
        }

        return [];
    }
}