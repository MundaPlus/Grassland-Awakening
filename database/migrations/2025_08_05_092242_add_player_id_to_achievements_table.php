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
        Schema::table('achievements', function (Blueprint $table) {
            // Check if player_id column doesn't exist, then add it
            if (!Schema::hasColumn('achievements', 'player_id')) {
                $table->foreignId('player_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            }
            
            // Add other missing columns if they don't exist
            if (!Schema::hasColumn('achievements', 'achievement_id')) {
                $table->string('achievement_id')->nullable();
            }
            
            if (!Schema::hasColumn('achievements', 'name')) {
                $table->string('name')->nullable();
            }
            
            if (!Schema::hasColumn('achievements', 'description')) {
                $table->text('description')->nullable();
            }
            
            if (!Schema::hasColumn('achievements', 'category')) {
                $table->string('category')->nullable();
            }
            
            if (!Schema::hasColumn('achievements', 'points')) {
                $table->integer('points')->default(0);
            }
            
            if (!Schema::hasColumn('achievements', 'icon')) {
                $table->string('icon')->nullable();
            }
            
            if (!Schema::hasColumn('achievements', 'unlocked_at')) {
                $table->timestamp('unlocked_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('achievements', function (Blueprint $table) {
            if (Schema::hasColumn('achievements', 'player_id')) {
                $table->dropForeign(['player_id']);
                $table->dropColumn('player_id');
            }
            if (Schema::hasColumn('achievements', 'achievement_id')) {
                $table->dropColumn('achievement_id');
            }
            if (Schema::hasColumn('achievements', 'name')) {
                $table->dropColumn('name');
            }
            if (Schema::hasColumn('achievements', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('achievements', 'category')) {
                $table->dropColumn('category');
            }
            if (Schema::hasColumn('achievements', 'points')) {
                $table->dropColumn('points');
            }
            if (Schema::hasColumn('achievements', 'icon')) {
                $table->dropColumn('icon');
            }
            if (Schema::hasColumn('achievements', 'unlocked_at')) {
                $table->dropColumn('unlocked_at');
            }
        });
    }
};
