<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerItem extends Model
{
    protected $fillable = [
        'player_id',
        'item_id', 
        'quantity',
        'current_durability',
        'is_equipped',
        'equipment_slot',
        'custom_name',
        'affix_stat_modifiers',
        'prefix_affix_id',
        'suffix_affix_id'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'current_durability' => 'integer',
        'is_equipped' => 'boolean',
        'affix_stat_modifiers' => 'array'
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function prefixAffix(): BelongsTo
    {
        return $this->belongsTo(ItemAffix::class, 'prefix_affix_id');
    }

    public function suffixAffix(): BelongsTo
    {
        return $this->belongsTo(ItemAffix::class, 'suffix_affix_id');
    }

    public function canEquip(): bool
    {
        return $this->item->is_equippable && !$this->is_equipped;
    }

    public function canUnequip(): bool
    {
        return $this->is_equipped;
    }

    public function getEquipmentSlot(): ?string
    {
        if (!$this->item->is_equippable) {
            return null;
        }

        // Handle special cases for ring slots
        if ($this->item->subtype === Item::SUBTYPE_RING) {
            return $this->equipment_slot ?? 'ring_1'; // Default to ring_1
        }

        // Handle one-handed weapons
        if (in_array($this->item->subtype, [Item::SUBTYPE_SWORD, Item::SUBTYPE_AXE, Item::SUBTYPE_MACE, Item::SUBTYPE_DAGGER])) {
            return $this->equipment_slot ?? 'weapon_1'; // Default to weapon_1
        }

        return $this->item->getEquipmentSlot();
    }

    // Methods for compatibility with inventory template

    public function getEffectiveACBonus(): int
    {
        if (!$this->item || !$this->item->ac_bonus) {
            return 0;
        }
        
        // Apply durability penalty
        $durabilityPercentage = $this->getDurabilityPercentage();
        if ($durabilityPercentage < 50) {
            return max(1, intval($this->item->ac_bonus * ($durabilityPercentage / 100)));
        }
        
        return $this->item->ac_bonus;
    }

    public function getEffectiveStatModifier(string $stat): int
    {
        $baseModifier = 0;
        
        // Get base item stat modifier
        if ($this->item && $this->item->stats_modifiers && isset($this->item->stats_modifiers[$stat])) {
            $baseModifier = $this->item->stats_modifiers[$stat];
        }
        
        // Add affix stat modifiers
        if ($this->affix_stat_modifiers && isset($this->affix_stat_modifiers[$stat])) {
            $baseModifier += $this->affix_stat_modifiers[$stat];
        }
        
        // Apply durability penalty to total modifier
        $durabilityPercentage = $this->getDurabilityPercentage();
        if ($durabilityPercentage < 50) {
            return intval($baseModifier * ($durabilityPercentage / 100));
        }
        
        return $baseModifier;
    }

    public function isDamaged(): bool
    {
        return $this->getDurabilityPercentage() < 100;
    }

    public function getDurabilityPercentage(): int
    {
        if (!$this->item->max_durability || $this->item->max_durability <= 0) {
            return 100; // Items without durability are always at 100%
        }
        
        return min(100, max(0, intval(($this->current_durability / $this->item->max_durability) * 100)));
    }

    /**
     * Get the display name (custom name if available, otherwise item name)
     */
    public function getDisplayName(): string
    {
        return $this->custom_name ?: $this->item->name;
    }

    /**
     * Check if this item has any affixes
     */
    public function hasAffixes(): bool
    {
        return $this->prefix_affix_id || $this->suffix_affix_id;
    }
}
