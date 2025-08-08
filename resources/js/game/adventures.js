// Adventure category switching
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.adventure-tab');
    const categories = document.querySelectorAll('.adventure-category');

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
    
    // Initialize adventure generator form if it exists
    const adventureGeneratorForm = document.getElementById('adventure-generator-form');
    if (adventureGeneratorForm) {
        initializeAdventureGenerator();
    }
});

let selectedAdventureId = null;

// Adventure Generator Form Submission
function initializeAdventureGenerator() {
    document.getElementById('adventure-generator-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const btn = document.getElementById('generate-adventure-btn');
        const spinner = btn.querySelector('.spinner-border');
        const originalText = btn.innerHTML;

        // Show loading state
        btn.disabled = true;
        spinner.classList.remove('d-none');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Generating...';

        // Submit form data
        const formData = new FormData(this);

        // Get the route from a meta tag or use a default
        const generateRoute = document.querySelector('meta[name="generate-adventure-route"]')?.getAttribute('content') || '/game/generate-adventure';

        fetch(generateRoute, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Refresh the page to show new adventure
                window.location.reload();
            } else {
                alert('Failed to generate adventure: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while generating the adventure.');
        })
        .finally(() => {
            // Reset button state
            btn.disabled = false;
            spinner.classList.add('d-none');
            btn.innerHTML = originalText;
        });
    });
}

function selectAdventure(adventureId) {
    selectedAdventureId = adventureId;

    // Remove previous selections
    document.querySelectorAll('.adventure-item').forEach(item => {
        item.style.borderColor = 'rgba(255, 255, 255, 0.2)';
    });

    // Highlight selected adventure
    event.currentTarget.style.borderColor = 'rgba(255, 255, 255, 0.8)';

    // Enable start button
    const startBtn = document.getElementById('start-adventure-btn');
    startBtn.disabled = false;
    startBtn.classList.remove('btn-secondary');
    startBtn.classList.add('btn-success');

    console.log('Selected adventure:', adventureId);
}

function startSelectedAdventure() {
    if (selectedAdventureId) {
        // Create form and submit to start adventure
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/game/adventures/' + selectedAdventureId + '/start';

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
    } else {
        alert('Please select an adventure first!');
    }
}

function continueAdventure(adventureId) {
    window.location.href = '/game/adventure/' + adventureId + '/map';
}

// These functions need to be initialized with data from the server
function continueActiveAdventure(activeAdventures = []) {
    if (activeAdventures.length > 0) {
        const firstActiveAdventure = activeAdventures[0];
        const hasNodeMap = firstActiveAdventure.generated_map && 
                          firstActiveAdventure.generated_map.map && 
                          firstActiveAdventure.generated_map.map.nodes;
        
        if (hasNodeMap) {
            continueAdventure(firstActiveAdventure.id);
        } else {
            alert('This adventure has no node map and cannot be continued. Please abandon it and generate a new adventure.');
        }
    } else {
        alert('No active adventures to continue.');
    }
}

function abandonSelectedAdventure(activeAdventures = []) {
    if (activeAdventures.length > 0) {
        if (confirm('Are you sure you want to abandon your current adventure? All progress will be lost!')) {
            // Create form and submit to abandon adventure
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/game/adventures/' + activeAdventures[0].id + '/abandon';

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
    } else {
        alert('No active adventures to abandon.');
    }
}