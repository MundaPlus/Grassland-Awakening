@extends('game.layout')

@section('title', 'Reputation')

@section('content')
<div class="container-fluid">
    <!-- Reputation Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h3 mb-2">Faction Reputation</h1>
                            <p class="text-muted mb-0">Your standing with various factions across the realm</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="reputation-summary">
                                <span class="badge bg-info fs-6" aria-label="Total faction reputation {{ $totalReputation }}">
                                    Total: {{ $totalReputation }} points
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reputation Overview -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0">Faction Standing Overview</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($reputations as $reputation)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 faction-card" role="article" aria-labelledby="faction-{{ $reputation->id }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h3 class="h6 mb-0" id="faction-{{ $reputation->id }}">
                                            {{ $reputation->faction_name ?? 'Unknown Faction' }}
                                        </h3>
                                        <span class="badge bg-{{ $reputation->reputation_score >= 0 ? 'success' : 'danger' }}" 
                                              aria-label="Reputation score {{ $reputation->reputation_score }}">
                                            {{ $reputation->reputation_score >= 0 ? 'Positive' : 'Negative' }}
                                        </span>
                                    </div>
                                    
                                    <p class="small text-muted mb-3">
                                        A faction in the grasslands
                                    </p>
                                    
                                    <!-- Reputation Score -->
                                    <div class="reputation-progress mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="text-muted">Reputation Score</small>
                                            <small class="text-muted" aria-label="Reputation score {{ $reputation->reputation_score }}">
                                                {{ $reputation->reputation_score }} points
                                            </small>
                                        </div>
                                        <div class="text-center">
                                            @if($reputation->reputation_score >= 100)
                                                <span class="badge bg-success">Revered</span>
                                            @elseif($reputation->reputation_score >= 50)
                                                <span class="badge bg-info">Honored</span>
                                            @elseif($reputation->reputation_score >= 10)
                                                <span class="badge bg-primary">Friendly</span>
                                            @elseif($reputation->reputation_score >= -10)
                                                <span class="badge bg-secondary">Neutral</span>
                                            @elseif($reputation->reputation_score >= -50)
                                                <span class="badge bg-warning">Unfriendly</span>
                                            @else
                                                <span class="badge bg-danger">Hostile</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="text-center py-4">
                                <i class="fas fa-handshake fa-3x text-muted mb-3" aria-hidden="true"></i>
                                <p class="text-muted">No faction relationships established yet. Complete adventures and interact with NPCs to build reputation!</p>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Reputation Changes -->
    @if($recentChanges->isNotEmpty())
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0">Recent Reputation Changes</h2>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($recentChanges as $change)
                        <div class="timeline-item d-flex align-items-center mb-3" role="article">
                            <div class="timeline-marker me-3">
                                <i class="fas fa-{{ $change->points_change > 0 ? 'arrow-up text-success' : 'arrow-down text-danger' }}" aria-hidden="true"></i>
                            </div>
                            <div class="timeline-content flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ $change->faction_name ?? 'Unknown Faction' }}</strong>
                                        <span class="badge bg-{{ $change->points_change > 0 ? 'success' : 'danger' }} ms-2" 
                                              aria-label="Reputation {{ $change->points_change > 0 ? 'gained' : 'lost' }} {{ abs($change->points_change) }} points">
                                            {{ $change->points_change > 0 ? '+' : '' }}{{ $change->points_change }}
                                        </span>
                                    </div>
                                    <small class="text-muted">{{ $change->created_at->diffForHumans() }}</small>
                                </div>
                                @if($change->reason)
                                <p class="small text-muted mb-0 mt-1">{{ $change->reason }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Reputation Tips -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-light">
                <div class="card-header bg-light">
                    <h2 class="h5 mb-0">
                        <i class="fas fa-lightbulb text-warning" aria-hidden="true"></i> Reputation Tips
                    </h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h3 class="h6 text-success">Ways to Gain Reputation:</h3>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success" aria-hidden="true"></i> Complete faction-related adventures</li>
                                <li><i class="fas fa-check text-success" aria-hidden="true"></i> Help faction NPCs with tasks</li>
                                <li><i class="fas fa-check text-success" aria-hidden="true"></i> Donate resources to faction causes</li>
                                <li><i class="fas fa-check text-success" aria-hidden="true"></i> Defend faction territories</li>
                                <li><i class="fas fa-check text-success" aria-hidden="true"></i> Participate in faction events</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h3 class="h6 text-danger">Actions That Hurt Reputation:</h3>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-times text-danger" aria-hidden="true"></i> Attack faction members</li>
                                <li><i class="fas fa-times text-danger" aria-hidden="true"></i> Steal from faction territories</li>
                                <li><i class="fas fa-times text-danger" aria-hidden="true"></i> Support opposing factions</li>
                                <li><i class="fas fa-times text-danger" aria-hidden="true"></i> Fail important faction quests</li>
                                <li><i class="fas fa-times text-danger" aria-hidden="true"></i> Ignore faction emergencies</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.faction-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.faction-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.timeline-item {
    position: relative;
}

.timeline-marker {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    border: 2px solid #dee2e6;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.reputation-progress .progress {
    height: 8px;
}

@media (prefers-reduced-motion: reduce) {
    .faction-card {
        transition: none;
    }
}
</style>
@endsection