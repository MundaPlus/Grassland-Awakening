<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <x-selected-theme />
    
    <title>{{ config('app.name', 'Grassland Awakening') }} - @yield('title', 'Game')</title>
    
    <!-- Accessibility meta tags -->
    <meta name="description" content="@yield('meta_description', 'Grassland Awakening - A fantasy RPG with accessibility features')">
    <meta name="theme-color" content="#4f46e5">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS for accessibility -->
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #6b7280;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --text-high-contrast: #111827;
            --text-medium-contrast: #374151;
            --text-low-contrast: #6b7280;
            --bg-primary: #ffffff;
            --bg-secondary: #f9fafb;
            --bg-accent: #f3f4f6;
            --border-color: #d1d5db;
            --focus-ring: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        /* High contrast mode support */
        @media (prefers-contrast: high) {
            :root {
                --text-high-contrast: #000000;
                --text-medium-contrast: #000000;
                --bg-primary: #ffffff;
                --border-color: #000000;
            }
        }
        
        /* Dark mode support */
        .dark {
            --text-high-contrast: #f9fafb;
            --text-medium-contrast: #e5e7eb;
            --text-low-contrast: #9ca3af;
            --bg-primary: #111827;
            --bg-secondary: #1f2937;
            --bg-accent: #374151;
            --border-color: #4b5563;
            
            /* Bootstrap CSS variables override */
            --bs-body-bg: #111827 !important;
            --bs-body-color: #f9fafb !important;
            --bs-border-color: #4b5563 !important;
            --bs-secondary-bg: #1f2937 !important;
            --bs-tertiary-bg: #374151 !important;
        }
        
        html.dark,
        .dark body {
            background-color: var(--bg-primary) !important;
            color: var(--text-high-contrast);
        }
        
        .dark .container,
        .dark .container-fluid {
            background-color: transparent;
        }
        
        .dark .navbar {
            background-color: var(--bg-secondary) !important;
            border-color: var(--border-color);
        }
        
        .dark .navbar-brand, .dark .nav-link {
            color: var(--text-high-contrast) !important;
        }
        
        .dark .card {
            background-color: var(--bg-secondary);
            border-color: var(--border-color);
            color: var(--text-high-contrast);
        }
        
        .dark .card-header {
            background-color: var(--bg-accent);
            border-color: var(--border-color);
        }
        
        .dark .text-muted {
            color: var(--text-low-contrast) !important;
        }
        
        .dark .btn-outline-secondary {
            color: var(--text-medium-contrast);
            border-color: var(--border-color);
        }
        
        .dark .btn-outline-secondary:hover {
            background-color: var(--bg-accent);
            color: var(--text-high-contrast);
        }
        
        /* Additional dark mode fixes */
        .dark .nav-tabs .nav-link.active {
            background-color: var(--bg-secondary) !important;
            border-color: var(--border-color) !important;
            color: var(--text-high-contrast) !important;
        }
        
        .dark .nav-tabs .nav-link {
            color: var(--text-medium-contrast) !important;
            border-color: var(--border-color) !important;
        }
        
        .dark .tab-content {
            background-color: var(--bg-secondary);
            color: var(--text-high-contrast);
        }
        
        .dark .modal-content {
            background-color: var(--bg-secondary);
            color: var(--text-high-contrast);
            border-color: var(--border-color);
        }
        
        .dark .modal-header {
            background-color: var(--bg-accent);
            border-color: var(--border-color);
        }
        
        .dark .form-control {
            background-color: var(--bg-accent);
            border-color: var(--border-color);
            color: var(--text-high-contrast);
        }
        
        .dark .form-control:focus {
            background-color: var(--bg-accent);
            border-color: var(--primary-color);
            color: var(--text-high-contrast);
        }
        
        /* Force dark background for common Bootstrap components */
        .dark .card-body,
        .dark .card-footer,
        .dark .list-group-item {
            background-color: var(--bg-secondary) !important;
            color: var(--text-high-contrast) !important;
        }
        
        .dark .main,
        .dark main {
            background-color: var(--bg-primary) !important;
        }
        
        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
        
        /* Enhanced focus indicators */
        .btn:focus, .form-control:focus, .form-select:focus, a:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: 2px;
            box-shadow: var(--focus-ring);
        }
        
        /* Skip to content link */
        .skip-link {
            position: absolute;
            top: -40px;
            left: 6px;
            background: var(--primary-color);
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 0 0 4px 4px;
            z-index: 1000;
            font-weight: 600;
        }
        
        .skip-link:focus {
            top: 0;
            color: white;
        }
        
        /* Game-specific styles */
        .stat-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
        }
        
        .weather-indicator {
            font-size: 1.25rem;
            margin-right: 0.5rem;
        }
        
        .reputation-bar {
            height: 8px;
            background: var(--bg-accent);
            border-radius: 4px;
            overflow: hidden;
        }
        
        .reputation-progress {
            height: 100%;
            transition: width 0.3s ease;
        }
        
        .achievement-icon {
            font-size: 2rem;
            margin-right: 0.75rem;
        }
        
        /* Screen reader only content */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
        
        /* High contrast borders for cards */
        .card {
            border: 2px solid var(--border-color);
        }
        
        /* Large click targets */
        .btn, .nav-link {
            min-height: 44px;
            display: flex;
            align-items: center;
        }
        
        /* Status indicators with both color and shape */
        .status-online::before {
            content: "●";
            color: var(--success-color);
            margin-right: 0.5rem;
        }
        
        .status-offline::before {
            content: "○";
            color: var(--danger-color);
            margin-right: 0.5rem;
        }
        
        /* Progress bars with patterns for accessibility */
        .progress-bar {
            position: relative;
        }
        
        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: repeating-linear-gradient(45deg, transparent, transparent 4px, rgba(255,255,255,0.2) 4px, rgba(255,255,255,0.2) 8px);
        }
        
        /* Sticky footer styles */
        html, body {
            height: 100%;
        }
        
        body {
            display: flex;
            flex-direction: column;
        }
        
        main {
            flex: 1 0 auto;
        }
        
        footer {
            flex-shrink: 0;
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-light">
    <!-- Skip to content link -->
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary" role="navigation" aria-label="Main navigation">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('game.dashboard') }}" aria-label="Grassland Awakening Home">
                <img src="{{ asset('img/logo.png') }}" alt="Grassland Awakening Logo" class="me-2" style="height: 32px; width: auto;">
                Grassland Awakening
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('game.dashboard') ? 'active' : '' }}" 
                           href="{{ route('game.dashboard') }}" 
                           aria-current="{{ request()->routeIs('game.dashboard') ? 'page' : 'false' }}">
                            🏠 Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('game.character') ? 'active' : '' }}" 
                           href="{{ route('game.character') }}"
                           aria-current="{{ request()->routeIs('game.character') ? 'page' : 'false' }}">
                            👤 Character
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('game.inventory') ? 'active' : '' }}" 
                           href="{{ route('game.inventory') }}"
                           aria-current="{{ request()->routeIs('game.inventory') ? 'page' : 'false' }}">
                            🎒 Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('game.village') ? 'active' : '' }}" 
                           href="{{ route('game.village') }}"
                           aria-current="{{ request()->routeIs('game.village') ? 'page' : 'false' }}">
                            🏘️ Village
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('game.adventures*') ? 'active' : '' }}" 
                           href="{{ route('game.adventures') }}"
                           aria-current="{{ request()->routeIs('game.adventures*') ? 'page' : 'false' }}">
                            🗺️ Adventures
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('game.achievements') ? 'active' : '' }}" 
                           href="{{ route('game.achievements') }}"
                           aria-current="{{ request()->routeIs('game.achievements') ? 'page' : 'false' }}">
                            🏆 Achievements
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('game.reputation') ? 'active' : '' }}" 
                           href="{{ route('game.reputation') }}"
                           aria-current="{{ request()->routeIs('game.reputation') ? 'page' : 'false' }}">
                            ⚖️ Reputation
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <button class="nav-link btn btn-link p-2" id="theme-toggle" type="button" aria-label="Toggle dark mode">
                            <svg class="d-none" id="theme-toggle-dark-icon" width="16" height="16" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                            </svg>
                            <svg class="d-none" id="theme-toggle-light-icon" width="16" height="16" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" 
                           aria-expanded="false" aria-label="User menu">
                            👤 {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('frontend.users.profile') }}">Profile</a></li>
                            <li><a class="dropdown-item" href="{{ route('frontend.users.changePassword') }}">Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main content -->
    <main id="main-content" role="main" tabindex="-1">
        <div class="container-fluid py-4">
            <!-- Flash messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" aria-live="polite">
                    <span aria-label="Success">✅</span> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert" aria-live="polite">
                    <span aria-label="Error">❌</span> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close alert"></button>
                </div>
            @endif
            
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert" aria-live="polite">
                    <span aria-label="Warning">⚠️</span> {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close alert"></button>
                </div>
            @endif
            
            <!-- Page content -->
            @yield('content')
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5" role="contentinfo">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2024 Grassland Awakening. Built with accessibility in mind.</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="#" class="text-light" onclick="document.documentElement.scrollTop = 0; return false;">
                        Back to Top ↑
                    </a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Accessibility enhancements -->
    <script>
        // Announce page changes to screen readers
        function announcePageChange(message) {
            const announcement = document.createElement('div');
            announcement.setAttribute('aria-live', 'polite');
            announcement.setAttribute('aria-atomic', 'true');
            announcement.className = 'sr-only';
            announcement.textContent = message;
            document.body.appendChild(announcement);
            setTimeout(() => document.body.removeChild(announcement), 1000);
        }
        
        // Enhanced keyboard navigation
        document.addEventListener('keydown', function(e) {
            // Alt + H: Go to homepage
            if (e.altKey && e.key === 'h') {
                e.preventDefault();
                window.location.href = "{{ route('game.dashboard') }}";
            }
            
            // Alt + V: Go to village
            if (e.altKey && e.key === 'v') {
                e.preventDefault();
                window.location.href = "{{ route('game.village') }}";
            }
            
            // Alt + A: Go to adventures
            if (e.altKey && e.key === 'a') {
                e.preventDefault();
                window.location.href = "{{ route('game.adventures') }}";
            }
            
            // Escape: Close modals or return to main content
            if (e.key === 'Escape') {
                const modal = document.querySelector('.modal.show');
                if (modal) {
                    bootstrap.Modal.getInstance(modal).hide();
                } else {
                    document.getElementById('main-content').focus();
                }
            }
        });
        
        // Announce successful form submissions
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                announcePageChange("{{ session('success') }}");
            @endif
            
            @if(session('error'))
                announcePageChange("Error: {{ session('error') }}");
            @endif
            
            // Level up notifications
            @if(session('combat_level_up') || session('npc_level_up'))
                @php
                    $levelUpData = session('combat_level_up') ?? session('npc_level_up');
                    // Clear the session data immediately after reading it
                    session()->forget(['combat_level_up', 'npc_level_up']);
                @endphp
                @if(!empty($levelUpData['levels_gained']))
                    @foreach($levelUpData['levels_gained'] as $levelGain)
                        GameUI.showToast('🎉 Level Up! You are now level {{ $levelGain["new_level"] }}!', 'success');
                    @endforeach
                    @if($levelUpData['can_allocate_stats'])
                        setTimeout(() => {
                            // Only show stat allocation modal if we haven't shown it this session
                            if (!sessionStorage.getItem('stat_points_modal_shown_' + {{ auth()->id() }})) {
                                sessionStorage.setItem('stat_points_modal_shown_' + {{ auth()->id() }}, 'true');
                                GameUI.showConfirmModal(
                                    'Stat Points Available!', 
                                    'You have unallocated stat points. Would you like to go to your character sheet to allocate them?',
                                    function() {
                                        window.location.href = '{{ route("game.character") }}';
                                    }
                                );
                            }
                        }, 2000);
                    @endif
                @endif
            @endif
        });
        
        // Add role and aria-label to progress bars
        document.querySelectorAll('.progress-bar').forEach(function(bar) {
            bar.setAttribute('role', 'progressbar');
            const value = bar.style.width || bar.getAttribute('style');
            if (value) {
                const percent = value.match(/width:\s*(\d+)%/);
                if (percent) {
                    bar.setAttribute('aria-valuenow', percent[1]);
                    bar.setAttribute('aria-valuemin', '0');
                    bar.setAttribute('aria-valuemax', '100');
                    bar.setAttribute('aria-label', `Progress: ${percent[1]}%`);
                }
            }
        });
        
        // Theme toggle functionality
        function setInitialTheme() {
            if (
                localStorage.getItem('color-theme') === 'dark' ||
                (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
            ) {
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.setAttribute('data-bs-theme', 'light');
            }
        }

        function updateThemeToggleIcons() {
            const darkIcon = document.getElementById('theme-toggle-dark-icon');
            const lightIcon = document.getElementById('theme-toggle-light-icon');
            if (!darkIcon || !lightIcon) return;

            if (
                localStorage.getItem('color-theme') === 'dark' ||
                (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
            ) {
                lightIcon.classList.remove('d-none');
                darkIcon.classList.add('d-none');
            } else {
                darkIcon.classList.remove('d-none');
                lightIcon.classList.add('d-none');
            }
        }

        // Initialize theme
        setInitialTheme();
        updateThemeToggleIcons();

        // Add click event listener for theme toggle
        const themeToggleBtn = document.getElementById('theme-toggle');
        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', function () {
                // Toggle theme
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    document.documentElement.setAttribute('data-bs-theme', 'light');
                    localStorage.setItem('color-theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    document.documentElement.setAttribute('data-bs-theme', 'dark');
                    localStorage.setItem('color-theme', 'dark');
                }
                updateThemeToggleIcons();
            });
        }

        // Global modal and toast utilities
        window.GameUI = {
            showConfirmModal: function(title, message, confirmCallback) {
                let modal = document.getElementById('globalConfirmationModal');
                if (!modal) {
                    // Create modal if it doesn't exist
                    document.body.insertAdjacentHTML('beforeend', `
                        <div class="modal fade" id="globalConfirmationModal" tabindex="-1" aria-labelledby="globalConfirmationModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="globalConfirmationModalLabel">Confirm Action</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body" id="globalConfirmationModalBody">
                                        Are you sure you want to proceed?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-primary" id="globalConfirmationModalConfirm">Confirm</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                    modal = document.getElementById('globalConfirmationModal');
                }
                
                document.getElementById('globalConfirmationModalLabel').textContent = title;
                document.getElementById('globalConfirmationModalBody').textContent = message;
                
                const confirmBtn = document.getElementById('globalConfirmationModalConfirm');
                confirmBtn.onclick = function() {
                    bootstrap.Modal.getInstance(modal).hide();
                    confirmCallback();
                };
                
                new bootstrap.Modal(modal).show();
            },

            showErrorModal: function(message) {
                let modal = document.getElementById('globalErrorModal');
                if (!modal) {
                    document.body.insertAdjacentHTML('beforeend', `
                        <div class="modal fade" id="globalErrorModal" tabindex="-1" aria-labelledby="globalErrorModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="globalErrorModalLabel">Error</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body" id="globalErrorModalBody">
                                        An error occurred. Please try again.
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                    modal = document.getElementById('globalErrorModal');
                }
                
                document.getElementById('globalErrorModalBody').textContent = message;
                new bootstrap.Modal(modal).show();
            },

            showToast: function(message, type = 'success') {
                let container = document.getElementById('globalToastContainer');
                if (!container) {
                    document.body.insertAdjacentHTML('beforeend', 
                        '<div class="toast-container position-fixed bottom-0 end-0 p-3" id="globalToastContainer"></div>'
                    );
                    container = document.getElementById('globalToastContainer');
                }
                
                const toastId = 'toast-' + Date.now();
                const bgClass = type === 'success' ? 'bg-success' : (type === 'error' ? 'bg-danger' : 'bg-info');
                
                const toastHtml = `
                    <div id="${toastId}" class="toast ${bgClass} text-white" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-body">
                            ${message}
                        </div>
                    </div>
                `;
                
                container.insertAdjacentHTML('beforeend', toastHtml);
                
                const toastElement = document.getElementById(toastId);
                const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
                toast.show();
                
                toastElement.addEventListener('hidden.bs.toast', () => {
                    toastElement.remove();
                });
            }
        };
    </script>
    
    @stack('scripts')
</body>
</html>