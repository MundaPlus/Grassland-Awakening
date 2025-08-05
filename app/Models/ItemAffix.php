<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemAffix extends Model
{
    protected $fillable = [
        'name',
        'type',
        'applicable_types',
        'stat_modifiers',
        'rarity_weight',
        'level_requirement',
        'description'
    ];

    protected $casts = [
        'applicable_types' => 'array',
        'stat_modifiers' => 'array',
        'rarity_weight' => 'integer',
        'level_requirement' => 'integer'
    ];

    public function isApplicableToItem(Item $item): bool
    {
        if (!$this->applicable_types) {
            return true; // No restrictions
        }

        return in_array($item->type, $this->applicable_types) || 
               in_array($item->subtype, $this->applicable_types);
    }

    public static function getRandomPrefix(Item $item, int $playerLevel = 1): ?self
    {
        return self::where('type', 'prefix')
            ->where('level_requirement', '<=', $playerLevel)
            ->get()
            ->filter(function ($affix) use ($item) {
                return $affix->isApplicableToItem($item);
            })
            ->randomWeighted('rarity_weight');
    }

    public static function getRandomSuffix(Item $item, int $playerLevel = 1): ?self
    {
        return self::where('type', 'suffix')
            ->where('level_requirement', '<=', $playerLevel)
            ->get()
            ->filter(function ($affix) use ($item) {
                return $affix->isApplicableToItem($item);
            })
            ->randomWeighted('rarity_weight');
    }

    public function getTotalStatBonus(): int
    {
        if (!$this->stat_modifiers) {
            return 0;
        }

        return array_sum(array_map('abs', $this->stat_modifiers));
    }
}