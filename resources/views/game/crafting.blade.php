@extends('game.layout')

@section('title', 'Crafting Workshop')
@section('meta_description', 'Craft powerful items and equipment in Grassland Awakening - combine materials to create weapons, armor, and consumables.')

@push('styles')
<style>
    /* Full-screen immersive layout */
    body {
        overflow: hidden;
    }
    
    .crafting-background {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-image: url('/img/backgrounds/crafting.png');
        z-index: 1;
    }
    
    .crafting-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: linear-gradient(rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.2));
        z-index: 2;
    }
    
    .crafting-ui-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 10;
        pointer-events: none;
    }
    
    .crafting-ui-container > * {
        pointer-events: all;
    }
    
    /* Header Panel - Top Center */
    .crafting-header-panel {
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
    
    /* Recipes Panel - Left Side */
    .recipes-panel {
        position: absolute;
        top: 100px;
        left: 20px;
        width: 350px;
        height: calc(100vh - 200px);
        background: rgba(40, 167, 69, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 20px;
        color: white;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        display: flex;
        flex-direction: column;
    }
    
    .recipe-tabs {
        display: flex;
        gap: 5px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }
    
    .recipe-tab {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: rgba(255, 255, 255, 0.7);
        padding: 6px 10px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.8rem;
        transition: all 0.3s ease;
        flex: 1;
        text-align: center;
    }
    
    .recipe-tab.active,
    .recipe-tab:hover {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border-color: rgba(255, 255, 255, 0.4);
    }
    
    .recipes-list {
        flex: 1;
        overflow-y: auto;
        padding-right: 5px;
    }
    
    .recipe-item {
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .recipe-item:hover,
    .recipe-item.selected {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.4);
        transform: translateY(-2px);
    }
    
    .recipe-name {
        font-weight: bold;
        margin-bottom: 4px;
        font-size: 0.9rem;
    }
    
    .recipe-materials {
        font-size: 0.75rem;
        opacity: 0.8;
        margin-bottom: 4px;
    }
    
    .recipe-level {
        font-size: 0.7rem;
        background: rgba(0, 0, 0, 0.3);
        border-radius: 4px;
        padding: 2px 6px;
        display: inline-block;
    }
    
    /* Crafting Area - Center */
    .crafting-area-panel {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 450px;
        height: 400px;
        background: rgba(33, 37, 41, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 20px;
        padding: 25px;
        color: white;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        text-align: center;
    }
    
    .crafting-slots {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        margin: 20px 0;
    }
    
    .crafting-slot {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.1);
        border: 2px dashed rgba(255, 255, 255, 0.4);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        margin: 0 auto;
    }
    
    .crafting-slot:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.6);
    }
    
    .crafting-slot.filled {
        background: rgba(40, 167, 69, 0.9);
        border: 3px solid #28a745;
        border-style: solid;
    }

    .crafting-slot.missing {
        background: rgba(220, 53, 69, 0.9);
        border: 3px solid #dc3545;
        border-style: solid;
    }

    .slot-label {
        position: absolute;
        bottom: -20px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 0.7rem;
        opacity: 0.8;
        white-space: nowrap;
    }

    .recipe-item.cannot-craft {
        opacity: 0.6;
    }

    .recipe-missing {
        font-size: 0.7rem;
        color: #dc3545;
        margin-top: 4px;
    }
    
    .result-slot {
        width: 100px;
        height: 100px;
        background: rgba(255, 193, 7, 0.9);
        border: 3px solid #ffc107;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 20px auto;
        position: relative;
    }
    
    .slot-item {
        width: 60px;
        height: 60px;
        object-fit: contain;
    }
    
    .slot-icon {
        font-size: 2rem;
        opacity: 0.6;
    }
    
    .craft-button {
        background: linear-gradient(135deg, #28a745, #1e7e34);
        border: none;
        color: white;
        padding: 12px 24px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 1rem;
        font-weight: bold;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        margin-top: 15px;
    }
    
    .craft-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
    }
    
    .craft-button:disabled {
        background: linear-gradient(135deg, #6c757d, #495057);
        cursor: not-allowed;
        opacity: 0.6;
    }
    
    /* Materials Panel - Right Side */
    .materials-panel {
        position: absolute;
        top: 100px;
        right: 20px;
        width: 300px;
        height: calc(100vh - 200px);
        background: rgba(23, 162, 184, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 20px;
        color: white;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        display: flex;
        flex-direction: column;
    }
    
    .materials-list {
        flex: 1;
        overflow-y: auto;
        padding-right: 5px;
    }
    
    .material-item {
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .material-item:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.4);
        transform: translateX(5px);
    }
    
    .material-icon {
        width: 40px;
        height: 40px;
        object-fit: contain;
        flex-shrink: 0;
    }
    
    .material-info {
        flex: 1;
    }
    
    .material-name {
        font-weight: bold;
        font-size: 0.8rem;
        margin-bottom: 2px;
    }
    
    .material-quantity {
        font-size: 0.7rem;
        opacity: 0.8;
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
    }
    
    .dashboard-btn.primary { background: linear-gradient(135deg, #007bff, #0056b3); }
    .dashboard-btn.success { background: linear-gradient(135deg, #28a745, #1e7e34); }
    .dashboard-btn.warning { background: linear-gradient(135deg, #ffc107, #e0a800); }
    .dashboard-btn.danger { background: linear-gradient(135deg, #dc3545, #c82333); }
    
    /* Custom Scrollbar */
    .recipes-list::-webkit-scrollbar,
    .materials-list::-webkit-scrollbar {
        width: 6px;
    }
    
    .recipes-list::-webkit-scrollbar-track,
    .materials-list::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 3px;
    }
    
    .recipes-list::-webkit-scrollbar-thumb,
    .materials-list::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 3px;
    }
    
    .recipes-list::-webkit-scrollbar-thumb:hover,
    .materials-list::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.5);
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: rgba(255, 255, 255, 0.6);
    }
    
    .empty-state-icon {
        font-size: 3rem;
        margin-bottom: 15px;
        opacity: 0.3;
    }
    
    /* Responsive Design */
    @media (max-width: 1200px) {
        .recipes-panel, .materials-panel {
            width: 280px;
        }
        
        .crafting-area-panel {
            width: 400px;
            height: 350px;
        }
    }
    
    @media (max-width: 768px) {
        .recipes-panel, .materials-panel {
            display: none;
        }
        
        .crafting-area-panel {
            width: 90%;
            height: auto;
            top: 50%;
        }
        
        .crafting-header-panel {
            left: 10px;
            right: 10px;
            transform: none;
        }
    }
</style>
@endpush

@section('content')
<!-- Crafting Background -->
<div class="crafting-background"></div>
<div class="crafting-overlay"></div>

<!-- Crafting UI Overlay System -->
<div class="crafting-ui-container">
    <!-- Header Panel - Top Center -->
    <div class="crafting-header-panel">
        <h1 class="mb-1">🔨 Crafting Workshop</h1>
        <div class="small">Combine materials to create powerful items</div>
    </div>

    <!-- Recipes Panel - Left Side -->
    <div class="recipes-panel">
        <div class="mb-2">
            <h2 class="h6 mb-2">📜 Available Recipes</h2>
        </div>
        
        <!-- Recipe Tabs -->
        <div class="recipe-tabs">
            <button class="recipe-tab active" data-category="weapons">⚔️ Weapons</button>
            <button class="recipe-tab" data-category="armor">🛡️ Armor</button>
            <button class="recipe-tab" data-category="consumables">🧪 Potions</button>
        </div>
        
        <!-- Recipes List -->
        <div class="recipes-list">
            @if(isset($availableRecipes) && count($availableRecipes) > 0)
                @php
                    $recipesByCategory = collect($availableRecipes)->groupBy(function($recipeData) {
                        return $recipeData['recipe']->category ?? 'misc';
                    });
                @endphp
                
                @foreach(['weapon' => 'weapons', 'armor' => 'armor', 'consumable' => 'consumables'] as $category => $displayCategory)
                    <div class="recipe-category" data-category="{{ $displayCategory }}" style="{{ $displayCategory === 'weapons' ? '' : 'display: none;' }}">
                        @if(isset($recipesByCategory[$category]))
                            @foreach($recipesByCategory[$category] as $recipeData)
                                @php
                                    $recipe = $recipeData['recipe'];
                                    $canCraft = $recipeData['can_craft'];
                                    $missingMaterials = $recipeData['missing_materials'];
                                @endphp
                                <div class="recipe-item {{ $canCraft ? 'can-craft' : 'cannot-craft' }}" 
                                     data-recipe-id="{{ $recipe->id }}"
                                     data-recipe="{{ Str::slug($recipe->name) }}">
                                    <div class="recipe-name">{{ $recipe->name }}</div>
                                    <div class="recipe-materials">
                                        @foreach($recipe->materials as $material)
                                            {{ $material->quantity_required }}x {{ $material->materialItem->name }}{{ !$loop->last ? ', ' : '' }}
                                        @endforeach
                                    </div>
                                    <div class="recipe-level">Level {{ $recipe->difficulty ?? 1 }}</div>
                                    @if(!$canCraft && count($missingMaterials) > 0)
                                        <div class="recipe-missing" style="font-size: 0.7rem; color: #dc3545; margin-top: 4px;">
                                            Missing: 
                                            @foreach($missingMaterials as $missing)
                                                {{ $missing['needed'] }}x {{ $missing['item']->name }}{{ !$loop->last ? ', ' : '' }}
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="empty-state">
                                <div class="empty-state-icon">📝</div>
                                <div>No {{ $displayCategory }} recipes available</div>
                            </div>
                        @endif
                    </div>
                @endforeach
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">📚</div>
                    <div>No recipes learned yet</div>
                    <div class="small mt-2">Discover recipes through adventures!</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Crafting Area Panel - Center -->
    <div class="crafting-area-panel">
        <h3 class="mb-3">🛠️ Crafting Station</h3>
        
        <div class="mb-3">
            <div class="small mb-2">Required materials for selected recipe:</div>
            <div class="crafting-slots" id="material-slots">
                <div class="crafting-slot" data-slot="0">
                    <div class="slot-icon">📦</div>
                    <div class="slot-label">Material 1</div>
                </div>
                <div class="crafting-slot" data-slot="1">
                    <div class="slot-icon">📦</div>
                    <div class="slot-label">Material 2</div>
                </div>
                <div class="crafting-slot" data-slot="2">
                    <div class="slot-icon">📦</div>
                    <div class="slot-label">Material 3</div>
                </div>
            </div>
        </div>
        
        <div class="mb-3">
            <div class="small mb-2">Result:</div>
            <div class="result-slot" id="result-slot">
                <div class="slot-icon">❓</div>
            </div>
        </div>
        
        <form id="craft-form" method="POST" action="{{ route('game.crafting-craft') }}">
            @csrf
            <input type="hidden" id="selected-recipe-id" name="recipe_id" value="">
            <button type="submit" class="craft-button" id="craft-button" disabled>
                🔨 Craft Item
            </button>
        </form>
        
        <div class="mt-3">
            <div class="small opacity-75" id="craft-instructions">
                Select a recipe from the left panel to see required materials
            </div>
        </div>
    </div>

    <!-- Materials Panel - Right Side -->
    <div class="materials-panel">
        <div class="mb-2">
            <h2 class="h6 mb-2">🎒 Your Materials</h2>
        </div>
        
        <div class="materials-list">
            @if(isset($materials) && count($materials) > 0)
                @foreach($materials as $inventoryItem)
                    <div class="material-item" data-material-id="{{ $inventoryItem->item->id }}" data-material="{{ Str::slug($inventoryItem->item->name) }}">
                        <div class="material-icon" style="background: #6c757d; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem;">
                            {{ $inventoryItem->item->icon ?? '📦' }}
                        </div>
                        <div class="material-info">
                            <div class="material-name">{{ $inventoryItem->item->name }}</div>
                            <div class="material-quantity">Quantity: {{ $inventoryItem->quantity }}</div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">📦</div>
                    <div>No crafting materials</div>
                    <div class="small mt-2">Gather materials through adventures!</div>
                </div>
            @endif
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
            <a href="{{ route('game.adventures') }}" class="dashboard-btn danger">
                🗺️ Adventure
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
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedRecipe = null;
let availableRecipes = @json($availableRecipes ?? []);
let playerMaterials = @json($materials ?? []);

// Recipe category switching
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.recipe-tab');
    const categories = document.querySelectorAll('.recipe-category');
    
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
    
    // Recipe selection
    const recipes = document.querySelectorAll('.recipe-item');
    recipes.forEach(recipe => {
        recipe.addEventListener('click', function() {
            const recipeId = this.getAttribute('data-recipe-id');
            selectRecipe(recipeId, this);
        });
    });
    
    // Craft button functionality
    const craftForm = document.getElementById('craft-form');
    if (craftForm) {
        craftForm.addEventListener('submit', function(e) {
            const craftButton = document.getElementById('craft-button');
            if (craftButton.disabled) {
                e.preventDefault();
                return;
            }
            
            if (!confirm('Are you sure you want to craft this item?')) {
                e.preventDefault();
            }
        });
    }
});

function selectRecipe(recipeId, recipeElement) {
    // Remove previous selection
    document.querySelectorAll('.recipe-item').forEach(r => r.classList.remove('selected'));
    // Add selection to clicked recipe
    recipeElement.classList.add('selected');
    
    // Find the recipe data
    const recipeData = availableRecipes.find(r => r.recipe.id == recipeId);
    if (!recipeData) {
        console.error('Recipe not found:', recipeId);
        return;
    }
    
    selectedRecipe = recipeData;
    
    // Update the crafting area
    updateCraftingArea(recipeData);
    
    // Update the craft button
    updateCraftButton(recipeData);
    
    // Set the selected recipe ID in the form
    document.getElementById('selected-recipe-id').value = recipeId;
}

function updateCraftingArea(recipeData) {
    const recipe = recipeData.recipe;
    const materials = recipe.materials || [];
    const materialSlots = document.querySelectorAll('#material-slots .crafting-slot');
    
    // Clear all slots first
    materialSlots.forEach((slot, index) => {
        slot.innerHTML = '<div class="slot-icon">📦</div><div class="slot-label">Material ' + (index + 1) + '</div>';
        slot.classList.remove('filled', 'missing');
    });
    
    // Fill slots with required materials
    materials.forEach((material, index) => {
        if (index < materialSlots.length) {
            const slot = materialSlots[index];
            const hasEnough = hasPlayerMaterial(material.material_item_id, material.quantity_required);
            
            slot.innerHTML = `
                <div class="slot-icon">${material.material_item?.icon || '📦'}</div>
                <div class="slot-label">${material.quantity_required}x ${material.material_item?.name || 'Unknown'}</div>
            `;
            
            if (hasEnough) {
                slot.classList.add('filled');
                slot.classList.remove('missing');
            } else {
                slot.classList.add('missing');
                slot.classList.remove('filled');
            }
        }
    });
    
    // Update result slot
    const resultSlot = document.getElementById('result-slot');
    if (resultSlot && recipe.result_item) {
        resultSlot.innerHTML = `
            <div class="slot-icon">${recipe.result_item.icon || '❓'}</div>
            <div class="slot-label">${recipe.result_quantity || 1}x ${recipe.result_item.name}</div>
        `;
    }
    
    // Update instructions
    const instructions = document.getElementById('craft-instructions');
    if (instructions) {
        if (recipeData.can_craft) {
            instructions.textContent = 'All materials available! Click craft to create this item.';
            instructions.style.color = '#28a745';
        } else {
            const missingMaterials = recipeData.missing_materials || [];
            if (missingMaterials.length > 0) {
                const missingNames = missingMaterials.map(m => `${m.needed}x ${m.item.name}`).join(', ');
                instructions.textContent = `Missing materials: ${missingNames}`;
                instructions.style.color = '#dc3545';
            } else {
                instructions.textContent = 'Requirements not met for this recipe.';
                instructions.style.color = '#ffc107';
            }
        }
    }
}

function updateCraftButton(recipeData) {
    const craftButton = document.getElementById('craft-button');
    const recipe = recipeData.recipe;
    
    if (recipeData.can_craft) {
        craftButton.disabled = false;
        craftButton.textContent = `🔨 Craft ${recipe.name}`;
        craftButton.style.background = 'linear-gradient(135deg, #28a745, #1e7e34)';
    } else {
        craftButton.disabled = true;
        craftButton.textContent = `❌ Cannot Craft ${recipe.name}`;
        craftButton.style.background = 'linear-gradient(135deg, #6c757d, #495057)';
    }
}

function hasPlayerMaterial(materialItemId, requiredQuantity) {
    const playerMaterial = playerMaterials.find(m => m.item.id == materialItemId);
    return playerMaterial && playerMaterial.quantity >= requiredQuantity;
}
</script>
@endpush