// Stat allocation functionality
let allocatedPoints = {
    str: 0, dex: 0, con: 0, int: 0, wis: 0, cha: 0
};

// These will be populated from the blade template
let maxPoints = 0;
let baseStat = {};

function adjustStat(stat, change) {
    const currentAllocated = allocatedPoints[stat];
    const totalAllocated = Object.values(allocatedPoints).reduce((sum, val) => sum + val, 0);
    
    if (change > 0 && totalAllocated >= maxPoints) {
        return; // Cannot allocate more points
    }
    
    if (change < 0 && currentAllocated <= 0) {
        return; // Cannot reduce below 0
    }
    
    allocatedPoints[stat] += change;
    updateUI();
}

function updateUI() {
    const totalAllocated = Object.values(allocatedPoints).reduce((sum, val) => sum + val, 0);
    const remainingPoints = maxPoints - totalAllocated;
    
    // Update remaining points display
    const remainingPointsEl = document.getElementById('remaining-points');
    if (remainingPointsEl) {
        remainingPointsEl.textContent = remainingPoints;
    }
    
    // Update each stat
    Object.keys(allocatedPoints).forEach(stat => {
        const allocated = allocatedPoints[stat];
        const total = baseStat[stat] + allocated;
        
        // Update displays
        const totalEl = document.getElementById(`total-${stat}`);
        const allocatedEl = document.getElementById(`allocated-${stat}`);
        const inputEl = document.getElementById(`${stat}-points-input`);
        
        if (totalEl) totalEl.textContent = total;
        if (allocatedEl) allocatedEl.textContent = allocated;
        if (inputEl) inputEl.value = allocated;
        
        // Update buttons
        const minusBtn = document.getElementById(`minus-${stat}`);
        const plusBtn = document.getElementById(`plus-${stat}`);
        
        if (minusBtn) minusBtn.disabled = allocated <= 0;
        if (plusBtn) plusBtn.disabled = remainingPoints <= 0;
    });
    
    // Update allocate button
    const allocateBtn = document.getElementById('allocate-btn');
    if (allocateBtn) {
        allocateBtn.disabled = totalAllocated === 0;
    }
}

function resetAllocation() {
    Object.keys(allocatedPoints).forEach(stat => {
        allocatedPoints[stat] = 0;
    });
    updateUI();
}

function allocateStats() {
    const form = document.getElementById('allocate-stats-form');
    const totalAllocated = Object.values(allocatedPoints).reduce((sum, val) => sum + val, 0);
    
    if (totalAllocated === 0) {
        alert('Please allocate at least one stat point before submitting.');
        return;
    }
    
    // Submit the form
    form.submit();
}

function unequipPlayerItem(itemId) {
    // Create form to unequip item
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/game/player-item/unequip/' + itemId;
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken.getAttribute('content');
        form.appendChild(csrfInput);
    }
    
    document.body.appendChild(form);
    form.submit();
}

function showChangeGenderModal() {
    const modal = new bootstrap.Modal(document.getElementById('genderChangeModal'));
    modal.show();
}

function changeGender(newGender) {
    // Create form to change gender
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/game/character/change-gender';
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken.getAttribute('content');
        form.appendChild(csrfInput);
    }
    
    const genderInput = document.createElement('input');
    genderInput.type = 'hidden';
    genderInput.name = 'gender';
    genderInput.value = newGender;
    form.appendChild(genderInput);
    
    document.body.appendChild(form);
    form.submit();
}

function equipFromCharacterPage(itemId) {
    console.log('Attempting to equip item:', itemId);
    
    // Create form to equip item
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/game/player-item/equip/' + itemId;
    
    console.log('Form action:', form.action);
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken.getAttribute('content');
        form.appendChild(csrfInput);
        console.log('CSRF token added');
    } else {
        console.error('CSRF token not found');
    }
    
    document.body.appendChild(form);
    console.log('Submitting form...');
    form.submit();
}

function switchInventoryTab(category, tabElement) {
    // Remove active class from all tabs
    document.querySelectorAll('.inventory-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Add active class to clicked tab
    tabElement.classList.add('active');
    
    // Hide all inventory categories
    document.querySelectorAll('.inventory-category').forEach(cat => {
        cat.classList.remove('active');
    });
    
    // Show selected category
    const targetCategory = document.querySelector(`[data-category="${category}"].inventory-category`);
    if (targetCategory) {
        targetCategory.classList.add('active');
    }
}

// Initialize the character page functionality
function initializeCharacterPage(playerData) {
    maxPoints = playerData.maxPoints;
    baseStat = playerData.baseStat;
    
    if (maxPoints > 0) {
        updateUI();
    }
    
    // Tooltip functionality for equipment slots
    const equipmentSlots = document.querySelectorAll('.equipment-slot');
    // Additional initialization can be added here
}

// Export functions for global use
window.adjustStat = adjustStat;
window.resetAllocation = resetAllocation;
window.allocateStats = allocateStats;
window.unequipPlayerItem = unequipPlayerItem;
window.showChangeGenderModal = showChangeGenderModal;
window.changeGender = changeGender;
window.equipFromCharacterPage = equipFromCharacterPage;
window.switchInventoryTab = switchInventoryTab;
window.initializeCharacterPage = initializeCharacterPage;