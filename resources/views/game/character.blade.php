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
    
    /* Equipment slot positioning - LEFT SIDE: GEAR */
    .slot-helm { top: 10px; left: -120px; }
    .slot-chest { top: 110px; left: -120px; }
    .slot-gloves { top: 210px; left: -120px; }
    .slot-pants { top: 310px; left: -120px; }
    .slot-boots { top: 410px; left: -120px; }
    
    /* Equipment slot positioning - RIGHT SIDE: ACCESSORIES */
    .slot-neck { top: 10px; right: -120px; }
    .slot-ring_1 { top: 110px; right: -120px; }
    .slot-ring_2 { top: 210px; right: -120px; }
    .slot-artifact_1 { top: 310px; right: -120px; }
    .slot-artifact_2 { top: 410px; right: -120px; }
    
    /* Equipment slot positioning - BOTTOM: WEAPONS (2x size) */
    .slot-weapon_1 { bottom: -60px; left: 25%; transform: translateX(-50%); width: 120px; height: 120px; }
    .slot-weapon_2 { bottom: -60px; right: 25%; transform: translateX(50%); width: 120px; height: 120px; }
    
    /* Weapon slots get larger icons */
    .slot-weapon_1 .slot-icon,
    .slot-weapon_2 .slot-icon {
        font-size: 3rem;
    }
    
    .slot-weapon_1 .slot-item-image,
    .slot-weapon_2 .slot-item-image {
        width: 100px;
        height: 100px;
        object-fit: contain;
    }
    
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
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .ability-score-value {
        font-size: 1.2em;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .ability-score-label {
        font-size: 0.8em;
        opacity: 0.9;
    }
    
    .character-info {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 15px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    
    .info-row:last-child {
        margin-bottom: 0;
    }
    
    .info-label {
        font-size: 0.9em;
        opacity: 0.8;
    }
    
    .info-value {
        font-weight: bold;
    }
    
    /* Equipment Panel - Right Side */
    .equipment-panel {
        position: absolute;
        top: 120px;
        right: 20px;
        width: 300px;
        background: rgba(220, 53, 69, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 20px;
        color: white;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        max-height: calc(100vh - 200px);
        overflow-y: auto;
    }
    
    .equipment-list {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .equipment-item {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .equipment-item-icon {
        width: 40px;
        height: 40px;
        object-fit: contain;
        border-radius: 6px;
    }
    
    .equipment-item-info {
        flex: 1;
    }
    
    .equipment-item-name {
        font-weight: bold;
        font-size: 0.9em;
    }
    
    .equipment-item-slot {
        font-size: 0.8em;
        opacity: 0.8;
        text-transform: capitalize;
    }
    
    .equipment-item-stats {
        font-size: 0.7em;
        opacity: 0.7;
        margin-top: 3px;
    }
    
    /* Actions Panel - Bottom Right */
    .character-actions-panel {
        position: absolute;
        bottom: 100px;
        right: 20px;
        width: 300px;
        background: rgba(23, 162, 184, 0.9);
        backdrop-filter: blur(15px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 15px;
        color: white;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }
    
    .character-btn {
        background: linear-gradient(135deg, #495057, #6c757d);
        border: none;
        color: white;
        padding: 10px 15px;
        margin: 5px 0;
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
    
    /* Responsive Design */
    @media (max-width: 1200px) {
        .character-stats-panel, .equipment-panel, .character-actions-panel {
            width: 250px;
        }
    }
    
    @media (max-width: 768px) {
        .character-stats-panel, .equipment-panel, .character-actions-panel {
            display: none;
        }
        
        .character-model-panel {
            width: 90%;
            height: 400px;
        }
        
        .character-header-panel {
            left: 10px;
            right: 10px;
            transform: none;
            min-width: auto;
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
                <div class="header-stat-value text-success">{{ $player->current_health }}/{{ $player->health }}</div>
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
                <div class="header-stat-value text-warning">{{ number_format($player->gold) }}</div>
                <div class="header-stat-label">Gold</div>
            </div>
            <div class="header-stat">
                <div class="header-stat-value text-primary">{{ $player->armor ?? 0 }}</div>
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

    <!-- Stats Panel - Left Side -->
    <div class="character-stats-panel">
        <div class="mb-3">
            <h2 class="h6 mb-3">⚔️ Ability Scores</h2>
            <div class="ability-scores">
                <div class="ability-score">
                    <div class="ability-score-value">{{ $player->str }}</div>
                    <div class="ability-score-label">Strength</div>
                </div>
                <div class="ability-score">
                    <div class="ability-score-value">{{ $player->dex }}</div>
                    <div class="ability-score-label">Dexterity</div>
                </div>
                <div class="ability-score">
                    <div class="ability-score-value">{{ $player->con }}</div>
                    <div class="ability-score-label">Constitution</div>
                </div>
                <div class="ability-score">
                    <div class="ability-score-value">{{ $player->int }}</div>
                    <div class="ability-score-label">Intelligence</div>
                </div>
                <div class="ability-score">
                    <div class="ability-score-value">{{ $player->wis }}</div>
                    <div class="ability-score-label">Wisdom</div>
                </div>
                <div class="ability-score">
                    <div class="ability-score-value">{{ $player->cha }}</div>
                    <div class="ability-score-label">Charisma</div>
                </div>
            </div>
        </div>

        <div class="character-info">
            <h3 class="h6 mb-2">📊 Character Info</h3>
            <div class="info-row">
                <span class="info-label">Class:</span>
                <span class="info-value">{{ ucfirst($player->character_class ?? 'Adventurer') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Background:</span>
                <span class="info-value">{{ ucfirst($player->background ?? 'Common Folk') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Gender:</span>
                <span class="info-value">{{ ucfirst($player->gender) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Created:</span>
                <span class="info-value">{{ $player->created_at->format('M j, Y') }}</span>
            </div>
        </div>
    </div>

    <!-- Inventory Panel - Right Side -->
    <div class="equipment-panel">
        <div class="mb-2">
            <h2 class="h6 mb-2">🎒 Quick Inventory</h2>
        </div>
        
        <div class="equipment-list">
            @if(isset($inventoryItems) && count($inventoryItems) > 0)
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
                        @if($playerItem->item->attack_bonus || $playerItem->item->defense_bonus)
                        <div class="equipment-item-stats">
                            @if($playerItem->item->attack_bonus)
                                +{{ $playerItem->item->attack_bonus }} ATK
                            @endif
                            @if($playerItem->item->defense_bonus)
                                +{{ $playerItem->item->defense_bonus }} DEF
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
<script>
function unequipPlayerItem(itemId) {
    // Create form to unequip item
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/game/player-item/unequip/' + itemId;
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken.getAttribute('content');
        form.appendChild(csrfInput);
    }
    
    document.body.appendChild(form);
    form.submit();
}

function showChangeGenderModal() {
    const modal = new bootstrap.Modal(document.getElementById('genderChangeModal'));
    modal.show();
}

function changeGender(newGender) {
    // Create form to change gender
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/game/character/change-gender';
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken.getAttribute('content');
        form.appendChild(csrfInput);
    }
    
    const genderInput = document.createElement('input');
    genderInput.type = 'hidden';
    genderInput.name = 'gender';
    genderInput.value = newGender;
    form.appendChild(genderInput);
    
    document.body.appendChild(form);
    form.submit();
}

function equipFromCharacterPage(itemId) {
    // Create form to equip item
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/game/inventory/equip/' + itemId;
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken.getAttribute('content');
        form.appendChild(csrfInput);
    }
    
    document.body.appendChild(form);
    form.submit();
}

// Tooltip functionality for equipment slots
document.addEventListener('DOMContentLoaded', function() {
    const equipmentSlots = document.querySelectorAll('.equipment-slot');
    let tooltip = null;

    equipmentSlots.forEach(slot => {
        if (slot.classList.contains('equipped-slot')) {
            slot.addEventListener('mouseenter', function(e) {
                const itemImage = this.querySelector('.slot-item-image');
                const itemName = this.getAttribute('title');
                if (itemImage && itemName && itemName !== 'Helmet' && itemName !== 'Main Hand') {
                    showTooltip(e, itemName);
                }
            });

            slot.addEventListener('mouseleave', function() {
                hideTooltip();
            });
        }
    });

    function showTooltip(event, content) {
        hideTooltip(); // Remove any existing tooltip
        
        tooltip = document.createElement('div');
        tooltip.className = 'equipment-tooltip';
        tooltip.innerHTML = content;
        tooltip.style.cssText = `
            position: absolute;
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
            z-index: 1000;
            max-width: 250px;
            word-wrap: break-word;
        `;
        
        document.body.appendChild(tooltip);
        
        // Position tooltip near cursor
        const rect = tooltip.getBoundingClientRect();
        tooltip.style.left = Math.min(event.pageX + 10, window.innerWidth - rect.width - 10) + 'px';
        tooltip.style.top = Math.max(event.pageY - rect.height - 10, 10) + 'px';
    }

    function hideTooltip() {
        if (tooltip) {
            tooltip.remove();
            tooltip = null;
        }
    }
});
</script>
@endpush