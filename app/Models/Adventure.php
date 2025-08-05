<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Adventure extends Model
{
    protected $fillable = [
        'player_id',
        'road',
        'seed',
        'title',
        'description',
        'difficulty',
        'status',
        'current_level',
        'current_node_id',
        'completed_nodes',
        'entered_nodes',
        'collected_loot',
        'currency_earned',
        'generated_map',
        'completed_at'
    ];

    protected $casts = [
        'completed_nodes' => 'array',
        'entered_nodes' => 'array',
        'collected_loot' => 'array',
        'generated_map' => 'array',
        'currency_earned' => 'integer',
        'current_level' => 'integer',
        'completed_at' => 'datetime'
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(AdventureProgress::class);
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(AdventureNode::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function markCompleted(): void
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->save();

        // Trigger achievement events for adventure completion
        $achievementService = app(\App\Services\AchievementService::class);
        $achievementService->processGameEvent($this->player, 'adventure_completed');
    }

    public function abandon(): void
    {
        $this->status = 'abandoned';
        $this->save();
    }

    public function markFailed(): void
    {
        $this->status = 'failed';
        $this->completed_at = now();
        $this->save();
    }

    public function addCompletedNode(string $nodeId): void
    {
        $completedNodes = $this->completed_nodes ?? [];
        if (!in_array($nodeId, $completedNodes)) {
            $completedNodes[] = $nodeId;
            $this->completed_nodes = $completedNodes;
            $this->save();

            // Trigger achievement events for node completion
            $this->triggerNodeCompletionAchievements($nodeId);
        }
    }

    private function triggerNodeCompletionAchievements(string $nodeId): void
    {
        $achievementService = app(\App\Services\AchievementService::class);
        
        // General node completion achievement
        $achievementService->processGameEvent($this->player, 'node_completed');

        // Find node type in generated map and trigger specific achievements
        $mapData = $this->generated_map ?? [];
        if (isset($mapData['map']['nodes'])) {
            foreach ($mapData['map']['nodes'] as $level => $levelNodes) {
                foreach ($levelNodes as $node) {
                    if ($node['id'] === $nodeId) {
                        // Trigger specific node type achievements
                        if ($node['type'] === 'treasure') {
                            $achievementService->processGameEvent($this->player, 'treasure_node_completed');
                        } elseif ($node['type'] === 'resource_gathering') {
                            $achievementService->processGameEvent($this->player, 'resource_node_completed');
                        }
                        break 2; // Break out of both loops
                    }
                }
            }
        }
    }

    public function addEnteredNode(string $nodeId): void
    {
        $enteredNodes = $this->entered_nodes ?? [];
        if (!in_array($nodeId, $enteredNodes)) {
            $enteredNodes[] = $nodeId;
            $this->entered_nodes = $enteredNodes;
            $this->save();
        }
    }

    public function hasEnteredLevel(int $level): bool
    {
        $enteredNodes = $this->entered_nodes ?? [];
        foreach ($enteredNodes as $nodeId) {
            if (str_starts_with($nodeId, "{$level}-")) {
                return true;
            }
        }
        return false;
    }

    public function addCollectedLoot(array $loot): void
    {
        $collectedLoot = $this->collected_loot ?? [];
        $collectedLoot[] = $loot;
        $this->collected_loot = $collectedLoot;
        $this->save();
    }

    public function addCurrencyEarned(int $amount): void
    {
        $this->currency_earned += $amount;
        $this->save();
    }

    public function getCurrentProgress(): float
    {
        $totalLevels = $this->getTotalLevelsCount();
        $completedLevels = $this->getCompletedLevelsCount();
        return min(1.0, $completedLevels / $totalLevels);
    }

    public function getTotalNodesCount(): int
    {
        if (!$this->generated_map || !isset($this->generated_map['map']['nodes'])) {
            return 25; // fallback
        }
        
        $totalNodes = 0;
        foreach ($this->generated_map['map']['nodes'] as $level => $levelNodes) {
            $totalNodes += count($levelNodes);
        }
        
        return $totalNodes;
    }

    public function getCurrentAdventureLevel(): int
    {
        $enteredNodes = $this->entered_nodes ?? [];
        $completedNodes = $this->completed_nodes ?? [];
        
        // Get the highest level from entered or completed nodes
        $highestLevel = 1;
        
        foreach (array_merge($enteredNodes, $completedNodes) as $nodeId) {
            if (strpos($nodeId, '-') !== false) {
                $level = (int) explode('-', $nodeId)[0];
                $highestLevel = max($highestLevel, $level);
            }
        }
        
        return $highestLevel;
    }

    public function getTotalLevelsCount(): int
    {
        if (!$this->generated_map || !isset($this->generated_map['map']['nodes'])) {
            return 15; // fallback
        }
        
        return count($this->generated_map['map']['nodes']);
    }

    public function getCompletedLevelsCount(): int
    {
        $completedNodes = $this->completed_nodes ?? [];
        $completedLevels = [];
        
        foreach ($completedNodes as $nodeId) {
            if (strpos($nodeId, '-') !== false) {
                $level = (int) explode('-', $nodeId)[0];
                $completedLevels[$level] = true;
            }
        }
        
        return count($completedLevels);
    }

    public function updatePosition(int $level, string $nodeId): void
    {
        $this->current_level = $level;
        $this->current_node_id = $nodeId;
        $this->save();
    }
}
