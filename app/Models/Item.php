<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'subtype',
        'armor_type',
        'rarity',
        'level_requirement',
        'stats_modifiers',
        'damage_dice',
        'damage_bonus',
        'ac_bonus',
        'is_equippable',
        'is_consumable',
        'is_stackable',
        'max_stack_size',
        'base_value',
        'max_durability',
        'icon'
    ];

    protected $casts = [
        'stats_modifiers' => 'array',
        'level_requirement' => 'integer',
        'damage_bonus' => 'integer',
        'ac_bonus' => 'integer',
        'is_equippable' => 'boolean',
        'is_consumable' => 'boolean',
        'is_stackable' => 'boolean',
        'max_stack_size' => 'integer',
        'base_value' => 'integer',
        'max_durability' => 'integer'
    ];

    // Item types
    const TYPE_WEAPON = 'weapon';
    const TYPE_ARMOR = 'armor';
    const TYPE_ACCESSORY = 'accessory';
    const TYPE_CONSUMABLE = 'consumable';
    const TYPE_MATERIAL = 'material';
    const TYPE_QUEST = 'quest';

    // Weapon subtypes
    const SUBTYPE_SWORD = 'sword';
    const SUBTYPE_AXE = 'axe';
    const SUBTYPE_MACE = 'mace';
    const SUBTYPE_DAGGER = 'dagger';
    const SUBTYPE_BOW = 'bow';
    const SUBTYPE_CROSSBOW = 'crossbow';
    const SUBTYPE_WAND = 'wand';
    const SUBTYPE_STAFF = 'staff';
    const SUBTYPE_SHIELD = 'shield';
    const SUBTYPE_TWO_HANDED = 'two_handed';

    // Armor subtypes
    const SUBTYPE_HELMET = 'helmet';
    const SUBTYPE_CHEST = 'chest';
    const SUBTYPE_PANTS = 'pants';
    const SUBTYPE_BOOTS = 'boots';
    const SUBTYPE_GLOVES = 'gloves';

    // Accessory subtypes
    const SUBTYPE_RING = 'ring';
    const SUBTYPE_NECKLACE = 'necklace';
    const SUBTYPE_ARTIFACT = 'artifact';

    // Armor types (D&D 2024)
    const ARMOR_TYPE_LIGHT = 'light';
    const ARMOR_TYPE_MEDIUM = 'medium';
    const ARMOR_TYPE_HEAVY = 'heavy';

    // Rarity levels
    const RARITY_COMMON = 'common';
    const RARITY_UNCOMMON = 'uncommon';
    const RARITY_RARE = 'rare';
    const RARITY_EPIC = 'epic';
    const RARITY_LEGENDARY = 'legendary';

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    public function getEquipmentSlot(): ?string
    {
        return match($this->subtype) {
            self::SUBTYPE_HELMET => 'helm',
            self::SUBTYPE_CHEST => 'chest',
            self::SUBTYPE_PANTS => 'pants',
            self::SUBTYPE_BOOTS => 'boots',
            self::SUBTYPE_GLOVES => 'gloves',
            self::SUBTYPE_NECKLACE => 'neck',
            self::SUBTYPE_RING => null, // Special handling needed
            self::SUBTYPE_ARTIFACT => 'artifact',
            self::SUBTYPE_SHIELD => 'shield',
            self::SUBTYPE_BOW => 'bow',
            self::SUBTYPE_WAND => 'wand',
            self::SUBTYPE_STAFF => 'staff',
            self::SUBTYPE_TWO_HANDED => 'two_handed_weapon',
            self::SUBTYPE_SWORD, self::SUBTYPE_AXE, self::SUBTYPE_MACE, self::SUBTYPE_DAGGER => null, // weapon_1 or weapon_2
            default => null
        };
    }

    public function canEquip(Player $player): bool
    {
        if (!$this->is_equippable) {
            return false;
        }

        return $player->level >= $this->level_requirement;
    }

    public function getRarityColor(): string
    {
        return match($this->rarity) {
            self::RARITY_COMMON => 'text-secondary',
            self::RARITY_UNCOMMON => 'text-success',
            self::RARITY_RARE => 'text-primary',
            self::RARITY_EPIC => 'text-warning',
            self::RARITY_LEGENDARY => 'text-danger',
            default => 'text-secondary'
        };
    }

    public function getStatModifier(string $stat): int
    {
        return $this->stats_modifiers[$stat] ?? 0;
    }

    /**
     * Get the armor type for D&D 2024 AC calculation
     */
    public function getArmorType(): ?string
    {
        if ($this->type !== self::TYPE_ARMOR) {
            return null;
        }

        // Use explicit armor_type field if set
        if (!empty($this->armor_type)) {
            return $this->armor_type;
        }

        // Fallback to name-based detection for existing items
        $armorName = strtolower($this->name ?? '');
        
        // Heavy armor keywords
        if (str_contains($armorName, 'plate') || 
            str_contains($armorName, 'chain mail') || 
            str_contains($armorName, 'splint') ||
            str_contains($armorName, 'ring mail')) {
            return self::ARMOR_TYPE_HEAVY;
        }
        
        // Medium armor keywords
        if (str_contains($armorName, 'hide') || 
            str_contains($armorName, 'scale') || 
            str_contains($armorName, 'breastplate') ||
            str_contains($armorName, 'half plate')) {
            return self::ARMOR_TYPE_MEDIUM;
        }
        
        // Default to light armor
        return self::ARMOR_TYPE_LIGHT;
    }

    // Accessor for value (maps to base_value)
    public function getValueAttribute()
    {
        return $this->base_value;
    }

    // Mutator for value (maps to base_value) 
    public function setValueAttribute($value)
    {
        $this->attributes['base_value'] = $value;
    }
}
