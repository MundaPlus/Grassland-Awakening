<?php

namespace App\Services;

use App\Models\Player;
use App\Models\Adventure;
use App\Models\CraftingRecipe;
use App\Models\PlayerKnownRecipe;

class RecipeDiscoveryService
{
    /**
     * Discover recipes based on completed adventure
     */
    public function discoverRecipesFromAdventure(Player $player, Adventure $adventure): array
    {
        $discoveredRecipes = [];
        
        // Get adventure level and difficulty for recipe discovery logic
        $adventureLevel = $adventure->getCurrentAdventureLevel();
        $difficulty = $adventure->difficulty;
        $road = $adventure->road;
        
        // Discovery chance based on difficulty
        $baseDiscoveryChance = match($difficulty) {
            'easy' => 0.15,
            'normal' => 0.25,
            'hard' => 0.35,
            'nightmare' => 0.50,
            default => 0.25
        };
        
        // Bonus discovery chance for higher adventure levels
        $levelBonus = min(0.20, $adventureLevel * 0.02);
        $totalDiscoveryChance = $baseDiscoveryChance + $levelBonus;
        
        // Get available recipes for discovery
        $availableRecipes = $this->getAvailableRecipesForDiscovery($player, $road, $adventureLevel);
        
        foreach ($availableRecipes as $recipe) {
            if (mt_rand(1, 100) / 100 <= $totalDiscoveryChance) {
                // Check if player already knows this recipe
                $alreadyKnown = PlayerKnownRecipe::where('player_id', $player->id)
                    ->where('recipe_id', $recipe->id)
                    ->exists();
                
                if (!$alreadyKnown) {
                    // Add recipe to player's known recipes
                    PlayerKnownRecipe::create([
                        'player_id' => $player->id,
                        'recipe_id' => $recipe->id,
                        'learned_at' => now(),
                        'discovery_method' => 'adventure_completion',
                        'times_crafted' => 0
                    ]);
                    
                    $discoveredRecipes[] = $recipe;
                }
            }
        }
        
        return $discoveredRecipes;
    }
    
    /**
     * Get recipes available for discovery based on road and level
     */
    private function getAvailableRecipesForDiscovery(Player $player, string $road, int $level): \Illuminate\Database\Eloquent\Collection
    {
        // Map roads to recipe themes
        $roadThemes = [
            'north' => ['ice', 'winter', 'cold'],
            'south' => ['desert', 'fire', 'heat'],
            'east' => ['forest', 'nature', 'wood'],
            'west' => ['mountain', 'earth', 'stone'],
            'forest_path' => ['forest', 'nature', 'wood'],
            'mountain_trail' => ['mountain', 'earth', 'stone'],
            'coastal_road' => ['water', 'sea', 'coastal'],
            'desert_route' => ['desert', 'fire', 'heat'],
            'river_crossing' => ['water', 'river', 'flow'],
            'ancient_highway' => ['ancient', 'relic', 'old']
        ];
        
        $themes = $roadThemes[$road] ?? ['common'];
        
        // Get recipes that match the road theme and are appropriate for player level
        $query = CraftingRecipe::where('required_level', '<=', $player->level + 2) // Allow slightly higher level recipes
            ->where('required_level', '>=', max(1, $level - 3)); // Not too low level
        
        // Filter by themes in recipe name or description
        $query->where(function($q) use ($themes) {
            foreach ($themes as $theme) {
                $q->orWhere('name', 'like', "%{$theme}%")
                  ->orWhere('description', 'like', "%{$theme}%");
            }
            // Always include some common recipes
            $q->orWhere('name', 'like', '%basic%')
              ->orWhere('name', 'like', '%simple%')
              ->orWhere('rarity', 'common');
        });
        
        return $query->limit(5)->get(); // Limit to prevent too many discoveries at once
    }
    
    /**
     * Discover recipes during adventure node completion (chance discovery)
     */
    public function tryDiscoverRecipeFromNode(Player $player, Adventure $adventure, string $nodeType, int $nodeLevel): ?CraftingRecipe
    {
        // Lower chance for node-based discovery
        $discoveryChance = match($nodeType) {
            'treasure' => 0.08,
            'event' => 0.05,
            'npc_encounter' => 0.06,
            'boss' => 0.20,
            default => 0.02
        };
        
        // Bonus for higher node levels
        $levelBonus = min(0.05, $nodeLevel * 0.005);
        $totalChance = $discoveryChance + $levelBonus;
        
        if (mt_rand(1, 100) / 100 <= $totalChance) {
            $availableRecipes = $this->getAvailableRecipesForDiscovery($player, $adventure->road, $nodeLevel);
            
            if ($availableRecipes->count() > 0) {
                $recipe = $availableRecipes->random();
                
                // Check if player already knows this recipe
                $alreadyKnown = PlayerKnownRecipe::where('player_id', $player->id)
                    ->where('recipe_id', $recipe->id)
                    ->exists();
                
                if (!$alreadyKnown) {
                    PlayerKnownRecipe::create([
                        'player_id' => $player->id,
                        'recipe_id' => $recipe->id,
                        'learned_at' => now(),
                        'discovery_method' => 'node_exploration',
                        'times_crafted' => 0
                    ]);
                    
                    return $recipe;
                }
            }
        }
        
        return null;
    }
}