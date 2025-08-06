<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - {{ config('app.name') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <x-selected-theme />
    
    <style>
        /* Light mode styles */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
            border-right: 1px solid #dee2e6;
        }
        
        .sidebar .nav-link {
            color: #495057;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 0.25rem;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover {
            background-color: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
        }
        
        .sidebar .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }
        
        .main-content {
            background-color: #ffffff;
            min-height: 100vh;
        }
        
        /* Dark mode styles */
        .dark .sidebar {
            background: linear-gradient(180deg, #1a2332 0%, #2c3e50 100%);
            border-right: 1px solid #495057;
        }
        
        .dark .sidebar .nav-link {
            color: #ecf0f1;
        }
        
        .dark .sidebar .nav-link:hover {
            background-color: rgba(52, 152, 219, 0.2);
            color: #3498db;
        }
        
        .dark .sidebar .nav-link.active {
            background-color: #3498db;
            color: white;
        }
        
        .dark .main-content {
            background-color: #212529;
        }
        
        /* Apply dark theme to html when dark class is present */
        .dark {
            --bs-body-bg: #212529 !important;
            --bs-body-color: #ffffff !important;
            --bs-border-color: #495057 !important;
            --bs-secondary-bg: #343a40 !important;
            --bs-tertiary-bg: #495057 !important;
        }
        
        .dark body {
            background-color: var(--bs-body-bg) !important;
            color: var(--bs-body-color);
        }
        
        .dark .card {
            background-color: var(--bs-secondary-bg);
            border-color: var(--bs-border-color);
            color: var(--bs-body-color);
        }
        
        .dark .card-header {
            background-color: var(--bs-tertiary-bg);
            border-color: var(--bs-border-color);
            color: var(--bs-body-color);
        }
        
        .dark .form-control {
            background-color: var(--bs-secondary-bg);
            border-color: var(--bs-border-color);
            color: var(--bs-body-color);
        }
        
        .dark .form-control:focus {
            background-color: var(--bs-secondary-bg);
            border-color: #0d6efd;
            color: var(--bs-body-color);
        }
        
        .dark .form-select {
            background-color: var(--bs-secondary-bg);
            border-color: var(--bs-border-color);
            color: var(--bs-body-color);
        }
        
        .dark .modal-content {
            background-color: var(--bs-secondary-bg);
            color: var(--bs-body-color);
            border-color: var(--bs-border-color);
        }
        
        .dark .modal-header {
            background-color: var(--bs-tertiary-bg);
            border-color: var(--bs-border-color);
        }
        
        .dark .table {
            --bs-table-bg: var(--bs-secondary-bg);
            --bs-table-color: var(--bs-body-color);
        }
        
        .dark .table th,
        .dark .table td {
            border-color: var(--bs-border-color);
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            color: white;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .table-dark {
            --bs-table-bg: #2c3e50;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 2rem 0;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="p-3">
                    <h4 class="text-white mb-4">
                        <i class="fas fa-crown me-2"></i>
                        Admin Panel
                    </h4>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Dashboard
                        </a>
                        
                        <a class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}" href="{{ route('admin.users') }}">
                            <i class="fas fa-users me-2"></i>
                            Users
                        </a>
                        
                        <a class="nav-link {{ request()->routeIs('admin.players*') ? 'active' : '' }}" href="{{ route('admin.players') }}">
                            <i class="fas fa-user-shield me-2"></i>
                            Players
                        </a>
                        
                        <a class="nav-link {{ request()->routeIs('admin.items*') ? 'active' : '' }}" href="{{ route('admin.items') }}">
                            <i class="fas fa-sword me-2"></i>
                            Items
                        </a>
                        
                        <a class="nav-link {{ request()->routeIs('admin.adventures*') ? 'active' : '' }}" href="{{ route('admin.adventures') }}">
                            <i class="fas fa-map me-2"></i>
                            Adventures
                        </a>
                        
                        <a class="nav-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}" href="{{ route('admin.settings') }}">
                            <i class="fas fa-cogs me-2"></i>
                            Settings
                        </a>
                        
                        <a class="nav-link {{ request()->routeIs('admin.qa-testing*') ? 'active' : '' }}" href="{{ route('admin.qa-testing') }}">
                            <i class="fas fa-flask me-2"></i>
                            QA Testing
                        </a>
                        
                        <hr class="text-muted">
                        
                        <a class="nav-link" href="{{ route('game.dashboard') }}">
                            <i class="fas fa-gamepad me-2"></i>
                            Back to Game
                        </a>
                        
                        <a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            Logout
                        </a>
                        
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Header -->
                <div class="admin-header">
                    <div class="container-fluid">
                        <div class="row align-items-center">
                            <div class="col">
                                <h1 class="h3 mb-0">@yield('page-title', 'Admin Dashboard')</h1>
                                <p class="mb-0 opacity-75">@yield('page-description', 'Manage your game platform')</p>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-success fs-6">
                                    <i class="fas fa-user-shield me-1"></i>
                                    {{ auth()->user()->name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="container-fluid py-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Theme initialization -->
    <script>
        // Initialize theme immediately
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
        
        // Set theme on page load
        setInitialTheme();
        
        // Listen for theme changes (if switched from main game)
        window.addEventListener('storage', function(e) {
            if (e.key === 'color-theme') {
                setInitialTheme();
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>