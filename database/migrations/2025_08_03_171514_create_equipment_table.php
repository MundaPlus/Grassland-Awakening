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
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->enum('slot', [
                'helm', 'chest', 'pants', 'boots', 'gloves',
                'neck', 'ring_1', 'ring_2', 'artifact',
                'weapon_1', 'weapon_2', 'shield', 'bow', 'wand', 'staff', 'two_handed_weapon'
            ]);
            $table->integer('durability')->default(100);
            $table->integer('max_durability')->default(100);
            $table->json('enchantments')->nullable();
            $table->timestamps();

            $table->unique(['player_id', 'slot']);
            $table->index('player_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
