<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Item;

class ConsumableItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $consumables = [
            // Healing Items
            [
                'name' => 'Minor Healing Potion',
                'description' => 'A small vial containing healing essence. Restores a modest amount of health.',
                'type' => 'consumable',
                'subtype' => 'potion',
                'rarity' => 'common',
                'base_value' => 25,
                'is_stackable' => true,
                'max_stack_size' => 99,
                'is_consumable' => true,
                'stats_modifiers' => json_encode(['heal' => 15]),
            ],
            [
                'name' => 'Healing Potion',
                'description' => 'A standard healing potion that restores significant health.',
                'type' => 'consumable',
                'subtype' => 'potion',
                'rarity' => 'uncommon',
                'base_value' => 50,
                'is_stackable' => true,
                'max_stack_size' => 99,
                'is_consumable' => true,
                'stats_modifiers' => json_encode(['heal' => 30]),
            ],
            [
                'name' => 'Greater Healing Potion',
                'description' => 'A powerful healing potion that restores substantial health.',
                'type' => 'consumable',
                'subtype' => 'potion',
                'rarity' => 'rare',
                'base_value' => 100,
                'is_stackable' => true,
                'max_stack_size' => 99,
                'is_consumable' => true,
                'stats_modifiers' => json_encode(['heal' => 50]),
            ],

            // Combat Items
            [
                'name' => 'Throwing Knife',
                'description' => 'A perfectly balanced knife designed for throwing. Deals moderate damage to enemies.',
                'type' => 'consumable',
                'subtype' => 'throwable',
                'rarity' => 'common',
                'base_value' => 15,
                'is_stackable' => true,
                'max_stack_size' => 50,
                'is_consumable' => true,
                'damage_dice' => '2d4',
                'damage_bonus' => 1,
                'stats_modifiers' => json_encode(['damage_type' => 'ranged']),
            ],
            [
                'name' => 'Poison Dart',
                'description' => 'A dart coated with weak poison. Deals damage and may cause poisoning.',
                'type' => 'consumable',
                'subtype' => 'throwable',
                'rarity' => 'uncommon',
                'base_value' => 20,
                'is_stackable' => true,
                'max_stack_size' => 50,
                'is_consumable' => true,
                'damage_dice' => '1d4',
                'damage_bonus' => 2,
                'stats_modifiers' => json_encode(['damage_type' => 'ranged', 'poison' => true]),
            ],
            [
                'name' => 'Explosive Bomb',
                'description' => 'A volatile explosive device that deals significant area damage to all enemies.',
                'type' => 'consumable',
                'subtype' => 'explosive',
                'rarity' => 'rare',
                'base_value' => 75,
                'is_stackable' => true,
                'max_stack_size' => 20,
                'is_consumable' => true,
                'damage_dice' => '3d6',
                'damage_bonus' => 2,
                'stats_modifiers' => json_encode(['damage_type' => 'area', 'explosive' => true]),
            ],
            [
                'name' => 'Flash Bomb',
                'description' => 'A blinding explosive that stuns all enemies for one turn.',
                'type' => 'consumable',
                'subtype' => 'explosive',
                'rarity' => 'uncommon',
                'base_value' => 40,
                'is_stackable' => true,
                'max_stack_size' => 20,
                'is_consumable' => true,
                'stats_modifiers' => json_encode(['effect' => 'stun', 'duration' => 1, 'range' => 'area']),
            ],
            [
                'name' => 'Smoke Bomb',
                'description' => 'Creates a cloud of smoke, allowing you to escape from combat.',
                'type' => 'consumable',
                'subtype' => 'utility',
                'rarity' => 'common',
                'base_value' => 30,
                'is_stackable' => true,
                'max_stack_size' => 10,
                'is_consumable' => true,
                'stats_modifiers' => json_encode(['effect' => 'escape', 'success_rate' => 80]),
            ],

            // Buff Items
            [
                'name' => 'Strength Tonic',
                'description' => 'A bitter tonic that temporarily increases physical power.',
                'type' => 'consumable',
                'subtype' => 'tonic',
                'rarity' => 'uncommon',
                'base_value' => 35,
                'is_stackable' => true,
                'max_stack_size' => 20,
                'is_consumable' => true,
                'stats_modifiers' => json_encode(['buff_str' => 3, 'duration' => 5]),
            ],
            [
                'name' => 'Agility Elixir',
                'description' => 'A light, effervescent liquid that enhances speed and reflexes.',
                'type' => 'consumable',
                'subtype' => 'elixir',
                'rarity' => 'uncommon',
                'base_value' => 35,
                'is_stackable' => true,
                'max_stack_size' => 20,
                'is_consumable' => true,
                'stats_modifiers' => json_encode(['buff_dex' => 3, 'duration' => 5]),
            ],
            [
                'name' => 'Fortitude Draught',
                'description' => 'A thick, hearty drink that bolsters constitution and endurance.',
                'type' => 'consumable',
                'subtype' => 'draught',
                'rarity' => 'uncommon',
                'base_value' => 35,
                'is_stackable' => true,
                'max_stack_size' => 20,
                'is_consumable' => true,
                'stats_modifiers' => json_encode(['buff_con' => 3, 'duration' => 5]),
            ],

            // Special Items
            [
                'name' => 'Lucky Charm',
                'description' => 'A small trinket that brings good fortune. Improves critical hit chance temporarily.',
                'type' => 'consumable',
                'subtype' => 'charm',
                'rarity' => 'rare',
                'base_value' => 60,
                'is_stackable' => true,
                'max_stack_size' => 10,
                'is_consumable' => true,
                'stats_modifiers' => json_encode(['critical_chance' => 15, 'duration' => 3]),
            ],
            [
                'name' => 'Antidote',
                'description' => 'A bitter remedy that cures poison and disease effects.',
                'type' => 'consumable',
                'subtype' => 'medicine',
                'rarity' => 'common',
                'base_value' => 20,
                'is_stackable' => true,
                'max_stack_size' => 50,
                'is_consumable' => true,
                'stats_modifiers' => json_encode(['heal' => 5, 'cure' => ['poison', 'disease']]),
            ],
        ];

        foreach ($consumables as $consumable) {
            // Check if item already exists by name to avoid duplicates
            $existingItem = Item::where('name', $consumable['name'])->first();
            
            if (!$existingItem) {
                Item::create($consumable);
            } else {
                // Update existing item with new data
                $existingItem->update($consumable);
            }
        }
    }
}