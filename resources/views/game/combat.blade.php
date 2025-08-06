@extends('game.layout')

@section('title', 'Combat - ' . $adventure->title)

@push('styles')
<style>
    /* Full-screen immersive layout */
    body {
        overflow: hidden;
    }
    
    .combat-background {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        z-index: 1;
    }
    
    .combat-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: linear-gradient(rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.2));
        z-index: 2;
    }
    
    .combat-ui-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 10;
        pointer-events: none;
    }
    
    .combat-ui-container > * {
        pointer-events: all;
    }
    
    /* Combat Header - Top Center */
    .combat-header-panel {
        position: absolute;
        top: 70px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(220, 53, 69, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 10px 20px;
        color: white;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }
    
    /* Player Status - Top Left */
    .player-status-panel {
        position: absolute;
        top: 70px;
        left: 20px;
        width: 300px;
        background: rgba(40, 167, 69, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 15px;
        color: white;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }
    
    /* Enemy Status - Top Right */
    .enemy-status-panel {
        position: absolute;
        top: 70px;
        right: 20px;
        width: 300px;
        background: rgba(220, 53, 69, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 15px;
        color: white;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }
    
    /* Combat Actions - Bottom Center */
    .combat-actions-panel {
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
    
    /* Combat Log - Bottom Left */
    .combat-log-panel {
        position: absolute;
        bottom: 20px;
        left: 20px;
        width: 450px;
        height: 200px;
        background: rgba(33, 37, 41, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 15px;
        color: white;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        overflow: hidden;
    }
    
    .combat-log-content {
        overflow-y: auto;
        padding-right: 5px;
    }
    
    /* Custom scrollbar for combat log */
    .combat-log-content::-webkit-scrollbar {
        width: 6px;
    }
    
    .combat-log-content::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 3px;
    }
    
    .combat-log-content::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.4);
        border-radius: 3px;
    }
    
    .combat-log-content::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.6);
    }
    
    /* Testing Tools - Bottom Right */
    .testing-tools-panel {
        position: absolute;
        bottom: 20px;
        right: 20px;
        background: rgba(255, 193, 7, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 15px;
        color: #333;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }
    
    /* Weather Effects - Top Left (below player) */
    .weather-panel {
        position: absolute;
        top: 280px;
        left: 20px;
        background: rgba(23, 162, 184, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 10px 15px;
        color: white;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }
    
    /* Progress bars */
    .combat-progress {
        background: rgba(0, 0, 0, 0.3);
        border-radius: 10px;
        overflow: hidden;
        margin: 5px 0;
        height: 12px;
    }
    
    .combat-progress-fill {
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
    .action-btn {
        background: linear-gradient(135deg, #495057, #6c757d);
        border: none;
        color: white;
        padding: 10px 15px;
        margin: 5px;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    }
    
    .action-btn.attack { background: linear-gradient(135deg, #dc3545, #c82333); }
    .action-btn.defend { background: linear-gradient(135deg, #17a2b8, #138496); }
    .action-btn.special { background: linear-gradient(135deg, #ffc107, #e0a800); }
    .action-btn.use-item { background: linear-gradient(135deg, #28a745, #20c997); }
    
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
    
    /* Enemy card styling */
    .enemy-card {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        padding: 10px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .enemy-card:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: scale(1.02);
    }
    
    .enemy-card.selected {
        border-color: #ffc107;
        background: rgba(255, 193, 7, 0.2);
    }
    
    .enemy-card.dead {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    /* Responsive Design */
    @media (max-width: 1200px) {
        .player-status-panel, .enemy-status-panel {
            width: 250px;
        }
        .combat-log-panel {
            width: 400px;
            height: 180px;
        }
    }
    
    @media (max-width: 768px) {
        .player-status-panel {
            top: 70px;
            left: 10px;
            width: 200px;
            padding: 10px;
        }
        
        .enemy-status-panel {
            top: 70px;
            right: 10px;
            width: 200px;
            padding: 10px;
        }
        
        .combat-log-panel {
            bottom: 200px;
            left: 10px;
            width: 320px;
            height: 140px;
            font-size: 0.9rem;
        }
        
        .combat-actions-panel {
            bottom: 10px;
            left: 10px;
            right: 10px;
            transform: none;
            padding: 10px;
        }
        
        .testing-tools-panel {
            display: none;
        }
        
        .weather-panel {
            top: 300px;
            left: 10px;
            padding: 8px 12px;
        }
    }
    
    @media (max-width: 576px) {
        .player-status-panel, .enemy-status-panel {
            width: 180px;
            font-size: 0.9rem;
        }
        
        .combat-header-panel {
            padding: 8px 15px;
            font-size: 0.9rem;
        }
        
        .action-btn {
            padding: 8px 12px;
            font-size: 0.9rem;
        }
    }
    
    /* Dark theme adjustments */
    [data-bs-theme="dark"] .combat-ui-container .player-status-panel {
        background: rgba(40, 167, 69, 0.8);
    }
    
    [data-bs-theme="dark"] .combat-ui-container .enemy-status-panel {
        background: rgba(220, 53, 69, 0.8);
    }
    
    [data-bs-theme="dark"] .combat-ui-container .combat-header-panel {
        background: rgba(220, 53, 69, 0.8);
    }
</style>
@endpush

@section('content')
<!-- Dynamic Combat Background -->
@php
    // Determine background based on location or adventure type
    $backgroundImage = 'grassland_day.png'; // default
    $location = $combat_data['location'] ?? $adventure->road ?? 'grassland';
    $timeOfDay = date('H') < 6 || date('H') > 18 ? 'night' : 'day';
    $weather = isset($weather['type']) ? $weather['type'] : 'clear';
    
    // Map locations to backgrounds with improved terrain detection
    $locationLower = strtolower($location);
    
    if (str_contains($locationLower, 'cave') || str_contains($locationLower, 'underground') || str_contains($locationLower, 'cavern')) {
        $backgroundImage = $timeOfDay === 'night' ? 'cave_night.png' : 'cave_day.png';
    } elseif (str_contains($locationLower, 'forest') || str_contains($locationLower, 'wood') || str_contains($locationLower, 'tree')) {
        if ($weather === 'rain') {
            $backgroundImage = 'forest_rain.png';
        } elseif ($weather === 'snow') {
            $backgroundImage = 'forest_snow.png';
        } else {
            $backgroundImage = $timeOfDay === 'night' ? 'forest_night.png' : 'forest_day.png';
        }
    } elseif (str_contains($locationLower, 'river') || str_contains($locationLower, 'water') || str_contains($locationLower, 'lake') || str_contains($locationLower, 'stream')) {
        if ($weather === 'rain') {
            $backgroundImage = 'riverbank_rain.png';
        } elseif ($weather === 'snow') {
            $backgroundImage = 'riverbank_snow.png';
        } else {
            $backgroundImage = $timeOfDay === 'night' ? 'riverbank_night.png' : 'riverbank_day.png';
        }
    } elseif (str_contains($locationLower, 'rock') || str_contains($locationLower, 'mountain') || str_contains($locationLower, 'cliff') || str_contains($locationLower, 'hill')) {
        if ($weather === 'rain') {
            $backgroundImage = 'rocky_rain.png';
        } elseif ($weather === 'snow') {
            $backgroundImage = 'rocky_snow.png';
        } else {
            $backgroundImage = $timeOfDay === 'night' ? 'rocky_night.png' : 'rocky_day.png';
        }
    } elseif (str_contains($locationLower, 'mine') || str_contains($locationLower, 'tunnel')) {
        $backgroundImage = 'mine.png';
    } else {
        // Default grassland (includes plains, meadow, field, etc.)
        if ($weather === 'rain') {
            $backgroundImage = 'grassland_rain.png';
        } elseif ($weather === 'snow') {
            $backgroundImage = 'grassland_snow.png';
        } else {
            $backgroundImage = $timeOfDay === 'night' ? 'grasland_night.png' : 'grassland_day.png';
        }
    }
@endphp

<div class="combat-background" style="background-image: url('/img/backgrounds/{{ $backgroundImage }}')"></div>
<div class="combat-overlay"></div>

<!-- Combat UI Overlay System -->
<div class="combat-ui-container">
    <!-- Combat Header - Top Center -->
    <div class="combat-header-panel">
        <h1 class="h5 mb-0">⚔️ Combat Encounter</h1>
        <div class="small opacity-75">{{ $adventure->title }} - Round {{ $combat_data['round'] ?? 1 }}</div>
    </div>

    <!-- Weather Effects - Top Left (below player) -->
    @if(isset($weatherEffects))
    <div class="weather-panel">
        <div class="d-flex align-items-center">
            <span class="me-2" aria-hidden="true">🌧️</span>
            <div>
                <div class="fw-bold small">Weather Effects</div>
                <div class="small">{{ Str::limit($weatherEffects, 40) }}</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Player Status - Top Left -->
    <div class="player-status-panel">
        <div class="mb-3">
            <h2 class="h6 mb-2">{{ $player->name }} (You)</h2>
            @php
                $playerHP = $combat_data['player']['hp'] ?? $player->hp;
                $playerMaxHP = $combat_data['player']['max_hp'] ?? $player->max_hp;
                $healthPercent = $playerMaxHP > 0 ? ($playerHP / $playerMaxHP) * 100 : 0;
            @endphp
            
            <!-- Health Bar -->
            <div class="mb-2">
                <div class="small mb-1">Health</div>
                <div class="combat-progress">
                    <div class="combat-progress-fill health-fill" style="width: {{ $healthPercent }}%"></div>
                </div>
                <div class="small text-center">{{ $playerHP }}/{{ $playerMaxHP }}</div>
            </div>
            
            <!-- Stats Grid -->
            <div class="stat-grid">
                <div class="stat-item">
                    <div class="stat-value">{{ $player->level }}</div>
                    <div class="stat-label">LVL</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $combat_data['player']['ac'] ?? $player->getTotalAC() }}</div>
                    <div class="stat-label">AC</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $combat_data['player']['stats']['str'] ?? $player->str }}</div>
                    <div class="stat-label">STR</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $combat_data['player']['stats']['dex'] ?? $player->dex }}</div>
                    <div class="stat-label">DEX</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $combat_data['player']['stats']['con'] ?? $player->con }}</div>
                    <div class="stat-label">CON</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $combat_data['player']['stats']['int'] ?? $player->int }}</div>
                    <div class="stat-label">INT</div>
                </div>
            </div>
            
            <!-- Status Effects -->
            @if(isset($combat_data['player_effects']) && !empty($combat_data['player_effects']))
            <div class="mt-3">
                <div class="small mb-2">Effects:</div>
                <div class="d-flex gap-1 flex-wrap">
                    @foreach($combat_data['player_effects'] as $effect)
                    <span class="badge bg-info small" title="{{ $effect['description'] ?? '' }}">
                        {{ Str::limit($effect['name'], 8) }}
                        @if(isset($effect['duration']))
                        ({{ $effect['duration']}})
                        @endif
                    </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Enemy Status - Top Right -->
    <div class="enemy-status-panel">
        <div class="mb-3">
            <h2 class="h6 mb-2">
                @if(isset($combat_data['enemies']))
                    Enemies ({{ count(array_filter($combat_data['enemies'], fn($e) => $e['status'] === 'alive')) }}/{{ count($combat_data['enemies']) }})
                @else
                    {{ $combat_data['enemy']['name'] ?? $enemy['name'] ?? 'Unknown Enemy' }}
                @endif
            </h2>
            
            <!-- Enemies List -->
            <div style="max-height: 250px; overflow-y: auto;">
                @if(isset($combat_data['enemies']))
                    @php
                        // Sort enemies so live ones appear on top
                        $sortedEnemies = collect($combat_data['enemies'])->sortBy(function($enemy) {
                            return $enemy['status'] === 'alive' ? 0 : 1;
                        });
                    @endphp
                    @foreach($sortedEnemies as $enemyId => $enemyData)
                        <div class="enemy-card mb-2 {{ $enemyData['status'] === 'dead' ? 'dead' : '' }} {{ $combat_data['selected_target'] === $enemyId ? 'selected' : '' }}" 
                             data-enemy-id="{{ $enemyId }}" 
                             onclick="selectTarget('{{ $enemyId }}')" 
                             style="padding: 8px; font-size: 0.9rem;">
                            <div class="d-flex align-items-center mb-2">
                                <div class="me-2" style="flex-shrink: 0;">
                                    @php
                                        // Map enemy names to images
                                        $enemyImage = 'goblin.png'; // default
                                        $enemyName = strtolower($enemyData['name'] ?? 'goblin');
                                        if (str_contains($enemyName, 'goblin')) $enemyImage = 'goblin.png';
                                        elseif (str_contains($enemyName, 'orc') || str_contains($enemyName, 'ork')) $enemyImage = 'ork.png';
                                        elseif (str_contains($enemyName, 'skeleton')) $enemyImage = 'skeleton.png';
                                        elseif (str_contains($enemyName, 'slime')) $enemyImage = 'slime.png';
                                        elseif (str_contains($enemyName, 'wolf')) $enemyImage = 'wolf.png';
                                        elseif (str_contains($enemyName, 'bandit')) $enemyImage = 'bandit.png';
                                        elseif (str_contains($enemyName, 'boss') || str_contains($enemyName, 'lord') || str_contains($enemyName, 'king') || str_contains($enemyName, 'demon')) $enemyImage = 'boss_1.png';
                                    @endphp
                                    <img src="{{ asset('img/enemies/' . $enemyImage) }}" 
                                         alt="{{ $enemyData['name'] }}"
                                         style="width: 32px; height: 32px; object-fit: contain; border-radius: 6px; {{ $enemyData['status'] === 'dead' ? 'filter: grayscale(100%);' : '' }}">
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold small">{{ Str::limit($enemyData['name'], 15) }}</div>
                                    @php
                                        $enemyHealth = $enemyData['health'] ?? $enemyData['hp'] ?? 0;
                                        $enemyMaxHealth = $enemyData['max_health'] ?? $enemyData['max_hp'] ?? 100;
                                        $healthPercent = $enemyMaxHealth > 0 ? ($enemyHealth / $enemyMaxHealth) * 100 : 0;
                                    @endphp
                                    <div class="combat-progress" style="height: 8px; margin: 3px 0;">
                                        <div class="combat-progress-fill" 
                                             style="width: {{ $healthPercent }}%; background: {{ $enemyData['status'] === 'dead' ? '#6c757d' : 'linear-gradient(90deg, #dc3545, #e74c3c)' }};"></div>
                                    </div>
                                    <div class="small">{{ $enemyHealth }}/{{ $enemyMaxHealth }} HP</div>
                                </div>
                                <div class="text-end">
                                    @if($enemyData['status'] === 'alive')
                                        <span style="color: #ffc107;">🎯</span>
                                    @else
                                        <span style="color: #6c757d;">💀</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <!-- Single Enemy (backward compatibility) -->
                    <div class="text-center mb-3">
                        @php
                            $enemyImage = 'goblin.png';
                            $enemyName = strtolower($combat_data['enemy']['name'] ?? $enemy['name'] ?? 'goblin');
                            if (str_contains($enemyName, 'goblin')) $enemyImage = 'goblin.png';
                            elseif (str_contains($enemyName, 'orc')) $enemyImage = 'ork.png';
                            elseif (str_contains($enemyName, 'skeleton')) $enemyImage = 'skeleton.png';
                            $enemyHealth = $enemy['health'] ?? $enemy['hp'] ?? 100;
                            $enemyMaxHealth = $enemy['max_health'] ?? $enemy['max_hp'] ?? 100;
                            $healthPercent = $enemyMaxHealth > 0 ? ($enemyHealth / $enemyMaxHealth) * 100 : 0;
                        @endphp
                        <img src="{{ asset('img/enemies/' . $enemyImage) }}" 
                             alt="{{ $combat_data['enemy']['name'] ?? $enemy['name'] ?? 'Enemy' }}"
                             style="width: 60px; height: 60px; object-fit: contain; border-radius: 8px; margin-bottom: 10px;">
                        <div class="combat-progress mb-2">
                            <div class="combat-progress-fill health-fill" style="width: {{ $healthPercent }}%"></div>
                        </div>
                        <div class="small mb-2">{{ $enemyHealth }}/{{ $enemyMaxHealth }} HP</div>
                        <div class="stat-grid">
                            <div class="stat-item">
                                <div class="stat-value">{{ $combat_data['enemy']['strength'] ?? $enemy['str'] ?? 10 }}</div>
                                <div class="stat-label">STR</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">{{ $combat_data['enemy']['intelligence'] ?? $enemy['int'] ?? 10 }}</div>
                                <div class="stat-label">INT</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">{{ $combat_data['enemy']['wisdom'] ?? $enemy['wis'] ?? 10 }}</div>
                                <div class="stat-label">WIS</div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Combat Actions - Bottom Center -->
    @if($combat_data['status'] === 'active')
    <div class="combat-actions-panel">
        <div class="mb-2 text-center text-white">
            <div class="fw-bold">Choose Your Action</div>
            @if(isset($combat_data['enemies']))
                <div class="small">
                    @if($combat_data['selected_target'])
                        Target: {{ $combat_data['enemies'][$combat_data['selected_target']]['name'] ?? 'Unknown' }}
                    @else
                        Select a target first
                    @endif
                </div>
            @endif
        </div>
        <div class="d-flex gap-2 flex-wrap justify-content-center">
            <button type="button" class="action-btn attack" 
                    onclick="performAction('attack')"
                    aria-label="Attack the enemy"
                    {{ isset($combat_data['enemies']) && !$combat_data['selected_target'] ? 'disabled' : '' }}>
                <span class="mb-1" aria-hidden="true">⚔️</span>
                <div>Attack</div>
            </button>
            <button type="button" class="action-btn defend" 
                    onclick="performAction('defend')"
                    aria-label="Defend against enemy attacks">
                <span class="mb-1" aria-hidden="true">🛡️</span>
                <div>Defend</div>
            </button>
            <button type="button" class="action-btn special" 
                    onclick="performAction('special')"
                    aria-label="Use special ability">
                <span class="mb-1" aria-hidden="true">✨</span>
                <div>Special</div>
            </button>
            <button type="button" class="action-btn use-item" 
                    onclick="performAction('use_item')"
                    aria-label="Use an item">
                <span class="mb-1" aria-hidden="true">🧪</span>
                <div>Item</div>
            </button>
        </div>
    </div>
    @endif

    <!-- Testing Tools - Bottom Right -->
    @if(app()->environment(['local', 'testing']) || (auth()->check() && auth()->user()->isAdmin()))
    <div class="testing-tools-panel">
        <div class="mb-2">
            <div class="fw-bold">Dev Tools</div>
            <div class="small">Testing Only</div>
        </div>
        <div class="d-flex flex-column gap-2">
            <button type="button" class="btn btn-success btn-sm" 
                    onclick="performTestAction('auto_complete_success')"
                    aria-label="Auto-complete combat with victory">
                <span class="me-1" aria-hidden="true">🏆</span>
                Win
            </button>
            <button type="button" class="btn btn-danger btn-sm" 
                    onclick="performTestAction('auto_complete_failure')"
                    aria-label="Auto-complete combat with defeat">
                <span class="me-1" aria-hidden="true">💀</span>
                Lose
            </button>
        </div>
    </div>
    @endif

    <!-- Combat Log - Bottom Left -->
    <div class="combat-log-panel">
        <div class="mb-1">
            <div class="fw-bold text-white small">Combat Log</div>
        </div>
        <div class="combat-log-content" id="combatLog" style="height: calc(100% - 35px);">
            @if(isset($combat_data['log']) && !empty($combat_data['log']))
                @foreach(array_slice($combat_data['log'], -8) as $logEntry)
                    <div class="mb-1 p-1 rounded small" style="background: rgba(255, 255, 255, 0.1); font-size: 0.8rem;">
                        <div class="opacity-75 small">R{{ $combat_data['round'] ?? 1 }}:</div>
                        <div>{{ Str::limit($logEntry['message'] ?? $logEntry, 70) }}</div>
                    </div>
                @endforeach
            @else
                <div class="mb-1 p-1 rounded small" style="background: rgba(255, 255, 255, 0.1); font-size: 0.8rem;">
                    <div class="opacity-75 small">R{{ $combat_data['round'] ?? 1 }}:</div>
                    <div>Combat begins! Choose your action.</div>
                </div>
            @endif
        </div>
    </div>

</div>

<!-- Combat Result Overlays -->
@if($combat_data['status'] === 'victory')
<div class="position-fixed top-50 start-50 translate-middle" style="z-index: 100; background: rgba(40, 167, 69, 0.95); backdrop-filter: blur(15px); border: 2px solid rgba(255, 255, 255, 0.3); border-radius: 15px; padding: 30px; color: white; text-align: center; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);">
    <span class="fs-1 text-warning mb-3" aria-hidden="true">🏆</span>
    <h3 class="mb-3">Victory!</h3>
    <p class="mb-3">You have defeated {{ $combat_data['enemy']['name'] ?? 'the enemy' }}!</p>
    
    @if(isset($combat_data['rewards']))
    <div class="rewards mb-3">
        <div class="fw-bold mb-2">Rewards Earned:</div>
        <div class="d-flex justify-content-center gap-2 flex-wrap">
            @if(isset($combat_data['rewards']['gold']) && $combat_data['rewards']['gold'] > 0)
            <span class="badge bg-warning text-dark">
                <span aria-hidden="true">💰</span> {{ $combat_data['rewards']['gold'] }} Gold
            </span>
            @endif
            @if(isset($combat_data['rewards']['experience']) && $combat_data['rewards']['experience'] > 0)
            <span class="badge bg-info">
                <span aria-hidden="true">⭐</span> {{ $combat_data['rewards']['experience'] }} XP
            </span>
            @endif
        </div>
    </div>
    @endif
    
    <a href="{{ route('game.adventure', $adventure->id) }}" class="btn btn-light btn-lg">
        <span aria-hidden="true">➡️</span> Continue Adventure
    </a>
</div>
@endif

@if($combat_data['status'] === 'defeat')
<div class="position-fixed top-50 start-50 translate-middle" style="z-index: 100; background: rgba(220, 53, 69, 0.95); backdrop-filter: blur(15px); border: 2px solid rgba(255, 255, 255, 0.3); border-radius: 15px; padding: 30px; color: white; text-align: center; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);">
    <span class="fs-1 mb-3" aria-hidden="true">💀</span>
    <h3 class="mb-3">Defeat...</h3>
    <p class="mb-3">You have been defeated by {{ $combat_data['enemy']['name'] ?? 'the enemy' }}.</p>
    <p class="mb-3">But all is not lost! You can rest and recover, then try again.</p>
    
    <div class="d-flex justify-content-center gap-2">
        <a href="{{ route('game.adventure', $adventure->id) }}" class="btn btn-light">
            <span aria-hidden="true">🔄</span> Try Again
        </a>
        <a href="{{ route('game.dashboard') }}" class="btn btn-outline-light">
            <span aria-hidden="true">🏠</span> Return to Village
        </a>
    </div>
</div>
@endif

@if($combat_data['status'] === 'fled')
<div class="position-fixed top-50 start-50 translate-middle" style="z-index: 100; background: rgba(255, 193, 7, 0.95); backdrop-filter: blur(15px); border: 2px solid rgba(255, 255, 255, 0.3); border-radius: 15px; padding: 30px; color: #333; text-align: center; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);">
    <span class="fs-1 mb-3" aria-hidden="true">🏃</span>
    <h3 class="mb-3">Escaped!</h3>
    <p class="mb-3">You successfully fled from {{ $combat_data['enemy']['name'] ?? 'the enemy' }}.</p>
    <p class="mb-3">You can continue your adventure or return to safety.</p>
    
    <div class="d-flex justify-content-center gap-2">
        <a href="{{ route('game.adventure', $adventure->id) }}" class="btn btn-dark">
            <span aria-hidden="true">➡️</span> Continue Adventure
        </a>
        <a href="{{ route('game.dashboard') }}" class="btn btn-outline-dark">
            <span aria-hidden="true">🏠</span> Return to Village
        </a>
    </div>
</div>
@endif

<style>
.stat-display .progress {
    height: 8px;
}

.stat-mini {
    padding: 0.5rem;
    border-radius: 0.25rem;
    background: rgba(0,0,0,0.05);
}

.log-entry {
    border-left: 3px solid transparent;
    transition: all 0.3s ease;
}

.log-entry.bg-success {
    border-left-color: #28a745;
}

.log-entry.bg-danger {
    border-left-color: #dc3545;
}

.log-entry.bg-info {
    border-left-color: #17a2b8;
}

.combat-actions .btn {
    min-height: 120px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.combat-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.combat-actions .btn:active {
    transform: translateY(0);
}

.status-effects .badge {
    margin-bottom: 0.25rem;
}

@media (prefers-reduced-motion: reduce) {
    .combat-actions .btn {
        transition: none;
    }
    
    .log-entry {
        transition: none;
    }
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
    }
}

.card.border-danger {
    animation: pulse 2s infinite;
}

@media (prefers-reduced-motion: reduce) {
    .card.border-danger {
        animation: none;
    }
}

/* Enemy Cards */
.enemy-card {
    border: 2px solid #dee2e6;
    border-radius: 8px;
    padding: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.8);
}

.enemy-card:hover {
    border-color: #dc3545;
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.2);
    transform: translateY(-2px);
}

.enemy-card.enemy-selected {
    border-color: #28a745;
    background: rgba(40, 167, 69, 0.1);
    box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.2);
}

.enemy-card.enemy-dead {
    opacity: 0.6;
    cursor: not-allowed;
    border-color: #6c757d;
    background: rgba(108, 117, 125, 0.1);
}

.enemy-card.enemy-dead:hover {
    transform: none;
    box-shadow: none;
}

.target-indicator, .status-indicator {
    font-size: 1.2rem;
}

.enemies-container {
    max-height: 400px;
    overflow-y: auto;
}
</style>

<script>
let actionInProgress = false;
let selectedTargetId = null;

function selectTarget(enemyId) {
    // Only allow selection of alive enemies
    const enemyCard = document.querySelector(`[data-enemy-id="${enemyId}"]`);
    if (!enemyCard || enemyCard.classList.contains('enemy-dead')) {
        return;
    }
    
    // Remove previous selection
    document.querySelectorAll('.enemy-card').forEach(card => {
        card.classList.remove('enemy-selected');
    });
    
    // Add selection to clicked enemy
    enemyCard.classList.add('enemy-selected');
    selectedTargetId = enemyId;
    
    // Update action buttons
    const attackButton = document.querySelector('.action-btn.attack');
    if (attackButton) {
        attackButton.disabled = false;
    }
    
    // Update target display
    const targetInfo = document.querySelector('.card-header small');
    if (targetInfo) {
        const enemyName = enemyCard.querySelector('.enemy-name strong').textContent;
        targetInfo.innerHTML = `Target: ${enemyName}`;
    }
}

// Check if current target is still alive, auto-select next alive enemy if not
function validateCurrentTarget() {
    if (!selectedTargetId) return;
    
    const currentTargetCard = document.querySelector(`[data-enemy-id="${selectedTargetId}"]`);
    if (currentTargetCard && currentTargetCard.classList.contains('enemy-dead')) {
        // Current target is dead, find next alive enemy
        const aliveEnemies = document.querySelectorAll('.enemy-card:not(.enemy-dead)');
        if (aliveEnemies.length > 0) {
            const nextTarget = aliveEnemies[0].getAttribute('data-enemy-id');
            selectTarget(nextTarget);
        } else {
            // No alive enemies, disable attack
            selectedTargetId = null;
            const attackButton = document.querySelector('.action-btn.attack');
            if (attackButton) {
                attackButton.disabled = true;
            }
        }
    }
}

function performAction(action) {
    if (actionInProgress) {
        return;
    }
    
    actionInProgress = true;
    
    // Visual feedback
    const buttons = document.querySelectorAll('.action-btn');
    buttons.forEach(btn => btn.disabled = true);
    
    // Show loading on clicked button
    const clickedButton = event.target.closest('.action-btn');
    const originalContent = clickedButton.innerHTML;
    clickedButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    
    fetch(`{{ route('game.combat-action', $adventure->id) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: action,
            target: selectedTargetId,
            node: new URLSearchParams(window.location.search).get('node')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Handle different combat outcomes
            if (data.status === 'death') {
                // Player died - redirect to village
                console.log('Player died:', data.message);
                window.location.href = data.redirect;
            } else if (data.status === 'victory') {
                // Player won - redirect to adventure map
                console.log('Victory!', data.message);
                window.location.href = data.redirect;
            } else if (data.redirect) {
                // Combat ended, redirect
                window.location.href = data.redirect;
            } else if (data.reload) {
                // Combat continues - reload to show updated state
                window.location.reload();
            } else {
                // Default - reload page
                window.location.reload();
            }
        } else if (data.show_item_selection) {
            // Show item selection dialog
            showItemSelectionDialog(data.available_items);
            
            // Restore button state
            clickedButton.innerHTML = originalContent;
            buttons.forEach(btn => btn.disabled = false);
            actionInProgress = false;
        } else {
            console.error('Combat action failed:', data.message);
            if (data.redirect) {
                window.location.href = data.redirect;
            }
            
            // Restore button state
            clickedButton.innerHTML = originalContent;
            buttons.forEach(btn => btn.disabled = false);
            actionInProgress = false;
        }
    })
    .catch(error => {
        console.error('Combat error:', error);
        
        // Restore button state
        clickedButton.innerHTML = originalContent;
        buttons.forEach(btn => btn.disabled = false);
        actionInProgress = false;
    });
}

function performTestAction(testAction) {
    if (actionInProgress) {
        return;
    }
    
    // Confirmation dialog for test actions
    const actionName = testAction === 'auto_complete_success' ? 'win' : 'lose';
    if (!confirm(`Are you sure you want to instantly ${actionName} this combat? This is for testing only.`)) {
        return;
    }
    
    actionInProgress = true;
    
    // Visual feedback
    const buttons = document.querySelectorAll('.action-btn, .btn');
    buttons.forEach(btn => btn.disabled = true);
    
    // Show loading on clicked button
    const clickedButton = event.target.closest('.action-btn');
    const originalContent = clickedButton.innerHTML;
    clickedButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    
    fetch(`{{ route('game.combat-action', $adventure->id) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: testAction,
            target: selectedTargetId,
            node: new URLSearchParams(window.location.search).get('node'),
            test_mode: true
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Handle test action results
            console.log(`Test action ${testAction} completed:`, data.message);
            
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                window.location.reload();
            }
        } else {
            console.error('Test action failed:', data.message);
            alert(`Test action failed: ${data.message || 'Unknown error'}`);
            
            // Restore button state
            clickedButton.innerHTML = originalContent;
            buttons.forEach(btn => btn.disabled = false);
            actionInProgress = false;
        }
    })
    .catch(error => {
        console.error('Test action error:', error);
        alert('An error occurred during the test action');
        
        // Restore button state
        clickedButton.innerHTML = originalContent;
        buttons.forEach(btn => btn.disabled = false);
        actionInProgress = false;
    });
}

function announceToScreenReader(message) {
    const announcement = document.createElement('div');
    announcement.setAttribute('aria-live', 'polite');
    announcement.setAttribute('aria-atomic', 'true');
    announcement.className = 'sr-only';
    announcement.textContent = message;
    document.body.appendChild(announcement);
    
    setTimeout(() => {
        document.body.removeChild(announcement);
    }, 1000);
}

// Keyboard shortcuts for combat actions
document.addEventListener('keydown', function(e) {
    if (actionInProgress) return;
    
    switch(e.key) {
        case '1':
        case 'a':
        case 'A':
            e.preventDefault();
            performAction('attack');
            break;
        case '2':
        case 'd':
        case 'D':
            e.preventDefault();
            performAction('defend');
            break;
        case '3':
        case 's':
        case 'S':
            e.preventDefault();
            performAction('special');
            break;
    }
});

// Auto-scroll combat log to bottom and restore target selection
document.addEventListener('DOMContentLoaded', function() {
    const combatLog = document.getElementById('combatLog');
    if (combatLog) {
        combatLog.scrollTop = combatLog.scrollHeight;
    }
    
    // Restore target selection from server-side selected target
    @if(isset($combat_data['selected_target']) && $combat_data['selected_target'])
        selectTarget('{{ $combat_data['selected_target'] }}');
    @else
        // Auto-select first alive enemy if no target selected
        const firstAliveEnemy = document.querySelector('.enemy-card:not(.enemy-dead)');
        if (firstAliveEnemy) {
            const enemyId = firstAliveEnemy.getAttribute('data-enemy-id');
            selectTarget(enemyId);
        }
    @endif
    
    // Validate current target on page load
    validateCurrentTarget();
});

// Item selection dialog
function showItemSelectionDialog(availableItems) {
    if (!availableItems || availableItems.length === 0) {
        alert('No consumable items available!');
        return;
    }
    
    // Create modal backdrop
    const backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop fade show';
    backdrop.style.zIndex = '1040';
    
    // Create modal
    const modal = document.createElement('div');
    modal.className = 'modal fade show d-block';
    modal.style.zIndex = '1050';
    modal.setAttribute('tabindex', '-1');
    modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Item to Use</h5>
                    <button type="button" class="btn-close" data-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2" id="itemSelection">
                        ${availableItems.map(item => `
                            <div class="col-12">
                                <button class="btn btn-outline-primary w-100 text-start d-flex justify-content-between align-items-center" 
                                        onclick="useSelectedItem(${item.id})" 
                                        data-item-id="${item.id}">
                                    <div>
                                        <strong>${item.name}</strong>
                                        <br><small class="text-muted">${item.description || 'Consumable item'}</small>
                                    </div>
                                    <span class="badge bg-secondary">×${item.quantity}</span>
                                </button>
                            </div>
                        `).join('')}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeItemDialog()">Cancel</button>
                </div>
            </div>
        </div>
    `;
    
    // Add to DOM
    document.body.appendChild(backdrop);
    document.body.appendChild(modal);
    
    // Store references for cleanup
    window.itemDialogBackdrop = backdrop;
    window.itemDialogModal = modal;
    
    // Add event listeners
    modal.querySelector('.btn-close').addEventListener('click', closeItemDialog);
    modal.querySelector('.btn-secondary').addEventListener('click', closeItemDialog);
    
    // Close on backdrop click
    backdrop.addEventListener('click', closeItemDialog);
}

function closeItemDialog() {
    if (window.itemDialogBackdrop) {
        window.itemDialogBackdrop.remove();
        delete window.itemDialogBackdrop;
    }
    if (window.itemDialogModal) {
        window.itemDialogModal.remove();
        delete window.itemDialogModal;
    }
}

function useSelectedItem(itemId) {
    closeItemDialog();
    
    if (actionInProgress) {
        return;
    }
    
    actionInProgress = true;
    
    // Show loading state
    const buttons = document.querySelectorAll('.action-btn');
    buttons.forEach(btn => btn.disabled = true);
    
    fetch(`{{ route('game.combat-action', $adventure->id) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'use_item',
            item_id: itemId,
            target: selectedTargetId,
            node: new URLSearchParams(window.location.search).get('node')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            console.error('Item usage failed:', data.message);
            buttons.forEach(btn => btn.disabled = false);
            actionInProgress = false;
            alert(data.message || 'Failed to use item');
        }
    })
    .catch(error => {
        console.error('Item usage error:', error);
        buttons.forEach(btn => btn.disabled = false);
        actionInProgress = false;
        alert('An error occurred while using the item');
    });
}
</script>
@endsection