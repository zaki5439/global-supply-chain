<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Port Management - Admin Panel</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .header-section {
            background: linear-gradient(135deg, rgba(102,126,234,0.1) 0%, rgba(118,75,162,0.1) 100%);
            border-bottom: 1px solid rgba(102,126,234,0.2);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }
    </style>
</head>
<body>
<div class="d-flex">
    @include('admin.sidebar')
    
    <main class="flex-grow-1 p-4">
        <div class="header-section d-flex justify-content-between align-items-center">
            <div>
                <h2 style="color: #1a1d2e; font-weight: 700;"><i class="bi bi-geo-alt me-2"></i>Port Management</h2>
                <p class="text-muted mb-0">Manage global port dataset for risk analysis</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPortModal">
                <i class="bi bi-plus-circle me-1"></i> Add New Port
            </button>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>UNLOCODE</th>
                            <th>Port Name</th>
                            <th>Country</th>
                            <th>Status</th>
                            <th>Coordinates</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ports as $port)
                        <tr>
                            <td><span class="badge bg-secondary">{{ $port->unlocode }}</span></td>
                            <td class="fw-bold">{{ $port->name }}</td>
                            <td>{{ $port->country->name ?? '-' }}</td>
                            <td>
                                @if($port->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($port->status === 'congested')
                                    <span class="badge bg-warning text-dark">Congested</span>
                                @else
                                    <span class="badge bg-danger">Closed</span>
                                @endif
                            </td>
                            <td>{{ $port->latitude ?? '-' }}, {{ $port->longitude ?? '-' }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editPortModal{{ $port->id }}">Edit</button>
                                <form action="{{ route('ports.destroy', $port->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editPortModal{{ $port->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="{{ route('ports.update', $port->id) }}" method="POST" class="modal-content">
                                    @csrf @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Port</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label>Name</label>
                                            <input type="text" name="name" class="form-control" value="{{ $port->name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>UNLOCODE</label>
                                            <input type="text" name="unlocode" class="form-control" value="{{ $port->unlocode }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Country</label>
                                            <select name="country_id" class="form-select" required>
                                                @foreach($countries as $c)
                                                    <option value="{{ $c->id }}" {{ $port->country_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label>Status</label>
                                            <select name="status" class="form-select" required>
                                                <option value="active" {{ $port->status == 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="congested" {{ $port->status == 'congested' ? 'selected' : '' }}>Congested</option>
                                                <option value="closed" {{ $port->status == 'closed' ? 'selected' : '' }}>Closed</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No ports found in database.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                {{ $ports->links() }}
            </div>
        </div>
    </main>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addPortModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('ports.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add New Port</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>UNLOCODE</label>
                    <input type="text" name="unlocode" class="form-control" placeholder="e.g. SGSIN" required>
                </div>
                <div class="mb-3">
                    <label>Country</label>
                    <select name="country_id" class="form-select" required>
                        @foreach($countries as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label>Status</label>
                    <select name="status" class="form-select" required>
                        <option value="active">Active</option>
                        <option value="congested">Congested</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Add Port</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
