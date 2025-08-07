@extends('game.layout')

@section('title', 'Adventure Map - ' . $adventure->title)

@section('content')
@php
// Map road types to background images
$backgroundMap = [
    'forest_path' => 'forest_day.png',
    'mountain_trail' => 'rocky_day.png',
    'coastal_road' => 'riverbank_day.png',
    'desert_route' => 'grassland_day.png',
    'river_crossing' => 'riverbank_day.png',
    'ancient_highway' => 'grassland_day.png'
];

$backgroundImage = $backgroundMap[$adventure->road] ?? 'grassland_day.png';
@endphp

<div class="adventure-map-wrapper" style="background-image: url('/img/backgrounds/{{ $backgroundImage }}');">
    <div class="adventure-map-overlay">
        <div class="container-fluid">
            <!-- Adventure Header Panel -->
            <div class="adventure-header-panel">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="adventure-info">
                        <h1 class="adventure-title mb-1">🗺️ {{ $adventure->title }}</h1>
                        <p class="adventure-description mb-0">{{ $adventure->description }}</p>
                    </div>
                    <div class="adventure-stats">
                        <span class="stat-badge difficulty-{{ strtolower($adventure->difficulty) }}">
                            @switch($adventure->difficulty)
                                @case('easy') 🟢 Easy @break
                                @case('medium') 🟡 Medium @break
                                @case('hard') 🟠 Hard @break
                                @case('expert') 🔴 Expert @break
                                @default 🟡 Medium
                            @endswitch
                        </span>
                        <span class="stat-badge progress-badge">
                            {{ round($adventure->getCurrentProgress() * 100) }}% Complete
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Left Panel: Adventure Map -->
                <div class="col-lg-9">
                    <div class="map-panel-glass">
                        <div class="map-header">
                            <h3>Adventure Map</h3>
                            <div class="map-controls">
                            </div>
                        </div>
                        <div class="map-content">
                            <div id="adventure-map" class="adventure-map-container">
                                <svg id="map-svg" width="1800" height="700" viewBox="0 0 1800 700" style="min-width: 1800px;">
                                    <!-- Background -->
                                    <defs>
                                        <pattern id="gridPattern" width="50" height="50" patternUnits="userSpaceOnUse">
                                            <path d="M 50 0 L 0 0 0 50" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/>
                                        </pattern>
                                        <filter id="glowEffect">
                                            <feGaussianBlur stdDeviation="4" result="coloredBlur"/>
                                            <feMerge>
                                                <feMergeNode in="coloredBlur"/>
                                                <feMergeNode in="SourceGraphic"/>
                                            </feMerge>
                                        </filter>
                                        <filter id="nodeGlow">
                                            <feGaussianBlur stdDeviation="2" result="coloredBlur"/>
                                            <feMerge>
                                                <feMergeNode in="coloredBlur"/>
                                                <feMergeNode in="SourceGraphic"/>
                                            </feMerge>
                                        </filter>
                                    </defs>

                                    <rect width="100%" height="100%" fill="url(#gridPattern)" opacity="0.3"/>

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
                                                        stroke="rgba(255,255,255,0.4)"
                                                        stroke-width="3"
                                                        stroke-dasharray="8,4"
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
                                                        r="28"
                                                        fill="{{ $isCompleted ? 'rgba(40, 167, 69, 0.9)' : ($isCurrent ? 'rgba(255, 193, 7, 0.9)' : ($isAccessible ? 'rgba(0, 123, 255, 0.9)' : 'rgba(108, 117, 125, 0.6)')) }}"
                                                        stroke="{{ $isCurrent ? '#fd7e14' : 'rgba(255,255,255,0.8)' }}"
                                                        stroke-width="{{ $isCurrent ? '3' : '2' }}"
                                                        filter="{{ $isCurrent ? 'url(#glowEffect)' : 'url(#nodeGlow)' }}"
                                                        class="node-circle"
                                                    />

                                                    <!-- Node Icon -->
                                                    <text
                                                        text-anchor="middle"
                                                        dy="0.35em"
                                                        font-size="22"
                                                        fill="white"
                                                        class="node-icon"
                                                        style="text-shadow: 2px 2px 4px rgba(0,0,0,0.8);"
                                                    >
                                                        @switch($node['type'])
                                                            @case('start') 🚪 @break
                                                            @case('combat') ⚔️ @break
                                                            @case('treasure') 💰 @break
                                                            @case('event') 📜 @break
                                                            @case('resource_gathering') 🌿 @break
                                                            @case('rest') 🏕️ @break
                                                            @case('npc_encounter') 👤 @break
                                                            @case('boss') 👹 @break
                                                            @default ❓
                                                        @endswitch
                                                    </text>

                                                    <!-- Node Level Indicator -->
                                                    <text
                                                        text-anchor="middle"
                                                        dy="45"
                                                        font-size="12"
                                                        fill="rgba(255,255,255,0.8)"
                                                        font-weight="bold"
                                                        class="node-level"
                                                        style="text-shadow: 1px 1px 2px rgba(0,0,0,0.8);"
                                                    >
                                                        L{{ $level }}
                                                    </text>

                                                    <!-- Completion Status -->
                                                    @if($isCompleted)
                                                        <circle r="10" cx="20" cy="-20" fill="rgba(40, 167, 69, 1)" stroke="white" stroke-width="2" filter="url(#nodeGlow)"/>
                                                        <text x="20" y="-16" text-anchor="middle" font-size="12" fill="white" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">✓</text>
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
                                                y="45"
                                                text-anchor="middle"
                                                font-size="14"
                                                font-weight="bold"
                                                fill="rgba(255,255,255,0.9)"
                                                style="text-shadow: 2px 2px 4px rgba(0,0,0,0.8);"
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
                    <div class="info-panel-glass mb-3" id="node-details">
                        <div class="panel-header">
                            <h5>Current Location</h5>
                        </div>
                        <div class="panel-content">
                            @if($currentNode)
                                <div class="node-info">
                                    <div class="node-header">
                                        <span class="node-icon-large">
                                            @switch($currentNode['type'])
                                                @case('start') 🚪 @break
                                                @case('combat') ⚔️ @break
                                                @case('treasure') 💰 @break
                                                @case('event') 📜 @break
                                                @case('resource_gathering') 🌿 @break
                                                @case('rest') 🏕️ @break
                                                @case('npc_encounter') 👤 @break
                                                @case('boss') 👹 @break
                                                @default ❓
                                            @endswitch
                                        </span>
                                        <div class="node-header-text">
                                            <h6>{{ ucfirst(str_replace('_', ' ', $currentNode['type'])) }} Node</h6>
                                            <small class="node-id">{{ $currentNode['id'] }}</small>
                                        </div>
                                    </div>

                                    <p class="node-description">
                                        {{ $currentNode['description'] ?? 'A mysterious location awaits exploration...' }}
                                    </p>

                                    @if(!in_array($currentNode['id'], $adventure->completed_nodes ?? []))
                                        <div class="node-actions">
                                            @switch($currentNode['type'])
                                                @case('combat')
                                                    <button class="action-btn danger" onclick="enterCombat('{{ $currentNode['id'] }}')">
                                                        ⚔️ Enter Combat
                                                    </button>
                                                @break

                                                @case('treasure')
                                                    <button class="action-btn warning" onclick="searchTreasure('{{ $currentNode['id'] }}')">
                                                        💰 Search for Treasure
                                                    </button>
                                                @break

                                                @case('event')
                                                    <button class="action-btn info" onclick="exploreEvent('{{ $currentNode['id'] }}')">
                                                        📜 Investigate Event
                                                    </button>
                                                @break

                                                @case('resource_gathering')
                                                    <button class="action-btn success" onclick="gatherResources('{{ $currentNode['id'] }}')">
                                                        🌿 Gather Resources
                                                    </button>
                                                @break

                                                @case('npc_encounter')
                                                    <button class="action-btn primary" onclick="interactWithNPC('{{ $currentNode['id'] }}')">
                                                        👤 Talk to NPC
                                                    </button>
                                                @break

                                                @case('rest')
                                                    <button class="action-btn primary" onclick="useRestSite('{{ $currentNode['id'] }}')">
                                                        🏕️ Rest Here
                                                    </button>
                                                @break

                                                @case('boss')
                                                    <button class="action-btn dark" onclick="challengeBoss('{{ $currentNode['id'] }}')">
                                                        👹 Challenge Boss
                                                    </button>
                                                @break

                                                @default
                                                    <button class="action-btn secondary" onclick="exploreNode('{{ $currentNode['id'] }}')">
                                                        🔍 Explore
                                                    </button>
                                            @endswitch
                                        </div>
                                    @else
                                        <div class="completed-notice">
                                            ✅ This location has been completed!
                                        </div>
                                    @endif
                                </div>
                            @else
                                <p class="no-selection">Select a node on the map to view details.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Adventure Actions -->
                    <div class="info-panel-glass mb-3">
                        <div class="panel-header">
                            <h5>Adventure Actions</h5>
                        </div>
                        <div class="panel-content">
                            <div class="action-grid">
                                <button class="utility-btn" onclick="viewInventory()" title="Open Inventory">
                                    🎒 Inventory
                                </button>
                                <button class="utility-btn" onclick="viewCharacter()" title="Character Sheet">
                                    👤 Character
                                </button>
                                <button class="utility-btn" onclick="saveProgress()" title="Save Progress">
                                    💾 Save
                                </button>
                                <button class="utility-btn danger" onclick="abandonAdventure()" title="Leave Adventure">
                                    🚪 Leave
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Adventure Stats -->
                    <div class="info-panel-glass">
                        <div class="panel-header">
                            <h5>Statistics</h5>
                        </div>
                        <div class="panel-content">
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <div class="stat-icon">🎯</div>
                                    <div class="stat-content">
                                        <div class="stat-value">{{ count($adventure->completed_nodes ?? []) }}</div>
                                        <div class="stat-label">Nodes Cleared</div>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon">💰</div>
                                    <div class="stat-content">
                                        <div class="stat-value">{{ $adventure->currency_earned }}</div>
                                        <div class="stat-label">Gold Earned</div>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon">🎒</div>
                                    <div class="stat-content">
                                        <div class="stat-value">{{ count($adventure->collected_loot ?? []) }}</div>
                                        <div class="stat-label">Items Found</div>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon">📍</div>
                                    <div class="stat-content">
                                        <div class="stat-value">{{ $adventure->current_level }}</div>
                                        <div class="stat-label">Current Level</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions Panel - Bottom Center -->
        <div class="quick-actions-panel">
            <div class="mb-2 text-center text-white">
                <div class="fw-bold small">Quick Actions</div>
            </div>
            <div class="d-flex gap-2 flex-wrap justify-content-center">
                <a href="{{ route('game.character') }}" class="dashboard-btn primary">
                    👤 Character
                </a>
                <a href="{{ route('game.inventory') }}" class="dashboard-btn warning">
                    🎒 Inventory
                </a>
                <a href="{{ route('game.adventures') }}" class="dashboard-btn danger">
                    🗺️ Adventures
                </a>
                <a href="{{ route('game.dashboard') }}" class="dashboard-btn success">
                    🏠 Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Background and Layout */
.adventure-map-wrapper {
    min-height: 100vh;
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    position: relative;
}

.adventure-map-overlay {
    background: rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(1px);
    min-height: 100vh;
    padding: 20px 0;
}

/* Adventure Header Panel */
.adventure-header-panel {
    background: rgba(33, 37, 41, 0.9);
    backdrop-filter: blur(15px);
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 15px;
    padding: 15px 20px;
    margin: 20px;
    color: white;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.adventure-title {
    font-size: 1.6rem;
    font-weight: bold;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
}

.adventure-description {
    opacity: 0.9;
    font-size: 0.95rem;
}

.stat-badge {
    display: inline-block;
    padding: 6px 12px;
    margin-left: 8px;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(5px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    font-size: 0.85rem;
    font-weight: 500;
}

.stat-badge.difficulty-easy { background: rgba(40, 167, 69, 0.3); }
.stat-badge.difficulty-medium { background: rgba(255, 193, 7, 0.3); }
.stat-badge.difficulty-hard { background: rgba(255, 133, 27, 0.3); }
.stat-badge.difficulty-expert { background: rgba(220, 53, 69, 0.3); }
.stat-badge.progress-badge { background: rgba(0, 123, 255, 0.3); }

/* Glass Morphism Panels */
.adventure-header-glass,
.map-panel-glass,
.info-panel-glass {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    padding: 20px;
    color: white;
}

/* Adventure Header (old styles removed - now using header panel styles) */

.adventure-meta {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.meta-badge {
    padding: 8px 16px;
    border-radius: 25px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(5px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    font-size: 0.9rem;
    font-weight: 500;
}

.meta-badge.difficulty-easy { background: rgba(40, 167, 69, 0.3); }
.meta-badge.difficulty-medium { background: rgba(255, 193, 7, 0.3); }
.meta-badge.difficulty-hard { background: rgba(255, 133, 27, 0.3); }
.meta-badge.difficulty-expert { background: rgba(220, 53, 69, 0.3); }
.meta-badge.road-badge { background: rgba(0, 123, 255, 0.3); }
.meta-badge.status-active { background: rgba(40, 167, 69, 0.3); }
.meta-badge.status-available { background: rgba(0, 123, 255, 0.3); }

/* Progress Bar */
.progress-container {
    margin-top: 10px;
}

.progress-bar-glass {
    position: relative;
    height: 25px;
    background: rgba(0, 0, 0, 0.3);
    border-radius: 15px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.2);
    margin-bottom: 8px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, rgba(40, 167, 69, 0.8), rgba(40, 167, 69, 0.9));
    transition: width 0.5s ease;
    border-radius: 15px;
}

.progress-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 0.8rem;
    font-weight: bold;
    color: white;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
}

.adventure-stats {
    text-align: right;
    opacity: 0.8;
    font-size: 0.9rem;
}

/* Map Panel */
.map-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.map-header h3 {
    margin: 0;
    font-size: 1.5rem;
}

.map-controls {
    display: flex;
    gap: 10px;
}

.map-control-btn {
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 8px;
    padding: 8px 12px;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.map-control-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-1px);
}

/* Map Container */
.map-content {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    overflow: hidden;
}

.adventure-map-container {
    position: relative;
    overflow-x: auto;
    overflow-y: hidden;
    background: rgba(0, 0, 0, 0.1);
}

.adventure-map-container::-webkit-scrollbar {
    height: 8px;
}

.adventure-map-container::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
}

.adventure-map-container::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 4px;
}

.adventure-map-container::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}

/* Adventure Nodes */
.adventure-node {
    cursor: pointer;
    transition: all 0.3s ease;
}

.adventure-node:hover .node-circle {
    r: 32;
    stroke-width: 3;
    filter: url(#glowEffect);
}

.adventure-node.locked {
    opacity: 0.4;
    cursor: not-allowed;
    pointer-events: none;
}

.adventure-node.selected .node-circle {
    stroke: #28a745;
    stroke-width: 4;
    filter: url(#glowEffect);
}

.adventure-node.current .node-circle {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { stroke-width: 3; opacity: 1; }
    50% { stroke-width: 5; opacity: 0.8; }
    100% { stroke-width: 3; opacity: 1; }
}

/* Info Panels */
.panel-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    margin-bottom: 15px;
    padding-bottom: 10px;
}

.panel-header h5 {
    margin: 0;
    font-size: 1.2rem;
}

.panel-content {
    color: rgba(255, 255, 255, 0.9);
}

/* Node Info */
.node-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.node-icon-large {
    font-size: 2.5rem;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
}

.node-header-text h6 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: bold;
}

.node-id {
    opacity: 0.7;
    font-size: 0.8rem;
}

.node-description {
    font-size: 0.95rem;
    line-height: 1.4;
    margin-bottom: 15px;
    opacity: 0.9;
}

/* Action Buttons */
.action-btn {
    width: 100%;
    padding: 12px 16px;
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    background: rgba(255, 255, 255, 0.1);
    color: white;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 8px;
}

.action-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.action-btn.danger { background: rgba(220, 53, 69, 0.3); }
.action-btn.warning { background: rgba(255, 193, 7, 0.3); }
.action-btn.success { background: rgba(40, 167, 69, 0.3); }
.action-btn.info { background: rgba(23, 162, 184, 0.3); }
.action-btn.primary { background: rgba(0, 123, 255, 0.3); }
.action-btn.dark { background: rgba(52, 58, 64, 0.3); }
.action-btn.secondary { background: rgba(108, 117, 125, 0.3); }

.completed-notice {
    background: rgba(40, 167, 69, 0.2);
    border: 1px solid rgba(40, 167, 69, 0.4);
    border-radius: 8px;
    padding: 12px;
    text-align: center;
    font-weight: 500;
}

.no-selection {
    text-align: center;
    opacity: 0.7;
    font-style: italic;
}

/* Utility Actions */
.action-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.utility-btn {
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    background: rgba(255, 255, 255, 0.1);
    color: white;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.utility-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-1px);
}

.utility-btn.danger {
    background: rgba(220, 53, 69, 0.3);
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(255, 255, 255, 0.1);
    padding: 12px;
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.stat-icon {
    font-size: 1.5rem;
}

.stat-value {
    font-size: 1.4rem;
    font-weight: bold;
    margin-bottom: 2px;
}

.stat-label {
    font-size: 0.75rem;
    opacity: 0.8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .adventure-header-panel {
        margin: 10px;
        padding: 12px 15px;
    }
    
    .adventure-header-panel .d-flex {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 10px;
    }
    
    .adventure-title {
        font-size: 1.3rem;
    }
    
    .adventure-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .stat-badge {
        margin-left: 0;
        font-size: 0.8rem;
        padding: 4px 8px;
    }

    .adventure-meta {
        flex-direction: column;
        gap: 5px;
    }

    .stats-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .action-grid {
        grid-template-columns: 1fr;
        gap: 8px;
    }
}

/* Quick Actions Panel */
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
    text-decoration: none;
}

.dashboard-btn.primary { background: linear-gradient(135deg, #007bff, #0056b3); }
.dashboard-btn.success { background: linear-gradient(135deg, #28a745, #1e7e34); }
.dashboard-btn.warning { background: linear-gradient(135deg, #ffc107, #e0a800); }
.dashboard-btn.danger { background: linear-gradient(135deg, #dc3545, #c82333); }

/* Fullscreen Mode */
.map-fullscreen {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.9);
    backdrop-filter: blur(20px);
}

.map-fullscreen .adventure-map-container {
    height: 100vh;
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
    // Find the node data from the map
    const nodeElement = document.querySelector(`[data-node-id="${nodeId}"]`);
    if (!nodeElement) return;
    
    const nodeType = nodeElement.getAttribute('data-node-type');
    const panelContent = document.querySelector('#node-details .panel-content');
    if (!panelContent) return;
    
    // Get node data from the map data (we'll need to pass this from PHP)
    let nodeData = null;
    @php echo 'const mapNodeData = ' . json_encode($mapData['map']['nodes'] ?? []) . ';'; @endphp
    
    // Find the specific node
    for (const level in mapNodeData) {
        for (const node of mapNodeData[level]) {
            if (node.id === nodeId) {
                nodeData = node;
                break;
            }
        }
        if (nodeData) break;
    }
    
    if (!nodeData) return;
    
    // Check if completed
    const completedNodes = @json($adventure->completed_nodes ?? []);
    const isCompleted = completedNodes.includes(nodeId);
    
    // Generate the icon
    const nodeIcons = {
        'start': '🚪',
        'combat': '⚔️',
        'treasure': '💰',
        'event': '📜',
        'resource_gathering': '🌿',
        'rest': '🏕️',
        'npc_encounter': '👤',
        'boss': '👹'
    };
    
    const icon = nodeIcons[nodeType] || '❓';
    const typeName = nodeType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    
    // Generate action button
    let actionButton = '';
    if (!isCompleted) {
        const actionButtons = {
            'combat': `<button class="action-btn danger" onclick="enterCombat('${nodeId}')">⚔️ Enter Combat</button>`,
            'treasure': `<button class="action-btn warning" onclick="searchTreasure('${nodeId}')">💰 Search for Treasure</button>`,
            'event': `<button class="action-btn info" onclick="exploreEvent('${nodeId}')">📜 Investigate Event</button>`,
            'resource_gathering': `<button class="action-btn success" onclick="gatherResources('${nodeId}')">🌿 Gather Resources</button>`,
            'npc_encounter': `<button class="action-btn primary" onclick="interactWithNPC('${nodeId}')">👤 Talk to NPC</button>`,
            'rest': `<button class="action-btn primary" onclick="useRestSite('${nodeId}')">🏕️ Rest Here</button>`,
            'boss': `<button class="action-btn dark" onclick="challengeBoss('${nodeId}')">👹 Challenge Boss</button>`
        };
        
        actionButton = actionButtons[nodeType] || `<button class="action-btn secondary" onclick="exploreNode('${nodeId}')">🔍 Explore</button>`;
    } else {
        actionButton = '<div class="completed-notice">✅ This location has been completed!</div>';
    }
    
    // Build the HTML with our glass-morphism styling
    const nodeHtml = `
        <div class="node-info">
            <div class="node-header">
                <span class="node-icon-large">${icon}</span>
                <div class="node-header-text">
                    <h6>${typeName} Node</h6>
                    <small class="node-id">${nodeId}</small>
                </div>
            </div>
            
            <p class="node-description">
                ${nodeData.description || 'A mysterious location awaits exploration...'}
            </p>
            
            <div class="node-actions">
                ${actionButton}
            </div>
        </div>
    `;
    
    panelContent.innerHTML = nodeHtml;
}

function enterCombat(nodeId) {
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
            window.location.href = `/game/adventure/{{ $adventure->id }}/combat?node=${nodeId}`;
        } else {
            console.error('Cannot enter combat:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.location.href = `/game/adventure/{{ $adventure->id }}/combat?node=${nodeId}`;
    });
}

function searchTreasure(nodeId) {
    processNodeAction(nodeId, 'search_treasure');
}

function exploreEvent(nodeId) {
    processNodeAction(nodeId, 'explore_event');
}

function gatherResources(nodeId) {
    processNodeAction(nodeId, 'gather_resources');
}

function interactWithNPC(nodeId, dialogueChoice = 'greet') {
    processNodeAction(nodeId, `interact_npc:${dialogueChoice}`);
}

function useRestSite(nodeId) {
    processNodeAction(nodeId, 'rest');
}

function challengeBoss(nodeId) {
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
            window.location.href = `/game/adventure/{{ $adventure->id }}/combat?node=${nodeId}&boss=true`;
        } else {
            console.error('Cannot enter boss combat:', data.message);
        }
        })
        .catch(error => {
            console.error('Error:', error);
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
            if (data.message) {
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
    const modal = document.createElement('div');
    modal.className = 'modal fade show';
    modal.style.display = 'block';
    modal.style.backgroundColor = 'rgba(0,0,0,0.8)';
    modal.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.2); color: white;">
                <div class="modal-header" style="border-bottom: 1px solid rgba(255, 255, 255, 0.2);">
                    <h5 class="modal-title">Adventure Event</h5>
                </div>
                <div class="modal-body">
                    <p>${message}</p>
                </div>
                <div class="modal-footer" style="border-top: 1px solid rgba(255, 255, 255, 0.2);">
                    <button type="button" class="btn" style="background: rgba(0, 123, 255, 0.3); border: 1px solid rgba(255, 255, 255, 0.3); color: white;" onclick="closeEventModal()">Continue</button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
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
            // Show subtle save confirmation
            const btn = document.querySelector('[onclick="saveProgress()"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '✓ Saved!';
            btn.style.background = 'rgba(40, 167, 69, 0.4)';
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.background = '';
            }, 2000);
        }
    });
}

function abandonAdventure() {
    if (confirm('Are you sure you want to abandon this adventure? All progress will be lost!')) {
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
}

function toggleFullscreen() {
    const mapContainer = document.querySelector('.map-panel-glass');
    mapContainer.classList.toggle('map-fullscreen');
}

function resetMapView() {
    const currentNode = document.querySelector('.adventure-node.current');
    if (currentNode) {
        const nodeTransform = currentNode.getAttribute('transform');
        const match = nodeTransform.match(/translate\((\d+),\s*(\d+)\)/);
        if (match) {
            const nodeX = parseInt(match[1]);
            const mapContainer = document.querySelector('.adventure-map-container');
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
    setTimeout(() => {
        resetMapView();
    }, 500);
});
</script>
@endsection
