@extends('game.layout')

@section('title', 'Dashboard')
@section('meta_description', 'Your character dashboard in Grassland Awakening - view stats, weather, village info, and achievements.')

@section('content')
<div class="row g-4">
    <!-- Character Stats -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h2 class="card-title h5 mb-0">
                    <span aria-hidden="true">⚔️</span> Character: {{ $player->character_name }}
                </h2>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="stat-card text-center">
                            <div class="h6 text-muted mb-1">Level</div>
                            <div class="h4 text-primary" aria-label="Character level {{ $player->level }}">{{ $player->level }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card text-center">
                            <div class="h6 text-muted mb-1">Experience</div>
                            <div class="h4 text-success" aria-label="Experience points {{ $player->experience }}">{{ $player->experience }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card text-center">
                            <div class="h6 text-muted mb-1">Health</div>
                            <div class="h4 {{ $player->hp < $player->max_hp * 0.3 ? 'text-danger' : 'text-info' }}" 
                                 aria-label="Health {{ $player->hp }} out of {{ $player->max_hp }}">
                                {{ $player->hp }}/{{ $player->max_hp }}
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card text-center">
                            <div class="h6 text-muted mb-1">Gold</div>
                            <div class="h4 text-warning" aria-label="{{ $player->persistent_currency }} gold pieces">
                                <span aria-hidden="true">💰</span> {{ number_format($player->persistent_currency) }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Ability Scores -->
                <div class="mt-4">
                    <h3 class="h6 text-muted mb-3">Ability Scores</h3>
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <small class="text-muted d-block">STR</small>
                            <span class="fw-bold" aria-label="Strength {{ $player->str }}">{{ $player->str }}</span>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">DEX</small>
                            <span class="fw-bold" aria-label="Dexterity {{ $player->dex }}">{{ $player->dex }}</span>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">CON</small>
                            <span class="fw-bold" aria-label="Constitution {{ $player->con }}">{{ $player->con }}</span>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">INT</small>
                            <span class="fw-bold" aria-label="Intelligence {{ $player->int }}">{{ $player->int }}</span>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">WIS</small>
                            <span class="fw-bold" aria-label="Wisdom {{ $player->wis }}">{{ $player->wis }}</span>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">CHA</small>
                            <span class="fw-bold" aria-label="Charisma {{ $player->cha }}">{{ $player->cha }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Weather & Season -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-info text-white">
                <h2 class="card-title h5 mb-0">
                    <span aria-hidden="true">🌦️</span> Weather & Season
                </h2>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="weather-indicator" aria-hidden="true">
                        @if(isset($weather['type']))
                            @switch($weather['type'])
                                @case('clear')
                                    ☀️
                                    @break
                                @case('rain')
                                    🌧️
                                    @break
                                @case('storm')
                                    ⛈️
                                    @break
                                @case('snow')
                                    ❄️
                                    @break
                                @case('fog')
                                    🌫️
                                    @break
                                @default
                                    🌤️
                            @endswitch
                        @else
                            🌤️
                        @endif
                    </div>
                    <h3 class="h5 mb-1">{{ $weather['name'] ?? 'Pleasant Weather' }}</h3>
                    <p class="text-muted mb-3">{{ $weather['description'] ?? 'A calm day in the grasslands.' }}</p>
                    @if(isset($weather['real_weather_data']['temperature']))
                        <div class="badge bg-secondary" aria-label="Temperature {{ $weather['real_weather_data']['temperature'] }} degrees celsius">
                            🌡️ {{ $weather['real_weather_data']['temperature'] }}°C
                        </div>
                    @endif
                </div>
                
                <div class="text-center">
                    <h3 class="h6 text-muted mb-2">Current Season</h3>
                    <div class="d-flex align-items-center justify-content-center">
                        <span class="me-2" aria-hidden="true">
                            @switch($season['name'])
                                @case('spring')
                                    🌸
                                    @break
                                @case('summer')
                                    ☀️
                                    @break
                                @case('autumn')
                                    🍂
                                    @break
                                @case('winter')
                                    ❄️
                                    @break
                                @default
                                    🌿
                            @endswitch
                        </span>
                        <span class="fw-bold">{{ ucfirst($season['name']) }}</span>
                    </div>
                    <small class="text-muted">{{ $season['description'] }}</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Village Overview -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h2 class="card-title h5 mb-0">
                    <span aria-hidden="true">🏘️</span> Village Overview
                </h2>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">Village Level</span>
                            <span class="fw-bold fs-5" aria-label="Village level {{ $village_info['level'] }}">
                                {{ $village_info['level'] }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">NPCs</span>
                            <span class="fw-bold" aria-label="{{ $village_info['npc_count'] }} NPCs in village">
                                {{ $village_info['npc_count'] }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">Specializations</span>
                            <span class="fw-bold" aria-label="{{ count($village_info['specializations']) }} specializations unlocked">
                                {{ count($village_info['specializations']) }}
                            </span>
                        </div>
                    </div>
                </div>
                
                @if(!empty($village_info['specializations']))
                    <div class="mt-3">
                        <h3 class="h6 text-muted mb-2">Active Specializations</h3>
                        @foreach($village_info['specializations'] as $spec)
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-primary me-2" aria-label="{{ $spec->getSpecializationName() }} level {{ $spec->level }}">
                                    Lv.{{ $spec->level }}
                                </span>
                                <small>{{ $spec->getSpecializationName() }}</small>
                            </div>
                        @endforeach
                    </div>
                @endif
                
                <div class="text-center mt-3">
                    <a href="{{ route('game.village') }}" class="btn btn-success" aria-describedby="village-link-desc">
                        Manage Village
                    </a>
                    <div id="village-link-desc" class="sr-only">Go to village management page to view and manage your NPCs and specializations</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Achievements & Reputation Summary -->
<div class="row g-4 mt-2">
    <!-- Recent Achievements -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h2 class="card-title h5 mb-0">
                    <span aria-hidden="true">🏆</span> Recent Achievements
                </h2>
            </div>
            <div class="card-body">
                @if(!empty($achievements['unlocked']) && count($achievements['unlocked']) > 0)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Total Points</span>
                            <span class="fw-bold text-warning" aria-label="{{ $achievements['total_points'] }} achievement points">
                                {{ $achievements['total_points'] }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Achievements</span>
                            <span class="fw-bold" aria-label="{{ $achievements['achievement_count'] }} achievements unlocked">
                                {{ $achievements['achievement_count'] }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="list-group list-group-flush">
                        @foreach(array_slice($achievements['unlocked'], 0, 3) as $achievement)
                            <div class="list-group-item px-0 border-0 d-flex align-items-center">
                                <span class="achievement-icon me-3" aria-hidden="true">{{ $achievement['icon'] }}</span>
                                <div class="flex-grow-1">
                                    <div class="fw-bold">{{ $achievement['name'] }}</div>
                                    <small class="text-muted">{{ $achievement['description'] }}</small>
                                    <div class="mt-1">
                                        <span class="badge bg-warning text-dark" aria-label="{{ $achievement['points'] }} points">
                                            {{ $achievement['points'] }} pts
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <span aria-hidden="true">🎯</span>
                        <p class="mb-2">No achievements yet!</p>
                        <small>Complete adventures, manage your village, and interact with NPCs to unlock achievements.</small>
                    </div>
                @endif
                
                <div class="text-center mt-3">
                    <a href="{{ route('game.achievements') }}" class="btn btn-warning" aria-describedby="achievements-link-desc">
                        View All Achievements
                    </a>
                    <div id="achievements-link-desc" class="sr-only">Go to achievements page to view all available achievements and track your progress</div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Reputation Summary - Hidden for now
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h2 class="card-title h5 mb-0">
                    <span aria-hidden="true">⚖️</span> Reputation Summary
                </h2>
            </div>
            <div class="card-body">
                @if(!empty($reputations))
                    @foreach($reputations as $rep)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="d-flex align-items-center">
                                    <span aria-hidden="true" class="me-2">{{ $rep['faction_icon'] }}</span>
                                    {{ $rep['faction_name'] }}
                                </span>
                                <span class="fw-bold" style="color: {{ $rep['level']['color'] }}" 
                                      aria-label="{{ $rep['faction_name'] }} reputation: {{ $rep['level']['name'] }} with score {{ $rep['current_score'] }}">
                                    {{ $rep['level']['name'] }}
                                </span>
                            </div>
                            <div class="reputation-bar">
                                @php
                                    $progress = $rep['progress_to_next'];
                                    $percentage = $progress ? $progress['percentage'] : 100;
                                @endphp
                                <div class="reputation-progress" 
                                     style="width: {{ $percentage }}%; background-color: {{ $rep['level']['color'] }}"
                                     role="progressbar" 
                                     aria-valuenow="{{ $percentage }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100"
                                     aria-label="Progress to next reputation level: {{ round($percentage, 1) }}%">
                                </div>
                            </div>
                            @if($progress)
                                <small class="text-muted">
                                    {{ $progress['progress'] }}/{{ $progress['needed'] }} to {{ $progress['next_level_name'] }}
                                </small>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="text-center text-muted py-4">
                        <span aria-hidden="true">🤝</span>
                        <p class="mb-2">Build relationships with factions!</p>
                        <small>Complete quests and make choices to gain or lose reputation with various factions.</small>
                    </div>
                @endif
                
                <div class="text-center mt-3">
                    <a href="{{ route('game.reputation') }}" class="btn btn-secondary" aria-describedby="reputation-link-desc">
                        View All Factions
                    </a>
                    <div id="reputation-link-desc" class="sr-only">Go to reputation page to view detailed faction relationships and benefits</div>
                </div>
            </div>
        </div>
    </div>
    --}}
</div>

@endsection