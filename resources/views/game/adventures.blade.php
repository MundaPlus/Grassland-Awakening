@extends('game.layout')

@section('title', 'Adventures')

@section('content')
<div class="container-fluid">
    <!-- Adventures Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h3 mb-2">Available Adventures</h1>
                            <p class="text-muted mb-0">Explore the world and discover new challenges</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateAdventureModal" aria-label="Generate new adventure">
                                <i class="fas fa-plus" aria-hidden="true"></i> Generate Adventure
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Weather Effects -->
    @if(isset($weatherEffects) && !empty($weatherEffects))
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-cloud-sun fa-2x me-3" aria-hidden="true"></i>
                    <div>
                        <h4 class="alert-heading mb-1">Current Weather Effects</h4>
                        <p class="mb-0">{{ is_string($weatherEffects) ? $weatherEffects : 'Weather conditions may affect your adventures' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Active Adventures -->
    @if($activeAdventures->isNotEmpty())
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0">Active Adventures</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($activeAdventures as $adventure)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 border-warning adventure-card" role="article" aria-labelledby="adventure-{{ $adventure->id }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h3 class="h6 mb-0" id="adventure-{{ $adventure->id }}">{{ $adventure->title }}</h3>
                                        @php
                                            $progress = round($adventure->getCurrentProgress() * 100);
                                        @endphp
                                        @if($progress >= 100)
                                            <span class="badge bg-success" aria-label="Completed">Completed</span>
                                        @else
                                            <span class="badge bg-warning" aria-label="In progress">In Progress</span>
                                        @endif
                                    </div>
                                    <p class="small text-muted mb-2">{{ ucfirst(str_replace('_', ' ', $adventure->road)) }} • {{ ucfirst($adventure->difficulty) }}</p>
                                    <p class="card-text">{{ Str::limit($adventure->description, 100) }}</p>
                                    
                                    <!-- Progress Bar -->
                                    @php
                                        $progress = round($adventure->getCurrentProgress() * 100);
                                    @endphp
                                    <div class="progress mb-3" role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100" aria-label="Adventure progress {{ $progress }}%">
                                        <div class="progress-bar" style="width: {{ $progress }}%">
                                            {{ $progress }}%
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <a href="{{ route('game.adventure-map', $adventure->id) }}" class="btn btn-primary btn-sm" style="flex: 2;" aria-label="Continue {{ $adventure->title }}">
                                            Open Map
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-sm" style="flex: 1;"
                                                onclick="abandonAdventure({{ $adventure->id }})"
                                                aria-label="Abandon {{ $adventure->title }}">
                                            Abandon
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Available Adventures -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Available Adventures</h2>
                    <div class="adventure-filters">
                        <select class="form-select form-select-sm" id="difficultyFilter" onchange="filterAdventures()" aria-label="Filter by difficulty">
                            <option value="">All Difficulties</option>
                            <option value="easy">Easy</option>
                            <option value="medium">Medium</option>
                            <option value="hard">Hard</option>
                            <option value="expert">Expert</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    @if($availableAdventures->isEmpty())
                    <div class="text-center py-4">
                        <i class="fas fa-map fa-3x text-muted mb-3" aria-hidden="true"></i>
                        <p class="text-muted">No adventures available. Generate new adventures to explore!</p>
                    </div>
                    @else
                    <div class="row" id="adventuresContainer">
                        @foreach($availableAdventures as $adventure)
                        <div class="col-md-6 col-lg-4 mb-3 adventure-item" data-difficulty="{{ $adventure->difficulty }}">
                            <div class="card h-100 adventure-card" role="article" aria-labelledby="adventure-{{ $adventure->id }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h3 class="h6 mb-0" id="adventure-{{ $adventure->id }}">{{ $adventure->title }}</h3>
                                        <span class="badge bg-{{ $adventure->difficulty === 'easy' ? 'success' : ($adventure->difficulty === 'medium' ? 'warning' : ($adventure->difficulty === 'hard' ? 'danger' : 'dark')) }}" 
                                              aria-label="Difficulty {{ $adventure->difficulty }}">
                                            {{ ucfirst($adventure->difficulty) }}
                                        </span>
                                    </div>
                                    
                                    <div class="adventure-meta mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-road" aria-hidden="true"></i> {{ $adventure->road_type }}
                                            <span class="mx-2">•</span>
                                            <i class="fas fa-clock" aria-hidden="true"></i> {{ $adventure->estimated_duration }} min
                                        </small>
                                    </div>

                                    <p class="card-text">{{ Str::limit($adventure->description, 120) }}</p>

                                    <!-- Rewards Preview -->
                                    <div class="rewards-preview mb-3">
                                        <h4 class="small text-muted mb-1">Potential Rewards:</h4>
                                        <div class="d-flex gap-2 flex-wrap">
                                            @if($adventure->gold_reward > 0)
                                            <span class="badge bg-warning text-dark" aria-label="Gold reward {{ $adventure->gold_reward }}">
                                                <i class="fas fa-coins" aria-hidden="true"></i> {{ $adventure->gold_reward }}
                                            </span>
                                            @endif
                                            @if($adventure->experience_reward > 0)
                                            <span class="badge bg-info" aria-label="Experience reward {{ $adventure->experience_reward }}">
                                                <i class="fas fa-star" aria-hidden="true"></i> {{ $adventure->experience_reward }} XP
                                            </span>
                                            @endif
                                            @if($adventure->item_rewards)
                                            <span class="badge bg-success" aria-label="Item rewards available">
                                                <i class="fas fa-gift" aria-hidden="true"></i> Items
                                            </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Requirements -->
                                    @if($adventure->level_requirement > 1)
                                    <div class="requirements mb-3">
                                        <small class="text-muted">
                                            <i class="fas fa-exclamation-triangle" aria-hidden="true"></i> 
                                            Requires level {{ $adventure->level_requirement }}
                                        </small>
                                    </div>
                                    @endif

                                    <div class="row g-2">
                                        <div class="col-8">
                                            @if($player->level >= $adventure->level_requirement)
                                            <button type="button" class="btn btn-success btn-sm w-100" 
                                                    onclick="startAdventure({{ $adventure->id }})"
                                                    aria-label="Start {{ $adventure->title }}">
                                                Start Adventure
                                            </button>
                                            @else
                                            <button type="button" class="btn btn-secondary btn-sm w-100" disabled 
                                                    aria-label="Level {{ $adventure->level_requirement }} required for {{ $adventure->title }}">
                                                Level {{ $adventure->level_requirement }} Required
                                            </button>
                                            @endif
                                        </div>
                                        <div class="col-2">
                                            <button type="button" class="btn btn-outline-secondary btn-sm w-100" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#adventurePreviewModal{{ $adventure->id }}"
                                                    aria-label="Preview {{ $adventure->title }}">
                                                Preview
                                            </button>
                                        </div>
                                        <div class="col-2">
                                            <button type="button" class="btn btn-outline-danger btn-sm w-100" 
                                                    onclick="deleteAdventure({{ $adventure->id }})"
                                                    aria-label="Delete {{ $adventure->title }}">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Completed Adventures -->
    @if($completedAdventures->isNotEmpty())
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0">Recently Completed Adventures</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($completedAdventures as $adventure)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 border-success adventure-card" role="article" aria-labelledby="completed-adventure-{{ $adventure->id }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h3 class="h6 mb-0" id="completed-adventure-{{ $adventure->id }}">{{ $adventure->title }}</h3>
                                        @if($adventure->status === 'completed')
                                            <span class="badge bg-success" aria-label="Completed">✓ Completed</span>
                                        @else
                                            <span class="badge bg-danger" aria-label="Failed">✗ Failed</span>
                                        @endif
                                    </div>
                                    <p class="small text-muted mb-2">{{ ucfirst(str_replace('_', ' ', $adventure->road)) }} • {{ ucfirst($adventure->difficulty) }}</p>
                                    <p class="card-text">{{ Str::limit($adventure->description, 100) }}</p>
                                    
                                    <!-- Completion Stats -->
                                    <div class="completion-stats mb-3">
                                        @if($adventure->status === 'completed')
                                            <small class="text-success">
                                                <i class="fas fa-trophy" aria-hidden="true"></i> Adventure Completed
                                                @if($adventure->completed_at)
                                                    <br><i class="fas fa-calendar" aria-hidden="true"></i> {{ $adventure->completed_at->format('M d, Y') }}
                                                @endif
                                            </small>
                                        @else
                                            <small class="text-danger">
                                                <i class="fas fa-skull" aria-hidden="true"></i> Adventure Failed
                                                @if($adventure->completed_at)
                                                    <br><i class="fas fa-calendar" aria-hidden="true"></i> {{ $adventure->completed_at->format('M d, Y') }}
                                                @endif
                                            </small>
                                        @endif
                                    </div>

                                    <div class="row g-2">
                                        <div class="col-8">
                                            @if($adventure->status === 'completed')
                                                <button type="button" class="btn btn-outline-success btn-sm w-100" disabled>
                                                    ✓ Completed
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-outline-danger btn-sm w-100" disabled>
                                                    ✗ Failed
                                                </button>
                                            @endif
                                        </div>
                                        <div class="col-4">
                                            <button type="button" class="btn btn-outline-info btn-sm w-100" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#adventurePreviewModal{{ $adventure->id }}"
                                                    aria-label="View {{ $adventure->title }}">
                                                View
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Generate Adventure Modal -->
<div class="modal fade" id="generateAdventureModal" tabindex="-1" aria-labelledby="generateAdventureModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('game.generate-adventure') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="generateAdventureModalLabel">Generate New Adventure</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="adventure_seed" class="form-label">Adventure Seed (Optional)</label>
                        <input type="text" class="form-control" id="adventure_seed" name="seed" aria-describedby="seedHelp">
                        <div id="seedHelp" class="form-text">Leave empty for random generation, or enter a seed for reproducible adventures</div>
                    </div>
                    <div class="mb-3">
                        <label for="adventure_difficulty" class="form-label">Preferred Difficulty</label>
                        <select class="form-select" id="adventure_difficulty" name="difficulty" aria-describedby="difficultyHelp">
                            <option value="">Auto (Based on level)</option>
                            <option value="easy">Easy</option>
                            <option value="medium">Medium</option>
                            <option value="hard">Hard</option>
                            @if($player->level >= 10)
                            <option value="expert">Expert</option>
                            @endif
                        </select>
                        <div id="difficultyHelp" class="form-text">Higher difficulty means better rewards but greater risk</div>
                    </div>
                    <div class="mb-3">
                        <label for="road_type" class="form-label">Road Type</label>
                        <select class="form-select" id="road_type" name="road_type" aria-describedby="roadHelp">
                            <option value="">Any Road</option>
                            <option value="forest_path">Forest Path</option>
                            <option value="mountain_trail">Mountain Trail</option>
                            <option value="coastal_road">Coastal Road</option>
                            <option value="desert_route">Desert Route</option>
                            <option value="river_crossing">River Crossing</option>
                            <option value="ancient_highway">Ancient Highway</option>
                        </select>
                        <div id="roadHelp" class="form-text">Different road types offer unique encounters and challenges</div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle" aria-hidden="true"></i>
                        <strong>Generation Cost:</strong> 10 gold pieces
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Generate Adventure</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Adventure Preview Modals -->
@foreach($availableAdventures as $adventure)
<div class="modal fade" id="adventurePreviewModal{{ $adventure->id }}" tabindex="-1" aria-labelledby="adventurePreviewModal{{ $adventure->id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adventurePreviewModal{{ $adventure->id }}Label">{{ $adventure->title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <h6>Adventure Description</h6>
                        <p>{{ $adventure->description }}</p>
                        
                        @if($adventure->story_hooks)
                        <h6>Story Elements</h6>
                        <p class="text-muted">{{ $adventure->story_hooks }}</p>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <h6>Adventure Details</h6>
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <td>Difficulty</td>
                                    <td><span class="badge bg-{{ $adventure->difficulty === 'easy' ? 'success' : ($adventure->difficulty === 'medium' ? 'warning' : ($adventure->difficulty === 'hard' ? 'danger' : 'dark')) }}">{{ ucfirst($adventure->difficulty) }}</span></td>
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
                            </tbody>
                        </table>

                        <h6>Rewards</h6>
                        <ul class="list-unstyled">
                            @if($adventure->gold_reward > 0)
                            <li><i class="fas fa-coins text-warning" aria-hidden="true"></i> {{ $adventure->gold_reward }} gold</li>
                            @endif
                            @if($adventure->experience_reward > 0)
                            <li><i class="fas fa-star text-info" aria-hidden="true"></i> {{ $adventure->experience_reward }} experience</li>
                            @endif
                            @if($adventure->item_rewards)
                            <li><i class="fas fa-gift text-success" aria-hidden="true"></i> Potential items</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                @if($player->level >= $adventure->level_requirement)
                <button type="button" class="btn btn-success" onclick="startAdventure({{ $adventure->id }})" data-bs-dismiss="modal">Start Adventure</button>
                @endif
            </div>
        </div>
    </div>
</div>
@endforeach

<!-- Completed Adventure Preview Modals -->
@foreach($completedAdventures as $adventure)
<div class="modal fade" id="adventurePreviewModal{{ $adventure->id }}" tabindex="-1" aria-labelledby="adventurePreviewModal{{ $adventure->id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adventurePreviewModal{{ $adventure->id }}Label">{{ $adventure->title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <h6>Adventure Description</h6>
                        <p>{{ $adventure->description }}</p>
                        
                        <h6>Adventure Results</h6>
                        <div class="result-summary">
                            @if($adventure->status === 'completed')
                                <div class="alert alert-success">
                                    <i class="fas fa-trophy"></i> <strong>Adventure Completed Successfully!</strong>
                                    @if($adventure->completed_at)
                                        <br><small>Completed on {{ $adventure->completed_at->format('M j, Y \a\t H:i') }}</small>
                                    @endif
                                </div>
                            @else
                                <div class="alert alert-danger">
                                    <i class="fas fa-times-circle"></i> <strong>Adventure Failed</strong>
                                    @if($adventure->completed_at)
                                        <br><small>Failed on {{ $adventure->completed_at->format('M j, Y \a\t H:i') }}</small>
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        @if($adventure->status === 'completed')
                            <h6>Adventure Statistics</h6>
                            <ul class="list-unstyled">
                                <li><strong>Nodes Completed:</strong> {{ count($adventure->completed_nodes ?? []) }}</li>
                                <li><strong>Progress:</strong> {{ round($adventure->getCurrentProgress() * 100) }}%</li>
                                @if($adventure->currency_earned > 0)
                                <li><strong>Gold Earned:</strong> {{ $adventure->currency_earned }}</li>
                                @endif
                                @if($adventure->collected_loot)
                                <li><strong>Items Found:</strong> {{ count($adventure->collected_loot) }}</li>
                                @endif
                            </ul>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <h6>Adventure Details</h6>
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <td>Difficulty</td>
                                    <td><span class="badge bg-{{ $adventure->difficulty === 'easy' ? 'success' : ($adventure->difficulty === 'normal' ? 'warning' : ($adventure->difficulty === 'hard' ? 'danger' : 'dark')) }}">{{ ucfirst($adventure->difficulty) }}</span></td>
                                </tr>
                                <tr>
                                    <td>Road</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $adventure->road)) }}</td>
                                </tr>
                                <tr>
                                    <td>Status</td>
                                    <td>
                                        @if($adventure->status === 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @else
                                            <span class="badge bg-danger">Failed</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Seed</td>
                                    <td><code class="small">{{ $adventure->seed }}</code></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach

<script>
function startAdventure(adventureId) {
    GameUI.showConfirmModal(
        'Start Adventure',
        'Start this adventure? You will be committed until it is completed or abandoned.',
        function() {
            fetch(`/game/adventures/${adventureId}/start`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    GameUI.showToast('Adventure started successfully!', 'success');
                    setTimeout(() => {
                        window.location.href = `/game/adventure/${adventureId}/map`;
                    }, 1000);
                } else {
                    GameUI.showErrorModal(data.message || 'Failed to start adventure. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                GameUI.showErrorModal('An error occurred. Please try again.');
            });
        }
    );
}

function abandonAdventure(adventureId) {
    GameUI.showConfirmModal(
        'Abandon Adventure',
        'Are you sure you want to abandon this adventure? Progress will be lost.',
        function() {
            fetch(`/game/adventures/${adventureId}/abandon`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    GameUI.showToast('Adventure abandoned', 'info');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    GameUI.showErrorModal(data.message || 'Failed to abandon adventure. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                GameUI.showErrorModal('An error occurred. Please try again.');
            });
        }
    );
}

function deleteAdventure(adventureId) {
    GameUI.showConfirmModal(
        'Delete Adventure',
        'Are you sure you want to delete this adventure? This action cannot be undone.',
        function() {
            fetch(`/game/adventures/${adventureId}/abandon`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    GameUI.showToast('Adventure deleted', 'info');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    GameUI.showErrorModal(data.message || 'Failed to delete adventure. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                GameUI.showErrorModal('An error occurred. Please try again.');
            });
        }
    );
}

function filterAdventures() {
    const filter = document.getElementById('difficultyFilter').value;
    const adventures = document.querySelectorAll('.adventure-item');
    
    adventures.forEach(adventure => {
        if (filter === '' || adventure.dataset.difficulty === filter) {
            adventure.style.display = '';
        } else {
            adventure.style.display = 'none';
        }
    });
}
</script>
@endsection