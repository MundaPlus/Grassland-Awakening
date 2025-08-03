@extends('game.layout')

@section('title', 'Village Management')

@section('content')
<div class="container-fluid">
    <!-- Village Overview Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h3 mb-2">{{ $village->name }}</h1>
                            <p class="text-muted mb-0" aria-label="Village description">{{ $village->description }}</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="village-level-badge">
                                <span class="badge bg-primary fs-6" aria-label="Village level {{ $village->level }}">
                                    Level {{ $village->level }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Village Specializations -->
    @if($village->specializations->isNotEmpty())
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0">Village Specializations</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($village->specializations as $specialization)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="specialization-card p-3 border rounded" role="article" aria-labelledby="spec-{{ $specialization->id }}">
                                <h3 class="h6 text-primary mb-2" id="spec-{{ $specialization->id }}">{{ $specialization->name }}</h3>
                                <p class="small text-muted mb-2">{{ $specialization->description }}</p>
                                <div class="specialization-bonus">
                                    <span class="badge bg-success" aria-label="Specialization bonus">{{ $specialization->bonus_description }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- NPCs Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0">Village NPCs ({{ $npcs->count() }})</h2>
                </div>
                <div class="card-body">
                    @if($npcs->isEmpty())
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3" aria-hidden="true"></i>
                        <p class="text-muted">No NPCs in your village yet. NPCs can be found during adventures!</p>
                    </div>
                    @else
                    <div class="row">
                        @foreach($npcs as $npc)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 npc-card" role="article" aria-labelledby="npc-{{ $npc->id }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h3 class="h6 mb-0" id="npc-{{ $npc->id }}">{{ $npc->name }}</h3>
                                        <span class="badge bg-secondary" aria-label="NPC level {{ $npc->level }}">Lv.{{ $npc->level }}</span>
                                    </div>
                                    <p class="small text-muted mb-2">{{ ucfirst($npc->profession) }}</p>
                                    
                                    <!-- NPC Stats -->
                                    <div class="npc-stats mb-3">
                                        <div class="row g-2 text-center">
                                            <div class="col-4">
                                                <div class="stat-mini">
                                                    <div class="small text-muted">STR</div>
                                                    <div class="fw-bold" aria-label="Strength {{ $npc->strength }}">{{ $npc->strength }}</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="stat-mini">
                                                    <div class="small text-muted">INT</div>
                                                    <div class="fw-bold" aria-label="Intelligence {{ $npc->intelligence }}">{{ $npc->intelligence }}</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="stat-mini">
                                                    <div class="small text-muted">WIS</div>
                                                    <div class="fw-bold" aria-label="Wisdom {{ $npc->wisdom }}">{{ $npc->wisdom }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- NPC Skills -->
                                    @if($npc->skills->isNotEmpty())
                                    <div class="npc-skills mb-3">
                                        <h4 class="small text-muted mb-2">Skills:</h4>
                                        <div class="skills-list">
                                            @foreach($npc->skills->take(3) as $skill)
                                            <span class="badge bg-light text-dark me-1 mb-1" title="{{ $skill->description }}">{{ $skill->name }}</span>
                                            @endforeach
                                            @if($npc->skills->count() > 3)
                                            <span class="small text-muted">+{{ $npc->skills->count() - 3 }} more</span>
                                            @endif
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Action Buttons -->
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-primary btn-sm flex-fill" 
                                                onclick="trainNPC({{ $npc->id }})" 
                                                aria-label="Train {{ $npc->name }}">
                                            <i class="fas fa-dumbbell" aria-hidden="true"></i> Train
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" 
                                                data-bs-toggle="modal" data-bs-target="#npcModal{{ $npc->id }}"
                                                aria-label="View {{ $npc->name }} details">
                                            <i class="fas fa-eye" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


<!-- NPC Detail Modals -->
@foreach($npcs as $npc)
<div class="modal fade" id="npcModal{{ $npc->id }}" tabindex="-1" aria-labelledby="npcModal{{ $npc->id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="npcModal{{ $npc->id }}Label">{{ $npc->name }} - {{ ucfirst($npc->profession) }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Character Stats</h6>
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <td>Level</td>
                                    <td>{{ $npc->level }}</td>
                                </tr>
                                <tr>
                                    <td>Experience</td>
                                    <td>{{ $npc->experience }} / {{ $npc->level * 100 }}</td>
                                </tr>
                                <tr>
                                    <td>Strength</td>
                                    <td>{{ $npc->strength }}</td>
                                </tr>
                                <tr>
                                    <td>Intelligence</td>
                                    <td>{{ $npc->intelligence }}</td>
                                </tr>
                                <tr>
                                    <td>Wisdom</td>
                                    <td>{{ $npc->wisdom }}</td>
                                </tr>
                                <tr>
                                    <td>Health</td>
                                    <td>{{ $npc->health }} / {{ $npc->max_health }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Skills & Abilities</h6>
                        @if($npc->skills->isNotEmpty())
                        <div class="skills-detailed">
                            @foreach($npc->skills as $skill)
                            <div class="skill-item mb-2 p-2 border rounded">
                                <div class="fw-bold">{{ $skill->name }}</div>
                                <div class="small text-muted">{{ $skill->description }}</div>
                                @if($skill->prerequisites)
                                <div class="small text-info">Prerequisites: {{ $skill->prerequisites }}</div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-muted">No skills learned yet. Train this NPC to unlock abilities!</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="trainNPC({{ $npc->id }})" data-bs-dismiss="modal">Train NPC</button>
            </div>
        </div>
    </div>
</div>
@endforeach

<script>
function trainNPC(npcId) {
    if (confirm('Train this NPC? This will cost gold and time.')) {
        fetch(`{{ url('game/npc') }}/${npcId}/train`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Training failed. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
}
</script>
@endsection