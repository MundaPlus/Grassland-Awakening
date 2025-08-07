@extends('game.layout')

@section('title', 'Skills')
@section('meta_description', 'Master various skills in Grassland Awakening - enhance your abilities, unlock new techniques, and become more powerful.')

@push('styles')
<style>
    /* Full-screen immersive layout */
    body {
        overflow: hidden;
    }

    .skills-background {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-image: url('/img/backgrounds/skills.png');
        z-index: 1;
    }

    .skills-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: linear-gradient(rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.2));
        z-index: 2;
    }

    .skills-ui-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 10;
        pointer-events: none;
    }

    .skills-ui-container > * {
        pointer-events: all;
    }

    /* Header Panel - Top Center */
    .skills-header-panel {
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

    /* Main Skills Panel - Center */
    .main-skills-panel {
        position: absolute;
        top: 150px;
        left: 20px;
        width: calc(100% - 340px);
        height: calc(100vh - 350px);
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
    .skills-tabs {
        display: flex;
        gap: 5px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }

    .skills-tab {
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

    .skills-tab.active,
    .skills-tab:hover {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border-color: rgba(255, 255, 255, 0.4);
    }

    .skills-content {
        flex: 1;
        overflow-y: auto;
        padding-right: 10px;
    }

    .skills-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 15px;
        padding: 5px;
    }

    .skill-card {
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        padding: 15px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .skill-card:hover {
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.4);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
    }

    .skill-card.learned {
        border-color: rgba(40, 167, 69, 0.6);
        background: rgba(40, 167, 69, 0.1);
    }

    .skill-card.unlearned {
        opacity: 0.7;
        border-color: rgba(108, 117, 125, 0.4);
    }

    .skill-card.available {
        border-color: rgba(255, 193, 7, 0.6);
        background: rgba(255, 193, 7, 0.1);
    }

    .skill-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }

    .skill-icon {
        font-size: 2.5rem;
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .skill-card.learned .skill-icon {
        background: rgba(40, 167, 69, 0.2);
        border-color: rgba(40, 167, 69, 0.4);
    }

    .skill-card.available .skill-icon {
        background: rgba(255, 193, 7, 0.2);
        border-color: rgba(255, 193, 7, 0.4);
    }

    .skill-info h3 {
        margin: 0 0 5px 0;
        font-size: 1.2rem;
        font-weight: bold;
    }

    .skill-level {
        background: rgba(40, 167, 69, 0.8);
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: bold;
    }

    .skill-level.unlearned {
        background: rgba(108, 117, 125, 0.8);
    }

    .skill-description {
        font-size: 0.9rem;
        margin-bottom: 12px;
        line-height: 1.4;
        opacity: 0.9;
    }

    .skill-progress {
        margin-bottom: 12px;
    }

    .progress-bar-container {
        background: rgba(0, 0, 0, 0.3);
        border-radius: 10px;
        height: 8px;
        overflow: hidden;
        margin-bottom: 8px;
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #28a745, #20c997);
        border-radius: 10px;
        transition: width 0.5s ease;
    }

    .progress-text {
        display: flex;
        justify-content: between;
        font-size: 0.8rem;
        opacity: 0.8;
    }

    .skill-effects {
        background: rgba(40, 167, 69, 0.2);
        border: 1px solid rgba(40, 167, 69, 0.4);
        border-radius: 8px;
        padding: 8px;
        margin-bottom: 12px;
    }

    .effect-title {
        font-size: 0.8rem;
        font-weight: bold;
        color: #28a745;
        margin-bottom: 4px;
    }

    .effect-item {
        font-size: 0.75rem;
        opacity: 0.9;
        margin-bottom: 2px;
    }

    .skill-actions {
        display: flex;
        gap: 8px;
    }

    .learn-btn {
        background: linear-gradient(135deg, #28a745, #1e7e34);
        border: none;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.85rem;
        flex: 1;
        justify-content: center;
    }

    .learn-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        color: white;
    }

    .learn-btn:disabled,
    .learn-btn.disabled {
        background: rgba(108, 117, 125, 0.6);
        cursor: not-allowed;
        transform: none;
    }

    /* Stats Panel - Right Side */
    .skills-stats-panel {
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
    .skills-categories-panel {
        position: absolute;
        top: 280px;
        right: 20px;
        width: 280px;
        height: calc(100vh - 440px);
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
    .skills-content::-webkit-scrollbar,
    .skills-categories-panel::-webkit-scrollbar {
        width: 8px;
    }

    .skills-content::-webkit-scrollbar-track,
    .skills-categories-panel::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
    }

    .skills-content::-webkit-scrollbar-thumb,
    .skills-categories-panel::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 4px;
    }

    .skills-content::-webkit-scrollbar-thumb:hover,
    .skills-categories-panel::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.5);
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .main-skills-panel {
            width: calc(100% - 40px);
            height: calc(100vh - 200px);
        }

        .skills-stats-panel,
        .skills-categories-panel {
            display: none;
        }
    }

    @media (max-width: 768px) {
        .skills-grid {
            grid-template-columns: 1fr;
        }

        .skills-header-panel {
            left: 10px;
            right: 10px;
            transform: none;
        }

        .main-skills-panel {
            left: 10px;
            width: calc(100% - 20px);
            top: 90px;
            height: calc(100vh - 150px);
        }

        .skills-tabs {
            flex-direction: column;
        }

        .skills-tab {
            flex: none;
        }
    }
</style>
@endpush

@section('content')
<!-- Skills Background -->
<div class="skills-background"></div>
<div class="skills-overlay"></div>

<!-- Skills UI Overlay System -->
<div class="skills-ui-container">
    <!-- Header Panel - Top Center -->
    <div class="skills-header-panel">
        <h1 class="mb-1">🎯 Skills & Abilities</h1>
        <div class="small">Master various skills to enhance your abilities and unlock new techniques</div>
    </div>

    <!-- Main Skills Panel - Center -->
    <div class="main-skills-panel">
        <div class="mb-2">
            <h2 class="h6 mb-2">📚 Your Skills</h2>
        </div>

        <!-- Skill Category Tabs -->
        <div class="skills-tabs">
            <button class="skills-tab active" data-category="all" onclick="filterSkills('all')">
                🌟 All
            </button>
            <button class="skills-tab" data-category="passive" onclick="filterSkills('passive')">
                📈 Passive
            </button>
            <button class="skills-tab" data-category="active" onclick="filterSkills('active')">
                ⚡ Active
            </button>
            <button class="skills-tab" data-category="combat" onclick="filterSkills('combat')">
                ⚔️ Combat
            </button>
            <button class="skills-tab" data-category="crafting" onclick="filterSkills('crafting')">
                🔨 Crafting
            </button>
            <button class="skills-tab" data-category="gathering" onclick="filterSkills('gathering')">
                🌿 Gathering
            </button>
        </div>

        <!-- Skills Content -->
        <div class="skills-content">
            <div class="skills-grid" id="skills-grid">
                @if(isset($skillData))
                    @foreach(['passive', 'active'] as $skillType)
                        @if(isset($skillData[$skillType]))
                            @foreach($skillData[$skillType] as $skillInfo)
                                @php
                                    $isLearned = $skillInfo['level'] > 0;
                                    $canLearn = isset($skillInfo['can_learn']) && $skillInfo['can_learn'] && !$isLearned;
                                    $cardClass = $isLearned ? 'learned' : ($canLearn ? 'available' : 'unlearned');
                                @endphp
                                <div class="skill-card {{ $cardClass }}" data-category="{{ $skillInfo['skill']->category ?? $skillType }}" data-type="{{ $skillType }}">

                                    <div class="skill-header">
                                        <div class="skill-icon">
                                            {{ $skillInfo['skill']->icon ?? '🎯' }}
                                        </div>
                                        <div class="skill-info">
                                            <h3>{{ $skillInfo['skill']->name }}</h3>
                                            <div class="skill-level {{ $isLearned ? 'learned' : 'unlearned' }}">
                                                Level {{ $skillInfo['level'] }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="skill-description">
                                        {{ $skillInfo['skill']->description }}
                                    </div>

                                    @if($isLearned)
                                        <!-- Progress Bar for Learned Skills -->
                                        <div class="skill-progress">
                                            <div class="progress-bar-container">
                                                <div class="progress-bar" style="width: {{ $skillInfo['progress_percentage'] ?? 0 }}%"></div>
                                            </div>
                                            <div class="progress-text">
                                                <span>{{ number_format($skillInfo['progress_percentage'] ?? 0, 1) }}% to next level</span>
                                                <span>{{ number_format($skillInfo['exp_to_next'] ?? 0) }} XP needed</span>
                                            </div>
                                        </div>

                                        <!-- Current Effects -->
                                        @if(!empty($skillInfo['current_effects']))
                                            <div class="skill-effects">
                                                <div class="effect-title">Current Bonuses:</div>
                                                @foreach($skillInfo['current_effects'] as $effect => $value)
                                                    <div class="effect-item">
                                                        • {{ ucfirst(str_replace('_', ' ', $effect)) }}: +{{ $value }}{{ in_array($effect, ['damage_reduction', 'crafting_bonus', 'yield_bonus']) ? '%' : '' }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    @elseif($canLearn)
                                        <!-- Learn Button for Available Skills -->
                                        <div class="skill-actions">
                                            @if($player->skill_points >= $skillInfo['skill']->base_cost)
                                                <button class="learn-btn learn-skill-btn"
                                                        data-skill-id="{{ $skillInfo['skill']->id }}"
                                                        data-skill-name="{{ $skillInfo['skill']->name }}"
                                                        data-skill-cost="{{ $skillInfo['skill']->base_cost }}">
                                                    ✨ Learn ({{ $skillInfo['skill']->base_cost }} SP)
                                                </button>
                                            @else
                                                <button class="learn-btn disabled" disabled>
                                                    ❌ Need {{ $skillInfo['skill']->base_cost }} SP
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <!-- Requirements not met -->
                                        <div class="skill-effects" style="background: rgba(108, 117, 125, 0.2); border-color: rgba(108, 117, 125, 0.4);">
                                            <div class="effect-title" style="color: #6c757d;">Requirements not met</div>
                                            <div class="effect-item">Complete prerequisites to unlock this skill</div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    @endforeach
                @else
                    <!-- Sample Skills for Demo -->
                    <div class="skill-card learned" data-category="combat" data-type="passive">
                        <div class="skill-header">
                            <div class="skill-icon">⚔️</div>
                            <div class="skill-info">
                                <h3>Combat Mastery</h3>
                                <div class="skill-level learned">Level 3</div>
                            </div>
                        </div>
                        <div class="skill-description">
                            Increases your damage with all weapons and improves combat effectiveness.
                        </div>
                        <div class="skill-progress">
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: 65%"></div>
                            </div>
                            <div class="progress-text">
                                <span>65.0% to next level</span>
                                <span>1,250 XP needed</span>
                            </div>
                        </div>
                        <div class="skill-effects">
                            <div class="effect-title">Current Bonuses:</div>
                            <div class="effect-item">• Damage bonus: +15%</div>
                            <div class="effect-item">• Critical hit chance: +5%</div>
                        </div>
                    </div>

                    <div class="skill-card available" data-category="crafting" data-type="active">
                        <div class="skill-header">
                            <div class="skill-icon">🔨</div>
                            <div class="skill-info">
                                <h3>Advanced Crafting</h3>
                                <div class="skill-level unlearned">Level 0</div>
                            </div>
                        </div>
                        <div class="skill-description">
                            Unlock advanced crafting recipes and improve success rates for complex items.
                        </div>
                        <div class="skill-actions">
                            <button class="learn-btn learn-skill-btn">
                                ✨ Learn (5 SP)
                            </button>
                        </div>
                    </div>

                    <div class="skill-card unlearned" data-category="gathering" data-type="passive">
                        <div class="skill-header">
                            <div class="skill-icon">🌿</div>
                            <div class="skill-info">
                                <h3>Herb Lore</h3>
                                <div class="skill-level unlearned">Level 0</div>
                            </div>
                        </div>
                        <div class="skill-description">
                            Increases the yield when gathering herbs and improves identification of rare plants.
                        </div>
                        <div class="skill-effects" style="background: rgba(108, 117, 125, 0.2); border-color: rgba(108, 117, 125, 0.4);">
                            <div class="effect-title" style="color: #6c757d;">Requirements not met</div>
                            <div class="effect-item">Requires Basic Gathering Level 5</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Skills Stats Panel - Top Right -->
    <div class="skills-stats-panel">
        <div class="mb-2">
            <h2 class="h6 mb-2">📊 Skill Statistics</h2>
        </div>

        <div class="stat-item">
            <div class="stat-value">{{ isset($player->skill_points) ? $player->skill_points : '12' }}</div>
            <div class="stat-label">Skill Points</div>
        </div>

        <div class="stat-item">
            <div class="stat-value">{{ isset($skillStats['total_skills']) ? $skillStats['total_skills'] : '8' }}</div>
            <div class="stat-label">Skills Learned</div>
        </div>

        <div class="stat-item">
            <div class="stat-value">{{ isset($skillStats['highest_level']) ? $skillStats['highest_level'] : '5' }}</div>
            <div class="stat-label">Highest Level</div>
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
            <a href="{{ route('game.achievements') }}" class="dashboard-btn primary">
                🏆 Achievements
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Skill filtering functionality
function filterSkills(category) {
    // Update active tab
    document.querySelectorAll('.skills-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelector(`[data-category="${category}"]`).classList.add('active');

    // Update active category in sidebar (if it exists)
    const categoryItems = document.querySelectorAll('.category-item');
    if (categoryItems.length > 0) {
        categoryItems.forEach(item => {
            item.classList.remove('active');
        });
        const targetCategoryItem = document.querySelector(`.category-item[data-category="${category}"]`);
        if (targetCategoryItem) {
            targetCategoryItem.classList.add('active');
        }
    }

    // Filter skill cards
    const cards = document.querySelectorAll('.skill-card');
    cards.forEach(card => {
        const cardCategory = card.getAttribute('data-category');
        const cardType = card.getAttribute('data-type');

        if (category === 'all' ||
            cardCategory === category ||
            cardType === category) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });

    // Add smooth transition
    const grid = document.getElementById('skills-grid');
    grid.style.opacity = '0.7';
    setTimeout(() => {
        grid.style.opacity = '1';
    }, 150);
}

// Learn skill functionality
function learnSkill(skillId, skillName, cost) {
    if (confirm(`Learn ${skillName} for ${cost} skill points?`)) {
        // Create a form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/game/skills/learn';

        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken.getAttribute('content');
            form.appendChild(csrfInput);
        }

        // Add skill ID
        const skillInput = document.createElement('input');
        skillInput.type = 'hidden';
        skillInput.name = 'skill_id';
        skillInput.value = skillId;
        form.appendChild(skillInput);

        document.body.appendChild(form);
        form.submit();
    }
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Show all skills by default
    filterSkills('all');

    // Add click handlers for learn buttons
    document.querySelectorAll('.learn-skill-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const skillId = this.getAttribute('data-skill-id');
            const skillName = this.getAttribute('data-skill-name');
            const cost = this.getAttribute('data-skill-cost');

            if (skillId && skillName && cost) {
                learnSkill(skillId, skillName, cost);
            }
        });
    });

    // Add hover effects for skill cards
    document.querySelectorAll('.skill-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            if (this.classList.contains('learned')) {
                // Could add some glow effect or animation for learned skills
            }
        });
    });
});
</script>
@endpush
