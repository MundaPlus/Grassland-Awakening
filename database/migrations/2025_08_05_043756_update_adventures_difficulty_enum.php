<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First update any existing 'normal' values to 'medium' to match the application
        DB::statement("UPDATE adventures SET difficulty = 'medium' WHERE difficulty = 'normal'");
        DB::statement("UPDATE adventures SET difficulty = 'expert' WHERE difficulty = 'nightmare'");
        
        // Then alter the enum to match the application's expected values
        DB::statement("ALTER TABLE adventures MODIFY COLUMN difficulty ENUM('easy', 'medium', 'hard', 'expert') DEFAULT 'medium'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert any updated values back
        DB::statement("UPDATE adventures SET difficulty = 'normal' WHERE difficulty = 'medium'");
        DB::statement("UPDATE adventures SET difficulty = 'nightmare' WHERE difficulty = 'expert'");
        
        // Revert the enum back to original values
        DB::statement("ALTER TABLE adventures MODIFY COLUMN difficulty ENUM('easy', 'normal', 'hard', 'nightmare') DEFAULT 'normal'");
    }
};
