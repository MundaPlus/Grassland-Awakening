<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Equipment extends Model
{
    protected $table = 'equipment';

    protected $fillable = [
        'player_id',
        'item_id',
        'slot',
        'durability',
        'max_durability',
        'enchantments'
    ];

    protected $casts = [
        'durability' => 'integer',
        'max_durability' => 'integer',
        'enchantments' => 'array'
    ];

    // All equipment slots
    const SLOT_HELM = 'helm';
    const SLOT_CHEST = 'chest';
    const SLOT_PANTS = 'pants';
    const SLOT_BOOTS = 'boots';
    const SLOT_GLOVES = 'gloves';
    const SLOT_NECK = 'neck';
    const SLOT_RING_1 = 'ring_1';
    const SLOT_RING_2 = 'ring_2';
    const SLOT_ARTIFACT = 'artifact';
    const SLOT_WEAPON_1 = 'weapon_1';
    const SLOT_WEAPON_2 = 'weapon_2';
    const SLOT_SHIELD = 'shield';
    const SLOT_BOW = 'bow';
    const SLOT_WAND = 'wand';
    const SLOT_STAFF = 'staff';
    const SLOT_TWO_HANDED_WEAPON = 'two_handed_weapon';

    public static function getAllSlots(): array
    {
        return [
            self::SLOT_HELM,
            self::SLOT_CHEST,
            self::SLOT_PANTS,
            self::SLOT_BOOTS,
            self::SLOT_GLOVES,
            self::SLOT_NECK,
            self::SLOT_RING_1,
            self::SLOT_RING_2,
            self::SLOT_ARTIFACT,
            self::SLOT_WEAPON_1,
            self::SLOT_WEAPON_2,
            self::SLOT_SHIELD,
            self::SLOT_BOW,
            self::SLOT_WAND,
            self::SLOT_STAFF,
            self::SLOT_TWO_HANDED_WEAPON
        ];
    }

    public static function getArmorSlots(): array
    {
        return [
            self::SLOT_HELM,
            self::SLOT_CHEST,
            self::SLOT_PANTS,
            self::SLOT_BOOTS,
            self::SLOT_GLOVES
        ];
    }

    public static function getWeaponSlots(): array
    {
        return [
            self::SLOT_WEAPON_1,
            self::SLOT_WEAPON_2,
            self::SLOT_SHIELD,
            self::SLOT_BOW,
            self::SLOT_WAND,
            self::SLOT_STAFF,
            self::SLOT_TWO_HANDED_WEAPON
        ];
    }

    public static function getAccessorySlots(): array
    {
        return [
            self::SLOT_NECK,
            self::SLOT_RING_1,
            self::SLOT_RING_2,
            self::SLOT_ARTIFACT
        ];
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function getDurabilityPercentage(): float
    {
        if ($this->max_durability <= 0) {
            return 100.0;
        }
        
        return ($this->durability / $this->max_durability) * 100;
    }

    public function isDamaged(): bool
    {
        return $this->durability < $this->max_durability;
    }

    public function isBroken(): bool
    {
        return $this->durability <= 0;
    }

    public function repair(int $amount = null): void
    {
        if ($amount === null) {
            $this->durability = $this->max_durability;
        } else {
            $this->durability = min($this->max_durability, $this->durability + $amount);
        }
        $this->save();
    }

    public function damage(int $amount): void
    {
        $this->durability = max(0, $this->durability - $amount);
        $this->save();
    }

    public function getEffectiveStatModifier(string $stat): int
    {
        if ($this->isBroken()) {
            return 0;
        }

        $baseModifier = $this->item->getStatModifier($stat);
        $durabilityMultiplier = $this->getDurabilityPercentage() / 100;
        
        return (int)floor($baseModifier * $durabilityMultiplier);
    }

    public function getEffectiveACBonus(): int
    {
        if ($this->isBroken()) {
            return 0;
        }

        $baseAC = $this->item->ac_bonus ?? 0;
        $durabilityMultiplier = $this->getDurabilityPercentage() / 100;
        
        return (int)floor($baseAC * $durabilityMultiplier);
    }

    public function getSlotDisplayName(): string
    {
        return match($this->slot) {
            self::SLOT_HELM => 'Helmet',
            self::SLOT_CHEST => 'Chest Armor',
            self::SLOT_PANTS => 'Leg Armor',
            self::SLOT_BOOTS => 'Boots',
            self::SLOT_GLOVES => 'Gloves',
            self::SLOT_NECK => 'Necklace',
            self::SLOT_RING_1 => 'Ring (Left)',
            self::SLOT_RING_2 => 'Ring (Right)',
            self::SLOT_ARTIFACT => 'Artifact',
            self::SLOT_WEAPON_1 => 'Main Hand',
            self::SLOT_WEAPON_2 => 'Off Hand',
            self::SLOT_SHIELD => 'Shield',
            self::SLOT_BOW => 'Ranged Weapon',
            self::SLOT_WAND => 'Wand',
            self::SLOT_STAFF => 'Staff',
            self::SLOT_TWO_HANDED_WEAPON => 'Two-Handed Weapon',
            default => ucfirst(str_replace('_', ' ', $this->slot))
        };
    }
}
