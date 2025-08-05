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
        Schema::create('adventure_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adventure_id')->constrained()->onDelete('cascade');
            $table->string('node_id'); // e.g., '1-1', '2-2', '10-boss'
            $table->enum('type', ['start', 'combat', 'treasure', 'event', 'rest', 'boss']);
            $table->integer('level')->index();
            $table->json('node_data'); // Stores type-specific data (enemies, treasures, etc.)
            $table->json('connections')->nullable(); // Connected node IDs
            $table->boolean('completed')->default(false);
            $table->boolean('accessible')->default(false);
            $table->integer('position_x')->nullable(); // For visual map positioning
            $table->integer('position_y')->nullable();
            $table->json('completion_data')->nullable(); // Rewards collected, choices made, etc.
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->unique(['adventure_id', 'node_id']);
            $table->index(['adventure_id', 'level']);
            $table->index(['adventure_id', 'completed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adventure_nodes');
    }
};
