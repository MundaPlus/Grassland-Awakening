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
        // Drop and recreate faction_reputations table
        Schema::dropIfExists('faction_reputations');
        
        Schema::create('faction_reputations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->string('faction_id'); // e.g., 'village_council', 'merchants_guild'
            $table->string('faction_name');
            $table->integer('reputation_score')->default(0);
            $table->timestamps();
            
            $table->unique(['player_id', 'faction_id']);
            $table->index(['faction_id', 'reputation_score']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faction_reputations');
    }
};
