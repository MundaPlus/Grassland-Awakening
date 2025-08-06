@extends('admin.layout')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')
@section('page-description', 'Overview of your game platform')

@section('content')
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stat-card p-4">
            <div class="d-flex align-items-center">
                <div class="stat-icon me-3">
                    <i class="fas fa-users fa-3x opacity-75"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ number_format($stats['total_users']) }}</h3>
                    <p class="mb-0">Total Users</p>
                    <small class="opacity-75">{{ $stats['admin_users'] }} admins</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="stat-card p-4">
            <div class="d-flex align-items-center">
                <div class="stat-icon me-3">
                    <i class="fas fa-user-shield fa-3x opacity-75"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ number_format($stats['active_players']) }}</h3>
                    <p class="mb-0">Active Players</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="stat-card p-4">
            <div class="d-flex align-items-center">
                <div class="stat-icon me-3">
                    <i class="fas fa-sword fa-3x opacity-75"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ number_format($stats['total_items']) }}</h3>
                    <p class="mb-0">Total Items</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="stat-card p-4">
            <div class="d-flex align-items-center">
                <div class="stat-icon me-3">
                    <i class="fas fa-map fa-3x opacity-75"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ number_format($stats['active_adventures']) }}</h3>
                    <p class="mb-0">Active Adventures</p>
                    <small class="opacity-75">{{ $stats['completed_adventures'] }} completed</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user-plus me-2"></i>
                    Recent Users
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Player</th>
                                <th>Admin</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentUsers as $user)
                            <tr>
                                <td>
                                    <strong>{{ $user->name }}</strong>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->player)
                                        <span class="badge bg-success">
                                            {{ $user->player->character_name }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">No Player</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->is_admin)
                                        <span class="badge bg-warning">
                                            <i class="fas fa-crown"></i> Admin
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark">User</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-trophy me-2"></i>
                    Top Players
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Character</th>
                                <th>User</th>
                                <th>Level</th>
                                <th>Experience</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPlayers as $player)
                            <tr>
                                <td>
                                    <strong>{{ $player->character_name }}</strong>
                                </td>
                                <td>{{ $player->user->name }}</td>
                                <td>
                                    <span class="badge bg-primary">
                                        Level {{ $player->level }}
                                    </span>
                                </td>
                                <td>{{ number_format($player->experience) }} XP</td>
                                <td>{{ $player->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-server me-2"></i>
                    System Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-3">
                        <strong>Laravel Version:</strong><br>
                        <span class="text-muted">{{ $systemInfo['laravel_version'] }}</span>
                    </div>
                    <div class="col-6 mb-3">
                        <strong>PHP Version:</strong><br>
                        <span class="text-muted">{{ $systemInfo['php_version'] }}</span>
                    </div>
                    <div class="col-6 mb-3">
                        <strong>Server:</strong><br>
                        <span class="text-muted">{{ $systemInfo['server_software'] }}</span>
                    </div>
                    <div class="col-6 mb-3">
                        <strong>Database:</strong><br>
                        <span class="text-muted">{{ strtoupper($systemInfo['database_type']) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-tools me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.users') }}" class="btn btn-primary">
                        <i class="fas fa-users me-2"></i>
                        Manage Users
                    </a>
                    <a href="{{ route('admin.players') }}" class="btn btn-success">
                        <i class="fas fa-user-shield me-2"></i>
                        Manage Players
                    </a>
                    <a href="{{ route('admin.items') }}" class="btn btn-warning">
                        <i class="fas fa-sword me-2"></i>
                        Manage Items
                    </a>
                    <a href="{{ route('admin.adventures') }}" class="btn btn-info">
                        <i class="fas fa-map me-2"></i>
                        View Adventures
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection