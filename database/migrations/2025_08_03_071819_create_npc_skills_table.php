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
        Schema::create('npc_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('npc_id')->constrained()->onDelete('cascade');
            $table->string('skill_tree'); // crafting, medicine, commerce, etc.
            $table->string('skill_name'); // specific skill within tree
            $table->integer('level')->default(1);
            $table->json('abilities_json')->nullable(); // Skill-specific abilities
            $table->timestamps();
            
            $table->unique(['npc_id', 'skill_name']);
            $table->index(['skill_tree', 'skill_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('npc_skills');
    }
};
