<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class WeatherService
{
    private array $weatherEffects = [
        'clear' => [
            'name' => 'Clear Skies',
            'description' => 'Perfect weather for adventuring',
            'combat_effects' => [],
            'visibility' => 1.0,
            'movement_speed' => 1.0
        ],
        'rain' => [
            'name' => 'Rain',
            'description' => 'Steady rainfall affects fire and electric damage',
            'combat_effects' => [
                'fire_damage_multiplier' => 0.75,
                'electric_damage_multiplier' => 1.15,
                'accuracy_penalty' => -0.05
            ],
            'visibility' => 0.8,
            'movement_speed' => 0.9
        ],
        'snow' => [
            'name' => 'Snow',
            'description' => 'Cold weather slows movement but enhances ice attacks',
            'combat_effects' => [
                'ice_damage_multiplier' => 1.25,
                'fire_damage_multiplier' => 0.85,
                'movement_speed_penalty' => -0.15
            ],
            'visibility' => 0.7,
            'movement_speed' => 0.85
        ],
        'fog' => [
            'name' => 'Fog',
            'description' => 'Dense fog reduces visibility and ranged accuracy',
            'combat_effects' => [
                'ranged_accuracy_penalty' => -0.25,
                'stealth_bonus' => 0.2,
                'surprise_attack_chance' => 1.3
            ],
            'visibility' => 0.5,
            'movement_speed' => 0.95
        ],
        'storm' => [
            'name' => 'Storm',
            'description' => 'Violent storms with lightning and strong winds',
            'combat_effects' => [
                'electric_damage_multiplier' => 1.5,
                'ranged_accuracy_penalty' => -0.3,
                'spell_failure_chance' => 0.1
            ],
            'visibility' => 0.4,
            'movement_speed' => 0.8
        ],
        'sandstorm' => [
            'name' => 'Sandstorm',
            'description' => 'Swirling sand reduces visibility and damages equipment',
            'combat_effects' => [
                'visibility_penalty' => -0.4,
                'equipment_durability_loss' => 0.05,
                'earth_damage_multiplier' => 1.2
            ],
            'visibility' => 0.3,
            'movement_speed' => 0.75
        ],
        'blizzard' => [
            'name' => 'Blizzard',
            'description' => 'Severe snowstorm with freezing winds',
            'combat_effects' => [
                'ice_damage_multiplier' => 1.4,
                'fire_damage_multiplier' => 0.6,
                'movement_speed_penalty' => -0.3,
                'cold_damage_per_turn' => 2
            ],
            'visibility' => 0.2,
            'movement_speed' => 0.7
        ]
    ];

    private array $seasonalEffects = [
        'spring' => [
            'name' => 'Spring',
            'description' => 'A time of renewal and growth, with frequent rains nurturing new life',
            'duration_weeks' => 13,
            'effects' => [
                'npc_migration_chance' => 1.5,
                'crafting_material_bonus' => 1.2,
                'healing_effectiveness' => 1.1,
                'growth_events' => true
            ],
            'weather_modifiers' => [
                'rain' => 1.3,
                'clear' => 1.1,
                'storm' => 0.8
            ]
        ],
        'summer' => [
            'name' => 'Summer',
            'description' => 'Hot days bring increased activity and challenges across the grasslands',
            'duration_weeks' => 13,
            'effects' => [
                'combat_encounter_multiplier' => 1.25,
                'heat_exhaustion_chance' => 0.1,
                'fire_damage_bonus' => 1.15,
                'festival_events' => true
            ],
            'weather_modifiers' => [
                'clear' => 1.4,
                'storm' => 1.2,
                'rain' => 0.7
            ]
        ],
        'autumn' => [
            'name' => 'Autumn',
            'description' => 'Harvest season brings bounty and preparation for the cold months ahead',
            'duration_weeks' => 13,
            'effects' => [
                'harvest_bonus' => 1.5,
                'trading_bonus' => 1.3,
                'rare_material_chance' => 1.2,
                'migration_events' => true
            ],
            'weather_modifiers' => [
                'fog' => 1.3,
                'rain' => 1.1,
                'clear' => 0.9
            ]
        ],
        'winter' => [
            'name' => 'Winter',
            'description' => 'Cold months test survival skills while offering unique opportunities',
            'duration_weeks' => 13,
            'effects' => [
                'survival_challenge' => true,
                'npc_activity_reduction' => 0.7,
                'ice_damage_bonus' => 1.25,
                'resource_scarcity' => 0.8
            ],
            'weather_modifiers' => [
                'snow' => 1.5,
                'blizzard' => 1.2,
                'clear' => 0.6
            ]
        ]
    ];

    public function getCurrentWeather(?string $location = null, bool $useRealWeather = false): array
    {
        if ($useRealWeather && $location) {
            $realWeather = $this->fetchRealWorldWeather($location);
            if ($realWeather) {
                return $this->mapRealWeatherToGame($realWeather);
            }
        }

        return $this->generateSimulatedWeather();
    }

    public function getCurrentSeason(): array
    {
        $dayOfYear = Carbon::now()->dayOfYear;
        $seasonStartDays = [
            'spring' => 80,  // ~March 21
            'summer' => 172, // ~June 21
            'autumn' => 266, // ~September 23
            'winter' => 355  // ~December 21
        ];

        $currentSeason = 'winter'; // Default
        foreach ($seasonStartDays as $season => $startDay) {
            if ($dayOfYear >= $startDay) {
                $currentSeason = $season;
            }
        }

        // Handle winter wraparound
        if ($dayOfYear < $seasonStartDays['spring']) {
            $currentSeason = 'winter';
        }

        return array_merge(
            ['season' => $currentSeason],
            $this->seasonalEffects[$currentSeason]
        );
    }

    public function getWeatherForAdventure(string $road, string $seed, ?string $location = null, bool $useRealWeather = false): array
    {
        // Use seed for deterministic generation in adventures
        $this->setSeed($seed);
        
        if ($useRealWeather && $location) {
            $realWeather = $this->fetchRealWorldWeather($location);
            if ($realWeather) {
                $gameWeather = $this->mapRealWeatherToGame($realWeather);
                return $this->applyRoadWeatherModifiers($gameWeather, $road);
            }
        }

        return $this->generateAdventureWeather($road);
    }

    public function fetchRealWorldWeather(string $location): ?array
    {
        $cacheKey = "weather_" . md5($location);
        
        return Cache::remember($cacheKey, 600, function () use ($location) { // 10 minute cache
            try {
                $apiKey = env('OPENWEATHER_API_KEY');
                if (!$apiKey) {
                    return null;
                }

                $response = Http::timeout(10)->get('https://api.openweathermap.org/data/2.5/weather', [
                    'q' => $location,
                    'appid' => $apiKey,
                    'units' => 'metric'
                ]);

                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to fetch real weather data', [
                    'location' => $location,
                    'error' => $e->getMessage()
                ]);
            }

            return null;
        });
    }

    public function mapRealWeatherToGame(array $realWeatherData): array
    {
        $weatherId = $realWeatherData['weather'][0]['id'] ?? 800;
        $main = $realWeatherData['weather'][0]['main'] ?? 'Clear';
        $description = $realWeatherData['weather'][0]['description'] ?? 'clear sky';
        $temp = $realWeatherData['main']['temp'] ?? 20;
        $humidity = $realWeatherData['main']['humidity'] ?? 50;
        $windSpeed = $realWeatherData['wind']['speed'] ?? 0;

        // Map OpenWeatherMap conditions to game weather
        $gameWeatherType = $this->mapWeatherCondition($weatherId, $main, $temp, $windSpeed);
        $weatherData = $this->weatherEffects[$gameWeatherType];

        return [
            'type' => $gameWeatherType,
            'name' => $weatherData['name'],
            'description' => $weatherData['description'] . " (Real weather from: {$description})",
            'effects' => $weatherData['combat_effects'],
            'visibility' => $weatherData['visibility'],
            'movement_speed' => $weatherData['movement_speed'],
            'real_weather_data' => [
                'temperature' => $temp,
                'humidity' => $humidity,
                'wind_speed' => $windSpeed,
                'description' => $description,
                'location' => $realWeatherData['name'] ?? 'Unknown'
            ],
            'duration_hours' => rand(2, 8)
        ];
    }

    private function mapWeatherCondition(int $weatherId, string $main, float $temp, float $windSpeed): string
    {
        // OpenWeatherMap ID ranges
        if ($weatherId >= 200 && $weatherId < 300) {
            return $windSpeed > 10 ? 'storm' : 'rain';
        }
        if ($weatherId >= 300 && $weatherId < 600) {
            return 'rain';
        }
        if ($weatherId >= 600 && $weatherId < 700) {
            return $temp < -5 ? 'blizzard' : 'snow';
        }
        if ($weatherId >= 700 && $weatherId < 800) {
            if ($weatherId == 781) return 'storm'; // Tornado
            if ($weatherId == 751 || $weatherId == 761) return 'sandstorm'; // Sand/dust
            return 'fog';
        }
        if ($weatherId == 800) {
            return 'clear';
        }
        if ($weatherId > 800) {
            return $windSpeed > 15 ? 'storm' : 'clear';
        }

        return 'clear';
    }

    private function generateSimulatedWeather(): array
    {
        $currentSeason = $this->getCurrentSeason();
        $weatherTypes = array_keys($this->weatherEffects);
        
        // Apply seasonal modifiers to weather chances
        $weightedWeather = $this->applySeasonalWeatherModifiers($weatherTypes, $currentSeason);
        $selectedWeather = $this->weightedRandomWeather($weightedWeather);
        
        $weatherData = $this->weatherEffects[$selectedWeather];
        
        return [
            'type' => $selectedWeather,
            'name' => $weatherData['name'],
            'description' => $weatherData['description'],
            'effects' => $weatherData['combat_effects'],
            'visibility' => $weatherData['visibility'],
            'movement_speed' => $weatherData['movement_speed'],
            'season' => $currentSeason['season'],
            'seasonal_effects' => $currentSeason['effects'],
            'duration_hours' => rand(1, 6)
        ];
    }

    private function generateAdventureWeather(string $road): array
    {
        $roadWeatherChances = [
            'north' => ['snow' => 0.4, 'blizzard' => 0.2, 'fog' => 0.2, 'clear' => 0.2],
            'south' => ['clear' => 0.5, 'sandstorm' => 0.2, 'storm' => 0.2, 'rain' => 0.1],
            'east' => ['rain' => 0.3, 'fog' => 0.3, 'clear' => 0.3, 'storm' => 0.1],
            'west' => ['clear' => 0.4, 'fog' => 0.2, 'snow' => 0.2, 'rain' => 0.2]
        ];

        $chances = $roadWeatherChances[$road] ?? $roadWeatherChances['north'];
        $selectedWeather = $this->weightedRandomFromChances($chances);
        $weatherData = $this->weatherEffects[$selectedWeather];

        return [
            'type' => $selectedWeather,
            'name' => $weatherData['name'],
            'description' => $weatherData['description'],
            'effects' => $weatherData['combat_effects'],
            'visibility' => $weatherData['visibility'],
            'movement_speed' => $weatherData['movement_speed'],
            'road_influenced' => true,
            'duration_hours' => rand(2, 8)
        ];
    }

    private function applyRoadWeatherModifiers(array $weatherData, string $road): array
    {
        // Enhance weather effects based on road specialization
        $roadModifiers = [
            'north' => ['ice_damage_multiplier' => 1.1, 'cold_resistance' => 0.9],
            'south' => ['fire_damage_multiplier' => 1.1, 'heat_resistance' => 0.9],
            'east' => ['nature_affinity' => 1.1, 'magic_enhancement' => 1.05],
            'west' => ['earth_damage_multiplier' => 1.1, 'stability_bonus' => 1.05]
        ];

        if (isset($roadModifiers[$road])) {
            $weatherData['effects'] = array_merge(
                $weatherData['effects'] ?? [],
                $roadModifiers[$road]
            );
        }

        return $weatherData;
    }

    private function applySeasonalWeatherModifiers(array $weatherTypes, array $season): array
    {
        $modifiers = $season['weather_modifiers'] ?? [];
        $weights = [];

        foreach ($weatherTypes as $weather) {
            $baseWeight = 1.0;
            $modifier = $modifiers[$weather] ?? 1.0;
            $weights[$weather] = $baseWeight * $modifier;
        }

        return $weights;
    }

    private function weightedRandomWeather(array $weights): string
    {
        $totalWeight = array_sum($weights);
        $random = mt_rand(1, intval($totalWeight * 100)) / 100;

        foreach ($weights as $weather => $weight) {
            $random -= $weight;
            if ($random <= 0) {
                return $weather;
            }
        }

        return array_keys($weights)[0];
    }

    private function weightedRandomFromChances(array $chances): string
    {
        $random = mt_rand(1, 100) / 100;
        $cumulative = 0;

        foreach ($chances as $weather => $chance) {
            $cumulative += $chance;
            if ($random <= $cumulative) {
                return $weather;
            }
        }

        return array_keys($chances)[0];
    }

    private function setSeed(string $seed): void
    {
        $numericSeed = crc32($seed);
        mt_srand($numericSeed);
    }

    public function getWeatherEffects(string $weatherType): array
    {
        return $this->weatherEffects[$weatherType] ?? $this->weatherEffects['clear'];
    }

    public function getSeasonalEffects(): array
    {
        return $this->getCurrentSeason();
    }

    public function getAvailableWeatherTypes(): array
    {
        return array_keys($this->weatherEffects);
    }

    public function getCombatModifiers(array $weatherData): array
    {
        $modifiers = [
            'attack_modifier' => 0,
            'damage_modifier' => 0,
            'initiative_modifier' => 0,
            'visibility_modifier' => 0
        ];

        $weatherType = $weatherData['type'] ?? $weatherData['condition'] ?? 'clear';
        $effects = $this->weatherEffects[$weatherType] ?? [];

        // Apply weather-specific combat modifiers
        switch ($weatherType) {
            case 'rain':
                $modifiers['attack_modifier'] = -1; // Harder to hit in rain
                $modifiers['visibility_modifier'] = -2;
                break;
            case 'storm':
                $modifiers['attack_modifier'] = -2; // Much harder to hit in storms
                $modifiers['initiative_modifier'] = -1; // Slower reactions
                $modifiers['visibility_modifier'] = -3;
                break;
            case 'snow':
                $modifiers['attack_modifier'] = -1;
                $modifiers['initiative_modifier'] = -1; // Cold slows movement
                $modifiers['visibility_modifier'] = -2;
                break;
            case 'fog':
                $modifiers['attack_modifier'] = -3; // Very hard to see targets
                $modifiers['visibility_modifier'] = -4;
                break;
            case 'wind':
                $modifiers['attack_modifier'] = -1; // Wind affects projectiles
                break;
            case 'sunny':
                $modifiers['initiative_modifier'] = 1; // Clear conditions boost morale
                break;
            case 'clear':
            default:
                // No modifiers for clear weather
                break;
        }

        // Apply temperature effects
        $temp = $weatherData['temperature'] ?? $weatherData['temp'] ?? 20;
        if ($temp < 0) {
            $modifiers['initiative_modifier'] -= 1; // Extreme cold
            $modifiers['damage_modifier'] -= 1; // Numb fingers
        } elseif ($temp > 35) {
            $modifiers['initiative_modifier'] -= 1; // Extreme heat
        }

        // Apply seasonal modifiers
        $season = $weatherData['season'] ?? $this->getCurrentSeason()['name'];
        switch ($season) {
            case 'winter':
                $modifiers['damage_modifier'] -= 1; // Cold weather reduces effectiveness
                break;
            case 'summer':
                $modifiers['initiative_modifier'] += 1; // Warm weather improves reflexes
                break;
        }

        return $modifiers;
    }

    public function simulateWeatherTransition(string $currentWeather, int $hoursElapsed = 1): string
    {
        // Weather transition probabilities
        $transitions = [
            'clear' => ['clear' => 0.7, 'rain' => 0.15, 'fog' => 0.1, 'storm' => 0.05],
            'rain' => ['rain' => 0.6, 'clear' => 0.2, 'storm' => 0.15, 'fog' => 0.05],
            'snow' => ['snow' => 0.65, 'blizzard' => 0.15, 'clear' => 0.15, 'fog' => 0.05],
            'fog' => ['fog' => 0.5, 'clear' => 0.3, 'rain' => 0.15, 'storm' => 0.05],
            'storm' => ['storm' => 0.4, 'rain' => 0.3, 'clear' => 0.2, 'fog' => 0.1],
            'sandstorm' => ['sandstorm' => 0.6, 'clear' => 0.25, 'fog' => 0.1, 'storm' => 0.05],
            'blizzard' => ['blizzard' => 0.5, 'snow' => 0.3, 'clear' => 0.15, 'fog' => 0.05]
        ];

        $chances = $transitions[$currentWeather] ?? $transitions['clear'];
        
        // Multiple hours increase transition probability
        for ($i = 1; $i < $hoursElapsed; $i++) {
            // Reduce chance of staying the same weather
            $chances[$currentWeather] *= 0.9;
            // Redistribute to other weather types
            $remaining = 1.0 - $chances[$currentWeather];
            $others = array_filter($chances, fn($key) => $key !== $currentWeather, ARRAY_FILTER_USE_KEY);
            $scale = $remaining / array_sum($others);
            foreach ($others as $weather => $chance) {
                $chances[$weather] = $chance * $scale;
            }
        }

        return $this->weightedRandomFromChances($chances);
    }
}