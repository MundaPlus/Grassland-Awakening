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
        Schema::table('items', function (Blueprint $table) {
            // Add max_durability column
            $table->integer('max_durability')->default(100)->after('base_value');
        });
        
        // Update type enum to include crafting_material and misc
        DB::statement("ALTER TABLE items MODIFY COLUMN type ENUM('weapon', 'armor', 'accessory', 'consumable', 'crafting_material', 'quest_item', 'misc') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('max_durability');
        });
        
        // Revert type enum
        DB::statement("ALTER TABLE items MODIFY COLUMN type ENUM('weapon', 'armor', 'accessory', 'consumable', 'material', 'quest') NOT NULL");
    }
};
