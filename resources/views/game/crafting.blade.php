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
            <div class="recipe-category" data-category="weapons">
                <div class="recipe-item" data-recipe="iron-sword">
                    <div class="recipe-name">Iron Sword ⚔️</div>
                    <div class="recipe-materials">3x Iron Ingot, 1x Wood</div>
                    <div class="recipe-level">Level 1</div>
                </div>
                <div class="recipe-item" data-recipe="steel-dagger">
                    <div class="recipe-name">Steel Dagger 🗡️</div>
                    <div class="recipe-materials">2x Steel Ingot, 1x Leather</div>
                    <div class="recipe-level">Level 3</div>
                </div>
                <div class="recipe-item" data-recipe="fire-staff">
                    <div class="recipe-name">Fire Staff 🔥</div>
                    <div class="recipe-materials">1x Magic Crystal, 2x Wood, 1x Fire Essence</div>
                    <div class="recipe-level">Level 5</div>
                </div>
            </div>
            
            <div class="recipe-category" data-category="armor" style="display: none;">
                <div class="recipe-item" data-recipe="leather-armor">
                    <div class="recipe-name">Leather Armor 👕</div>
                    <div class="recipe-materials">5x Leather, 2x Thread</div>
                    <div class="recipe-level">Level 1</div>
                </div>
                <div class="recipe-item" data-recipe="iron-helmet">
                    <div class="recipe-name">Iron Helmet ⛑️</div>
                    <div class="recipe-materials">4x Iron Ingot, 1x Cloth</div>
                    <div class="recipe-level">Level 2</div>
                </div>
            </div>
            
            <div class="recipe-category" data-category="consumables" style="display: none;">
                <div class="recipe-item" data-recipe="health-potion">
                    <div class="recipe-name">Health Potion 🧪</div>
                    <div class="recipe-materials">2x Red Herb, 1x Water, 1x Glass Vial</div>
                    <div class="recipe-level">Level 1</div>
                </div>
                <div class="recipe-item" data-recipe="mana-potion">
                    <div class="recipe-name">Mana Potion 💙</div>
                    <div class="recipe-materials">2x Blue Herb, 1x Water, 1x Glass Vial</div>
                    <div class="recipe-level">Level 2</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Crafting Area Panel - Center -->
    <div class="crafting-area-panel">
        <h3 class="mb-3">🛠️ Crafting Station</h3>
        
        <div class="mb-3">
            <div class="small mb-2">Place materials in the slots below:</div>
            <div class="crafting-slots">
                <div class="crafting-slot" data-slot="0">
                    <div class="slot-icon">📦</div>
                </div>
                <div class="crafting-slot" data-slot="1">
                    <div class="slot-icon">📦</div>
                </div>
                <div class="crafting-slot" data-slot="2">
                    <div class="slot-icon">📦</div>
                </div>
            </div>
        </div>
        
        <div class="mb-3">
            <div class="small mb-2">Result:</div>
            <div class="result-slot">
                <div class="slot-icon">❓</div>
            </div>
        </div>
        
        <button class="craft-button" disabled>
            🔨 Craft Item
        </button>
        
        <div class="mt-3">
            <div class="small opacity-75">
                Select a recipe from the left panel or drag materials to the slots above
            </div>
        </div>
    </div>

    <!-- Materials Panel - Right Side -->
    <div class="materials-panel">
        <div class="mb-2">
            <h2 class="h6 mb-2">🎒 Your Materials</h2>
        </div>
        
        <div class="materials-list">
            <div class="material-item" data-material="iron-ingot">
                <div class="material-icon" style="background: #8B4513; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: white;">⚒️</div>
                <div class="material-info">
                    <div class="material-name">Iron Ingot</div>
                    <div class="material-quantity">Quantity: 12</div>
                </div>
            </div>
            
            <div class="material-item" data-material="wood">
                <div class="material-icon" style="background: #8B4513; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: white;">🪵</div>
                <div class="material-info">
                    <div class="material-name">Wood</div>
                    <div class="material-quantity">Quantity: 8</div>
                </div>
            </div>
            
            <div class="material-item" data-material="leather">
                <div class="material-icon" style="background: #8B4513; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: white;">🦌</div>
                <div class="material-info">
                    <div class="material-name">Leather</div>
                    <div class="material-quantity">Quantity: 5</div>
                </div>
            </div>
            
            <div class="material-item" data-material="red-herb">
                <div class="material-icon" style="background: #228B22; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: white;">🌿</div>
                <div class="material-info">
                    <div class="material-name">Red Herb</div>
                    <div class="material-quantity">Quantity: 15</div>
                </div>
            </div>
            
            <div class="material-item" data-material="glass-vial">
                <div class="material-icon" style="background: #87CEEB; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: white;">🧪</div>
                <div class="material-info">
                    <div class="material-name">Glass Vial</div>
                    <div class="material-quantity">Quantity: 3</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Panel - Bottom Center -->
    <div class="quick-actions-panel">
        <div class="mb-2 text-center text-white">
            <div class="fw-bold small">Quick Actions</div>
        </div>
        <div class="d-flex gap-2 flex-wrap justify-content-center">
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
            // Remove previous selection
            recipes.forEach(r => r.classList.remove('selected'));
            // Add selection to clicked recipe
            this.classList.add('selected');
            
            // Enable craft button (placeholder functionality)
            const craftButton = document.querySelector('.craft-button');
            craftButton.disabled = false;
            craftButton.textContent = '🔨 Craft ' + this.querySelector('.recipe-name').textContent;
        });
    });
    
    // Material clicking (placeholder functionality)
    const materials = document.querySelectorAll('.material-item');
    materials.forEach(material => {
        material.addEventListener('click', function() {
            console.log('Selected material:', this.getAttribute('data-material'));
            // Here you would implement material selection/dragging logic
        });
    });
    
    // Craft button functionality (placeholder)
    const craftButton = document.querySelector('.craft-button');
    craftButton.addEventListener('click', function() {
        if (!this.disabled) {
            alert('Crafting functionality would be implemented here!');
            // Here you would implement the actual crafting logic
        }
    });
});
</script>
@endpush