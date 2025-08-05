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
            $table->json('entered_nodes')->nullable()->after('completed_nodes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adventures', function (Blueprint $table) {
            $table->dropColumn('entered_nodes');
        });
    }
};
