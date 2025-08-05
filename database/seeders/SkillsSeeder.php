<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Skill;
use App\Models\SkillExperienceSource;

class SkillsSeeder extends Seeder
{
    public function run(): void
    {
        // PASSIVE SKILLS - Crafting
        $smithing = Skill::create([
            'name' => 'Smithing',
            'slug' => 'smithing',
            'description' => 'Craft better weapons and armor with higher success rates',
            'type' => 'passive',
            'category' => 'crafting',
            'icon' => '🔨',
            'max_level' => 100,
            'effects' => [
                'crafting_bonus' => ['base' => 5, 'per_level' => 2], // % bonus to crafting success
                'durability_bonus' => ['base' => 10, 'per_level' => 1], // % bonus to item durability
                'rare_chance' => ['base' => 1, 'per_level' => 0.5] // % chance for rare upgrade
            ]
        ]);

        $alchemy = Skill::create([
            'name' => 'Alchemy',
            'slug' => 'alchemy',
            'description' => 'Brew potions with enhanced effects and duration',
            'type' => 'passive',
            'category' => 'crafting',
            'icon' => '🧪',
            'max_level' => 100,
            'effects' => [
                'potion_potency' => ['base' => 10, 'per_level' => 1], // % stronger effects
                'duration_bonus' => ['base' => 20, 'per_level' => 1], // % longer duration
                'yield_bonus' => ['base' => 0, 'per_level' => 0.2] // % chance for extra potion
            ]
        ]);

        // PASSIVE SKILLS - Gathering
        $mining = Skill::create([
            'name' => 'Mining',
            'slug' => 'mining',
            'description' => 'Extract rare ores and gems from resource nodes',
            'type' => 'passive',
            'category' => 'gathering',
            'icon' => '⛏️',
            'max_level' => 100,
            'effects' => [
                'yield_bonus' => ['base' => 10, 'per_level' => 1], // % bonus resources
                'rare_find' => ['base' => 2, 'per_level' => 0.3], // % chance for rare materials
                'speed_bonus' => ['base' => 5, 'per_level' => 0.5] // % faster gathering
            ]
        ]);

        $herbalism = Skill::create([
            'name' => 'Herbalism',
            'slug' => 'herbalism',
            'description' => 'Gather rare herbs and identify magical plants',
            'type' => 'passive',
            'category' => 'gathering',
            'icon' => '🌿',
            'max_level' => 100,
            'effects' => [
                'herb_yield' => ['base' => 15, 'per_level' => 1.2],
                'rare_herb' => ['base' => 3, 'per_level' => 0.4],
                'potion_knowledge' => ['base' => 0, 'per_level' => 1] // Unlocks recipes
            ]
        ]);

        // PASSIVE SKILLS - Combat Support
        $toughness = Skill::create([
            'name' => 'Toughness',
            'slug' => 'toughness',
            'description' => 'Increase health and damage resistance',
            'type' => 'passive',
            'category' => 'combat',
            'icon' => '🛡️',
            'max_level' => 50,
            'effects' => [
                'health_bonus' => ['base' => 10, 'per_level' => 5], // Flat HP bonus
                'damage_reduction' => ['base' => 1, 'per_level' => 0.5], // % damage reduction
                'healing_bonus' => ['base' => 5, 'per_level' => 1] // % bonus to healing received
            ]
        ]);

        $athletics = Skill::create([
            'name' => 'Athletics',
            'slug' => 'athletics',
            'description' => 'Improve movement speed and stamina efficiency',
            'type' => 'passive',
            'category' => 'survival',
            'icon' => '🏃',
            'max_level' => 50,
            'effects' => [
                'stamina_bonus' => ['base' => 10, 'per_level' => 2],
                'stamina_regen' => ['base' => 5, 'per_level' => 1], // % faster regen
                'movement_speed' => ['base' => 2, 'per_level' => 0.5] // % movement bonus
            ]
        ]);

        // ACTIVE SKILLS - Sword Combat
        $powerStrike = Skill::create([
            'name' => 'Power Strike',
            'slug' => 'power-strike',
            'description' => 'A devastating attack that deals massive damage',
            'type' => 'active',
            'category' => 'combat',
            'icon' => '⚔️',
            'max_level' => 20,
            'weapon_types' => ['sword', 'axe', 'mace'],
            'base_cost' => 15,
            'cooldown' => 6,
            'effects' => [
                'damage_multiplier' => ['base' => 1.5, 'per_level' => 0.1], // 150% damage at level 1
                'accuracy_bonus' => ['base' => 10, 'per_level' => 2] // % accuracy bonus
            ]
        ]);

        $whirlwind = Skill::create([
            'name' => 'Whirlwind',
            'slug' => 'whirlwind',
            'description' => 'Attack all nearby enemies with reduced damage',
            'type' => 'active',
            'category' => 'combat',
            'icon' => '🌪️',
            'max_level' => 15,
            'weapon_types' => ['sword', 'axe'],
            'base_cost' => 20,
            'cooldown' => 10,
            'effects' => [
                'damage_multiplier' => ['base' => 0.7, 'per_level' => 0.05], // 70% damage to all
                'hit_count' => ['base' => 3, 'per_level' => 0.2] // Max enemies hit
            ]
        ]);

        // ACTIVE SKILLS - Archery
        $aimedShot = Skill::create([
            'name' => 'Aimed Shot',
            'slug' => 'aimed-shot',
            'description' => 'A precise shot with increased critical hit chance',
            'type' => 'active',
            'category' => 'combat',
            'icon' => '🏹',
            'max_level' => 20,
            'weapon_types' => ['bow', 'crossbow'],
            'base_cost' => 12,
            'cooldown' => 4,
            'effects' => [
                'critical_chance' => ['base' => 25, 'per_level' => 3], // % crit chance
                'critical_damage' => ['base' => 1.5, 'per_level' => 0.1], // Crit multiplier
                'range_bonus' => ['base' => 20, 'per_level' => 2] // % range increase
            ]
        ]);

        $multiShot = Skill::create([
            'name' => 'Multi Shot',
            'slug' => 'multi-shot',
            'description' => 'Fire multiple arrows simultaneously',
            'type' => 'active',
            'category' => 'combat',
            'icon' => '🎯',
            'max_level' => 10,
            'weapon_types' => ['bow'],
            'requirements' => ['skills' => ['aimed-shot' => 5]],
            'base_cost' => 25,
            'cooldown' => 8,
            'effects' => [
                'arrow_count' => ['base' => 2, 'per_level' => 0.3], // Number of arrows
                'damage_per_arrow' => ['base' => 0.8, 'per_level' => 0.02] // Damage per arrow
            ]
        ]);

        // ACTIVE SKILLS - Magic/Dagger
        $backstab = Skill::create([
            'name' => 'Backstab',
            'slug' => 'backstab',
            'description' => 'Strike from behind for massive critical damage',
            'type' => 'active',
            'category' => 'combat',
            'icon' => '🗡️',
            'max_level' => 25,
            'weapon_types' => ['dagger'],
            'base_cost' => 10,
            'cooldown' => 5,
            'effects' => [
                'critical_multiplier' => ['base' => 2.0, 'per_level' => 0.1], // 200% crit damage
                'stealth_bonus' => ['base' => 15, 'per_level' => 2], // % chance to not be seen
                'poison_chance' => ['base' => 5, 'per_level' => 1] // % chance to poison
            ]
        ]);

        $heal = Skill::create([
            'name' => 'Heal',
            'slug' => 'heal',
            'description' => 'Restore health using magical energy',
            'type' => 'active',
            'category' => 'magic',
            'icon' => '✨',
            'max_level' => 30,
            'weapon_types' => ['staff', 'wand'],
            'base_cost' => 20,
            'cooldown' => 3,
            'effects' => [
                'heal_amount' => ['base' => 50, 'per_level' => 10], // Base healing
                'efficiency' => ['base' => 100, 'per_level' => 2], // % mana efficiency
                'over_time' => ['base' => 0, 'per_level' => 1] // Healing over time
            ]
        ]);

        // Now create experience sources for these skills
        
        // Smithing XP sources
        SkillExperienceSource::create([
            'skill_id' => $smithing->id,
            'source_type' => 'item_crafted',
            'conditions' => ['item_type' => 'weapon'],
            'base_experience' => 25
        ]);

        SkillExperienceSource::create([
            'skill_id' => $smithing->id,
            'source_type' => 'item_crafted',
            'conditions' => ['item_type' => 'armor'],
            'base_experience' => 30
        ]);

        // Mining XP sources
        SkillExperienceSource::create([
            'skill_id' => $mining->id,
            'source_type' => 'resource_gathered',
            'conditions' => ['resource_type' => 'ore'],
            'base_experience' => 15
        ]);

        // Combat skills XP sources
        SkillExperienceSource::create([
            'skill_id' => $powerStrike->id,
            'source_type' => 'skill_used',
            'base_experience' => 10
        ]);

        SkillExperienceSource::create([
            'skill_id' => $aimedShot->id,
            'source_type' => 'skill_used',
            'conditions' => ['critical_hit' => true],
            'base_experience' => 15
        ]);

        SkillExperienceSource::create([
            'skill_id' => $toughness->id,
            'source_type' => 'damage_taken',
            'base_experience' => 5,
            'level_multiplier' => 0.1
        ]);

        SkillExperienceSource::create([
            'skill_id' => $athletics->id,
            'source_type' => 'adventure_completed',
            'base_experience' => 20
        ]);
    }
}