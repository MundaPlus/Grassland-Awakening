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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('character_name');
            $table->integer('level')->default(1);
            $table->integer('experience')->default(0);
            $table->integer('persistent_currency')->default(0);
            
            // D&D Stats
            $table->integer('hp')->default(10);
            $table->integer('max_hp')->default(10);
            $table->integer('ac')->default(10);
            $table->integer('str')->default(10);
            $table->integer('dex')->default(10);
            $table->integer('con')->default(10);
            $table->integer('int')->default(10);
            $table->integer('wis')->default(10);
            $table->integer('cha')->default(10);
            
            // Unallocated stat points from leveling
            $table->integer('unallocated_stat_points')->default(0);
            
            // Current position (null if in village)
            $table->string('current_road')->nullable();
            $table->integer('current_level')->nullable();
            $table->string('current_node_id')->nullable();
            
            $table->timestamps();
            
            $table->unique(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
