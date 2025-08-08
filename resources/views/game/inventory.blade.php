@extends('game.layout')

@section('title', 'Inventory')
@section('meta_description', 'Manage your equipment and items in Grassland Awakening - organize weapons, armor, consumables, and materials.')

@push('styles')
@vite('resources/css/game/inventory.css')
@endpush

@section('content')
<!-- Inventory Background -->
<div class="inventory-background"></div>
<div class="inventory-overlay"></div>

<!-- Inventory UI Overlay System -->
<div class="inventory-ui-container">
    <!-- Header Panel - Top Center -->
    <div class="inventory-header-panel">
        <h1 class="mb-1">🎒 Inventory Management</h1>
        <div class="d-flex justify-content-center align-items-center gap-3">
            <span class="badge bg-info">{{ $totalItems }} Items</span>
            <span class="badge bg-success">{{ number_format($totalValue) }} 💰</span>
        </div>
    </div>

    <!-- Main Inventory Panel - Center Left -->
    <div class="main-inventory-panel">
        <!-- Category Tabs -->
        <div class="inventory-tabs">
            <button class="inventory-tab active" data-category="weapon">
                ⚔️ Weapons ({{ $inventorySlots['weapon']->count() }})
            </button>
            <button class="inventory-tab" data-category="armor">
                🛡️ Armor ({{ $inventorySlots['armor']->count() }})
            </button>
            <button class="inventory-tab" data-category="accessory">
                💍 Accessories ({{ $inventorySlots['accessory']->count() }})
            </button>
            <button class="inventory-tab" data-category="consumable">
                🧪 Consumables ({{ $inventorySlots['consumable']->count() }})
            </button>
            <button class="inventory-tab" data-category="crafting_material">
                ⚒️ Materials ({{ $inventorySlots['crafting_material']->count() }})
            </button>
            <button class="inventory-tab" data-category="misc">
                📦 Misc ({{ $inventorySlots['misc']->count() + $inventorySlots['quest_item']->count() }})
            </button>
        </div>

        <!-- Items Content -->
        <div class="inventory-content">
            <div class="inventory-items-container">
                <!-- Weapons Category -->
                <div class="inventory-category" data-category="weapon">
                    @if($inventorySlots['weapon']->count() > 0)
                        <div class="inventory-items-grid">
                            @foreach($inventorySlots['weapon'] as $item)
                                <div class="inventory-item-card @if($item->is_equipped) equipped @endif"
                                     onclick="showItemDetail({{ $item->id }})">
                                    <div class="item-header">
                                        <span class="item-icon">⚔️</span>
                                        <span class="item-name rarity-{{ $item->item->rarity }}">
                                            {{ $item->item->name }}
                                            @if($item->is_equipped) 🟢 @endif
                                        </span>
                                        @if($item->quantity > 1)
                                            <span class="item-quantity">x{{ $item->quantity }}</span>
                                        @endif
                                    </div>
                                    <div class="item-details">
                                        @if($item->item->damage)
                                            <div>⚔️ {{ $item->item->damage }} dmg</div>
                                        @endif
                                        @if($item->item->value)
                                            <div>💰 {{ number_format($item->item->value) }}</div>
                                        @endif
                                    </div>
                                    @if($item->current_durability !== null && $item->max_durability > 0)
                                        <div class="durability-bar">
                                            <div class="durability-fill" style="width: {{ ($item->current_durability / $item->max_durability) * 100 }}%"></div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-category">
                            <div class="empty-category-icon">⚔️</div>
                            <div>No weapons in inventory</div>
                        </div>
                    @endif
                </div>

                <!-- Armor Category -->
                <div class="inventory-category" data-category="armor" style="display: none;">
                    @if($inventorySlots['armor']->count() > 0)
                        <div class="inventory-items-grid">
                            @foreach($inventorySlots['armor'] as $item)
                                <div class="inventory-item-card @if($item->is_equipped) equipped @endif"
                                     onclick="showItemDetail({{ $item->id }})">
                                    <div class="item-header">
                                        <span class="item-icon">🛡️</span>
                                        <span class="item-name rarity-{{ $item->item->rarity }}">
                                            {{ $item->item->name }}
                                            @if($item->is_equipped) 🟢 @endif
                                        </span>
                                        @if($item->quantity > 1)
                                            <span class="item-quantity">x{{ $item->quantity }}</span>
                                        @endif
                                    </div>
                                    <div class="item-details">
                                        @if($item->item->ac_bonus)
                                            <div>🛡️ +{{ $item->item->ac_bonus }} AC</div>
                                        @endif
                                        @if($item->item->value)
                                            <div>💰 {{ number_format($item->item->value) }}</div>
                                        @endif
                                    </div>
                                    @if($item->current_durability !== null && $item->max_durability > 0)
                                        <div class="durability-bar">
                                            <div class="durability-fill" style="width: {{ ($item->current_durability / $item->max_durability) * 100 }}%"></div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-category">
                            <div class="empty-category-icon">🛡️</div>
                            <div>No armor in inventory</div>
                        </div>
                    @endif
                </div>

                <!-- Accessories Category -->
                <div class="inventory-category" data-category="accessory" style="display: none;">
                    @if($inventorySlots['accessory']->count() > 0)
                        <div class="inventory-items-grid">
                            @foreach($inventorySlots['accessory'] as $item)
                                <div class="inventory-item-card @if($item->is_equipped) equipped @endif"
                                     onclick="showItemDetail({{ $item->id }})">
                                    <div class="item-header">
                                        <span class="item-icon">💍</span>
                                        <span class="item-name rarity-{{ $item->item->rarity }}">
                                            {{ $item->item->name }}
                                            @if($item->is_equipped) 🟢 @endif
                                        </span>
                                        @if($item->quantity > 1)
                                            <span class="item-quantity">x{{ $item->quantity }}</span>
                                        @endif
                                    </div>
                                    <div class="item-details">
                                        @if($item->item->value)
                                            <div>💰 {{ number_format($item->item->value) }}</div>
                                        @endif
                                    </div>
                                    @if($item->current_durability !== null && $item->max_durability > 0)
                                        <div class="durability-bar">
                                            <div class="durability-fill" style="width: {{ ($item->current_durability / $item->max_durability) * 100 }}%"></div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-category">
                            <div class="empty-category-icon">💍</div>
                            <div>No accessories in inventory</div>
                        </div>
                    @endif
                </div>

                <!-- Consumables Category -->
                <div class="inventory-category" data-category="consumable" style="display: none;">
                    @if($inventorySlots['consumable']->count() > 0)
                        <div class="inventory-items-grid">
                            @foreach($inventorySlots['consumable'] as $item)
                                <div class="inventory-item-card" onclick="showItemDetail({{ $item->id }})">
                                    <div class="item-header">
                                        <span class="item-icon">🧪</span>
                                        <span class="item-name rarity-{{ $item->item->rarity }}">
                                            {{ $item->item->name }}
                                        </span>
                                        @if($item->quantity > 1)
                                            <span class="item-quantity">x{{ $item->quantity }}</span>
                                        @endif
                                    </div>
                                    <div class="item-details">
                                        @if($item->item->description)
                                            <div>{{ Str::limit($item->item->description, 60) }}</div>
                                        @endif
                                        @if($item->item->value)
                                            <div>💰 {{ number_format($item->item->value) }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-category">
                            <div class="empty-category-icon">🧪</div>
                            <div>No consumables in inventory</div>
                        </div>
                    @endif
                </div>

                <!-- Materials Category -->
                <div class="inventory-category" data-category="crafting_material" style="display: none;">
                    @if($inventorySlots['crafting_material']->count() > 0)
                        <div class="inventory-items-grid">
                            @foreach($inventorySlots['crafting_material'] as $item)
                                <div class="inventory-item-card" onclick="showItemDetail({{ $item->id }})">
                                    <div class="item-header">
                                        <span class="item-icon">⚒️</span>
                                        <span class="item-name rarity-{{ $item->item->rarity }}">
                                            {{ $item->item->name }}
                                        </span>
                                        @if($item->quantity > 1)
                                            <span class="item-quantity">x{{ $item->quantity }}</span>
                                        @endif
                                    </div>
                                    <div class="item-details">
                                        @if($item->item->description)
                                            <div>{{ Str::limit($item->item->description, 60) }}</div>
                                        @endif
                                        @if($item->item->value)
                                            <div>💰 {{ number_format($item->item->value) }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-category">
                            <div class="empty-category-icon">⚒️</div>
                            <div>No materials in inventory</div>
                        </div>
                    @endif
                </div>

                <!-- Misc Category -->
                <div class="inventory-category" data-category="misc" style="display: none;">
                    @php $miscItems = $inventorySlots['misc']->merge($inventorySlots['quest_item']); @endphp
                    @if($miscItems->count() > 0)
                        <div class="inventory-items-grid">
                            @foreach($miscItems as $item)
                                <div class="inventory-item-card" onclick="showItemDetail({{ $item->id }})">
                                    <div class="item-header">
                                        <span class="item-icon">📦</span>
                                        <span class="item-name rarity-{{ $item->item->rarity }}">
                                            {{ $item->item->name }}
                                        </span>
                                        @if($item->quantity > 1)
                                            <span class="item-quantity">x{{ $item->quantity }}</span>
                                        @endif
                                    </div>
                                    <div class="item-details">
                                        @if($item->item->description)
                                            <div>{{ Str::limit($item->item->description, 60) }}</div>
                                        @endif
                                        @if($item->item->value)
                                            <div>💰 {{ number_format($item->item->value) }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-category">
                            <div class="empty-category-icon">📦</div>
                            <div>No misc items in inventory</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Stats Panel - Top Right -->
    <div class="inventory-stats-panel">
        <div class="mb-2">
            <h2 class="h6 mb-2">📊 Inventory Statistics</h2>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $totalItems }}</div>
            <div class="stat-label">Total Items</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ number_format($totalValue) }}</div>
            <div class="stat-label">Total Value 💰</div>
        </div>
        @if($itemsByRarity)
            <div class="mt-2">
                <div class="small mb-1">Items by Rarity:</div>
                @foreach($itemsByRarity as $rarity => $count)
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="rarity-{{ $rarity }} small">{{ ucfirst($rarity) }}</span>
                        <span class="badge bg-secondary small">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Recently Added Panel - Center Right -->
    @if($recentItems->count() > 0)
    <div class="recent-items-panel">
        <div class="mb-2">
            <div class="fw-bold small">📦 Recently Added</div>
        </div>
        <div class="recent-items-list">
            @foreach($recentItems as $recentItem)
                <div class="recent-item">
                    <span class="recent-item-icon">
                        @switch($recentItem->item->type)
                            @case('weapon') ⚔️ @break
                            @case('armor') 🛡️ @break
                            @case('accessory') 💍 @break
                            @case('consumable') 🧪 @break
                            @default 📦
                        @endswitch
                    </span>
                    <div class="recent-item-info">
                        <div class="recent-item-name">
                            {{ Str::limit($recentItem->item->name, 20) }}
                        </div>
                        <div class="recent-item-time">{{ $recentItem->created_at->diffForHumans() }}</div>
                    </div>
                    @if($recentItem->quantity > 1)
                        <span class="badge bg-info small">x{{ $recentItem->quantity }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Actions Panel - Bottom Right -->
    <div class="inventory-actions-panel">
        <div class="mb-2">
            <div class="fw-bold small">⚡ Quick Actions</div>
        </div>
        <button class="inventory-btn primary" onclick="sortInventory('name')">
            📋 Sort by Name
        </button>
        <button class="inventory-btn primary" onclick="sortInventory('rarity')">
            ⭐ Sort by Rarity
        </button>
        <button class="inventory-btn primary" onclick="sortInventory('value')">
            💰 Sort by Value
        </button>
        <button class="inventory-btn success" onclick="repairAllItems()">
            🔧 Repair All Items
        </button>
        <hr style="border-color: rgba(255,255,255,0.2); margin: 10px 0;">
        <a href="{{ route('game.character') }}" class="inventory-btn warning">
            👤 Character Equipment
        </a>
        <a href="{{ route('game.village') }}" class="inventory-btn success">
            🏘️ Back to Village
        </a>
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
            <a href="{{ route('game.crafting') }}" class="dashboard-btn warning">
                🔨 Crafting
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

<!-- Item Detail Modal -->
<div class="modal fade" id="itemDetailModal" tabindex="-1" aria-labelledby="itemDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background: rgba(33, 37, 41, 0.95); backdrop-filter: blur(15px); border: 2px solid rgba(255, 255, 255, 0.3); color: white;">
            <div class="modal-header" style="border-color: rgba(255, 255, 255, 0.2);">
                <h5 class="modal-title" id="itemDetailModalLabel">Item Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="itemDetailContent">
                <!-- Content loaded dynamically -->
            </div>
            <div class="modal-footer" style="border-color: rgba(255, 255, 255, 0.2);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <div id="itemActions">
                    <!-- Action buttons loaded dynamically -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/game/inventory.js')
@endpush
