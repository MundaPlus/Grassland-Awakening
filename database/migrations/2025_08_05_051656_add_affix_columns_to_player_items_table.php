<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('player_items', function (Blueprint $table) {
            $table->string('custom_name')->nullable()->after('equipment_slot');
            $table->json('affix_stat_modifiers')->nullable()->after('custom_name');
            $table->foreignId('prefix_affix_id')->nullable()->constrained('item_affixes')->onDelete('set null')->after('affix_stat_modifiers');
            $table->foreignId('suffix_affix_id')->nullable()->constrained('item_affixes')->onDelete('set null')->after('prefix_affix_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_items', function (Blueprint $table) {
            $table->dropForeign(['prefix_affix_id']);
            $table->dropForeign(['suffix_affix_id']);
            $table->dropColumn(['custom_name', 'affix_stat_modifiers', 'prefix_affix_id', 'suffix_affix_id']);
        });
    }
};
