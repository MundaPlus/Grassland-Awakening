<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Player extends Model
{
    protected $fillable = [
        'user_id',
        'character_name',
        'gender',
        'level',
        'experience',
        'persistent_currency',
        'hp',
        'max_hp',
        'ac',
        'str',
        'dex',
        'con',
        'int',
        'wis',
        'cha',
        'unallocated_stat_points',
        'current_road',
        'current_level',
        'current_node_id'
    ];

    protected $casts = [
        'level' => 'integer',
        'experience' => 'integer',
        'persistent_currency' => 'integer',
        'hp' => 'integer',
        'max_hp' => 'integer',
        'ac' => 'integer',
        'str' => 'integer',
        'dex' => 'integer',
        'con' => 'integer',
        'int' => 'integer',
        'wis' => 'integer',
        'cha' => 'integer',
        'unallocated_stat_points' => 'integer',
        'current_level' => 'integer'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function adventures(): HasMany
    {
        return $this->hasMany(Adventure::class);
    }

    public function activeAdventure(): ?Adventure
    {
        return $this->adventures()->where('status', 'active')->first();
    }

    public function getStatModifier(string $stat): int
    {
        $statValue = $this->getAttribute($stat);
        return floor(($statValue - 10) / 2);
    }

    public function calculateExperienceToNextLevel(): int
    {
        return $this->level * 100;
    }

    public function canLevelUp(): bool
    {
        return $this->experience >= $this->calculateExperienceToNextLevel();
    }

    public function levelUp(): void
    {
        if ($this->canLevelUp()) {
            $oldLevel = $this->level;
            $experienceNeeded = $this->calculateExperienceToNextLevel();
            
            $this->level++;
            $this->experience -= $experienceNeeded;
            $this->unallocated_stat_points += 2;
            $this->max_hp += 5 + $this->getStatModifier('con');
            $this->hp = $this->max_hp;
            $this->save();
            
            // Log level up for potential notifications
            session(['recent_level_up' => [
                'old_level' => $oldLevel,
                'new_level' => $this->level,
                'stat_points_gained' => 2,
                'hp_gained' => 5 + $this->getStatModifier('con')
            ]]);
        }
    }

    public function addExperience(int $amount): array
    {
        $this->experience += $amount;
        $levelsGained = [];
        
        // Check for multiple level ups
        while ($this->canLevelUp()) {
            $oldLevel = $this->level;
            $this->levelUp();
            $levelsGained[] = [
                'old_level' => $oldLevel,
                'new_level' => $this->level
            ];
        }
        
        $this->save();
        
        return [
            'experience_gained' => $amount,
            'levels_gained' => $levelsGained,
            'can_allocate_stats' => $this->hasUnallocatedStatPoints()
        ];
    }

    public function allocateStatPoint(string $stat): bool
    {
        if ($this->unallocated_stat_points > 0 && in_array($stat, ['str', 'dex', 'con', 'int', 'wis', 'cha'])) {
            $this->increment($stat);
            $this->decrement('unallocated_stat_points');
            
            if ($stat === 'con') {
                $this->increment('max_hp');
            }
            
            return true;
        }
        
        return false;
    }

    public function isInVillage(): bool
    {
        return is_null($this->current_road);
    }

    public function getCurrentPosition(): array
    {
        if ($this->isInVillage()) {
            return ['location' => 'village'];
        }
        
        return [
            'location' => 'adventure',
            'road' => $this->current_road,
            'level' => $this->current_level,
            'node_id' => $this->current_node_id
        ];
    }

    public function npcs(): HasMany
    {
        return $this->hasMany(NPC::class);
    }

    public function villageSpecializations(): HasMany
    {
        return $this->hasMany(VillageSpecialization::class);
    }

    public function getVillageInfo(): array
    {
        $npcService = app(\App\Services\NPCService::class);
        return $npcService->getVillageInfo($this);
    }

    public function getSettledNPCs()
    {
        return $this->npcs()->where('village_status', 'settled')->get();
    }

    public function getMigratingNPCs()
    {
        return $this->npcs()->where('village_status', 'migrating')->get();
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }

    public function factionReputations(): HasMany
    {
        return $this->hasMany(FactionReputation::class);
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function playerItems(): HasMany
    {
        return $this->hasMany(PlayerItem::class);
    }

    public function equippedItems(): HasMany
    {
        return $this->hasMany(PlayerItem::class)->where('is_equipped', true);
    }

    public function unequippedItems(): HasMany
    {
        return $this->hasMany(PlayerItem::class)->where('is_equipped', false);
    }

    public function getEquippedItem(string $slot): ?Equipment
    {
        return $this->equipment()->where('slot', $slot)->first();
    }

    public function getAllEquipment(): array
    {
        $equipment = [];
        foreach (Equipment::getAllSlots() as $slot) {
            $equipment[$slot] = $this->getEquippedItem($slot);
        }
        return $equipment;
    }

    public function getEquipmentStatModifier(string $stat): int
    {
        return $this->getTotalEquipmentStatModifier($stat);
    }

    public function getEquipmentACBonus(): int
    {
        return $this->getTotalEquipmentACBonus();
    }

    public function getTotalAC(): int
    {
        return $this->calculateAC();
    }

    /**
     * Calculate AC according to D&D 2024 rules
     */
    public function calculateAC(): int
    {
        $baseAC = 10; // Base AC without armor
        $dexModifier = $this->getStatModifier('dex');
        $maxDexModifier = null; // No limit by default
        
        // Check for equipped armor
        $equippedArmor = $this->getEquippedArmor();
        
        if ($equippedArmor) {
            $armorType = $equippedArmor->getArmorType();
            
            switch ($armorType) {
                case 'light':
                    // Light armor: Base AC + full Dex modifier
                    $baseAC = $equippedArmor->ac_bonus ?? 11; // Default leather armor AC
                    $maxDexModifier = null; // No limit
                    break;
                    
                case 'medium':
                    // Medium armor: Base AC + Dex modifier (max 2)
                    $baseAC = $equippedArmor->ac_bonus ?? 12; // Default hide armor AC
                    $maxDexModifier = 2;
                    break;
                    
                case 'heavy':
                    // Heavy armor: Base AC only, no Dex modifier
                    $baseAC = $equippedArmor->ac_bonus ?? 14; // Default ring mail AC
                    $maxDexModifier = 0;
                    break;
                    
                default:
                    // Unarmored: 10 + Dex modifier
                    $baseAC = 10;
                    $maxDexModifier = null;
                    break;
            }
        }
        
        // Apply Dex modifier with cap
        $effectiveDexModifier = $dexModifier;
        if ($maxDexModifier !== null) {
            $effectiveDexModifier = min($dexModifier, $maxDexModifier);
        }
        
        $totalAC = $baseAC + $effectiveDexModifier;
        
        // Add shield bonus
        $shieldBonus = $this->getShieldACBonus();
        $totalAC += $shieldBonus;
        
        // Add other AC bonuses from accessories/magical items
        $totalAC += $this->getOtherACBonuses();
        
        return $totalAC;
    }

    /**
     * Get equipped armor item
     */
    private function getEquippedArmor()
    {
        // Check new PlayerItem system first
        $armorPiece = $this->equippedItems()
            ->with('item')
            ->whereHas('item', function($query) {
                $query->where('type', 'armor')
                      ->whereIn('subtype', ['chest', 'helmet', 'pants', 'boots', 'gloves']);
            })
            ->first();
            
        if ($armorPiece && $armorPiece->item) {
            return $armorPiece->item;
        }
        
        // Check old Equipment system
        $equipment = $this->equipment()
            ->with('item')
            ->whereHas('item', function($query) {
                $query->where('type', 'armor')
                      ->whereIn('subtype', ['chest']); // Chest armor determines AC type
            })
            ->first();
            
        return $equipment?->item;
    }


    /**
     * Get shield AC bonus
     */
    private function getShieldACBonus(): int
    {
        // Check for equipped shield
        $shield = $this->equippedItems()
            ->with('item')
            ->whereHas('item', function($query) {
                $query->where('subtype', 'shield');
            })
            ->first();
            
        if ($shield && $shield->item) {
            return 2; // Standard shield bonus in D&D 2024
        }
        
        // Check old equipment system
        $oldShield = $this->equipment()
            ->with('item')
            ->where('slot', 'shield')
            ->first();
            
        if ($oldShield && $oldShield->item) {
            return 2;
        }
        
        return 0;
    }

    /**
     * Get other AC bonuses from accessories and magical items
     */
    private function getOtherACBonuses(): int
    {
        $totalBonus = 0;
        
        // Check accessories and other non-armor AC bonuses
        $accessories = $this->equippedItems()
            ->with('item')
            ->whereHas('item', function($query) {
                $query->where('type', 'accessory');
            })
            ->get();
            
        foreach ($accessories as $accessory) {
            if ($accessory->item && $accessory->item->ac_bonus) {
                $totalBonus += $accessory->item->ac_bonus;
            }
        }
        
        // Check old equipment system for accessories
        $oldAccessories = $this->equipment()
            ->with('item')
            ->whereHas('item', function($query) {
                $query->where('type', 'accessory');
            })
            ->get();
            
        foreach ($oldAccessories as $accessory) {
            if ($accessory->item && $accessory->item->ac_bonus) {
                $totalBonus += $accessory->item->ac_bonus;
            }
        }
        
        return $totalBonus;
    }

    public function getTotalStat(string $stat): int
    {
        $baseStat = $this->getAttribute($stat);
        $equipmentBonus = $this->getEquipmentStatModifier($stat);
        return $baseStat + $equipmentBonus;
    }

    public function equipItem(Item $item, string $slot): bool
    {
        if (!$item->canEquip($this)) {
            return false;
        }

        // Handle two-handed weapons
        if ($slot === Equipment::SLOT_TWO_HANDED_WEAPON) {
            $this->unequipSlot(Equipment::SLOT_WEAPON_1);
            $this->unequipSlot(Equipment::SLOT_WEAPON_2);
            $this->unequipSlot(Equipment::SLOT_SHIELD);
        } elseif (in_array($slot, [Equipment::SLOT_WEAPON_1, Equipment::SLOT_WEAPON_2, Equipment::SLOT_SHIELD])) {
            $this->unequipSlot(Equipment::SLOT_TWO_HANDED_WEAPON);
        }

        // Unequip current item in slot
        $this->unequipSlot($slot);

        // Equip new item
        $this->equipment()->create([
            'item_id' => $item->id,
            'slot' => $slot,
            'durability' => 100,
            'max_durability' => 100
        ]);

        // Trigger equipment achievements
        $this->triggerEquipmentAchievements();

        return true;
    }

    public function unequipSlot(string $slot): bool
    {
        $equipment = $this->getEquippedItem($slot);
        if ($equipment && $equipment->item) {
            // Add item back to inventory with current durability
            $this->addItemToInventory(
                $equipment->item, 
                1, 
                ['durability' => $equipment->durability]
            );
            
            $equipment->delete();
            return true;
        }
        return false;
    }

    public function hasUnallocatedStatPoints(): bool
    {
        return $this->unallocated_stat_points > 0;
    }

    /**
     * Get the character image path based on gender
     */
    public function getCharacterImagePath(): string
    {
        return asset("img/player_{$this->gender}.png");
    }

    // Inventory Management Methods

    public function addItemToInventory(Item $item, int $quantity = 1, array $metadata = []): Inventory
    {
        $existingInventoryItem = $this->inventory()->where('item_id', $item->id)->first();

        if ($existingInventoryItem && $existingInventoryItem->canStack()) {
            // Stack with existing item
            $existingInventoryItem->quantity += $quantity;
            $existingInventoryItem->save();
            return $existingInventoryItem;
        } else {
            // Create new inventory entry
            return $this->inventory()->create([
                'item_id' => $item->id,
                'quantity' => $quantity,
                'max_durability' => $item->max_durability ?? 100,
                'current_durability' => $item->max_durability ?? 100,
                'item_metadata' => $metadata
            ]);
        }
    }

    public function removeItemFromInventory(Item $item, int $quantity = 1): bool
    {
        $inventoryItem = $this->inventory()->where('item_id', $item->id)->first();

        if (!$inventoryItem || $inventoryItem->quantity < $quantity) {
            return false;
        }

        if ($inventoryItem->quantity <= $quantity) {
            $inventoryItem->delete();
        } else {
            $inventoryItem->quantity -= $quantity;
            $inventoryItem->save();
        }

        return true;
    }

    public function hasItemInInventory(Item $item, int $quantity = 1): bool
    {
        $inventoryItem = $this->inventory()->where('item_id', $item->id)->first();
        return $inventoryItem && $inventoryItem->quantity >= $quantity;
    }

    public function getInventoryItemCount(Item $item): int
    {
        $inventoryItem = $this->inventory()->where('item_id', $item->id)->first();
        return $inventoryItem ? $inventoryItem->quantity : 0;
    }

    public function getInventoryByCategory(string $category): \Illuminate\Database\Eloquent\Collection
    {
        // Get items from old inventory system
        $oldInventoryItems = $this->inventory()->with('item')->whereHas('item', function($query) use ($category) {
            $query->where('type', $category);
        })->get();
        
        // Get items from new PlayerItem system, but only unequipped ones
        $newPlayerItems = $this->playerItems()->with('item')
            ->where('is_equipped', false)
            ->whereHas('item', function($query) use ($category) {
                $query->where('type', $category);
            })->get();
        
        // Combine both collections
        return $oldInventoryItems->concat($newPlayerItems);
    }

    public function getInventorySlots(): array
    {
        return [
            'weapon' => $this->getInventoryByCategory('weapon'),
            'armor' => $this->getInventoryByCategory('armor'),
            'accessory' => $this->getInventoryByCategory('accessory'),
            'consumable' => $this->getInventoryByCategory('consumable'),
            'crafting_material' => $this->getInventoryByCategory('crafting_material'),
            'quest_item' => $this->getInventoryByCategory('quest_item'),
            'misc' => $this->getInventoryByCategory('misc')
        ];
    }

    public function equipItemFromInventory(int $inventoryItemId, string $slot): bool
    {
        $inventoryItem = $this->inventory()->with('item')->find($inventoryItemId);
        
        if (!$inventoryItem || !$inventoryItem->item) {
            return false;
        }

        $item = $inventoryItem->item;

        // Check if item can be equipped in this slot - handle flexible slots
        $itemSlot = $item->getEquipmentSlot();
        if ($itemSlot && $itemSlot !== $slot) {
            return false;
        }
        
        // For items with null slots (weapons/rings), validate the slot is appropriate
        if (!$itemSlot) {
            if (in_array($item->subtype, [Item::SUBTYPE_SWORD, Item::SUBTYPE_AXE, Item::SUBTYPE_MACE, Item::SUBTYPE_DAGGER])) {
                if (!in_array($slot, [Equipment::SLOT_WEAPON_1, Equipment::SLOT_WEAPON_2])) {
                    return false;
                }
            } elseif ($item->subtype === Item::SUBTYPE_RING) {
                if (!in_array($slot, [Equipment::SLOT_RING_1, Equipment::SLOT_RING_2])) {
                    return false;
                }
            }
        }

        // Check if player meets requirements
        if (!$item->canEquip($this)) {
            return false;
        }

        // Handle two-handed weapons and conflicting slots
        if ($slot === Equipment::SLOT_TWO_HANDED_WEAPON) {
            $this->unequipSlot(Equipment::SLOT_WEAPON_1);
            $this->unequipSlot(Equipment::SLOT_WEAPON_2);
            $this->unequipSlot(Equipment::SLOT_SHIELD);
        } elseif (in_array($slot, [Equipment::SLOT_WEAPON_1, Equipment::SLOT_WEAPON_2, Equipment::SLOT_SHIELD])) {
            $this->unequipSlot(Equipment::SLOT_TWO_HANDED_WEAPON);
        }

        // Unequip current item in slot (this will return it to inventory)
        $this->unequipSlot($slot);

        // Create equipment entry
        $this->equipment()->create([
            'item_id' => $item->id,
            'slot' => $slot,
            'durability' => $inventoryItem->current_durability,
            'max_durability' => $inventoryItem->max_durability
        ]);

        // Remove from inventory (non-stackable items, or reduce quantity for stackable)
        if ($inventoryItem->canStack() && $inventoryItem->quantity > 1) {
            $inventoryItem->quantity -= 1;
            $inventoryItem->save();
        } else {
            $inventoryItem->delete();
        }

        // Trigger equipment achievements
        $this->triggerEquipmentAchievements();

        return true;
    }

    public function unequipToInventory(string $slot): bool
    {
        $equipment = $this->getEquippedItem($slot);
        
        if (!$equipment || !$equipment->item) {
            return false;
        }

        // Add item back to inventory
        $this->addItemToInventory(
            $equipment->item, 
            1, 
            ['durability' => $equipment->durability]
        );

        // Remove from equipment
        $equipment->delete();

        return true;
    }

    public function getTotalInventoryItems(): int
    {
        $oldSystemTotal = $this->inventory()->sum('quantity');
        $newSystemTotal = $this->playerItems()->where('is_equipped', false)->sum('quantity');
        return $oldSystemTotal + $newSystemTotal;
    }

    public function getInventoryValue(): int
    {
        $oldSystemValue = $this->inventory()->with('item')->get()->sum(function($inventoryItem) {
            return ($inventoryItem->item->value ?? 0) * $inventoryItem->quantity;
        });
        
        $newSystemValue = $this->playerItems()->with('item')->where('is_equipped', false)->get()->sum(function($playerItem) {
            return ($playerItem->item->value ?? 0) * $playerItem->quantity;
        });
        
        return $oldSystemValue + $newSystemValue;
    }

    /**
     * Add item to the new PlayerItem inventory system
     */
    public function addItemToPlayerInventory(Item $item, int $quantity = 1): PlayerItem
    {
        // Check if item already exists in inventory and is stackable
        if ($item->is_stackable) {
            $existingPlayerItem = $this->playerItems()
                ->where('item_id', $item->id)
                ->where('is_equipped', false)
                ->first();
            
            if ($existingPlayerItem) {
                $existingPlayerItem->quantity += $quantity;
                $existingPlayerItem->save();
                return $existingPlayerItem;
            }
        }
        
        // Create new player item entry
        return $this->playerItems()->create([
            'item_id' => $item->id,
            'quantity' => $quantity,
            'current_durability' => $item->max_durability,
            'is_equipped' => false
        ]);
    }

    /**
     * Get items available for equipping by type
     */
    public function getAvailableItemsByType(string $type): \Illuminate\Database\Eloquent\Collection
    {
        return $this->playerItems()
            ->with('item')
            ->where('is_equipped', false)
            ->whereHas('item', function($query) use ($type) {
                $query->where('type', $type);
            })
            ->get();
    }

    /**
     * Get equipped item by slot using PlayerItem system
     */
    public function getEquippedPlayerItem(string $slot): ?PlayerItem
    {
        return $this->playerItems()
            ->with('item')
            ->where('is_equipped', true)
            ->where('equipment_slot', $slot)
            ->first();
    }

    /**
     * Calculate equipment stat bonuses from both old Equipment and new PlayerItem systems
     */
    public function getTotalEquipmentStatModifier(string $stat): int
    {
        $totalModifier = 0;
        
        // Old equipment system
        foreach ($this->equipment as $equipmentPiece) {
            $totalModifier += $equipmentPiece->getEffectiveStatModifier($stat);
        }
        
        // New PlayerItem system
        foreach ($this->equippedItems as $playerItem) {
            if ($playerItem->item && $playerItem->item->stats_modifiers) {
                $totalModifier += $playerItem->item->stats_modifiers[$stat] ?? 0;
            }
        }
        
        return $totalModifier;
    }

    /**
     * Calculate total AC bonus from equipment
     */
    public function getTotalEquipmentACBonus(): int
    {
        $totalAC = 0;
        
        // Old equipment system
        foreach ($this->equipment as $equipmentPiece) {
            $totalAC += $equipmentPiece->getEffectiveACBonus();
        }
        
        // New PlayerItem system
        foreach ($this->equippedItems as $playerItem) {
            if ($playerItem->item) {
                $totalAC += $playerItem->item->ac_bonus ?? 0;
            }
        }
        
        return $totalAC;
    }

    /**
     * Get equipped weapon damage bonus
     */
    public function getWeaponDamageBonus(): int
    {
        $totalDamageBonus = 0;
        
        // Check old Equipment system
        foreach ($this->equipment as $equipmentPiece) {
            if ($equipmentPiece->item && $equipmentPiece->item->type === 'weapon') {
                $totalDamageBonus += $equipmentPiece->item->damage_bonus ?? 0;
            }
        }
        
        // Check new PlayerItem system
        foreach ($this->equippedItems as $playerItem) {
            if ($playerItem->item && $playerItem->item->type === 'weapon') {
                $totalDamageBonus += $playerItem->item->damage_bonus ?? 0;
            }
        }
        
        return $totalDamageBonus;
    }

    /**
     * Get max damage that player can deal
     */
    public function getMaxDamage(): string
    {
        $strModifier = $this->getStatModifier('str');
        $weaponDamageBonus = $this->getWeaponDamageBonus();
        
        // Get weapon damage dice from equipped weapons
        $damageDice = '1d4'; // Default unarmed damage
        
        // Check equipped weapons for damage dice
        foreach ($this->equippedItems as $playerItem) {
            if ($playerItem->item && $playerItem->item->type === 'weapon' && $playerItem->item->damage_dice) {
                $damageDice = $playerItem->item->damage_dice;
                break; // Use first weapon found
            }
        }
        
        // Check old equipment system as fallback
        if ($damageDice === '1d4') {
            foreach ($this->equipment as $equipmentPiece) {
                if ($equipmentPiece->item && $equipmentPiece->item->type === 'weapon' && $equipmentPiece->item->damage_dice) {
                    $damageDice = $equipmentPiece->item->damage_dice;
                    break;
                }
            }
        }
        
        $totalBonus = $strModifier + $weaponDamageBonus;
        
        if ($totalBonus > 0) {
            return $damageDice . '+' . $totalBonus;
        } elseif ($totalBonus < 0) {
            return $damageDice . $totalBonus;
        } else {
            return $damageDice;
        }
    }

    // Crafting System Relations

    public function knownRecipes(): BelongsToMany
    {
        return $this->belongsToMany(CraftingRecipe::class, 'player_known_recipes', 'player_id', 'recipe_id')
                    ->withPivot(['learned_at', 'discovery_method', 'times_crafted'])
                    ->withTimestamps();
    }

    public function learnRecipe(CraftingRecipe $recipe, string $discoveryMethod = null): bool
    {
        if ($this->knownRecipes()->where('recipe_id', $recipe->id)->exists()) {
            return false; // Already known
        }

        $this->knownRecipes()->attach($recipe->id, [
            'learned_at' => now(),
            'discovery_method' => $discoveryMethod,
            'times_crafted' => 0
        ]);

        return true;
    }

    public function knowsRecipe(CraftingRecipe $recipe): bool
    {
        return $this->knownRecipes()->where('recipe_id', $recipe->id)->exists();
    }

    public function canCraftRecipe(CraftingRecipe $recipe): bool
    {
        return $this->knowsRecipe($recipe) && 
               $recipe->canPlayerCraft($this) && 
               $recipe->hasRequiredMaterials($this);
    }

    public function getAvailableRecipesByCategory(string $category = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->knownRecipes()->with(['resultItem', 'materials.materialItem']);
        
        if ($category) {
            $query->where('category', $category);
        }
        
        return $query->get();
    }

    private function triggerEquipmentAchievements(): void
    {
        $achievementService = app(\App\Services\AchievementService::class);
        
        // Trigger item equipped achievement
        $achievementService->processGameEvent($this, 'item_equipped');

        // Check for equipment slot completion achievements
        $achievementService->processGameEvent($this, 'equipment_slot_filled');
        
        // Check for equipment rarity achievements
        $achievementService->processGameEvent($this, 'equipment_upgrade');
    }

}
