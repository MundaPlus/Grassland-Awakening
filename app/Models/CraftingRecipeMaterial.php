<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CraftingRecipeMaterial extends Model
{
    protected $fillable = [
        'recipe_id',
        'material_item_id',
        'quantity_required',
        'is_consumed'
    ];

    protected $casts = [
        'is_consumed' => 'boolean'
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(CraftingRecipe::class, 'recipe_id');
    }

    public function materialItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'material_item_id');
    }
}
