<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Risk Intelligence</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Leaflet CSS for Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        
        #sidebar { 
            min-height: 100vh; 
            background: linear-gradient(180deg, #1a1d2e 0%, #16213e 100%);
            color: #fff;
            box-shadow: 4px 0 15px rgba(0,0,0,0.3);
            position: sticky;
            top: 0;
        }
        
        #sidebar .logo {
            font-size: 24px;
            font-weight: 900;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        #sidebar .nav-link { 
            color: #adb5bd; 
            border-radius: 8px; 
            margin-bottom: 8px;
            padding: 12px 16px !important;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        #sidebar .nav-link:hover, #sidebar .nav-link.active { 
            background: rgba(102, 126, 234, 0.15);
            color: #667eea;
            border-left-color: #667eea;
        }
        
        main { background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%); }
        
        .header-section {
            background: linear-gradient(135deg, rgba(102,126,234,0.1) 0%, rgba(118,75,162,0.1) 100%);
            border-bottom: 1px solid rgba(102,126,234,0.2);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }
        
        .header-section h2 {
            color: #1a1d2e;
            font-weight: 700;
            font-size: 28px;
            margin: 0 0 8px 0;
        }
        
        .header-section p {
            color: #666;
            margin: 0;
            font-size: 14px;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            margin-bottom: 16px;
            border-left: 4px solid #667eea;
        }
        
        .stat-card:hover {
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #667eea;
        }
        
        .stat-label {
            font-size: 14px;
            color: #888;
            margin-top: 4px;
        }
        
        .stat-icon {
            font-size: 36px;
            color: #667eea;
            opacity: 0.2;
            float: right;
        }
        
        .quick-link {
            display: inline-block;
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-decoration: none;
            color: #1a1d2e;
            margin: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            text-align: center;
            min-width: 180px;
        }
        
        .quick-link:hover {
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            transform: translateY(-4px);
            text-decoration: none;
            color: #667eea;
        }
        
        .quick-link i {
            font-size: 32px;
            display: block;
            margin-bottom: 8px;
            color: #667eea;
        }
        
        .quick-link-title {
            font-weight: 700;
            font-size: 14px;
        }
        
        .quick-link-desc {
            font-size: 12px;
            color: #888;
            margin-top: 4px;
        }
        
        .alert-box {
            background: white;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid #ffc107;
        }
        
        .alert-box.critical {
            border-left-color: #dc3545;
        }
        
        .alert-box.success {
            border-left-color: #28a745;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    @if(isset($isAdminLayout) && $isAdminLayout)
        @include('admin.sidebar')
    @else
    <nav id="sidebar" class="p-4" style="width: 260px;">
        <div class="logo mb-5">
            <i class="bi bi-geo-alt"></i>
            <span>Risk Intel</span>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="/" class="nav-link active" title="Dashboard">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="/country" class="nav-link" title="Country Intelligence">
                    <i class="bi bi-globe me-2"></i> Country Intelligence
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/port" class="nav-link" title="Port Monitoring">
                    <i class="bi bi-geo-alt me-2"></i> Port Monitoring
                </a>
            </li>
            <li class="nav-item">
                <a href="/news" class="nav-link" title="News & Sentiment">
                    <i class="bi bi-newspaper me-2"></i> News & Sentiment
                </a>
            </li>
            <li class="nav-item">
                <a href="/currency" class="nav-link" title="Currency Exchange">
                    <i class="bi bi-cash-coin me-2"></i> Currency Exchange
                </a>
            </li>
            <li class="nav-item">
                <a href="/watchlist" class="nav-link" title="Daftar Pantauan">
                    <i class="bi bi-star me-2"></i> Daftar Pantauan
                </a>
            </li>
            @if(Auth::check() && Auth::user()->role === 'admin')
            <li class="nav-item mt-2">
                <a href="/admin/dashboard" class="nav-link" title="Admin Dashboard" style="color: #c084fc;">
                    <i class="bi bi-shield-lock me-2"></i> Admin Panel
                </a>
            </li>
            @endif
            
            @if(Auth::check())
            <li class="nav-item mt-4 pt-3 border-top" style="border-color: rgba(255,255,255,0.1) !important;">
                <div class="px-3 mb-3 text-muted" style="font-size: 12px;">
                    Logged in as:<br>
                    <strong class="text-white">{{ Auth::user()->name }}</strong>
                </div>
                <form action="/logout" method="POST" class="d-grid px-3">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm text-start" style="border-radius: 8px;">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </button>
                </form>
            </li>
            @endif
        </ul>
    </nav>
    @endif
    
    <!-- Main Content -->
    <main class="flex-grow-1 p-4" id="main-content">
        <!-- Header -->
        <div class="header-section mb-4">
            <h2><i class="bi bi-star-fill text-warning me-2"></i>Daftar Pantauan</h2>
            <p>Pantau risiko dan tren terkini untuk negara dan pelabuhan yang Anda ikuti.</p>
        </div>

        @if(Auth::check())
        <div class="row">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm rounded-4 p-4 min-vh-50">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Item Pantauan Anda</h5>
                        <div>
                            <a href="/country" class="btn btn-sm btn-outline-primary rounded-pill px-3"><i class="bi bi-plus-lg me-1"></i> Tambah Negara</a>
                            <a href="/port" class="btn btn-sm btn-outline-success rounded-pill px-3 ms-2"><i class="bi bi-plus-lg me-1"></i> Tambah Pelabuhan</a>
                        </div>
                    </div>
                    
                    <div class="row g-4" id="watchlistContainer">
                        <div class="col-12 text-center py-5 text-muted">
                            <div class="spinner-border text-primary mb-3" role="status"></div>
                            <p>Memuat daftar pantauan...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i> Anda harus <a href="/login" class="alert-link">login</a> untuk melihat daftar pantauan.
        </div>
        @endif
    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    @if(Auth::check())
    function loadWatchlist() {
        const container = document.getElementById('watchlistContainer');
        
        fetch('/watchlists')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.data && data.data.length > 0) {
                    container.innerHTML = '';
                    data.data.forEach(item => {
                        let trendIcon = '<i class="bi bi-dash-circle-fill text-secondary"></i> Stabil';
                        let borderClass = 'border-secondary';
                        
                        if (item.trend === 'Up') {
                            trendIcon = '<i class="bi bi-arrow-up-circle-fill text-danger"></i> Risiko Naik';
                            borderClass = 'border-danger';
                        }
                        if (item.trend === 'Down') {
                            trendIcon = '<i class="bi bi-arrow-down-circle-fill text-success"></i> Risiko Turun';
                            borderClass = 'border-success';
                        }

                        const typeIcon = item.type === 'Country' ? 'bi-globe-americas text-primary' : 'bi-geo-alt-fill text-success';
                        const typeLabel = item.type === 'Country' ? 'Negara' : 'Pelabuhan';

                        const card = `
                            <div class="col-md-4 col-sm-6">
                                <div class="card h-100 shadow-sm border-0 border-top border-4 ${borderClass}" style="border-radius: 12px; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='none'">
                                    <div class="card-body p-4 position-relative">
                                        <button onclick="removeWatchlist(${item.id})" class="btn btn-sm btn-light position-absolute top-0 end-0 m-2 text-danger rounded-circle" title="Hapus dari pantauan">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="fs-3 me-3"><i class="bi ${typeIcon}"></i></div>
                                            <div>
                                                <h5 class="fw-bold mb-0 text-truncate" title="${item.entity_name}">${item.entity_name}</h5>
                                                <small class="text-muted">${typeLabel}</small>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-end mt-4">
                                            <div>
                                                <div class="text-muted small mb-1">Skor Risiko</div>
                                                <h3 class="fw-bold mb-0" style="color: #6f42c1;">${item.current_risk_score}</h3>
                                            </div>
                                            <div class="text-end small fw-semibold">
                                                ${trendIcon}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent border-0 text-center pb-3">
                                        <a href="/${item.type === 'Country' ? 'country' : 'port'}" class="btn btn-sm btn-outline-primary w-100 rounded-pill">Lihat Detail</a>
                                    </div>
                                </div>
                            </div>
                        `;
                        container.innerHTML += card;
                    });
                } else {
                    container.innerHTML = `
                        <div class="col-12 text-center py-5">
                            <div class="display-1 text-muted mb-3"><i class="bi bi-star"></i></div>
                            <h4 class="text-muted">Daftar Pantauan Kosong</h4>
                            <p class="text-muted mb-4">Anda belum menambahkan negara atau pelabuhan ke daftar pantauan.</p>
                            <a href="/country" class="btn btn-primary rounded-pill px-4"><i class="bi bi-globe me-2"></i>Jelajahi Negara</a>
                        </div>
                    `;
                }
            })
            .catch(err => {
                container.innerHTML = '<div class="col-12 text-center py-5 text-danger"><i class="bi bi-exclamation-triangle fs-1 d-block mb-3"></i> Gagal memuat data pantauan.</div>';
            });
    }

    function removeWatchlist(id) {
        if(!confirm('Hapus item ini dari daftar pantauan?')) return;
        
        fetch('/watchlists/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                loadWatchlist();
            } else {
                alert('Gagal menghapus item.');
            }
        })
        .catch(err => console.error(err));
    }

    // Load initial
    loadWatchlist();
    @endif
</script>
</body>
</html>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

