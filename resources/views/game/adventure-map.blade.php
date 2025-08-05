@extends('game.layout')

@section('title', 'Adventure Map - ' . $adventure->title)

@section('content')
<div class="container-fluid">
    <!-- Adventure Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h3 mb-2">{{ $adventure->title }}</h1>
                            <p class="text-muted mb-1">{{ $adventure->description }}</p>
                            <div class="adventure-meta">
                                <span class="badge bg-info me-2">{{ ucfirst($adventure->difficulty) }}</span>
                                <span class="badge bg-secondary me-2">{{ ucfirst(str_replace('_', ' ', $adventure->road)) }}</span>
                                <span class="badge bg-{{ $adventure->status === 'active' ? 'success' : 'warning' }}">{{ ucfirst($adventure->status) }}</span>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="adventure-progress mb-2">
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" style="width: {{ $adventure->getCurrentProgress() * 100 }}%">
                                        {{ round($adventure->getCurrentProgress() * 100) }}%
                                    </div>
                                </div>
                                <small class="text-muted">Adventure Progress</small>
                            </div>
                            <div class="adventure-stats">
                                <small class="text-muted">
                                    Level {{ $adventure->getCurrentAdventureLevel() }}/15 | 
                                    {{ count($adventure->completed_nodes ?? []) }} nodes completed
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Panel: Adventure Map -->
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="h5 mb-0">Adventure Map</h3>
                    <div class="map-controls">
                        <button class="btn btn-sm btn-outline-primary" onclick="toggleFullscreen()">
                            <i class="fas fa-expand"></i> Fullscreen
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="resetMapView()">
                            <i class="fas fa-crosshairs"></i> Center
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="adventure-map" class="adventure-map-container">
                        <svg id="map-svg" width="1800" height="700" viewBox="0 0 1800 700" style="min-width: 1800px;">
                            <!-- Background -->
                            <defs>
                                <pattern id="gridPattern" width="50" height="50" patternUnits="userSpaceOnUse">
                                    <path d="M 50 0 L 0 0 0 50" fill="none" stroke="#e9ecef" stroke-width="1" opacity="0.3"/>
                                </pattern>
                                <filter id="glowEffect">
                                    <feGaussianBlur stdDeviation="3" result="coloredBlur"/>
                                    <feMerge> 
                                        <feMergeNode in="coloredBlur"/>
                                        <feMergeNode in="SourceGraphic"/>
                                    </feMerge>
                                </filter>
                            </defs>
                            
                            <rect width="100%" height="100%" fill="url(#gridPattern)"/>
                            
                            <!-- Path Connections -->
                            <g id="path-connections">
                                @php
                                    $nodePositions = [];
                                    // First pass: calculate node positions (must match the node positioning logic)
                                    foreach(($mapData['map']['nodes'] ?? []) as $level => $levelNodes) {
                                        foreach($levelNodes as $index => $node) {
                                            $nodeX = 150 + ($level - 1) * 110;
                                            $spacing = 500 / max(1, count($levelNodes));
                                            $nodeY = 100 + ($index * $spacing) + ($spacing / 2);
                                            $nodePositions[$node['id']] = ['x' => $nodeX, 'y' => $nodeY];
                                        }
                                    }
                                @endphp
                                
                                @foreach($mapData['map']['connections'] ?? [] as $fromNodeId => $toNodeIds)
                                    @foreach($toNodeIds as $toNodeId)
                                        @if(isset($nodePositions[$fromNodeId]) && isset($nodePositions[$toNodeId]))
                                            <line 
                                                x1="{{ $nodePositions[$fromNodeId]['x'] }}" 
                                                y1="{{ $nodePositions[$fromNodeId]['y'] }}" 
                                                x2="{{ $nodePositions[$toNodeId]['x'] }}" 
                                                y2="{{ $nodePositions[$toNodeId]['y'] }}"
                                                stroke="#6c757d" 
                                                stroke-width="3" 
                                                stroke-dasharray="5,5"
                                                opacity="0.6"
                                                class="path-line accessible"
                                            />
                                        @endif
                                    @endforeach
                                @endforeach
                            </g>
                            
                            <!-- Adventure Nodes -->
                            <g id="adventure-nodes">
                                @foreach($mapData['map']['nodes'] ?? [] as $level => $levelNodes)
                                    @foreach($levelNodes as $node)
                                        @php
                                            // Better node positioning
                                            $nodeX = 150 + ($level - 1) * 110;
                                            $spacing = 500 / max(1, count($levelNodes));
                                            $nodeY = 100 + ($loop->index * $spacing) + ($spacing / 2);
                                            
                                            $isCompleted = in_array($node['id'], $adventure->completed_nodes ?? []);
                                            $isCurrent = $node['id'] === $adventure->current_node_id;
                                            $isEntered = in_array($node['id'], $adventure->entered_nodes ?? []);
                                            
                                            // Check if player has already entered a different node on this level
                                            $nodeLevel = intval(explode('-', $node['id'])[0]);
                                            $hasEnteredLevelElsewhere = false;
                                            foreach (($adventure->entered_nodes ?? []) as $enteredNodeId) {
                                                $enteredLevel = intval(explode('-', $enteredNodeId)[0]);
                                                if ($enteredLevel === $nodeLevel && $enteredNodeId !== $node['id']) {
                                                    $hasEnteredLevelElsewhere = true;
                                                    break;
                                                }
                                            }
                                            
                                            // Check if node is accessible based on connections
                                            $isAccessible = false;
                                            if ($node['id'] === '1-1') {
                                                // Start node is always accessible
                                                $isAccessible = true;
                                            } elseif ($hasEnteredLevelElsewhere) {
                                                // If player entered another node on this level, this node is locked
                                                $isAccessible = false;
                                            } elseif ($isCurrent || $isCompleted || $isEntered) {
                                                // Current, completed, or entered nodes are accessible
                                                $isAccessible = true;
                                            } else {
                                                // Check if any COMPLETED node connects to this node (must be completed, not just current)
                                                $connections = $mapData['map']['connections'] ?? [];
                                                foreach ($connections as $fromNodeId => $toNodeIds) {
                                                    if (in_array($node['id'], $toNodeIds) && 
                                                        in_array($fromNodeId, $adventure->completed_nodes ?? [])) {
                                                        $isAccessible = true;
                                                        break;
                                                    }
                                                }
                                            }
                                        @endphp
                                        
                                        <g class="adventure-node {{ $isAccessible ? 'accessible' : 'locked' }} {{ $isCompleted ? 'completed' : '' }} {{ $isCurrent ? 'current' : '' }} {{ $isEntered && !$isCompleted ? 'entered' : '' }}" 
                                           data-node-id="{{ $node['id'] }}" 
                                           data-node-type="{{ $node['type'] }}"
                                           transform="translate({{ $nodeX }}, {{ $nodeY }})"
                                           onclick="selectNode('{{ $node['id'] }}')">
                                           
                                            <!-- Node Background Circle -->
                                            <circle 
                                                r="25" 
                                                fill="{{ $isCompleted ? '#28a745' : ($isCurrent ? '#ffc107' : ($isAccessible ? '#007bff' : '#6c757d')) }}" 
                                                stroke="{{ $isCurrent ? '#fd7e14' : '#ffffff' }}" 
                                                stroke-width="{{ $isCurrent ? '4' : '2' }}"
                                                filter="{{ $isCurrent ? 'url(#glowEffect)' : '' }}"
                                                class="node-circle"
                                            />
                                            
                                            <!-- Node Icon -->
                                            <text 
                                                text-anchor="middle" 
                                                dy="0.35em" 
                                                font-size="20" 
                                                fill="white"
                                                class="node-icon"
                                            >
                                                @switch($node['type'])
                                                    @case('start') 🚪 @break
                                                    @case('combat') ⚔️ @break
                                                    @case('treasure') 💰 @break
                                                    @case('event') 📜 @break
                                                    @case('rest') 🏕️ @break
                                                    @case('boss') 👹 @break
                                                    @default ❓
                                                @endswitch
                                            </text>
                                            
                                            <!-- Node Level Indicator -->
                                            <text 
                                                text-anchor="middle" 
                                                dy="40" 
                                                font-size="12" 
                                                fill="#495057"
                                                font-weight="bold"
                                                class="node-level"
                                            >
                                                L{{ $level }}
                                            </text>
                                            
                                            <!-- Completion Status -->
                                            @if($isCompleted)
                                                <circle r="8" cx="18" cy="-18" fill="#28a745" stroke="white" stroke-width="2"/>
                                                <text x="18" y="-14" text-anchor="middle" font-size="10" fill="white">✓</text>
                                            @endif
                                        </g>
                                    @endforeach
                                @endforeach
                            </g>
                            
                            <!-- Level Labels -->
                            <g id="level-labels">
                                @for($level = 1; $level <= 15; $level++)
                                    <text 
                                        x="{{ 150 + ($level - 1) * 110 }}" 
                                        y="50" 
                                        text-anchor="middle" 
                                        font-size="14" 
                                        font-weight="bold" 
                                        fill="#495057"
                                    >
                                        Level {{ $level }}
                                    </text>
                                @endfor
                            </g>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Node Details & Actions -->
        <div class="col-lg-3">
            <!-- Current Node Info -->
            <div class="card mb-3" id="node-details">
                <div class="card-header">
                    <h5 class="mb-0">Current Location</h5>
                </div>
                <div class="card-body">
                    @if($currentNode)
                        <div class="node-info">
                            <div class="d-flex align-items-center mb-3">
                                <span class="node-icon-large me-3">
                                    @switch($currentNode['type'])
                                        @case('start') 🚪 @break  
                                        @case('combat') ⚔️ @break
                                        @case('treasure') 💰 @break
                                        @case('event') 📜 @break
                                        @case('rest') 🏕️ @break
                                        @case('boss') 👹 @break
                                        @default ❓
                                    @endswitch
                                </span>
                                <div>
                                    <h6 class="mb-1">{{ ucfirst($currentNode['type']) }} Node</h6>
                                    <small class="text-muted">{{ $currentNode['id'] }}</small>
                                </div>
                            </div>
                            
                            <p class="node-description">
                                {{ $currentNode['description'] ?? 'A mysterious location awaits...' }}
                            </p>
                            
                            @if(!in_array($currentNode['id'], $adventure->completed_nodes ?? []))
                                <div class="node-actions">
                                    @switch($currentNode['type'])
                                        @case('combat')
                                            <button class="btn btn-danger btn-sm w-100 mb-2" onclick="enterCombat('{{ $currentNode['id'] }}')">
                                                ⚔️ Enter Combat
                                            </button>
                                        @break
                                        
                                        @case('treasure')
                                            <button class="btn btn-warning btn-sm w-100 mb-2" onclick="searchTreasure('{{ $currentNode['id'] }}')">
                                                💰 Search for Treasure
                                            </button>
                                        @break
                                        
                                        @case('event')
                                            <button class="btn btn-info btn-sm w-100 mb-2" onclick="exploreEvent('{{ $currentNode['id'] }}')">
                                                📜 Investigate Event
                                            </button>
                                        @break
                                        
                                        @case('rest')
                                            <button class="btn btn-primary btn-sm w-100 mb-2" onclick="useRestSite('{{ $currentNode['id'] }}')">
                                                🏕️ Rest Here
                                            </button>
                                        @break
                                        
                                        @case('boss')
                                            <button class="btn btn-dark btn-sm w-100 mb-2" onclick="challengeBoss('{{ $currentNode['id'] }}')">
                                                👹 Challenge Boss
                                            </button>
                                        @break
                                        
                                        @default
                                            <button class="btn btn-secondary btn-sm w-100 mb-2" onclick="exploreNode('{{ $currentNode['id'] }}')">
                                                🔍 Explore
                                            </button>
                                    @endswitch
                                </div>
                            @else
                                <div class="alert alert-success">
                                    ✅ This location has been completed!
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-muted">Select a node to view details.</p>
                    @endif
                </div>
            </div>

            <!-- Adventure Actions -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Adventure Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-info btn-sm" onclick="viewInventory()">
                            🎒 Open Inventory
                        </button>
                        <button class="btn btn-outline-success btn-sm" onclick="viewCharacter()">
                            👤 Character Sheet
                        </button>
                        <button class="btn btn-outline-warning btn-sm" onclick="saveProgress()">
                            💾 Save Progress
                        </button>
                        <hr>
                        <button class="btn btn-outline-danger btn-sm" onclick="abandonAdventure()">
                            🚪 Leave Adventure
                        </button>
                    </div>
                </div>
            </div>

            <!-- Adventure Stats -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value">{{ count($adventure->completed_nodes ?? []) }}</div>
                            <div class="stat-label">Nodes Cleared</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">{{ $adventure->currency_earned }}</div>
                            <div class="stat-label">Gold Earned</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">{{ count($adventure->collected_loot ?? []) }}</div>
                            <div class="stat-label">Items Found</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">{{ $adventure->current_level }}</div>
                            <div class="stat-label">Current Level</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.adventure-map-container {
    position: relative;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 8px;
    overflow-x: auto;
    overflow-y: hidden;
}

.adventure-map-container::-webkit-scrollbar {
    height: 8px;
}

.adventure-map-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.adventure-map-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.adventure-map-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.adventure-node {
    cursor: pointer;
    transition: all 0.3s ease;
}

.adventure-node:hover .node-circle {
    r: 28;
    stroke-width: 3;
}

.adventure-node.locked {
    opacity: 0.4;
    cursor: not-allowed;
    pointer-events: none;
}

.adventure-node.accessible {
    cursor: pointer;
}

.adventure-node.current {
    cursor: pointer;
}

.adventure-node.selected .node-circle {
    stroke: #28a745;
    stroke-width: 4;
    filter: drop-shadow(0 0 8px rgba(40, 167, 69, 0.6));
}

.adventure-node.entered .node-circle {
    fill: #fd7e14;
    stroke: #e55100;
}

.adventure-node.completed .node-circle {
    filter: brightness(1.1);
}

.adventure-node.current .node-circle {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { stroke-width: 4; }
    50% { stroke-width: 6; }
    100% { stroke-width: 4; }
}

.path-line.accessible {
    stroke: #007bff;
    opacity: 0.8;
}

.path-line.locked {
    stroke: #6c757d;
    opacity: 0.3;
}

.node-icon-large {
    font-size: 2rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.stat-item {
    text-align: center;
    padding: 0.5rem;
    background: var(--bs-light);
    border-radius: 6px;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--bs-primary);
}

.stat-label {
    font-size: 0.75rem;
    color: var(--bs-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.adventure-meta .badge {
    font-size: 0.8rem;
}

.node-description {
    font-size: 0.9rem;
    line-height: 1.4;
}

/* Fullscreen mode */
.map-fullscreen {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 9999;
    background: white;
}

.map-fullscreen #map-svg {
    width: 100%;
    height: 100%;
}
</style>

<script>
let selectedNodeId = '{{ $adventure->current_node_id }}';

function selectNode(nodeId) {
    // Only allow selection of accessible nodes
    const targetNode = document.querySelector(`[data-node-id="${nodeId}"]`);
    if (!targetNode || targetNode.classList.contains('locked')) {
        return;
    }
    
    // Update visual selection
    document.querySelectorAll('.adventure-node').forEach(node => {
        node.classList.remove('selected');
    });
    
    targetNode.classList.add('selected');
    selectedNodeId = nodeId;
    loadNodeDetails(nodeId);
}

function loadNodeDetails(nodeId) {
    fetch(`/game/adventure/{{ $adventure->id }}/node/${nodeId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('node-details').innerHTML = data.html;
            }
        })
        .catch(error => console.error('Error loading node details:', error));
}

function enterCombat(nodeId) {
    // Mark node as entered before going to combat
    fetch(`/game/adventure/{{ $adventure->id }}/node/${nodeId}/action`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ action: 'enter_combat' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to combat page
            window.location.href = `/game/adventure/{{ $adventure->id }}/combat?node=${nodeId}`;
        } else {
            console.error('Cannot enter combat:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Fallback to direct navigation
        window.location.href = `/game/adventure/{{ $adventure->id }}/combat?node=${nodeId}`;
    });
}

function searchTreasure(nodeId) {
    processNodeAction(nodeId, 'search_treasure');
}

function exploreEvent(nodeId) {
    processNodeAction(nodeId, 'explore_event');
}

function interactWithNPC(nodeId, dialogueChoice) {
    processNodeAction(nodeId, `interact_npc:${dialogueChoice}`);
}

function useRestSite(nodeId) {
    processNodeAction(nodeId, 'rest');
}

function challengeBoss(nodeId) {
    // Mark node as entered before going to combat
    fetch(`/game/adventure/{{ $adventure->id }}/node/${nodeId}/action`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ action: 'enter_combat' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to combat page with boss flag
            window.location.href = `/game/adventure/{{ $adventure->id }}/combat?node=${nodeId}&boss=true`;
        } else {
            console.error('Cannot enter boss combat:', data.message);
        }
        })
        .catch(error => {
            console.error('Error:', error);
            // Fallback to direct navigation
            window.location.href = `/game/adventure/{{ $adventure->id }}/combat?node=${nodeId}&boss=true`;
        });
}

function exploreNode(nodeId) {
    processNodeAction(nodeId, 'explore');
}

function processNodeAction(nodeId, action) {
    fetch(`/game/adventure/{{ $adventure->id }}/node/${nodeId}/action`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ action: action })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show the result message in a better way
            if (data.message) {
                // Create a modal or alert to show the result
                showEventResult(data.message, () => {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        location.reload();
                    }
                });
            } else {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    location.reload();
                }
            }
        } else {
            console.error('Action failed:', data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

function showEventResult(message, callback) {
    // Create a modal to show the event result
    const modal = document.createElement('div');
    modal.className = 'modal fade show';
    modal.style.display = 'block';
    modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
    modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adventure Event</h5>
                </div>
                <div class="modal-body">
                    <p>${message}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="closeEventModal()">Continue</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Store callback for when modal is closed
    window.eventModalCallback = callback;
}

function closeEventModal() {
    const modal = document.querySelector('.modal.show');
    if (modal) {
        modal.remove();
    }
    if (window.eventModalCallback) {
        window.eventModalCallback();
        window.eventModalCallback = null;
    }
}

function viewInventory() {
    window.open('/game/inventory', '_blank');
}

function viewCharacter() {
    window.open('/game/character', '_blank');
}

function saveProgress() {
    fetch(`/game/adventure/{{ $adventure->id }}/save`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Progress saved successfully!');
        }
    });
}

function abandonAdventure() {
    GameUI.showConfirmModal(
        'Abandon Adventure',
        'Are you sure you want to abandon this adventure? All progress will be lost!',
        function() {
            fetch(`/game/adventures/{{ $adventure->id }}/abandon`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '/game/adventures';
                } else {
                    console.error('Failed to abandon adventure:', data.message);
                }
            })
            .catch(error => {
                console.error('Error abandoning adventure:', error);
            });
        }
    );
}

function toggleFullscreen() {
    const mapContainer = document.querySelector('.adventure-map-container');
    mapContainer.classList.toggle('map-fullscreen');
}

function resetMapView() {
    // Center the map view on current node
    const currentNode = document.querySelector('.adventure-node.current');
    if (currentNode) {
        // Get the node's position
        const nodeTransform = currentNode.getAttribute('transform');
        const match = nodeTransform.match(/translate\((\d+),\s*(\d+)\)/);
        if (match) {
            const nodeX = parseInt(match[1]);
            const mapContainer = document.querySelector('.adventure-map-container');
            // Scroll to center the current node
            const containerWidth = mapContainer.clientWidth;
            const scrollLeft = Math.max(0, nodeX - containerWidth / 2);
            mapContainer.scrollTo({ left: scrollLeft, behavior: 'smooth' });
        }
    }
}

// Initialize map
document.addEventListener('DOMContentLoaded', function() {
    if (selectedNodeId) {
        selectNode(selectedNodeId);
    }
    // Auto-center on current player position
    setTimeout(() => {
        resetMapView();
    }, 500); // Wait for DOM to be fully rendered
});
</script>
@endsection