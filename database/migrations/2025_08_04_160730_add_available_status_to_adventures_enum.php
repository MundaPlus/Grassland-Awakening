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
        // Add 'available' to the adventures status enum
        DB::statement("ALTER TABLE adventures MODIFY COLUMN status ENUM('active', 'available', 'completed', 'abandoned', 'failed') DEFAULT 'available'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'available' from the enum (set any available adventures to active first)
        DB::statement("UPDATE adventures SET status = 'available' WHERE status = 'active'");
        DB::statement("ALTER TABLE adventures MODIFY COLUMN status ENUM('active', 'completed', 'abandoned', 'failed') default 'active'");
    }
};
