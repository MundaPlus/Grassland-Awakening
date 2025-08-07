<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CraftingRecipe extends Model
{
    protected $fillable = [
        'name',
        'description',
        'result_item_id',
        'result_quantity',
        'category',
        'difficulty',
        'crafting_time',
        'gold_cost',
        'experience_reward',
        'stat_requirements',
        'recipe_discovery',
        'is_upgrade_recipe',
        'upgrade_base_item_id'
    ];

    protected $casts = [
        'stat_requirements' => 'array',
        'recipe_discovery' => 'array',
        'is_upgrade_recipe' => 'boolean'
    ];

    public function resultItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'result_item_id');
    }

    public function upgradeBaseItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'upgrade_base_item_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(CraftingRecipeMaterial::class, 'recipe_id');
    }

    public function knownByPlayers(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'player_known_recipes', 'recipe_id', 'player_id')
                    ->withPivot(['learned_at', 'discovery_method', 'times_crafted'])
                    ->withTimestamps();
    }

    public function canPlayerCraft(Player $player): bool
    {
        if ($this->stat_requirements) {
            foreach ($this->stat_requirements as $stat => $required) {
                if ($player->getTotalStat($stat) < $required) {
                    return false;
                }
            }
        }

        return true;
    }

    public function hasRequiredMaterials(Player $player): bool
    {
        foreach ($this->materials as $material) {
            // Check old inventory system
            $oldInventoryQuantity = $player->inventory()
                ->where('item_id', $material->material_item_id)
                ->sum('quantity');
            
            // Check new PlayerItem system (unequipped items)
            $newInventoryQuantity = $player->playerItems()
                ->where('item_id', $material->material_item_id)
                ->where('is_equipped', false)
                ->sum('quantity');
            
            $hasQuantity = $oldInventoryQuantity + $newInventoryQuantity;
            
            if ($hasQuantity < $material->quantity_required) {
                return false;
            }
        }

        return true;
    }

    public function getMissingMaterials(Player $player): array
    {
        $missing = [];
        
        foreach ($this->materials as $material) {
            // Check old inventory system
            $oldInventoryQuantity = $player->inventory()
                ->where('item_id', $material->material_item_id)
                ->sum('quantity');
            
            // Check new PlayerItem system (unequipped items)
            $newInventoryQuantity = $player->playerItems()
                ->where('item_id', $material->material_item_id)
                ->where('is_equipped', false)
                ->sum('quantity');
            
            $hasQuantity = $oldInventoryQuantity + $newInventoryQuantity;
            
            $needed = $material->quantity_required - $hasQuantity;
            if ($needed > 0) {
                $missing[] = [
                    'item' => $material->materialItem,
                    'needed' => $needed,
                    'has' => $hasQuantity
                ];
            }
        }

        return $missing;
    }
}
