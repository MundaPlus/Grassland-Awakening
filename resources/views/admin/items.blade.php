@extends('admin.layout')

@section('title', 'Item Management')
@section('page-title', 'Item Management')
@section('page-description', 'Create and manage game items')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-plus me-2"></i>
                    Create New Item
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.items.create') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select" required>
                                <option value="">Select Type</option>
                                @foreach($itemTypes as $type)
                                <option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-6 mb-3">
                            <label class="form-label">Rarity</label>
                            <select name="rarity" class="form-select" required>
                                @foreach($rarities as $rarity)
                                <option value="{{ $rarity }}">{{ ucfirst($rarity) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Value</label>
                            <input type="number" name="base_value" class="form-control" min="0" required>
                        </div>
                        
                        <div class="col-6 mb-3">
                            <label class="form-label">Level Req.</label>
                            <input type="number" name="level_requirement" class="form-control" min="1">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Subtype</label>
                        <input type="text" name="subtype" class="form-control">
                    </div>
                    
                    <div class="form-check mb-3">
                        <input type="checkbox" name="is_stackable" class="form-check-input" id="stackable">
                        <label class="form-check-label" for="stackable">
                            Stackable
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-plus me-2"></i>
                        Create Item
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-sword me-2"></i>
                    All Items ({{ $items->total() }})
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Rarity</th>
                                <th>Value</th>
                                <th>Level</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->name }}</strong>
                                    @if($item->subtype)
                                        <br><small class="text-muted">{{ ucfirst($item->subtype) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ ucfirst(str_replace('_', ' ', $item->type)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge 
                                        @if($item->rarity === 'common') bg-secondary
                                        @elseif($item->rarity === 'uncommon') bg-success
                                        @elseif($item->rarity === 'rare') bg-primary
                                        @elseif($item->rarity === 'epic') bg-warning
                                        @elseif($item->rarity === 'legendary') bg-danger
                                        @endif">
                                        {{ ucfirst($item->rarity) }}
                                    </span>
                                </td>
                                <td>{{ number_format($item->base_value) }} gold</td>
                                <td>{{ $item->level_requirement ?? 'Any' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.items.delete', $item) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Are you sure you want to delete {{ $item->name }}?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if($items->hasPages())
            <div class="card-footer">
                {{ $items->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection