@extends('game.layout')

@section('title', 'Character Sheet')

@section('content')
<div class="container-fluid">
    <!-- Compact Character Header -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-body py-2">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <div class="character-portrait position-relative">
                                <img src="{{ $player->getCharacterImagePath() }}" 
                                     alt="{{ ucfirst($player->gender) }} Character" 
                                     class="character-avatar rounded"
                                     style="width: 50px; height: 70px; object-fit: cover;">
                                <button class="btn btn-sm btn-outline-secondary position-absolute bottom-0 end-0 p-1" 
                                        onclick="showGenderModal()" title="Change Gender" style="font-size: 0.7rem;">
                                    ⚧
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h1 class="h6 mb-1">{{ $player->character_name }} - Level {{ $player->level }} {{ ucfirst($player->gender) }}</h1>
                            <div class="row text-center g-1">
                                <div class="col">
                                    <div class="fw-bold text-success small">{{ $player->hp }}/{{ $player->max_hp }}</div>
                                    <small class="text-muted" style="font-size: 0.7rem;">HP</small>
                                </div>
                                <div class="col">
                                    <div class="fw-bold text-primary small">{{ $totalAC }}</div>
                                    <small class="text-muted" style="font-size: 0.7rem;">AC</small>
                                </div>
                                <div class="col">
                                    <div class="fw-bold text-danger small">{{ $maxDamage }}</div>
                                    <small class="text-muted" style="font-size: 0.7rem;">DMG</small>
                                </div>
                                <div class="col">
                                    <div class="fw-bold text-warning small">{{ number_format($player->persistent_currency) }}</div>
                                    <small class="text-muted" style="font-size: 0.7rem;">Gold</small>
                                </div>
                                <div class="col">
                                    @if($player->hasUnallocatedStatPoints())
                                        <div class="fw-bold text-danger small">{{ $player->unallocated_stat_points }}</div>
                                        <small class="text-muted" style="font-size: 0.7rem;">Stats</small>
                                    @else
                                        <div class="fw-bold text-info small">{{ $player->level }}</div>
                                        <small class="text-muted" style="font-size: 0.7rem;">Level</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="progress mb-1" style="height: 8px;">
                                @php
                                    $expToNext = $player->calculateExperienceToNextLevel();
                                    $expProgress = $expToNext > 0 ? ($player->experience / $expToNext) * 100 : 100;
                                @endphp
                                <div class="progress-bar bg-info" style="width: {{ $expProgress }}%"></div>
                            </div>
                            <small class="text-muted">{{ $player->experience }}/{{ $expToNext }} XP</small>
                            <div class="text-end">
                                @if($player->canLevelUp())
                                    <button class="btn btn-success btn-sm me-1" onclick="levelUpPlayer()">
                                        🎉 Level Up!
                                    </button>
                                @endif
                                @if($player->hasUnallocatedStatPoints())
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#statAllocationModal">
                                        Allocate ({{ $player->unallocated_stat_points }})
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Character Layout -->
    <div class="row" style="height: calc(70vh - 100px);">
        <!-- Left Panel: Character with Equipment Slots on Border -->
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header py-1">
                    <h6 class="mb-0">Equipment</h6>
                </div>
                <div class="card-body p-1 position-relative" style="background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);">
                    <div class="equipment-panel-container position-relative h-100">
                        <!-- Character Image Centered -->
                        <div class="character-display d-flex align-items-center justify-content-center h-100">
                            <img src="{{ $player->getCharacterImagePath() }}" 
                                 alt="{{ ucfirst($player->gender) }} Character" 
                                 class="character-model"
                                 style="width: 200px; height: 355px; object-fit: contain; filter: drop-shadow(0 0 15px rgba(0,0,0,0.5));">
                        </div>
                        
                        <!-- Equipment Slots on Panel Borders -->
                        @php
                            // Equipment slots positioned on panel borders - reorganized layout
                            $borderSlots = [
                                // Left border - head, chest, gloves, pants, feet (evenly spaced)
                                'helm' => ['top: 5%; left: 10px;', 'fas fa-hard-hat', 'Helmet'],
                                'chest' => ['top: 25%; left: 10px;', 'fas fa-vest', 'Chest Armor'],
                                'gloves' => ['top: 45%; left: 10px;', 'fas fa-mitten', 'Gloves'],
                                'pants' => ['top: 65%; left: 10px;', 'fas fa-socks', 'Pants'],
                                'boots' => ['top: 85%; left: 10px;', 'fas fa-shoe-prints', 'Boots'],
                                
                                // Right border - neck, rings, artifacts (evenly spaced)
                                'neck' => ['top: 5%; right: 10px;', 'fas fa-gem', 'Necklace'],
                                'ring_1' => ['top: 25%; right: 10px;', 'fas fa-ring', 'Ring 1'],
                                'ring_2' => ['top: 45%; right: 10px;', 'fas fa-ring', 'Ring 2'],
                                'artifact_1' => ['top: 65%; right: 10px;', 'fas fa-magic', 'Artifact 1'],
                                'artifact_2' => ['top: 85%; right: 10px;', 'fas fa-magic', 'Artifact 2'],
                            ];
                            
                            // Weapon slots - larger and positioned under character
                            $weaponSlots = [
                                'weapon_1' => ['bottom: 80px; left: 20%;', 'fas fa-sword', 'Main Hand'],
                                'weapon_2' => ['bottom: 80px; right: 20%;', 'fas fa-dagger', 'Off Hand'],
                            ];
                        @endphp
                        
                        <!-- Regular equipment slots -->
                        @foreach($borderSlots as $slot => $config)
                            <div class="equipment-slot-border" 
                                 data-slot="{{ $slot }}" 
                                 style="position: absolute; {{ $config[0] }} width: 90px; height: 90px; z-index: 10;">
                                @if(isset($equipment[$slot]) && $equipment[$slot])
                                    <div class="equipment-item-border {{ $equipment[$slot]->item->getRarityColor() }} equipment-clickable" 
                                         data-bs-toggle="tooltip" 
                                         data-bs-html="true" 
                                         data-bs-placement="top"
                                         data-item-name="{{ method_exists($equipment[$slot], 'getDisplayName') ? $equipment[$slot]->getDisplayName() : $equipment[$slot]->item->name }}"
                                         data-item-rarity="{{ $equipment[$slot]->item->rarity }}"
                                         data-item-stats="{{ json_encode($equipment[$slot]->item->stats_modifiers ?? []) }}"
                                         data-item-ac="{{ $equipment[$slot]->item->ac_bonus ?? 0 }}"
                                         data-item-damage="{{ $equipment[$slot]->item->damage_dice ?? '' }}"
                                         data-item-damage-bonus="{{ $equipment[$slot]->item->damage_bonus ?? 0 }}"
                                         onclick="unequipPlayerItem({{ $equipment[$slot]->id }})"
                                         title="Click to unequip">
                                        <img src="{{ $equipment[$slot]->item->getImagePath() }}" 
                                             alt="{{ $equipment[$slot]->item->name }}"
                                             style="width: 70px; height: 70px; object-fit: contain; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));">
                                    </div>
                                @else
                                    <div class="empty-equipment-slot-border" title="{{ $config[2] }}">
                                        <i class="{{ $config[1] }}"></i>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                        
                        <!-- Weapon slots (larger) -->
                        @foreach($weaponSlots as $slot => $config)
                            <div class="equipment-slot-border weapon-slot" 
                                 data-slot="{{ $slot }}" 
                                 style="position: absolute; {{ $config[0] }} width: 180px; height: 180px; z-index: 10;">
                                @if(isset($equipment[$slot]) && $equipment[$slot])
                                    <div class="equipment-item-border weapon-item {{ $equipment[$slot]->item->getRarityColor() }} equipment-clickable" 
                                         data-bs-toggle="tooltip" 
                                         data-bs-html="true" 
                                         data-bs-placement="top"
                                         data-item-name="{{ method_exists($equipment[$slot], 'getDisplayName') ? $equipment[$slot]->getDisplayName() : $equipment[$slot]->item->name }}"
                                         data-item-rarity="{{ $equipment[$slot]->item->rarity }}"
                                         data-item-stats="{{ json_encode($equipment[$slot]->item->stats_modifiers ?? []) }}"
                                         data-item-ac="{{ $equipment[$slot]->item->ac_bonus ?? 0 }}"
                                         data-item-damage="{{ $equipment[$slot]->item->damage_dice ?? '' }}"
                                         data-item-damage-bonus="{{ $equipment[$slot]->item->damage_bonus ?? 0 }}"
                                         onclick="unequipPlayerItem({{ $equipment[$slot]->id }})"
                                         title="Click to unequip">
                                        <img src="{{ $equipment[$slot]->item->getImagePath() }}" 
                                             alt="{{ $equipment[$slot]->item->name }}"
                                             style="width: 140px; height: 140px; object-fit: contain; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.4));">
                                    </div>
                                @else
                                    <div class="empty-equipment-slot-border weapon-empty" title="{{ $config[2] }}">
                                        <i class="{{ $config[1] }}"></i>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Panel: Stats and Inventory Only -->
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header py-1">
                    <ul class="nav nav-tabs card-header-tabs" id="characterTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active small" id="stats-tab" data-bs-toggle="tab" data-bs-target="#stats" type="button" role="tab">
                                Stats & Skills
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link small" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab">
                                Inventory
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-2">
                    <div class="tab-content" id="characterTabContent" style="height: calc(100% - 10px); overflow: visible;">
                        <!-- Stats & Skills Tab -->
                        <div class="tab-pane fade show active" id="stats" role="tabpanel">
                            <!-- Ability Scores -->
                            <div class="mb-3">
                                <h6 class="text-muted mb-2 small">Ability Scores</h6>
                                <div class="row g-2">
                                    @foreach(['str' => 'STR', 'dex' => 'DEX', 'con' => 'CON', 'int' => 'INT', 'wis' => 'WIS', 'cha' => 'CHA'] as $stat => $name)
                                        <div class="col-4">
                                            <div class="stat-card text-center p-2 border rounded">
                                                <div class="fw-bold small">{{ $name }}</div>
                                                <div class="stat-value small">
                                                    {{ $player->getAttribute($stat) }}
                                                    @if($equipmentBonuses[$stat] != 0)
                                                        <span class="text-{{ $equipmentBonuses[$stat] > 0 ? 'success' : 'danger' }}" style="font-size: 0.7rem;">
                                                            {{ $equipmentBonuses[$stat] > 0 ? '+' : '' }}{{ $equipmentBonuses[$stat] }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="badge bg-secondary" style="font-size: 0.7rem;">
                                                    {{ floor(($totalStats[$stat] - 10) / 2) > 0 ? '+' : '' }}{{ floor(($totalStats[$stat] - 10) / 2) }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            
                            <!-- Equipment Summary -->
                            <div class="mb-3">
                                <h6 class="text-muted mb-2 small">Equipment Bonuses</h6>
                                <div class="row g-1">
                                    @php $hasAnyBonus = false; @endphp
                                    @foreach($equipmentBonuses as $stat => $bonus)
                                        @if($bonus != 0)
                                            @php $hasAnyBonus = true; @endphp
                                            <div class="col-6">
                                                <span class="text-{{ $bonus > 0 ? 'success' : 'danger' }}" style="font-size: 0.8rem;">
                                                    @if($stat === 'weapon_damage')
                                                        WEAPON DMG: {{ $bonus > 0 ? '+' : '' }}{{ $bonus }}
                                                    @elseif($stat === 'ac')
                                                        AC: {{ $bonus > 0 ? '+' : '' }}{{ $bonus }}
                                                    @else
                                                        {{ strtoupper($stat) }}: {{ $bonus > 0 ? '+' : '' }}{{ $bonus }}
                                                    @endif
                                                </span>
                                            </div>
                                        @endif
                                    @endforeach
                                    @if(!$hasAnyBonus)
                                        <div class="col-12">
                                            <span class="text-muted small">No equipment bonuses</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Skills Section (Future Development) -->
                            <div class="mb-3">
                                <h6 class="text-muted mb-2 small">Skills</h6>
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-tools fa-2x mb-2"></i>
                                    <div>Skills system coming soon...</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Inventory Tab -->
                        <div class="tab-pane fade" id="inventory" role="tabpanel">
                            <div class="inventory-section">
                                <ul class="nav nav-pills nav-fill mb-2" id="inventory-subtabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" style="font-size: 0.8rem;" id="weapons-subtab" data-bs-toggle="pill" data-bs-target="#weapons" type="button" role="tab">
                                            Weapons <span class="badge bg-secondary ms-1">{{ $inventory['weapons']->count() }}</span>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" style="font-size: 0.8rem;" id="armor-subtab" data-bs-toggle="pill" data-bs-target="#armor" type="button" role="tab">
                                            Armor <span class="badge bg-secondary ms-1">{{ $inventory['armor']->count() }}</span>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" style="font-size: 0.8rem;" id="accessories-subtab" data-bs-toggle="pill" data-bs-target="#accessories" type="button" role="tab">
                                            Accessories <span class="badge bg-secondary ms-1">{{ $inventory['accessories']->count() }}</span>
                                        </button>
                                    </li>
                                </ul>
                                
                                <div class="tab-content" id="inventory-subcontent" style="max-height: 500px; overflow-y: auto;">
                                    @foreach(['weapons', 'armor', 'accessories'] as $category)
                                        <div class="tab-pane fade {{ $category === 'weapons' ? 'show active' : '' }}" id="{{ $category }}" role="tabpanel">
                                            @if($inventory[$category]->count() > 0)
                                                <div class="row g-2">
                                                    @foreach($inventory[$category] as $playerItem)
                                                        <div class="col-4">
                                                            <div class="inventory-item-card p-2 border rounded {{ $playerItem->item->getRarityColor() }} h-100" data-item-id="{{ $playerItem->id }}">
                                                                <div class="text-center mb-2">
                                                                    <img src="{{ $playerItem->item->getImagePath() }}" 
                                                                         alt="{{ $playerItem->item->name }}" 
                                                                         style="width: 40px; height: 40px; object-fit: contain;">
                                                                </div>
                                                                <div class="text-center">
                                                                    <div class="fw-bold" style="font-size: 0.75rem; line-height: 1.1;">{{ Str::limit($playerItem->item->name, 15) }}</div>
                                                                    <small class="text-muted d-block" style="font-size: 0.7rem;">{{ ucfirst($playerItem->item->rarity) }}</small>
                                                                    
                                                                    @if($playerItem->item->stats_modifiers)
                                                                        <div style="font-size: 0.65rem; margin: 2px 0;">
                                                                            @foreach($playerItem->item->stats_modifiers as $stat => $bonus)
                                                                                @if($bonus != 0)
                                                                                    <div class="text-truncate">{{ strtoupper($stat) }}: {{ $bonus > 0 ? '+' : '' }}{{ $bonus }}</div>
                                                                                @endif
                                                                            @endforeach
                                                                        </div>
                                                                    @endif
                                                                    @if($playerItem->item->damage_dice)
                                                                        <div style="font-size: 0.65rem;" class="text-truncate">DMG: {{ $playerItem->item->damage_dice }}@if($playerItem->item->damage_bonus > 0)+{{ $playerItem->item->damage_bonus }}@endif</div>
                                                                    @endif
                                                                    @if($playerItem->item->ac_bonus)
                                                                        <div style="font-size: 0.65rem;">AC: +{{ $playerItem->item->ac_bonus }}</div>
                                                                    @endif
                                                                    
                                                                    <div class="mt-2">
                                                                        @if($playerItem->canEquip())
                                                                            <button class="btn btn-xs btn-success equip-item-btn w-100" data-item-id="{{ $playerItem->id }}" style="font-size: 0.7rem; padding: 2px 4px;">
                                                                                Equip
                                                                            </button>
                                                                        @elseif($playerItem->canUnequip())
                                                                            <button class="btn btn-xs btn-warning unequip-item-btn w-100" data-item-id="{{ $playerItem->id }}" style="font-size: 0.7rem; padding: 2px 4px;">
                                                                                Unequip
                                                                            </button>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-center text-muted py-3">
                                                    <i class="fas fa-box-open fa-2x mb-2"></i>
                                                    <div>No {{ $category }} in inventory</div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gender Change Modal -->
<div class="modal fade" id="genderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Gender</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="gender-option btn btn-outline-primary w-100 p-3 {{ $player->gender === 'male' ? 'active' : '' }}" 
                             data-gender="male" onclick="selectGender('male')">
                            <img src="{{ asset('img/player_male.png') }}" alt="Male" style="width: 60px; height: 60px; object-fit: cover;" class="mb-2">
                            <div>Male</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="gender-option btn btn-outline-primary w-100 p-3 {{ $player->gender === 'female' ? 'active' : '' }}" 
                             data-gender="female" onclick="selectGender('female')">
                            <img src="{{ asset('img/player_female.png') }}" alt="Female" style="width: 60px; height: 60px; object-fit: cover;" class="mb-2">
                            <div>Female</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmGenderBtn" onclick="changeGender()" disabled>Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Stat Allocation Modal -->
<div class="modal fade" id="statAllocationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Allocate Stat Points</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong>Available Points:</strong> <span id="remaining-points">{{ $player->unallocated_stat_points }}</span>
                </div>
                
                <div class="row g-3">
                    @foreach(['str' => 'Strength', 'dex' => 'Dexterity', 'con' => 'Constitution', 'int' => 'Intelligence', 'wis' => 'Wisdom', 'cha' => 'Charisma'] as $stat => $name)
                        <div class="col-md-6">
                            <div class="stat-allocation-row p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>{{ $name }}</strong>
                                    <span class="total-{{ $stat }}">{{ $player->getAttribute($stat) }}</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-stat-decrease" data-stat="{{ $stat }}">-</button>
                                    <input type="number" class="form-control form-control-sm mx-2 text-center" id="{{ $stat }}_points" value="0" readonly style="width: 60px;">
                                    <button type="button" class="btn btn-sm btn-outline-success btn-stat-increase" data-stat="{{ $stat }}">+</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="allocate-btn" onclick="submitStatAllocation()" disabled>Allocate Points</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Compact Layout Styles */
.equipment-panel-container {
    min-height: 600px;
}

.equipment-slot-border {
    cursor: pointer;
    transition: all 0.2s ease;
}

.equipment-slot-border:hover {
    transform: scale(1.1) !important;
    z-index: 20 !important;
}

.equipment-item-border {
    width: 90px;
    height: 90px;
    background: rgba(40, 167, 69, 0.9);
    border: 3px solid #28a745;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.4);
    transition: all 0.2s ease;
}

.equipment-item-border:hover {
    box-shadow: 0 6px 16px rgba(0,0,0,0.6);
    transform: translateY(-2px);
}

.equipment-clickable {
    cursor: pointer;
}

.equipment-clickable:hover {
    filter: brightness(1.1);
    transform: translateY(-3px);
}

.equipment-item-border.common { border-color: #6c757d; background: rgba(108, 117, 125, 0.9); }
.equipment-item-border.uncommon { border-color: #28a745; background: rgba(40, 167, 69, 0.9); }
.equipment-item-border.rare { border-color: #007bff; background: rgba(0, 123, 255, 0.9); }
.equipment-item-border.epic { border-color: #6f42c1; background: rgba(111, 66, 193, 0.9); }
.equipment-item-border.legendary { border-color: #fd7e14; background: rgba(253, 126, 20, 0.9); }

.empty-equipment-slot-border {
    width: 90px;
    height: 90px;
    background: rgba(255, 255, 255, 0.15);
    border: 3px dashed rgba(255, 255, 255, 0.6);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255, 255, 255, 0.8);
    transition: all 0.2s ease;
    backdrop-filter: blur(2px);
}

.empty-equipment-slot-border:hover {
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.9);
    color: rgba(255, 255, 255, 1);
}

.equipment-item-border i, .empty-equipment-slot-border i {
    font-size: 2.2rem;
    color: white;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.7);
}

.empty-equipment-slot-border i {
    color: rgba(255, 255, 255, 0.8);
    text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
}

/* Weapon slot specific styles */
.weapon-item {
    width: 180px;
    height: 180px;
    border-radius: 18px;
    border-width: 4px;
}

.weapon-empty {
    width: 180px;
    height: 180px;
    border-radius: 18px;
    border-width: 4px;
}

.weapon-item i, .weapon-empty i {
    font-size: 4.4rem;
}

.stat-card {
    background: rgba(248, 249, 250, 0.8);
    transition: all 0.2s ease;
    min-height: 70px;
}

.stat-card:hover {
    background: rgba(248, 249, 250, 1);
    transform: translateY(-1px);
}

.inventory-item {
    transition: all 0.2s ease;
}

.inventory-item:hover {
    transform: translateX(2px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.inventory-item-card {
    transition: all 0.2s ease;
    min-height: 120px;
    cursor: pointer;
}

.inventory-item-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.character-avatar {
    object-position: top center;
}

/* Dark mode support */
.dark .stat-card {
    background: var(--bg-accent);
    border-color: var(--border-color);
    color: var(--text-high-contrast);
}

.dark .stat-card:hover {
    background: var(--bg-secondary);
    border-color: var(--border-color);
}

.dark .empty-equipment-slot-border {
    background: rgba(31, 41, 55, 0.3);
    border-color: var(--border-color);
    color: var(--text-medium-contrast);
}

.dark .empty-equipment-slot-border:hover {
    background: rgba(31, 41, 55, 0.5);
    border-color: var(--text-medium-contrast);
    color: var(--text-high-contrast);
}
</style>

<script>
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
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.equip-item-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            equipPlayerItem(itemId);
        });
    });

    document.querySelectorAll('.unequip-item-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            unequipPlayerItem(itemId);
        });
    });
    
    // Initialize Bootstrap tooltips with custom formatting for equipment items
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        // Check if this is an equipment item with custom data
        if (tooltipTriggerEl.hasAttribute('data-item-name')) {
            const itemName = tooltipTriggerEl.getAttribute('data-item-name');
            const itemRarity = tooltipTriggerEl.getAttribute('data-item-rarity');
            const itemStats = JSON.parse(tooltipTriggerEl.getAttribute('data-item-stats') || '{}');
            const itemAC = parseInt(tooltipTriggerEl.getAttribute('data-item-ac') || '0');
            const itemDamage = tooltipTriggerEl.getAttribute('data-item-damage') || '';
            const itemDamageBonus = parseInt(tooltipTriggerEl.getAttribute('data-item-damage-bonus') || '0');
            
            let tooltipContent = `<div class="text-center">
                <div class="fw-bold text-${getRarityColor(itemRarity)}">${itemName}</div>
                <div class="text-muted small">${itemRarity.charAt(0).toUpperCase() + itemRarity.slice(1)}</div>`;
            
            // Add damage info for weapons
            if (itemDamage) {
                tooltipContent += '<hr class="my-1"><div class="text-start">';
                tooltipContent += `<div class="text-primary">🗡️ Damage: ${itemDamage}`;
                if (itemDamageBonus > 0) {
                    tooltipContent += `+${itemDamageBonus}`;
                }
                tooltipContent += '</div>';
            }
            
            // Add AC info for armor
            if (itemAC > 0) {
                if (!itemDamage) tooltipContent += '<hr class="my-1"><div class="text-start">';
                tooltipContent += `<div class="text-success">🛡️ AC: +${itemAC}</div>`;
            }
            
            // Add stat modifiers
            if (Object.keys(itemStats).length > 0) {
                if (!itemDamage && itemAC === 0) tooltipContent += '<hr class="my-1"><div class="text-start">';
                Object.keys(itemStats).forEach(stat => {
                    if (itemStats[stat] !== 0) {
                        const bonus = itemStats[stat];
                        const color = bonus > 0 ? 'success' : 'danger';
                        tooltipContent += `<div class="text-${color}">${stat.toUpperCase()}: ${bonus > 0 ? '+' : ''}${bonus}</div>`;
                    }
                });
            }
            
            if (itemDamage || itemAC > 0 || Object.keys(itemStats).length > 0) {
                tooltipContent += '</div>';
            }
            
            tooltipContent += '</div>';
            
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                title: tooltipContent
            });
        } else {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        }
    });
    
    function getRarityColor(rarity) {
        const rarityColors = {
            'common': 'secondary',
            'uncommon': 'success', 
            'rare': 'primary',
            'epic': 'info',
            'legendary': 'warning'
        };
        return rarityColors[rarity] || 'secondary';
    }
});

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
        btn.classList.remove('btn-primary', 'active');
        btn.classList.add('btn-outline-primary');
    });
    
    const selectedBtn = document.querySelector(`[data-gender="${gender}"]`);
    selectedBtn.classList.remove('btn-outline-primary');
    selectedBtn.classList.add('btn-primary', 'active');
    
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
@endsection