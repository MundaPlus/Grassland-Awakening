<div class="row">
    <div class="col-md-4">
        <div class="item-icon-large border-{{ $inventoryItem->item->getRarityColor() }} text-center p-3 rounded" style="border: 3px solid; background: rgba(255,255,255,0.1);">
            <img src="{{ $inventoryItem->item->getImagePath() }}" 
                 alt="{{ $inventoryItem->item->name }}" 
                 style="width: 120px; height: 120px; object-fit: contain; border-radius: 8px;">
        </div>
    </div>
    <div class="col-md-8">
        <h4 class="rarity-{{ $inventoryItem->item->rarity }}">
            @if(method_exists($inventoryItem, 'getDisplayName'))
                {{ $inventoryItem->getDisplayName() }}
                @if(method_exists($inventoryItem, 'hasAffixes') && $inventoryItem->hasAffixes())
                    <small class="text-warning">✨</small>
                @endif
            @else
                {{ $inventoryItem->item->name }}
            @endif
        </h4>
        <p class="text-muted mb-2">{{ ucfirst($inventoryItem->item->type) }} - {{ ucfirst($inventoryItem->item->subtype) }}</p>
        <p class="rarity-{{ $inventoryItem->item->rarity }} fw-bold">{{ ucfirst($inventoryItem->item->rarity) }}</p>
        
        @if($inventoryItem->quantity > 1)
            <p><strong>Quantity:</strong> {{ $inventoryItem->quantity }}</p>
        @endif
        
        @if($inventoryItem->item->description)
            <p><strong>Description:</strong> {{ $inventoryItem->item->description }}</p>
        @endif
        
        @if($inventoryItem->item->level_requirement > 0)
            <p><strong>Level Requirement:</strong> {{ $inventoryItem->item->level_requirement }}</p>
        @endif
    </div>
</div>

<hr>

<div class="row">
    <div class="col-md-6">
        <h5>Item Statistics</h5>
        
        @if($inventoryItem->item->damage_dice)
            <p><strong>Damage:</strong> {{ $inventoryItem->item->damage_dice }}
            @if($inventoryItem->item->damage_bonus > 0) + {{ $inventoryItem->item->damage_bonus }}@endif</p>
        @endif
        
        @if($inventoryItem->item->ac_bonus > 0)
            <p><strong>Armor Class:</strong> +{{ $inventoryItem->getEffectiveACBonus() }}</p>
        @endif
        
        @if($inventoryItem->item->stats_modifiers)
            <h6>Stat Modifiers:</h6>
            <ul class="list-unstyled">
                @foreach($inventoryItem->item->stats_modifiers as $stat => $bonus)
                    @if($bonus != 0)
                        <li class="text-{{ $bonus > 0 ? 'success' : 'danger' }}">
                            {{ strtoupper($stat) }}: {{ $bonus > 0 ? '+' : '' }}{{ $inventoryItem->getEffectiveStatModifier($stat) }}
                        </li>
                    @endif
                @endforeach
            </ul>
        @endif
        
        @if($inventoryItem->item->value > 0)
            <p><strong>Value:</strong> {{ number_format($inventoryItem->item->value) }} gold</p>
        @endif
    </div>
    
    <div class="col-md-6">
        <h5>Item Condition</h5>
        
        @if($inventoryItem->max_durability > 0)
            <div class="mb-3">
                <div class="d-flex justify-content-between">
                    <span>Durability:</span>
                    <span>{{ $inventoryItem->current_durability }}/{{ $inventoryItem->max_durability }}</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-{{ $inventoryItem->getDurabilityPercentage() > 50 ? 'success' : ($inventoryItem->getDurabilityPercentage() > 25 ? 'warning' : 'danger') }}" 
                         style="width: {{ $inventoryItem->getDurabilityPercentage() }}%"></div>
                </div>
                <small class="text-muted">{{ $inventoryItem->getDurabilityPercentage() }}% condition</small>
            </div>
        @endif
        
        @if($inventoryItem->item_metadata)
            <h6>Special Properties:</h6>
            <ul class="list-unstyled">
                @foreach($inventoryItem->item_metadata as $key => $value)
                    <li><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</li>
                @endforeach
            </ul>
        @endif
        
        <p><strong>Added:</strong> {{ $inventoryItem->created_at->format('M j, Y') }}</p>
        @if($inventoryItem->updated_at != $inventoryItem->created_at)
            <p><strong>Last Modified:</strong> {{ $inventoryItem->updated_at->diffForHumans() }}</p>
        @endif
    </div>
</div>