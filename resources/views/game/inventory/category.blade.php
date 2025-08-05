@if($items->count() > 0)
    <div class="row">
        @foreach($items as $inventoryItem)
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="inventory-item {{ $inventoryItem->item->getRarityColor() }}-border" 
                     onclick="showItemDetail({{ $inventoryItem->id }})">
                    <div class="d-flex align-items-start">
                        <div class="item-icon bg-{{ $inventoryItem->item->getRarityColor() }}">
                            @switch($inventoryItem->item->type)
                                @case('weapon')
                                    @switch($inventoryItem->item->subtype)
                                        @case('sword') ⚔️ @break
                                        @case('axe') 🪓 @break
                                        @case('bow') 🏹 @break
                                        @case('staff') 🔮 @break
                                        @case('wand') ✨ @break
                                        @default ⚔️
                                    @endswitch
                                @break
                                @case('armor')
                                    @switch($inventoryItem->item->subtype)
                                        @case('helmet') ⛑️ @break
                                        @case('chest') 🦺 @break
                                        @case('pants') 👖 @break
                                        @case('boots') 👢 @break
                                        @case('gloves') 🧤 @break
                                        @case('shield') 🛡️ @break
                                        @default 🛡️
                                    @endswitch
                                @break
                                @case('accessory')
                                    @switch($inventoryItem->item->subtype)
                                        @case('ring') 💍 @break
                                        @case('necklace') 📿 @break
                                        @case('artifact') ⚡ @break
                                        @default 💍
                                    @endswitch
                                @break
                                @case('consumable') 🧪 @break
                                @case('crafting_material') ⚒️ @break
                                @case('quest_item') 📜 @break
                                @default 📦
                            @endswitch
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1 item-name rarity-{{ $inventoryItem->item->rarity }}">
                                        {{ $inventoryItem->item->name }}
                                    </h6>
                                    <small class="text-muted">{{ ucfirst($inventoryItem->item->subtype) }}</small>
                                </div>
                                @if($inventoryItem->quantity > 1)
                                    <span class="badge bg-info">x{{ $inventoryItem->quantity }}</span>
                                @endif
                            </div>
                            
                            <!-- Item Stats Preview -->
                            <div class="item-stats mt-2">
                                @if($inventoryItem->item->damage_dice)
                                    <small class="text-primary">
                                        🗡️ {{ $inventoryItem->item->damage_dice }}
                                        @if($inventoryItem->item->damage_bonus > 0)+{{ $inventoryItem->item->damage_bonus }}@endif
                                    </small>
                                @endif
                                
                                @if($inventoryItem->item->ac_bonus > 0)
                                    <small class="text-success">
                                        🛡️ +{{ $inventoryItem->getEffectiveACBonus() }} AC
                                    </small>
                                @endif
                                
                                @if($inventoryItem->item->stats_modifiers)
                                    <div class="stat-modifiers">
                                        @foreach($inventoryItem->item->stats_modifiers as $stat => $bonus)
                                            @if($bonus != 0)
                                                <small class="text-{{ $bonus > 0 ? 'success' : 'danger' }} me-2">
                                                    {{ strtoupper($stat) }} {{ $bonus > 0 ? '+' : '' }}{{ $inventoryItem->getEffectiveStatModifier($stat) }}
                                                </small>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Durability Bar -->
                            @if($inventoryItem->isDamaged())
                                <div class="durability-bar mt-2">
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar bg-{{ $inventoryItem->getDurabilityPercentage() > 50 ? 'success' : ($inventoryItem->getDurabilityPercentage() > 25 ? 'warning' : 'danger') }}" 
                                             style="width: {{ $inventoryItem->getDurabilityPercentage() }}%"
                                             title="Durability: {{ $inventoryItem->getDurabilityPercentage() }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $inventoryItem->getDurabilityPercentage() }}% condition</small>
                                </div>
                            @endif
                            
                            <!-- Item Value -->
                            @if($inventoryItem->item->value > 0)
                                <div class="item-value mt-1">
                                    <small class="text-warning">💰 {{ number_format($inventoryItem->item->value) }} gold</small>
                                </div>
                            @endif
                            
                            <!-- Quick Actions -->
                            <div class="item-actions mt-2">
                                @if($inventoryItem->item->type === 'weapon' || $inventoryItem->item->type === 'armor' || $inventoryItem->item->type === 'accessory')
                                    <button class="btn btn-sm btn-outline-primary me-1" 
                                            onclick="event.stopPropagation(); equipItem({{ $inventoryItem->id }}, '{{ $inventoryItem->item->getEquipmentSlot() }}')">
                                        Equip
                                    </button>
                                @endif
                                
                                @if($inventoryItem->item->type === 'consumable')
                                    <button class="btn btn-sm btn-outline-success me-1" 
                                            onclick="event.stopPropagation(); useItem({{ $inventoryItem->id }})">
                                        Use
                                    </button>
                                @endif
                                
                                @if($inventoryItem->item->level_requirement > 0)
                                    <small class="text-muted">
                                        Req. Level {{ $inventoryItem->item->level_requirement }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="empty-category">
        <i class="fas fa-box-open"></i>
        <h5>No {{ ucfirst($category) }} Items</h5>
        <p class="text-muted">You don't have any {{ $category }} items in your inventory yet.</p>
        <small class="text-muted">
            @switch($category)
                @case('weapon')
                    Weapons can be found as loot from enemies or purchased from merchants.
                @break
                @case('armor')
                    Armor pieces provide protection and can be found during adventures.
                @break
                @case('accessory')
                    Accessories like rings and amulets provide stat bonuses.
                @break
                @case('consumable')
                    Consumables like potions and food can be used during combat or exploration.
                @break
                @case('crafting_material')
                    Crafting materials are used to create new items and equipment.
                @break
                @default
                    Items can be obtained through various activities in the game.
            @endswitch
        </small>
    </div>
@endif