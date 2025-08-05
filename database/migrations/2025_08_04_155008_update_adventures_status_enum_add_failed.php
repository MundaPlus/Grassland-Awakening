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
        // For MySQL, we need to alter the enum column to add 'failed'
        DB::statement("ALTER TABLE adventures MODIFY COLUMN status ENUM('active', 'completed', 'abandoned', 'failed') DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'failed' from the enum (set any failed adventures to abandoned first)
        DB::statement("UPDATE adventures SET status = 'abandoned' WHERE status = 'failed'");
        DB::statement("ALTER TABLE adventures MODIFY COLUMN status ENUM('active', 'completed', 'abandoned') DEFAULT 'active'");
    }
};
