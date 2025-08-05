@extends('game.layout')

@section('title', 'Village Management')

@section('content')
<div class="container-fluid">
    <!-- Village Overview Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h3 mb-2">{{ $village->name }}</h1>
                            <p class="text-muted mb-0" aria-label="Village description">{{ $village->description }}</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="village-level-badge">
                                <span class="badge bg-primary fs-6" aria-label="Village level {{ $village->level }}">
                                    Level {{ $village->level }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recipe Discovery Notifications -->
    @if(session('discovered_recipes'))
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <h5 class="alert-heading"><i class="fas fa-scroll"></i> New Recipes Discovered!</h5>
                <p class="mb-2">Your adventures have yielded new crafting knowledge:</p>
                <ul class="mb-0">
                    @foreach(session('discovered_recipes') as $recipe)
                    <li><strong>{{ $recipe->name }}</strong> - {{ $recipe->description }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    @php
        session()->forget('discovered_recipes');
    @endphp
    @endif

    <!-- Village Specializations -->
    @if($village->specializations->isNotEmpty())
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0">Village Specializations</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($village->specializations as $specialization)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="specialization-card p-3 border rounded" role="article" aria-labelledby="spec-{{ $specialization->id }}">
                                <h3 class="h6 text-primary mb-2" id="spec-{{ $specialization->id }}">{{ $specialization->name }}</h3>
                                <p class="small text-muted mb-2">{{ $specialization->description }}</p>
                                <div class="specialization-bonus">
                                    <span class="badge bg-success" aria-label="Specialization bonus">{{ $specialization->bonus_description }}</span>
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

    <!-- Crafting Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Village Crafting</h2>
                    <button type="button" class="btn btn-primary btn-sm" 
                            data-bs-toggle="modal" data-bs-target="#craftingModal">
                        <i class="fas fa-hammer" aria-hidden="true"></i> Open Crafting Station
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded">
                                <i class="fas fa-hammer fa-2x text-primary mb-2" aria-hidden="true"></i>
                                <h6>Blacksmith</h6>
                                <p class="small text-muted mb-2">Craft weapons and armor</p>
                                <small class="text-success">{{ $player->knownRecipes()->where('category', 'weapon')->count() + $player->knownRecipes()->where('category', 'armor')->count() }} recipes known</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded">
                                <i class="fas fa-flask fa-2x text-success mb-2" aria-hidden="true"></i>
                                <h6>Alchemy</h6>
                                <p class="small text-muted mb-2">Brew potions and consumables</p>
                                <small class="text-success">{{ $player->knownRecipes()->where('category', 'consumable')->count() }} recipes known</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded">
                                <i class="fas fa-gem fa-2x text-warning mb-2" aria-hidden="true"></i>
                                <h6>Enchanting</h6>
                                <p class="small text-muted mb-2">Create magical accessories</p>
                                <small class="text-success">{{ $player->knownRecipes()->where('category', 'accessory')->count() }} recipes known</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- NPCs Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0">Village NPCs ({{ $npcs->count() }})</h2>
                </div>
                <div class="card-body">
                    @if($npcs->isEmpty())
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3" aria-hidden="true"></i>
                        <p class="text-muted">No NPCs in your village yet. NPCs can be found during adventures!</p>
                    </div>
                    @else
                    <div class="row">
                        @foreach($npcs as $npc)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 npc-card" role="article" aria-labelledby="npc-{{ $npc->id }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h3 class="h6 mb-0" id="npc-{{ $npc->id }}">{{ $npc->name }}</h3>
                                        <span class="badge bg-secondary" aria-label="NPC level {{ $npc->level }}">Lv.{{ $npc->level }}</span>
                                    </div>
                                    <p class="small text-muted mb-2">{{ ucfirst($npc->profession) }}</p>
                                    
                                    <!-- NPC Stats -->
                                    <div class="npc-stats mb-3">
                                        <div class="row g-2 text-center">
                                            <div class="col-4">
                                                <div class="stat-mini">
                                                    <div class="small text-muted">STR</div>
                                                    <div class="fw-bold" aria-label="Strength {{ $npc->strength }}">{{ $npc->strength }}</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="stat-mini">
                                                    <div class="small text-muted">INT</div>
                                                    <div class="fw-bold" aria-label="Intelligence {{ $npc->intelligence }}">{{ $npc->intelligence }}</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="stat-mini">
                                                    <div class="small text-muted">WIS</div>
                                                    <div class="fw-bold" aria-label="Wisdom {{ $npc->wisdom }}">{{ $npc->wisdom }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- NPC Skills -->
                                    @if($npc->skills->isNotEmpty())
                                    <div class="npc-skills mb-3">
                                        <h4 class="small text-muted mb-2">Skills:</h4>
                                        <div class="skills-list">
                                            @foreach($npc->skills->take(3) as $skill)
                                            <span class="badge bg-light text-dark me-1 mb-1" title="{{ $skill->description }}">{{ $skill->name }}</span>
                                            @endforeach
                                            @if($npc->skills->count() > 3)
                                            <span class="small text-muted">+{{ $npc->skills->count() - 3 }} more</span>
                                            @endif
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Action Buttons -->
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-primary btn-sm flex-fill" 
                                                onclick="trainNPC({{ $npc->id }})" 
                                                aria-label="Train {{ $npc->name }}">
                                            <i class="fas fa-dumbbell" aria-hidden="true"></i> Train
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" 
                                                data-bs-toggle="modal" data-bs-target="#npcModal{{ $npc->id }}"
                                                aria-label="View {{ $npc->name }} details">
                                            <i class="fas fa-eye" aria-hidden="true"></i>
                                        </button>
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
</div>


<!-- NPC Detail Modals -->
@foreach($npcs as $npc)
<div class="modal fade" id="npcModal{{ $npc->id }}" tabindex="-1" aria-labelledby="npcModal{{ $npc->id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="npcModal{{ $npc->id }}Label">{{ $npc->name }} - {{ ucfirst($npc->profession) }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Character Stats</h6>
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <td>Level</td>
                                    <td>{{ $npc->level }}</td>
                                </tr>
                                <tr>
                                    <td>Experience</td>
                                    <td>{{ $npc->experience }} / {{ $npc->level * 100 }}</td>
                                </tr>
                                <tr>
                                    <td>Strength</td>
                                    <td>{{ $npc->strength }}</td>
                                </tr>
                                <tr>
                                    <td>Intelligence</td>
                                    <td>{{ $npc->intelligence }}</td>
                                </tr>
                                <tr>
                                    <td>Wisdom</td>
                                    <td>{{ $npc->wisdom }}</td>
                                </tr>
                                <tr>
                                    <td>Health</td>
                                    <td>{{ $npc->health }} / {{ $npc->max_health }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Skills & Abilities</h6>
                        @if($npc->skills->isNotEmpty())
                        <div class="skills-detailed">
                            @foreach($npc->skills as $skill)
                            <div class="skill-item mb-2 p-2 border rounded">
                                <div class="fw-bold">{{ $skill->name }}</div>
                                <div class="small text-muted">{{ $skill->description }}</div>
                                @if($skill->prerequisites)
                                <div class="small text-info">Prerequisites: {{ $skill->prerequisites }}</div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-muted">No skills learned yet. Train this NPC to unlock abilities!</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="trainNPC({{ $npc->id }})" data-bs-dismiss="modal">Train NPC</button>
            </div>
        </div>
    </div>
</div>
@endforeach

<!-- Crafting Modal -->
<div class="modal fade" id="craftingModal" tabindex="-1" aria-labelledby="craftingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="craftingModalLabel">Village Crafting Station</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Categories Navigation -->
                    <div class="col-md-3">
                        <div class="list-group">
                            <button type="button" class="list-group-item list-group-item-action active" 
                                    onclick="showCraftingCategory('all')">
                                <i class="fas fa-list me-2"></i> All Recipes
                            </button>
                            <button type="button" class="list-group-item list-group-item-action" 
                                    onclick="showCraftingCategory('weapon')">
                                <i class="fas fa-sword me-2"></i> Weapons
                            </button>
                            <button type="button" class="list-group-item list-group-item-action" 
                                    onclick="showCraftingCategory('armor')">
                                <i class="fas fa-shield-alt me-2"></i> Armor
                            </button>
                            <button type="button" class="list-group-item list-group-item-action" 
                                    onclick="showCraftingCategory('accessory')">
                                <i class="fas fa-gem me-2"></i> Accessories
                            </button>
                            <button type="button" class="list-group-item list-group-item-action" 
                                    onclick="showCraftingCategory('consumable')">
                                <i class="fas fa-flask me-2"></i> Consumables
                            </button>
                        </div>
                    </div>
                    
                    <!-- Recipes List -->
                    <div class="col-md-9">
                        <div id="craftingContent">
                            <div class="text-center py-4">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Loading recipes...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="me-auto">
                    <small class="text-muted">
                        <i class="fas fa-coins me-1"></i> Gold: <span id="playerGold">{{ $player->persistent_currency }}</span>
                    </small>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function trainNPC(npcId) {
    if (confirm('Train this NPC? This will cost gold and time.')) {
        fetch(`{{ url('game/npc') }}/${npcId}/train`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Training failed. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
}

// Crafting functionality
let currentCategory = 'all';

function showCraftingCategory(category) {
    // Update active button
    document.querySelectorAll('.list-group-item').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    currentCategory = category;
    loadCraftingRecipes(category);
}

function loadCraftingRecipes(category = 'all') {
    const contentDiv = document.getElementById('craftingContent');
    contentDiv.innerHTML = '<div class="text-center py-4"><div class="spinner-border" role="status"><span class="visually-hidden">Loading recipes...</span></div></div>';
    
    fetch(`{{ url('game/crafting/recipes') }}?category=${category}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayRecipes(data.recipes);
        } else {
            contentDiv.innerHTML = '<div class="alert alert-danger">Failed to load recipes.</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        contentDiv.innerHTML = '<div class="alert alert-danger">An error occurred while loading recipes.</div>';
    });
}

function displayRecipes(recipes) {
    const contentDiv = document.getElementById('craftingContent');
    
    if (recipes.length === 0) {
        contentDiv.innerHTML = '<div class="text-center py-4"><p class="text-muted">No recipes available in this category.</p></div>';
        return;
    }
    
    let html = '<div class="row">';
    
    recipes.forEach((recipeData, index) => {
        const recipe = recipeData.recipe;
        const canCraft = recipeData.can_craft;
        const missingMaterials = recipeData.missing_materials;
        const hasGold = recipeData.has_gold;
        
        const cardClass = canCraft ? 'border-success' : 'border-secondary';
        const craftButtonClass = canCraft ? 'btn-success' : 'btn-outline-secondary';
        const craftButtonDisabled = canCraft ? '' : 'disabled';
        
        html += `
            <div class="col-md-6 mb-3">
                <div class="card h-100 ${cardClass}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title mb-0">${recipe.name}</h6>
                            <span class="badge bg-${getDifficultyColor(recipe.difficulty)}">${recipe.difficulty}</span>
                        </div>
                        <p class="card-text small text-muted">${recipe.description}</p>
                        
                        <!-- Result Item -->
                        <div class="mb-2">
                            <strong>Creates:</strong> ${recipe.result_quantity}x ${recipe.result_item.name}
                        </div>
                        
                        <!-- Materials Required -->
                        <div class="mb-2">
                            <strong>Materials:</strong>
                            <ul class="list-unstyled small mb-0">`;
        
        recipe.materials.forEach(material => {
            const hasEnough = !missingMaterials.find(m => m.item.id === material.material_item.id);
            const textClass = hasEnough ? 'text-success' : 'text-danger';
            html += `<li class="${textClass}">${material.quantity_required}x ${material.material_item.name}</li>`;
        });
        
        html += `
                            </ul>
                        </div>
                        
                        <!-- Costs and Requirements -->
                        <div class="mb-3">
                            <div class="row text-center small">
                                <div class="col-4">
                                    <div class="${hasGold ? 'text-success' : 'text-danger'}">
                                        <i class="fas fa-coins"></i> ${recipe.gold_cost}
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-info">
                                        <i class="fas fa-clock"></i> ${recipe.crafting_time}s
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-warning">
                                        <i class="fas fa-star"></i> ${recipe.experience_reward} XP
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Craft Button -->
                        <button type="button" class="btn ${craftButtonClass} w-100" 
                                onclick="craftItem(${recipe.id})" ${craftButtonDisabled}>
                            <i class="fas fa-hammer me-1"></i> Craft Item
                        </button>
                        
                        ${!canCraft && missingMaterials.length > 0 ? `
                        <div class="mt-2">
                            <small class="text-danger">Missing: ${missingMaterials.map(m => m.item.name).join(', ')}</small>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    contentDiv.innerHTML = html;
}

function getDifficultyColor(difficulty) {
    switch(difficulty) {
        case 'basic': return 'success';
        case 'intermediate': return 'warning';
        case 'advanced': return 'danger';
        case 'master': return 'dark';
        default: return 'secondary';
    }
}

function craftItem(recipeId) {
    fetch(`{{ url('game/crafting/craft') }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ recipe_id: recipeId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update player gold
            document.getElementById('playerGold').textContent = data.player_gold;
            
            // Show success message
            GameUI.showModal('Crafting Success', `
                <div class="text-center">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>Item Crafted Successfully!</h5>
                    <p>You crafted <strong>${data.quantity}x ${data.item.item.name}</strong></p>
                    <p class="text-warning">+${data.experience_gained} XP gained</p>
                    ${data.gold_spent > 0 ? `<p class="text-info">-${data.gold_spent} gold spent</p>` : ''}
                </div>
            `);
            
            // Refresh the recipes to update availability
            loadCraftingRecipes(currentCategory);
        } else {
            GameUI.showModal('Crafting Failed', `
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                    <h5>Crafting Failed</h5>
                    <p>${data.message}</p>
                </div>
            `);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        GameUI.showModal('Error', `
            <div class="text-center">
                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                <h5>An Error Occurred</h5>
                <p>Please try again later.</p>
            </div>
        `);
    });
}

// Load recipes when modal is opened
document.getElementById('craftingModal').addEventListener('shown.bs.modal', function () {
    loadCraftingRecipes('all');
});
</script>
@endsection