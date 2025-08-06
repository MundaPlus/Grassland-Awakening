@extends('game.layout')

@section('title', 'Dashboard')
@section('meta_description', 'Your character dashboard in Grassland Awakening - view stats, weather, village info, and achievements.')

@push('styles')
<style>
    /* Full-screen immersive layout */
    body {
        overflow: hidden;
    }
    
    .dashboard-background {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-image: url('/img/backgrounds/village.png');
        z-index: 1;
    }
    
    .dashboard-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: linear-gradient(rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.2));
        z-index: 2;
    }
    
    .dashboard-ui-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 10;
        pointer-events: none;
    }
    
    .dashboard-ui-container > * {
        pointer-events: all;
    }
    
    /* Welcome Panel - Top Center */
    .welcome-panel {
        position: absolute;
        top: 70px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(40, 167, 69, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 15px 25px;
        color: white;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }
    
    /* Character Status - Top Left */
    .character-status-panel {
        position: absolute;
        top: 70px;
        left: 20px;
        width: 320px;
        background: rgba(40, 167, 69, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 15px;
        color: white;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }
    
    /* Village Info - Top Right */
    .village-info-panel {
        position: absolute;
        top: 70px;
        right: 20px;
        width: 320px;
        background: rgba(23, 162, 184, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 15px;
        color: white;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }
    
    /* Weather Panel - Left Side */
    .weather-panel {
        position: absolute;
        top: 420px;
        left: 20px;
        width: 280px;
        background: rgba(23, 162, 184, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 15px;
        color: white;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        text-align: center;
    }
    
    /* Quick Actions - Bottom Center */
    .quick-actions-panel {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(33, 37, 41, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }
    
    /* Achievements Panel - Bottom Right */
    .achievements-panel {
        position: absolute;
        bottom: 20px;
        right: 20px;
        width: 350px;
        background: rgba(255, 193, 7, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 15px;
        color: #333;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }
    
    /* Progress bars */
    .dashboard-progress {
        background: rgba(0, 0, 0, 0.3);
        border-radius: 10px;
        overflow: hidden;
        margin: 5px 0;
        height: 12px;
    }
    
    .dashboard-progress-fill {
        height: 100%;
        border-radius: 10px;
        transition: width 0.3s ease;
    }
    
    .health-fill {
        background: linear-gradient(90deg, #dc3545, #e74c3c);
    }
    
    .xp-fill {
        background: linear-gradient(90deg, #28a745, #20c997);
    }
    
    /* Action buttons */
    .dashboard-btn {
        background: linear-gradient(135deg, #495057, #6c757d);
        border: none;
        color: white;
        padding: 10px 15px;
        margin: 5px;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        text-decoration: none;
        display: inline-block;
    }
    
    .dashboard-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        color: white;
    }
    
    .dashboard-btn.primary { background: linear-gradient(135deg, #007bff, #0056b3); }
    .dashboard-btn.success { background: linear-gradient(135deg, #28a745, #1e7e34); }
    .dashboard-btn.warning { background: linear-gradient(135deg, #ffc107, #e0a800); }
    .dashboard-btn.danger { background: linear-gradient(135deg, #dc3545, #c82333); }
    
    /* Stat displays */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        margin-top: 10px;
    }
    
    .stat-item {
        text-align: center;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 5px;
    }
    
    .stat-value {
        font-weight: bold;
        font-size: 1.1em;
    }
    
    .stat-label {
        font-size: 0.8em;
        opacity: 0.8;
    }
    
    /* Responsive Design */
    @media (max-width: 1200px) {
        .character-status-panel, .village-info-panel {
            width: 280px;
        }
        
        .achievements-panel {
            width: 300px;
        }
        
        .weather-panel {
            width: 250px;
        }
    }
    
    @media (max-width: 768px) {
        .character-status-panel {
            top: 70px;
            left: 10px;
            width: 200px;
            padding: 10px;
        }
        
        .village-info-panel {
            top: 70px;
            right: 10px;
            width: 200px;
            padding: 10px;
        }
        
        .weather-panel {
            top: 300px;
            left: 10px;
            width: 180px;
            padding: 10px;
        }
        
        .quick-actions-panel {
            bottom: 10px;
            left: 10px;
            right: 10px;
            transform: none;
            padding: 10px;
        }
        
        .achievements-panel {
            display: none;
        }
        
        .welcome-panel {
            padding: 10px 15px;
        }
    }
    
    @media (max-width: 576px) {
        .character-status-panel, .village-info-panel {
            width: 160px;
            font-size: 0.9rem;
        }
        
        .weather-panel {
            width: 150px;
            font-size: 0.9rem;
        }
        
        .dashboard-btn {
            padding: 8px 12px;
            font-size: 0.9rem;
        }
    }
    
    /* Dark theme adjustments */
    [data-bs-theme="dark"] .dashboard-ui-container .character-status-panel {
        background: rgba(40, 167, 69, 0.8);
    }
    
    [data-bs-theme="dark"] .dashboard-ui-container .village-info-panel,
    [data-bs-theme="dark"] .dashboard-ui-container .weather-panel {
        background: rgba(23, 162, 184, 0.8);
    }
    
    [data-bs-theme="dark"] .dashboard-ui-container .welcome-panel {
        background: rgba(40, 167, 69, 0.8);
    }
</style>
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