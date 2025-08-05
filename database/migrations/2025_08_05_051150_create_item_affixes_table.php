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
        Schema::create('item_affixes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Blazing", "of the Wolf", etc.
            $table->enum('type', ['prefix', 'suffix']); 
            $table->json('applicable_types')->nullable(); // weapon, armor, accessory types this can apply to
            $table->json('stat_modifiers')->nullable(); // stat bonuses/penalties
            $table->integer('rarity_weight')->default(100); // higher = more common
            $table->integer('level_requirement')->default(1); // minimum level for this affix
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['type', 'rarity_weight']);
            $table->index(['level_requirement', 'rarity_weight']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_affixes');
    }
};