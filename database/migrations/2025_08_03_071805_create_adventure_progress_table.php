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
        Schema::create('adventure_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adventure_id')->constrained()->onDelete('cascade');
            $table->string('node_id');
            $table->enum('node_type', ['combat', 'treasure', 'event', 'boss', 'rest']);
            $table->json('node_state')->nullable(); // Combat results, choices made, etc.
            $table->json('loot_collected')->nullable();
            $table->integer('currency_earned')->default(0);
            $table->boolean('completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->unique(['adventure_id', 'node_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adventure_progress');
    }
};
