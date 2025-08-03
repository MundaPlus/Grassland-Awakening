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
        Schema::table('adventures', function (Blueprint $table) {
            // Change road column from enum to string to accommodate new road types
            $table->string('road', 50)->change();
            
            // Also add missing columns that are referenced in GameController
            $table->string('title')->nullable()->after('seed');
            $table->text('description')->nullable()->after('title');
            $table->json('generated_map')->nullable()->after('description');
            
            // Update status enum to include 'available'
            $table->dropColumn('status');
        });
        
        Schema::table('adventures', function (Blueprint $table) {
            $table->enum('status', ['active', 'available', 'completed', 'abandoned'])->default('active')->after('generated_map');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adventures', function (Blueprint $table) {
            // Revert road column back to enum
            $table->enum('road', ['north', 'south', 'east', 'west'])->change();
            
            // Remove added columns
            $table->dropColumn(['title', 'description', 'generated_map']);
            
            // Revert status enum
            $table->dropColumn('status');
        });
        
        Schema::table('adventures', function (Blueprint $table) {
            $table->enum('status', ['active', 'completed', 'abandoned'])->default('active');
        });
    }
};
