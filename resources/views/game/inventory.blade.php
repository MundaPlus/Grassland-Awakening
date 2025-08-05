@extends('game.layout')

@section('title', 'Inventory')

@section('content')
<div class="container-fluid">
    <!-- Inventory Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h3 mb-2">Inventory</h1>
                            <p class="text-muted mb-0">Manage your items and equipment</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="inventory-summary">
                                <span class="badge bg-info fs-6 me-2">
                                    {{ $totalItems }} Items
                                </span>
                                <span class="badge bg-success fs-6">
                                    {{ number_format($totalValue) }} Gold Value
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Inventory Categories -->
        <div class="col-lg-8">
            <!-- Category Tabs -->
            <div class="card mb-4">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="inventoryTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="weapons-tab" data-bs-toggle="tab" data-bs-target="#weapons" 
                                    type="button" role="tab" aria-controls="weapons" aria-selected="true">
                                ⚔️ Weapons ({{ $inventorySlots['weapon']->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="armor-tab" data-bs-toggle="tab" data-bs-target="#armor" 
                                    type="button" role="tab" aria-controls="armor" aria-selected="false">
                                🛡️ Armor ({{ $inventorySlots['armor']->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="accessories-tab" data-bs-toggle="tab" data-bs-target="#accessories" 
                                    type="button" role="tab" aria-controls="accessories" aria-selected="false">
                                💍 Accessories ({{ $inventorySlots['accessory']->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="consumables-tab" data-bs-toggle="tab" data-bs-target="#consumables" 
                                    type="button" role="tab" aria-controls="consumables" aria-selected="false">
                                🧪 Consumables ({{ $inventorySlots['consumable']->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="materials-tab" data-bs-toggle="tab" data-bs-target="#materials" 
                                    type="button" role="tab" aria-controls="materials" aria-selected="false">
                                ⚒️ Materials ({{ $inventorySlots['crafting_material']->count() }})
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="misc-tab" data-bs-toggle="tab" data-bs-target="#misc" 
                                    type="button" role="tab" aria-controls="misc" aria-selected="false">
                                📦 Misc ({{ $inventorySlots['misc']->count() + $inventorySlots['quest_item']->count() }})
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="inventoryTabContent">
                        <!-- Weapons Tab -->
                        <div class="tab-pane fade show active" id="weapons" role="tabpanel" aria-labelledby="weapons-tab">
                            @include('game.inventory.category', ['items' => $inventorySlots['weapon'], 'category' => 'weapon'])
                        </div>
                        
                        <!-- Armor Tab -->
                        <div class="tab-pane fade" id="armor" role="tabpanel" aria-labelledby="armor-tab">
                            @include('game.inventory.category', ['items' => $inventorySlots['armor'], 'category' => 'armor'])
                        </div>
                        
                        <!-- Accessories Tab -->
                        <div class="tab-pane fade" id="accessories" role="tabpanel" aria-labelledby="accessories-tab">
                            @include('game.inventory.category', ['items' => $inventorySlots['accessory'], 'category' => 'accessory'])
                        </div>
                        
                        <!-- Consumables Tab -->
                        <div class="tab-pane fade" id="consumables" role="tabpanel" aria-labelledby="consumables-tab">
                            @include('game.inventory.category', ['items' => $inventorySlots['consumable'], 'category' => 'consumable'])
                        </div>
                        
                        <!-- Materials Tab -->
                        <div class="tab-pane fade" id="materials" role="tabpanel" aria-labelledby="materials-tab">
                            @include('game.inventory.category', ['items' => $inventorySlots['crafting_material'], 'category' => 'crafting_material'])
                        </div>
                        
                        <!-- Misc Tab -->
                        <div class="tab-pane fade" id="misc" role="tabpanel" aria-labelledby="misc-tab">
                            @include('game.inventory.category', ['items' => $inventorySlots['misc']->merge($inventorySlots['quest_item']), 'category' => 'misc'])
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Quick Equipment & Actions -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="h5 mb-0">Quick Actions</h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" onclick="sortInventory('name')">
                            📋 Sort by Name
                        </button>
                        <button class="btn btn-outline-primary" onclick="sortInventory('rarity')">
                            ⭐ Sort by Rarity
                        </button>
                        <button class="btn btn-outline-primary" onclick="sortInventory('value')">
                            💰 Sort by Value
                        </button>
                        <button class="btn btn-outline-success" onclick="repairAllItems()">
                            🔧 Repair All Items
                        </button>
                    </div>
                </div>
            </div>

            <!-- Inventory Stats -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="h5 mb-0">Inventory Statistics</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-6">
                            <div class="stat-card p-3 border rounded">
                                <div class="display-6 fw-bold text-primary">{{ $totalItems }}</div>
                                <small class="text-muted">Total Items</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-card p-3 border rounded">
                                <div class="display-6 fw-bold text-success">{{ number_format($totalValue) }}</div>
                                <small class="text-muted">Total Value</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <hr>
                            <h6 class="mb-2">Items by Rarity:</h6>
                            @foreach($itemsByRarity as $rarity => $count)
                                <div class="d-flex justify-content-between">
                                    <span class="rarity-{{ $rarity }}">{{ ucfirst($rarity) }}</span>
                                    <span class="badge bg-secondary">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recently Added Items -->
            @if($recentItems->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h3 class="h5 mb-0">Recently Added</h3>
                </div>
                <div class="card-body">
                    @foreach($recentItems as $recentItem)
                        <div class="d-flex align-items-center mb-2">
                            <div class="item-icon me-2">
                                <span class="badge bg-{{ $recentItem->item->getRarityColor() }}">
                                    @switch($recentItem->item->type)
                                        @case('weapon') ⚔️ @break
                                        @case('armor') 🛡️ @break
                                        @case('accessory') 💍 @break
                                        @case('consumable') 🧪 @break
                                        @default 📦
                                    @endswitch
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">
                                    {{ $recentItem->getDisplayName() }}
                                    @if($recentItem->hasAffixes())
                                        <small class="text-muted">✨</small>
                                    @endif
                                </div>
                                <small class="text-muted">{{ $recentItem->created_at->diffForHumans() }}</small>
                            </div>
                            @if($recentItem->quantity > 1)
                                <span class="badge bg-info">x{{ $recentItem->quantity }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Item Detail Modal -->
<div class="modal fade" id="itemDetailModal" tabindex="-1" aria-labelledby="itemDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemDetailModalLabel">Item Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="itemDetailContent">
                <!-- Content loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <div id="itemActions">
                    <!-- Action buttons loaded dynamically -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.inventory-item {
    border: 2px solid #dee2e6;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 10px;
    transition: border-color 0.2s, box-shadow 0.2s;
    cursor: pointer;
}

.inventory-item:hover {
    border-color: #adb5bd;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.inventory-item.equipped {
    border-color: #28a745;
    background-color: rgba(40, 167, 69, 0.05);
}

.item-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    border-radius: 8px;
    margin-right: 12px;
}

.durability-bar {
    width: 100%;
    height: 4px;
    background-color: #e9ecef;
    border-radius: 2px;
    overflow: hidden;
    margin-top: 8px;
}

.rarity-common { color: #6c757d; }
.rarity-uncommon { color: #28a745; }
.rarity-rare { color: #007bff; }
.rarity-epic { color: #6f42c1; }
.rarity-legendary { color: #fd7e14; }

.empty-category {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.empty-category i {
    font-size: 3rem;
    margin-bottom: 16px;
    display: block;
}
</style>

<script>
function showItemDetail(inventoryItemId) {
    fetch(`/game/inventory/item/${inventoryItemId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('itemDetailContent').innerHTML = data.html;
            document.getElementById('itemActions').innerHTML = data.actions;
            new bootstrap.Modal(document.getElementById('itemDetailModal')).show();
        })
        .catch(error => console.error('Error:', error));
}

function equipItem(inventoryItemId, slot) {
    fetch(`/game/inventory/equip/${inventoryItemId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ slot: slot })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            console.error('Failed to equip item:', data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

function useItem(inventoryItemId) {
    fetch(`/game/inventory/use/${inventoryItemId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            console.error('Failed to use item:', data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

function sortInventory(sortBy) {
    const url = new URL(window.location);
    url.searchParams.set('sort', sortBy);
    window.location.href = url.toString();
}

function repairAllItems() {
    GameUI.showConfirmModal(
        'Repair All Items',
        'Repair all damaged items? This will cost gold based on item values.',
        function() {
            fetch('/game/inventory/repair-all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    console.error('Failed to repair items:', data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    );
}
</script>
@endsection