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

        <!-- Adventure Tabs -->
        <div class="adventure-tabs">
            <button class="adventure-tab active" data-category="dungeons">🏰 Dungeons</button>
            <button class="adventure-tab" data-category="exploration">🌲 Exploration</button>
            <button class="adventure-tab" data-category="quests">📜 Quests</button>
        </div>

        <!-- Adventures Content -->
        <div class="adventures-content">
            <!-- Dungeons -->
            <div class="adventure-category" data-category="dungeons">
                <div class="adventure-item" onclick="selectAdventure('goblin-cave')">
                    <div class="adventure-difficulty difficulty-easy">E</div>
                    <div class="adventure-header">
                        <div class="adventure-title">🏴‍☠️ Goblin Cave</div>
                        <div class="adventure-level">Level 1-3</div>
                    </div>
                    <div class="adventure-description">
                        A small cave system inhabited by goblins. Perfect for new adventurers to test their skills.
                    </div>
                    <div class="adventure-rewards">
                        <div class="reward-item">💰 50-100 Gold</div>
                        <div class="reward-item">🎒 Basic Loot</div>
                        <div class="reward-item">⭐ 25 XP</div>
                    </div>
                </div>

                <div class="adventure-item" onclick="selectAdventure('dark-forest')">
                    <div class="adventure-difficulty difficulty-medium">M</div>
                    <div class="adventure-header">
                        <div class="adventure-title">🌑 Dark Forest Depths</div>
                        <div class="adventure-level">Level 3-5</div>
                    </div>
                    <div class="adventure-description">
                        Ancient trees hide dangerous creatures and forgotten treasures in this mysterious woodland.
                    </div>
                    <div class="adventure-rewards">
                        <div class="reward-item">💰 100-200 Gold</div>
                        <div class="reward-item">🎒 Rare Items</div>
                        <div class="reward-item">⭐ 50 XP</div>
                    </div>
                </div>

                <div class="adventure-item" onclick="selectAdventure('dragon-lair')">
                    <div class="adventure-difficulty difficulty-extreme">X</div>
                    <div class="adventure-header">
                        <div class="adventure-title">🐉 Ancient Dragon's Lair</div>
                        <div class="adventure-level">Level 8+</div>
                    </div>
                    <div class="adventure-description">
                        Face the legendary dragon that has terrorized the lands for centuries. Only the bravest dare enter.
                    </div>
                    <div class="adventure-rewards">
                        <div class="reward-item">💰 1000+ Gold</div>
                        <div class="reward-item">🎒 Legendary Items</div>
                        <div class="reward-item">⭐ 500 XP</div>
                    </div>
                </div>
            </div>

            <!-- Exploration -->
            <div class="adventure-category" data-category="exploration" style="display: none;">
                <div class="adventure-item" onclick="selectAdventure('meadow-exploration')">
                    <div class="adventure-difficulty difficulty-easy">E</div>
                    <div class="adventure-header">
                        <div class="adventure-title">🌸 Peaceful Meadows</div>
                        <div class="adventure-level">Level 1+</div>
                    </div>
                    <div class="adventure-description">
                        Explore the beautiful grasslands surrounding your village. Gather herbs and enjoy nature.
                    </div>
                    <div class="adventure-rewards">
                        <div class="reward-item">🌿 Herbs</div>
                        <div class="reward-item">⭐ 10 XP</div>
                    </div>
                </div>

                <div class="adventure-item" onclick="selectAdventure('mountain-path')">
                    <div class="adventure-difficulty difficulty-medium">M</div>
                    <div class="adventure-header">
                        <div class="adventure-title">⛰️ Mountain Path</div>
                        <div class="adventure-level">Level 4+</div>
                    </div>
                    <div class="adventure-description">
                        Trek through treacherous mountain paths to discover hidden valleys and ancient ruins.
                    </div>
                    <div class="adventure-rewards">
                        <div class="reward-item">🏺 Artifacts</div>
                        <div class="reward-item">💎 Gems</div>
                        <div class="reward-item">⭐ 75 XP</div>
                    </div>
                </div>
            </div>

            <!-- Quests -->
            <div class="adventure-category" data-category="quests" style="display: none;">
                <div class="adventure-item" onclick="selectAdventure('merchant-delivery')">
                    <div class="adventure-difficulty difficulty-easy">E</div>
                    <div class="adventure-header">
                        <div class="adventure-title">📦 Merchant's Delivery</div>
                        <div class="adventure-level">Level 1+</div>
                    </div>
                    <div class="adventure-description">
                        Help the local merchant deliver goods to the neighboring village safely.
                    </div>
                    <div class="adventure-rewards">
                        <div class="reward-item">💰 75 Gold</div>
                        <div class="reward-item">⭐ 20 XP</div>
                    </div>
                </div>

                <div class="adventure-item" onclick="selectAdventure('missing-scholar')">
                    <div class="adventure-difficulty difficulty-hard">H</div>
                    <div class="adventure-header">
                        <div class="adventure-title">🎓 The Missing Scholar</div>
                        <div class="adventure-level">Level 5+</div>
                    </div>
                    <div class="adventure-description">
                        A renowned scholar has gone missing while researching ancient magic. Find them before it's too late.
                    </div>
                    <div class="adventure-rewards">
                        <div class="reward-item">💰 300 Gold</div>
                        <div class="reward-item">📚 Spell Tome</div>
                        <div class="reward-item">⭐ 150 XP</div>
                    </div>
                </div>
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

    <!-- Active Quest Panel - Center Right -->
    <div class="active-quest-panel">
        <div class="mb-2">
            <div class="fw-bold small">🎯 Active Quest</div>
        </div>
        <div class="fw-bold mb-2">📦 Merchant's Delivery</div>
        <div class="small mb-2">
            Deliver goods safely to Riverside Village. Watch out for bandits on the road!
        </div>

        <div class="quest-progress">
            <div class="small fw-bold">Progress: 3/5 Checkpoints</div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: 60%"></div>
            </div>
            <div class="small mt-1">Next: Cross the Old Bridge</div>
        </div>
    </div>

    <!-- Adventure Actions Panel - Bottom Right -->
    <div class="adventure-actions-panel">
        <div class="mb-2">
            <div class="fw-bold small">⚡ Adventure Actions</div>
        </div>

        <button class="adventure-btn danger" onclick="startSelectedAdventure()">
            🚀 Start Adventure
        </button>

        <button class="adventure-btn warning" onclick="continueCurrentQuest()">
            ⏩ Continue Current Quest
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

function selectAdventure(adventureId) {
    selectedAdventureId = adventureId;

    // Remove previous selections
    document.querySelectorAll('.adventure-item').forEach(item => {
        item.style.borderColor = 'rgba(255, 255, 255, 0.2)';
    });

    // Highlight selected adventure
    event.currentTarget.style.borderColor = 'rgba(255, 255, 255, 0.8)';

    console.log('Selected adventure:', adventureId);
}

function startSelectedAdventure() {
    if (selectedAdventureId) {
        alert('Starting adventure: ' + selectedAdventureId + '\n\nThis would redirect to the adventure/combat system!');
        // Here you would implement the actual adventure start logic
        // window.location.href = '/game/adventure/start/' + selectedAdventureId;
    } else {
        alert('Please select an adventure first!');
    }
}

function continueCurrentQuest() {
    alert('Continuing current quest...\n\nThis would resume the active quest!');
    // Here you would implement quest continuation logic
    // window.location.href = '/game/quest/continue';
}
</script>
@endpush
