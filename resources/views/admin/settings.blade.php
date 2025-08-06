@extends('admin.layout')

@section('title', 'System Settings')

@section('content')
<div class="row mb-4">
    <div class="col">
        <h1 class="h3">System Settings</h1>
        <p class="text-muted">Configure game mechanics and system parameters</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Game Mechanics</h5>
            </div>
            <div class="card-body">
                <form>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="maxLevel" class="form-label">Maximum Player Level</label>
                            <input type="number" class="form-control" id="maxLevel" value="20" min="1" max="100">
                            <div class="form-text">The highest level a player can reach</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="maxHpPerLevel" class="form-label">Base HP per Level</label>
                            <input type="number" class="form-control" id="maxHpPerLevel" value="10" min="5" max="50">
                            <div class="form-text">Base HP gained per level</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="maxStatValue" class="form-label">Maximum Stat Value</label>
                            <input type="number" class="form-control" id="maxStatValue" value="20" min="10" max="50">
                            <div class="form-text">Maximum value for character stats (STR, DEX, etc.)</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="skillPointsPerLevel" class="form-label">Skill Points per Level</label>
                            <input type="number" class="form-control" id="skillPointsPerLevel" value="3" min="1" max="10">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="baseExpRequired" class="form-label">Base EXP for Level 2</label>
                            <input type="number" class="form-control" id="baseExpRequired" value="100" min="50" max="1000">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="expMultiplier" class="form-label">EXP Multiplier per Level</label>
                            <input type="number" class="form-control" id="expMultiplier" value="1.5" min="1.1" max="3.0" step="0.1">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="allowPvp" checked>
                            <label class="form-check-label" for="allowPvp">
                                Enable Player vs Player Combat
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="allowGuilds" checked>
                            <label class="form-check-label" for="allowGuilds">
                                Enable Guild System
                            </label>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Economy Settings</h5>
            </div>
            <div class="card-body">
                <form>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="startingGold" class="form-label">Starting Gold</label>
                            <input type="number" class="form-control" id="startingGold" value="100" min="0" max="10000">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="combatGoldMultiplier" class="form-label">Combat Gold Multiplier</label>
                            <input type="number" class="form-control" id="combatGoldMultiplier" value="1.0" min="0.1" max="5.0" step="0.1">
                            <div class="form-text">Gold earned from combat encounters</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="shopTaxRate" class="form-label">Shop Tax Rate (%)</label>
                            <input type="number" class="form-control" id="shopTaxRate" value="5" min="0" max="50" step="0.1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="repairCostMultiplier" class="form-label">Repair Cost Multiplier</label>
                            <input type="number" class="form-control" id="repairCostMultiplier" value="0.1" min="0.01" max="1.0" step="0.01">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="enablePlayerTrading" checked>
                            <label class="form-check-label" for="enablePlayerTrading">
                                Enable Player-to-Player Trading
                            </label>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">System Status</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Game Server</span>
                    <span class="badge bg-success">Online</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Database</span>
                    <span class="badge bg-success">Connected</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Cache System</span>
                    <span class="badge bg-success">Active</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Background Jobs</span>
                    <span class="badge bg-warning">2 Pending</span>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Maintenance Mode</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small">Toggle maintenance mode to perform updates</p>
                <div class="d-grid">
                    <button class="btn btn-warning btn-sm" onclick="toggleMaintenance()">
                        <i class="bi bi-tools"></i> Enable Maintenance Mode
                    </button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Data Management</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-info btn-sm" onclick="exportPlayerData()">
                        <i class="bi bi-download"></i> Export Player Data
                    </button>
                    <button class="btn btn-outline-primary btn-sm" onclick="clearCache()">
                        <i class="bi bi-arrow-clockwise"></i> Clear Cache
                    </button>
                    <button class="btn btn-outline-warning btn-sm" onclick="backupDatabase()">
                        <i class="bi bi-archive"></i> Backup Database
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="cleanOldLogs()">
                        <i class="bi bi-trash"></i> Clean Old Logs
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col">
        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-secondary me-2" onclick="resetToDefaults()">Reset to Defaults</button>
            <button type="button" class="btn btn-primary" onclick="saveAllSettings()">Save All Settings</button>
        </div>
    </div>
</div>

<script>
function toggleMaintenance() {
    showConfirmModal(
        'Enable Maintenance Mode',
        'Are you sure you want to enable maintenance mode? This will prevent players from accessing the game.',
        function() {
            showToast('Maintenance mode enabled. Players will see a maintenance message.', 'warning');
            
            // Update button text
            const btn = document.querySelector('button[onclick="toggleMaintenance()"]');
            btn.innerHTML = '<i class="bi bi-tools"></i> Disable Maintenance Mode';
            btn.onclick = function() {
                showConfirmModal(
                    'Disable Maintenance Mode',
                    'Disable maintenance mode and allow players to access the game again?',
                    function() {
                        showToast('Maintenance mode disabled. Game is now accessible to players.', 'success');
                        btn.innerHTML = '<i class="bi bi-tools"></i> Enable Maintenance Mode';
                        btn.onclick = toggleMaintenance;
                    }
                );
            };
        }
    );
}

function exportPlayerData() {
    showConfirmModal(
        'Export Player Data',
        'Export all player data to CSV? This may take a few moments for large datasets.',
        function() {
            showToast('Player data export started...', 'info');
            window.location.href = '/admin/players/export';
        }
    );
}

function clearCache() {
    showConfirmModal(
        'Clear Application Cache',
        'Clear all application cache? This may temporarily slow down the application.',
        function() {
            fetch('/admin/cache/clear', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                showToast(data.message, data.success ? 'success' : 'error');
            })
            .catch(error => {
                showToast('Error clearing cache', 'error');
            });
        }
    );
}

function backupDatabase() {
    showConfirmModal(
        'Create Database Backup',
        'Create a full database backup? This operation may take several minutes and will create a large file.',
        function() {
            showToast('Database backup started...', 'info');
            fetch('/admin/database/backup', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                showToast(data.message, data.success ? 'success' : 'error');
            })
            .catch(error => {
                showToast('Error creating database backup', 'error');
            });
        }
    );
}

function cleanOldLogs() {
    showConfirmModal(
        'Clean Old Log Files',
        'Delete old log files? This will remove logs older than 30 days and cannot be undone.',
        function() {
            fetch('/admin/logs/clean', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                showToast(data.message, data.success ? 'success' : 'error');
            })
            .catch(error => {
                showToast('Error cleaning logs', 'error');
            });
        }
    );
}

function resetToDefaults() {
    showConfirmModal(
        'Reset Settings to Defaults',
        'Reset all settings to default values? This will overwrite your current configuration.',
        function() {
        // Reset all form fields to default values
        document.getElementById('maxLevel').value = 20;
        document.getElementById('maxHpPerLevel').value = 10;
        document.getElementById('maxStatValue').value = 20;
        document.getElementById('skillPointsPerLevel').value = 3;
        document.getElementById('baseExpRequired').value = 100;
        document.getElementById('expMultiplier').value = 1.5;
        document.getElementById('allowPvp').checked = true;
        document.getElementById('allowGuilds').checked = true;
        document.getElementById('startingGold').value = 100;
        document.getElementById('combatGoldMultiplier').value = 1.0;
        document.getElementById('shopTaxRate').value = 5;
        document.getElementById('repairCostMultiplier').value = 0.1;
        document.getElementById('enablePlayerTrading').checked = true;
            
            showToast('All settings have been reset to default values!', 'success');
        }
    );
}

function saveAllSettings() {
    // Collect all form values
    const settings = {
        maxLevel: document.getElementById('maxLevel').value,
        maxHpPerLevel: document.getElementById('maxHpPerLevel').value,
        maxStatValue: document.getElementById('maxStatValue').value,
        skillPointsPerLevel: document.getElementById('skillPointsPerLevel').value,
        baseExpRequired: document.getElementById('baseExpRequired').value,
        expMultiplier: document.getElementById('expMultiplier').value,
        allowPvp: document.getElementById('allowPvp').checked,
        allowGuilds: document.getElementById('allowGuilds').checked,
        startingGold: document.getElementById('startingGold').value,
        combatGoldMultiplier: document.getElementById('combatGoldMultiplier').value,
        shopTaxRate: document.getElementById('shopTaxRate').value,
        repairCostMultiplier: document.getElementById('repairCostMultiplier').value,
        enablePlayerTrading: document.getElementById('enablePlayerTrading').checked
    };
    
    fetch('/admin/settings/save', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(settings)
    })
    .then(response => response.json())
    .then(data => {
        showToast(data.message, data.success ? 'success' : 'error');
    })
    .catch(error => {
        showToast('Error saving settings', 'error');
    });
}

function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' : 
                   type === 'error' ? 'bg-danger' : 
                   type === 'warning' ? 'bg-warning' : 'bg-info';
    
    const toastHtml = `
        <div id="${toastId}" class="toast ${bgClass} text-white" role="alert">
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    document.body.appendChild(container);
    return container;
}

function showConfirmModal(title, message, confirmCallback) {
    let modal = document.getElementById('globalConfirmationModal');
    if (!modal) {
        // Create modal if it doesn't exist
        document.body.insertAdjacentHTML('beforeend', `
            <div class="modal fade" id="globalConfirmationModal" tabindex="-1" aria-labelledby="globalConfirmationModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="globalConfirmationModalLabel">Confirm Action</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="globalConfirmationModalBody">
                            Are you sure you want to proceed?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="globalConfirmationModalConfirm">Confirm</button>
                        </div>
                    </div>
                </div>
            </div>
        `);
        modal = document.getElementById('globalConfirmationModal');
    }
    
    document.getElementById('globalConfirmationModalLabel').textContent = title;
    document.getElementById('globalConfirmationModalBody').textContent = message;
    
    const confirmBtn = document.getElementById('globalConfirmationModalConfirm');
    confirmBtn.onclick = function() {
        bootstrap.Modal.getInstance(modal).hide();
        confirmCallback();
    };
    
    new bootstrap.Modal(modal).show();
}
</script>
@endsection