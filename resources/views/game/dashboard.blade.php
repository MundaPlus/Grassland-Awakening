@extends('game.layout')

@section('title', 'Dashboard')
@section('meta_description', 'Your character dashboard in Grassland Awakening - view stats, weather, village info, and achievements.')

@push('styles')
@vite('resources/css/game/dashboard.css')
@endpush

@section('content')
<!-- Dashboard Background -->
<div class="dashboard-background"></div>
<div class="dashboard-overlay"></div>

<!-- Dashboard UI Overlay System -->
<div class="dashboard-ui-container">
    <!-- Welcome Panel - Top Center -->
    <div class="welcome-panel">
        <h1 class="mb-1">Welcome back, {{ $player->character_name }}! 🏠</h1>
        <p class="mb-0 small">Your village in the Grasslands awaits your leadership</p>
    </div>

    <!-- Character Status - Top Left -->
    <div class="character-status-panel">
        <div class="mb-2">
            <h2 class="h6 mb-2">👤 Character Overview</h2>
            @php
                $playerHP = $player->hp;
                $playerMaxHP = $player->max_hp;
                $healthPercent = $playerMaxHP > 0 ? ($playerHP / $playerMaxHP) * 100 : 0;
                $nextLevelXP = $player->calculateExperienceToNextLevel();
                $currentXP = $player->experience;
                $xpProgress = min(100, ($currentXP / $nextLevelXP) * 100);
            @endphp
            
            <!-- Health Bar -->
            <div class="mb-2">
                <div class="small mb-1">Health</div>
                <div class="dashboard-progress">
                    <div class="dashboard-progress-fill health-fill" style="width: {{ $healthPercent }}%"></div>
                </div>
                <div class="small text-center">{{ $playerHP }}/{{ $playerMaxHP }}</div>
            </div>
            
            <!-- XP Bar -->
            <div class="mb-2">
                <div class="small mb-1">Experience</div>
                <div class="dashboard-progress">
                    <div class="dashboard-progress-fill xp-fill" style="width: {{ $xpProgress }}%"></div>
                </div>
                <div class="small text-center">{{ number_format($currentXP) }} / {{ number_format($nextLevelXP) }}</div>
            </div>
            
            <!-- Stats Grid -->
            <div class="stat-grid">
                <div class="stat-item">
                    <div class="stat-value">{{ $player->level }}</div>
                    <div class="stat-label">LVL</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $player->getTotalAC() }}</div>
                    <div class="stat-label">AC</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ number_format($player->persistent_currency) }}</div>
                    <div class="stat-label">💰</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $player->str }}</div>
                    <div class="stat-label">STR</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $player->dex }}</div>
                    <div class="stat-label">DEX</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $player->con }}</div>
                    <div class="stat-label">CON</div>
                </div>
            </div>
            
            @if($player->unallocated_stat_points > 0 || $player->skill_points > 0)
                <div class="mt-2 p-2 rounded" style="background: rgba(255, 193, 7, 0.2); border: 1px solid rgba(255, 193, 7, 0.5);">
                    <div class="small">⚠️ Points to Allocate!</div>
                    @if($player->unallocated_stat_points > 0)
                        <div class="small">• {{ $player->unallocated_stat_points }} stat points</div>
                    @endif
                    @if($player->skill_points > 0)
                        <div class="small">• {{ $player->skill_points }} skill points</div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Village Info - Top Right -->
    <div class="village-info-panel">
        <div class="mb-2">
            <h2 class="h6 mb-2">🏘️ Your Village</h2>
            
            <div class="stat-grid">
                <div class="stat-item">
                    <div class="stat-value">{{ $village_info['level'] }}</div>
                    <div class="stat-label">Level</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $village_info['npc_count'] }}</div>
                    <div class="stat-label">NPCs</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ count($village_info['specializations']) }}</div>
                    <div class="stat-label">Specs</div>
                </div>
            </div>

            @if(!empty($village_info['specializations']) && count($village_info['specializations']) > 0)
                <div class="mt-2">
                    <div class="small mb-1">Specializations:</div>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach(array_slice($village_info['specializations']->toArray(), 0, 4) as $spec)
                            <span class="badge bg-light text-dark small">
                                {{ Str::limit($spec->getSpecializationName(), 10) }} {{ $spec->level }}
                            </span>
                        @endforeach
                        @if(count($village_info['specializations']) > 4)
                            <span class="badge bg-secondary small">+{{ count($village_info['specializations']) - 4 }}</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Weather Panel - Left Side -->
    <div class="weather-panel">
        <div class="mb-1">
            <div class="fw-bold small">🌤️ Weather</div>
        </div>
        <div style="font-size: 2rem; margin-bottom: 8px;">
            @if(isset($weather['type']))
                @switch($weather['type'])
                    @case('clear') ☀️ @break
                    @case('rain') 🌧️ @break
                    @case('storm') ⛈️ @break
                    @case('snow') ❄️ @break
                    @case('fog') 🌫️ @break
                    @default 🌤️
                @endswitch
            @else
                🌤️
            @endif
        </div>
        <div class="small fw-bold">{{ $weather['name'] ?? 'Pleasant Weather' }}</div>
        <div class="small">🍃 {{ ucfirst($season['name']) }}</div>
        @if(isset($weather['real_weather_data']['temperature']))
            <div class="small">🌡️ {{ $weather['real_weather_data']['temperature'] }}°C</div>
        @endif
    </div>

    <!-- Quick Actions - Bottom Center -->
    <div class="quick-actions-panel">
        <div class="mb-2 text-center text-white">
            <div class="fw-bold small">Quick Actions</div>
        </div>
        <div class="d-flex gap-2 flex-wrap justify-content-center">
            <a href="{{ route('game.adventures') }}" class="dashboard-btn danger">
                🗺️ Adventure
            </a>
            <a href="{{ route('game.character') }}" class="dashboard-btn primary">
                👤 Character
            </a>
            <a href="{{ route('game.inventory') }}" class="dashboard-btn warning">
                🎒 Inventory
            </a>
            <a href="{{ route('game.village') }}" class="dashboard-btn success">
                🏘️ Village
            </a>
            <a href="{{ route('game.skills') }}" class="dashboard-btn primary">
                🎯 Skills
            </a>
        </div>
    </div>

    <!-- Achievements Panel - Bottom Right -->
    <div class="achievements-panel">
        <div class="mb-2">
            <div class="fw-bold">🏆 Achievements</div>
        </div>
        <div class="d-flex align-items-center gap-3 mb-2">
            <div class="text-center">
                <div class="fw-bold">{{ $achievements['total_points'] ?? 0 }}</div>
                <small>Points</small>
            </div>
            <div class="text-center">
                <div class="fw-bold">{{ $achievements['achievement_count'] ?? 0 }}</div>
                <small>Unlocked</small>
            </div>
        </div>
        @if(!empty($achievements['unlocked']) && count($achievements['unlocked']) > 0)
            <div class="mb-2">
                <div class="d-flex gap-1 flex-wrap">
                    @foreach(array_slice($achievements['unlocked'], 0, 6) as $achievement)
                        <span title="{{ $achievement['name'] }}: {{ $achievement['description'] }}">{{ $achievement['icon'] }}</span>
                    @endforeach
                    @if(count($achievements['unlocked']) > 6)
                        <span class="small">+{{ count($achievements['unlocked']) - 6 }}</span>
                    @endif
                </div>
            </div>
        @endif
        <div class="text-center">
            <a href="{{ route('game.achievements') }}" class="dashboard-btn primary">
                View All
            </a>
        </div>
    </div>
</div>
@endsection