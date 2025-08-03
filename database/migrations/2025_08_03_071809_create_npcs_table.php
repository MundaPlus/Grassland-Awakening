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
        Schema::create('npcs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->enum('personality', ['friendly', 'gruff', 'mysterious', 'cheerful', 'serious']);
            $table->enum('profession', ['blacksmith', 'healer', 'trader', 'scholar', 'guard', 'farmer']);
            $table->integer('relationship_score')->default(0);
            $table->enum('village_status', ['migrating', 'settled', 'departed'])->default('migrating');
            $table->timestamp('arrived_at')->nullable();
            $table->json('conversation_history')->nullable();
            $table->json('available_services')->nullable();
            $table->timestamps();
            
            $table->index(['player_id', 'village_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('npcs');
    }
};
