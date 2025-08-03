<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class NPC extends Model
{
    protected $table = 'npcs';
    
    protected $fillable = [
        'player_id',
        'name',
        'personality',
        'profession',
        'relationship_score',
        'village_status',
        'arrived_at',
        'conversation_history',
        'available_services'
    ];

    protected $casts = [
        'relationship_score' => 'integer',
        'arrived_at' => 'datetime',
        'conversation_history' => 'array',
        'available_services' => 'array'
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function skills(): HasMany
    {
        return $this->hasMany(NPCSkill::class, 'npc_id');
    }

    public function isSettled(): bool
    {
        return $this->village_status === 'settled';
    }

    public function isMigrating(): bool
    {
        return $this->village_status === 'migrating';
    }

    public function hasDeparted(): bool
    {
        return $this->village_status === 'departed';
    }

    public function getSkillLevel(): int
    {
        return $this->skills->sum('level');
    }

    public function getRelationshipStatus(): string
    {
        if ($this->relationship_score < -50) return 'hostile';
        if ($this->relationship_score < -20) return 'unfriendly';
        if ($this->relationship_score < 20) return 'neutral';
        if ($this->relationship_score < 50) return 'friendly';
        if ($this->relationship_score < 80) return 'close';
        return 'devoted';
    }

    public function getDaysInVillage(): int
    {
        if (!$this->arrived_at || !$this->isSettled()) {
            return 0;
        }
        
        return $this->arrived_at->diffInDays(Carbon::now());
    }

    public function canLearnSkill(string $skillName): bool
    {
        // Check if NPC already has this skill at max level
        $currentSkill = $this->skills()->where('skill_name', $skillName)->first();
        if ($currentSkill && $currentSkill->level >= 5) {
            return false;
        }
        
        return true;
    }

    public function getAvailableServices(): array
    {
        return $this->available_services ?? [];
    }

    public function addConversation(string $topic, array $content): void
    {
        $history = $this->conversation_history ?? [];
        $history[] = [
            'topic' => $topic,
            'content' => $content,
            'timestamp' => now()->toISOString()
        ];
        
        // Keep only last 10 conversations
        if (count($history) > 10) {
            $history = array_slice($history, -10);
        }
        
        $this->update(['conversation_history' => $history]);
    }

    public function getServiceQuality(): float
    {
        $baseQuality = 1.0;
        $relationshipBonus = max(0, $this->relationship_score / 100) * 0.2; // Up to 20% bonus
        $skillBonus = $this->getSkillLevel() * 0.05; // 5% per skill level
        
        return $baseQuality + $relationshipBonus + $skillBonus;
    }

    public function canProvideService(string $service): bool
    {
        return in_array($service, $this->getAvailableServices());
    }
}
