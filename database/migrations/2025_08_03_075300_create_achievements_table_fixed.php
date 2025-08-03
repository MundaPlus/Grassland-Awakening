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
        // Drop and recreate achievements table
        Schema::dropIfExists('achievements');
        
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->string('achievement_id'); // Unique identifier for the achievement type
            $table->string('name');
            $table->text('description');
            $table->string('category');
            $table->integer('points');
            $table->string('icon');
            $table->timestamp('unlocked_at');
            $table->timestamps();
            
            $table->unique(['player_id', 'achievement_id']);
            $table->index(['category', 'points']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
