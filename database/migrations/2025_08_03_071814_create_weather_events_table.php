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
        Schema::create('weather_events', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // clear, rain, snow, fog, storm, etc.
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->json('effects_json'); // Combat and game effects
            $table->string('location')->nullable(); // Real-world location if applicable
            $table->json('real_weather_data')->nullable(); // OpenWeatherMap API data
            $table->timestamps();
            
            $table->index(['start_date', 'end_date']);
            $table->index(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_events');
    }
};
