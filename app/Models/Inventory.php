<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    protected $fillable = [
        'player_id',
        'item_id',
        'quantity',
        'max_durability',
        'current_durability',
        'item_metadata'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'max_durability' => 'integer',
        'current_durability' => 'integer',
        'item_metadata' => 'array'
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function getDurabilityPercentage(): int
    {
        if ($this->max_durability <= 0) {
            return 100;
        }
        return (int) round(($this->current_durability / $this->max_durability) * 100);
    }

    public function isDamaged(): bool
    {
        return $this->current_durability < $this->max_durability;
    }

    public function isBroken(): bool
    {
        return $this->current_durability <= 0;
    }

    public function canStack(): bool
    {
        return $this->item->type === 'consumable' || $this->item->type === 'crafting_material';
    }

    public function getEffectiveStatModifier(string $stat): int
    {
        if ($this->isBroken()) {
            return 0;
        }

        $baseModifier = $this->item->getStatModifier($stat);
        $effectiveness = $this->getDurabilityPercentage() / 100;
        
        return (int) floor($baseModifier * $effectiveness);
    }

    public function getEffectiveACBonus(): int
    {
        if ($this->isBroken()) {
            return 0;
        }

        $baseAC = $this->item->ac_bonus ?? 0;
        $effectiveness = $this->getDurabilityPercentage() / 100;
        
        return (int) floor($baseAC * $effectiveness);
    }

    public function reduceDurability(int $amount = 1): void
    {
        $this->current_durability = max(0, $this->current_durability - $amount);
    }

    public function repair(int $amount = null): void
    {
        if ($amount === null) {
            $this->current_durability = $this->max_durability;
        } else {
            $this->current_durability = min($this->max_durability, $this->current_durability + $amount);
        }
    }
}
