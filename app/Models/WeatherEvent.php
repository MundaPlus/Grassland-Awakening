<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WeatherEvent extends Model
{
    protected $fillable = [
        'type',
        'start_date',
        'end_date',
        'effects_json',
        'location',
        'real_weather_data'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'effects_json' => 'array',
        'real_weather_data' => 'array'
    ];

    public function isActive(): bool
    {
        $now = Carbon::now();
        return $now->between($this->start_date, $this->end_date);
    }

    public function getRemainingDuration(): int
    {
        if (!$this->isActive()) {
            return 0;
        }
        
        return Carbon::now()->diffInHours($this->end_date);
    }

    public function getEffects(): array
    {
        return $this->effects_json ?? [];
    }

    public static function getCurrentWeatherEvent(?string $location = null): ?self
    {
        return self::where('start_date', '<=', Carbon::now())
                   ->where('end_date', '>=', Carbon::now())
                   ->when($location, fn($query) => $query->where('location', $location))
                   ->first();
    }

    public static function createWeatherEvent(string $type, array $effects, int $durationHours, ?string $location = null, ?array $realWeatherData = null): self
    {
        return self::create([
            'type' => $type,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addHours($durationHours),
            'effects_json' => $effects,
            'location' => $location,
            'real_weather_data' => $realWeatherData
        ]);
    }
}
