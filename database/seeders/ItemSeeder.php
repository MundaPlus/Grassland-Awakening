<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\Player;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            // Weapons
            [
                'name' => 'Iron Sword',
                'description' => 'A sturdy iron sword with a sharp edge.',
                'type' => 'weapon',
                'subtype' => 'sword',
                'rarity' => 'common',
                'level_requirement' => 1,
                'damage_dice' => '1d8',
                'damage_bonus' => 1,
                'stats_modifiers' => ['str' => 1],
                'value' => 50,
                'max_durability' => 100
            ],
            [
                'name' => 'Elven Bow',
                'description' => 'A finely crafted bow made by skilled elves.',
                'type' => 'weapon',
                'subtype' => 'bow',
                'rarity' => 'uncommon',
                'level_requirement' => 3,
                'damage_dice' => '1d8',
                'damage_bonus' => 2,
                'stats_modifiers' => ['dex' => 2],
                'value' => 120,
                'max_durability' => 80
            ],
            [
                'name' => 'Wizard Staff',
                'description' => 'A magical staff crackling with arcane energy.',
                'type' => 'weapon',
                'subtype' => 'staff',
                'rarity' => 'rare',
                'level_requirement' => 5,
                'damage_dice' => '1d6',
                'damage_bonus' => 0,
                'stats_modifiers' => ['int' => 3, 'wis' => 1],
                'value' => 200,
                'max_durability' => 60
            ],

            // Armor
            [
                'name' => 'Leather Armor',
                'description' => 'Basic leather armor providing modest protection.',
                'type' => 'armor',
                'subtype' => 'chest',
                'rarity' => 'common',
                'level_requirement' => 1,
                'ac_bonus' => 2,
                'stats_modifiers' => ['dex' => 1],
                'value' => 40,
                'max_durability' => 120
            ],
            [
                'name' => 'Steel Helmet',
                'description' => 'A well-forged steel helmet.',
                'type' => 'armor',
                'subtype' => 'helmet',
                'rarity' => 'common',
                'level_requirement' => 2,
                'ac_bonus' => 1,
                'value' => 30,
                'max_durability' => 100
            ],
            [
                'name' => 'Dragon Scale Boots',
                'description' => 'Boots made from genuine dragon scales.',
                'type' => 'armor',
                'subtype' => 'boots',
                'rarity' => 'epic',
                'level_requirement' => 8,
                'ac_bonus' => 2,
                'stats_modifiers' => ['str' => 2, 'con' => 2],
                'value' => 500,
                'max_durability' => 150
            ],

            // Accessories
            [
                'name' => 'Ring of Strength',
                'description' => 'A magical ring that enhances physical power.',
                'type' => 'accessory',
                'subtype' => 'ring',
                'rarity' => 'uncommon',
                'level_requirement' => 3,
                'stats_modifiers' => ['str' => 2],
                'value' => 100,
                'max_durability' => 50
            ],
            [
                'name' => 'Amulet of Wisdom',
                'description' => 'An ancient amulet that sharpens the mind.',
                'type' => 'accessory',
                'subtype' => 'necklace',
                'rarity' => 'rare',
                'level_requirement' => 4,
                'stats_modifiers' => ['wis' => 3, 'int' => 1],
                'value' => 180,
                'max_durability' => 40
            ],

            // Consumables
            [
                'name' => 'Health Potion',
                'description' => 'Restores health when consumed.',
                'type' => 'consumable',
                'subtype' => 'potion',
                'rarity' => 'common',
                'level_requirement' => 1,
                'value' => 25,
                'max_durability' => 1
            ],
            [
                'name' => 'Mana Potion',
                'description' => 'Restores magical energy.',
                'type' => 'consumable',
                'subtype' => 'potion',
                'rarity' => 'common',
                'level_requirement' => 1,
                'value' => 30,
                'max_durability' => 1
            ],

            // Crafting Materials
            [
                'name' => 'Iron Ore',
                'description' => 'Raw iron ore suitable for smithing.',
                'type' => 'crafting_material',
                'subtype' => 'ore',
                'rarity' => 'common',
                'level_requirement' => 0,
                'value' => 5,
                'max_durability' => 1
            ],
            [
                'name' => 'Dragon Bone',
                'description' => 'Extremely rare dragon bone for crafting.',
                'type' => 'crafting_material',
                'subtype' => 'bone',
                'rarity' => 'legendary',
                'level_requirement' => 0,
                'value' => 1000,
                'max_durability' => 1
            ],
        ];

        foreach ($items as $itemData) {
            Item::create($itemData);
        }

        // Add some items to the first player's inventory for testing
        $player = Player::first();
        if ($player) {
            // Add variety of items to test inventory
            $itemsToAdd = [
                ['name' => 'Iron Sword', 'quantity' => 1],
                ['name' => 'Health Potion', 'quantity' => 5],
                ['name' => 'Leather Armor', 'quantity' => 1],
                ['name' => 'Ring of Strength', 'quantity' => 1],
                ['name' => 'Iron Ore', 'quantity' => 10],
                ['name' => 'Elven Bow', 'quantity' => 1],
                ['name' => 'Mana Potion', 'quantity' => 3],
            ];

            foreach ($itemsToAdd as $itemToAdd) {
                $item = Item::where('name', $itemToAdd['name'])->first();
                if ($item) {
                    $player->addItemToInventory($item, $itemToAdd['quantity']);
                }
            }
        }
    }
}
