@extends('game.layout')

@section('title', 'Achievements')

@section('content')
<style>
    .game-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-image: url('/img/backgrounds/achievements.png');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        z-index: 1;
    }

    .overlay-content {
        position: relative;
        z-index: 10;
        width: 100vw;
        height: 100vh;
        overflow-y: auto;
    }

    .glass-panel {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 15px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }

    .title-panel {
        position: absolute;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        width: 400px;
        z-index: 15;
    }

    .achievements-panel {
        position: absolute;
        top: 140px;
        left: 20px;
        width: calc(100% - 40px);
        max-width: 1200px;
        left: 50%;
        transform: translateX(-50%);
        height: calc(100vh - 240px);
        overflow-y: auto;
        z-index: 12;
    }

    .stats-panel {
        position: absolute;
        top: 140px;
        right: 20px;
        width: 300px;
        height: 400px;
        overflow-y: auto;
        z-index: 12;
    }

    .filter-panel {
        position: absolute;
        top: 140px;
        left: 20px;
        width: 280px;
        height: 500px;
        overflow-y: auto;
        z-index: 12;
    }

    .quick-actions {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 20;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: center;
        max-width: 90vw;
    }

    .dashboard-btn {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 12px 20px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 120px;
        justify-content: center;
    }

    .dashboard-btn:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        color: white;
        text-decoration: none;
    }

    .achievement-card {
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 12px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }

    .achievement-card:hover {
        background: rgba(255, 255, 255, 0.12);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
    }

    .achievement-card.unlocked {
        border-left: 4px solid #ffc107;
        background: rgba(255, 193, 7, 0.1);
    }

    .achievement-card.locked {
        opacity: 0.7;
        background: rgba(108, 117, 125, 0.1);
    }

    .achievement-card.recent {
        border: 2px solid #28a745;
        animation: pulse 2s infinite;
        background: rgba(40, 167, 69, 0.15);
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
        }
    }

    .filter-btn {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        margin: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: block;
        width: calc(100% - 10px);
        text-align: center;
    }

    .filter-btn:hover,
    .filter-btn.active {
        background: rgba(255, 255, 255, 0.25);
        color: white;
        transform: translateY(-1px);
    }

    .stat-card {
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        margin-bottom: 15px;
        color: white;
    }

    .text-white {
        color: white !important;
    }

    .progress {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        overflow: hidden;
    }

    .progress-bar {
        background: linear-gradient(90deg, #28a745, #20c997);
        color: white;
        text-align: center;
        line-height: 1.5rem;
        font-size: 0.875rem;
        font-weight: 500;
    }

    @media (max-width: 1400px) {
        .achievements-panel {
            left: 320px;
            width: calc(100% - 660px);
        }
    }

    @media (max-width: 1200px) {
        .filter-panel,
        .stats-panel {
            display: none;
        }
        .achievements-panel {
            left: 20px;
            width: calc(100% - 40px);
        }
    }

    @media (max-width: 768px) {
        .title-panel {
            width: 90%;
        }
        .achievements-panel {
            top: 160px;
            height: calc(100vh - 260px);
        }
        .quick-actions {
            bottom: 10px;
            flex-wrap: wrap;
            gap: 5px;
        }
        .dashboard-btn {
            padding: 10px 16px;
            font-size: 0.9rem;
            min-width: 100px;
        }
    }
</style>

<div class="game-overlay"></div>

<div class="overlay-content">
    <!-- Title Panel -->
    <div class="title-panel">
        <div class="glass-panel p-4 text-center">
            <h1 class="text-white mb-2">🏆 Achievements</h1>
            <p class="text-white mb-0 opacity-75">Track your legendary accomplishments</p>
        </div>
    </div>

    <!-- Filter Panel (Desktop) -->
    <div class="filter-panel d-none d-lg-block">
        <div class="glass-panel p-3">
            <h5 class="text-white mb-3">📂 Categories</h5>
            <button class="filter-btn active" onclick="filterAchievements('all')" id="filter-all">
                🌟 All
            </button>
            <button class="filter-btn" onclick="filterAchievements('combat')" id="filter-combat">
                ⚔️ Combat
            </button>
            <button class="filter-btn" onclick="filterAchievements('exploration')" id="filter-exploration">
                🗺️ Exploration
            </button>
            <button class="filter-btn" onclick="filterAchievements('crafting')" id="filter-crafting">
                🔨 Crafting
            </button>
            <button class="filter-btn" onclick="filterAchievements('equipment')" id="filter-equipment">
                🛡️ Equipment
            </button>
            <button class="filter-btn" onclick="filterAchievements('character')" id="filter-character">
                👤 Character
            </button>
            <button class="filter-btn" onclick="filterAchievements('economy')" id="filter-economy">
                💰 Economy
            </button>
            <button class="filter-btn" onclick="filterAchievements('village')" id="filter-village">
                🏘️ Village
            </button>
            <button class="filter-btn" onclick="filterAchievements('social')" id="filter-social">
                👥 Social
            </button>
            <button class="filter-btn" onclick="filterAchievements('secret')" id="filter-secret">
                🔮 Secret
            </button>
            
            <div class="mt-3">
                <label class="text-white">
                    <input type="checkbox" id="showOnlyUnlocked" onchange="toggleUnlockedOnly()">
                    <span class="ms-2">✅ Unlocked Only</span>
                </label>
            </div>
        </div>
    </div>

    <!-- Stats Panel (Desktop) -->
    <div class="stats-panel d-none d-lg-block">
        <div class="glass-panel p-3">
            <h5 class="text-white mb-3">📊 Statistics</h5>
            <div class="stat-card">
                <div class="h4 text-warning">{{ $unlockedCount ?? 0 }}</div>
                <div class="text-white opacity-75">🏆 Unlocked</div>
            </div>
            <div class="stat-card">
                <div class="h4 text-muted">{{ ($totalCount ?? 0) - ($unlockedCount ?? 0) }}</div>
                <div class="text-white opacity-75">🔒 Remaining</div>
            </div>
            <div class="stat-card">
                <div class="h4 text-info">{{ ($totalCount ?? 1) > 0 ? round((($unlockedCount ?? 0) / ($totalCount ?? 1)) * 100) : 0 }}%</div>
                <div class="text-white opacity-75">📈 Completion</div>
            </div>
            <div class="stat-card">
                <div class="h4 text-success">{{ $totalPoints ?? 0 }}</div>
                <div class="text-white opacity-75">⭐ Points</div>
            </div>
        </div>
    </div>

    <!-- Achievements Panel -->
    <div class="achievements-panel">
        <div class="glass-panel p-4">
            <!-- Recent Achievements -->
            @if(isset($recentAchievements) && $recentAchievements->isNotEmpty())
            <div class="mb-4">
                <h5 class="text-warning mb-3">🎉 Recently Unlocked</h5>
                <div class="row">
                    @foreach($recentAchievements as $achievement)
                    <div class="col-lg-6 mb-3">
                        <div class="achievement-card unlocked recent p-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div style="font-size: 2rem;">🏆</div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="text-warning mb-1">{{ $achievement->name }}</h6>
                                    <p class="text-white opacity-75 mb-2 small">{{ $achievement->description }}</p>
                                    <div class="d-flex justify-content-between">
                                        <span class="badge bg-success">✅ Unlocked</span>
                                        <small class="text-white opacity-50">{{ $achievement->pivot->unlocked_at->diffForHumans() }}</small>
                                    </div>
                                    @if($achievement->rewards)
                                    <div class="mt-2">
                                        <small class="text-success">🎁 {{ $achievement->rewards }}</small>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- All Achievements -->
            <div class="row">
                @if(isset($achievements))
                @foreach($achievements as $achievement)
                <div class="col-lg-6 mb-3 achievement-item" 
                     data-category="{{ $achievement->category ?? 'general' }}" 
                     data-unlocked="{{ isset($achievement->pivot) ? 'true' : 'false' }}">
                    <div class="achievement-card {{ isset($achievement->pivot) ? 'unlocked' : 'locked' }} p-3">
                        <div class="d-flex align-items-start">
                            <div class="me-3 text-center" style="min-width: 60px;">
                                @if(isset($achievement->pivot))
                                <div style="font-size: 2rem;">🏆</div>
                                @else
                                <div style="font-size: 2rem; opacity: 0.5;">🔒</div>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="{{ isset($achievement->pivot) ? 'text-warning' : 'text-white opacity-50' }} mb-0">
                                        {{ $achievement->name ?? 'Unknown Achievement' }}
                                    </h6>
                                    @php
                                        $categoryEmojis = [
                                            'combat' => '⚔️',
                                            'exploration' => '🗺️', 
                                            'crafting' => '🔨',
                                            'equipment' => '🛡️',
                                            'character' => '👤',
                                            'economy' => '💰',
                                            'village' => '🏘️',
                                            'social' => '👥',
                                            'secret' => '🔮'
                                        ];
                                        $categoryColors = [
                                            'combat' => 'danger',
                                            'exploration' => 'success', 
                                            'crafting' => 'warning',
                                            'equipment' => 'primary',
                                            'character' => 'info',
                                            'economy' => 'warning',
                                            'village' => 'secondary',
                                            'social' => 'info',
                                            'secret' => 'dark'
                                        ];
                                        $category = $achievement->category ?? 'general';
                                        $emoji = $categoryEmojis[$category] ?? '📋';
                                        $badgeColor = $categoryColors[$category] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $badgeColor }}">
                                        {{ $emoji }} {{ ucfirst($category) }}
                                    </span>
                                </div>
                                
                                <p class="small {{ isset($achievement->pivot) ? 'text-white' : 'text-white opacity-50' }} mb-2">
                                    {{ $achievement->description ?? 'No description available.' }}
                                </p>

                                <!-- Progress Bar for Trackable Achievements -->
                                @if(isset($achievement->is_progress_based) && $achievement->is_progress_based && !isset($achievement->pivot))
                                <div class="progress mb-2" style="height: 20px;">
                                    @php
                                        $currentProgress = $achievement->current_progress ?? 0;
                                        $targetValue = $achievement->target_value ?? 1;
                                        $progressPercent = $targetValue > 0 ? ($currentProgress / $targetValue) * 100 : 0;
                                    @endphp
                                    <div class="progress-bar" style="width: {{ $progressPercent }}%">
                                        {{ $currentProgress }} / {{ $targetValue }}
                                    </div>
                                </div>
                                @endif

                                <!-- Achievement Status -->
                                <div class="achievement-status">
                                    @if(isset($achievement->pivot))
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-success">✅ Unlocked</span>
                                        <small class="text-white opacity-50">{{ $achievement->pivot->unlocked_at->format('M j, Y') }}</small>
                                    </div>
                                    @else
                                    <span class="badge bg-secondary">🔒 Locked</span>
                                    @endif
                                </div>

                                <!-- Rewards -->
                                @if(isset($achievement->rewards) && $achievement->rewards)
                                <div class="achievement-rewards mt-2">
                                    <small class="{{ isset($achievement->pivot) ? 'text-success' : 'text-white opacity-75' }}">
                                        🎁 Reward: {{ $achievement->rewards }}
                                    </small>
                                </div>
                                @endif

                                <!-- Hints for Locked Achievements -->
                                @if(!isset($achievement->pivot) && isset($achievement->hints) && $achievement->hints)
                                <div class="achievement-hints mt-2">
                                    <small class="text-info">
                                        💡 Hint: {{ $achievement->hints }}
                                    </small>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                @else
                <div class="col-12">
                    <div class="achievement-card p-4 text-center">
                        <div style="font-size: 3rem; opacity: 0.5;">🏆</div>
                        <h5 class="text-white opacity-75 mt-3">No Achievements Yet</h5>
                        <p class="text-white opacity-50">Complete challenges to unlock achievements!</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <a href="{{ route('game.dashboard') }}" class="dashboard-btn">
            🏠 Dashboard
        </a>
        <a href="{{ route('game.character') }}" class="dashboard-btn">
            👤 Character
        </a>
        <a href="{{ route('game.inventory') }}" class="dashboard-btn">
            🎒 Inventory
        </a>
        <a href="{{ route('game.crafting') }}" class="dashboard-btn">
            🔨 Crafting
        </a>
        <a href="{{ route('game.adventures') }}" class="dashboard-btn">
            🗺️ Adventures
        </a>
        <a href="{{ route('game.combat') }}" class="dashboard-btn">
            ⚔️ Combat
        </a>
        <a href="{{ route('game.skills') }}" class="dashboard-btn">
            📚 Skills
        </a>
    </div>
</div>

<script>
function filterAchievements(category) {
    // Update active button
    document.querySelectorAll('[id^="filter-"]').forEach(btn => {
        btn.classList.remove('active');
    });
    const button = document.getElementById(`filter-${category}`);
    if (button) {
        button.classList.add('active');
    }

    // Filter achievements
    const achievements = document.querySelectorAll('.achievement-item');
    achievements.forEach(achievement => {
        if (category === 'all' || achievement.dataset.category === category) {
            achievement.style.display = '';
        } else {
            achievement.style.display = 'none';
        }
    });

    // Announce filter change to screen readers
    const announcement = category === 'all' ? 'Showing all achievements' : `Showing ${category} achievements`;
    announceToScreenReader(announcement);
}

function toggleUnlockedOnly() {
    const showOnlyUnlocked = document.getElementById('showOnlyUnlocked').checked;
    const achievements = document.querySelectorAll('.achievement-item');
    
    achievements.forEach(achievement => {
        if (showOnlyUnlocked && achievement.dataset.unlocked === 'false') {
            achievement.style.display = 'none';
        } else if (achievement.style.display === 'none' && !showOnlyUnlocked) {
            // Only show if it passes current category filter
            const activeButton = document.querySelector('[id^="filter-"].active');
            if (activeButton) {
                const activeCategory = activeButton.id.replace('filter-', '');
                if (activeCategory === 'all' || achievement.dataset.category === activeCategory) {
                    achievement.style.display = '';
                }
            }
        }
    });

    // Announce change to screen readers
    const announcement = showOnlyUnlocked ? 'Showing only unlocked achievements' : 'Showing all achievements';
    announceToScreenReader(announcement);
}

function announceToScreenReader(message) {
    const announcement = document.createElement('div');
    announcement.setAttribute('aria-live', 'polite');
    announcement.setAttribute('aria-atomic', 'true');
    announcement.className = 'sr-only';
    announcement.textContent = message;
    document.body.appendChild(announcement);
    
    setTimeout(() => {
        if (document.body.contains(announcement)) {
            document.body.removeChild(announcement);
        }
    }, 1000);
}

// Keyboard navigation support
document.addEventListener('keydown', function(e) {
    if (e.altKey && e.key >= '1' && e.key <= '9') {
        e.preventDefault();
        const categories = ['all', 'combat', 'exploration', 'crafting', 'equipment', 'character', 'economy', 'village', 'social'];
        const index = parseInt(e.key) - 1;
        if (categories[index]) {
            filterAchievements(categories[index]);
        }
    }
    if (e.altKey && e.key === '0') {
        e.preventDefault();
        filterAchievements('secret');
    }
});
</script>
@endsection