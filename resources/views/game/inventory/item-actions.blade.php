@if($inventoryItem->item->type === 'weapon' || $inventoryItem->item->type === 'armor' || $inventoryItem->item->type === 'accessory')
    <button class="btn btn-primary" onclick="equipItem({{ $inventoryItem->id }}, '{{ $inventoryItem->item->getEquipmentSlot() }}')">
        <i class="fas fa-hand-paper"></i> Equip Item
    </button>
@endif

@if($inventoryItem->item->type === 'consumable')
    <button class="btn btn-success" onclick="useItem({{ $inventoryItem->id }})">
        <i class="fas fa-flask"></i> Use Item
        @if($inventoryItem->quantity > 1)
            ({{ $inventoryItem->quantity }} available)
        @endif
    </button>
@endif

@if($inventoryItem->isDamaged())
    <button class="btn btn-warning" onclick="repairItem({{ $inventoryItem->id }})">
        <i class="fas fa-wrench"></i> Repair
        @php
            $repairCost = (int) (($inventoryItem->item->value ?? 10) * 0.1 * ((100 - $inventoryItem->getDurabilityPercentage()) / 100));
        @endphp
        ({{ $repairCost }} gold)
    </button>
@endif

@if($inventoryItem->item->type === 'crafting_material')
    <button class="btn btn-info" onclick="viewCraftingRecipes({{ $inventoryItem->item->id }})">
        <i class="fas fa-hammer"></i> View Recipes
    </button>
@endif

<button class="btn btn-outline-danger" onclick="dropItem({{ $inventoryItem->id }})">
    <i class="fas fa-trash"></i> Drop Item
</button>

<script>
function repairItem(inventoryItemId) {
    fetch(`/game/inventory/repair/${inventoryItemId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('itemDetailModal')).hide();
            location.reload();
        } else {
            alert(data.message || 'Failed to repair item');
        }
    })
    .catch(error => console.error('Error:', error));
}

function dropItem(inventoryItemId) {
    if (confirm('Are you sure you want to drop this item? This action cannot be undone.')) {
        fetch(`/game/inventory/drop/${inventoryItemId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('itemDetailModal')).hide();
                location.reload();
            } else {
                alert(data.message || 'Failed to drop item');
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

function viewCraftingRecipes(itemId) {
    // This will be implemented when crafting system is added
    alert('Crafting system coming soon!');
}
</script>