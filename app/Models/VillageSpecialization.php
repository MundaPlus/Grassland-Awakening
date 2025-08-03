<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VillageSpecialization extends Model
{
    protected $fillable = [
        'player_id',
        'specialization_type',
        'level',
        'bonuses_json'
    ];

    protected $casts = [
        'level' => 'integer',
        'bonuses_json' => 'array'
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function getBonuses(): array
    {
        return $this->bonuses_json ?? [];
    }

    public function getBonus(string $bonusType): float
    {
        $bonuses = $this->getBonuses();
        return $bonuses[$bonusType] ?? 1.0;
    }

    public function canUpgrade(): bool
    {
        return $this->level < 10; // Max specialization level
    }

    public function getUpgradeCost(): int
    {
        return $this->level * 500; // Base cost scaling
    }

    public function getSpecializationName(): string
    {
        $names = [
            'military_outpost' => 'Military Outpost',
            'trading_hub' => 'Trading Hub',
            'magical_academy' => 'Magical Academy'
        ];
        
        return $names[$this->specialization_type] ?? 'Unknown Specialization';
    }

    public function getSpecializationDescription(): string
    {
        $descriptions = [
            'military_outpost' => 'A fortified settlement focused on combat training and defense.',
            'trading_hub' => 'A bustling center of commerce and trade.',
            'magical_academy' => 'A center of learning and magical research.'
        ];
        
        return $descriptions[$this->specialization_type] ?? 'No description available.';
    }

    public function getUnlockedFeatures(): array
    {
        $features = [
            'military_outpost' => [
                1 => ['advanced_combat_training'],
                3 => ['weapon_enchantment'],
                5 => ['fortress_upgrades'],
                7 => ['elite_guard_units'],
                10 => ['legendary_warriors']
            ],
            'trading_hub' => [
                1 => ['international_trade'],
                3 => ['merchant_guild'],
                5 => ['auction_house'],
                7 => ['trade_monopolies'],
                10 => ['economic_empire']
            ],
            'magical_academy' => [
                1 => ['spell_creation'],
                3 => ['magical_research'],
                5 => ['teleportation_circle'],
                7 => ['arcane_mastery'],
                10 => ['reality_manipulation']
            ]
        ];
        
        $unlockedFeatures = [];
        $levelFeatures = $features[$this->specialization_type] ?? [];
        
        foreach ($levelFeatures as $requiredLevel => $levelUnlocks) {
            if ($this->level >= $requiredLevel) {
                $unlockedFeatures = array_merge($unlockedFeatures, $levelUnlocks);
            }
        }
        
        return $unlockedFeatures;
    }
}
