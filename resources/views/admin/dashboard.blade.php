<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - Risk Intelligence</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body { 
            background: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: 0.3s;
        }
        .card:hover { transform: translateY(-3px); }
        
        /* Custom Scrollbar for tables/logs */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
        
        .activity-item {
            border-left: 3px solid #667eea;
            padding-left: 15px;
            margin-bottom: 15px;
            position: relative;
        }
        .activity-item::before {
            content: '';
            position: absolute;
            left: -7px;
            top: 5px;
            width: 11px;
            height: 11px;
            border-radius: 50%;
            background: #fff;
            border: 3px solid #667eea;
        }
        .activity-item.danger { border-left-color: #dc3545; }
        .activity-item.danger::before { border-color: #dc3545; }
        .activity-item.success { border-left-color: #28a745; }
        .activity-item.success::before { border-color: #28a745; }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- Sidebar -->
    @include('admin.sidebar')

    <!-- Main Content -->
    <div class="flex-grow-1 p-4" style="height: 100vh; overflow-y: auto;">
        
        <!-- Flash Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark">Enterprise Back-Office</h2>
            <div>
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-person-plus me-1"></i> Add New User
                </button>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card p-4 bg-white h-100">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-2">Total Users</h6>
                            <h3 class="fw-bold mb-0">{{ $totalUsers ?? 0 }}</h3>
                        </div>
                        <div class="fs-1 text-primary"><i class="bi bi-people-fill"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 bg-white h-100">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-2">Admins / Analysts</h6>
                            <h3 class="fw-bold mb-0">{{ $admins ?? 0 }} / {{ $analysts ?? 0 }}</h3>
                        </div>
                        <div class="fs-1 text-success"><i class="bi bi-shield-lock"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 bg-white h-100">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-2">System Status</h6>
                            <h3 class="fw-bold mb-0 text-success">Healthy</h3>
                        </div>
                        <div class="fs-1 text-success"><i class="bi bi-check-circle"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <!-- User Analytics Chart -->
            <div class="col-md-8">
                <div class="card p-4 h-100">
                    <h5 class="fw-bold mb-4"><i class="bi bi-graph-up text-primary me-2"></i>User Growth Analytics</h5>
                    <div style="position: relative; height: 300px;">
                        <canvas id="userGrowthChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- System Health & API Monitoring -->
            <div class="col-md-4" id="system-health">
                <div class="card p-4 h-100">
                    <h5 class="fw-bold mb-4"><i class="bi bi-activity text-danger me-2"></i>API Monitoring</h5>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold">News RSS Services</span>
                            <span class="badge bg-success rounded-pill">Online</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                        </div>
                        <small class="text-muted">Latency: {{ $systemHealth['news_api']['latency'] ?? '45ms' }}</small>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold">Currency Exchange API</span>
                            <span class="badge bg-success rounded-pill">Online</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                        </div>
                        <small class="text-muted">Latency: {{ $systemHealth['currency_api']['latency'] ?? '120ms' }}</small>
                    </div>

                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold">World Port Index</span>
                            <span class="badge bg-success rounded-pill">Synced</span>
                        </div>
                        <small class="text-muted">Last Update: {{ $systemHealth['port_dataset']['last_sync'] ?? '2 hours ago' }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Management Sections -->
        <div class="row">
            <!-- User Management -->
            <div class="col-md-8 mb-4" id="user-management">
                <div class="card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-person-gear text-primary me-2"></i>User Access Control</h5>
                        <div class="input-group" style="width: 250px;">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control border-start-0 bg-light" placeholder="Search users..." id="searchUser">
                        </div>
                    </div>
                    
                    <div class="table-responsive" style="max-height: 400px;">
                        <table class="table table-hover align-middle">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="userTableBody">
                                @foreach($users as $user)
                                <tr id="user-row-{{ $user->id }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $user->name }}</div>
                                                <small class="text-muted">{{ $user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm w-auto" onchange="updateRole({{ $user->id }}, this.value)">
                                            <option value="user" {{ $user->role == 'user' ? 'selected' : '' }}>User</option>
                                            <option value="analyst" {{ $user->role == 'analyst' ? 'selected' : '' }}>Analyst</option>
                                            <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                        </select>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-light text-danger" onclick="deleteUser({{ $user->id }})" title="Delete User">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Audit Trail (Recent Activity) -->
            <div class="col-md-4 mb-4" id="audit-trail">
                <div class="card p-4 h-100">
                    <h5 class="fw-bold mb-4"><i class="bi bi-clock-history text-secondary me-2"></i>Audit Trail</h5>
                    <div class="activity-feed">
                        @foreach($activityLogs ?? [] as $log)
                            <div class="activity-item {{ $log['type'] }}">
                                <div class="fw-semibold" style="font-size: 14px;">{{ $log['action'] }}</div>
                                <small class="text-muted">{{ $log['time'] }}</small>
                            </div>
                        @endforeach
                    </div>
                    <button class="btn btn-sm btn-outline-secondary mt-auto w-100">View Full Logs</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 bg-light">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus text-primary me-2"></i>Create New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="/admin/users" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name</label>
                        <input type="text" class="form-control" name="name" required placeholder="John Doe">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" class="form-control" name="email" required placeholder="john@riskintel.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" class="form-control" name="password" required placeholder="Min. 8 characters" minlength="8">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Role Access</label>
                        <select name="role" class="form-select" required>
                            <option value="user">User (View Only)</option>
                            <option value="analyst">Analyst (Edit Data)</option>
                            <option value="admin">Admin (Full Access)</option>
                        </select>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary py-2 fw-bold">Create Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Search functionality
    document.getElementById('searchUser').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.getElementById('userTableBody').getElementsByTagName('tr');
        for (let i = 0; i < rows.length; i++) {
            let text = rows[i].textContent || rows[i].innerText;
            if (text.toLowerCase().indexOf(filter) > -1) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }
        }
    });

    async function updateRole(userId, newRole) {
        try {
            const res = await fetch(`/admin/users/${userId}/role`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ role: newRole })
            });
            const data = await res.json();
            if(data.status !== 'success') {
                alert('Error updating role: ' + (data.message || 'Unknown error'));
            }
        } catch (e) {
            console.error(e);
            alert('Failed to update role');
        }
    }

    async function deleteUser(userId) {
        if(!confirm('Are you sure you want to delete this user? This action cannot be undone.')) return;
        
        try {
            const res = await fetch(`/admin/users/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            const data = await res.json();
            if(data.status === 'success') {
                const row = document.getElementById(`user-row-${userId}`);
                row.style.transition = "all 0.3s ease";
                row.style.opacity = "0";
                setTimeout(() => row.remove(), 300);
            } else {
                alert('Error deleting user');
            }
        } catch (e) {
            console.error(e);
            alert('Failed to delete user');
        }
    }

    // Chart.js implementation
    document.addEventListener("DOMContentLoaded", function() {
        const growthData = @json($userGrowthData ?? ['labels' => [], 'data' => []]);
        
        if (growthData.labels.length > 0) {
            const ctx = document.getElementById('userGrowthChart').getContext('2d');
            
            // Create gradient
            let gradient = ctx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(102, 126, 234, 0.5)');
            gradient.addColorStop(1, 'rgba(102, 126, 234, 0.0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: growthData.labels,
                    datasets: [{
                        label: 'Active Users',
                        data: growthData.data,
                        borderColor: '#667eea',
                        backgroundColor: gradient,
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#667eea',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e283c',
                            titleFont: { size: 13 },
                            bodyFont: { size: 14, weight: 'bold' },
                            padding: 12,
                            displayColors: false,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
                            ticks: { font: { size: 11 }, color: '#888' }
                        },
                        x: {
                            grid: { display: false, drawBorder: false },
                            ticks: { font: { size: 11 }, color: '#888' }
                        }
                    },
                    interaction: { mode: 'index', intersect: false }
                }
            });
        }
    });
</script>
</body>
</html>
