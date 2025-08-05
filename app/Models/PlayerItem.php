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
        'equipment_slot'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'current_durability' => 'integer',
        'is_equipped' => 'boolean'
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
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
}
