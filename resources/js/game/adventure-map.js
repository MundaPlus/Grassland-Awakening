let selectedNodeId = null;
let mapNodeData = {};
let adventureId = null;
let completedNodes = [];

// Initialize with server data
function initializeAdventureMap(data) {
    selectedNodeId = data.currentNodeId;
    mapNodeData = data.mapData;
    adventureId = data.adventureId;
    completedNodes = data.completedNodes || [];
    
    if (selectedNodeId) {
        selectNode(selectedNodeId);
    }
    setTimeout(() => {
        resetMapView();
    }, 500);
}

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
    
    // Find the specific node
    let nodeData = null;
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
    fetch(`/game/adventure/${adventureId}/node/${nodeId}/action`, {
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
            window.location.href = `/game/adventure/${adventureId}/combat?node=${nodeId}`;
        } else {
            console.error('Cannot enter combat:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.location.href = `/game/adventure/${adventureId}/combat?node=${nodeId}`;
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
    fetch(`/game/adventure/${adventureId}/node/${nodeId}/action`, {
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
            window.location.href = `/game/adventure/${adventureId}/combat?node=${nodeId}&boss=true`;
        } else {
            console.error('Cannot enter boss combat:', data.message);
        }
        })
        .catch(error => {
            console.error('Error:', error);
            window.location.href = `/game/adventure/${adventureId}/combat?node=${nodeId}&boss=true`;
        });
}

function exploreNode(nodeId) {
    processNodeAction(nodeId, 'explore');
}

function processNodeAction(nodeId, action) {
    fetch(`/game/adventure/${adventureId}/node/${nodeId}/action`, {
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
    fetch(`/game/adventure/${adventureId}/save`, {
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
        fetch(`/game/adventures/${adventureId}/abandon`, {
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