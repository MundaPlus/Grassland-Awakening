<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Item;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add stackable crafting materials to the items table
        $this->createCraftingMaterials();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove crafting materials
        Item::whereIn('type', ['crafting_material'])
            ->whereIn('subtype', [
                'metal', 'wood', 'herb_health', 'herb_mana', 'gem'
            ])->delete();
    }

    private function createCraftingMaterials(): void
    {
        $materials = [
            // Metals for weapons (5 levels matching weapon rarity)
            [
                'name' => 'Copper Ore',
                'description' => 'Common metal ore used for basic weapon crafting.',
                'type' => 'crafting_material',
                'subtype' => 'metal',
                'rarity' => 'common',
                'level_requirement' => 1,
                'is_stackable' => true,
                'max_stack_size' => 99,
                'base_value' => 5,
                'icon' => '⚫'
            ],
            [
                'name' => 'Bronze Ingot',
                'description' => 'Refined bronze metal for crafting uncommon weapons.',
                'type' => 'crafting_material',
                'subtype' => 'metal',
                'rarity' => 'uncommon',
                'level_requirement' => 3,
                'is_stackable' => true,
                'max_stack_size' => 99,
                'base_value' => 15,
                'icon' => '🟤'
            ],
            [
                'name' => 'Iron Ingot',
                'description' => 'Strong iron metal for crafting rare weapons.',
                'type' => 'crafting_material',
                'subtype' => 'metal',
                'rarity' => 'rare',
                'level_requirement' => 5,
                'is_stackable' => true,
                'max_stack_size' => 99,
                'base_value' => 35,
                'icon' => '⚪'
            ],
            [
                'name' => 'Mithril Ore',
                'description' => 'Precious mithril ore for crafting epic weapons.',
                'type' => 'crafting_material',
                'subtype' => 'metal',
                'rarity' => 'epic',
                'level_requirement' => 8,
                'is_stackable' => true,
                'max_stack_size' => 99,
                'base_value' => 75,
                'icon' => '🔵'
            ],
            [
                'name' => 'Adamantium Shard',
                'description' => 'Legendary adamantium for crafting the finest weapons.',
                'type' => 'crafting_material',
                'subtype' => 'metal',
                'rarity' => 'legendary',
                'level_requirement' => 12,
                'is_stackable' => true,
                'max_stack_size' => 99,
                'base_value' => 150,
                'icon' => '🟣'
            ],

            // Wood materials
            [
                'name' => 'Oak Wood',
                'description' => 'Sturdy oak wood for crafting weapon handles and armor.',
                'type' => 'crafting_material',
                'subtype' => 'wood',
                'rarity' => 'common',
                'level_requirement' => 1,
                'is_stackable' => true,
                'max_stack_size' => 99,
                'base_value' => 3,
                'icon' => '🪵'
            ],
            [
                'name' => 'Enchanted Willow',
                'description' => 'Magical willow wood imbued with natural energy.',
                'type' => 'crafting_material',
                'subtype' => 'wood',
                'rarity' => 'uncommon',
                'level_requirement' => 4,
                'is_stackable' => true,
                'max_stack_size' => 99,
                'base_value' => 12,
                'icon' => '🌿'
            ],
            [
                'name' => 'Ancient Heartwood',
                'description' => 'Rare wood from ancient trees, perfect for rare items.',
                'type' => 'crafting_material',
                'subtype' => 'wood',
                'rarity' => 'rare',
                'level_requirement' => 7,
                'is_stackable' => true,
                'max_stack_size' => 99,
                'base_value' => 25,
                'icon' => '🌳'
            ],

            // Health herbs (3 levels)
            [
                'name' => 'Healing Moss',
                'description' => 'Common moss with minor healing properties.',
                'type' => 'crafting_material',
                'subtype' => 'herb_health',
                'rarity' => 'common',
                'level_requirement' => 1,
                'is_stackable' => true,
                'max_stack_size' => 99,
                'base_value' => 8,
                'icon' => '🌱'
            ],
            [
                'name' => 'Red Clover',
                'description' => 'Uncommon herb used for moderate healing potions.',
                'type' => 'crafting_material',
                'subtype' => 'herb_health',
                'rarity' => 'uncommon',
                'level_requirement' => 3,
                'is_stackable' => true,
                'max_stack_size' => 99,
                'base_value' => 18,
                'icon' => '🌺'
            ],
            [
                'name' => 'Golden Root',
                'description' => 'Rare golden root for powerful healing potions.',
                'type' => 'crafting_material',
                'subtype' => 'herb_health',
                'rarity' => 'rare',
                'level_requirement' => 6,
                'is_stackable' => true,
                'max_stack_size' => 99,
                'base_value' => 40,
                'icon' => '🌼'
            ],

            // Mana herbs (3 levels)
            [
                'name' => 'Blue Sage',
                'description' => 'Common herb that restores minor magical energy.',
                'type' => 'crafting_material',
                'subtype' => 'herb_mana',
                'rarity' => 'common',
                'level_requirement' => 2,
                'is_stackable' => true,
                'max_stack_size' => 99,
                'base_value' => 10,
                'icon' => '🔵'
            ],
            [
                'name' => 'Mystic Thistle',
                'description' => 'Uncommon thistle that enhances mana regeneration.',
                'type' => 'crafting_material',
                'subtype' => 'herb_mana',
                'rarity' => 'uncommon',
                'level_requirement' => 4,
                'is_stackable' => true,
                'max_stack_size' => 99,
                'base_value' => 22,
                'icon' => '💙'
            ],
            [
                'name' => 'Arcane Lotus',
                'description' => 'Rare lotus flower that greatly restores magical power.',
                'type' => 'crafting_material',
                'subtype' => 'herb_mana',
                'rarity' => 'rare',
                'level_requirement' => 7,
                'is_stackable' => true,
                'max_stack_size' => 99,
                'base_value' => 45,
                'icon' => '🪷'
            ],

            // Gems for accessories (5 levels)
            [
                'name' => 'Rough Quartz',
                'description' => 'Common quartz crystal for basic accessory crafting.',
                'type' => 'crafting_material',
                'subtype' => 'gem',
                'rarity' => 'common',
                'level_requirement' => 2,
                'is_stackable' => true,
                'max_stack_size' => 99,
                'base_value' => 12,
                'icon' => '💎'
            ],
            [
                'name' => 'Polished Amethyst',
                'description' => 'Uncommon purple gem for enchanted accessories.',
                'type' => 'crafting_material',
                'subtype' => 'gem',
                'rarity' => 'uncommon',
                'level_requirement' => 4,
                'is_stackable' => true,
                'max_stack_size' => 99,
                'base_value' => 25,
                'icon' => '🟣'
            ],
            [
                'name' => 'Emerald Shard',
                'description' => 'Rare emerald fragment for powerful accessories.',
                'type' => 'crafting_material',
                'subtype' => 'gem',
                'rarity' => 'rare',
                'level_requirement' => 6,
                'is_stackable' => true,
                'max_stack_size' => 99,
                'base_value' => 55,
                'icon' => '🟢'
            ],
            [
                'name' => 'Sapphire Crystal',
                'description' => 'Epic sapphire crystal for legendary accessories.',
                'type' => 'crafting_material',
                'subtype' => 'gem',
                'rarity' => 'epic',
                'level_requirement' => 9,
                'is_stackable' => true,
                'max_stack_size' => 99,
                'base_value' => 120,
                'icon' => '🔷'
            ],
            [
                'name' => 'Perfect Diamond',
                'description' => 'Legendary flawless diamond for the finest accessories.',
                'type' => 'crafting_material',
                'subtype' => 'gem',
                'rarity' => 'legendary',
                'level_requirement' => 12,
                'is_stackable' => true,
                'max_stack_size' => 99,
                'base_value' => 250,
                'icon' => '💍'
            ],
        ];

        foreach ($materials as $material) {
            Item::create($material);
        }
    }
};