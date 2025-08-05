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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['weapon', 'armor', 'accessory', 'consumable', 'material', 'quest']);
            $table->string('subtype')->nullable();
            $table->enum('rarity', ['common', 'uncommon', 'rare', 'epic', 'legendary'])->default('common');
            $table->integer('level_requirement')->default(1);
            $table->json('stats_modifiers')->nullable();
            $table->string('damage_dice')->nullable();
            $table->integer('damage_bonus')->default(0);
            $table->integer('ac_bonus')->default(0);
            $table->boolean('is_equippable')->default(false);
            $table->boolean('is_consumable')->default(false);
            $table->boolean('is_stackable')->default(false);
            $table->integer('max_stack_size')->default(1);
            $table->integer('base_value')->default(0);
            $table->string('icon')->nullable();
            $table->timestamps();

            $table->index(['type', 'subtype']);
            $table->index('rarity');
            $table->index('level_requirement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
