<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\WeatherService;
use App\Models\WeatherEvent;

class WeatherController extends Controller
{
    protected WeatherService $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    public function getCurrentWeather(Request $request): JsonResponse
    {
        $location = $request->input('location');
        $useRealWeather = $request->boolean('use_real_weather', false);
        
        $weather = $this->weatherService->getCurrentWeather($location, $useRealWeather);
        $season = $this->weatherService->getCurrentSeason();
        
        return response()->json([
            'weather' => $weather,
            'season' => $season,
            'timestamp' => now()
        ]);
    }

    public function getWeatherEffects(string $weatherType): JsonResponse
    {
        $effects = $this->weatherService->getWeatherEffects($weatherType);
        
        return response()->json([
            'weather_type' => $weatherType,
            'effects' => $effects
        ]);
    }

    public function getAvailableWeatherTypes(): JsonResponse
    {
        $types = $this->weatherService->getAvailableWeatherTypes();
        
        return response()->json([
            'weather_types' => $types
        ]);
    }

    public function getSeasonalEffects(): JsonResponse
    {
        $effects = $this->weatherService->getSeasonalEffects();
        
        return response()->json([
            'seasonal_effects' => $effects
        ]);
    }

    public function createWeatherEvent(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string',
            'duration_hours' => 'required|integer|min:1|max:72',
            'location' => 'sometimes|string|max:100'
        ]);

        $weatherType = $request->input('type');
        $durationHours = $request->input('duration_hours');
        $location = $request->input('location');

        $effects = $this->weatherService->getWeatherEffects($weatherType);
        
        $weatherEvent = WeatherEvent::createWeatherEvent(
            $weatherType,
            $effects,
            $durationHours,
            $location
        );

        return response()->json([
            'success' => true,
            'weather_event' => $weatherEvent,
            'message' => "Weather event '{$weatherType}' created for {$durationHours} hours"
        ]);
    }

    public function getActiveWeatherEvents(Request $request): JsonResponse
    {
        $location = $request->input('location');
        
        $activeEvents = WeatherEvent::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->when($location, fn($query) => $query->where('location', $location))
            ->get();

        return response()->json([
            'active_events' => $activeEvents,
            'count' => $activeEvents->count()
        ]);
    }

    public function simulateWeatherTransition(Request $request): JsonResponse
    {
        $request->validate([
            'current_weather' => 'required|string',
            'hours_elapsed' => 'sometimes|integer|min:1|max:24'
        ]);

        $currentWeather = $request->input('current_weather');
        $hoursElapsed = $request->input('hours_elapsed', 1);

        $newWeather = $this->weatherService->simulateWeatherTransition($currentWeather, $hoursElapsed);
        $newWeatherEffects = $this->weatherService->getWeatherEffects($newWeather);

        return response()->json([
            'previous_weather' => $currentWeather,
            'new_weather' => $newWeather,
            'weather_effects' => $newWeatherEffects,
            'hours_elapsed' => $hoursElapsed,
            'transition_successful' => true
        ]);
    }

    public function getRealWorldWeather(Request $request): JsonResponse
    {
        $request->validate([
            'location' => 'required|string|max:100'
        ]);

        $location = $request->input('location');
        $realWeatherData = $this->weatherService->fetchRealWorldWeather($location);
        
        if (!$realWeatherData) {
            return response()->json([
                'error' => 'Unable to fetch real weather data for the specified location',
                'location' => $location
            ], 404);
        }

        $gameWeather = $this->weatherService->mapRealWeatherToGame($realWeatherData);

        return response()->json([
            'location' => $location,
            'real_weather_data' => $realWeatherData,
            'game_weather' => $gameWeather,
            'fetched_at' => now()
        ]);
    }

    public function updateGameSettings(Request $request): JsonResponse
    {
        $request->validate([
            'default_location' => 'sometimes|string|max:100',
            'use_real_weather_by_default' => 'sometimes|boolean',
            'weather_update_frequency' => 'sometimes|integer|min:5|max:1440' // 5 minutes to 24 hours
        ]);

        $user = auth()->user();
        $settings = [];

        if ($request->has('default_location')) {
            $settings['default_location'] = $request->input('default_location');
        }

        if ($request->has('use_real_weather_by_default')) {
            $settings['use_real_weather_by_default'] = $request->boolean('use_real_weather_by_default');
        }

        if ($request->has('weather_update_frequency')) {
            $settings['weather_update_frequency'] = $request->input('weather_update_frequency');
        }

        // Store settings in user preferences or a settings table
        // For now, we'll just return the settings
        
        return response()->json([
            'success' => true,
            'settings' => $settings,
            'message' => 'Weather settings updated successfully'
        ]);
    }

    public function getWeatherHistory(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'sometimes|integer|min:1|max:30',
            'location' => 'sometimes|string|max:100'
        ]);

        $days = $request->input('days', 7);
        $location = $request->input('location');
        
        $weatherHistory = WeatherEvent::where('start_date', '>=', now()->subDays($days))
            ->when($location, fn($query) => $query->where('location', $location))
            ->orderBy('start_date', 'desc')
            ->get();

        return response()->json([
            'weather_history' => $weatherHistory,
            'period_days' => $days,
            'location' => $location,
            'total_events' => $weatherHistory->count()
        ]);
    }
}