@if($items->count() > 0)
    <div class="row">
        @foreach($items as $inventoryItem)
            <div class="col-md-4 col-lg-3 mb-3">
                <div class="inventory-item-card {{ $inventoryItem->item->getRarityColor() }}-border" 
                     onclick="showItemDetail({{ $inventoryItem->id }})"
                     style="height: 200px; cursor: pointer; transition: all 0.2s ease;"
                     onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.1)';"
                     onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                    <div class="d-flex flex-column h-100">
                        <div class="text-center mb-2">
                            <div class="item-icon-small border-{{ $inventoryItem->item->getRarityColor() }} mx-auto" style="width: 50px; height: 50px; border: 2px solid; border-radius: 8px; padding: 2px; background: rgba(255,255,255,0.1);">
                                <img src="{{ $inventoryItem->item->getImagePath() }}" 
                                     alt="{{ $inventoryItem->item->name }}" 
                                     style="width: 100%; height: 100%; object-fit: contain; border-radius: 6px;">
                            </div>
                        </div>
                        <div class="flex-grow-1 text-center">
                            <h6 class="mb-1 item-name rarity-{{ $inventoryItem->item->rarity }}" style="font-size: 0.9rem; line-height: 1.2;">
                                {{ $inventoryItem->getDisplayName() }}
                                @if($inventoryItem->hasAffixes())
                                    <small class="text-warning">✨</small>
                                @endif
                            </h6>
                            <small class="text-muted d-block mb-2">{{ ucfirst($inventoryItem->item->subtype) }}</small>
                            @if($inventoryItem->quantity > 1)
                                <span class="badge bg-info small">x{{ $inventoryItem->quantity }}</span>
                            @endif
                        </div>
                        
                        <!-- Compact Item Stats -->
                        <div class="mt-auto pt-2 border-top">
                            @if($inventoryItem->item->damage_dice)
                                <small class="text-primary d-block" style="font-size: 0.75rem;">
                                    🗡️ {{ $inventoryItem->item->damage_dice }}@if($inventoryItem->item->damage_bonus > 0)+{{ $inventoryItem->item->damage_bonus }}@endif
                                </small>
                            @endif
                            
                            @if($inventoryItem->item->ac_bonus > 0)
                                <small class="text-success d-block" style="font-size: 0.75rem;">
                                    🛡️ +{{ $inventoryItem->getEffectiveACBonus() }} AC
                                </small>
                            @endif
                            
                            @php
                                $allStatModifiers = [];
                                if ($inventoryItem->item->stats_modifiers) {
                                    $allStatModifiers = $inventoryItem->item->stats_modifiers;
                                }
                                if ($inventoryItem->affix_stat_modifiers) {
                                    foreach ($inventoryItem->affix_stat_modifiers as $stat => $bonus) {
                                        if (!isset($allStatModifiers[$stat])) {
                                            $allStatModifiers[$stat] = 0;
                                        }
                                    }
                                }
                                $displayedStats = 0;
                            @endphp
                            @if($allStatModifiers)
                                @foreach($allStatModifiers as $stat => $bonus)
                                    @php $effectiveBonus = $inventoryItem->getEffectiveStatModifier($stat); @endphp
                                    @if($effectiveBonus != 0 && $displayedStats < 2)
                                        <small class="text-{{ $effectiveBonus > 0 ? 'success' : 'danger' }} d-block" style="font-size: 0.75rem;">
                                            {{ strtoupper($stat) }} {{ $effectiveBonus > 0 ? '+' : '' }}{{ $effectiveBonus }}
                                        </small>
                                        @php $displayedStats++; @endphp
                                    @endif
                                @endforeach
                            @endif
                            
                            <!-- Quick Action Button -->
                            <div class="mt-2">
                                @if($inventoryItem->item->type === 'weapon' || $inventoryItem->item->type === 'armor' || $inventoryItem->item->type === 'accessory')
                                    <button class="btn btn-sm btn-outline-primary w-100" style="font-size: 0.75rem; padding: 4px 8px;"
                                            onclick="event.stopPropagation(); equipItem({{ $inventoryItem->id }}, '{{ $inventoryItem->getEquipmentSlot() }}')">
                                        Equip
                                    </button>
                                @elseif($inventoryItem->item->type === 'consumable')
                                    <button class="btn btn-sm btn-outline-success w-100" style="font-size: 0.75rem; padding: 4px 8px;"
                                            onclick="event.stopPropagation(); useItem({{ $inventoryItem->id }})">
                                        Use
                                    </button>
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