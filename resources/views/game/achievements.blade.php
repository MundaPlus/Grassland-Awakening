@extends('game.layout')

@section('title', 'Achievements')
@section('meta_description', 'View your achievements and progress in Grassland Awakening - track your accomplishments and unlock new rewards.')

@push('styles')
<style>
    /* Full-screen immersive layout */
    body {
        overflow: hidden;
    }

    .achievements-background {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-image: url('/img/backgrounds/achievements.png');
        z-index: 1;
    }

    .achievements-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: linear-gradient(rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.2));
        z-index: 2;
    }

    .achievements-ui-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 10;
        pointer-events: none;
    }

    .achievements-ui-container > * {
        pointer-events: all;
    }

    /* Header Panel - Top Center */
    .achievements-header-panel {
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
    }

    /* Main Achievements Panel - Center */
    .main-achievements-panel {
        position: absolute;
        top: 150px;
        left: 20px;
        width: calc(100% - 340px);
        height: calc(100vh - 330px);
        background: rgba(33, 37, 41, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 20px;
        color: white;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        display: flex;
        flex-direction: column;
    }

    /* Category Tabs */
    .achievements-tabs {
        display: flex;
        gap: 5px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }

    .achievements-tab {
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
        min-width: 80px;
    }

    .achievements-tab.active,
    .achievements-tab:hover {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border-color: rgba(255, 255, 255, 0.4);
    }

    .achievements-content {
        flex: 1;
        overflow-y: auto;
        padding-right: 10px;
    }

    .achievements-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 15px;
        padding: 5px;
    }

    .achievement-card {
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        padding: 15px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .achievement-card:hover {
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.4);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
    }

    .achievement-card.unlocked {
        border-color: rgba(40, 167, 69, 0.6);
        background: rgba(40, 167, 69, 0.1);
    }

    .achievement-card.locked {
        opacity: 0.6;
        border-color: rgba(108, 117, 125, 0.4);
    }

    .achievement-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 10px;
    }

    .achievement-icon {
        font-size: 2rem;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .achievement-card.unlocked .achievement-icon {
        background: rgba(40, 167, 69, 0.2);
        border-color: rgba(40, 167, 69, 0.4);
    }

    .achievement-info h3 {
        margin: 0 0 5px 0;
        font-size: 1.1rem;
        font-weight: bold;
    }

    .achievement-category {
        font-size: 0.75rem;
        opacity: 0.8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .achievement-description {
        font-size: 0.9rem;
        margin-bottom: 10px;
        line-height: 1.4;
        opacity: 0.9;
    }

    .achievement-progress {
        margin-bottom: 10px;
    }

    .progress-bar-container {
        background: rgba(0, 0, 0, 0.3);
        border-radius: 10px;
        height: 8px;
        overflow: hidden;
        margin-bottom: 5px;
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #28a745, #20c997);
        border-radius: 10px;
        transition: width 0.5s ease;
    }

    .progress-text {
        font-size: 0.8rem;
        opacity: 0.8;
    }

    .achievement-reward {
        background: rgba(255, 193, 7, 0.2);
        border: 1px solid rgba(255, 193, 7, 0.4);
        border-radius: 6px;
        padding: 6px 10px;
        font-size: 0.8rem;
        text-align: center;
        font-weight: 500;
    }

    .achievement-unlocked-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(40, 167, 69, 0.9);
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: bold;
    }

    /* Stats Panel - Right Side */
    .achievements-stats-panel {
        position: absolute;
        top: 100px;
        right: 20px;
        width: 280px;
        background: rgba(23, 162, 184, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 15px;
        color: white;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }

    .stat-item {
        text-align: center;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 12px;
        margin: 8px 0;
    }

    .stat-value {
        font-weight: bold;
        font-size: 1.4rem;
        margin-bottom: 4px;
    }

    .stat-label {
        font-size: 0.85rem;
        opacity: 0.9;
    }

    /* Categories Panel - Right Side Bottom */
    .achievements-categories-panel {
        position: absolute;
        top: 480px;
        right: 20px;
        width: 280px;
        height: calc(100vh - 640px);
        background: rgba(220, 53, 69, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 15px;
        color: white;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        overflow-y: auto;
    }

    .category-item {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 10px;
        margin: 8px 0;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .category-item:hover,
    .category-item.active {
        background: rgba(255, 255, 255, 0.2);
        transform: translateX(2px);
    }

    .category-name {
        font-weight: 500;
        font-size: 0.9rem;
    }

    .category-count {
        background: rgba(0, 0, 0, 0.2);
        border-radius: 12px;
        padding: 2px 8px;
        font-size: 0.8rem;
    }

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
        text-decoration: none;
    }

    .dashboard-btn.primary { background: linear-gradient(135deg, #007bff, #0056b3); }
    .dashboard-btn.success { background: linear-gradient(135deg, #28a745, #1e7e34); }
    .dashboard-btn.warning { background: linear-gradient(135deg, #ffc107, #e0a800); }
    .dashboard-btn.danger { background: linear-gradient(135deg, #dc3545, #c82333); }

    /* Custom Scrollbar */
    .achievements-content::-webkit-scrollbar,
    .achievements-categories-panel::-webkit-scrollbar {
        width: 8px;
    }

    .achievements-content::-webkit-scrollbar-track,
    .achievements-categories-panel::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
    }

    .achievements-content::-webkit-scrollbar-thumb,
    .achievements-categories-panel::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 4px;
    }

    .achievements-content::-webkit-scrollbar-thumb:hover,
    .achievements-categories-panel::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.5);
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .main-achievements-panel {
            width: calc(100% - 40px);
            height: calc(100vh - 200px);
        }

        .achievements-stats-panel,
        .achievements-categories-panel {
            display: none;
        }
    }

    @media (max-width: 768px) {
        .achievements-grid {
            grid-template-columns: 1fr;
        }

        .achievements-header-panel {
            left: 10px;
            right: 10px;
            transform: none;
        }

        .main-achievements-panel {
            left: 10px;
            width: calc(100% - 20px);
            top: 90px;
            height: calc(100vh - 150px);
        }

        .achievements-tabs {
            flex-direction: column;
        }

        .achievements-tab {
            flex: none;
        }
    }
</style>
@endpush

@section('content')
<!-- Achievements Background -->
<div class="achievements-background"></div>
<div class="achievements-overlay"></div>

<!-- Achievements UI Overlay System -->
<div class="achievements-ui-container">
    <!-- Header Panel - Top Center -->
    <div class="achievements-header-panel">
        <h1 class="mb-1">🏆 Achievements & Progress</h1>
        <div class="small">Track your accomplishments and unlock new rewards</div>
    </div>

    <!-- Main Achievements Panel - Center Left -->
    <div class="main-achievements-panel">
        <div class="mb-2">
            <h2 class="h6 mb-2">🎯 Your Achievements</h2>
        </div>

        <!-- Achievement Tabs -->
        <div class="achievements-tabs">
            <button class="achievements-tab active" data-category="all" onclick="filterAchievements('all')">
                🌟 All
            </button>
            <button class="achievements-tab" data-category="combat" onclick="filterAchievements('combat')">
                ⚔️ Combat
            </button>
            <button class="achievements-tab" data-category="exploration" onclick="filterAchievements('exploration')">
                🗺️ Exploration
            </button>
            <button class="achievements-tab" data-category="crafting" onclick="filterAchievements('crafting')">
                🔨 Crafting
            </button>
            <button class="achievements-tab" data-category="social" onclick="filterAchievements('social')">
                👥 Social
            </button>
        </div>

        <!-- Achievements Content -->
        <div class="achievements-content">
            <div class="achievements-grid" id="achievements-grid">
                @if(isset($achievements) && $achievements->count() > 0)
                    @foreach($achievements as $achievement)
                        @php
                            // Check if achievement is unlocked based on pivot data
                            $isUnlocked = isset($achievement->pivot) && $achievement->pivot !== null;
                        @endphp
                        <div class="achievement-card {{ $isUnlocked ? 'unlocked' : 'locked' }}"
                             data-category="{{ $achievement->category ?? 'general' }}">

                            @if($isUnlocked)
                                <div class="achievement-unlocked-badge">✓ Unlocked</div>
                            @endif

                            <div class="achievement-header">
                                <div class="achievement-icon">
                                    {{ $achievement->icon ?? '🏆' }}
                                </div>
                                <div class="achievement-info">
                                    <h3>{{ $achievement->name }}</h3>
                                    <div class="achievement-category">{{ ucfirst($achievement->category ?? 'General') }}</div>
                                </div>
                            </div>

                            <div class="achievement-description">
                                {{ $achievement->description }}
                            </div>

                            @if(!$isUnlocked && isset($achievement->current_progress) && isset($achievement->target_value))
                                <div class="achievement-progress">
                                    <div class="progress-bar-container">
                                        <div class="progress-bar" style="width: {{ ($achievement->current_progress / $achievement->target_value) * 100 }}%"></div>
                                    </div>
                                    <div class="progress-text">
                                        {{ $achievement->current_progress }} / {{ $achievement->target_value }}
                                    </div>
                                </div>
                            @endif

                            @if(isset($achievement->rewards))
                                <div class="achievement-reward">
                                    🎁 {{ $achievement->rewards }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <!-- Sample Achievements for Demo -->
                    <div class="achievement-card unlocked" data-category="combat">
                        <div class="achievement-unlocked-badge">✓ Unlocked</div>
                        <div class="achievement-header">
                            <div class="achievement-icon">⚔️</div>
                            <div class="achievement-info">
                                <h3>First Blood</h3>
                                <div class="achievement-category">Combat</div>
                            </div>
                        </div>
                        <div class="achievement-description">
                            Win your first combat encounter
                        </div>
                        <div class="achievement-reward">
                            🎁 50 Gold + Combat Experience
                        </div>
                    </div>

                    <div class="achievement-card unlocked" data-category="exploration">
                        <div class="achievement-unlocked-badge">✓ Unlocked</div>
                        <div class="achievement-header">
                            <div class="achievement-icon">🗺️</div>
                            <div class="achievement-info">
                                <h3>Explorer</h3>
                                <div class="achievement-category">Exploration</div>
                            </div>
                        </div>
                        <div class="achievement-description">
                            Complete your first adventure
                        </div>
                        <div class="achievement-reward">
                            🎁 Adventure Map Bonus
                        </div>
                    </div>

                    <div class="achievement-card locked" data-category="combat">
                        <div class="achievement-header">
                            <div class="achievement-icon">👹</div>
                            <div class="achievement-info">
                                <h3>Boss Slayer</h3>
                                <div class="achievement-category">Combat</div>
                            </div>
                        </div>
                        <div class="achievement-description">
                            Defeat 10 boss enemies
                        </div>
                        <div class="achievement-progress">
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: 30%"></div>
                            </div>
                            <div class="progress-text">3 / 10</div>
                        </div>
                        <div class="achievement-reward">
                            🎁 Legendary Weapon
                        </div>
                    </div>

                    <div class="achievement-card locked" data-category="crafting">
                        <div class="achievement-header">
                            <div class="achievement-icon">🔨</div>
                            <div class="achievement-info">
                                <h3>Master Crafter</h3>
                                <div class="achievement-category">Crafting</div>
                            </div>
                        </div>
                        <div class="achievement-description">
                            Craft 100 items
                        </div>
                        <div class="achievement-progress">
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: 15%"></div>
                            </div>
                            <div class="progress-text">15 / 100</div>
                        </div>
                        <div class="achievement-reward">
                            🎁 Crafting Mastery Bonus
                        </div>
                    </div>

                    <div class="achievement-card locked" data-category="social">
                        <div class="achievement-header">
                            <div class="achievement-icon">👥</div>
                            <div class="achievement-info">
                                <h3>People Person</h3>
                                <div class="achievement-category">Social</div>
                            </div>
                        </div>
                        <div class="achievement-description">
                            Complete 20 NPC interactions
                        </div>
                        <div class="achievement-progress">
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: 60%"></div>
                            </div>
                            <div class="progress-text">12 / 20</div>
                        </div>
                        <div class="achievement-reward">
                            🎁 Social Reputation Boost
                        </div>
                    </div>

                    <div class="achievement-card unlocked" data-category="exploration">
                        <div class="achievement-unlocked-badge">✓ Unlocked</div>
                        <div class="achievement-header">
                            <div class="achievement-icon">💰</div>
                            <div class="achievement-info">
                                <h3>Treasure Hunter</h3>
                                <div class="achievement-category">Exploration</div>
                            </div>
                        </div>
                        <div class="achievement-description">
                            Find 5 treasure chests
                        </div>
                        <div class="achievement-reward">
                            🎁 Treasure Detection Skill
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Achievements Stats Panel - Top Right -->
    <div class="achievements-stats-panel">
        <div class="mb-2">
            <h2 class="h6 mb-2">📊 Achievement Statistics</h2>
        </div>

        <div class="stat-item">
            <div class="stat-value">{{ isset($unlockedCount) ? $unlockedCount : '3' }}</div>
            <div class="stat-label">Unlocked</div>
        </div>

        <div class="stat-item">
            <div class="stat-value">{{ isset($totalCount) ? $totalCount : '6' }}</div>
            <div class="stat-label">Total Achievements</div>
        </div>

        <div class="stat-item">
            <div class="stat-value">{{ isset($unlockedCount) && isset($totalCount) && $totalCount > 0 ? round(($unlockedCount / $totalCount) * 100) : '50' }}%</div>
            <div class="stat-label">Completion Rate</div>
        </div>
    </div>

    <!-- Achievement Categories Panel - Bottom Right -->
    <div class="achievements-categories-panel">
        <div class="mb-2">
            <div class="fw-bold small">🗂️ Categories</div>
        </div>

        <div class="category-item active" data-category="all" onclick="filterAchievements('all')">
            <span class="category-name">🌟 All Categories</span>
            <span class="category-count">6</span>
        </div>

        <div class="category-item" data-category="combat" onclick="filterAchievements('combat')">
            <span class="category-name">⚔️ Combat</span>
            <span class="category-count">2</span>
        </div>

        <div class="category-item" data-category="exploration" onclick="filterAchievements('exploration')">
            <span class="category-name">🗺️ Exploration</span>
            <span class="category-count">2</span>
        </div>

        <div class="category-item" data-category="crafting" onclick="filterAchievements('crafting')">
            <span class="category-name">🔨 Crafting</span>
            <span class="category-count">1</span>
        </div>

        <div class="category-item" data-category="social" onclick="filterAchievements('social')">
            <span class="category-name">👥 Social</span>
            <span class="category-count">1</span>
        </div>
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
            <a href="{{ route('game.adventures') }}" class="dashboard-btn danger">
                🗺️ Adventures
            </a>
            <a href="{{ route('game.inventory') }}" class="dashboard-btn warning">
                🎒 Inventory
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
// Achievement filtering functionality
function filterAchievements(category) {
    // Update active tab
    document.querySelectorAll('.achievements-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelector(`[data-category="${category}"]`).classList.add('active');

    // Update active category in sidebar
    document.querySelectorAll('.category-item').forEach(item => {
        item.classList.remove('active');
    });
    document.querySelector(`.category-item[data-category="${category}"]`).classList.add('active');

    // Filter achievement cards
    const cards = document.querySelectorAll('.achievement-card');
    cards.forEach(card => {
        if (category === 'all' || card.getAttribute('data-category') === category) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });

    // Add a little animation
    const grid = document.getElementById('achievements-grid');
    grid.style.opacity = '0.7';
    setTimeout(() => {
        grid.style.opacity = '1';
    }, 150);
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Show all achievements by default
    filterAchievements('all');

    // Add smooth scrolling for achievement cards
    document.querySelectorAll('.achievement-card').forEach(card => {
        card.addEventListener('click', function() {
            if (this.classList.contains('unlocked')) {
                // Could add some celebration animation for unlocked achievements
                console.log('Viewing achievement details...');
            }
        });
    });
});
</script>
@endpush
