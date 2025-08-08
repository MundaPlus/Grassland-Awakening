@extends('game.layout')

@section('title', 'Character Sheet')
@section('meta_description', 'Manage your character equipment and stats in Grassland Awakening - view abilities, allocate points, and equip gear.')

@push('styles')
@vite('resources/css/game/character.css')
@endpush

@section('content')
<div class="character-background"></div>
<div class="character-overlay"></div>

<!-- Character UI Overlay System -->
<div class="character-ui-container">
    <!-- Header Panel - Top Center -->
    <div class="character-header-panel">
        <div class="d-flex align-items-center justify-content-center gap-3 mb-2">
            <img src="{{ $player->getCharacterImagePath() }}" 
                 alt="{{ ucfirst($player->gender) }} Portrait" 
                 class="character-portrait">
            <div class="text-center">
                <h1 class="mb-1">👤 {{ $player->name }}</h1>
                <div class="small">Level {{ $player->level }} {{ ucfirst($player->gender) }}</div>
            </div>
        </div>
        
        <div class="experience-bar">
            <div class="experience-fill" style="width: {{ $player->level > 0 ? ($player->experience / ($player->level * 100)) * 100 : 0 }}%"></div>
        </div>
        
        <div class="header-stats">
            <div class="header-stat">
                <div class="header-stat-value text-success">{{ $player->hp }}/{{ $player->max_hp }}</div>
                <div class="header-stat-label">Health</div>
            </div>
            <div class="header-stat">
                <div class="header-stat-value text-warning">{{ number_format($player->experience) }}</div>
                <div class="header-stat-label">Experience</div>
            </div>
            <div class="header-stat">
                <div class="header-stat-value text-info">{{ $player->skill_points ?? 0 }}</div>
                <div class="header-stat-label">Skill Points</div>
            </div>
            <div class="header-stat">
                <div class="header-stat-value text-warning">{{ number_format($player->persistent_currency) }}</div>
                <div class="header-stat-label">Gold</div>
            </div>
            <div class="header-stat">
                <div class="header-stat-value text-primary">{{ $player->getTotalAC() }}</div>
                <div class="header-stat-label">Armor</div>
            </div>
        </div>
    </div>

    <!-- Character Model with Equipment Slots -->
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
                     data-item-name="{{ method_exists($equipment[$slot], 'getDisplayName') ? $equipment[$slot]->getDisplayName() : $equipment[$slot]->item->name }}"
                     data-item-rarity="{{ $equipment[$slot]->item->rarity }}"
                     data-item-type="{{ ucfirst(str_replace('_', ' ', $equipment[$slot]->item->type)) }}"
                     data-item-attack="{{ $equipment[$slot]->item->damage_bonus ?? 0 }}"
                     data-item-defense="{{ $equipment[$slot]->item->ac_bonus ?? 0 }}"
                     data-item-description="{{ $equipment[$slot]->item->description ?? '' }}"
                     title="Click to unequip"
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

    <!-- Stats Panel - Left Side -->
    <div class="character-stats-panel">
        <div class="mb-3">
            <h2 class="h6 mb-3">⚔️ Ability Scores</h2>
            @if($player->unallocated_stat_points > 0)
                <div class="alert alert-info mb-3" style="background: rgba(13, 110, 253, 0.1); border: 1px solid rgba(13, 110, 253, 0.3); color: #9fc5e8;">
                    <strong>{{ $player->unallocated_stat_points }}</strong> stat points available to allocate!
                </div>
            @endif
            <div class="ability-scores">
                @php
                    $stats = [
                        'str' => ['name' => 'Strength', 'value' => $player->str],
                        'dex' => ['name' => 'Dexterity', 'value' => $player->dex], 
                        'con' => ['name' => 'Constitution', 'value' => $player->con],
                        'int' => ['name' => 'Intelligence', 'value' => $player->int],
                        'wis' => ['name' => 'Wisdom', 'value' => $player->wis],
                        'cha' => ['name' => 'Charisma', 'value' => $player->cha]
                    ];
                @endphp
                
                @foreach($stats as $statKey => $stat)
                    <div class="ability-score">
                        <div class="ability-score-value" id="total-{{ $statKey }}">{{ $stat['value'] }}</div>
                        <div class="ability-score-label">{{ $stat['name'] }}</div>
                        @if($player->unallocated_stat_points > 0)
                            <div class="stat-allocation-controls mt-2">
                                <button type="button" class="btn btn-sm btn-outline-danger me-1" 
                                        onclick="adjustStat('{{ $statKey }}', -1)" 
                                        id="minus-{{ $statKey }}" disabled>-</button>
                                <span class="allocated-points" id="allocated-{{ $statKey }}">0</span>
                                <button type="button" class="btn btn-sm btn-outline-success ms-1" 
                                        onclick="adjustStat('{{ $statKey }}', 1)" 
                                        id="plus-{{ $statKey }}">+</button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            
            @if($player->unallocated_stat_points > 0)
                <div class="text-center mt-3">
                    <div class="mb-2">
                        <strong>Remaining Points:</strong> <span id="remaining-points" class="text-warning">{{ $player->unallocated_stat_points }}</span>
                    </div>
                    <form id="allocate-stats-form" method="POST" action="{{ route('game.allocate-stats') }}">
                        @csrf
                        @foreach($stats as $statKey => $stat)
                            <input type="hidden" name="{{ $statKey }}_points" id="{{ $statKey }}-points-input" value="0">
                        @endforeach
                        <button type="button" class="btn btn-success me-2" onclick="allocateStats()" id="allocate-btn" disabled>
                            🎯 Allocate Points
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="resetAllocation()">
                            🔄 Reset
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <div class="character-info">
            <h3 class="h6 mb-2">⚡ Equipment Bonuses</h3>
            @if(isset($equipmentBonuses))
                @if($equipmentBonuses['str'] != 0)
                <div class="info-row">
                    <span class="info-label">💪 Strength:</span>
                    <span class="info-value text-success">+{{ $equipmentBonuses['str'] }}</span>
                </div>
                @endif
                @if($equipmentBonuses['dex'] != 0)
                <div class="info-row">
                    <span class="info-label">🏃 Dexterity:</span>
                    <span class="info-value text-success">+{{ $equipmentBonuses['dex'] }}</span>
                </div>
                @endif
                @if($equipmentBonuses['con'] != 0)
                <div class="info-row">
                    <span class="info-label">🛡️ Constitution:</span>
                    <span class="info-value text-success">+{{ $equipmentBonuses['con'] }}</span>
                </div>
                @endif
                @if($equipmentBonuses['int'] != 0)
                <div class="info-row">
                    <span class="info-label">🧠 Intelligence:</span>
                    <span class="info-value text-success">+{{ $equipmentBonuses['int'] }}</span>
                </div>
                @endif
                @if($equipmentBonuses['wis'] != 0)
                <div class="info-row">
                    <span class="info-label">🦉 Wisdom:</span>
                    <span class="info-value text-success">+{{ $equipmentBonuses['wis'] }}</span>
                </div>
                @endif
                @if($equipmentBonuses['cha'] != 0)
                <div class="info-row">
                    <span class="info-label">💬 Charisma:</span>
                    <span class="info-value text-success">+{{ $equipmentBonuses['cha'] }}</span>
                </div>
                @endif
                @if($equipmentBonuses['weapon_damage'] != 0)
                <div class="info-row">
                    <span class="info-label">⚔️ Weapon Damage:</span>
                    <span class="info-value text-danger">+{{ $equipmentBonuses['weapon_damage'] }}</span>
                </div>
                @endif
                @if($equipmentBonuses['ac'] != 0)
                <div class="info-row">
                    <span class="info-label">🛡️ Armor Class:</span>
                    <span class="info-value text-primary">+{{ $equipmentBonuses['ac'] }}</span>
                </div>
                @endif
                @if(array_sum($equipmentBonuses) == 0)
                <div class="info-row">
                    <span class="info-label text-muted">No equipment bonuses</span>
                </div>
                @endif
            @else
                <div class="info-row">
                    <span class="info-label text-muted">No equipment bonuses</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Inventory Panel - Right Side -->
    <div class="equipment-panel">
        <div class="mb-2">
            <h2 class="h6 mb-2">🎒 Quick Inventory</h2>
        </div>
        
        <!-- Inventory Tabs -->
        <div class="inventory-tabs">
            <button class="inventory-tab active" data-category="all" onclick="switchInventoryTab('all', this)">
                🌟 All
            </button>
            <button class="inventory-tab" data-category="weapon" onclick="switchInventoryTab('weapon', this)">
                ⚔️ Weapons
            </button>
            <button class="inventory-tab" data-category="armor" onclick="switchInventoryTab('armor', this)">
                🛡️ Armor
            </button>
            <button class="inventory-tab" data-category="accessory" onclick="switchInventoryTab('accessory', this)">
                💎 Accessories
            </button>
        </div>
        
        <div class="equipment-list">
            @if(isset($inventoryItems) && count($inventoryItems) > 0)
                @php
                    $weaponItems = $inventoryItems->filter(fn($item) => $item->item->type === 'weapon');
                    $armorItems = $inventoryItems->filter(fn($item) => $item->item->type === 'armor');
                    $accessoryItems = $inventoryItems->filter(fn($item) => $item->item->type === 'accessory');
                @endphp
                
                <!-- All Items -->
                <div class="inventory-category active" data-category="all">
                    @foreach($inventoryItems as $playerItem)
                    <div class="equipment-item" onclick="equipFromCharacterPage({{ $playerItem->id }})" style="cursor: pointer;">
                        <img src="{{ $playerItem->item->getImagePath() }}" 
                             alt="{{ $playerItem->item->name }}"
                             class="equipment-item-icon">
                        <div class="equipment-item-info">
                            <div class="equipment-item-name {{ $playerItem->item->rarity }}">
                                {{ $playerItem->item->name }}
                            </div>
                            <div class="equipment-item-slot">{{ ucfirst(str_replace('_', ' ', $playerItem->item->type)) }}</div>
                            @php
                                $hasStats = $playerItem->item->damage_bonus || $playerItem->item->ac_bonus || 
                                           ($playerItem->item->stats_modifiers && count(array_filter($playerItem->item->stats_modifiers)));
                            @endphp
                            @if($hasStats)
                            <div class="equipment-item-stats">
                                @if($playerItem->item->damage_bonus)
                                    ⚔️ +{{ $playerItem->item->damage_bonus }} DMG
                                @endif
                                @if($playerItem->item->ac_bonus)
                                    🛡️ +{{ $playerItem->item->ac_bonus }} AC
                                @endif
                                @if($playerItem->item->stats_modifiers)
                                    @foreach($playerItem->item->stats_modifiers as $stat => $value)
                                        @if($value > 0)
                                            @php
                                                $statIcons = ['str' => '💪', 'dex' => '🏃', 'con' => '❤️', 'int' => '🧠', 'wis' => '🦉', 'cha' => '💬'];
                                                $displayValue = is_float($value) ? number_format($value, 1) : $value;
                                            @endphp
                                            {{ $statIcons[$stat] ?? '📊' }} +{{ $displayValue }} {{ strtoupper($stat) }}
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                            @endif
                            @if($playerItem->quantity > 1)
                            <div class="equipment-item-quantity">
                                <small class="text-muted">x{{ $playerItem->quantity }}</small>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Weapons -->
                <div class="inventory-category" data-category="weapon">
                    @foreach($weaponItems as $playerItem)
                    <div class="equipment-item" onclick="equipFromCharacterPage({{ $playerItem->id }})" style="cursor: pointer;">
                        <img src="{{ $playerItem->item->getImagePath() }}" 
                             alt="{{ $playerItem->item->name }}"
                             class="equipment-item-icon">
                        <div class="equipment-item-info">
                            <div class="equipment-item-name {{ $playerItem->item->rarity }}">
                                {{ $playerItem->item->name }}
                            </div>
                            <div class="equipment-item-slot">{{ ucfirst(str_replace('_', ' ', $playerItem->item->subtype ?? $playerItem->item->type)) }}</div>
                            @if($playerItem->item->damage_bonus)
                            <div class="equipment-item-stats">
                                ⚔️ +{{ $playerItem->item->damage_bonus }} DMG
                            </div>
                            @endif
                            @if($playerItem->quantity > 1)
                            <div class="equipment-item-quantity">
                                <small class="text-muted">x{{ $playerItem->quantity }}</small>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Armor -->
                <div class="inventory-category" data-category="armor">
                    @foreach($armorItems as $playerItem)
                    <div class="equipment-item" onclick="equipFromCharacterPage({{ $playerItem->id }})" style="cursor: pointer;">
                        <img src="{{ $playerItem->item->getImagePath() }}" 
                             alt="{{ $playerItem->item->name }}"
                             class="equipment-item-icon">
                        <div class="equipment-item-info">
                            <div class="equipment-item-name {{ $playerItem->item->rarity }}">
                                {{ $playerItem->item->name }}
                            </div>
                            <div class="equipment-item-slot">{{ ucfirst(str_replace('_', ' ', $playerItem->item->subtype ?? $playerItem->item->type)) }}</div>
                            @if($playerItem->item->ac_bonus)
                            <div class="equipment-item-stats">
                                🛡️ +{{ $playerItem->item->ac_bonus }} AC
                            </div>
                            @endif
                            @if($playerItem->quantity > 1)
                            <div class="equipment-item-quantity">
                                <small class="text-muted">x{{ $playerItem->quantity }}</small>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Accessories -->
                <div class="inventory-category" data-category="accessory">
                    @foreach($accessoryItems as $playerItem)
                    <div class="equipment-item" onclick="equipFromCharacterPage({{ $playerItem->id }})" style="cursor: pointer;">
                        <img src="{{ $playerItem->item->getImagePath() }}" 
                             alt="{{ $playerItem->item->name }}"
                             class="equipment-item-icon">
                        <div class="equipment-item-info">
                            <div class="equipment-item-name {{ $playerItem->item->rarity }}">
                                {{ $playerItem->item->name }}
                            </div>
                            <div class="equipment-item-slot">{{ ucfirst(str_replace('_', ' ', $playerItem->item->subtype ?? $playerItem->item->type)) }}</div>
                            @php
                                $hasStats = $playerItem->item->damage_bonus || $playerItem->item->ac_bonus || 
                                           ($playerItem->item->stats_modifiers && count(array_filter($playerItem->item->stats_modifiers)));
                            @endphp
                            @if($hasStats)
                            <div class="equipment-item-stats">
                                @if($playerItem->item->damage_bonus)
                                    ⚔️ +{{ $playerItem->item->damage_bonus }} DMG
                                @endif
                                @if($playerItem->item->ac_bonus)
                                    🛡️ +{{ $playerItem->item->ac_bonus }} AC
                                @endif
                                @if($playerItem->item->stats_modifiers)
                                    @foreach($playerItem->item->stats_modifiers as $stat => $value)
                                        @if($value > 0)
                                            @php
                                                $statIcons = ['str' => '💪', 'dex' => '🏃', 'con' => '❤️', 'int' => '🧠', 'wis' => '🦉', 'cha' => '💬'];
                                                $displayValue = is_float($value) ? number_format($value, 1) : $value;
                                            @endphp
                                            {{ $statIcons[$stat] ?? '📊' }} +{{ $displayValue }} {{ strtoupper($stat) }}
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                            @endif
                            @if($playerItem->quantity > 1)
                            <div class="equipment-item-quantity">
                                <small class="text-muted">x{{ $playerItem->quantity }}</small>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                
            @else
                <div class="text-center py-3">
                    <div class="mb-2" style="font-size: 2rem; opacity: 0.5;">📦</div>
                    <div class="small">No items in inventory</div>
                    <div class="small opacity-75">Find items in adventures!</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Actions Panel - Bottom Right -->
    <div class="character-actions-panel">
        <div class="mb-2">
            <div class="fw-bold small">⚡ Character Actions</div>
        </div>

        <a href="{{ route('game.inventory') }}" class="character-btn warning">
            🎒 Manage Equipment
        </a>

        <a href="{{ route('game.skills') }}" class="character-btn primary">
            🎯 Manage Skills
        </a>

        <hr style="border-color: rgba(255,255,255,0.2); margin: 10px 0;">

        <button class="character-btn success" onclick="showChangeGenderModal()">
            🔄 Change Gender
        </button>

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
            <a href="{{ route('game.dashboard') }}" class="dashboard-btn primary">
                🏠 Dashboard
            </a>
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
<div class="modal fade" id="genderChangeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">Change Character Gender</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Choose your character's new gender:</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-light" onclick="changeGender('male')">
                        👨 Male
                    </button>
                    <button class="btn btn-outline-light" onclick="changeGender('female')">
                        👩 Female
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/game/character.js')
<script>
// Initialize character page with server data
document.addEventListener('DOMContentLoaded', function() {
    const playerData = {
        maxPoints: {{ $player->unallocated_stat_points }},
        baseStat: {
            str: {{ $player->str }},
            dex: {{ $player->dex }},
            con: {{ $player->con }},
            int: {{ $player->int }},
            wis: {{ $player->wis }},
            cha: {{ $player->cha }}
        }
    };
    
    initializeCharacterPage(playerData);
});
</script>
@endpush

