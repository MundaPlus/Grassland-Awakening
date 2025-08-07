@extends('game.layout')

@section('title', 'Adventures')
@section('meta_description', 'Embark on epic quests and adventures in Grassland Awakening - explore dungeons, fight monsters, and discover treasures.')

@push('styles')
<style>
    /* Full-screen immersive layout */
    body {
        overflow: hidden;
    }

    .adventures-background {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-image: url('/img/backgrounds/adventures.png');
        z-index: 1;
    }

    .adventures-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: linear-gradient(rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.2));
        z-index: 2;
    }

    .adventures-ui-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 10;
        pointer-events: none;
    }

    .adventures-ui-container > * {
        pointer-events: all;
    }

    /* Header Panel - Top Center */
    .adventures-header-panel {
        position: absolute;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(33, 37, 41, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 15px 25px;
        color: white;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        min-width: 500px;
    }

    /* Adventures Panel - Center Left */
    .adventures-panel {
        position: absolute;
        top: 150px;
        left: 20px;
        width: 45%;
        height: calc(100vh - 300px);
        background: rgba(220, 53, 69, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 20px;
        color: white;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        display: flex;
        flex-direction: column;
    }

    .adventure-tabs {
        display: flex;
        gap: 5px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }

    .adventure-tab {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: rgba(255, 255, 255, 0.7);
        padding: 8px 12px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        flex: 1;
        text-align: center;
    }

    .adventure-tab.active,
    .adventure-tab:hover {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border-color: rgba(255, 255, 255, 0.4);
    }

    .adventures-content {
        flex: 1;
        overflow-y: auto;
        padding-right: 10px;
    }

    .adventure-item {
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .adventure-item:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.4);
        transform: translateY(-2px);
    }

    .adventure-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .adventure-title {
        font-weight: bold;
        font-size: 1rem;
        flex: 1;
    }

    .adventure-level {
        background: rgba(0, 0, 0, 0.3);
        border-radius: 4px;
        padding: 3px 8px;
        font-size: 0.75rem;
        margin-left: 10px;
    }

    .adventure-description {
        font-size: 0.85rem;
        opacity: 0.9;
        margin-bottom: 8px;
        line-height: 1.3;
    }

    .adventure-rewards {
        display: flex;
        gap: 10px;
        align-items: center;
        font-size: 0.8rem;
    }

    .reward-item {
        background: rgba(0, 0, 0, 0.2);
        border-radius: 4px;
        padding: 2px 6px;
    }

    .adventure-difficulty {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.8rem;
    }

    .difficulty-easy { background: #28a745; }
    .difficulty-medium { background: #ffc107; color: #333; }
    .difficulty-hard { background: #dc3545; }
    .difficulty-extreme { background: #6f42c1; }

    /* Character Status Panel - Top Right */
    .character-status-panel {
        position: absolute;
        top: 120px;
        right: 20px;
        width: 300px;
        background: rgba(40, 167, 69, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 20px;
        color: white;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }

    .character-stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-bottom: 15px;
    }

    .stat-item {
        text-align: center;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 8px;
    }

    .stat-value {
        font-weight: bold;
        font-size: 1.1em;
    }

    .stat-label {
        font-size: 0.8em;
        opacity: 0.8;
    }

    .readiness-indicator {
        background: rgba(0, 0, 0, 0.2);
        border-radius: 8px;
        padding: 10px;
        text-align: center;
    }

    .readiness-good {
        border: 2px solid #28a745;
        color: #28a745;
    }

    .readiness-warning {
        border: 2px solid #ffc107;
        color: #ffc107;
    }

    .readiness-danger {
        border: 2px solid #dc3545;
        color: #dc3545;
    }

    /* Active Quest Panel - Center Right */
    .active-quest-panel {
        position: absolute;
        top: 420px;
        right: 20px;
        width: 300px;
        height: 200px;
        background: rgba(255, 193, 7, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 20px;
        color: #333;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }

    .quest-progress {
        background: rgba(0, 0, 0, 0.2);
        border-radius: 8px;
        padding: 10px;
        margin-top: 10px;
    }

    .progress-bar {
        width: 100%;
        height: 8px;
        background: rgba(0, 0, 0, 0.3);
        border-radius: 4px;
        overflow: hidden;
        margin-top: 5px;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #28a745, #20c997);
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    /* Adventure Actions Panel - Bottom Right */
    .adventure-actions-panel {
        position: absolute;
        bottom: 100px;
        right: 20px;
        width: 300px;
        background: rgba(23, 162, 184, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 15px;
        color: white;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }

    .adventure-btn {
        background: linear-gradient(135deg, #495057, #6c757d);
        border: none;
        color: white;
        padding: 10px 15px;
        margin: 5px 0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        text-decoration: none;
        display: block;
        width: 100%;
        font-size: 0.85rem;
        text-align: center;
    }

    .adventure-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 12px rgba(0, 0, 0, 0.3);
        color: white;
        text-decoration: none;
    }

    .adventure-btn.primary { background: linear-gradient(135deg, #007bff, #0056b3); }
    .adventure-btn.success { background: linear-gradient(135deg, #28a745, #1e7e34); }
    .adventure-btn.warning { background: linear-gradient(135deg, #ffc107, #e0a800); }
    .adventure-btn.danger { background: linear-gradient(135deg, #dc3545, #c82333); }

    /* Quick Actions Panel - Bottom Center */
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

    /* Custom Scrollbar */
    .adventures-content::-webkit-scrollbar {
        width: 8px;
    }

    .adventures-content::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
    }

    .adventures-content::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 4px;
    }

    .adventures-content::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.5);
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .adventures-panel {
            width: 40%;
        }

        .character-status-panel, .active-quest-panel, .adventure-actions-panel {
            width: 250px;
        }
    }

    @media (max-width: 768px) {
        .adventures-panel {
            left: 10px;
            right: 10px;
            width: auto;
            height: calc(100vh - 260px);
        }

        .character-status-panel, .active-quest-panel, .adventure-actions-panel {
            display: none;
        }

        .adventures-header-panel {
            left: 10px;
            right: 10px;
            transform: none;
            min-width: auto;
        }
    }
</style>
@endpush

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
<script>
// Adventure category switching
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.adventure-tab');
    const categories = document.querySelectorAll('.adventure-category');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetCategory = this.getAttribute('data-category');

            // Update active tab
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // Show target category, hide others
            categories.forEach(cat => {
                if (cat.getAttribute('data-category') === targetCategory) {
                    cat.style.display = 'block';
                } else {
                    cat.style.display = 'none';
                }
            });
        });
    });
});

let selectedAdventureId = null;

// Adventure Generator Form Submission
document.getElementById('adventure-generator-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const btn = document.getElementById('generate-adventure-btn');
    const spinner = btn.querySelector('.spinner-border');
    const originalText = btn.innerHTML;

    // Show loading state
    btn.disabled = true;
    spinner.classList.remove('d-none');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Generating...';

    // Submit form data
    const formData = new FormData(this);

    fetch('{{ route('game.generate-adventure') }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Refresh the page to show new adventure
            window.location.reload();
        } else {
            alert('Failed to generate adventure: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while generating the adventure.');
    })
    .finally(() => {
        // Reset button state
        btn.disabled = false;
        spinner.classList.add('d-none');
        btn.innerHTML = originalText;
    });
});

function selectAdventure(adventureId) {
    selectedAdventureId = adventureId;

    // Remove previous selections
    document.querySelectorAll('.adventure-item').forEach(item => {
        item.style.borderColor = 'rgba(255, 255, 255, 0.2)';
    });

    // Highlight selected adventure
    event.currentTarget.style.borderColor = 'rgba(255, 255, 255, 0.8)';

    // Enable start button
    const startBtn = document.getElementById('start-adventure-btn');
    startBtn.disabled = false;
    startBtn.classList.remove('btn-secondary');
    startBtn.classList.add('btn-success');

    console.log('Selected adventure:', adventureId);
}

function startSelectedAdventure() {
    if (selectedAdventureId) {
        // Create form and submit to start adventure
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/game/adventures/' + selectedAdventureId + '/start';

        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken.getAttribute('content');
            form.appendChild(csrfInput);
        }

        document.body.appendChild(form);
        form.submit();
    } else {
        alert('Please select an adventure first!');
    }
}

function continueAdventure(adventureId) {
    window.location.href = '/game/adventure/' + adventureId + '/map';
}

function continueActiveAdventure() {
    @if($activeAdventures->isNotEmpty())
        @php
            $firstActiveAdventure = $activeAdventures->first();
            $hasNodeMap = isset($firstActiveAdventure->generated_map) &&
                         isset($firstActiveAdventure->generated_map['map']) &&
                         isset($firstActiveAdventure->generated_map['map']['nodes']);
        @endphp
        @if($hasNodeMap)
            continueAdventure('{{ $firstActiveAdventure->id }}');
        @else
            alert('This adventure has no node map and cannot be continued. Please abandon it and generate a new adventure.');
        @endif
    @else
        alert('No active adventures to continue.');
    @endif
}

function abandonSelectedAdventure() {
    @if($activeAdventures->isNotEmpty())
        if (confirm('Are you sure you want to abandon your current adventure? All progress will be lost!')) {
            // Create form and submit to abandon adventure
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/game/adventures/{{ $activeAdventures->first()->id }}/abandon';

            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken.getAttribute('content');
                form.appendChild(csrfInput);
            }

            document.body.appendChild(form);
            form.submit();
        }
    @else
        alert('No active adventures to abandon.');
    @endif
}
</script>
@endpush
