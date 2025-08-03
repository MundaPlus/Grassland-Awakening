@extends('game.layout')

@section('title', $adventure->title)

@section('content')
<div class="container-fluid">
    <!-- Adventure Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h3 mb-2">{{ $adventure->title }}</h1>
                            <p class="text-muted mb-0">{{ $adventure->road_type }} • {{ ucfirst($adventure->difficulty) }} Adventure</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <span class="badge bg-{{ $adventure->status === 'completed' ? 'success' : ($adventure->status === 'active' ? 'warning' : 'secondary') }} fs-6" 
                                  aria-label="Adventure status {{ $adventure->status }}">
                                {{ ucfirst($adventure->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Adventure Progress -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="h5 mb-0">Adventure Progress</h2>
                        <span class="text-muted" aria-label="Progress {{ $adventure->progress }}%">{{ $adventure->progress }}% Complete</span>
                    </div>
                    <div class="progress mb-2" role="progressbar" 
                         aria-valuenow="{{ $adventure->progress }}" 
                         aria-valuemin="0" 
                         aria-valuemax="100"
                         aria-label="Adventure progress {{ $adventure->progress }} percent">
                        <div class="progress-bar" style="width: {{ $adventure->progress }}%">
                            {{ $adventure->progress }}%
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">Started: {{ $adventure->created_at->format('M j, Y g:i A') }}</small>
                        <small class="text-muted">Estimated Duration: {{ $adventure->estimated_duration }} minutes</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Weather -->
    @if(isset($currentWeather))
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-cloud-sun fa-2x me-3" aria-hidden="true"></i>
                    <div>
                        <h4 class="alert-heading mb-1">Current Conditions</h4>
                        <p class="mb-0">
                            {{ $currentWeather['condition'] }} • {{ $currentWeather['temperature'] }}°C • {{ ucfirst($currentWeather['season']) }}
                            @if(isset($currentWeather['effects']))
                            <br><strong>Effects:</strong> {{ $currentWeather['effects'] }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Adventure Story -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header">
                    <h2 class="h5 mb-0">Adventure Story</h2>
                </div>
                <div class="card-body">
                    <div class="adventure-narrative" aria-label="Adventure story">
                        <p class="lead">{{ $adventure->description }}</p>
                        
                        @if($adventure->story_hooks)
                        <div class="story-hooks mt-3">
                            <h3 class="h6 text-muted">Story Elements:</h3>
                            <p class="text-muted">{{ $adventure->story_hooks }}</p>
                        </div>
                        @endif

                        @if(isset($adventureData['current_scene']))
                        <div class="current-scene mt-4 p-3 bg-light rounded">
                            <h3 class="h6 text-primary mb-2">Current Scene:</h3>
                            <p class="mb-0">{{ $adventureData['current_scene'] }}</p>
                        </div>
                        @endif
                    </div>

                    <!-- Adventure Choices -->
                    @if($adventure->status === 'active' && isset($adventureData['choices']) && !empty($adventureData['choices']))
                    <div class="adventure-choices mt-4">
                        <h3 class="h6 text-primary mb-3">What do you do?</h3>
                        <div class="choices-container">
                            @foreach($adventureData['choices'] as $index => $choice)
                            <button type="button" 
                                    class="btn btn-outline-primary btn-lg w-100 mb-2 choice-button" 
                                    onclick="makeChoice({{ $index }})"
                                    aria-label="Choice {{ $index + 1 }}: {{ $choice['text'] }}">
                                <div class="d-flex align-items-center">
                                    <div class="choice-number me-3">
                                        <span class="badge bg-primary">{{ $index + 1 }}</span>
                                    </div>
                                    <div class="choice-text text-start">
                                        {{ $choice['text'] }}
                                        @if(isset($choice['requirements']))
                                        <div class="small text-muted mt-1">
                                            <i class="fas fa-info-circle" aria-hidden="true"></i> 
                                            Requirements: {{ $choice['requirements'] }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Adventure Complete -->
                    @if($adventure->status === 'completed')
                    <div class="adventure-complete mt-4 p-3 bg-success bg-opacity-10 border border-success rounded">
                        <div class="text-center">
                            <i class="fas fa-check-circle fa-3x text-success mb-3" aria-hidden="true"></i>
                            <h3 class="h5 text-success">Adventure Complete!</h3>
                            <p class="mb-3">You have successfully completed this adventure.</p>
                            
                            @if(isset($adventureData['completion_story']))
                            <p class="text-muted mb-3">{{ $adventureData['completion_story'] }}</p>
                            @endif
                            
                            <a href="{{ route('game.adventures') }}" class="btn btn-success">
                                <i class="fas fa-map" aria-hidden="true"></i> Find New Adventures
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <!-- Adventure Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="h5 mb-0">Adventure Details</h2>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td>Difficulty</td>
                                <td>
                                    <span class="badge bg-{{ $adventure->difficulty === 'easy' ? 'success' : ($adventure->difficulty === 'medium' ? 'warning' : ($adventure->difficulty === 'hard' ? 'danger' : 'dark')) }}">
                                        {{ ucfirst($adventure->difficulty) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>Road Type</td>
                                <td>{{ $adventure->road_type }}</td>
                            </tr>
                            <tr>
                                <td>Duration</td>
                                <td>{{ $adventure->estimated_duration }} minutes</td>
                            </tr>
                            <tr>
                                <td>Level Req.</td>
                                <td>{{ $adventure->level_requirement }}</td>
                            </tr>
                            @if($adventure->seed)
                            <tr>
                                <td>Seed</td>
                                <td><code class="small">{{ $adventure->seed }}</code></td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Potential Rewards -->
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="h5 mb-0">Potential Rewards</h2>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        @if($adventure->gold_reward > 0)
                        <li class="mb-2">
                            <i class="fas fa-coins text-warning" aria-hidden="true"></i> 
                            {{ $adventure->gold_reward }} gold pieces
                        </li>
                        @endif
                        @if($adventure->experience_reward > 0)
                        <li class="mb-2">
                            <i class="fas fa-star text-info" aria-hidden="true"></i> 
                            {{ $adventure->experience_reward }} experience points
                        </li>
                        @endif
                        @if($adventure->item_rewards)
                        <li class="mb-2">
                            <i class="fas fa-gift text-success" aria-hidden="true"></i> 
                            Special items and treasures
                        </li>
                        @endif
                        @if(!$adventure->gold_reward && !$adventure->experience_reward && !$adventure->item_rewards)
                        <li class="text-muted">
                            <i class="fas fa-info-circle" aria-hidden="true"></i> 
                            Rewards will be revealed as you progress
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Adventure Actions -->
            @if($adventure->status === 'active')
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0">Adventure Actions</h2>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-warning" onclick="pauseAdventure()" aria-label="Pause adventure">
                            <i class="fas fa-pause" aria-hidden="true"></i> Pause Adventure
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="abandonAdventure()" aria-label="Abandon adventure">
                            <i class="fas fa-times" aria-hidden="true"></i> Abandon Adventure
                        </button>
                        <a href="{{ route('game.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-home" aria-hidden="true"></i> Return to Village
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Adventure Log -->
    @if(isset($adventureData['log']) && !empty($adventureData['log']))
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0">Adventure Log</h2>
                </div>
                <div class="card-body">
                    <div class="adventure-log" aria-live="polite" aria-label="Adventure events">
                        @foreach($adventureData['log'] as $logEntry)
                        <div class="log-entry mb-3 p-3 border-start border-3 border-primary bg-light">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h3 class="h6 mb-0">{{ $logEntry['title'] ?? 'Adventure Event' }}</h3>
                                <small class="text-muted">{{ $logEntry['timestamp'] ?? 'Unknown time' }}</small>
                            </div>
                            <p class="mb-0">{{ $logEntry['description'] }}</p>
                            @if(isset($logEntry['outcome']))
                            <div class="mt-2">
                                <span class="badge bg-{{ $logEntry['outcome']['type'] === 'success' ? 'success' : ($logEntry['outcome']['type'] === 'failure' ? 'danger' : 'info') }}">
                                    {{ $logEntry['outcome']['message'] }}
                                </span>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
.choice-button {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    text-align: left;
}

.choice-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.choice-button:active {
    transform: translateY(0);
}

.choice-number .badge {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.adventure-log .log-entry {
    transition: all 0.3s ease;
}

.adventure-log .log-entry:hover {
    background-color: rgba(13, 110, 253, 0.05) !important;
}

.current-scene {
    border-left: 4px solid var(--bs-primary);
}

@media (prefers-reduced-motion: reduce) {
    .choice-button,
    .adventure-log .log-entry {
        transition: none;
    }
}

@media (max-width: 768px) {
    .choice-button .d-flex {
        flex-direction: column;
        text-align: center;
    }
    
    .choice-number {
        margin-bottom: 0.5rem;
        margin-right: 0 !important;
    }
}
</style>

<script>
let choiceInProgress = false;

function makeChoice(choiceIndex) {
    if (choiceInProgress) {
        return;
    }
    
    choiceInProgress = true;
    
    // Visual feedback
    const buttons = document.querySelectorAll('.choice-button');
    buttons.forEach(btn => btn.disabled = true);
    
    // Show loading on clicked button
    const clickedButton = buttons[choiceIndex];
    const originalContent = clickedButton.innerHTML;
    clickedButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing choice...';
    
    fetch(`{{ route('game.adventure-choice', $adventure->id) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            choice: choiceIndex
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Announce choice result to screen readers
            announceToScreenReader(`Choice made. ${data.message || 'Adventure continues.'}`);
            
            // Reload page to show updated adventure state
            window.location.reload();
        } else {
            alert(data.message || 'Choice failed. Please try again.');
            
            // Restore button state
            clickedButton.innerHTML = originalContent;
            buttons.forEach(btn => btn.disabled = false);
            choiceInProgress = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        
        // Restore button state
        clickedButton.innerHTML = originalContent;
        buttons.forEach(btn => btn.disabled = false);
        choiceInProgress = false;
    });
}

function pauseAdventure() {
    if (confirm('Pause this adventure? You can resume it later from your dashboard.')) {
        fetch(`{{ route('game.pause-adventure', $adventure->id) }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '{{ route('game.dashboard') }}';
            } else {
                alert(data.message || 'Failed to pause adventure.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
}

function abandonAdventure() {
    if (confirm('Are you sure you want to abandon this adventure? All progress will be lost.')) {
        fetch(`{{ route('game.abandon-adventure', $adventure->id) }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '{{ route('game.adventures') }}';
            } else {
                alert(data.message || 'Failed to abandon adventure.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
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

// Keyboard shortcuts for choices
document.addEventListener('keydown', function(e) {
    if (choiceInProgress) return;
    
    const choiceButtons = document.querySelectorAll('.choice-button');
    const choiceNumber = parseInt(e.key);
    
    if (choiceNumber >= 1 && choiceNumber <= choiceButtons.length) {
        e.preventDefault();
        makeChoice(choiceNumber - 1);
    }
});

// Auto-scroll to current scene or choices
document.addEventListener('DOMContentLoaded', function() {
    const currentScene = document.querySelector('.current-scene');
    const choices = document.querySelector('.adventure-choices');
    
    if (choices) {
        choices.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else if (currentScene) {
        currentScene.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
});
</script>
@endsection