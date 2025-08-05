<div class="text-start">
    <div class="fw-bold text-{{ $item->item->getRarityColor() }}">
        @if(method_exists($item, 'getDisplayName'))
            {{ $item->getDisplayName() }}
            @if(method_exists($item, 'hasAffixes') && $item->hasAffixes())
                <small class="text-warning">✨</small>
            @endif
        @else
            {{ $item->item->name }}
        @endif
    </div>
    <small class="text-muted">{{ ucfirst($item->item->subtype) }}</small>
    
    @if($item->item->description)
        <div class="mt-1 small">{{ $item->item->description }}</div>
    @endif
    
    <!-- Item Stats --> 
    @if($item->item->damage_dice)
        <div class="text-danger small mt-1">
            🗡️ {{ $item->item->damage_dice }}
            @if($item->item->damage_bonus > 0)+{{ $item->item->damage_bonus }}@endif
            damage
        </div>
    @endif
    
    @if($item->item->ac_bonus > 0)
        <div class="text-success small">
            🛡️ +{{ method_exists($item, 'getEffectiveACBonus') ? $item->getEffectiveACBonus() : $item->item->ac_bonus }} AC
        </div>
    @endif
    
    @php
        $allStatModifiers = [];
        if ($item->item->stats_modifiers) {
            $allStatModifiers = $item->item->stats_modifiers;
        }
        if (property_exists($item, 'affix_stat_modifiers') && $item->affix_stat_modifiers) {
            foreach ($item->affix_stat_modifiers as $stat => $bonus) {
                if (!isset($allStatModifiers[$stat])) {
                    $allStatModifiers[$stat] = 0;
                }
            }
        }
    @endphp
    @if($allStatModifiers)
        <div class="small mt-1">
            @foreach($allStatModifiers as $stat => $bonus)
                @php 
                    $effectiveBonus = method_exists($item, 'getEffectiveStatModifier') 
                        ? $item->getEffectiveStatModifier($stat) 
                        : $item->item->getStatModifier($stat); 
                @endphp
                @if($effectiveBonus != 0)
                    <span class="text-{{ $effectiveBonus > 0 ? 'success' : 'danger' }} me-2">
                        {{ strtoupper($stat) }} {{ $effectiveBonus > 0 ? '+' : '' }}{{ $effectiveBonus }}
                    </span>
                @endif
            @endforeach
        </div>
    @endif
    
    <!-- Durability -->
    @if(method_exists($item, 'isDamaged') && $item->isDamaged())
        <div class="small mt-1">
            <span class="text-{{ $item->getDurabilityPercentage() > 50 ? 'warning' : 'danger' }}">
                🔧 {{ $item->getDurabilityPercentage() }}% condition
            </span>
        </div>
    @endif
    
    <!-- Value -->
    @if($item->item->value > 0)
        <div class="text-warning small mt-1">
            💰 {{ number_format($item->item->value) }} gold
        </div>
    @endif
    
    <!-- Level Requirement -->
    @if($item->item->level_requirement > 0)
        <div class="text-info small mt-1">
            Requires Level {{ $item->item->level_requirement }}
        </div>
    @endif
</div>