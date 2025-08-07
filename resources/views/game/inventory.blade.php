@extends('game.layout')

@section('title', 'Inventory')
@section('meta_description', 'Manage your equipment and items in Grassland Awakening - organize weapons, armor, consumables, and materials.')

@push('styles')
<style>
    /* Full-screen immersive layout */
    body {
        overflow: hidden;
    }

    .inventory-background {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-image: url('/img/backgrounds/inventory.png');
        z-index: 1;
    }

    .inventory-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: linear-gradient(rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.2));
        z-index: 2;
    }

    .inventory-ui-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 10;
        pointer-events: none;
    }

    .inventory-ui-container > * {
        pointer-events: all;
    }

    /* Header Panel - Top Center */
    .inventory-header-panel {
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

    /* Main Inventory Panel - Center Left */
    .main-inventory-panel {
        position: absolute;
        top: 140px;
        left: 20px;
        width: 80%;
        height: calc(100vh - 300px);
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
    .inventory-tabs {
        display: flex;
        gap: 5px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }

    .inventory-tab {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: rgba(255, 255, 255, 0.7);
        padding: 8px 12px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .inventory-tab.active,
    .inventory-tab:hover {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border-color: rgba(255, 255, 255, 0.4);
    }

    /* Items Container */
    .inventory-content {
        flex: 1;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .inventory-items-container {
        flex: 1;
        overflow-y: auto;
        padding-right: 10px;
    }

    /* Item Grid */
    .inventory-items-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 10px;
    }

    .inventory-item-card {
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        padding: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .inventory-item-card:hover {
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.4);
        transform: translateY(-2px);
    }

    .inventory-item-card.equipped {
        border-color: rgba(40, 167, 69, 0.8);
        background: rgba(40, 167, 69, 0.1);
    }

    .item-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 8px;
    }

    .item-icon {
        font-size: 1.2rem;
        margin-right: 8px;
    }

    .item-name {
        font-weight: bold;
        flex: 1;
        font-size: 0.9rem;
    }

    .item-quantity {
        background: rgba(255, 255, 255, 0.2);
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.75rem;
        margin-left: auto;
    }

    .item-details {
        font-size: 0.8rem;
        opacity: 0.8;
        line-height: 1.3;
    }

    .durability-bar {
        width: 100%;
        height: 3px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 2px;
        margin-top: 6px;
        overflow: hidden;
    }

    .durability-fill {
        height: 100%;
        background: linear-gradient(90deg, #dc3545, #28a745);
        border-radius: 2px;
        transition: width 0.3s ease;
    }

    /* Stats Panel - Top Right */
    .inventory-stats-panel {
        position: absolute;
        top: 100px;
        right: 20px;
        width: 280px;
        max-height: 300px;
        background: rgba(23, 162, 184, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 15px;
        color: white;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        overflow-y: auto;
    }

    .stat-item {
        text-align: center;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 8px;
        margin: 5px 0;
    }

    .stat-value {
        font-weight: bold;
        font-size: 1.1em;
    }

    .stat-label {
        font-size: 0.8em;
        opacity: 0.8;
    }

    /* Actions Panel - Bottom Right */
    .inventory-actions-panel {
        position: absolute;
        bottom: 20px;
        right: 20px;
        width: 280px;
        background: rgba(40, 167, 69, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 15px;
        color: white;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }

    .inventory-btn {
        background: linear-gradient(135deg, #495057, #6c757d);
        border: none;
        color: white;
        padding: 8px 12px;
        margin: 3px 0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        text-decoration: none;
        display: block;
        width: 100%;
        font-size: 0.85rem;
        text-align: center;
    }

    .inventory-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 12px rgba(0, 0, 0, 0.3);
        color: white;
        text-decoration: none;
    }

    .inventory-btn.primary { background: linear-gradient(135deg, #007bff, #0056b3); }
    .inventory-btn.success { background: linear-gradient(135deg, #28a745, #1e7e34); }
    .inventory-btn.warning { background: linear-gradient(135deg, #ffc107, #e0a800); }
    .inventory-btn.danger { background: linear-gradient(135deg, #dc3545, #c82333); }

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

    /* Recently Added Panel - Center Right */
    .recent-items-panel {
        position: absolute;
        top: 420px;
        right: 20px;
        width: 280px;
        height: 180px;
        background: rgba(255, 193, 7, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 15px;
        color: #333;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        display: flex;
        flex-direction: column;
    }

    .recent-items-list {
        flex: 1;
        overflow-y: auto;
        padding-right: 5px;
    }

    .recent-item {
        display: flex;
        align-items: center;
        padding: 6px;
        margin: 2px 0;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 6px;
        font-size: 0.8rem;
    }

    .recent-item-icon {
        margin-right: 8px;
        font-size: 1rem;
    }

    .recent-item-info {
        flex: 1;
        line-height: 1.2;
    }

    .recent-item-name {
        font-weight: bold;
    }

    .recent-item-time {
        opacity: 0.7;
        font-size: 0.7rem;
    }

    /* Rarity Colors */
    .rarity-common { color: #6c757d; }
    .rarity-uncommon { color: #28a745; }
    .rarity-rare { color: #007bff; }
    .rarity-epic { color: #6f42c1; }
    .rarity-legendary { color: #fd7e14; }

    /* Empty State */
    .empty-category {
        text-align: center;
        padding: 40px 20px;
        color: rgba(255, 255, 255, 0.6);
    }

    .empty-category-icon {
        font-size: 3rem;
        margin-bottom: 15px;
        opacity: 0.3;
    }

    /* Custom Scrollbar */
    .inventory-items-container::-webkit-scrollbar,
    .recent-items-list::-webkit-scrollbar {
        width: 8px;
    }

    .inventory-items-container::-webkit-scrollbar-track,
    .recent-items-list::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
    }

    .inventory-items-container::-webkit-scrollbar-thumb,
    .recent-items-list::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 4px;
    }

    .inventory-items-container::-webkit-scrollbar-thumb:hover,
    .recent-items-list::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.5);
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .main-inventory-panel {
            width: 50%;
        }

        .inventory-stats-panel, .inventory-actions-panel, .recent-items-panel {
            width: 250px;
        }
    }

    @media (max-width: 768px) {
        .main-inventory-panel {
            left: 10px;
            right: 10px;
            width: auto;
            height: calc(100vh - 180px);
        }

        .inventory-stats-panel, .inventory-actions-panel, .recent-items-panel {
            display: none;
        }

        .inventory-header-panel {
            padding: 10px 15px;
            left: 10px;
            right: 10px;
            transform: none;
        }

        .inventory-items-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
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
<script>
// Category switching functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.inventory-tab');
    const categories = document.querySelectorAll('.inventory-category');

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
});

// Item detail modal functionality
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

// Equipment functionality
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

// Use item functionality
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

// Sort inventory functionality
function sortInventory(sortBy) {
    const url = new URL(window.location);
    url.searchParams.set('sort', sortBy);
    window.location.href = url.toString();
}

// Repair all items functionality
function repairAllItems() {
    if (confirm('Repair all damaged items? This will cost gold based on item values.')) {
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
}
</script>
@endpush
