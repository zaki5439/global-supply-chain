<style>
    .admin-sidebar {
        min-height: 100vh;
        background: #1e283c;
        color: white;
        box-shadow: 4px 0 15px rgba(0,0,0,0.1);
        width: 260px;
        position: sticky;
        top: 0;
    }
    .admin-sidebar .nav-link {
        color: #adb5bd;
        padding: 12px 15px;
        margin: 4px 10px;
        border-radius: 8px;
        transition: 0.3s;
        border: none;
        display: block;
        text-decoration: none;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        border: none;
        display: block;
        text-decoration: none;
    }
    .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active {
        background: #3a4a6b;
        color: white;
    }
    .admin-logo-text { font-weight: 800; letter-spacing: 1px; color: #667eea; }
</style>

<div class="admin-sidebar py-4 d-flex flex-column">
    <h4 class="text-center mb-4 px-3 admin-logo-text">Admin Panel</h4>
    <div class="nav flex-column flex-grow-1">
        <a href="/admin/dashboard" class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2 me-2"></i> Overview</a>
        <a href="/admin/dashboard#user-management" class="nav-link"><i class="bi bi-people me-2"></i> User Management</a>
        <a href="{{ route('ports.index') }}" class="nav-link {{ request()->is('ports*') ? 'active' : '' }}"><i class="bi bi-geo-alt me-2"></i> Port Database</a>
        <a href="{{ route('articles.index') }}" class="nav-link {{ request()->is('articles*') ? 'active' : '' }}"><i class="bi bi-newspaper me-2"></i> Articles Database</a>
        <a href="/admin/dashboard#system-health" class="nav-link"><i class="bi bi-activity me-2"></i> System Health</a>
        <a href="/admin/dashboard#audit-trail" class="nav-link"><i class="bi bi-shield-check me-2"></i> Audit Trail</a>
        
        <div class="px-3 mt-4 mb-2 text-uppercase text-secondary fw-bold" style="font-size: 10px; letter-spacing: 1px;">User App Features</div>
        <a href="/admin/dashboard-view" class="nav-link {{ request()->is('admin/dashboard-view') ? 'active' : '' }}"><i class="bi bi-display me-2"></i> User Dashboard</a>
        <a href="/admin/country" class="nav-link {{ request()->is('admin/country') ? 'active' : '' }}"><i class="bi bi-globe me-2"></i> Country Intelligence</a>
        <a href="/admin/port" class="nav-link {{ request()->is('admin/port') ? 'active' : '' }}"><i class="bi bi-geo-alt me-2"></i> Port Monitoring</a>
        <a href="/admin/news" class="nav-link {{ request()->is('admin/news') ? 'active' : '' }}"><i class="bi bi-newspaper me-2"></i> News & Sentiment</a>
        <a href="/admin/currency" class="nav-link {{ request()->is('admin/currency') ? 'active' : '' }}"><i class="bi bi-cash-coin me-2"></i> Currency Exchange</a>
        
        <div class="mt-auto pt-4 px-3">
            <div class="mb-3 text-muted" style="font-size: 12px;">
                Logged in as:<br>
                <strong class="text-white">{{ Auth::user()->name ?? 'Admin' }}</strong>
            </div>
            <form action="/logout" method="POST" class="d-grid">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm text-start" style="border-radius: 8px;">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </button>
            </form>
        </div>
    </div>
</div>
