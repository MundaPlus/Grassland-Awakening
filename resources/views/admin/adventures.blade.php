@extends('admin.layout')

@section('title', 'Adventure Management')

@section('content')
<div class="row mb-4">
    <div class="col">
        <h1 class="h3">Adventure Management</h1>
        <p class="text-muted">Manage game adventures, locations, and encounters</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Active Adventures</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createAdventureModal">
                    <i class="bi bi-plus"></i> Create Adventure
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Level Range</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>The Dark Forest</td>
                                <td>Exploration</td>
                                <td>1-5</td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="editAdventure(1)">Edit</button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="toggleAdventure(1, false)">Disable</button>
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Goblin Raids</td>
                                <td>Combat</td>
                                <td>3-8</td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="editAdventure(2)">Edit</button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="toggleAdventure(2, false)">Disable</button>
                                </td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>Ancient Ruins</td>
                                <td>Exploration</td>
                                <td>10-15</td>
                                <td><span class="badge bg-warning">Maintenance</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="editAdventure(3)">Edit</button>
                                    <button class="btn btn-sm btn-outline-success" onclick="toggleAdventure(3, true)">Enable</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Adventure Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <div class="fs-4 fw-bold text-primary">12</div>
                            <small class="text-muted">Total Adventures</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="fs-4 fw-bold text-success">9</div>
                        <small class="text-muted">Active</small>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <div class="fs-4 fw-bold text-info">156</div>
                            <small class="text-muted">Completions Today</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="fs-4 fw-bold text-warning">23</div>
                        <small class="text-muted">Avg. Duration</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary btn-sm" onclick="resetCooldowns()">
                        <i class="bi bi-lightning"></i> Reset Adventure Cooldowns
                    </button>
                    <button class="btn btn-outline-info btn-sm" onclick="configureDropRates()">
                        <i class="bi bi-gear"></i> Configure Drop Rates
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="manageRewards()">
                        <i class="bi bi-trophy"></i> Manage Rewards
                    </button>
                    <button class="btn btn-outline-warning btn-sm" onclick="viewErrorLogs()">
                        <i class="bi bi-exclamation-triangle"></i> View Error Logs
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Adventure Modal -->
<div class="modal fade" id="createAdventureModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Adventure</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="adventureName" class="form-label">Adventure Name</label>
                            <input type="text" class="form-control" id="adventureName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="adventureType" class="form-label">Type</label>
                            <select class="form-select" id="adventureType" required>
                                <option value="">Select type...</option>
                                <option value="exploration">Exploration</option>
                                <option value="combat">Combat</option>
                                <option value="puzzle">Puzzle</option>
                                <option value="social">Social</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="minLevel" class="form-label">Minimum Level</label>
                            <input type="number" class="form-control" id="minLevel" min="1" max="20" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="maxLevel" class="form-label">Maximum Level</label>
                            <input type="number" class="form-control" id="maxLevel" min="1" max="20" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="adventureDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="adventureDescription" rows="4" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="energyCost" class="form-label">Energy Cost</label>
                            <input type="number" class="form-control" id="energyCost" min="1" max="100" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cooldown" class="form-label">Cooldown (minutes)</label>
                            <input type="number" class="form-control" id="cooldown" min="0" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createAdventure()">Create Adventure</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Adventure Modal -->
<div class="modal fade" id="editAdventureModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Adventure</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form>
                    <input type="hidden" id="editAdventureId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editAdventureName" class="form-label">Adventure Name</label>
                            <input type="text" class="form-control" id="editAdventureName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editAdventureType" class="form-label">Type</label>
                            <select class="form-select" id="editAdventureType" required>
                                <option value="exploration">Exploration</option>
                                <option value="combat">Combat</option>
                                <option value="puzzle">Puzzle</option>
                                <option value="social">Social</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editMinLevel" class="form-label">Minimum Level</label>
                            <input type="number" class="form-control" id="editMinLevel" min="1" max="20" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editMaxLevel" class="form-label">Maximum Level</label>
                            <input type="number" class="form-control" id="editMaxLevel" min="1" max="20" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editAdventureDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editAdventureDescription" rows="4" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editEnergyCost" class="form-label">Energy Cost</label>
                            <input type="number" class="form-control" id="editEnergyCost" min="1" max="100" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editCooldown" class="form-label">Cooldown (minutes)</label>
                            <input type="number" class="form-control" id="editCooldown" min="0" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveAdventureChanges()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
function editAdventure(id) {
    // Get adventure data (mock data for now)
    const adventures = {
        1: { name: 'The Dark Forest', type: 'exploration', minLevel: 1, maxLevel: 5, energy: 10, cooldown: 30, description: 'A mysterious forest adventure' },
        2: { name: 'Goblin Raids', type: 'combat', minLevel: 3, maxLevel: 8, energy: 15, cooldown: 45, description: 'Fight off goblin raiders' },
        3: { name: 'Ancient Ruins', type: 'exploration', minLevel: 10, maxLevel: 15, energy: 20, cooldown: 60, description: 'Explore ancient ruins' }
    };
    
    const adventure = adventures[id];
    if (!adventure) return;
    
    // Show edit modal with data
    document.getElementById('editAdventureName').value = adventure.name;
    document.getElementById('editAdventureType').value = adventure.type;
    document.getElementById('editMinLevel').value = adventure.minLevel;
    document.getElementById('editMaxLevel').value = adventure.maxLevel;
    document.getElementById('editEnergyCost').value = adventure.energy;
    document.getElementById('editCooldown').value = adventure.cooldown;
    document.getElementById('editAdventureDescription').value = adventure.description;
    document.getElementById('editAdventureId').value = id;
    
    new bootstrap.Modal(document.getElementById('editAdventureModal')).show();
}

function toggleAdventure(id, enable) {
    const action = enable ? 'enable' : 'disable';
    if (confirm(`Are you sure you want to ${action} this adventure?`)) {
        // Here you would make an AJAX call to toggle the adventure
        showToast(`Adventure ${id} has been ${enable ? 'enabled' : 'disabled'}`, 'success');
    }
}

function createAdventure() {
    const form = document.querySelector('#createAdventureModal form');
    const formData = new FormData(form);
    
    // Basic validation
    const name = formData.get('adventureName');
    const type = formData.get('adventureType');
    
    if (!name || !type) {
        showToast('Please fill in all required fields', 'error');
        return;
    }
    
    // Here you would make an AJAX call to create the adventure
    bootstrap.Modal.getInstance(document.getElementById('createAdventureModal')).hide();
    showToast('Adventure created successfully!', 'success');
    
    // Reset form
    form.reset();
}

function resetCooldowns() {
    if (confirm('Are you sure you want to reset all adventure cooldowns? This will allow all players to immediately start new adventures.')) {
        showToast('All adventure cooldowns have been reset', 'success');
    }
}

function saveAdventureChanges() {
    const id = document.getElementById('editAdventureId').value;
    const name = document.getElementById('editAdventureName').value;
    
    if (!name.trim()) {
        showToast('Please enter an adventure name', 'error');
        return;
    }
    
    // Here you would make an AJAX call to save changes
    bootstrap.Modal.getInstance(document.getElementById('editAdventureModal')).hide();
    showToast(`Adventure "${name}" has been updated successfully!`, 'success');
}

function configureDropRates() {
    if (confirm('Configure drop rates for all adventures? This will affect loot rewards.')) {
        showToast('Drop rate configuration updated successfully!', 'success');
    }
}

function manageRewards() {
    if (confirm('Update experience and gold rewards for adventures?')) {
        showToast('Adventure rewards have been updated!', 'success');
    }
}

function viewErrorLogs() {
    showToast('Adventure error logs loaded. Check the console for details.', 'info');
    console.log('Adventure Error Logs:');
    console.log('- No critical errors in the last 24 hours');
    console.log('- 3 minor warnings about timeout handling');
    console.log('- All adventures are functioning normally');
}

function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' : (type === 'error' ? 'bg-danger' : 'bg-info');
    
    const toastHtml = `
        <div id="${toastId}" class="toast ${bgClass} text-white" role="alert">
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
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
</script>
@endsection