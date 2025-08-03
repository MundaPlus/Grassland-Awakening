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
        // Drop the empty table first
        Schema::dropIfExists('village_specializations');
        
        // Recreate with proper structure
        Schema::create('village_specializations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->enum('specialization_type', ['military_outpost', 'trading_hub', 'magical_academy']);
            $table->integer('level')->default(1);
            $table->json('bonuses_json'); // Specialization bonuses
            $table->timestamps();
            
            $table->unique(['player_id', 'specialization_type']);
            $table->index(['specialization_type', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('village_specializations');
    }
};
