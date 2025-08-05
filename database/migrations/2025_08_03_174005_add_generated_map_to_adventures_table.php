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
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('adventures', 'title')) {
                $table->string('title')->nullable()->after('seed');
            }
            if (!Schema::hasColumn('adventures', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adventures', function (Blueprint $table) {
            $table->dropColumn(['title', 'description']);
        });
    }
};
