<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Skills definitions table
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description');
            $table->enum('type', ['passive', 'active']);
            $table->string('category'); // combat, crafting, gathering, magic, survival
            $table->string('icon')->nullable();
            $table->integer('max_level')->default(100);
            $table->json('requirements')->nullable(); // Level, other skills, etc.
            $table->json('weapon_types')->nullable(); // For active skills - which weapons can use this
            $table->json('effects')->nullable(); // What the skill does at different levels
            $table->integer('base_cost')->default(10); // Base mana/stamina cost for active skills
            $table->integer('cooldown')->default(0); // Cooldown in seconds for active skills
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        // Player skills progress
        Schema::create('player_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->foreignId('skill_id')->constrained()->onDelete('cascade');
            $table->integer('level')->default(1);
            $table->bigInteger('experience')->default(0);
            $table->timestamp('last_used')->nullable();
            $table->integer('times_used')->default(0);
            $table->timestamps();
            
            $table->unique(['player_id', 'skill_id']);
            $table->index(['player_id', 'skill_id', 'level']);
        });

        // Skill experience gain rules
        Schema::create('skill_experience_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skill_id')->constrained()->onDelete('cascade');
            $table->string('source_type'); // combat_victory, item_crafted, resource_gathered, etc.
            $table->json('conditions')->nullable(); // Additional conditions for XP gain
            $table->integer('base_experience');
            $table->decimal('level_multiplier', 3, 2)->default(1.0);
            $table->timestamps();
        });

        // Active skill cooldowns (for combat)
        Schema::create('player_skill_cooldowns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->foreignId('skill_id')->constrained()->onDelete('cascade');
            $table->timestamp('available_at');
            $table->timestamps();
            
            $table->unique(['player_id', 'skill_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('player_skill_cooldowns');
        Schema::dropIfExists('skill_experience_sources');
        Schema::dropIfExists('player_skills');
        Schema::dropIfExists('skills');
    }
};