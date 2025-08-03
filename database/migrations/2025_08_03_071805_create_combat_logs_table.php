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
        Schema::create('combat_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->foreignId('adventure_id')->constrained()->onDelete('cascade');
            $table->string('node_id');
            $table->string('enemy_type');
            $table->enum('outcome', ['victory', 'defeat', 'fled']);
            $table->integer('damage_dealt')->default(0);
            $table->integer('damage_taken')->default(0);
            $table->integer('turns_taken')->default(1);
            $table->integer('currency_earned')->default(0);
            $table->json('combat_details')->nullable(); // Turn-by-turn log
            $table->timestamps();
            
            $table->index(['player_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combat_logs');
    }
};
