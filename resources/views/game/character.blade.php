@extends('game.layout')

@section('title', 'Character Sheet')
@section('meta_description', 'Manage your character equipment and stats in Grassland Awakening - view abilities, allocate points, and equip gear.')

@push('styles')
<style>
    /* Full-screen immersive layout */
    body {
        overflow: hidden;
    }
    
    .character-background {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-image: url('/img/backgrounds/character.png');
        z-index: 1;
    }
    
    .character-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: linear-gradient(rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.2));
        z-index: 2;
    }
    
    .character-ui-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 10;
        pointer-events: none;
    }
    
    .character-ui-container > * {
        pointer-events: all;
    }
    
    /* Character Header Panel - Top Center */
    .character-header-panel {
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
        min-width: 600px;
    }
    
    .character-portrait {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: 3px solid rgba(255, 255, 255, 0.5);
        object-fit: cover;
    }
    
    .header-stats {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 15px;
        margin-top: 10px;
    }
    
    .header-stat {
        text-align: center;
    }
    
    .header-stat-value {
        font-weight: bold;
        font-size: 1.1em;
    }
    
    .header-stat-label {
        font-size: 0.7em;
        opacity: 0.8;
    }
    
    .experience-bar {
        width: 100%;
        height: 6px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 3px;
        margin: 8px 0;
        overflow: hidden;
    }
    
    .experience-fill {
        height: 100%;
        background: linear-gradient(90deg, #007bff, #20c997);
        border-radius: 3px;
        transition: width 0.3s ease;
    }
    
    /* Character Model Panel - Center */
    .character-model-panel {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 400px;
        height: 500px;
        background: rgba(33, 37, 41, 0.3);
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    
    .character-model {
        width: 250px;
        height: 400px;
        object-fit: contain;
        filter: drop-shadow(0 0 20px rgba(0, 0, 0, 0.7));
    }
    
    /* Equipment Slots Around Character Model */
    .equipment-slot {
        position: absolute;
        width: 80px;
        height: 80px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }
    
    .equipment-slot:hover {
        transform: scale(1.1);
        z-index: 20;
    }
    
    .empty-slot {
        background: rgba(255, 255, 255, 0.1);
        border: 2px dashed rgba(255, 255, 255, 0.4);
        color: rgba(255, 255, 255, 0.6);
    }
    
    .empty-slot:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.7);
    }
    
    .equipped-slot {
        border: 3px solid;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
    }
    
    .equipped-slot:hover {
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.6);
    }
    
    .equipped-slot.common { border-color: #6c757d; background: rgba(108, 117, 125, 0.9); }
    .equipped-slot.uncommon { border-color: #28a745; background: rgba(40, 167, 69, 0.9); }
    .equipped-slot.rare { border-color: #007bff; background: rgba(0, 123, 255, 0.9); }
    .equipped-slot.epic { border-color: #6f42c1; background: rgba(111, 66, 193, 0.9); }
    .equipped-slot.legendary { border-color: #fd7e14; background: rgba(253, 126, 20, 0.9); }
    
    .slot-icon {
        font-size: 2rem;
    }
    
    .slot-item-image {
        width: 60px;
        height: 60px;
        object-fit: contain;
    }
    
    /* Equipment slot positioning */
    .slot-helm { top: -10px; left: 50%; transform: translateX(-50%); }
    .slot-neck { top: 40px; right: -40px; }
    .slot-weapon-1 { top: 100px; left: -40px; width: 100px; height: 100px; }
    .slot-chest { top: 120px; left: 50%; transform: translateX(-50%); }
    .slot-weapon-2 { top: 100px; right: -50px; width: 100px; height: 100px; }
    .slot-gloves { top: 180px; left: -40px; }
    .slot-ring-1 { top: 180px; right: -40px; }
    .slot-pants { top: 240px; left: 50%; transform: translateX(-50%); }
    .slot-ring-2 { top: 240px; right: -40px; }
    .slot-boots { top: 320px; left: 50%; transform: translateX(-50%); }
    .slot-artifact-1 { bottom: 60px; left: -40px; }
    .slot-artifact-2 { bottom: 60px; right: -40px; }
    
    /* Stats Panel - Left Side */
    .character-stats-panel {
        position: absolute;
        top: 120px;
        left: 20px;
        width: 300px;
        background: rgba(40, 167, 69, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 20px;
        color: white;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }
    
    .ability-scores {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-bottom: 20px;
    }
    
    .ability-score {
        text-align: center;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 10px;
        transition: all 0.3s ease;
    }
    
    .ability-score:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
    }
    
    .ability-name {
        font-size: 0.8rem;
        font-weight: bold;
        margin-bottom: 4px;
    }
    
    .ability-value {
        font-size: 1.4rem;
        font-weight: bold;
        margin-bottom: 2px;
    }
    
    .ability-modifier {
        font-size: 0.75rem;
        background: rgba(0, 0, 0, 0.3);
        border-radius: 4px;
        padding: 2px 4px;
    }
    
    .equipment-bonuses {
        background: rgba(0, 0, 0, 0.2);
        border-radius: 8px;
        padding: 10px;
    }
    
    .bonus-item {
        display: flex;
        justify-content: space-between;
        margin: 2px 0;
        font-size: 0.85rem;
    }
    
    /* Inventory Panel - Right Side */
    .character-inventory-panel {
        position: absolute;
        top: 120px;
        right: 20px;
        width: 320px;
        height: calc(100vh - 160px);
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
    
    .inventory-tabs {
        display: flex;
        gap: 5px;
        margin-bottom: 15px;
    }
    
    .inventory-tab {
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
    
    .inventory-tab.active,
    .inventory-tab:hover {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border-color: rgba(255, 255, 255, 0.4);
    }
    
    .inventory-content {
        flex: 1;
        overflow-y: auto;
        padding-right: 5px;
    }
    
    .inventory-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
    }
    
    .inventory-item {
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        padding: 8px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        min-height: 100px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    
    .inventory-item:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.4);
        transform: translateY(-2px);
    }
    
    .inventory-item.equipped {
        border-color: rgba(40, 167, 69, 0.8);
        background: rgba(40, 167, 69, 0.2);
    }
    
    .item-image {
        width: 40px;
        height: 40px;
        object-fit: contain;
        margin: 0 auto 4px;
    }
    
    .item-name {
        font-size: 0.7rem;
        font-weight: bold;
        line-height: 1.1;
        margin-bottom: 4px;
    }
    
    .item-stats {
        font-size: 0.6rem;
        opacity: 0.8;
        margin-bottom: 4px;
    }
    
    .item-button {
        background: linear-gradient(135deg, #495057, #6c757d);
        border: none;
        color: white;
        padding: 4px 6px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.6rem;
        transition: all 0.3s ease;
    }
    
    .item-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
    }
    
    .item-button.equip { background: linear-gradient(135deg, #28a745, #1e7e34); }
    .item-button.unequip { background: linear-gradient(135deg, #ffc107, #e0a800); }
    
    .empty-inventory {
        text-align: center;
        padding: 40px 10px;
        color: rgba(255, 255, 255, 0.6);
    }
    
    /* Actions Panel - Bottom Left */
    .character-actions-panel {
        position: absolute;
        bottom: 20px;
        left: 20px;
        width: 300px;
        background: rgba(255, 193, 7, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 15px;
        color: #333;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }
    
    .character-btn {
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
    
    .character-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 12px rgba(0, 0, 0, 0.3);
        color: white;
        text-decoration: none;
    }
    
    .character-btn.primary { background: linear-gradient(135deg, #007bff, #0056b3); }
    .character-btn.success { background: linear-gradient(135deg, #28a745, #1e7e34); }
    .character-btn.warning { background: linear-gradient(135deg, #ffc107, #e0a800); }
    .character-btn.danger { background: linear-gradient(135deg, #dc3545, #c82333); }

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
    
    /* Rarity Colors */
    .rarity-common { color: #6c757d; }
    .rarity-uncommon { color: #28a745; }
    .rarity-rare { color: #007bff; }
    .rarity-epic { color: #6f42c1; }
    .rarity-legendary { color: #fd7e14; }
    
    /* Custom Scrollbar */
    .inventory-content::-webkit-scrollbar {
        width: 6px;
    }
    
    .inventory-content::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 3px;
    }
    
    .inventory-content::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 3px;
    }
    
    .inventory-content::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.5);
    }
    
    /* Responsive Design */
    @media (max-width: 1400px) {
        .character-stats-panel, .character-inventory-panel, .character-actions-panel {
            width: 250px;
        }
        
        .character-model-panel {
            width: 350px;
            height: 450px;
        }
        
        .character-model {
            width: 200px;
            height: 350px;
        }
    }
    
    @media (max-width: 1024px) {
        .character-stats-panel, .character-actions-panel {
            display: none;
        }
        
        .character-inventory-panel {
            right: 10px;
            width: 280px;
        }
        
        .character-model-panel {
            left: 30%;
        }
    }
    
    @media (max-width: 768px) {
        .character-model-panel {
            width: 300px;
            height: 400px;
            left: 50%;
            top: 45%;
        }
        
        .character-model {
            width: 150px;
            height: 300px;
        }
        
        .character-inventory-panel {
            top: 100px;
            right: 10px;
            left: 10px;
            width: auto;
            height: calc(100vh - 140px);
        }
        
        .character-header-panel {
            min-width: auto;
            left: 10px;
            right: 10px;
            transform: none;
        }
        
        .header-stats {
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }
    }
</style>
@endpush

@section('content')
<!-- Character Background -->
<div class="character-background"></div>
<div class="character-overlay"></div>

<!-- Character UI Overlay System -->
<div class="character-ui-container">
    <!-- Character Header Panel - Top Center -->
    <div class="character-header-panel">
        <div class="d-flex align-items-center justify-content-center gap-3 mb-2">
            <img src="{{ $player->getCharacterImagePath() }}" 
                 alt="{{ ucfirst($player->gender) }} Character" 
                 class="character-portrait">
            <div>
                <h1 class="mb-1">{{ $player->character_name }}</h1>
                <div class="small">Level {{ $player->level }} {{ ucfirst($player->gender) }}</div>
            </div>
            <button class="btn btn-sm btn-outline-light" onclick="showGenderModal()">⚧ Change</button>
        </div>
        
        <!-- Experience Bar -->
        @php
            $expToNext = $player->calculateExperienceToNextLevel();
            $expProgress = $expToNext > 0 ? ($player->experience / $expToNext) * 100 : 100;
        @endphp
        <div class="experience-bar">
            <div class="experience-fill" style="width: {{ $expProgress }}%"></div>
        </div>
        <div class="small">{{ number_format($player->experience) }}/{{ number_format($expToNext) }} XP</div>
        
        <!-- Header Stats -->
        <div class="header-stats">
            <div class="header-stat">
                <div class="header-stat-value text-success">{{ $player->hp }}/{{ $player->max_hp }}</div>
                <div class="header-stat-label">Health</div>
            </div>
            <div class="header-stat">
                <div class="header-stat-value text-primary">{{ $totalAC }}</div>
                <div class="header-stat-label">Armor Class</div>
            </div>
            <div class="header-stat">
                <div class="header-stat-value text-danger">{{ $maxDamage }}</div>
                <div class="header-stat-label">Max Damage</div>
            </div>
            <div class="header-stat">
                <div class="header-stat-value text-warning">{{ number_format($player->persistent_currency) }}</div>
                <div class="header-stat-label">Gold 💰</div>
            </div>
            <div class="header-stat">
                @if($player->hasUnallocatedStatPoints())
                    <div class="header-stat-value text-danger">{{ $player->unallocated_stat_points }}</div>
                    <div class="header-stat-label">Stat Points</div>
                @elseif($player->canLevelUp())
                    <div class="header-stat-value text-success">🎉</div>
                    <div class="header-stat-label">Level Up!</div>
                @else
                    <div class="header-stat-value text-info">{{ $player->level }}</div>
                    <div class="header-stat-label">Level</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Character Model Panel - Center -->
    <div class="character-model-panel">
        <img src="{{ $player->getCharacterImagePath() }}" 
             alt="{{ ucfirst($player->gender) }} Character" 
             class="character-model">
        
        <!-- Equipment Slots -->
        @php
            $slots = [
                'helm' => ['⛑️', 'Helmet'],
                'neck' => ['💎', 'Necklace'], 
                'weapon_1' => ['⚔️', 'Main Hand'],
                'chest' => ['👔', 'Chest Armor'],
                'weapon_2' => ['🗡️', 'Off Hand'],
                'gloves' => ['🧤', 'Gloves'],
                'ring_1' => ['💍', 'Ring 1'],
                'pants' => ['👖', 'Pants'],
                'ring_2' => ['💍', 'Ring 2'],
                'boots' => ['👢', 'Boots'],
                'artifact_1' => ['✨', 'Artifact 1'],
                'artifact_2' => ['✨', 'Artifact 2'],
            ];
        @endphp
        
        @foreach($slots as $slot => $config)
            <div class="equipment-slot slot-{{ $slot }} 
                        @if(isset($equipment[$slot]) && $equipment[$slot])
                            equipped-slot {{ $equipment[$slot]->item->rarity }}
                        @else
                            empty-slot
                        @endif"
                 data-slot="{{ $slot }}"
                 @if(isset($equipment[$slot]) && $equipment[$slot])
                     onclick="unequipPlayerItem({{ $equipment[$slot]->id }})"
                     title="{{ method_exists($equipment[$slot], 'getDisplayName') ? $equipment[$slot]->getDisplayName() : $equipment[$slot]->item->name }} (Click to unequip)"
                 @else
                     title="{{ $config[1] }}"
                 @endif>
                @if(isset($equipment[$slot]) && $equipment[$slot])
                    <img src="{{ $equipment[$slot]->item->getImagePath() }}" 
                         alt="{{ $equipment[$slot]->item->name }}"
                         class="slot-item-image">
                @else
                    <div class="slot-icon">{{ $config[0] }}</div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Character Stats Panel - Left Side -->
    <div class="character-stats-panel">
        <div class="mb-3">
            <h2 class="h6 mb-3">📊 Ability Scores</h2>
            <div class="ability-scores">
                @foreach(['str' => 'STR', 'dex' => 'DEX', 'con' => 'CON', 'int' => 'INT', 'wis' => 'WIS', 'cha' => 'CHA'] as $stat => $name)
                    <div class="ability-score">
                        <div class="ability-name">{{ $name }}</div>
                        <div class="ability-value">
                            {{ $player->getAttribute($stat) }}
                            @if($equipmentBonuses[$stat] != 0)
                                <span class="text-{{ $equipmentBonuses[$stat] > 0 ? 'success' : 'danger' }}" style="font-size: 0.7em;">
                                    {{ $equipmentBonuses[$stat] > 0 ? '+' : '' }}{{ $equipmentBonuses[$stat] }}
                                </span>
                            @endif
                        </div>
                        <div class="ability-modifier">
                            {{ floor(($totalStats[$stat] - 10) / 2) >= 0 ? '+' : '' }}{{ floor(($totalStats[$stat] - 10) / 2) }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        
        <div class="equipment-bonuses">
            <div class="fw-bold small mb-2">⚡ Equipment Bonuses</div>
            @php $hasAnyBonus = false; @endphp
            @foreach($equipmentBonuses as $stat => $bonus)
                @if($bonus != 0)
                    @php $hasAnyBonus = true; @endphp
                    <div class="bonus-item">
                        <span>
                            @if($stat === 'weapon_damage')
                                Weapon Damage
                            @elseif($stat === 'ac')
                                Armor Class
                            @else
                                {{ strtoupper($stat) }}
                            @endif
                        </span>
                        <span class="text-{{ $bonus > 0 ? 'success' : 'danger' }}">
                            {{ $bonus > 0 ? '+' : '' }}{{ $bonus }}
                        </span>
                    </div>
                @endif
            @endforeach
            @if(!$hasAnyBonus)
                <div class="text-center small opacity-75">No active bonuses</div>
            @endif
        </div>
    </div>

    <!-- Character Inventory Panel - Right Side -->
    <div class="character-inventory-panel">
        <div class="mb-2">
            <div class="fw-bold small">🎒 Quick Equipment</div>
        </div>
        
        <!-- Inventory Tabs -->
        <div class="inventory-tabs">
            <button class="inventory-tab active" data-category="weapons">
                ⚔️ {{ $inventory['weapons']->count() }}
            </button>
            <button class="inventory-tab" data-category="armor">
                🛡️ {{ $inventory['armor']->count() }}
            </button>
            <button class="inventory-tab" data-category="accessories">
                💍 {{ $inventory['accessories']->count() }}
            </button>
        </div>
        
        <!-- Inventory Content -->
        <div class="inventory-content">
            <!-- Weapons -->
            <div class="inventory-category" data-category="weapons">
                @if($inventory['weapons']->count() > 0)
                    <div class="inventory-grid">
                        @foreach($inventory['weapons'] as $item)
                            <div class="inventory-item @if($item->is_equipped) equipped @endif">
                                <img src="{{ $item->item->getImagePath() }}" 
                                     alt="{{ $item->item->name }}" 
                                     class="item-image">
                                <div class="item-name rarity-{{ $item->item->rarity }}">
                                    {{ Str::limit($item->item->name, 12) }}
                                </div>
                                <div class="item-stats">
                                    @if($item->item->damage_dice)
                                        ⚔️ {{ $item->item->damage_dice }}@if($item->item->damage_bonus > 0)+{{ $item->item->damage_bonus }}@endif
                                    @endif
                                </div>
                                @if($item->is_equipped)
                                    <button class="item-button unequip" onclick="unequipPlayerItem({{ $item->id }})">
                                        Unequip
                                    </button>
                                @else
                                    <button class="item-button equip" onclick="equipPlayerItem({{ $item->id }})">
                                        Equip
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-inventory">
                        <div style="font-size: 2rem; margin-bottom: 10px;">⚔️</div>
                        <div>No weapons in inventory</div>
                    </div>
                @endif
            </div>
            
            <!-- Armor -->
            <div class="inventory-category" data-category="armor" style="display: none;">
                @if($inventory['armor']->count() > 0)
                    <div class="inventory-grid">
                        @foreach($inventory['armor'] as $item)
                            <div class="inventory-item @if($item->is_equipped) equipped @endif">
                                <img src="{{ $item->item->getImagePath() }}" 
                                     alt="{{ $item->item->name }}" 
                                     class="item-image">
                                <div class="item-name rarity-{{ $item->item->rarity }}">
                                    {{ Str::limit($item->item->name, 12) }}
                                </div>
                                <div class="item-stats">
                                    @if($item->item->ac_bonus)
                                        🛡️ +{{ $item->item->ac_bonus }} AC
                                    @endif
                                </div>
                                @if($item->is_equipped)
                                    <button class="item-button unequip" onclick="unequipPlayerItem({{ $item->id }})">
                                        Unequip
                                    </button>
                                @else
                                    <button class="item-button equip" onclick="equipPlayerItem({{ $item->id }})">
                                        Equip
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-inventory">
                        <div style="font-size: 2rem; margin-bottom: 10px;">🛡️</div>
                        <div>No armor in inventory</div>
                    </div>
                @endif
            </div>
            
            <!-- Accessories -->
            <div class="inventory-category" data-category="accessories" style="display: none;">
                @if($inventory['accessories']->count() > 0)
                    <div class="inventory-grid">
                        @foreach($inventory['accessories'] as $item)
                            <div class="inventory-item @if($item->is_equipped) equipped @endif">
                                <img src="{{ $item->item->getImagePath() }}" 
                                     alt="{{ $item->item->name }}" 
                                     class="item-image">
                                <div class="item-name rarity-{{ $item->item->rarity }}">
                                    {{ Str::limit($item->item->name, 12) }}
                                </div>
                                <div class="item-stats">
                                    @if($item->item->stats_modifiers)
                                        @foreach($item->item->stats_modifiers as $stat => $bonus)
                                            @if($bonus != 0)
                                                <div>{{ strtoupper($stat) }}: {{ $bonus > 0 ? '+' : '' }}{{ $bonus }}</div>
                                                @break
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                                @if($item->is_equipped)
                                    <button class="item-button unequip" onclick="unequipPlayerItem({{ $item->id }})">
                                        Unequip
                                    </button>
                                @else
                                    <button class="item-button equip" onclick="equipPlayerItem({{ $item->id }})">
                                        Equip
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-inventory">
                        <div style="font-size: 2rem; margin-bottom: 10px;">💍</div>
                        <div>No accessories in inventory</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Character Actions Panel - Bottom Left -->
    <div class="character-actions-panel">
        <div class="mb-2">
            <div class="fw-bold small">⚡ Character Actions</div>
        </div>
        
        @if($player->canLevelUp())
            <button class="character-btn success" onclick="levelUpPlayer()">
                🎉 Level Up Available!
            </button>
        @endif
        
        @if($player->hasUnallocatedStatPoints())
            <button class="character-btn warning" data-bs-toggle="modal" data-bs-target="#statAllocationModal">
                📊 Allocate Points ({{ $player->unallocated_stat_points }})
            </button>
        @endif
        
        @if($player->skill_points > 0)
            <a href="{{ route('game.skills') }}" class="character-btn primary">
                🎯 Skill Points ({{ $player->skill_points }})
            </a>
        @endif
        
        <hr style="border-color: rgba(0,0,0,0.2); margin: 10px 0;">
        
        <a href="{{ route('game.inventory') }}" class="character-btn primary">
            🎒 Full Inventory
        </a>
        
        <a href="{{ route('game.skills') }}" class="character-btn primary">
            🎯 Skills & Talents
        </a>
        
        <a href="{{ route('game.village') }}" class="character-btn success">
            🏘️ Back to Village
        </a>
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
            <a href="{{ route('game.inventory') }}" class="dashboard-btn warning">
                🎒 Inventory
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

<!-- Gender Change Modal -->
<div class="modal fade" id="genderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background: rgba(33, 37, 41, 0.95); backdrop-filter: blur(15px); border: 2px solid rgba(255, 255, 255, 0.3); color: white;">
            <div class="modal-header" style="border-color: rgba(255, 255, 255, 0.2);">
                <h5 class="modal-title">Change Gender</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="gender-option btn btn-outline-light w-100 p-3 {{ $player->gender === 'male' ? 'active' : '' }}" 
                             data-gender="male" onclick="selectGender('male')">
                            <img src="{{ asset('img/player_male.png') }}" alt="Male" style="width: 60px; height: 60px; object-fit: cover;" class="mb-2">
                            <div>Male</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="gender-option btn btn-outline-light w-100 p-3 {{ $player->gender === 'female' ? 'active' : '' }}" 
                             data-gender="female" onclick="selectGender('female')">
                            <img src="{{ asset('img/player_female.png') }}" alt="Female" style="width: 60px; height: 60px; object-fit: cover;" class="mb-2">
                            <div>Female</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-color: rgba(255, 255, 255, 0.2);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmGenderBtn" onclick="changeGender()" disabled>Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Stat Allocation Modal -->
<div class="modal fade" id="statAllocationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background: rgba(33, 37, 41, 0.95); backdrop-filter: blur(15px); border: 2px solid rgba(255, 255, 255, 0.3); color: white;">
            <div class="modal-header" style="border-color: rgba(255, 255, 255, 0.2);">
                <h5 class="modal-title">Allocate Stat Points</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong>Available Points:</strong> <span id="remaining-points">{{ $player->unallocated_stat_points }}</span>
                </div>
                
                <div class="row g-3">
                    @foreach(['str' => 'Strength', 'dex' => 'Dexterity', 'con' => 'Constitution', 'int' => 'Intelligence', 'wis' => 'Wisdom', 'cha' => 'Charisma'] as $stat => $name)
                        <div class="col-md-6">
                            <div class="stat-allocation-row p-3 border rounded" style="background: rgba(255, 255, 255, 0.1); border-color: rgba(255, 255, 255, 0.2) !important;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>{{ $name }}</strong>
                                    <span class="total-{{ $stat }}">{{ $player->getAttribute($stat) }}</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-stat-decrease" data-stat="{{ $stat }}">-</button>
                                    <input type="number" class="form-control form-control-sm mx-2 text-center" id="{{ $stat }}_points" value="0" readonly style="width: 60px; background: rgba(255, 255, 255, 0.1); border-color: rgba(255, 255, 255, 0.3); color: white;">
                                    <button type="button" class="btn btn-sm btn-outline-success btn-stat-increase" data-stat="{{ $stat }}">+</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer" style="border-color: rgba(255, 255, 255, 0.2);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="allocate-btn" onclick="submitStatAllocation()" disabled>Allocate Points</button>
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

// Stat allocation functionality
let allocatedPoints = {
    str: 0, dex: 0, con: 0, int: 0, wis: 0, cha: 0
};

document.addEventListener('DOMContentLoaded', function() {
    const maxPoints = {{ $player->unallocated_stat_points }};
    let remainingPoints = maxPoints;

    const baseStat = {
        str: {{ $player->str }},
        dex: {{ $player->dex }},
        con: {{ $player->con }},
        int: {{ $player->int }},
        wis: {{ $player->wis }},
        cha: {{ $player->cha }}
    };

    function updateDisplay() {
        const remainingElement = document.getElementById('remaining-points');
        if (remainingElement) {
            remainingElement.textContent = remainingPoints;
        }
        
        Object.keys(allocatedPoints).forEach(stat => {
            const input = document.getElementById(stat + '_points');
            const total = document.querySelector('.total-' + stat);
            
            if (input) input.value = allocatedPoints[stat];
            if (total) total.textContent = baseStat[stat] + allocatedPoints[stat];
        });

        const allocateBtn = document.getElementById('allocate-btn');
        if (allocateBtn) {
            const hasAllocated = Object.values(allocatedPoints).some(points => points > 0);
            allocateBtn.disabled = !hasAllocated;
        }

        document.querySelectorAll('.btn-stat-increase').forEach(btn => {
            btn.disabled = remainingPoints <= 0;
        });

        document.querySelectorAll('.btn-stat-decrease').forEach(btn => {
            const stat = btn.dataset.stat;
            btn.disabled = allocatedPoints[stat] <= 0;
        });
    }

    document.querySelectorAll('.btn-stat-increase').forEach(btn => {
        btn.addEventListener('click', function() {
            const stat = this.dataset.stat;
            if (remainingPoints > 0) {
                allocatedPoints[stat]++;
                remainingPoints--;
                updateDisplay();
            }
        });
    });

    document.querySelectorAll('.btn-stat-decrease').forEach(btn => {
        btn.addEventListener('click', function() {
            const stat = this.dataset.stat;
            if (allocatedPoints[stat] > 0) {
                allocatedPoints[stat]--;
                remainingPoints++;
                updateDisplay();
            }
        });
    });

    updateDisplay();
});

// AJAX stat allocation submission
function submitStatAllocation() {
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    Object.keys(allocatedPoints).forEach(stat => {
        formData.append(stat + '_points', allocatedPoints[stat]);
    });
    
    const allocateBtn = document.getElementById('allocate-btn');
    allocateBtn.disabled = true;
    allocateBtn.textContent = 'Allocating...';
    
    fetch('{{ route("game.allocate-stats") }}', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            sessionStorage.removeItem('stat_points_modal_shown_' + {{ auth()->id() }});
            bootstrap.Modal.getInstance(document.getElementById('statAllocationModal')).hide();
            setTimeout(() => location.reload(), 500);
        } else {
            console.error('Failed to allocate stats:', data.message);
            allocateBtn.disabled = false;
            allocateBtn.textContent = 'Allocate Points';
        }
    })
    .catch(error => {
        console.error('Error allocating stats:', error);
        allocateBtn.disabled = false;
        allocateBtn.textContent = 'Allocate Points';
    });
}

// Equipment functionality
function equipPlayerItem(itemId) {
    fetch(`{{ url('/game/player-item/equip') }}/${itemId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            console.error('Failed to equip item:', data.error);
        }
    })
    .catch(error => {
        console.error('Error equipping item:', error);
    });
}

function unequipPlayerItem(itemId) {
    fetch(`{{ url('/game/player-item/unequip') }}/${itemId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            console.error('Failed to unequip item:', data.error);
        }
    })
    .catch(error => {
        console.error('Error unequipping item:', error);
    });
}

function levelUpPlayer() {
    if (confirm('Level up your character? This will increase your level and grant stat points.')) {
        fetch('{{ route("game.level-up") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                sessionStorage.removeItem('stat_points_modal_shown_' + {{ auth()->id() }});
                setTimeout(() => location.reload(), 1000);
            } else {
                console.error('Failed to level up:', data.message);
            }
        })
        .catch(error => {
            console.error('Error leveling up:', error);
        });
    }
}

// Gender change functionality
let selectedGender = '{{ $player->gender }}';

function showGenderModal() {
    const modal = new bootstrap.Modal(document.getElementById('genderModal'));
    modal.show();
}

function selectGender(gender) {
    selectedGender = gender;
    
    document.querySelectorAll('.gender-option').forEach(btn => {
        btn.classList.remove('btn-light', 'active');
        btn.classList.add('btn-outline-light');
    });
    
    const selectedBtn = document.querySelector(`[data-gender="${gender}"]`);
    selectedBtn.classList.remove('btn-outline-light');
    selectedBtn.classList.add('btn-light', 'active');
    
    document.getElementById('confirmGenderBtn').disabled = false;
}

function changeGender() {
    if (!selectedGender) {
        alert('Please select a gender');
        return;
    }
    
    const confirmBtn = document.getElementById('confirmGenderBtn');
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Changing...';
    
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('gender', selectedGender);
    
    fetch('{{ route("game.change-gender") }}', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                console.error('Server returned non-JSON response:', text);
                throw new Error('Server returned HTML instead of JSON. Check server logs for errors.');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('genderModal')).hide();
            setTimeout(() => location.reload(), 500);
        } else {
            console.error('Failed to change gender:', data.message);
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Confirm';
        }
    })
    .catch(error => {
        console.error('Error changing gender:', error);
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Confirm';
    });
}
</script>
@endpush