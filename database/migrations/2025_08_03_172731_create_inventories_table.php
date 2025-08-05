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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->integer('max_durability')->default(100);
            $table->integer('current_durability')->default(100);
            $table->json('item_metadata')->nullable(); // For storing item-specific data like enchantments
            $table->timestamps();
            
            $table->index(['player_id', 'item_id']);
            $table->unique(['player_id', 'item_id']); // Each player can only have one stack per item type
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
