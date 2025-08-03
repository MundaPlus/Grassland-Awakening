<?php

namespace App\Services;

use App\Models\Player;
use App\Models\FactionReputation;
use Illuminate\Support\Facades\Log;

class ReputationService
{
    private array $factionDefinitions = [
        'village_council' => [
            'name' => 'Village Council',
            'description' => 'The governing body of your village',
            'icon' => '🏛️',
            'base_reputation' => 0,
            'reputation_levels' => [
                -100 => ['name' => 'Exiled', 'color' => '#8B0000', 'benefits' => [], 'penalties' => ['Village services unavailable', 'NPCs refuse to settle']],
                -50 => ['name' => 'Despised', 'color' => '#CD5C5C', 'benefits' => [], 'penalties' => ['50% higher NPC training costs', 'Reduced village efficiency']],
                -25 => ['name' => 'Disliked', 'color' => '#F08080', 'benefits' => [], 'penalties' => ['25% higher NPC training costs']],
                0 => ['name' => 'Neutral', 'color' => '#808080', 'benefits' => [], 'penalties' => []],
                25 => ['name' => 'Accepted', 'color' => '#90EE90', 'benefits' => ['5% discount on village services'], 'penalties' => []],
                50 => ['name' => 'Respected', 'color' => '#32CD32', 'benefits' => ['10% discount on village services', 'Faster NPC skill training'], 'penalties' => []],
                75 => ['name' => 'Honored', 'color' => '#228B22', 'benefits' => ['15% discount on village services', 'Faster NPC skill training', 'Access to elite NPCs'], 'penalties' => []],
                100 => ['name' => 'Revered', 'color' => '#006400', 'benefits' => ['20% discount on village services', 'Fastest NPC skill training', 'Access to elite NPCs', 'Special village projects'], 'penalties' => []]
            ]
        ],
        'merchants_guild' => [
            'name' => 'Merchants Guild',
            'description' => 'Trade organization controlling commerce',
            'icon' => '💰',
            'base_reputation' => 0,
            'reputation_levels' => [
                -100 => ['name' => 'Blacklisted', 'color' => '#8B0000', 'benefits' => [], 'penalties' => ['Cannot trade with merchants', '200% shop prices']],
                -50 => ['name' => 'Unwelcome', 'color' => '#CD5C5C', 'benefits' => [], 'penalties' => ['150% shop prices', 'Limited item selection']],
                -25 => ['name' => 'Suspicious', 'color' => '#F08080', 'benefits' => [], 'penalties' => ['125% shop prices']],
                0 => ['name' => 'Unknown', 'color' => '#808080', 'benefits' => [], 'penalties' => []],
                25 => ['name' => 'Customer', 'color' => '#90EE90', 'benefits' => ['5% shop discounts'], 'penalties' => []],
                50 => ['name' => 'Valued', 'color' => '#32CD32', 'benefits' => ['10% shop discounts', 'Access to rare items'], 'penalties' => []],
                75 => ['name' => 'Partner', 'color' => '#228B22', 'benefits' => ['15% shop discounts', 'Access to rare items', 'Bulk purchase options'], 'penalties' => []],
                100 => ['name' => 'Guild Member', 'color' => '#006400', 'benefits' => ['20% shop discounts', 'Access to exclusive items', 'Bulk purchase options', 'Trading contracts'], 'penalties' => []]
            ]
        ],
        'explorers_society' => [
            'name' => 'Explorers Society',
            'description' => 'Organization of adventurers and pathfinders',
            'icon' => '🗺️',
            'base_reputation' => 0,
            'reputation_levels' => [
                -100 => ['name' => 'Banished', 'color' => '#8B0000', 'benefits' => [], 'penalties' => ['No adventure support', 'Blocked from dangerous roads']],
                -50 => ['name' => 'Outcast', 'color' => '#CD5C5C', 'benefits' => [], 'penalties' => ['Reduced adventure rewards', 'Limited road access']],
                -25 => ['name' => 'Novice', 'color' => '#F08080', 'benefits' => [], 'penalties' => ['Reduced adventure rewards']],
                0 => ['name' => 'Unproven', 'color' => '#808080', 'benefits' => [], 'penalties' => []],
                25 => ['name' => 'Explorer', 'color' => '#90EE90', 'benefits' => ['Better adventure maps'], 'penalties' => []],
                50 => ['name' => 'Pathfinder', 'color' => '#32CD32', 'benefits' => ['Better adventure maps', '10% bonus adventure rewards'], 'penalties' => []],
                75 => ['name' => 'Veteran', 'color' => '#228B22', 'benefits' => ['Excellent adventure maps', '15% bonus adventure rewards', 'Access to legendary roads'], 'penalties' => []],
                100 => ['name' => 'Legend', 'color' => '#006400', 'benefits' => ['Perfect adventure maps', '25% bonus adventure rewards', 'Access to all roads', 'Explorer contracts'], 'penalties' => []]
            ]
        ],
        'nature_spirits' => [
            'name' => 'Nature Spirits',
            'description' => 'Mystical entities governing the natural world',
            'icon' => '🌿',
            'base_reputation' => 0,
            'reputation_levels' => [
                -100 => ['name' => 'Cursed', 'color' => '#8B0000', 'benefits' => [], 'penalties' => ['Constant bad weather', 'Animals are hostile', 'Plants wither']],
                -50 => ['name' => 'Shunned', 'color' => '#CD5C5C', 'benefits' => [], 'penalties' => ['Frequent bad weather', 'Reduced crop yields']],
                -25 => ['name' => 'Ignored', 'color' => '#F08080', 'benefits' => [], 'penalties' => ['Occasional bad weather']],
                0 => ['name' => 'Tolerated', 'color' => '#808080', 'benefits' => [], 'penalties' => []],
                25 => ['name' => 'Noticed', 'color' => '#90EE90', 'benefits' => ['Occasional good weather'], 'penalties' => []],
                50 => ['name' => 'Favored', 'color' => '#32CD32', 'benefits' => ['Better weather patterns', 'Enhanced crop growth'], 'penalties' => []],
                75 => ['name' => 'Blessed', 'color' => '#228B22', 'benefits' => ['Excellent weather', 'Abundant harvests', 'Animal allies'], 'penalties' => []],
                100 => ['name' => 'Champion', 'color' => '#006400', 'benefits' => ['Perfect weather control', 'Magical crop enhancement', 'Nature magic access'], 'penalties' => []]
            ]
        ]
    ];

    public function getPlayerReputation(Player $player, string $factionId): FactionReputation
    {
        return FactionReputation::firstOrCreate([
            'player_id' => $player->id,
            'faction_id' => $factionId
        ], [
            'reputation_score' => $this->factionDefinitions[$factionId]['base_reputation'] ?? 0,
            'faction_name' => $this->factionDefinitions[$factionId]['name'] ?? $factionId
        ]);
    }

    public function getAllPlayerReputations(Player $player): array
    {
        $reputations = [];
        
        foreach ($this->factionDefinitions as $factionId => $definition) {
            $reputation = $this->getPlayerReputation($player, $factionId);
            $level = $this->getReputationLevel($factionId, $reputation->reputation_score);
            
            $reputations[] = [
                'faction_id' => $factionId,
                'faction_name' => $definition['name'],
                'faction_description' => $definition['description'],
                'faction_icon' => $definition['icon'],
                'current_score' => $reputation->reputation_score,
                'level' => $level,
                'benefits' => $level['benefits'],
                'penalties' => $level['penalties'],
                'progress_to_next' => $this->getProgressToNextLevel($factionId, $reputation->reputation_score)
            ];
        }
        
        return $reputations;
    }

    public function modifyReputation(Player $player, string $factionId, int $change, string $reason = ''): array
    {
        $reputation = $this->getPlayerReputation($player, $factionId);
        $oldScore = $reputation->reputation_score;
        $oldLevel = $this->getReputationLevel($factionId, $oldScore);
        
        $reputation->reputation_score = max(-100, min(100, $oldScore + $change));
        $reputation->save();
        
        $newLevel = $this->getReputationLevel($factionId, $reputation->reputation_score);
        
        $result = [
            'faction_id' => $factionId,
            'faction_name' => $this->factionDefinitions[$factionId]['name'],
            'old_score' => $oldScore,
            'new_score' => $reputation->reputation_score,
            'change' => $change,
            'reason' => $reason,
            'old_level' => $oldLevel,
            'new_level' => $newLevel,
            'level_changed' => $oldLevel['name'] !== $newLevel['name']
        ];

        Log::info('Reputation changed', [
            'player_id' => $player->id,
            'faction' => $factionId,
            'change' => $change,
            'reason' => $reason,
            'new_score' => $reputation->reputation_score
        ]);

        return $result;
    }

    public function getReputationLevel(string $factionId, int $score): array
    {
        $definition = $this->factionDefinitions[$factionId] ?? null;
        if (!$definition) {
            return ['name' => 'Unknown', 'color' => '#808080', 'benefits' => [], 'penalties' => []];
        }

        $levels = $definition['reputation_levels'];
        $currentLevel = ['name' => 'Unknown', 'color' => '#808080', 'benefits' => [], 'penalties' => []];

        // Find the highest threshold that the score meets
        foreach ($levels as $threshold => $level) {
            if ($score >= $threshold) {
                $currentLevel = $level;
            }
        }

        return $currentLevel;
    }

    private function getProgressToNextLevel(string $factionId, int $currentScore): ?array
    {
        $definition = $this->factionDefinitions[$factionId] ?? null;
        if (!$definition) {
            return null;
        }

        $levels = $definition['reputation_levels'];
        $nextThreshold = null;

        // Find the next threshold above current score
        foreach ($levels as $threshold => $level) {
            if ($threshold > $currentScore) {
                $nextThreshold = $threshold;
                break;
            }
        }

        if ($nextThreshold === null) {
            return null; // Already at max level
        }

        // Find the current threshold
        $currentThreshold = -100; // Minimum possible
        foreach ($levels as $threshold => $level) {
            if ($threshold <= $currentScore) {
                $currentThreshold = $threshold;
            }
        }

        $range = $nextThreshold - $currentThreshold;
        $progress = $currentScore - $currentThreshold;
        $percentage = $range > 0 ? ($progress / $range) * 100 : 0;

        return [
            'current_threshold' => $currentThreshold,
            'next_threshold' => $nextThreshold,
            'progress' => $progress,
            'needed' => $nextThreshold - $currentScore,
            'percentage' => $percentage,
            'next_level_name' => $levels[$nextThreshold]['name']
        ];
    }

    public function processGameEvent(Player $player, string $eventType, array $eventData = []): array
    {
        $reputationChanges = [];

        switch ($eventType) {
            case 'npc_recruited':
                $reputationChanges[] = $this->modifyReputation($player, 'village_council', 5, 'Recruited new villager');
                break;

            case 'npc_trained':
                $reputationChanges[] = $this->modifyReputation($player, 'village_council', 2, 'Improved villager skills');
                break;

            case 'specialization_unlocked':
                $reputationChanges[] = $this->modifyReputation($player, 'village_council', 15, 'Unlocked village specialization');
                break;

            case 'adventure_completed':
                $road = $eventData['road'] ?? 'unknown';
                $reputationChanges[] = $this->modifyReputation($player, 'explorers_society', 3, "Completed adventure on {$road} road");
                
                // Nature spirits care about environmental stewardship
                if ($road === 'east') { // Forest/nature road
                    $reputationChanges[] = $this->modifyReputation($player, 'nature_spirits', 2, 'Explored natural areas respectfully');
                }
                break;

            case 'trade_completed':
                $value = $eventData['value'] ?? 0;
                $change = min(5, max(1, intval($value / 100))); // 1-5 points based on trade value
                $reputationChanges[] = $this->modifyReputation($player, 'merchants_guild', $change, 'Completed trade transaction');
                break;

            case 'combat_victory':
                $enemy = $eventData['enemy'] ?? 'unknown';
                if (str_contains(strtolower($enemy), 'bandit') || str_contains(strtolower($enemy), 'criminal')) {
                    $reputationChanges[] = $this->modifyReputation($player, 'village_council', 3, 'Defeated criminals');
                    $reputationChanges[] = $this->modifyReputation($player, 'merchants_guild', 2, 'Made roads safer for trade');
                }
                break;

            case 'environmental_damage':
                $damage = $eventData['severity'] ?? 'minor';
                $penalty = match($damage) {
                    'minor' => -2,
                    'moderate' => -5,
                    'severe' => -10,
                    default => -1
                };
                $reputationChanges[] = $this->modifyReputation($player, 'nature_spirits', $penalty, 'Caused environmental damage');
                break;

            case 'good_weather_used':
                $reputationChanges[] = $this->modifyReputation($player, 'nature_spirits', 1, 'Appreciated natural weather conditions');
                break;

            case 'village_milestone':
                $milestone = $eventData['milestone'] ?? 'growth';
                $change = match($milestone) {
                    'first_npc' => 10,
                    'small_village' => 15,
                    'town' => 25,
                    'city' => 40,
                    default => 5
                };
                $reputationChanges[] = $this->modifyReputation($player, 'village_council', $change, "Village reached: {$milestone}");
                break;

            case 'currency_milestone':
                $amount = $eventData['amount'] ?? 0;
                if ($amount >= 1000) {
                    $change = min(10, intval($amount / 1000));
                    $reputationChanges[] = $this->modifyReputation($player, 'merchants_guild', $change, 'Demonstrated economic success');
                }
                break;
        }

        return $reputationChanges;
    }

    public function getReputationBonuses(Player $player): array
    {
        $bonuses = [
            'shop_discount' => 0,
            'adventure_reward_bonus' => 0,
            'npc_training_speed' => 1.0,
            'weather_improvement' => 0,
            'village_efficiency' => 1.0,
            'special_services' => []
        ];

        foreach ($this->factionDefinitions as $factionId => $definition) {
            $reputation = $this->getPlayerReputation($player, $factionId);
            $level = $this->getReputationLevel($factionId, $reputation->reputation_score);

            switch ($factionId) {
                case 'merchants_guild':
                    if ($reputation->reputation_score >= 25) $bonuses['shop_discount'] += 5;
                    if ($reputation->reputation_score >= 50) $bonuses['shop_discount'] += 5;
                    if ($reputation->reputation_score >= 75) $bonuses['shop_discount'] += 5;
                    if ($reputation->reputation_score >= 100) $bonuses['shop_discount'] += 5;
                    break;

                case 'explorers_society':
                    if ($reputation->reputation_score >= 50) $bonuses['adventure_reward_bonus'] += 10;
                    if ($reputation->reputation_score >= 75) $bonuses['adventure_reward_bonus'] += 5;
                    if ($reputation->reputation_score >= 100) $bonuses['adventure_reward_bonus'] += 10;
                    break;

                case 'village_council':
                    if ($reputation->reputation_score >= 50) $bonuses['npc_training_speed'] *= 1.2;
                    if ($reputation->reputation_score >= 75) $bonuses['npc_training_speed'] *= 1.1;
                    if ($reputation->reputation_score >= 100) $bonuses['special_services'][] = 'village_projects';
                    break;

                case 'nature_spirits':
                    if ($reputation->reputation_score >= 25) $bonuses['weather_improvement'] += 1;
                    if ($reputation->reputation_score >= 50) $bonuses['weather_improvement'] += 1;
                    if ($reputation->reputation_score >= 75) $bonuses['weather_improvement'] += 1;
                    if ($reputation->reputation_score >= 100) $bonuses['special_services'][] = 'weather_control';
                    break;
            }
        }

        return $bonuses;
    }

    public function getFactionDefinitions(): array
    {
        return $this->factionDefinitions;
    }

    public function getReputationSummary(Player $player): array
    {
        $reputations = $this->getAllPlayerReputations($player);
        $bonuses = $this->getReputationBonuses($player);
        
        $summary = [
            'total_factions' => count($reputations),
            'average_reputation' => 0,
            'highest_reputation' => null,
            'lowest_reputation' => null,
            'active_bonuses' => $bonuses,
            'reputation_breakdown' => []
        ];

        if (!empty($reputations)) {
            $totalScore = array_sum(array_column($reputations, 'current_score'));
            $summary['average_reputation'] = $totalScore / count($reputations);
            
            usort($reputations, fn($a, $b) => $b['current_score'] <=> $a['current_score']);
            $summary['highest_reputation'] = $reputations[0];
            $summary['lowest_reputation'] = end($reputations);
            
            foreach ($reputations as $rep) {
                $summary['reputation_breakdown'][$rep['faction_id']] = [
                    'name' => $rep['faction_name'],
                    'score' => $rep['current_score'],
                    'level' => $rep['level']['name'],
                    'color' => $rep['level']['color']
                ];
            }
        }

        return $summary;
    }
}