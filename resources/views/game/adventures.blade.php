@extends('game.layout')

@section('title', 'Adventures')
@section('meta_description', 'Embark on epic quests and adventures in Grassland Awakening - explore dungeons, fight monsters, and discover treasures.')

@push('styles')
@vite('resources/css/game/adventures.css')
@endpush

<meta name="generate-adventure-route" content="{{ route('game.generate-adventure') }}">

@section('content')
<!-- Adventures Background -->
<div class="adventures-background"></div>
<div class="adventures-overlay"></div>

<!-- Adventures UI Overlay System -->
<div class="adventures-ui-container">
    <!-- Header Panel - Top Center -->
    <div class="adventures-header-panel">
        <h1 class="mb-1">🗺️ Adventures & Quests</h1>
        <div class="small">Choose your next adventure and embark on epic journeys</div>
    </div>

    <!-- Adventures Panel - Center Left -->
    <div class="adventures-panel">
        <div class="mb-2">
            <h2 class="h6 mb-2">⚔️ Available Adventures</h2>
        </div>

        <!-- Adventure Generator Section -->
        <div class="mb-4 pb-3" style="border-bottom: 2px solid rgba(255,255,255,0.3);">
            <h3 class="h6 mb-3 text-warning">⚡ Generate New Adventure</h3>
            <form id="adventure-generator-form" method="POST" action="{{ route('game.generate-adventure') }}">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="text-white small mb-1">🛤️ Road Type:</label>
                        <select name="road_type" class="form-select form-select-sm bg-dark text-white border-secondary">
                            <option value="forest_path">🌲 Forest Path</option>
                            <option value="mountain_trail">⛰️ Mountain Trail</option>
                            <option value="coastal_road">🌊 Coastal Road</option>
                            <option value="desert_route">🏜️ Desert Route</option>
                            <option value="river_crossing">🏞️ River Crossing</option>
                            <option value="ancient_highway">🏛️ Ancient Highway</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="text-white small mb-1">⚔️ Difficulty:</label>
                        <select name="difficulty" class="form-select form-select-sm bg-dark text-white border-secondary">
                            <option value="easy">🟢 Easy</option>
                            <option value="medium" selected>🟡 Medium</option>
                            <option value="hard">🟠 Hard</option>
                            <option value="expert">🔴 Expert</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-warning btn-sm w-100" id="generate-adventure-btn">
                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                    🎲 Generate Adventure
                </button>
            </form>
        </div>

        <!-- Adventure Tabs -->
        <div class="adventure-tabs">
            <button class="adventure-tab active" data-category="available">📋 Available</button>
            <button class="adventure-tab" data-category="active">⚡ Active</button>
            <button class="adventure-tab" data-category="completed">✅ Completed</button>
        </div>

        <!-- Adventures Content -->
        <div class="adventures-content">
            <!-- Available Adventures -->
            <div class="adventure-category" data-category="available">
                @if($availableAdventures->isNotEmpty())
                    @foreach($availableAdventures as $adventure)
                    <div class="adventure-item" onclick="selectAdventure('{{ $adventure->id }}')">
                        @php
                            $difficultyClass = match($adventure->difficulty ?? 'normal') {
                                'easy' => 'difficulty-easy',
                                'normal' => 'difficulty-medium',
                                'hard' => 'difficulty-hard',
                                'nightmare' => 'difficulty-extreme',
                                default => 'difficulty-medium'
                            };
                            $difficultyIcon = match($adventure->difficulty ?? 'normal') {
                                'easy' => 'E',
                                'normal' => 'N',
                                'hard' => 'H',
                                'nightmare' => 'X',
                                default => 'N'
                            };
                        @endphp
                        <div class="adventure-difficulty {{ $difficultyClass }}">{{ $difficultyIcon }}</div>
                        <div class="adventure-header">
                            <div class="adventure-title">{{ $adventure->title }}</div>
                            <div class="adventure-level">{{ ucfirst($adventure->difficulty ?? 'normal') }}</div>
                        </div>
                        <div class="adventure-description">
                            {{ $adventure->description }}
                        </div>
                        <div class="adventure-rewards">
                            <div class="reward-item">🗓️ {{ $adventure->created_at->diffForHumans() }}</div>
                            @if(isset($adventure->generated_map['metadata']['estimated_duration']))
                            <div class="reward-item">⏱️ {{ $adventure->generated_map['metadata']['estimated_duration'] }}min</div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-4">
                        <div class="mb-3" style="font-size: 3rem; opacity: 0.5;">🗺️</div>
                        <h5 class="text-white-50">No Available Adventures</h5>
                        <p class="text-white-50 small">Generate a new adventure above to begin your journey!</p>
                    </div>
                @endif
            </div>

            <!-- Active Adventures -->
            <div class="adventure-category" data-category="active" style="display: none;">
                @if($activeAdventures->isNotEmpty())
                    @foreach($activeAdventures as $adventure)
                    <div class="adventure-item" onclick="continueAdventure('{{ $adventure->id }}')">
                        <div class="adventure-difficulty difficulty-medium">A</div>
                        <div class="adventure-header">
                            <div class="adventure-title">{{ $adventure->title }}</div>
                            <div class="adventure-level">In Progress</div>
                        </div>
                        <div class="adventure-description">
                            {{ $adventure->description }}
                        </div>
                        <div class="adventure-rewards">
                            <div class="reward-item">📍 Node {{ $adventure->current_node_id }}</div>
                            <div class="reward-item">📊 {{ number_format($adventure->getCurrentProgress() * 100) }}%</div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-4">
                        <div class="mb-3" style="font-size: 3rem; opacity: 0.5;">⚡</div>
                        <h5 class="text-white-50">No Active Adventures</h5>
                        <p class="text-white-50 small">Start an adventure to see it here!</p>
                    </div>
                @endif
            </div>

            <!-- Completed Adventures -->
            <div class="adventure-category" data-category="completed" style="display: none;">
                @if(isset($completedAdventures) && $completedAdventures->isNotEmpty())
                    @foreach($completedAdventures as $adventure)
                    <div class="adventure-item" style="opacity: 0.8;">
                        <div class="adventure-difficulty {{ $adventure->status === 'completed' ? 'difficulty-easy' : 'difficulty-hard' }}">
                            {{ $adventure->status === 'completed' ? '✓' : '✗' }}
                        </div>
                        <div class="adventure-header">
                            <div class="adventure-title">{{ $adventure->title }}</div>
                            <div class="adventure-level">{{ ucfirst($adventure->status) }}</div>
                        </div>
                        <div class="adventure-description">
                            {{ $adventure->description }}
                        </div>
                        <div class="adventure-rewards">
                            <div class="reward-item">🗓️ {{ $adventure->updated_at->diffForHumans() }}</div>
                            @if($adventure->status === 'completed')
                            <div class="reward-item">✅ Success</div>
                            @else
                            <div class="reward-item">❌ Failed</div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-4">
                        <div class="mb-3" style="font-size: 3rem; opacity: 0.5;">✅</div>
                        <h5 class="text-white-50">No Completed Adventures</h5>
                        <p class="text-white-50 small">Complete adventures to build your legend!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Character Status Panel - Top Right -->
    <div class="character-status-panel">
        <div class="mb-3">
            <h2 class="h6 mb-2">👤 Adventure Readiness</h2>
            <div class="character-stats">
                <div class="stat-item">
                    <div class="stat-value text-success">85/100</div>
                    <div class="stat-label">Health</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value text-warning">12</div>
                    <div class="stat-label">Level</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value text-primary">18</div>
                    <div class="stat-label">Armor</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value text-danger">1d8+3</div>
                    <div class="stat-label">Damage</div>
                </div>
            </div>

            <div class="readiness-indicator readiness-good">
                <div class="fw-bold">✅ Ready for Adventure!</div>
                <div class="small mt-1">Your character is well-prepared for most challenges</div>
            </div>
        </div>
    </div>


    <!-- Adventure Actions Panel - Bottom Right -->
    <div class="adventure-actions-panel">
        <div class="mb-2">
            <div class="fw-bold small">⚡ Adventure Actions</div>
        </div>

        <button class="adventure-btn success" onclick="startSelectedAdventure()" id="start-adventure-btn" disabled>
            🚀 Start Adventure
        </button>

        <button class="adventure-btn warning" onclick="continueActiveAdventure()" id="continue-adventure-btn"
                {{ $activeAdventures->isEmpty() ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' }}>
            ⏩ Continue Adventure
        </button>

        <button class="adventure-btn danger" onclick="abandonSelectedAdventure()" id="abandon-adventure-btn"
                {{ $activeAdventures->isEmpty() ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' }}>
            ❌ Abandon Adventure
        </button>

        <hr style="border-color: rgba(255,255,255,0.2); margin: 10px 0;">

        <a href="{{ route('game.character') }}" class="adventure-btn primary">
            👤 Check Equipment
        </a>

        <a href="{{ route('game.inventory') }}" class="adventure-btn warning">
            🎒 Manage Inventory
        </a>
    </div>

    <!-- Quick Actions Panel - Bottom Center -->
    <div class="quick-actions-panel">
        <div class="mb-2 text-center text-white">
            <div class="fw-bold small">Quick Actions</div>
        </div>
        <div class="d-flex gap-2 flex-wrap justify-content-center">
            <a href="{{ route('game.dashboard') }}" class="dashboard-btn success">
                🏠 Dashboard
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
</div>
@endsection

@push('scripts')
@vite('resources/js/game/adventures.js')
<script>
// Initialize page-specific data
document.addEventListener('DOMContentLoaded', function() {
    const adventuresData = {
        activeAdventures: {!! json_encode($activeAdventures->map(function($adventure) {
            return [
                'id' => $adventure->id,
                'generated_map' => $adventure->generated_map ?? null
            ];
        })) !!}
    };
    
    // Override the generic functions with server data
    window.continueActiveAdventure = function() {
        continueActiveAdventure(adventuresData.activeAdventures);
    };
    
    window.abandonSelectedAdventure = function() {
        abandonSelectedAdventure(adventuresData.activeAdventures);
    };
});
</script>
@endpush
