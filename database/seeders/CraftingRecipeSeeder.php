<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CraftingRecipe;
use App\Models\CraftingRecipeMaterial;
use App\Models\Item;

class CraftingRecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, add some additional crafting materials if they don't exist
        $craftingMaterials = [
            [
                'name' => 'Leather Strip',
                'description' => 'Strips of leather for crafting.',
                'type' => 'crafting_material',
                'subtype' => 'leather',
                'rarity' => 'common',
                'level_requirement' => 0,
                'value' => 2,
                'max_durability' => 1,
                'is_stackable' => true
            ],
            [
                'name' => 'Steel Ingot',
                'description' => 'Refined steel ingot for advanced crafting.',
                'type' => 'crafting_material',
                'subtype' => 'ingot',
                'rarity' => 'uncommon',
                'level_requirement' => 0,
                'value' => 20,
                'max_durability' => 1,
                'is_stackable' => true
            ],
            [
                'name' => 'Magic Crystal',
                'description' => 'A crystal infused with magical energy.',
                'type' => 'crafting_material',
                'subtype' => 'crystal',
                'rarity' => 'rare',
                'level_requirement' => 0,
                'value' => 50,
                'max_durability' => 1,
                'is_stackable' => true
            ],
            [
                'name' => 'Crafting Hammer',
                'description' => 'A sturdy hammer for crafting weapons and armor.',
                'type' => 'crafting_material',
                'subtype' => 'tool',
                'rarity' => 'common',
                'level_requirement' => 0,
                'value' => 25,
                'max_durability' => 100,
                'is_stackable' => false
            ]
        ];

        foreach ($craftingMaterials as $materialData) {
            if (!Item::where('name', $materialData['name'])->exists()) {
                Item::create($materialData);
            }
        }

        // Define crafting recipes
        $recipes = [
            // Basic Sword Recipe
            [
                'name' => 'Craft Simple Sword',
                'description' => 'Craft a basic sword from iron ore.',
                'result_item' => 'Simple Sword',
                'result_quantity' => 1,
                'category' => 'weapon',
                'difficulty' => 'basic',
                'crafting_time' => 120,
                'gold_cost' => 10,
                'experience_reward' => 25,
                'stat_requirements' => ['str' => 3],
                'recipe_discovery' => ['method' => 'auto_learn', 'level' => 1],
                'materials' => [
                    ['item' => 'Iron Ore', 'quantity' => 3, 'is_consumed' => true],
                    ['item' => 'Crafting Hammer', 'quantity' => 1, 'is_consumed' => false]
                ]
            ],

            // Health Potion Recipe
            [
                'name' => 'Brew Health Potion',
                'description' => 'Brew a healing potion using herbs.',
                'result_item' => 'Health Potion',
                'result_quantity' => 2,
                'category' => 'consumable',
                'difficulty' => 'basic',
                'crafting_time' => 60,
                'gold_cost' => 5,
                'experience_reward' => 15,
                'stat_requirements' => ['int' => 2],
                'recipe_discovery' => ['method' => 'auto_learn', 'level' => 1],
                'materials' => [
                    ['item' => 'Iron Ore', 'quantity' => 1, 'is_consumed' => true] // Using iron ore as placeholder herb
                ]
            ],

            // Upgrade Iron Sword to Better Version
            [
                'name' => 'Reinforce Iron Sword',
                'description' => 'Upgrade an Iron Sword with steel to make it stronger.',
                'result_item' => 'Iron Sword', // For now, same item but could be different
                'result_quantity' => 1,
                'category' => 'weapon',
                'difficulty' => 'intermediate',
                'crafting_time' => 180,
                'gold_cost' => 25,
                'experience_reward' => 40,
                'stat_requirements' => ['str' => 5, 'int' => 3],
                'recipe_discovery' => ['method' => 'adventure', 'location' => 'smithy'],
                'is_upgrade_recipe' => true,
                'upgrade_base_item' => 'Simple Sword',
                'materials' => [
                    ['item' => 'Steel Ingot', 'quantity' => 2, 'is_consumed' => true],
                    ['item' => 'Crafting Hammer', 'quantity' => 1, 'is_consumed' => false]
                ]
            ],

            // Leather Armor Recipe
            [
                'name' => 'Craft Leather Armor',
                'description' => 'Create basic leather armor for protection.',
                'result_item' => 'Leather Armor',
                'result_quantity' => 1,
                'category' => 'armor',
                'difficulty' => 'basic',
                'crafting_time' => 150,
                'gold_cost' => 15,
                'experience_reward' => 30,
                'stat_requirements' => ['dex' => 3, 'int' => 2],
                'recipe_discovery' => ['method' => 'auto_learn', 'level' => 2],
                'materials' => [
                    ['item' => 'Leather Strip', 'quantity' => 5, 'is_consumed' => true]
                ]
            ],

            // Advanced Magic Staff
            [
                'name' => 'Craft Wizard Staff',
                'description' => 'Create a powerful magical staff.',
                'result_item' => 'Wizard Staff',
                'result_quantity' => 1,
                'category' => 'weapon',
                'difficulty' => 'advanced',
                'crafting_time' => 300,
                'gold_cost' => 100,
                'experience_reward' => 75,
                'stat_requirements' => ['int' => 8, 'wis' => 5],
                'recipe_discovery' => ['method' => 'npc', 'source' => 'wizard_mentor'],
                'materials' => [
                    ['item' => 'Magic Crystal', 'quantity' => 3, 'is_consumed' => true],
                    ['item' => 'Steel Ingot', 'quantity' => 1, 'is_consumed' => true],
                    ['item' => 'Dragon Bone', 'quantity' => 1, 'is_consumed' => true]
                ]
            ]
        ];

        // Create the recipes
        foreach ($recipes as $recipeData) {
            // Find the result item
            $resultItem = Item::where('name', $recipeData['result_item'])->first();
            if (!$resultItem) {
                echo "Warning: Result item '{$recipeData['result_item']}' not found. Skipping recipe '{$recipeData['name']}'.\n";
                continue;
            }

            // Create the recipe
            $recipe = CraftingRecipe::create([
                'name' => $recipeData['name'],
                'description' => $recipeData['description'],
                'result_item_id' => $resultItem->id,
                'result_quantity' => $recipeData['result_quantity'],
                'category' => $recipeData['category'],
                'difficulty' => $recipeData['difficulty'],
                'crafting_time' => $recipeData['crafting_time'],
                'gold_cost' => $recipeData['gold_cost'],
                'experience_reward' => $recipeData['experience_reward'],
                'stat_requirements' => $recipeData['stat_requirements'],
                'recipe_discovery' => $recipeData['recipe_discovery'],
                'is_upgrade_recipe' => $recipeData['is_upgrade_recipe'] ?? false,
                'upgrade_base_item_id' => isset($recipeData['upgrade_base_item']) 
                    ? Item::where('name', $recipeData['upgrade_base_item'])->first()?->id 
                    : null
            ]);

            // Add materials to the recipe
            foreach ($recipeData['materials'] as $materialData) {
                $materialItem = Item::where('name', $materialData['item'])->first();
                if ($materialItem) {
                    CraftingRecipeMaterial::create([
                        'recipe_id' => $recipe->id,
                        'material_item_id' => $materialItem->id,
                        'quantity_required' => $materialData['quantity'],
                        'is_consumed' => $materialData['is_consumed']
                    ]);
                } else {
                    echo "Warning: Material item '{$materialData['item']}' not found for recipe '{$recipeData['name']}'.\n";
                }
            }
        }

        echo "Created " . count($recipes) . " crafting recipes.\n";
    }
}