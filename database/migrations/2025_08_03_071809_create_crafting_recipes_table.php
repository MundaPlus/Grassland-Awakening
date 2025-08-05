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
        Schema::create('crafting_recipes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('result_item_id')->constrained('items')->onDelete('cascade');
            $table->integer('result_quantity')->default(1);
            $table->enum('category', ['weapon', 'armor', 'accessory', 'consumable', 'crafting_material']);
            $table->enum('difficulty', ['basic', 'intermediate', 'advanced', 'master']);
            $table->integer('crafting_time')->default(60); // seconds
            $table->integer('gold_cost')->default(0);
            $table->integer('experience_reward')->default(10);
            $table->json('stat_requirements')->nullable(); // e.g., {"int": 5, "dex": 3}
            $table->json('recipe_discovery')->nullable(); // How recipe is discovered
            $table->boolean('is_upgrade_recipe')->default(false);
            $table->foreignId('upgrade_base_item_id')->nullable()->constrained('items');
            $table->timestamps();
            
            $table->index(['category', 'difficulty']);
            $table->index('is_upgrade_recipe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crafting_recipes');
    }
};
