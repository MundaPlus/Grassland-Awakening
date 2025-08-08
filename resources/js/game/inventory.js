// Category switching functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.inventory-tab');
    const categories = document.querySelectorAll('.inventory-category');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetCategory = this.getAttribute('data-category');

            // Update active tab
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // Show target category, hide others
            categories.forEach(cat => {
                if (cat.getAttribute('data-category') === targetCategory) {
                    cat.style.display = 'block';
                } else {
                    cat.style.display = 'none';
                }
            });
        });
    });
});

// Item detail modal functionality
function showItemDetail(inventoryItemId) {
    fetch(`/game/inventory/item/${inventoryItemId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('itemDetailContent').innerHTML = data.html;
            document.getElementById('itemActions').innerHTML = data.actions;
            new bootstrap.Modal(document.getElementById('itemDetailModal')).show();
        })
        .catch(error => console.error('Error:', error));
}

// Equipment functionality
function equipItem(inventoryItemId, slot) {
    fetch(`/game/inventory/equip/${inventoryItemId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ slot: slot })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            console.error('Failed to equip item:', data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Use item functionality
function useItem(inventoryItemId) {
    fetch(`/game/inventory/use/${inventoryItemId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            console.error('Failed to use item:', data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Sort inventory functionality
function sortInventory(sortBy) {
    const url = new URL(window.location);
    url.searchParams.set('sort', sortBy);
    window.location.href = url.toString();
}

// Repair all items functionality
function repairAllItems() {
    if (confirm('Repair all damaged items? This will cost gold based on item values.')) {
        fetch('/game/inventory/repair-all', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                console.error('Failed to repair items:', data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
}