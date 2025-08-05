<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Support\Facades\DB;

class ItemGenerationService
{
    /**
     * Generate a random item for combat loot
     */
    public function generateCombatLoot(int $level, string $enemyType = 'generic', string $difficulty = 'normal'): ?Item
    {
        $dropChance = $this->getCombatDropChance($level, $difficulty);
        
        if (rand(1, 100) > $dropChance) {
            return null; // No drop
        }
        
        // Define item weights for combat
        $itemWeights = [
            Item::TYPE_WEAPON => 40,
            Item::TYPE_ARMOR => 45,
            Item::TYPE_CONSUMABLE => 15
        ];
        
        $itemType = $this->weightedRandom(array_keys($itemWeights), $itemWeights);
        return $this->generateItemByType($itemType, $level, $enemyType, $difficulty);
    }
    
    /**
     * Generate a random item for treasure nodes
     */
    public function generateTreasureLoot(int $level, string $difficulty = 'normal'): ?Item
    {
        $dropChance = $this->getTreasureDropChance($level, $difficulty);
        
        if (rand(1, 100) > $dropChance) {
            return null; // No drop
        }
        
        // Treasure nodes have better chance for higher quality items
        $itemWeights = [
            Item::TYPE_WEAPON => 30,
            Item::TYPE_ARMOR => 30,
            Item::TYPE_ACCESSORY => 20,
            Item::TYPE_CONSUMABLE => 20
        ];
        
        $itemType = $this->weightedRandom(array_keys($itemWeights), $itemWeights);
        return $this->generateItemByType($itemType, $level, 'treasure', $difficulty);
    }
    
    /**
     * Generate accessory items for events (exclusive to events)
     */
    public function generateEventLoot(int $level): ?Item
    {
        $dropChance = $this->getEventDropChance($level);
        
        if (rand(1, 100) > $dropChance) {
            return null; // No drop
        }
        
        // Events are the only source of accessories
        $itemWeights = [
            Item::TYPE_ACCESSORY => 60,
            Item::TYPE_CONSUMABLE => 25,
            Item::TYPE_MATERIAL => 15
        ];
        
        $itemType = $this->weightedRandom(array_keys($itemWeights), $itemWeights);
        return $this->generateItemByType($itemType, $level, 'event');
    }
    
    /**
     * Generate item by type and level
     */
    private function generateItemByType(string $type, int $level, string $source = 'generic', string $difficulty = 'normal'): Item
    {
        $rarity = $this->determineRarity($level, $source, $difficulty);
        
        switch ($type) {
            case Item::TYPE_WEAPON:
                return $this->generateWeapon($level, $rarity);
            case Item::TYPE_ARMOR:
                return $this->generateArmor($level, $rarity);
            case Item::TYPE_ACCESSORY:
                return $this->generateAccessory($level, $rarity);
            case Item::TYPE_CONSUMABLE:
                return $this->generateConsumable($level, $rarity);
            case Item::TYPE_MATERIAL:
                return $this->generateMaterial($level, $rarity);
            default:
                return $this->generateWeapon($level, $rarity);
        }
    }
    
    /**
     * Generate a weapon
     */
    private function generateWeapon(int $level, string $rarity): Item
    {
        $weaponTypes = [
            Item::SUBTYPE_SWORD, Item::SUBTYPE_AXE, Item::SUBTYPE_MACE, 
            Item::SUBTYPE_DAGGER, Item::SUBTYPE_BOW, Item::SUBTYPE_WAND, 
            Item::SUBTYPE_STAFF, Item::SUBTYPE_TWO_HANDED
        ];
        
        $subtype = $weaponTypes[array_rand($weaponTypes)];
        $rarityMultiplier = $this->getRarityMultiplier($rarity);
        
        // Generate stats based on weapon type and level
        $baseDamage = $this->getBaseDamageForWeapon($subtype);
        $damageBonus = intval(($level * 0.5 + rand(0, 2)) * $rarityMultiplier);
        
        $statsModifiers = [];
        if (rand(1, 100) <= 30) { // 30% chance of additional stat bonus
            $statTypes = ['str', 'dex', 'int', 'wis'];
            $bonusStat = $statTypes[array_rand($statTypes)];
            $statsModifiers[$bonusStat] = rand(1, 3) * $rarityMultiplier;
        }
        
        return Item::create([
            'name' => $this->generateWeaponName($subtype, $rarity),
            'description' => $this->generateWeaponDescription($subtype, $rarity),
            'type' => Item::TYPE_WEAPON,
            'subtype' => $subtype,
            'rarity' => $rarity,
            'level_requirement' => max(1, $level - 2),
            'stats_modifiers' => $statsModifiers,
            'damage_dice' => $baseDamage,
            'damage_bonus' => $damageBonus,
            'is_equippable' => true,
            'base_value' => $this->calculateBaseValue($level, $rarity, 'weapon'),
            'max_durability' => 100 * $rarityMultiplier
        ]);
    }
    
    /**
     * Generate armor
     */
    private function generateArmor(int $level, string $rarity): Item
    {
        $armorTypes = [
            Item::SUBTYPE_HELMET, Item::SUBTYPE_CHEST, Item::SUBTYPE_PANTS,
            Item::SUBTYPE_BOOTS, Item::SUBTYPE_GLOVES
        ];
        
        $subtype = $armorTypes[array_rand($armorTypes)];
        $rarityMultiplier = $this->getRarityMultiplier($rarity);
        
        $acBonus = intval((1 + $level * 0.3) * $rarityMultiplier);
        
        $statsModifiers = [];
        if (rand(1, 100) <= 25) { // 25% chance of stat bonus
            $statTypes = ['str', 'dex', 'con'];
            $bonusStat = $statTypes[array_rand($statTypes)];
            $statsModifiers[$bonusStat] = rand(1, 2) * $rarityMultiplier;
        }
        
        return Item::create([
            'name' => $this->generateArmorName($subtype, $rarity),
            'description' => $this->generateArmorDescription($subtype, $rarity),
            'type' => Item::TYPE_ARMOR,
            'subtype' => $subtype,
            'rarity' => $rarity,
            'level_requirement' => max(1, $level - 2),
            'stats_modifiers' => $statsModifiers,
            'ac_bonus' => $acBonus,
            'is_equippable' => true,
            'base_value' => $this->calculateBaseValue($level, $rarity, 'armor'),
            'max_durability' => 100 * $rarityMultiplier
        ]);
    }
    
    /**
     * Generate accessory (only from events)
     */
    private function generateAccessory(int $level, string $rarity): Item
    {
        $accessoryTypes = [Item::SUBTYPE_RING, Item::SUBTYPE_NECKLACE, Item::SUBTYPE_ARTIFACT];
        $subtype = $accessoryTypes[array_rand($accessoryTypes)];
        $rarityMultiplier = $this->getRarityMultiplier($rarity);
        
        // Accessories focus on stat bonuses
        $statsModifiers = [];
        $numStats = rand(1, 3); // 1-3 stat bonuses
        $allStats = ['str', 'dex', 'con', 'int', 'wis', 'cha'];
        shuffle($allStats);
        
        for ($i = 0; $i < $numStats; $i++) {
            $statsModifiers[$allStats[$i]] = rand(1, 4) * $rarityMultiplier;
        }
        
        return Item::create([
            'name' => $this->generateAccessoryName($subtype, $rarity),
            'description' => $this->generateAccessoryDescription($subtype, $rarity),
            'type' => Item::TYPE_ACCESSORY,
            'subtype' => $subtype,
            'rarity' => $rarity,
            'level_requirement' => max(1, $level - 1),
            'stats_modifiers' => $statsModifiers,
            'is_equippable' => true,
            'base_value' => $this->calculateBaseValue($level, $rarity, 'accessory'),
            'max_durability' => 50 * $rarityMultiplier
        ]);
    }
    
    /**
     * Generate consumable
     */
    private function generateConsumable(int $level, string $rarity): Item
    {
        $consumableTypes = ['health_potion', 'mana_potion', 'buff_scroll'];
        $consumableType = $consumableTypes[array_rand($consumableTypes)];
        
        return Item::create([
            'name' => $this->generateConsumableName($consumableType, $rarity),
            'description' => $this->generateConsumableDescription($consumableType, $rarity),
            'type' => Item::TYPE_CONSUMABLE,
            'subtype' => $consumableType,
            'rarity' => $rarity,
            'level_requirement' => 1,
            'is_consumable' => true,
            'is_stackable' => true,
            'max_stack_size' => 10,
            'base_value' => $this->calculateBaseValue($level, $rarity, 'consumable')
        ]);
    }
    
    /**
     * Generate material
     */
    private function generateMaterial(int $level, string $rarity): Item
    {
        $materialTypes = ['ore', 'gem', 'essence', 'herb'];
        $materialType = $materialTypes[array_rand($materialTypes)];
        
        return Item::create([
            'name' => $this->generateMaterialName($materialType, $rarity),
            'description' => $this->generateMaterialDescription($materialType, $rarity),
            'type' => Item::TYPE_MATERIAL,
            'subtype' => $materialType,
            'rarity' => $rarity,
            'level_requirement' => 1,
            'is_stackable' => true,
            'max_stack_size' => 50,
            'base_value' => $this->calculateBaseValue($level, $rarity, 'material')
        ]);
    }
    
    /**
     * Drop chance calculations
     */
    public function getCombatDropChance(int $level, string $difficulty = 'normal'): int
    {
        $baseChance = min(30, 15 + $level); // 15-30% based on level
        
        return match($difficulty) {
            'easy' => $baseChance + 50,    // +50% drop chance on easy (more items)
            'normal' => $baseChance + 35,  // +35% drop chance on normal
            'hard' => $baseChance + 20,    // +20% drop chance on hard (fewer items, but higher quality)
            default => $baseChance + 35
        };
    }
    
    public function getTreasureDropChance(int $level, string $difficulty = 'normal'): int
    {
        $baseChance = min(70, 40 + $level * 2); // 40-70% based on level
        
        return match($difficulty) {
            'easy' => $baseChance + 30,    // +30% drop chance on easy (more treasure)
            'normal' => $baseChance + 20,  // +20% drop chance on normal  
            'hard' => $baseChance + 10,    // +10% drop chance on hard (less treasure, but higher quality)
            default => $baseChance + 20
        };
    }
    
    public function getEventDropChance(int $level): int
    {
        return min(25, 10 + $level); // 10-25% based on level
    }
    
    /**
     * Rarity determination with difficulty-based caps
     */
    private function determineRarity(int $level, string $source, string $difficulty = 'normal'): string
    {
        // Base rarity chances
        $rarityChances = [
            Item::RARITY_COMMON => 60,
            Item::RARITY_UNCOMMON => 25,
            Item::RARITY_RARE => 12,
            Item::RARITY_EPIC => 2,
            Item::RARITY_LEGENDARY => 1
        ];
        
        // Apply difficulty-based rarity caps and adjustments
        switch ($difficulty) {
            case 'easy':
                // Easy: common, uncommon, very low chance of rare
                $rarityChances = [
                    Item::RARITY_COMMON => 70,
                    Item::RARITY_UNCOMMON => 27,
                    Item::RARITY_RARE => 3,
                    Item::RARITY_EPIC => 0,
                    Item::RARITY_LEGENDARY => 0
                ];
                break;
                
            case 'normal':
                // Normal: uncommon, rare, very low chance of epic
                $rarityChances = [
                    Item::RARITY_COMMON => 45,
                    Item::RARITY_UNCOMMON => 40,
                    Item::RARITY_RARE => 13,
                    Item::RARITY_EPIC => 2,
                    Item::RARITY_LEGENDARY => 0
                ];
                break;
                
            case 'hard':
                // Hard: rare, epic, very low chance of legendary
                $rarityChances = [
                    Item::RARITY_COMMON => 25,
                    Item::RARITY_UNCOMMON => 35,
                    Item::RARITY_RARE => 32,
                    Item::RARITY_EPIC => 7,
                    Item::RARITY_LEGENDARY => 1
                ];
                break;
        }
        
        // Adjust chances based on level
        if ($level >= 10) {
            $rarityChances[Item::RARITY_UNCOMMON] += 5;
            $rarityChances[Item::RARITY_RARE] += 3;
            $rarityChances[Item::RARITY_COMMON] -= 8;
        }
        
        // Treasure bonus (better rarities from treasure chests)
        if ($source === 'treasure') {
            $rarityChances[Item::RARITY_RARE] += 8;
            $rarityChances[Item::RARITY_EPIC] += 3;
            $rarityChances[Item::RARITY_COMMON] -= 11;
        }
        
        // Ensure no negative chances
        foreach ($rarityChances as $rarity => $chance) {
            $rarityChances[$rarity] = max(0, $chance);
        }
        
        return $this->weightedRandom(array_keys($rarityChances), $rarityChances);
    }
    
    private function getRarityMultiplier(string $rarity): float
    {
        return match($rarity) {
            Item::RARITY_COMMON => 1.0,
            Item::RARITY_UNCOMMON => 1.3,
            Item::RARITY_RARE => 1.6,
            Item::RARITY_EPIC => 2.0,
            Item::RARITY_LEGENDARY => 2.5,
            default => 1.0
        };
    }
    
    /**
     * Base damage for weapons
     */
    private function getBaseDamageForWeapon(string $subtype): string
    {
        return match($subtype) {
            Item::SUBTYPE_DAGGER => '1d4',
            Item::SUBTYPE_SWORD, Item::SUBTYPE_AXE, Item::SUBTYPE_MACE => '1d8',
            Item::SUBTYPE_BOW => '1d8',
            Item::SUBTYPE_WAND => '1d6',
            Item::SUBTYPE_STAFF => '1d8',
            Item::SUBTYPE_TWO_HANDED => '2d6',
            default => '1d6'
        };
    }
    
    /**
     * Name generation
     */
    private function generateWeaponName(string $subtype, string $rarity): string
    {
        $rarityPrefixes = [
            Item::RARITY_COMMON => ['Simple', 'Basic', 'Crude'],
            Item::RARITY_UNCOMMON => ['Fine', 'Well-crafted', 'Sharp'],
            Item::RARITY_RARE => ['Masterwork', 'Enchanted', 'Superior'],
            Item::RARITY_EPIC => ['Legendary', 'Magical', 'Ancient'],
            Item::RARITY_LEGENDARY => ['Divine', 'Mythical', 'Artifact']
        ];
        
        $weaponNames = [
            Item::SUBTYPE_SWORD => 'Sword',
            Item::SUBTYPE_AXE => 'Axe',
            Item::SUBTYPE_MACE => 'Mace',
            Item::SUBTYPE_DAGGER => 'Dagger',
            Item::SUBTYPE_BOW => 'Bow',
            Item::SUBTYPE_WAND => 'Wand',
            Item::SUBTYPE_STAFF => 'Staff',
            Item::SUBTYPE_TWO_HANDED => 'Greatsword'
        ];
        
        $prefix = $rarityPrefixes[$rarity][array_rand($rarityPrefixes[$rarity])];
        $name = $weaponNames[$subtype];
        
        return "{$prefix} {$name}";
    }
    
    private function generateArmorName(string $subtype, string $rarity): string
    {
        $rarityPrefixes = [
            Item::RARITY_COMMON => ['Leather', 'Cloth', 'Simple'],
            Item::RARITY_UNCOMMON => ['Studded', 'Reinforced', 'Quality'],
            Item::RARITY_RARE => ['Chain', 'Scale', 'Masterwork'],
            Item::RARITY_EPIC => ['Plate', 'Enchanted', 'Royal'],
            Item::RARITY_LEGENDARY => ['Dragon', 'Divine', 'Mythril']
        ];
        
        $armorNames = [
            Item::SUBTYPE_HELMET => 'Helmet',
            Item::SUBTYPE_CHEST => 'Chestplate',
            Item::SUBTYPE_PANTS => 'Leggings',
            Item::SUBTYPE_BOOTS => 'Boots',
            Item::SUBTYPE_GLOVES => 'Gloves'
        ];
        
        $prefix = $rarityPrefixes[$rarity][array_rand($rarityPrefixes[$rarity])];
        $name = $armorNames[$subtype];
        
        return "{$prefix} {$name}";
    }
    
    private function generateAccessoryName(string $subtype, string $rarity): string
    {
        $rarityPrefixes = [
            Item::RARITY_COMMON => ['Simple', 'Plain', 'Common'],
            Item::RARITY_UNCOMMON => ['Silver', 'Polished', 'Ornate'],
            Item::RARITY_RARE => ['Golden', 'Jeweled', 'Enchanted'],
            Item::RARITY_EPIC => ['Platinum', 'Mystical', 'Ancient'],
            Item::RARITY_LEGENDARY => ['Divine', 'Celestial', 'Legendary']
        ];
        
        $accessoryNames = [
            Item::SUBTYPE_RING => 'Ring',
            Item::SUBTYPE_NECKLACE => 'Amulet',
            Item::SUBTYPE_ARTIFACT => 'Artifact'
        ];
        
        $prefix = $rarityPrefixes[$rarity][array_rand($rarityPrefixes[$rarity])];
        $name = $accessoryNames[$subtype];
        
        return "{$prefix} {$name}";
    }
    
    private function generateConsumableName(string $type, string $rarity): string
    {
        $names = [
            'health_potion' => 'Health Potion',
            'mana_potion' => 'Mana Potion',
            'buff_scroll' => 'Enhancement Scroll'
        ];
        
        $prefix = match($rarity) {
            Item::RARITY_COMMON => 'Minor',
            Item::RARITY_UNCOMMON => 'Lesser',
            Item::RARITY_RARE => 'Greater',
            Item::RARITY_EPIC => 'Superior',
            Item::RARITY_LEGENDARY => 'Divine',
            default => 'Minor'
        };
        
        return "{$prefix} {$names[$type]}";
    }
    
    private function generateMaterialName(string $type, string $rarity): string
    {
        $names = [
            'ore' => 'Ore',
            'gem' => 'Gem',
            'essence' => 'Essence',
            'herb' => 'Herb'
        ];
        
        $prefix = match($rarity) {
            Item::RARITY_COMMON => 'Iron',
            Item::RARITY_UNCOMMON => 'Silver',
            Item::RARITY_RARE => 'Gold',
            Item::RARITY_EPIC => 'Platinum',
            Item::RARITY_LEGENDARY => 'Mythril',
            default => 'Iron'
        };
        
        return "{$prefix} {$names[$type]}";
    }
    
    /**
     * Description generation
     */
    private function generateWeaponDescription(string $subtype, string $rarity): string
    {
        return "A {$rarity} {$subtype} crafted for battle.";
    }
    
    private function generateArmorDescription(string $subtype, string $rarity): string
    {
        return "A piece of {$rarity} {$subtype} armor providing protection.";
    }
    
    private function generateAccessoryDescription(string $subtype, string $rarity): string
    {
        return "A {$rarity} {$subtype} imbued with magical properties.";
    }
    
    private function generateConsumableDescription(string $type, string $rarity): string
    {
        return "A {$rarity} consumable that provides temporary benefits.";
    }
    
    private function generateMaterialDescription(string $type, string $rarity): string
    {
        return "A {$rarity} crafting material of high quality.";
    }
    
    /**
     * Value calculation
     */
    private function calculateBaseValue(int $level, string $rarity, string $type): int
    {
        $baseValues = [
            'weapon' => 50,
            'armor' => 40,
            'accessory' => 80,
            'consumable' => 15,
            'material' => 10
        ];
        
        $rarityMultiplier = $this->getRarityMultiplier($rarity);
        $levelMultiplier = 1 + ($level * 0.1);
        
        return intval($baseValues[$type] * $rarityMultiplier * $levelMultiplier);
    }
    
    /**
     * Weighted random selection
     */
    private function weightedRandom(array $options, array $weights): string
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
        
        $currentWeight = 0;
        foreach ($options as $option) {
            $currentWeight += $weights[$option];
            if ($random <= $currentWeight) {
                return $option;
            }
        }
        
        return $options[0]; // Fallback
    }
}