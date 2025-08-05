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
        Schema::create('crafting_recipe_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('crafting_recipes')->onDelete('cascade');
            $table->foreignId('material_item_id')->constrained('items')->onDelete('cascade');
            $table->integer('quantity_required');
            $table->boolean('is_consumed')->default(true); // Tools aren't consumed
            $table->timestamps();
            
            $table->index(['recipe_id', 'material_item_id']);
            $table->unique(['recipe_id', 'material_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crafting_recipe_materials');
    }
};
