@extends('game.layout')

@section('title', 'Skills')

@section('content')
<div class="container-fluid">
    <!-- Skills Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h3 mb-2">Skills</h1>
                            <p class="text-muted mb-0">Master various skills to enhance your abilities and unlock new combat techniques</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="skill-stats">
                                <span class="badge bg-primary fs-6">{{ $skillStats['total_skills'] }} Skills Learned</span>
                                <small class="d-block text-muted mt-1">Level {{ $skillStats['highest_level'] }} highest</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Skill Categories Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="btn-group flex-wrap" role="group" aria-label="Skill categories">
                        <button type="button" class="btn btn-outline-primary active" onclick="filterSkills('all')" id="filter-all">
                            All Skills
                        </button>
                        <button type="button" class="btn btn-outline-success" onclick="filterSkills('passive')" id="filter-passive">
                            Passive Skills
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="filterSkills('active')" id="filter-active">
                            Active Skills
                        </button>
                        <button type="button" class="btn btn-outline-warning" onclick="filterSkills('crafting')" id="filter-crafting">
                            Crafting
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="filterSkills('gathering')" id="filter-gathering">
                            Gathering
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="filterSkills('combat')" id="filter-combat">
                            Combat
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Passive Skills Section -->
    <div class="row mb-4" id="passive-skills-section">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h2 class="h5 mb-0">
                        <i class="fas fa-chart-line" aria-hidden="true"></i> Passive Skills
                        <small class="opacity-75">(Automatic progression)</small>
                    </h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($skillData['passive'] as $skillInfo)
                        <div class="col-lg-6 mb-3 skill-item" data-type="passive" data-category="{{ $skillInfo['skill']->category }}">
                            <div class="card h-100 skill-card {{ $skillInfo['level'] > 0 ? 'learned' : 'unlearned' }}">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-2 text-center">
                                            <div class="skill-icon">
                                                <span class="fs-2">{{ $skillInfo['skill']->icon }}</span>
                                            </div>
                                        </div>
                                        <div class="col-10">
                                            <div class="d-flex justify-content-between mb-1">
                                                <h5 class="mb-0">{{ $skillInfo['skill']->name }}</h5>
                                                <span class="badge bg-{{ $skillInfo['level'] > 0 ? 'success' : 'secondary' }}">
                                                    Level {{ $skillInfo['level'] }}
                                                </span>
                                            </div>
                                            <p class="text-muted small mb-2">{{ $skillInfo['skill']->description }}</p>
                                            
                                            @if($skillInfo['level'] > 0)
                                                <!-- Progress Bar -->
                                                <div class="progress mb-2" style="height: 8px;">
                                                    <div class="progress-bar bg-success" 
                                                         style="width: {{ $skillInfo['progress_percentage'] }}%"
                                                         aria-valuenow="{{ $skillInfo['progress_percentage'] }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between small text-muted">
                                                    <span>{{ number_format($skillInfo['progress_percentage'], 1) }}% to next level</span>
                                                    <span>{{ number_format($skillInfo['exp_to_next']) }} XP needed</span>
                                                </div>
                                                
                                                <!-- Current Effects -->
                                                @if(!empty($skillInfo['current_effects']))
                                                <div class="mt-2">
                                                    <small class="text-success fw-bold">Current Bonuses:</small>
                                                    @foreach($skillInfo['current_effects'] as $effect => $value)
                                                    <div class="small text-success">
                                                        • {{ ucfirst(str_replace('_', ' ', $effect)) }}: +{{ $value }}{{ in_array($effect, ['damage_reduction', 'crafting_bonus', 'yield_bonus']) ? '%' : '' }}
                                                    </div>
                                                    @endforeach
                                                </div>
                                                @endif
                                            @else
                                                @if($skillInfo['can_learn'])
                                                <div class="alert alert-info small mb-0">
                                                    <i class="fas fa-info-circle"></i> Available to learn! Use related actions to gain experience.
                                                </div>
                                                @else
                                                <div class="small text-muted">
                                                    Requirements not met yet.
                                                </div>
                                                @endif
                                            @endif
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

    <!-- Active Skills Section -->
    <div class="row mb-4" id="active-skills-section">
        <div class="col-12">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h2 class="h5 mb-0">
                        <i class="fas fa-fist-raised" aria-hidden="true"></i> Active Skills
                        <small class="opacity-75">(Combat abilities)</small>
                    </h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($skillData['active'] as $skillInfo)
                        <div class="col-lg-6 mb-3 skill-item" data-type="active" data-category="{{ $skillInfo['skill']->category }}">
                            <div class="card h-100 skill-card {{ $skillInfo['level'] > 0 ? 'learned' : 'unlearned' }}">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-2 text-center">
                                            <div class="skill-icon">
                                                <span class="fs-2">{{ $skillInfo['skill']->icon }}</span>
                                            </div>
                                        </div>
                                        <div class="col-10">
                                            <div class="d-flex justify-content-between mb-1">
                                                <h5 class="mb-0">{{ $skillInfo['skill']->name }}</h5>
                                                <div>
                                                    @if($skillInfo['level'] > 0)
                                                    <span class="badge bg-danger">Level {{ $skillInfo['level'] }}</span>
                                                    @if($skillInfo['is_on_cooldown'])
                                                    <span class="badge bg-warning">Cooldown</span>
                                                    @endif
                                                    @else
                                                    <span class="badge bg-secondary">Locked</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <p class="text-muted small mb-2">{{ $skillInfo['skill']->description }}</p>
                                            
                                            @if($skillInfo['level'] > 0)
                                                <!-- Skill Details -->
                                                <div class="row text-center mb-2">
                                                    <div class="col-4">
                                                        <small class="text-muted d-block">Cost</small>
                                                        <strong>{{ $skillInfo['player_skill']->getCurrentCost() }}</strong>
                                                    </div>
                                                    <div class="col-4">
                                                        <small class="text-muted d-block">Cooldown</small>
                                                        <strong>{{ $skillInfo['skill']->getCooldownAtLevel($skillInfo['level']) }}s</strong>
                                                    </div>
                                                    <div class="col-4">
                                                        <small class="text-muted d-block">Uses</small>
                                                        <strong>{{ $skillInfo['player_skill']->times_used }}</strong>
                                                    </div>
                                                </div>
                                                
                                                <!-- Weapon Requirements -->
                                                @if(!empty($skillInfo['skill']->weapon_types))
                                                <div class="mb-2">
                                                    <small class="text-info fw-bold">Weapon Types:</small>
                                                    <div class="small">
                                                        @foreach($skillInfo['skill']->weapon_types as $weaponType)
                                                        <span class="badge bg-info me-1">{{ ucfirst($weaponType) }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @endif
                                                
                                                <!-- Current Effects -->
                                                @if(!empty($skillInfo['current_effects']))
                                                <div class="mt-2">
                                                    <small class="text-danger fw-bold">Effects at Level {{ $skillInfo['level'] }}:</small>
                                                    @foreach($skillInfo['current_effects'] as $effect => $value)
                                                    <div class="small text-danger">
                                                        • {{ ucfirst(str_replace('_', ' ', $effect)) }}: {{ $value }}{{ str_contains($effect, 'multiplier') ? 'x' : (str_contains($effect, 'chance') ? '%' : '') }}
                                                    </div>
                                                    @endforeach
                                                </div>
                                                @endif
                                            @else
                                                @if($skillInfo['can_learn'])
                                                <div class="alert alert-warning small mb-0">
                                                    <i class="fas fa-exclamation-triangle"></i> Available to learn! Meet the requirements to unlock.
                                                </div>
                                                @else
                                                <div class="small text-muted">
                                                    Requirements not met yet.
                                                    @if(!empty($skillInfo['skill']->requirements))
                                                    <br><strong>Requires:</strong>
                                                    @foreach($skillInfo['skill']->requirements as $req => $value)
                                                        @if($req == 'level')
                                                        Level {{ $value }}
                                                        @elseif($req == 'skills')
                                                        @foreach($value as $skillSlug => $skillLevel)
                                                        {{ ucfirst(str_replace('-', ' ', $skillSlug)) }} Level {{ $skillLevel }}
                                                        @endforeach
                                                        @endif
                                                    @endforeach
                                                    @endif
                                                </div>
                                                @endif
                                            @endif
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

    <!-- Skill Statistics -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="h5 mb-0">Skill Statistics</h3>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="h4 text-primary">{{ $skillStats['total_skills'] }}</div>
                                <div class="text-muted">Total Skills</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="h4 text-success">{{ $skillStats['passive_skills'] }}</div>
                                <div class="text-muted">Passive Skills</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="h4 text-danger">{{ $skillStats['active_skills'] }}</div>
                                <div class="text-muted">Active Skills</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="h4 text-info">{{ number_format($skillStats['average_level'], 1) }}</div>
                                <div class="text-muted">Average Level</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.skill-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.skill-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.skill-card.learned {
    border-left: 4px solid #28a745;
}

.skill-card.unlearned {
    opacity: 0.7;
    border-left: 4px solid #6c757d;
}

.skill-icon {
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
</style>

<script>
function filterSkills(filter) {
    // Update active button
    document.querySelectorAll('[id^="filter-"]').forEach(btn => {
        btn.classList.remove('active');
    });
    document.getElementById(`filter-${filter}`).classList.add('active');

    // Filter skill items
    const skillItems = document.querySelectorAll('.skill-item');
    const passiveSection = document.getElementById('passive-skills-section');
    const activeSection = document.getElementById('active-skills-section');

    skillItems.forEach(item => {
        const itemType = item.dataset.type;
        const itemCategory = item.dataset.category;
        
        let shouldShow = false;
        
        switch (filter) {
            case 'all':
                shouldShow = true;
                break;
            case 'passive':
                shouldShow = itemType === 'passive';
                break;
            case 'active':
                shouldShow = itemType === 'active';
                break;
            default:
                shouldShow = itemCategory === filter;
                break;
        }
        
        item.style.display = shouldShow ? '' : 'none';
    });

    // Show/hide sections based on filter
    if (filter === 'active') {
        passiveSection.style.display = 'none';
        activeSection.style.display = '';
    } else if (filter === 'passive') {
        passiveSection.style.display = '';
        activeSection.style.display = 'none';
    } else {
        passiveSection.style.display = '';
        activeSection.style.display = '';
    }
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.altKey) {
        switch(e.key) {
            case '1': filterSkills('all'); break;
            case '2': filterSkills('passive'); break;
            case '3': filterSkills('active'); break;
            case '4': filterSkills('crafting'); break;
            case '5': filterSkills('gathering'); break;
            case '6': filterSkills('combat'); break;
        }
    }
});
</script>
@endsection