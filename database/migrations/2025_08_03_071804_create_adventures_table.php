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
        Schema::create('adventures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->enum('road', ['north', 'south', 'east', 'west']);
            $table->string('seed');
            $table->enum('difficulty', ['easy', 'normal', 'hard', 'nightmare'])->default('normal');
            $table->enum('status', ['active', 'completed', 'abandoned'])->default('active');
            $table->integer('current_level')->default(1);
            $table->string('current_node_id')->nullable();
            $table->json('completed_nodes')->nullable(); // Array of completed node IDs
            $table->json('collected_loot')->nullable(); // Array of collected items
            $table->integer('currency_earned')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['player_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adventures');
    }
};
