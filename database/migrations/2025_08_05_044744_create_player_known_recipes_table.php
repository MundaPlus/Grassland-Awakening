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
        Schema::create('player_known_recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->foreignId('recipe_id')->constrained('crafting_recipes')->onDelete('cascade');
            $table->timestamp('learned_at')->useCurrent();
            $table->string('discovery_method')->nullable(); // 'adventure', 'npc', 'book', etc.
            $table->integer('times_crafted')->default(0);
            $table->timestamps();
            
            $table->unique(['player_id', 'recipe_id']);
            $table->index(['player_id', 'learned_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_known_recipes');
    }
};
