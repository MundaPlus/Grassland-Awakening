<div class="card-header">
    <h5 class="mb-0">{{ ucfirst($node['type']) }} Node</h5>
</div>
<div class="card-body">
    <div class="node-info">
        <div class="d-flex align-items-center mb-3">
            <span class="node-icon-large me-3">
                @switch($node['type'])
                    @case('start') 🚪 @break  
                    @case('combat') ⚔️ @break
                    @case('treasure') 💰 @break
                    @case('event') 📜 @break
                    @case('npc_encounter') 👤 @break
                    @case('rest') 🏕️ @break
                    @case('boss') 👹 @break
                    @default ❓
                @endswitch
            </span>
            <div>
                <h6 class="mb-1">{{ $node['id'] }}</h6>
                <small class="text-muted">Level {{ $node['level'] }}</small>
            </div>
        </div>
        
        <p class="node-description">
            @switch($node['type'])
                @case('start')
                    {{ $node['description'] ?? 'The beginning of your adventure' }}
                @break
                @case('combat')
                    @php
                        $enemyType = $node['enemy_type'] ?? 'Unknown Enemy';
                        $enemyCount = $node['enemy_count'] ?? 1;
                    @endphp
                    @if($enemyCount > 1)
                        Face {{ $enemyCount }} {{ $enemyType }}s in battle
                    @else
                        Battle against a {{ $enemyType }}
                    @endif
                @break
                @case('treasure')
                    @php
                        $treasureType = $node['treasure_type'] ?? 'treasure';
                    @endphp
                    Discover a hidden {{ $treasureType }}
                @break
                @case('event')
                    @php
                        $eventType = $node['event_type'] ?? 'mysterious event';
                    @endphp
                    Encounter a {{ $eventType }}
                @break
                @case('npc_encounter')
                    @php
                        $npcData = $node['npc_data'] ?? [];
                        $npcName = $npcData['name'] ?? 'Stranger';
                        $npcType = $node['npc_type'] ?? 'traveler';
                    @endphp
                    Meet {{ $npcName }}, a {{ $npcType }}
                @break
                @case('rest')
                    A safe place to rest and recover
                @break
                @case('boss')
                    @php
                        $bossType = $node['boss_type'] ?? 'Powerful Boss';
                    @endphp
                    Face the mighty {{ $bossType }}
                @break
                @default
                    A mysterious location awaits...
            @endswitch
        </p>
        
        @if(!$isCompleted)
            <div class="node-actions">
                @switch($node['type'])
                    @case('combat')
                        <button class="btn btn-danger btn-sm w-100 mb-2" onclick="enterCombat('{{ $node['id'] }}')">
                            ⚔️ Enter Combat
                        </button>
                    @break
                    
                    @case('treasure')
                        <button class="btn btn-warning btn-sm w-100 mb-2" onclick="searchTreasure('{{ $node['id'] }}')">
                            💰 Search for Treasure
                        </button>
                    @break
                    
                    @case('event')
                        <button class="btn btn-info btn-sm w-100 mb-2" onclick="exploreEvent('{{ $node['id'] }}')">
                            📜 Investigate Event
                        </button>
                    @break
                    
                    @case('npc_encounter')
                        @php
                            $dialogueOptions = $node['dialogue_options'] ?? [];
                            $npcData = $node['npc_data'] ?? [];
                        @endphp
                        <div class="npc-dialogue-options">
                            <h6 class="text-muted mb-2">{{ $npcData['name'] ?? 'Stranger' }}</h6>
                            <p class="small mb-3">{{ $npcData['current_situation'] ?? 'Someone approaches you...' }}</p>
                            
                            @foreach($dialogueOptions as $optionKey => $option)
                                @php
                                    $requirements = $option['requirements'] ?? [];
                                    $canUse = true;
                                    $reqText = '';
                                    
                                    foreach($requirements as $stat => $minValue) {
                                        if(auth()->user()->player && auth()->user()->player->getTotalStat($stat) < $minValue) {
                                            $canUse = false;
                                            $reqText = " (Requires {$stat} {$minValue})";
                                            break;
                                        }
                                    }
                                @endphp
                                <button class="btn btn-outline-primary btn-sm w-100 mb-1 {{ $canUse ? '' : 'disabled' }}" 
                                        onclick="interactWithNPC('{{ $node['id'] }}', '{{ $optionKey }}')"
                                        {{ $canUse ? '' : 'disabled' }}>
                                    {{ $option['text'] }}{{ $reqText }}
                                </button>
                            @endforeach
                        </div>
                    @break
                    
                    @case('rest')
                        <button class="btn btn-primary btn-sm w-100 mb-2" onclick="useRestSite('{{ $node['id'] }}')">
                            🏕️ Rest Here
                        </button>
                    @break
                    
                    @case('boss')
                        <button class="btn btn-dark btn-sm w-100 mb-2" onclick="challengeBoss('{{ $node['id'] }}')">
                            👹 Challenge Boss
                        </button>
                    @break
                    
                    @default
                        <button class="btn btn-secondary btn-sm w-100 mb-2" onclick="exploreNode('{{ $node['id'] }}')">
                            🔍 Explore
                        </button>
                @endswitch
                
                @if(isset($node['currency_reward']) && $node['currency_reward'] > 0)
                    <small class="text-success d-block">💰 Potential reward: {{ $node['currency_reward'] }} gold</small>
                @endif
                
                @if(isset($node['has_item_drop']) && $node['has_item_drop'])
                    <small class="text-info d-block">
                        @switch($node['item_type'])
                            @case('combat_loot')
                                ⚔️ May drop weapons or armor
                            @break
                            @case('treasure_loot')
                                🎒 Contains valuable items
                            @break
                            @case('event_loot')
                                💍 Rare accessories possible
                            @break
                        @endswitch
                    </small>
                @endif
            </div>
        @else
            <div class="alert alert-success">
                ✅ This location has been completed!
            </div>
        @endif
    </div>
</div>