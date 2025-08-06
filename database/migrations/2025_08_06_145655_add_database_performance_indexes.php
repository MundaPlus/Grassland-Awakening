<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add performance indexes using raw SQL with IF NOT EXISTS logic
        $indexes = [
            // Players table indexes
            "CREATE INDEX IF NOT EXISTS players_level_index ON players(level)",
            "CREATE INDEX IF NOT EXISTS players_experience_index ON players(experience)",
            "CREATE INDEX IF NOT EXISTS players_current_road_current_level_index ON players(current_road, current_level)",
            "CREATE INDEX IF NOT EXISTS players_persistent_currency_index ON players(persistent_currency)",
            
            // Adventures table indexes
            "CREATE INDEX IF NOT EXISTS adventures_road_index ON adventures(road)",
            "CREATE INDEX IF NOT EXISTS adventures_difficulty_index ON adventures(difficulty)",
            "CREATE INDEX IF NOT EXISTS adventures_completed_at_index ON adventures(completed_at)",
            "CREATE INDEX IF NOT EXISTS adventures_player_id_road_difficulty_index ON adventures(player_id, road, difficulty)",
            "CREATE INDEX IF NOT EXISTS adventures_status_completed_at_index ON adventures(status, completed_at)",
            
            // Items table indexes
            "CREATE INDEX IF NOT EXISTS items_name_index ON items(name)",
            "CREATE INDEX IF NOT EXISTS items_type_rarity_index ON items(type, rarity)",
            "CREATE INDEX IF NOT EXISTS items_is_equippable_index ON items(is_equippable)",
            "CREATE INDEX IF NOT EXISTS items_is_consumable_index ON items(is_consumable)",
            "CREATE INDEX IF NOT EXISTS items_is_stackable_index ON items(is_stackable)",
            
            // Player_items table indexes
            "CREATE INDEX IF NOT EXISTS player_items_equipment_slot_index ON player_items(equipment_slot)",
            "CREATE INDEX IF NOT EXISTS player_items_player_id_equipment_slot_index ON player_items(player_id, equipment_slot)",
            "CREATE INDEX IF NOT EXISTS player_items_is_equipped_equipment_slot_index ON player_items(is_equipped, equipment_slot)",
            
            // Equipment table indexes
            "CREATE INDEX IF NOT EXISTS equipment_slot_index ON equipment(slot)",
            "CREATE INDEX IF NOT EXISTS equipment_durability_index ON equipment(durability)",
            
            // Player_skills table indexes
            "CREATE INDEX IF NOT EXISTS player_skills_level_index ON player_skills(level)",
            "CREATE INDEX IF NOT EXISTS player_skills_experience_index ON player_skills(experience)",
            "CREATE INDEX IF NOT EXISTS player_skills_player_id_level_index ON player_skills(player_id, level)",
            
            // Skills table indexes
            "CREATE INDEX IF NOT EXISTS skills_type_index ON skills(type)",
            "CREATE INDEX IF NOT EXISTS skills_category_index ON skills(category)",
            "CREATE INDEX IF NOT EXISTS skills_is_enabled_index ON skills(is_enabled)",
        ];
        
        // Conditional indexes for tables that might not exist or have certain columns
        if (Schema::hasTable('inventories')) {
            $indexes[] = "CREATE INDEX IF NOT EXISTS inventories_quantity_index ON inventories(quantity)";
            $indexes[] = "CREATE INDEX IF NOT EXISTS inventories_current_durability_index ON inventories(current_durability)";
        }
        
        if (Schema::hasTable('crafting_recipes')) {
            if (Schema::hasColumn('crafting_recipes', 'category')) {
                $indexes[] = "CREATE INDEX IF NOT EXISTS crafting_recipes_category_index ON crafting_recipes(category)";
            }
            if (Schema::hasColumn('crafting_recipes', 'level_requirement')) {
                $indexes[] = "CREATE INDEX IF NOT EXISTS crafting_recipes_level_requirement_index ON crafting_recipes(level_requirement)";
            }
        }
        
        if (Schema::hasTable('player_known_recipes')) {
            if (Schema::hasColumn('player_known_recipes', 'learned_at')) {
                $indexes[] = "CREATE INDEX IF NOT EXISTS player_known_recipes_learned_at_index ON player_known_recipes(learned_at)";
            }
        }
        
        // Execute all index creation queries
        foreach ($indexes as $sql) {
            DB::statement($sql);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes using raw SQL
        $dropIndexes = [
            "DROP INDEX IF EXISTS players_level_index ON players",
            "DROP INDEX IF EXISTS players_experience_index ON players",
            "DROP INDEX IF EXISTS players_current_road_current_level_index ON players",
            "DROP INDEX IF EXISTS players_persistent_currency_index ON players",
            "DROP INDEX IF EXISTS adventures_road_index ON adventures",
            "DROP INDEX IF EXISTS adventures_difficulty_index ON adventures",
            "DROP INDEX IF EXISTS adventures_completed_at_index ON adventures",
            "DROP INDEX IF EXISTS adventures_player_id_road_difficulty_index ON adventures",
            "DROP INDEX IF EXISTS adventures_status_completed_at_index ON adventures",
            "DROP INDEX IF EXISTS items_name_index ON items",
            "DROP INDEX IF EXISTS items_type_rarity_index ON items",
            "DROP INDEX IF EXISTS items_is_equippable_index ON items",
            "DROP INDEX IF EXISTS items_is_consumable_index ON items",
            "DROP INDEX IF EXISTS items_is_stackable_index ON items",
            "DROP INDEX IF EXISTS player_items_equipment_slot_index ON player_items",
            "DROP INDEX IF EXISTS player_items_player_id_equipment_slot_index ON player_items",
            "DROP INDEX IF EXISTS player_items_is_equipped_equipment_slot_index ON player_items",
            "DROP INDEX IF EXISTS equipment_slot_index ON equipment",
            "DROP INDEX IF EXISTS equipment_durability_index ON equipment",
            "DROP INDEX IF EXISTS player_skills_level_index ON player_skills",
            "DROP INDEX IF EXISTS player_skills_experience_index ON player_skills",
            "DROP INDEX IF EXISTS player_skills_player_id_level_index ON player_skills",
            "DROP INDEX IF EXISTS skills_type_index ON skills",
            "DROP INDEX IF EXISTS skills_category_index ON skills",
            "DROP INDEX IF EXISTS skills_is_enabled_index ON skills",
        ];
        
        // Conditional index drops
        if (Schema::hasTable('inventories')) {
            $dropIndexes[] = "DROP INDEX IF EXISTS inventories_quantity_index ON inventories";
            $dropIndexes[] = "DROP INDEX IF EXISTS inventories_current_durability_index ON inventories";
        }
        
        if (Schema::hasTable('crafting_recipes')) {
            $dropIndexes[] = "DROP INDEX IF EXISTS crafting_recipes_category_index ON crafting_recipes";
            $dropIndexes[] = "DROP INDEX IF EXISTS crafting_recipes_level_requirement_index ON crafting_recipes";
        }
        
        if (Schema::hasTable('player_known_recipes')) {
            $dropIndexes[] = "DROP INDEX IF EXISTS player_known_recipes_learned_at_index ON player_known_recipes";
        }
        
        // Execute all drop queries (suppress errors if index doesn't exist)
        foreach ($dropIndexes as $sql) {
            try {
                DB::statement($sql);
            } catch (\Exception $e) {
                // Ignore errors - index might not exist
            }
        }
    }
};