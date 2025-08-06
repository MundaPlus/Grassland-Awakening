@extends('admin.layout')

@section('title', 'Player Management')
@section('page-title', 'Player Management')
@section('page-description', 'Manage player characters and statistics')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user-shield me-2"></i>
                    All Players ({{ $players->total() }})
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Character</th>
                                <th>User</th>
                                <th>Level</th>
                                <th>Experience</th>
                                <th>Stats</th>
                                <th>HP</th>
                                <th>AC</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($players as $player)
                            <tr>
                                <td>
                                    <strong>{{ $player->character_name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ ucfirst($player->gender) }}</small>
                                </td>
                                <td>{{ $player->user->name }}</td>
                                <td>
                                    <span class="badge bg-primary fs-6">
                                        {{ $player->level }}
                                    </span>
                                </td>
                                <td>{{ number_format($player->experience) }} XP</td>
                                <td>
                                    <small>
                                        STR:{{ $player->str }} DEX:{{ $player->dex }} CON:{{ $player->con }}<br>
                                        INT:{{ $player->int }} WIS:{{ $player->wis }} CHA:{{ $player->cha }}
                                    </small>
                                </td>
                                <td>{{ $player->hp }}/{{ $player->max_hp }}</td>
                                <td>{{ $player->getTotalAC() }}</td>
                                <td>{{ $player->created_at->format('M d, Y') }}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editPlayerModal{{ $player->id }}">
                                        <i class="fas fa-edit"></i>
                                        Edit
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if($players->hasPages())
            <div class="card-footer">
                {{ $players->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h4>{{ $players->avg('level') ? round($players->avg('level'), 1) : 0 }}</h4>
                <p class="mb-0">Average Level</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h4>{{ $players->max('level') ?? 0 }}</h4>
                <p class="mb-0">Highest Level</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h4>{{ number_format($players->sum('experience')) }}</h4>
                <p class="mb-0">Total XP</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <h4>{{ $players->count() }}</h4>
                <p class="mb-0">Total Players</p>
            </div>
        </div>
    </div>
</div>

<!-- Edit Player Modal -->
<div class="modal fade" id="editPlayerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Player</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editPlayerForm">
                    @csrf
                    <input type="hidden" id="editPlayerId" name="player_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editCharacterName" class="form-label">Character Name</label>
                            <input type="text" class="form-control" id="editCharacterName" name="character_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editLevel" class="form-label">Level</label>
                            <input type="number" class="form-control" id="editLevel" name="level" min="1" max="20" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editHp" class="form-label">Current HP</label>
                            <input type="number" class="form-control" id="editHp" name="hp" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editMaxHp" class="form-label">Max HP</label>
                            <input type="number" class="form-control" id="editMaxHp" name="max_hp" min="1" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="editStr" class="form-label">Strength</label>
                            <input type="number" class="form-control" id="editStr" name="str" min="1" max="30" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="editDex" class="form-label">Dexterity</label>
                            <input type="number" class="form-control" id="editDex" name="dex" min="1" max="30" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="editCon" class="form-label">Constitution</label>
                            <input type="number" class="form-control" id="editCon" name="con" min="1" max="30" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="editInt" class="form-label">Intelligence</label>
                            <input type="number" class="form-control" id="editInt" name="int" min="1" max="30" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="editWis" class="form-label">Wisdom</label>
                            <input type="number" class="form-control" id="editWis" name="wis" min="1" max="30" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="editCha" class="form-label">Charisma</label>
                            <input type="number" class="form-control" id="editCha" name="cha" min="1" max="30" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editExperience" class="form-label">Experience</label>
                            <input type="number" class="form-control" id="editExperience" name="experience" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="editGold" class="form-label">Gold</label>
                            <input type="number" class="form-control" id="editGold" name="persistent_currency" min="0" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="savePlayerChanges()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
function editPlayer(playerId) {
    // Find player data from the table
    const row = document.querySelector(`tr[data-player-id="${playerId}"]`);
    if (!row) return;
    
    const cells = row.querySelectorAll('td');
    
    // Populate form fields
    document.getElementById('editPlayerId').value = playerId;
    document.getElementById('editCharacterName').value = cells[1].textContent.trim();
    document.getElementById('editLevel').value = cells[2].textContent.trim();
    
    // Show modal
    new bootstrap.Modal(document.getElementById('editPlayerModal')).show();
}

function savePlayerChanges() {
    const form = document.getElementById('editPlayerForm');
    const formData = new FormData(form);
    
    // Convert FormData to JSON
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    fetch('/admin/players/update', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editPlayerModal')).hide();
            showToast(data.message, 'success');
            // Optionally refresh the page or update the table row
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Error updating player', 'error');
        }
    })
    .catch(error => {
        showToast('Error updating player', 'error');
    });
}

function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
    
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