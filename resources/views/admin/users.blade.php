@extends('admin.layout')

@section('title', 'User Management')
@section('page-title', 'User Management')
@section('page-description', 'Manage user accounts and admin privileges')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-users me-2"></i>
                    All Users ({{ $users->total() }})
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Player Character</th>
                                <th>Admin Status</th>
                                <th>Email Verified</th>
                                <th>Last Login</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>
                                    <code>#{{ $user->id }}</code>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <strong>{{ $user->name }}</strong>
                                        @if($user->is_admin)
                                            <span class="badge bg-warning ms-2">
                                                <i class="fas fa-crown"></i>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->player)
                                        <div>
                                            <strong>{{ $user->player->character_name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                Level {{ $user->player->level }} • {{ number_format($user->player->experience) }} XP
                                            </small>
                                        </div>
                                    @else
                                        <span class="badge bg-secondary">No Character</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->is_admin)
                                        <span class="badge bg-danger">Administrator</span>
                                    @else
                                        <span class="badge bg-success">Regular User</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->email_verified_at)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> Verified
                                        </span>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->last_login)
                                        {{ $user->last_login->diffForHumans() }}
                                    @else
                                        <span class="text-muted">Never</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        @if($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.toggle-admin', $user) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn {{ $user->is_admin ? 'btn-outline-danger' : 'btn-outline-warning' }}"
                                                        onclick="return confirm('Are you sure you want to {{ $user->is_admin ? 'remove admin privileges from' : 'grant admin privileges to' }} {{ $user->name }}?')">
                                                    @if($user->is_admin)
                                                        <i class="fas fa-user-minus"></i>
                                                        Remove Admin
                                                    @else
                                                        <i class="fas fa-user-plus"></i>
                                                        Make Admin
                                                    @endif
                                                </button>
                                            </form>
                                        @else
                                            <span class="badge bg-info">You</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if($users->hasPages())
            <div class="card-footer">
                {{ $users->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- User Statistics -->
<div class="row mt-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h3>{{ $users->where('is_admin', true)->count() }}</h3>
                <p class="mb-0">Administrators</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3>{{ $users->where('email_verified_at', '!=', null)->count() }}</h3>
                <p class="mb-0">Verified Users</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h3>{{ $users->filter(function($user) { return $user->player; })->count() }}</h3>
                <p class="mb-0">Users with Players</p>
            </div>
        </div>
    </div>
</div>
@endsection