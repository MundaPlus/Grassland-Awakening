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
                            <p class="text-muted mb-0">{{ $adventure->title }} - {{ $combatData['location'] ?? 'Unknown Location' }}</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <span class="badge bg-danger fs-6" aria-label="Combat round {{ $combatData['round'] ?? 1 }}">
                                Round {{ $combatData['round'] ?? 1 }}
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

    <!-- Combat Log -->
    @if(isset($combatData['log']) && !empty($combatData['log']))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0">Combat Log</h2>
                </div>
                <div class="card-body">
                    <div class="combat-log" id="combatLog" aria-live="polite" aria-label="Combat events">
                        @foreach(array_slice($combatData['log'], -5) as $index => $logEntry)
                        <div class="log-entry mb-2 p-2 rounded {{ $logEntry['type'] === 'player' ? 'bg-success bg-opacity-10' : ($logEntry['type'] === 'enemy' ? 'bg-danger bg-opacity-10' : 'bg-info bg-opacity-10') }}">
                            <small class="text-muted">Round {{ $logEntry['round'] ?? 'Unknown' }}:</small>
                            <div>{{ $logEntry['message'] }}</div>
                        </div>
                        @endforeach
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
                                <div class="progress mb-1" role="progressbar" 
                                     aria-valuenow="{{ $player->health }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="{{ $player->max_health }}"
                                     aria-label="Player health {{ $player->health }} out of {{ $player->max_health }}">
                                    <div class="progress-bar bg-success" style="width: {{ ($player->health / $player->max_health) * 100 }}%">
                                        {{ $player->health }}/{{ $player->max_health }}
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
                    
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-mini">
                                <div class="small text-muted">STR</div>
                                <div class="fw-bold" aria-label="Strength {{ $player->strength }}">{{ $player->strength }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-mini">
                                <div class="small text-muted">INT</div>
                                <div class="fw-bold" aria-label="Intelligence {{ $player->intelligence }}">{{ $player->intelligence }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-mini">
                                <div class="small text-muted">WIS</div>
                                <div class="fw-bold" aria-label="Wisdom {{ $player->wisdom }}">{{ $player->wisdom }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Effects -->
                    @if(isset($combatData['player_effects']) && !empty($combatData['player_effects']))
                    <div class="status-effects mt-3">
                        <h4 class="small text-muted mb-2">Status Effects:</h4>
                        <div class="d-flex gap-1 flex-wrap">
                            @foreach($combatData['player_effects'] as $effect)
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
            <!-- Enemy Status -->
            <div class="card h-100">
                <div class="card-header bg-danger text-white">
                    <h2 class="h5 mb-0">{{ $combatData['enemy']['name'] ?? 'Unknown Enemy' }}</h2>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <div class="stat-display">
                                <div class="small text-muted">Health</div>
                                <div class="progress mb-1" role="progressbar" 
                                     aria-valuenow="{{ $combatData['enemy']['health'] }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="{{ $combatData['enemy']['max_health'] }}"
                                     aria-label="Enemy health {{ $combatData['enemy']['health'] }} out of {{ $combatData['enemy']['max_health'] }}">
                                    <div class="progress-bar bg-danger" style="width: {{ ($combatData['enemy']['health'] / $combatData['enemy']['max_health']) * 100 }}%">
                                        {{ $combatData['enemy']['health'] }}/{{ $combatData['enemy']['max_health'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-display">
                                <div class="small text-muted">Type</div>
                                <div class="h6">{{ $combatData['enemy']['type'] ?? 'Monster' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-mini">
                                <div class="small text-muted">STR</div>
                                <div class="fw-bold" aria-label="Enemy strength {{ $combatData['enemy']['strength'] }}">{{ $combatData['enemy']['strength'] }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-mini">
                                <div class="small text-muted">INT</div>
                                <div class="fw-bold" aria-label="Enemy intelligence {{ $combatData['enemy']['intelligence'] }}">{{ $combatData['enemy']['intelligence'] }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-mini">
                                <div class="small text-muted">WIS</div>
                                <div class="fw-bold" aria-label="Enemy wisdom {{ $combatData['enemy']['wisdom'] }}">{{ $combatData['enemy']['wisdom'] }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Enemy Status Effects -->
                    @if(isset($combatData['enemy_effects']) && !empty($combatData['enemy_effects']))
                    <div class="status-effects mt-3">
                        <h4 class="small text-muted mb-2">Status Effects:</h4>
                        <div class="d-flex gap-1 flex-wrap">
                            @foreach($combatData['enemy_effects'] as $effect)
                            <span class="badge bg-warning text-dark" title="{{ $effect['description'] ?? '' }}">
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
    </div>

    <!-- Combat Actions -->
    @if($combatData['status'] === 'active')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0">Choose Your Action</h2>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <button type="button" class="btn btn-danger btn-lg w-100 h-100" 
                                    onclick="performAction('attack')"
                                    aria-label="Attack the enemy">
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
                            <button type="button" class="btn btn-secondary btn-lg w-100 h-100" 
                                    onclick="performAction('flee')"
                                    aria-label="Attempt to flee from combat">
                                <div>
                                    <i class="fas fa-running fa-2x mb-2" aria-hidden="true"></i>
                                    <div class="h6">Flee</div>
                                    <small class="text-muted">Attempt to escape</small>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Combat Result -->
    @if($combatData['status'] === 'victory')
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-success" role="alert">
                <div class="text-center">
                    <i class="fas fa-trophy fa-3x text-warning mb-3" aria-hidden="true"></i>
                    <h3 class="alert-heading">Victory!</h3>
                    <p class="mb-3">You have defeated {{ $combatData['enemy']['name'] ?? 'the enemy' }}!</p>
                    
                    @if(isset($combatData['rewards']))
                    <div class="rewards mb-3">
                        <h4 class="h6">Rewards Earned:</h4>
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            @if(isset($combatData['rewards']['gold']) && $combatData['rewards']['gold'] > 0)
                            <span class="badge bg-warning text-dark fs-6">
                                <i class="fas fa-coins" aria-hidden="true"></i> {{ $combatData['rewards']['gold'] }} Gold
                            </span>
                            @endif
                            @if(isset($combatData['rewards']['experience']) && $combatData['rewards']['experience'] > 0)
                            <span class="badge bg-info fs-6">
                                <i class="fas fa-star" aria-hidden="true"></i> {{ $combatData['rewards']['experience'] }} XP
                            </span>
                            @endif
                            @if(isset($combatData['rewards']['items']) && !empty($combatData['rewards']['items']))
                            @foreach($combatData['rewards']['items'] as $item)
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

    @if($combatData['status'] === 'defeat')
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-danger" role="alert">
                <div class="text-center">
                    <i class="fas fa-skull fa-3x text-danger mb-3" aria-hidden="true"></i>
                    <h3 class="alert-heading">Defeat...</h3>
                    <p class="mb-3">You have been defeated by {{ $combatData['enemy']['name'] ?? 'the enemy' }}.</p>
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

    @if($combatData['status'] === 'fled')
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning" role="alert">
                <div class="text-center">
                    <i class="fas fa-running fa-3x text-warning mb-3" aria-hidden="true"></i>
                    <h3 class="alert-heading">Escaped!</h3>
                    <p class="mb-3">You successfully fled from {{ $combatData['enemy']['name'] ?? 'the enemy' }}.</p>
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
</style>

<script>
let actionInProgress = false;

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
            action: action
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Announce action result to screen readers
            announceToScreenReader(`Combat action ${action} completed. ${data.message || ''}`);
            
            // Reload page to show updated combat state
            window.location.reload();
        } else {
            alert(data.message || 'Action failed. Please try again.');
            
            // Restore button state
            clickedButton.innerHTML = originalContent;
            buttons.forEach(btn => btn.disabled = false);
            actionInProgress = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        
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
        case '4':
        case 'f':
        case 'F':
            e.preventDefault();
            performAction('flee');
            break;
    }
});

// Auto-scroll combat log to bottom
document.addEventListener('DOMContentLoaded', function() {
    const combatLog = document.getElementById('combatLog');
    if (combatLog) {
        combatLog.scrollTop = combatLog.scrollHeight;
    }
});
</script>
@endsection