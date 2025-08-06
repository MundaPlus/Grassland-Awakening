@extends('admin.layout')

@section('title', 'QA Testing Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-flask me-2"></i>QA Testing Dashboard
                    </h5>
                    <div class="btn-group">
                        <button class="btn btn-warning btn-sm" onclick="showResetAllModal()">
                            <i class="fas fa-exclamation-triangle me-1"></i>Reset All Players
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Player Selection -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Select Player for Testing</label>
                            <select id="selectedPlayer" class="form-select">
                                <option value="">Select a player...</option>
                                @foreach($players as $player)
                                <option value="{{ $player->id }}" 
                                        data-name="{{ $player->character_name }}"
                                        data-level="{{ $player->level }}"
                                        data-gold="{{ $player->persistent_currency }}">
                                    {{ $player->character_name }} ({{ $player->user->name }}) - Level {{ $player->level }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="mt-4" id="playerInfo" style="display: none;">
                                <div class="alert alert-info">
                                    <strong id="playerName">Player Name</strong><br>
                                    <small>Level <span id="playerLevel">1</span> | Gold: <span id="playerGold">0</span></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="testingTools" style="display: none;">
                        <!-- Player Management Tools -->
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-user me-2"></i>Player Management</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <button class="btn btn-danger btn-sm w-100" onclick="resetPlayer()">
                                                <i class="fas fa-undo me-1"></i>Reset to Level 1
                                            </button>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <button class="btn btn-warning btn-sm w-100" onclick="clearInventory()">
                                                <i class="fas fa-trash me-1"></i>Clear Inventory
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Set Level -->
                                    <div class="mb-3">
                                        <label class="form-label">Set Level</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="targetLevel" min="1" max="100" placeholder="Target level">
                                            <button class="btn btn-success" onclick="setPlayerLevel()">
                                                <i class="fas fa-level-up-alt me-1"></i>Set Level
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Add/Remove Currency -->
                                    <div class="mb-3">
                                        <label class="form-label">Modify Gold</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="currencyAmount" placeholder="Amount (+/-)">
                                            <button class="btn btn-success" onclick="addCurrency()">
                                                <i class="fas fa-coins me-1"></i>Update Gold
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stats Management -->
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Stats Management</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="form-label">STR</label>
                                                <input type="number" class="form-control form-control-sm" id="stat_str" min="1" max="30">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">DEX</label>
                                                <input type="number" class="form-control form-control-sm" id="stat_dex" min="1" max="30">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">CON</label>
                                                <input type="number" class="form-control form-control-sm" id="stat_con" min="1" max="30">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="form-label">INT</label>
                                                <input type="number" class="form-control form-control-sm" id="stat_int" min="1" max="30">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">WIS</label>
                                                <input type="number" class="form-control form-control-sm" id="stat_wis" min="1" max="30">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">CHA</label>
                                                <input type="number" class="form-control form-control-sm" id="stat_cha" min="1" max="30">
                                            </div>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary btn-sm w-100 mt-2" onclick="setStats()">
                                        <i class="fas fa-save me-1"></i>Update Stats
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Item Management -->
                        <div class="col-lg-12 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-treasure-chest me-2"></i>Item Management</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Select Item</label>
                                            <select class="form-select" id="itemSelect">
                                                <option value="">Choose an item...</option>
                                                @foreach($items as $item)
                                                <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->type }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Quantity</label>
                                            <input type="number" class="form-control" id="itemQuantity" value="1" min="1" max="999">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">&nbsp;</label>
                                            <button class="btn btn-success w-100" onclick="giveItem()">
                                                <i class="fas fa-gift me-1"></i>Give Item
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Quick Item Sets -->
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <label class="form-label">Quick Item Sets</label>
                                            <div class="btn-group w-100" role="group">
                                                <button class="btn btn-outline-secondary btn-sm" onclick="giveStarterGear()">
                                                    <i class="fas fa-sword me-1"></i>Starter Gear
                                                </button>
                                                <button class="btn btn-outline-secondary btn-sm" onclick="givePotions()">
                                                    <i class="fas fa-flask me-1"></i>Healing Potions
                                                </button>
                                                <button class="btn btn-outline-secondary btn-sm" onclick="giveCraftingMaterials()">
                                                    <i class="fas fa-hammer me-1"></i>Crafting Materials
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toast" class="toast" role="alert">
        <div class="toast-header">
            <strong class="me-auto">QA Testing</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body" id="toastMessage">
        </div>
    </div>
</div>

<script>
document.getElementById('selectedPlayer').addEventListener('change', function() {
    const playerId = this.value;
    const playerInfo = document.getElementById('playerInfo');
    const testingTools = document.getElementById('testingTools');
    
    if (playerId) {
        const option = this.options[this.selectedIndex];
        document.getElementById('playerName').textContent = option.dataset.name;
        document.getElementById('playerLevel').textContent = option.dataset.level;
        document.getElementById('playerGold').textContent = option.dataset.gold;
        
        playerInfo.style.display = 'block';
        testingTools.style.display = 'block';
    } else {
        playerInfo.style.display = 'none';
        testingTools.style.display = 'none';
    }
});

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');
    
    toastMessage.textContent = message;
    toast.className = `toast ${type === 'success' ? 'bg-success' : 'bg-danger'} text-white`;
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
}

function getSelectedPlayerId() {
    const playerId = document.getElementById('selectedPlayer').value;
    if (!playerId) {
        showToast('Please select a player first', 'error');
        return null;
    }
    return playerId;
}

function resetPlayer() {
    const playerId = getSelectedPlayerId();
    if (!playerId) return;

    if (confirm('Are you sure you want to reset this player to level 1? This will clear all progress!')) {
        fetch('/admin/qa/reset-player', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ player_id: playerId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message);
                location.reload();
            } else {
                showToast(data.message, 'error');
            }
        });
    }
}

function setPlayerLevel() {
    const playerId = getSelectedPlayerId();
    const level = document.getElementById('targetLevel').value;
    
    if (!playerId || !level) {
        showToast('Please select a player and enter a target level', 'error');
        return;
    }

    fetch('/admin/qa/set-player-level', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ player_id: playerId, level: parseInt(level) })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message);
            location.reload();
        } else {
            showToast(data.message, 'error');
        }
    });
}

function addCurrency() {
    const playerId = getSelectedPlayerId();
    const amount = document.getElementById('currencyAmount').value;
    
    if (!playerId || !amount) {
        showToast('Please select a player and enter an amount', 'error');
        return;
    }

    fetch('/admin/qa/add-currency', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ player_id: playerId, amount: parseInt(amount) })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message);
            location.reload();
        } else {
            showToast(data.message, 'error');
        }
    });
}

function clearInventory() {
    const playerId = getSelectedPlayerId();
    if (!playerId) return;

    if (confirm('Are you sure you want to clear this player\'s inventory?')) {
        fetch('/admin/qa/clear-inventory', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ player_id: playerId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message);
            } else {
                showToast(data.message, 'error');
            }
        });
    }
}

function setStats() {
    const playerId = getSelectedPlayerId();
    if (!playerId) return;

    const stats = {};
    ['str', 'dex', 'con', 'int', 'wis', 'cha'].forEach(stat => {
        const value = document.getElementById(`stat_${stat}`).value;
        if (value) stats[stat] = parseInt(value);
    });

    if (Object.keys(stats).length === 0) {
        showToast('Please enter at least one stat value', 'error');
        return;
    }

    fetch('/admin/qa/set-stats', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ player_id: playerId, ...stats })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message);
        } else {
            showToast(data.message, 'error');
        }
    });
}

function giveItem() {
    const playerId = getSelectedPlayerId();
    const itemId = document.getElementById('itemSelect').value;
    const quantity = document.getElementById('itemQuantity').value;
    
    if (!playerId || !itemId) {
        showToast('Please select a player and an item', 'error');
        return;
    }

    fetch('/admin/qa/give-item', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ 
            player_id: playerId, 
            item_id: itemId, 
            quantity: parseInt(quantity) 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message);
        } else {
            showToast(data.message, 'error');
        }
    });
}

// Quick item set functions
function giveStarterGear() {
    showToast('Starter gear functionality coming soon...', 'info');
}

function givePotions() {
    showToast('Potion pack functionality coming soon...', 'info');
}

function giveCraftingMaterials() {
    showToast('Crafting materials pack functionality coming soon...', 'info');
}
</script>
@endsection