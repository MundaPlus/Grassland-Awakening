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
        'difficulty',
        'status',
        'current_level',
        'current_node_id',
        'completed_nodes',
        'collected_loot',
        'currency_earned',
        'completed_at'
    ];

    protected $casts = [
        'completed_nodes' => 'array',
        'collected_loot' => 'array',
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

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function markCompleted(): void
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->save();
    }

    public function abandon(): void
    {
        $this->status = 'abandoned';
        $this->save();
    }

    public function addCompletedNode(string $nodeId): void
    {
        $completedNodes = $this->completed_nodes ?? [];
        if (!in_array($nodeId, $completedNodes)) {
            $completedNodes[] = $nodeId;
            $this->completed_nodes = $completedNodes;
            $this->save();
        }
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
        $totalNodes = 25;
        $completedCount = count($this->completed_nodes ?? []);
        return min(1.0, $completedCount / $totalNodes);
    }

    public function updatePosition(int $level, string $nodeId): void
    {
        $this->current_level = $level;
        $this->current_node_id = $nodeId;
        $this->save();
    }
}
