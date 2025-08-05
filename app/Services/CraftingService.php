<?php

namespace App\Services;

use App\Models\Player;
use App\Models\CraftingRecipe;
use App\Models\Item;
use App\Models\PlayerItem;
use App\Models\ItemAffix;
use Exception;
use Illuminate\Support\Facades\DB;

class CraftingService
{
    public function craftItem(Player $player, CraftingRecipe $recipe): array
    {
        return DB::transaction(function () use ($player, $recipe) {
            
            if (!$player->knowsRecipe($recipe)) {
                throw new Exception('Player does not know this recipe');
            }

            if (!$recipe->canPlayerCraft($player)) {
                throw new Exception('Player does not meet the stat requirements for this recipe');
            }

            if (!$recipe->hasRequiredMaterials($player)) {
                $missing = $recipe->getMissingMaterials($player);
                $missingNames = array_map(fn($m) => $m['item']->name, $missing);
                throw new Exception('Missing materials: ' . implode(', ', $missingNames));
            }

            if ($player->persistent_currency < $recipe->gold_cost) {
                throw new Exception('Not enough gold to craft this item');
            }

            // Consume materials
            foreach ($recipe->materials as $material) {
                if ($material->is_consumed) {
                    $this->consumeMaterial($player, $material->materialItem, $material->quantity_required);
                }
            }

            // Deduct gold cost
            if ($recipe->gold_cost > 0) {
                $player->persistent_currency -= $recipe->gold_cost;
                $player->save();
            }

            // Create the result item(s) with potential affixes
            $resultItem = $this->createAffixedPlayerItem(
                $player,
                $recipe->resultItem, 
                $recipe->result_quantity,
                $player->level
            );

            // Award experience
            if ($recipe->experience_reward > 0) {
                $player->addExperience($recipe->experience_reward);
            }

            // Increment times crafted
            $player->knownRecipes()->updateExistingPivot($recipe->id, [
                'times_crafted' => DB::raw('times_crafted + 1')
            ]);

            // Trigger crafting achievements
            $this->triggerCraftingAchievements($player, $recipe);

            return [
                'success' => true,
                'item' => $resultItem,
                'quantity' => $recipe->result_quantity,
                'experience_gained' => $recipe->experience_reward,
                'gold_spent' => $recipe->gold_cost
            ];
        });
    }

    private function triggerCraftingAchievements(Player $player, CraftingRecipe $recipe): void
    {
        $achievementService = app(\App\Services\AchievementService::class);
        
        // General item crafted achievement
        $achievementService->processGameEvent($player, 'item_crafted');

        // Specific type crafting achievements based on result item type
        $resultItem = $recipe->resultItem;
        if ($resultItem) {
            $itemType = $resultItem->type;
            
            // Map item types to achievement events
            if ($itemType === 'weapon') {
                $achievementService->processGameEvent($player, 'weapon_crafted');
            } elseif ($itemType === 'armor') {
                $achievementService->processGameEvent($player, 'armor_crafted');
            } elseif ($itemType === 'consumable') {
                // Check if it's a potion based on name or subtype
                $itemName = strtolower($resultItem->name);
                if (str_contains($itemName, 'potion') || $resultItem->subtype === 'potion') {
                    $achievementService->processGameEvent($player, 'potion_crafted');
                }
            }
        }
    }

    public function upgradeItem(Player $player, CraftingRecipe $upgradeRecipe, PlayerItem $baseItem): array
    {
        return DB::transaction(function () use ($player, $upgradeRecipe, $baseItem) {
            
            if (!$upgradeRecipe->is_upgrade_recipe) {
                throw new Exception('This is not an upgrade recipe');
            }

            if (!$player->knowsRecipe($upgradeRecipe)) {
                throw new Exception('Player does not know this upgrade recipe');
            }

            if ($baseItem->item->id !== $upgradeRecipe->upgrade_base_item_id) {
                throw new Exception('This item cannot be upgraded with this recipe');
            }

            if (!$upgradeRecipe->canPlayerCraft($player)) {
                throw new Exception('Player does not meet the stat requirements for this upgrade');
            }

            if (!$upgradeRecipe->hasRequiredMaterials($player)) {
                $missing = $upgradeRecipe->getMissingMaterials($player);
                $missingNames = array_map(fn($m) => $m['item']->name, $missing);
                throw new Exception('Missing materials: ' . implode(', ', $missingNames));
            }

            if ($player->persistent_currency < $upgradeRecipe->gold_cost) {
                throw new Exception('Not enough gold to upgrade this item');
            }

            // Consume the base item
            if ($baseItem->quantity > 1) {
                $baseItem->quantity -= 1;
                $baseItem->save();
            } else {
                $baseItem->delete();
            }

            // Consume materials
            foreach ($upgradeRecipe->materials as $material) {
                if ($material->is_consumed) {
                    $this->consumeMaterial($player, $material->materialItem, $material->quantity_required);
                }
            }

            // Deduct gold cost
            if ($upgradeRecipe->gold_cost > 0) {
                $player->persistent_currency -= $upgradeRecipe->gold_cost;
                $player->save();
            }

            // Create the upgraded item with potential affixes
            $upgradedItem = $this->createAffixedPlayerItem(
                $player,
                $upgradeRecipe->resultItem, 
                $upgradeRecipe->result_quantity,
                $player->level
            );

            // Award experience
            if ($upgradeRecipe->experience_reward > 0) {
                $player->addExperience($upgradeRecipe->experience_reward);
            }

            // Increment times crafted
            $player->knownRecipes()->updateExistingPivot($upgradeRecipe->id, [
                'times_crafted' => DB::raw('times_crafted + 1')
            ]);

            return [
                'success' => true,
                'upgraded_item' => $upgradedItem,
                'quantity' => $upgradeRecipe->result_quantity,
                'experience_gained' => $upgradeRecipe->experience_reward,
                'gold_spent' => $upgradeRecipe->gold_cost
            ];
        });
    }

    private function consumeMaterial(Player $player, Item $material, int $quantity): void
    {
        // Try new PlayerItem system first
        $playerItems = $player->playerItems()
            ->where('item_id', $material->id)
            ->where('is_equipped', false)
            ->orderBy('created_at', 'asc')
            ->get();

        $remainingToConsume = $quantity;

        foreach ($playerItems as $playerItem) {
            if ($remainingToConsume <= 0) break;

            if ($playerItem->quantity >= $remainingToConsume) {
                // This item has enough quantity
                $playerItem->quantity -= $remainingToConsume;
                if ($playerItem->quantity <= 0) {
                    $playerItem->delete();
                } else {
                    $playerItem->save();
                }
                $remainingToConsume = 0;
            } else {
                // Consume all of this item and continue
                $remainingToConsume -= $playerItem->quantity;
                $playerItem->delete();
            }
        }

        // Fallback to old Inventory system if needed
        if ($remainingToConsume > 0) {
            $inventoryItems = $player->inventory()
                ->where('item_id', $material->id)
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($inventoryItems as $inventoryItem) {
                if ($remainingToConsume <= 0) break;

                if ($inventoryItem->quantity >= $remainingToConsume) {
                    $inventoryItem->quantity -= $remainingToConsume;
                    if ($inventoryItem->quantity <= 0) {
                        $inventoryItem->delete();
                    } else {
                        $inventoryItem->save();
                    }
                    $remainingToConsume = 0;
                } else {
                    $remainingToConsume -= $inventoryItem->quantity;
                    $inventoryItem->delete();
                }
            }
        }

        if ($remainingToConsume > 0) {
            throw new Exception("Not enough {$material->name} to consume");
        }
    }

    public function discoverRecipe(Player $player, CraftingRecipe $recipe, string $discoveryMethod = 'adventure'): bool
    {
        if ($player->knowsRecipe($recipe)) {
            return false; // Already known
        }

        return $player->learnRecipe($recipe, $discoveryMethod);
    }

    public function getAvailableRecipesForPlayer(Player $player, string $category = null): array
    {
        $recipes = $player->getAvailableRecipesByCategory($category);
        
        return $recipes->map(function ($recipe) use ($player) {
            return [
                'recipe' => $recipe,
                'can_craft' => $player->canCraftRecipe($recipe),
                'missing_materials' => $recipe->getMissingMaterials($player),
                'stat_requirements_met' => $recipe->canPlayerCraft($player),
                'has_gold' => $player->persistent_currency >= $recipe->gold_cost,
                'times_crafted' => $recipe->pivot->times_crafted ?? 0
            ];
        })->toArray();
    }

    public function getUpgradeRecipesForItem(Player $player, Item $item): array
    {
        $upgradeRecipes = $player->knownRecipes()
            ->where('is_upgrade_recipe', true)
            ->where('upgrade_base_item_id', $item->id)
            ->with(['resultItem', 'materials.materialItem'])
            ->get();

        return $upgradeRecipes->map(function ($recipe) use ($player) {
            return [
                'recipe' => $recipe,
                'can_craft' => $player->canCraftRecipe($recipe),
                'missing_materials' => $recipe->getMissingMaterials($player),
                'stat_requirements_met' => $recipe->canPlayerCraft($player),
                'has_gold' => $player->persistent_currency >= $recipe->gold_cost,
                'times_crafted' => $recipe->pivot->times_crafted ?? 0
            ];
        })->toArray();
    }

    /**
     * Create a PlayerItem with potential affixes for exciting names and bonuses
     */
    private function createAffixedPlayerItem(Player $player, Item $baseItem, int $quantity, int $playerLevel): PlayerItem
    {
        // For stackable items, don't apply affixes to maintain consistency
        if ($baseItem->is_stackable) {
            return $player->addItemToPlayerInventory($baseItem, $quantity);
        }

        // Calculate affix chance based on item rarity and player level
        $affixChance = $this->calculateAffixChance($baseItem, $playerLevel);
        $shouldHavePrefix = rand(1, 100) <= $affixChance;
        $shouldHaveSuffix = rand(1, 100) <= $affixChance;

        // Generate affixes if applicable
        $prefix = $shouldHavePrefix ? ItemAffix::getRandomPrefix($baseItem, $playerLevel) : null;
        $suffix = $shouldHaveSuffix ? ItemAffix::getRandomSuffix($baseItem, $playerLevel) : null;

        // Create the base PlayerItem
        $playerItem = $player->addItemToPlayerInventory($baseItem, $quantity);

        // Apply affixes if we have them
        if ($prefix || $suffix) {
            $this->applyAffixesToPlayerItem($playerItem, $prefix, $suffix);
        }

        return $playerItem;
    }

    /**
     * Calculate the chance for affixes based on item and player level
     */
    private function calculateAffixChance(Item $item, int $playerLevel): int
    {
        $baseChance = 15; // 15% base chance for affixes
        
        // Increase chance for higher level items
        if ($item->level_requirement) {
            $baseChance += min($item->level_requirement * 2, 30); // Up to +30%
        }

        // Increase chance for higher player level
        $baseChance += min($playerLevel * 2, 25); // Up to +25%

        // Equipment gets higher chance than tools/consumables
        if (in_array($item->type, ['weapon', 'armor', 'accessory'])) {
            $baseChance += 20;
        }

        return min($baseChance, 75); // Cap at 75%
    }

    /**
     * Apply affixes to a PlayerItem, modifying its properties
     */
    private function applyAffixesToPlayerItem(PlayerItem $playerItem, ?ItemAffix $prefix, ?ItemAffix $suffix): void
    {
        $customName = '';
        $totalStatModifiers = [];

        // Build the custom name
        if ($prefix) {
            $customName .= $prefix->name . ' ';
        }
        
        $customName .= $playerItem->item->name;
        
        if ($suffix) {
            $customName .= ' ' . $suffix->name;
        }

        // Combine stat modifiers from both affixes
        if ($prefix && $prefix->stat_modifiers) {
            foreach ($prefix->stat_modifiers as $stat => $value) {
                $totalStatModifiers[$stat] = ($totalStatModifiers[$stat] ?? 0) + $value;
            }
        }

        if ($suffix && $suffix->stat_modifiers) {
            foreach ($suffix->stat_modifiers as $stat => $value) {
                $totalStatModifiers[$stat] = ($totalStatModifiers[$stat] ?? 0) + $value;
            }
        }

        // Update the PlayerItem with affixed properties
        $playerItem->update([
            'custom_name' => $customName,
            'affix_stat_modifiers' => $totalStatModifiers,
            'prefix_affix_id' => $prefix?->id,
            'suffix_affix_id' => $suffix?->id
        ]);
    }
}