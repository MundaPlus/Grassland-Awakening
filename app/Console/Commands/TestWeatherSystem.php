<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WeatherService;

class TestWeatherSystem extends Command
{
    protected $signature = 'game:test-weather {--location=} {--real-weather} {--road=north}';
    protected $description = 'Test weather and seasonal systems';

    public function handle()
    {
        $weatherService = app(WeatherService::class);
        $location = $this->option('location');
        $useRealWeather = $this->option('real-weather');
        $road = $this->option('road');

        $this->info('=== WEATHER SYSTEM TEST ===');
        
        // Test current season
        $this->line('=== CURRENT SEASON ===');
        $season = $weatherService->getCurrentSeason();
        $this->table(['Property', 'Value'], [
            ['Season', $season['season']],
            ['Name', $season['name']],
            ['Duration (weeks)', $season['duration_weeks']],
            ['NPC Migration Chance', $season['effects']['npc_migration_chance'] ?? 'N/A'],
            ['Crafting Bonus', $season['effects']['crafting_material_bonus'] ?? 'N/A'],
        ]);

        // Test simulated weather
        $this->line('=== SIMULATED WEATHER ===');
        $simulatedWeather = $weatherService->getCurrentWeather();
        $this->displayWeatherInfo($simulatedWeather, 'Simulated');

        // Test real weather if location provided
        if ($location && $useRealWeather) {
            $this->line('=== REAL WORLD WEATHER ===');
            $realWeather = $weatherService->getCurrentWeather($location, true);
            if ($realWeather && isset($realWeather['real_weather_data'])) {
                $this->displayWeatherInfo($realWeather, 'Real World');
                
                $realData = $realWeather['real_weather_data'];
                $this->line('=== REAL WEATHER DATA ===');
                $this->table(['Property', 'Value'], [
                    ['Location', $realData['location']],
                    ['Temperature', $realData['temperature'] . '°C'],
                    ['Humidity', $realData['humidity'] . '%'],
                    ['Wind Speed', $realData['wind_speed'] . ' m/s'],
                    ['Description', $realData['description']]
                ]);
            } else {
                $this->warn("Could not fetch real weather for location: {$location}");
                $this->line('Note: Set OPENWEATHER_API_KEY in .env to use real weather data');
            }
        }

        // Test adventure weather generation
        $this->line('=== ADVENTURE WEATHER ===');
        $adventureWeather = $weatherService->getWeatherForAdventure($road, 'test-seed-123', $location, $useRealWeather);
        $this->displayWeatherInfo($adventureWeather, "Adventure ({$road} road)");

        // Test weather effects
        $this->line('=== WEATHER EFFECTS ===');
        $weatherTypes = $weatherService->getAvailableWeatherTypes();
        foreach ($weatherTypes as $type) {
            $effects = $weatherService->getWeatherEffects($type);
            if (!empty($effects['combat_effects'])) {
                $this->line("{$type}: " . json_encode($effects['combat_effects']));
            }
        }

        // Test weather transitions
        $this->line('=== WEATHER TRANSITIONS ===');
        $currentWeather = $simulatedWeather['type'];
        for ($hours = 1; $hours <= 6; $hours++) {
            $newWeather = $weatherService->simulateWeatherTransition($currentWeather, $hours);
            $this->line("After {$hours} hour(s): {$currentWeather} → {$newWeather}");
        }

        // Test seasonal weather modifiers
        $this->line('=== SEASONAL WEATHER MODIFIERS ===');
        if (isset($season['weather_modifiers'])) {
            foreach ($season['weather_modifiers'] as $weather => $modifier) {
                $this->line("{$weather}: {$modifier}x chance");
            }
        }

        $this->info('Weather system test completed successfully!');
    }

    private function displayWeatherInfo(array $weather, string $source): void
    {
        $this->table(['Property', 'Value'], [
            ['Source', $source],
            ['Type', $weather['type']],
            ['Name', $weather['name']],
            ['Description', $weather['description']],
            ['Visibility', ($weather['visibility'] ?? 1.0) * 100 . '%'],
            ['Movement Speed', ($weather['movement_speed'] ?? 1.0) * 100 . '%'],
            ['Duration', ($weather['duration_hours'] ?? 'Unknown') . ' hours'],
            ['Effects Count', count($weather['effects'] ?? [])]
        ]);

        if (!empty($weather['effects'])) {
            $this->line('Combat Effects:');
            foreach ($weather['effects'] as $effect => $value) {
                $this->line("  - {$effect}: {$value}");
            }
        }
    }
}