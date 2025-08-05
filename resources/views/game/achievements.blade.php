@extends('game.layout')

@section('title', 'Achievements')

@section('content')
<div class="container-fluid">
    <!-- Achievements Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h3 mb-2">Achievements</h1>
                            <p class="text-muted mb-0">Track your progress and unlock special rewards</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="achievement-stats">
                                <span class="badge bg-warning fs-6" aria-label="Achievement progress {{ $unlockedCount }} out of {{ $totalCount }}">
                                    {{ $unlockedCount }} / {{ $totalCount }} Unlocked
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Achievement Categories Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="btn-group flex-wrap" role="group" aria-label="Achievement categories">
                                <button type="button" class="btn btn-outline-primary active" onclick="filterAchievements('all')" id="filter-all">
                                    All
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="filterAchievements('combat')" id="filter-combat">
                                    Combat
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="filterAchievements('exploration')" id="filter-exploration">
                                    Exploration
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="filterAchievements('crafting')" id="filter-crafting">
                                    Crafting
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="filterAchievements('equipment')" id="filter-equipment">
                                    Equipment
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="filterAchievements('character')" id="filter-character">
                                    Character
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="filterAchievements('economy')" id="filter-economy">
                                    Economy
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="filterAchievements('village')" id="filter-village">
                                    Village
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="filterAchievements('social')" id="filter-social">
                                    Social
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="filterAchievements('secret')" id="filter-secret">
                                    Secret
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="showOnlyUnlocked" onchange="toggleUnlockedOnly()">
                                <label class="form-check-label" for="showOnlyUnlocked">
                                    Show only unlocked
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Achievements -->
    @if($recentAchievements->isNotEmpty())
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h2 class="h5 mb-0">
                        <i class="fas fa-trophy" aria-hidden="true"></i> Recently Unlocked
                    </h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($recentAchievements as $achievement)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card achievement-card unlocked recent" role="article" aria-labelledby="achievement-{{ $achievement->id }}">
                                <div class="card-body text-center">
                                    <div class="achievement-icon mb-2">
                                        <i class="fas fa-trophy fa-2x text-warning" aria-hidden="true"></i>
                                    </div>
                                    <h3 class="h6 text-warning mb-1" id="achievement-{{ $achievement->id }}">{{ $achievement->name }}</h3>
                                    <p class="small text-muted mb-2">{{ $achievement->description }}</p>
                                    <div class="achievement-meta">
                                        <span class="badge bg-success" aria-label="Unlocked on {{ $achievement->pivot->unlocked_at->format('M j, Y') }}">
                                            <i class="fas fa-check" aria-hidden="true"></i> {{ $achievement->pivot->unlocked_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    @if($achievement->rewards)
                                    <div class="achievement-rewards mt-2">
                                        <small class="text-success">
                                            <i class="fas fa-gift" aria-hidden="true"></i> {{ $achievement->rewards }}
                                        </small>
                                    </div>
                                    @endif
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

    <!-- All Achievements -->
    <div class="row">
        @foreach($achievements as $achievement)
        <div class="col-md-6 col-lg-4 mb-4 achievement-item" 
             data-category="{{ $achievement->category }}" 
             data-unlocked="{{ $achievement->pivot ? 'true' : 'false' }}">
            <div class="card h-100 achievement-card {{ $achievement->pivot ? 'unlocked' : 'locked' }}" 
                 role="article" 
                 aria-labelledby="achievement-{{ $achievement->id }}"
                 aria-describedby="achievement-desc-{{ $achievement->id }}">
                <div class="card-body">
                    <div class="row align-items-start">
                        <div class="col-3 text-center">
                            <div class="achievement-icon">
                                @if($achievement->pivot)
                                <i class="fas fa-trophy fa-2x text-warning" aria-hidden="true"></i>
                                @else
                                <i class="fas fa-lock fa-2x text-muted" aria-hidden="true"></i>
                                @endif
                            </div>
                        </div>
                        <div class="col-9">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h3 class="h6 mb-0 {{ $achievement->pivot ? 'text-warning' : 'text-muted' }}" id="achievement-{{ $achievement->id ?? $loop->index }}">
                                    {{ $achievement->name }}
                                </h3>
                                @php
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
                                    $badgeColor = $categoryColors[$achievement->category] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $badgeColor }}" 
                                      aria-label="Category {{ $achievement->category }}">
                                    {{ ucfirst($achievement->category) }}
                                </span>
                            </div>
                            
                            <p class="small {{ $achievement->pivot ? 'text-body' : 'text-muted' }} mb-2" id="achievement-desc-{{ $achievement->id ?? $loop->index }}">
                                {{ $achievement->description }}
                            </p>

                            <!-- Progress Bar for Trackable Achievements -->
                            @if($achievement->is_progress_based && !$achievement->pivot)
                            <div class="progress mb-2" role="progressbar" 
                                 aria-valuenow="{{ $achievement->current_progress ?? 0 }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="{{ $achievement->target_value }}"
                                 aria-label="Achievement progress {{ $achievement->current_progress ?? 0 }} out of {{ $achievement->target_value }}">
                                @php
                                    $progressPercent = $achievement->target_value > 0 ? (($achievement->current_progress ?? 0) / $achievement->target_value) * 100 : 0;
                                @endphp
                                <div class="progress-bar" style="width: {{ $progressPercent }}%">
                                    {{ $achievement->current_progress ?? 0 }} / {{ $achievement->target_value }}
                                </div>
                            </div>
                            @endif

                            <!-- Achievement Status -->
                            <div class="achievement-status">
                                @if($achievement->pivot)
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-success" aria-label="Achievement unlocked">
                                        <i class="fas fa-check" aria-hidden="true"></i> Unlocked
                                    </span>
                                    <small class="text-muted">{{ $achievement->pivot->unlocked_at->format('M j, Y') }}</small>
                                </div>
                                @else
                                <span class="badge bg-secondary" aria-label="Achievement locked">
                                    <i class="fas fa-lock" aria-hidden="true"></i> Locked
                                </span>
                                @endif
                            </div>

                            <!-- Rewards -->
                            @if($achievement->rewards)
                            <div class="achievement-rewards mt-2">
                                <small class="{{ $achievement->pivot ? 'text-success' : 'text-muted' }}">
                                    <i class="fas fa-gift" aria-hidden="true"></i> 
                                    Reward: {{ $achievement->rewards }}
                                </small>
                            </div>
                            @endif

                            <!-- Hints for Locked Achievements -->
                            @if(!$achievement->pivot && $achievement->hints)
                            <div class="achievement-hints mt-2">
                                <small class="text-info">
                                    <i class="fas fa-lightbulb" aria-hidden="true"></i> 
                                    Hint: {{ $achievement->hints }}
                                </small>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Achievement Statistics -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0">Achievement Statistics</h2>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="h4 text-warning" aria-label="Total achievements unlocked {{ $unlockedCount }}">{{ $unlockedCount }}</div>
                                <div class="text-muted">Unlocked</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="h4 text-muted" aria-label="Total achievements remaining {{ $totalCount - $unlockedCount }}">{{ $totalCount - $unlockedCount }}</div>
                                <div class="text-muted">Remaining</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="h4 text-info" aria-label="Completion percentage {{ $totalCount > 0 ? round(($unlockedCount / $totalCount) * 100) : 0 }}%">{{ $totalCount > 0 ? round(($unlockedCount / $totalCount) * 100) : 0 }}%</div>
                                <div class="text-muted">Completion</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="h4 text-success" aria-label="Total achievement points {{ $totalPoints }}">{{ $totalPoints }}</div>
                                <div class="text-muted">Points</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.achievement-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.achievement-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.achievement-card.unlocked {
    border-left: 4px solid #ffc107;
}

.achievement-card.locked {
    opacity: 0.7;
}

.achievement-card.recent {
    border: 2px solid #28a745;
    animation: pulse 2s infinite;
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

.achievement-icon {
    min-height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-card {
    padding: 1rem;
    border-radius: 0.5rem;
    background: rgba(0,0,0,0.02);
}

@media (prefers-reduced-motion: reduce) {
    .achievement-card {
        transition: none;
    }
    
    .achievement-card.recent {
        animation: none;
    }
}
</style>

<script>
function filterAchievements(category) {
    // Update active button
    document.querySelectorAll('[id^="filter-"]').forEach(btn => {
        btn.classList.remove('active');
    });
    document.getElementById(`filter-${category}`).classList.add('active');

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
            const activeCategory = document.querySelector('[id^="filter-"].active').id.replace('filter-', '');
            if (activeCategory === 'all' || achievement.dataset.category === activeCategory) {
                achievement.style.display = '';
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
        document.body.removeChild(announcement);
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