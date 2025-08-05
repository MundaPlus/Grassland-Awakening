<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ItemAffix;

class ItemAffixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prefixes = [
            // Power/Strength Prefixes
            ['name' => 'Mighty', 'stat_modifiers' => ['str' => 2], 'rarity_weight' => 80, 'level_requirement' => 1],
            ['name' => 'Powerful', 'stat_modifiers' => ['str' => 3], 'rarity_weight' => 60, 'level_requirement' => 3],
            ['name' => 'Heroic', 'stat_modifiers' => ['str' => 4], 'rarity_weight' => 40, 'level_requirement' => 5],
            ['name' => 'Legendary', 'stat_modifiers' => ['str' => 5], 'rarity_weight' => 20, 'level_requirement' => 8],
            ['name' => 'Godlike', 'stat_modifiers' => ['str' => 6], 'rarity_weight' => 10, 'level_requirement' => 12],
            
            // Agility/Dexterity Prefixes
            ['name' => 'Swift', 'stat_modifiers' => ['dex' => 2], 'rarity_weight' => 80, 'level_requirement' => 1],
            ['name' => 'Nimble', 'stat_modifiers' => ['dex' => 3], 'rarity_weight' => 60, 'level_requirement' => 3],
            ['name' => 'Lightning', 'stat_modifiers' => ['dex' => 4], 'rarity_weight' => 40, 'level_requirement' => 5],
            ['name' => 'Ethereal', 'stat_modifiers' => ['dex' => 5], 'rarity_weight' => 20, 'level_requirement' => 8],
            
            // Intelligence/Wisdom Prefixes
            ['name' => 'Arcane', 'stat_modifiers' => ['int' => 2], 'rarity_weight' => 70, 'level_requirement' => 2],
            ['name' => 'Mystical', 'stat_modifiers' => ['int' => 3], 'rarity_weight' => 50, 'level_requirement' => 4],
            ['name' => 'Celestial', 'stat_modifiers' => ['wis' => 2], 'rarity_weight' => 70, 'level_requirement' => 2],
            ['name' => 'Divine', 'stat_modifiers' => ['wis' => 4], 'rarity_weight' => 30, 'level_requirement' => 6],
            
            // Constitution/Health Prefixes
            ['name' => 'Sturdy', 'stat_modifiers' => ['con' => 2], 'rarity_weight' => 75, 'level_requirement' => 1],
            ['name' => 'Resilient', 'stat_modifiers' => ['con' => 3], 'rarity_weight' => 55, 'level_requirement' => 3],
            ['name' => 'Enduring', 'stat_modifiers' => ['con' => 4], 'rarity_weight' => 35, 'level_requirement' => 5],
            
            // Charisma Prefixes
            ['name' => 'Charming', 'stat_modifiers' => ['cha' => 2], 'rarity_weight' => 70, 'level_requirement' => 1],
            ['name' => 'Noble', 'stat_modifiers' => ['cha' => 3], 'rarity_weight' => 50, 'level_requirement' => 3],
            ['name' => 'Majestic', 'stat_modifiers' => ['cha' => 4], 'rarity_weight' => 30, 'level_requirement' => 5],
            
            // Elemental Prefixes
            ['name' => 'Blazing', 'stat_modifiers' => ['str' => 1, 'int' => 1], 'rarity_weight' => 60, 'level_requirement' => 3, 'applicable_types' => ['weapon']],
            ['name' => 'Frozen', 'stat_modifiers' => ['int' => 2], 'rarity_weight' => 60, 'level_requirement' => 3, 'applicable_types' => ['weapon']],
            ['name' => 'Shocking', 'stat_modifiers' => ['dex' => 1, 'int' => 1], 'rarity_weight' => 60, 'level_requirement' => 3, 'applicable_types' => ['weapon']],
            ['name' => 'Venomous', 'stat_modifiers' => ['dex' => 2], 'rarity_weight' => 50, 'level_requirement' => 4, 'applicable_types' => ['weapon']],
            
            // Material Prefixes
            ['name' => 'Iron', 'stat_modifiers' => ['str' => 1], 'rarity_weight' => 90, 'level_requirement' => 1],
            ['name' => 'Steel', 'stat_modifiers' => ['str' => 2], 'rarity_weight' => 70, 'level_requirement' => 2],
            ['name' => 'Mithril', 'stat_modifiers' => ['str' => 2, 'dex' => 1], 'rarity_weight' => 40, 'level_requirement' => 6],
            ['name' => 'Adamantine', 'stat_modifiers' => ['str' => 3, 'con' => 2], 'rarity_weight' => 20, 'level_requirement' => 10],
            
            // Craft Quality Prefixes
            ['name' => 'Fine', 'stat_modifiers' => ['str' => 1], 'rarity_weight' => 85, 'level_requirement' => 1],
            ['name' => 'Superior', 'stat_modifiers' => ['str' => 1, 'dex' => 1], 'rarity_weight' => 65, 'level_requirement' => 2],
            ['name' => 'Masterwork', 'stat_modifiers' => ['str' => 2, 'dex' => 1], 'rarity_weight' => 45, 'level_requirement' => 4],
            ['name' => 'Flawless', 'stat_modifiers' => ['str' => 2, 'dex' => 2], 'rarity_weight' => 25, 'level_requirement' => 7],
            ['name' => 'Perfect', 'stat_modifiers' => ['str' => 3, 'dex' => 2, 'con' => 1], 'rarity_weight' => 15, 'level_requirement' => 10],
            
            // Cursed/Dark Prefixes
            ['name' => 'Cursed', 'stat_modifiers' => ['str' => 3, 'cha' => -1], 'rarity_weight' => 25, 'level_requirement' => 5],
            ['name' => 'Dark', 'stat_modifiers' => ['int' => 2, 'wis' => -1], 'rarity_weight' => 40, 'level_requirement' => 3],
            ['name' => 'Shadow', 'stat_modifiers' => ['dex' => 3, 'cha' => -1], 'rarity_weight' => 35, 'level_requirement' => 4],
            
            // Ancient/Old Prefixes
            ['name' => 'Ancient', 'stat_modifiers' => ['wis' => 3], 'rarity_weight' => 30, 'level_requirement' => 6],
            ['name' => 'Primordial', 'stat_modifiers' => ['str' => 2, 'wis' => 2], 'rarity_weight' => 20, 'level_requirement' => 8],
            ['name' => 'Eternal', 'stat_modifiers' => ['con' => 4], 'rarity_weight' => 15, 'level_requirement' => 10],
            
            // Beast/Nature Prefixes
            ['name' => 'Wolfish', 'stat_modifiers' => ['str' => 1, 'dex' => 1], 'rarity_weight' => 60, 'level_requirement' => 2],
            ['name' => 'Draconic', 'stat_modifiers' => ['str' => 2, 'int' => 2], 'rarity_weight' => 20, 'level_requirement' => 8],
            ['name' => 'Feral', 'stat_modifiers' => ['str' => 2, 'dex' => 1], 'rarity_weight' => 50, 'level_requirement' => 3],
            ['name' => 'Primal', 'stat_modifiers' => ['str' => 3, 'con' => 1], 'rarity_weight' => 35, 'level_requirement' => 5],
        ];

        $suffixes = [
            // Power Suffixes
            ['name' => 'of Power', 'stat_modifiers' => ['str' => 2], 'rarity_weight' => 80, 'level_requirement' => 1],
            ['name' => 'of Might', 'stat_modifiers' => ['str' => 3], 'rarity_weight' => 60, 'level_requirement' => 3],
            ['name' => 'of the Titan', 'stat_modifiers' => ['str' => 4, 'con' => 1], 'rarity_weight' => 30, 'level_requirement' => 6],
            ['name' => 'of the Giant', 'stat_modifiers' => ['str' => 5], 'rarity_weight' => 20, 'level_requirement' => 8],
            
            // Agility Suffixes
            ['name' => 'of Speed', 'stat_modifiers' => ['dex' => 2], 'rarity_weight' => 80, 'level_requirement' => 1],
            ['name' => 'of Swiftness', 'stat_modifiers' => ['dex' => 3], 'rarity_weight' => 60, 'level_requirement' => 3],
            ['name' => 'of the Cheetah', 'stat_modifiers' => ['dex' => 4], 'rarity_weight' => 35, 'level_requirement' => 5],
            ['name' => 'of the Wind', 'stat_modifiers' => ['dex' => 5], 'rarity_weight' => 20, 'level_requirement' => 8],
            
            // Intelligence Suffixes
            ['name' => 'of Wisdom', 'stat_modifiers' => ['wis' => 2], 'rarity_weight' => 75, 'level_requirement' => 1],
            ['name' => 'of Intelligence', 'stat_modifiers' => ['int' => 2], 'rarity_weight' => 75, 'level_requirement' => 1],
            ['name' => 'of the Scholar', 'stat_modifiers' => ['int' => 3, 'wis' => 1], 'rarity_weight' => 45, 'level_requirement' => 4],
            ['name' => 'of the Sage', 'stat_modifiers' => ['wis' => 4], 'rarity_weight' => 30, 'level_requirement' => 6],
            ['name' => 'of the Archmage', 'stat_modifiers' => ['int' => 4, 'wis' => 2], 'rarity_weight' => 15, 'level_requirement' => 10],
            
            // Constitution Suffixes
            ['name' => 'of Vitality', 'stat_modifiers' => ['con' => 2], 'rarity_weight' => 80, 'level_requirement' => 1],
            ['name' => 'of Health', 'stat_modifiers' => ['con' => 3], 'rarity_weight' => 60, 'level_requirement' => 3],
            ['name' => 'of the Bear', 'stat_modifiers' => ['con' => 3, 'str' => 1], 'rarity_weight' => 40, 'level_requirement' => 4],
            ['name' => 'of Fortitude', 'stat_modifiers' => ['con' => 4], 'rarity_weight' => 25, 'level_requirement' => 6],
            
            // Animal Suffixes
            ['name' => 'of the Wolf', 'stat_modifiers' => ['str' => 1, 'dex' => 1], 'rarity_weight' => 65, 'level_requirement' => 2],
            ['name' => 'of the Eagle', 'stat_modifiers' => ['dex' => 2, 'wis' => 1], 'rarity_weight' => 55, 'level_requirement' => 3],
            ['name' => 'of the Lion', 'stat_modifiers' => ['str' => 2, 'cha' => 1], 'rarity_weight' => 50, 'level_requirement' => 3],
            ['name' => 'of the Dragon', 'stat_modifiers' => ['str' => 3, 'int' => 2], 'rarity_weight' => 15, 'level_requirement' => 10],
            ['name' => 'of the Phoenix', 'stat_modifiers' => ['int' => 3, 'wis' => 2], 'rarity_weight' => 15, 'level_requirement' => 10],
            
            // Elemental Suffixes
            ['name' => 'of Fire', 'stat_modifiers' => ['str' => 1, 'int' => 1], 'rarity_weight' => 60, 'level_requirement' => 2],
            ['name' => 'of Ice', 'stat_modifiers' => ['int' => 2], 'rarity_weight' => 60, 'level_requirement' => 2],
            ['name' => 'of Lightning', 'stat_modifiers' => ['dex' => 1, 'int' => 1], 'rarity_weight' => 55, 'level_requirement' => 3],
            ['name' => 'of Earth', 'stat_modifiers' => ['str' => 1, 'con' => 1], 'rarity_weight' => 65, 'level_requirement' => 2],
            ['name' => 'of the Storm', 'stat_modifiers' => ['dex' => 2, 'int' => 2], 'rarity_weight' => 25, 'level_requirement' => 6],
            ['name' => 'of the Inferno', 'stat_modifiers' => ['str' => 3, 'int' => 2], 'rarity_weight' => 20, 'level_requirement' => 8],
            
            // Craftsmanship Suffixes
            ['name' => 'of the Apprentice', 'stat_modifiers' => ['str' => 1], 'rarity_weight' => 90, 'level_requirement' => 1],
            ['name' => 'of the Journeyman', 'stat_modifiers' => ['str' => 2], 'rarity_weight' => 70, 'level_requirement' => 2],
            ['name' => 'of the Master', 'stat_modifiers' => ['str' => 2, 'dex' => 1], 'rarity_weight' => 40, 'level_requirement' => 5],
            ['name' => 'of the Grandmaster', 'stat_modifiers' => ['str' => 3, 'dex' => 2], 'rarity_weight' => 20, 'level_requirement' => 8],
            
            // Protection Suffixes
            ['name' => 'of Protection', 'stat_modifiers' => ['con' => 2], 'rarity_weight' => 75, 'level_requirement' => 1, 'applicable_types' => ['armor']],
            ['name' => 'of Warding', 'stat_modifiers' => ['con' => 1, 'wis' => 1], 'rarity_weight' => 60, 'level_requirement' => 3, 'applicable_types' => ['armor']],
            ['name' => 'of the Guardian', 'stat_modifiers' => ['con' => 3, 'str' => 1], 'rarity_weight' => 30, 'level_requirement' => 6, 'applicable_types' => ['armor']],
            
            // Nobility Suffixes
            ['name' => 'of the Noble', 'stat_modifiers' => ['cha' => 2], 'rarity_weight' => 70, 'level_requirement' => 2],
            ['name' => 'of the King', 'stat_modifiers' => ['cha' => 3, 'str' => 1], 'rarity_weight' => 25, 'level_requirement' => 7],
            ['name' => 'of the Emperor', 'stat_modifiers' => ['cha' => 4, 'str' => 2], 'rarity_weight' => 15, 'level_requirement' => 10],
            
            // Mystery/Ancient Suffixes
            ['name' => 'of Mystery', 'stat_modifiers' => ['int' => 2, 'wis' => 1], 'rarity_weight' => 45, 'level_requirement' => 4],
            ['name' => 'of the Ancients', 'stat_modifiers' => ['wis' => 3], 'rarity_weight' => 30, 'level_requirement' => 6],
            ['name' => 'of Eternity', 'stat_modifiers' => ['con' => 3, 'wis' => 2], 'rarity_weight' => 20, 'level_requirement' => 8],
            ['name' => 'of the Void', 'stat_modifiers' => ['int' => 4, 'cha' => -1], 'rarity_weight' => 15, 'level_requirement' => 9],
            
            // Skill Suffixes
            ['name' => 'of Slaying', 'stat_modifiers' => ['str' => 2, 'dex' => 1], 'rarity_weight' => 50, 'level_requirement' => 3, 'applicable_types' => ['weapon']],
            ['name' => 'of Precision', 'stat_modifiers' => ['dex' => 3], 'rarity_weight' => 45, 'level_requirement' => 4, 'applicable_types' => ['weapon']],
            ['name' => 'of Devastation', 'stat_modifiers' => ['str' => 4], 'rarity_weight' => 25, 'level_requirement' => 7, 'applicable_types' => ['weapon']],
            
            // Mythical Suffixes
            ['name' => 'of Legend', 'stat_modifiers' => ['str' => 2, 'dex' => 1, 'cha' => 2], 'rarity_weight' => 10, 'level_requirement' => 12],
            ['name' => 'of Myth', 'stat_modifiers' => ['int' => 3, 'wis' => 3], 'rarity_weight' => 10, 'level_requirement' => 12],
            ['name' => 'of the Gods', 'stat_modifiers' => ['str' => 3, 'dex' => 2, 'int' => 2, 'wis' => 2, 'con' => 2, 'cha' => 3], 'rarity_weight' => 5, 'level_requirement' => 15],
        ];

        // Insert prefixes
        foreach ($prefixes as $prefixData) {
            ItemAffix::create(array_merge($prefixData, ['type' => 'prefix']));
        }

        // Insert suffixes
        foreach ($suffixes as $suffixData) {
            ItemAffix::create(array_merge($suffixData, ['type' => 'suffix']));
        }

        echo "Created " . count($prefixes) . " prefixes and " . count($suffixes) . " suffixes.\n";
    }
}