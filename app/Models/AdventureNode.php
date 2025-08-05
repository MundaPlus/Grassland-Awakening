<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdventureNode extends Model
{
    protected $fillable = [
        'adventure_id',
        'node_id',
        'type',
        'level',
        'node_data',
        'connections',
        'completed',
        'accessible',
        'position_x',
        'position_y',
        'completion_data',
        'completed_at'
    ];

    protected $casts = [
        'node_data' => 'array',
        'connections' => 'array',
        'completion_data' => 'array',
        'completed' => 'boolean',
        'accessible' => 'boolean',
        'level' => 'integer',
        'position_x' => 'integer',
        'position_y' => 'integer',
        'completed_at' => 'datetime'
    ];

    // Node types
    const TYPE_START = 'start';
    const TYPE_COMBAT = 'combat';
    const TYPE_TREASURE = 'treasure';
    const TYPE_EVENT = 'event';
    const TYPE_REST = 'rest';
    const TYPE_BOSS = 'boss';

    public function adventure(): BelongsTo
    {
        return $this->belongsTo(Adventure::class);
    }

    public function isAccessible(): bool
    {
        return $this->accessible;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function canAccess(Adventure $adventure): bool
    {
        // Start node is always accessible
        if ($this->type === self::TYPE_START) {
            return true;
        }

        // Check if all prerequisite nodes are completed
        $prerequisiteNodes = $this->getPrerequisiteNodes($adventure);
        
        foreach ($prerequisiteNodes as $prereqNode) {
            if (!$prereqNode->completed) {
                return false;
            }
        }

        return true;
    }

    public function getPrerequisiteNodes(Adventure $adventure)
    {
        // Find nodes that connect to this node
        return $adventure->nodes()
            ->where('level', '<', $this->level)
            ->whereJsonContains('connections', $this->node_id)
            ->get();
    }

    public function markCompleted(array $completionData = []): void
    {
        $this->completed = true;
        $this->completion_data = $completionData;
        $this->completed_at = now();
        $this->save();

        // Update accessibility of connected nodes
        $this->updateConnectedNodesAccessibility();
    }

    public function updateConnectedNodesAccessibility(): void
    {
        if (!$this->connections) {
            return;
        }

        foreach ($this->connections as $connectedNodeId) {
            $connectedNode = $this->adventure->nodes()
                ->where('node_id', $connectedNodeId)
                ->first();

            if ($connectedNode && $connectedNode->canAccess($this->adventure)) {
                $connectedNode->accessible = true;
                $connectedNode->save();
            }
        }
    }

    public function getTypeIcon(): string
    {
        return match($this->type) {
            self::TYPE_START => '🚪',
            self::TYPE_COMBAT => '⚔️',
            self::TYPE_TREASURE => '💰',
            self::TYPE_EVENT => '📜',
            self::TYPE_REST => '🏕️',
            self::TYPE_BOSS => '👹',
            default => '❓'
        };
    }

    public function getTypeColor(): string
    {
        return match($this->type) {
            self::TYPE_START => 'success',
            self::TYPE_COMBAT => 'danger',
            self::TYPE_TREASURE => 'warning',
            self::TYPE_EVENT => 'info',
            self::TYPE_REST => 'primary',
            self::TYPE_BOSS => 'dark',
            default => 'secondary'
        };
    }

    public function getDescription(): string
    {
        return match($this->type) {
            self::TYPE_START => $this->node_data['description'] ?? 'The beginning of your adventure',
            self::TYPE_COMBAT => $this->getCombatDescription(),
            self::TYPE_TREASURE => $this->getTreasureDescription(),
            self::TYPE_EVENT => $this->getEventDescription(),
            self::TYPE_REST => $this->getRestDescription(),
            self::TYPE_BOSS => $this->getBossDescription(),
            default => 'A mysterious location'
        };
    }

    private function getCombatDescription(): string
    {
        $enemyType = $this->node_data['enemy_type'] ?? 'Unknown Enemy';
        $enemyCount = $this->node_data['enemy_count'] ?? 1;
        
        return $enemyCount > 1 
            ? "Face {$enemyCount} {$enemyType}s in battle"
            : "Battle against a {$enemyType}";
    }

    private function getTreasureDescription(): string
    {
        $treasureType = $this->node_data['treasure_type'] ?? 'treasure';
        return "Discover a hidden {$treasureType}";
    }

    private function getEventDescription(): string
    {
        $eventType = $this->node_data['event_type'] ?? 'mysterious event';
        return "Encounter a {$eventType}";
    }

    private function getRestDescription(): string
    {
        return "A safe place to rest and recover";
    }

    private function getBossDescription(): string
    {
        $bossType = $this->node_data['boss_type'] ?? 'Powerful Boss';
        return "Face the mighty {$bossType}";
    }
}
