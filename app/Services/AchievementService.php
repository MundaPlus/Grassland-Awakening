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
        'veteran_warrior' => [
            'name' => 'Veteran Warrior',
            'description' => 'Win 50 combat encounters',
            'category' => 'combat',
            'points' => 100,
            'icon' => '🛡️',
            'requirements' => ['combat_victories' => 50],
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
        'survivor' => [
            'name' => 'Survivor',
            'description' => 'Win a combat with only 1 HP remaining',
            'category' => 'combat',
            'points' => 50,
            'icon' => '💀',
            'requirements' => ['close_call_victories' => 1],
            'hidden' => false
        ],

        // Exploration Achievements
        'first_steps' => [
            'name' => 'First Steps',
            'description' => 'Complete your first adventure',
            'category' => 'exploration',
            'points' => 15,
            'icon' => '👣',
            'requirements' => ['adventures_completed' => 1],
            'hidden' => false
        ],
        'road_master' => [
            'name' => 'Master of Roads',
            'description' => 'Complete adventures on all 4 roads',
            'category' => 'exploration',
            'points' => 150,
            'icon' => '🗺️',
            'requirements' => ['roads_explored' => 4],
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

        // Character Development Achievements
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
        'wealthy' => [
            'name' => 'Wealthy',
            'description' => 'Accumulate 10,000 gold',
            'category' => 'character',
            'points' => 100,
            'icon' => '💰',
            'requirements' => ['total_currency_earned' => 10000],
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

        Log::info("Achievement unlocked", [
            'player_id' => $player->id,
            'achievement' => $achievementId,
            'points' => $definition['points'],
            'currency_bonus' => $currencyBonus
        ]);

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
        
        return [
            'player_level' => $player->level,
            'total_currency_earned' => $player->persistent_currency,
            'npcs_recruited' => $settledNPCs->count(),
            'specializations_unlocked' => $player->villageSpecializations->count(),
            'friendly_npcs' => $settledNPCs->where('relationship_score', '>=', 15)->count(),
            'devoted_npcs' => $settledNPCs->where('relationship_score', '>=', 25)->count(),
            'adventures_completed' => $player->adventures()->where('status', 'completed')->count(),
            'combat_victories' => $this->getCombatStats($player, 'victories'),
            'critical_hits' => $this->getCombatStats($player, 'critical_hits'),
            'close_call_victories' => $this->getCombatStats($player, 'close_calls'),
            'roads_explored' => $this->getRoadsExplored($player),
            'weather_conditions_experienced' => $this->getWeatherConditionsExperienced($player),
            'perfect_adventures' => $this->getPerfectAdventures($player),
            'critical_misses_single_combat' => 0 // This would be tracked in real-time during combat
        ];
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
            default => 0
        };
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
            'combat_victory' => ['combat_victories'],
            'critical_hit' => ['critical_hits'],
            'adventure_completed' => ['adventures_completed'],
            'npc_recruited' => ['npcs_recruited'],
            'specialization_unlocked' => ['specializations_unlocked'],
            'level_gained' => ['player_level'],
            'close_call_victory' => ['close_call_victories']
        ];

        if (isset($eventMap[$eventType])) {
            return $this->checkAndUnlockAchievements($player, $eventData);
        }

        return [];
    }
}