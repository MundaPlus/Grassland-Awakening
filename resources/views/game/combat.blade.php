@extends('game.layout')

@section('title', 'Combat - ' . $adventure->title)

@section('content')
<div class="container-fluid">
    <!-- Combat Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h3 mb-2 text-danger">⚔️ Combat Encounter</h1>
                            <p class="text-muted mb-0">{{ $adventure->title }} - {{ $combat_data['location'] ?? 'Unknown Location' }}</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <span class="badge bg-danger fs-6" aria-label="Combat round {{ $combat_data['round'] ?? 1 }}">
                                Round {{ $combat_data['round'] ?? 1 }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weather Effects -->
    @if(isset($weatherEffects))
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-cloud-rain fa-2x me-3" aria-hidden="true"></i>
                    <div>
                        <h4 class="alert-heading mb-1">Weather Effects</h4>
                        <p class="mb-0">{{ $weatherEffects }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif


    <!-- Combat Status -->
    <div class="row mb-4">
        <div class="col-md-6">
            <!-- Player Status -->
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h2 class="h5 mb-0">{{ $player->name }} (You)</h2>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <div class="stat-display">
                                <div class="small text-muted">Health</div>
                                @php
                                    $playerHP = $combat_data['player']['hp'] ?? $player->hp;
                                    $playerMaxHP = $combat_data['player']['max_hp'] ?? $player->max_hp;
                                @endphp
                                <div class="progress mb-1" role="progressbar" 
                                     aria-valuenow="{{ $playerHP }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="{{ $playerMaxHP }}"
                                     aria-label="Player health {{ $playerHP }} out of {{ $playerMaxHP }}"
                                     style="height: 25px;">
                                    <div class="progress-bar bg-success d-flex align-items-center justify-content-center fw-bold" style="width: {{ $playerMaxHP > 0 ? ($playerHP / $playerMaxHP) * 100 : 0 }}%; font-size: 14px;">
                                        {{ $playerHP }}/{{ $playerMaxHP }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-display">
                                <div class="small text-muted">Level</div>
                                <div class="h4" aria-label="Player level {{ $player->level }}">{{ $player->level }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row text-center mb-3">
                        <div class="col-2">
                            <div class="stat-mini">
                                <div class="small text-muted">STR</div>
                                <div class="fw-bold" aria-label="Strength {{ $combat_data['player']['stats']['str'] ?? $player->str }}">{{ $combat_data['player']['stats']['str'] ?? $player->str }}</div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="stat-mini">
                                <div class="small text-muted">DEX</div>
                                <div class="fw-bold" aria-label="Dexterity {{ $combat_data['player']['stats']['dex'] ?? $player->dex }}">{{ $combat_data['player']['stats']['dex'] ?? $player->dex }}</div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="stat-mini">
                                <div class="small text-muted">CON</div>
                                <div class="fw-bold" aria-label="Constitution {{ $combat_data['player']['stats']['con'] ?? $player->con }}">{{ $combat_data['player']['stats']['con'] ?? $player->con }}</div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="stat-mini">
                                <div class="small text-muted">INT</div>
                                <div class="fw-bold" aria-label="Intelligence {{ $combat_data['player']['stats']['int'] ?? $player->int }}">{{ $combat_data['player']['stats']['int'] ?? $player->int }}</div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="stat-mini">
                                <div class="small text-muted">WIS</div>
                                <div class="fw-bold" aria-label="Wisdom {{ $combat_data['player']['stats']['wis'] ?? $player->wis }}">{{ $combat_data['player']['stats']['wis'] ?? $player->wis }}</div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="stat-mini">
                                <div class="small text-muted">CHA</div>
                                <div class="fw-bold" aria-label="Charisma {{ $combat_data['player']['stats']['cha'] ?? $player->cha }}">{{ $combat_data['player']['stats']['cha'] ?? $player->cha }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="stat-mini">
                                <div class="small text-muted">AC</div>
                                <div class="fw-bold" aria-label="Armor Class {{ $combat_data['player']['ac'] ?? $player->ac }}">{{ $combat_data['player']['ac'] ?? $player->ac }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-mini">
                                <div class="small text-muted">XP</div>
                                <div class="fw-bold" aria-label="Experience {{ $player->experience }}">{{ $player->experience }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Effects -->
                    @if(isset($combat_data['player_effects']) && !empty($combat_data['player_effects']))
                    <div class="status-effects mt-3">
                        <h4 class="small text-muted mb-2">Status Effects:</h4>
                        <div class="d-flex gap-1 flex-wrap">
                            @foreach($combat_data['player_effects'] as $effect)
                            <span class="badge bg-info" title="{{ $effect['description'] ?? '' }}">
                                {{ $effect['name'] }}
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
        </div>
        
        <div class="col-md-6">
            <!-- Enemies Status -->
            <div class="card h-100">
                <div class="card-header bg-danger text-white">
                    <h2 class="h5 mb-0">
                        @if(isset($combat_data['enemies']))
                            Enemies ({{ count(array_filter($combat_data['enemies'], fn($e) => $e['status'] === 'alive')) }}/{{ count($combat_data['enemies']) }})
                        @else
                            {{ $combat_data['enemy']['name'] ?? $enemy['name'] ?? 'Unknown Enemy' }}
                        @endif
                    </h2>
                </div>
                <div class="card-body enemies-container">
                    @if(isset($combat_data['enemies']))
                        @foreach($combat_data['enemies'] as $enemyId => $enemyData)
                            <div class="enemy-card mb-3 {{ $enemyData['status'] === 'dead' ? 'enemy-dead' : '' }} {{ $combat_data['selected_target'] === $enemyId ? 'enemy-selected' : '' }}" 
                                 data-enemy-id="{{ $enemyId }}" 
                                 onclick="selectTarget('{{ $enemyId }}')">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="enemy-name flex-grow-1">
                                        <strong>{{ $enemyData['name'] }}</strong>
                                        <small class="text-muted d-block">{{ $enemyData['type'] ?? 'Monster' }}</small>
                                    </div>
                                    @if($enemyData['status'] === 'alive')
                                        <div class="target-indicator">
                                            <i class="fas fa-crosshairs text-danger"></i>
                                        </div>
                                    @else
                                        <div class="status-indicator">
                                            <i class="fas fa-skull text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="enemy-health mb-2">
                                    @php
                                        $enemyHealth = $enemyData['health'] ?? $enemyData['hp'] ?? 0;
                                        $enemyMaxHealth = $enemyData['max_health'] ?? $enemyData['max_hp'] ?? 100;
                                        $healthPercent = $enemyMaxHealth > 0 ? ($enemyHealth / $enemyMaxHealth) * 100 : 0;
                                    @endphp
                                    <div class="progress mb-1" style="height: 20px;">
                                        <div class="progress-bar {{ $enemyData['status'] === 'dead' ? 'bg-secondary' : 'bg-danger' }} d-flex align-items-center justify-content-center fw-bold text-white" 
                                             style="width: {{ $healthPercent }}%; font-size: 12px;">
                                            @if($healthPercent > 30)
                                                {{ $enemyHealth }}/{{ $enemyMaxHealth }}
                                            @endif
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $enemyHealth }}/{{ $enemyMaxHealth }} HP</small>
                                </div>
                                
                                <div class="enemy-stats">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <small class="text-muted">STR</small>
                                            <div class="fw-bold small">{{ $enemyData['str'] ?? 10 }}</div>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted">INT</small>
                                            <div class="fw-bold small">{{ $enemyData['int'] ?? 10 }}</div>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted">WIS</small>
                                            <div class="fw-bold small">{{ $enemyData['wis'] ?? 10 }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <!-- Single Enemy (backward compatibility) -->
                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="stat-display">
                                    <div class="small text-muted">Health</div>
                                    @php
                                        $enemyHealth = $enemy['health'] ?? $enemy['hp'] ?? 100;
                                        $enemyMaxHealth = $enemy['max_health'] ?? $enemy['max_hp'] ?? 100;
                                    @endphp
                                    <div class="progress mb-1" role="progressbar" 
                                         aria-valuenow="{{ $enemyHealth }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="{{ $enemyMaxHealth }}"
                                         aria-label="Enemy health {{ $enemyHealth }} out of {{ $enemyMaxHealth }}">
                                        <div class="progress-bar bg-danger" style="width: {{ $enemyMaxHealth > 0 ? ($enemyHealth / $enemyMaxHealth) * 100 : 0 }}%">
                                            {{ $enemyHealth }}/{{ $enemyMaxHealth }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-display">
                                    <div class="small text-muted">Type</div>
                                    <div class="h6">{{ $combat_data['enemy']['type'] ?? $enemy['type'] ?? 'Monster' }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="stat-mini">
                                    <div class="small text-muted">STR</div>
                                    <div class="fw-bold" aria-label="Enemy strength {{ $combat_data['enemy']['strength'] ?? $enemy['str'] ?? 10 }}">{{ $combat_data['enemy']['strength'] ?? $enemy['str'] ?? 10 }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-mini">
                                    <div class="small text-muted">INT</div>
                                    <div class="fw-bold" aria-label="Enemy intelligence {{ $combat_data['enemy']['intelligence'] ?? $enemy['int'] ?? 10 }}">{{ $combat_data['enemy']['intelligence'] ?? $enemy['int'] ?? 10 }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-mini">
                                    <div class="small text-muted">WIS</div>
                                    <div class="fw-bold" aria-label="Enemy wisdom {{ $combat_data['enemy']['wisdom'] ?? $enemy['wis'] ?? 10 }}">{{ $combat_data['enemy']['wisdom'] ?? $enemy['wis'] ?? 10 }}</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Combat Actions -->
    @if($combat_data['status'] === 'active')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0">Choose Your Action</h2>
                    @if(isset($combat_data['enemies']))
                        <small class="text-muted">
                            @if($combat_data['selected_target'])
                                Target: {{ $combat_data['enemies'][$combat_data['selected_target']]['name'] ?? 'Unknown' }}
                            @else
                                Select a target first
                            @endif
                        </small>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <button type="button" class="btn btn-danger btn-lg w-100 h-100" 
                                    onclick="performAction('attack')"
                                    aria-label="Attack the enemy"
                                    {{ isset($combat_data['enemies']) && !$combat_data['selected_target'] ? 'disabled' : '' }}>
                                <div>
                                    <i class="fas fa-sword fa-2x mb-2" aria-hidden="true"></i>
                                    <div class="h6">Attack</div>
                                    <small class="text-muted">Deal physical damage</small>
                                </div>
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-info btn-lg w-100 h-100" 
                                    onclick="performAction('defend')"
                                    aria-label="Defend against enemy attacks">
                                <div>
                                    <i class="fas fa-shield-alt fa-2x mb-2" aria-hidden="true"></i>
                                    <div class="h6">Defend</div>
                                    <small class="text-muted">Reduce incoming damage</small>
                                </div>
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-warning btn-lg w-100 h-100" 
                                    onclick="performAction('special')"
                                    aria-label="Use special ability">
                                <div>
                                    <i class="fas fa-magic fa-2x mb-2" aria-hidden="true"></i>
                                    <div class="h6">Special</div>
                                    <small class="text-muted">Use special ability</small>
                                </div>
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-success btn-lg w-100 h-100" 
                                    onclick="performAction('use_item')"
                                    aria-label="Use an item">
                                <div>
                                    <i class="fas fa-flask fa-2x mb-2" aria-hidden="true"></i>
                                    <div class="h6">Use Item</div>
                                    <small class="text-muted">Consume an item</small>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Combat Log -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0">Combat Log</h2>
                </div>
                <div class="card-body">
                    <div class="combat-log" id="combatLog" style="max-height: 200px; overflow-y: auto;">
                        @if(isset($combat_data['log']) && !empty($combat_data['log']))
                            @foreach(array_slice($combat_data['log'], -10) as $logEntry)
                                <div class="log-entry mb-1 p-2 rounded {{ ($logEntry['type'] ?? 'info') === 'player' ? 'bg-success bg-opacity-10' : (($logEntry['type'] ?? 'info') === 'enemy' ? 'bg-danger bg-opacity-10' : 'bg-info bg-opacity-10') }}">
                                    <small class="text-muted">Round {{ $combat_data['round'] ?? 1 }}:</small>
                                    <div>{{ $logEntry['message'] ?? $logEntry }}</div>
                                </div>
                            @endforeach
                        @else
                            <div class="log-entry mb-1 p-2 rounded bg-info bg-opacity-10">
                                <small class="text-muted">Round {{ $combat_data['round'] ?? 1 }}:</small>
                                <div>Combat begins! Choose your action.</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Combat Result -->
    @if($combat_data['status'] === 'victory')
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-success" role="alert">
                <div class="text-center">
                    <i class="fas fa-trophy fa-3x text-warning mb-3" aria-hidden="true"></i>
                    <h3 class="alert-heading">Victory!</h3>
                    <p class="mb-3">You have defeated {{ $combat_data['enemy']['name'] ?? 'the enemy' }}!</p>
                    
                    @if(isset($combat_data['rewards']))
                    <div class="rewards mb-3">
                        <h4 class="h6">Rewards Earned:</h4>
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            @if(isset($combat_data['rewards']['gold']) && $combat_data['rewards']['gold'] > 0)
                            <span class="badge bg-warning text-dark fs-6">
                                <i class="fas fa-coins" aria-hidden="true"></i> {{ $combat_data['rewards']['gold'] }} Gold
                            </span>
                            @endif
                            @if(isset($combat_data['rewards']['experience']) && $combat_data['rewards']['experience'] > 0)
                            <span class="badge bg-info fs-6">
                                <i class="fas fa-star" aria-hidden="true"></i> {{ $combat_data['rewards']['experience'] }} XP
                            </span>
                            @endif
                            @if(isset($combat_data['rewards']['items']) && !empty($combat_data['rewards']['items']))
                            @foreach($combat_data['rewards']['items'] as $item)
                            <span class="badge bg-success fs-6">
                                <i class="fas fa-gift" aria-hidden="true"></i> {{ $item }}
                            </span>
                            @endforeach
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    <a href="{{ route('game.adventure', $adventure->id) }}" class="btn btn-success btn-lg">
                        <i class="fas fa-arrow-right" aria-hidden="true"></i> Continue Adventure
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($combat_data['status'] === 'defeat')
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-danger" role="alert">
                <div class="text-center">
                    <i class="fas fa-skull fa-3x text-danger mb-3" aria-hidden="true"></i>
                    <h3 class="alert-heading">Defeat...</h3>
                    <p class="mb-3">You have been defeated by {{ $combat_data['enemy']['name'] ?? 'the enemy' }}.</p>
                    <p class="mb-3">But all is not lost! You can rest and recover, then try again.</p>
                    
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('game.adventure', $adventure->id) }}" class="btn btn-primary">
                            <i class="fas fa-redo" aria-hidden="true"></i> Try Again
                        </a>
                        <a href="{{ route('game.dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-home" aria-hidden="true"></i> Return to Village
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($combat_data['status'] === 'fled')
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning" role="alert">
                <div class="text-center">
                    <i class="fas fa-running fa-3x text-warning mb-3" aria-hidden="true"></i>
                    <h3 class="alert-heading">Escaped!</h3>
                    <p class="mb-3">You successfully fled from {{ $combat_data['enemy']['name'] ?? 'the enemy' }}.</p>
                    <p class="mb-3">You can continue your adventure or return to safety.</p>
                    
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('game.adventure', $adventure->id) }}" class="btn btn-primary">
                            <i class="fas fa-arrow-right" aria-hidden="true"></i> Continue Adventure
                        </a>
                        <a href="{{ route('game.dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-home" aria-hidden="true"></i> Return to Village
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

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
    const attackButton = document.querySelector('button[onclick="performAction(\'attack\')"]');
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
            const attackButton = document.querySelector('button[onclick="performAction(\'attack\')"]');
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
    const buttons = document.querySelectorAll('.combat-actions .btn');
    buttons.forEach(btn => btn.disabled = true);
    
    // Show loading on clicked button
    const clickedButton = event.target.closest('.btn');
    const originalContent = clickedButton.innerHTML;
    clickedButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
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
</script>
@endsection