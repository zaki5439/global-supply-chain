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
        <div class="header-section">
            <h2><i class="bi bi-speedometer2 me-2"></i>Supply Chain Intelligence Dashboard</h2>
            <p>Real-time monitoring of global supply chain risks and port operations</p>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="bi bi-ship stat-icon"></i>
                    <div class="stat-value">380+</div>
                    <div class="stat-label">Active Ports</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="bi bi-check-circle stat-icon"></i>
                    <div class="stat-value" style="color: #28a745;">342</div>
                    <div class="stat-label">Operational</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="bi bi-exclamation-circle stat-icon"></i>
                    <div class="stat-value" style="color: #ffc107;">28</div>
                    <div class="stat-label">Delayed</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="bi bi-x-circle stat-icon"></i>
                    <div class="stat-value" style="color: #dc3545;">10</div>
                    <div class="stat-label">Critical</div>
                </div>
            </div>
        </div>


        <!-- Risk Alerts -->
        <div class="row mb-4">
            <div class="col-md-12">
                <h5 style="color: #1a1d2e; font-weight: 700; margin-bottom: 16px;">
                    <i class="bi bi-bell me-2"></i>Risk Alerts
                </h5>
                <div class="alert-box critical">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Port of Manila</strong> - Typhoon recovery ongoing. 72% congestion level.
                </div>
                <div class="alert-box">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Port of Ho Chi Minh</strong> - Weather delays affecting operations. 45% congestion.
                </div>
                <div class="alert-box">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Port of Lagos</strong> - Infrastructure development in progress. 48% congestion.
                </div>
                <div class="alert-box success">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong>Port of Singapore</strong> - Operating at optimal efficiency. Only 8% congestion.
                </div>
            </div>
        </div>

        <!-- Global Weather Map (Leaflet) -->
        <div class="row mb-4">
            <div class="col-md-12 mb-4">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-cloud-lightning-rain text-info me-2"></i>Global Weather Monitoring</h5>
                        <input type="text" id="searchWeatherCountry" class="form-control form-control-sm w-25" placeholder="Cari Negara..." style="font-size: 14px;">
                    </div>
                    <div id="weatherMap" style="height: 400px; border-radius: 8px; z-index: 1;"></div>
                </div>
            </div>
        </div>

        <!-- Data Visualization (Chart.js) -->
        <div class="row mb-4">
            <div class="col-md-12 mb-4">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0" id="riskTrendTitle"><i class="bi bi-graph-up-arrow text-primary me-2"></i>Global Risk Trend (6 Months)</h5>
                        <div class="d-flex gap-2">
                            <select id="riskTrendCountrySelect" class="form-select form-select-sm" style="width: 180px; font-size: 12px;">
                                <option value="GLOBAL">Global Trend</option>
                            </select>
                        </div>
                    </div>
                    <div style="height: 300px;">
                        <canvas id="riskTrendChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8 mb-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-bar-chart-fill text-success me-2"></i>Economic Impact Analysis</h5>
                        <div class="d-flex gap-2">
                            <select id="econCountrySelect" class="form-select form-select-sm" style="width: 140px; font-size: 12px;">
                                <option value="ALL">All Countries</option>
                            </select>
                            <input type="text" id="econSearchInput" class="form-control form-control-sm" placeholder="Search country..." style="width: 140px; font-size: 12px;">
                        </div>
                    </div>
                    <div style="height: 300px;">
                        <canvas id="economicImpactChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                    <h5 class="fw-bold mb-3"><i class="bi bi-pie-chart-fill text-warning me-2"></i>Port Congestion Distribution</h5>
                    <div style="height: 300px; display: flex; justify-content: center;">
                        <canvas id="portCongestionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live News Widget -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-broadcast text-danger me-2"></i>Live News & Sentiment 
                            <span class="badge bg-primary ms-2" id="newsCountryLabel">Global</span>
                        </h5>
                        <a href="/news" class="btn btn-sm btn-outline-primary rounded-pill px-3">Full Analysis</a>
                    </div>
                    <div id="dashboardNewsContainer" class="row g-3">
                        <div class="col-12 text-center py-4 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                            Fetching real-time updates...
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="row mb-4">
            <div class="col-md-12">
                <h5 style="color: #1a1d2e; font-weight: 700; margin-bottom: 16px;">Quick Access</h5>
                <a href="/port" class="quick-link">
                    <i class="bi bi-map"></i>
                    <div class="quick-link-title">Port Monitoring</div>
                    <div class="quick-link-desc">Real-time port tracking & filters</div>
                </a>
                <a href="/news" class="quick-link">
                    <i class="bi bi-newspaper"></i>
                    <div class="quick-link-title">News & Sentiment</div>
                    <div class="quick-link-desc">Market intelligence & trends</div>
                </a>
                <a href="/currency" class="quick-link">
                    <i class="bi bi-cash-coin"></i>
                    <div class="quick-link-title">Currency Exchange</div>
                    <div class="quick-link-desc">Exchange rates & forecasts</div>
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-5 pt-4 border-top text-center text-muted small">
            <p><i class="bi bi-info-circle me-1"></i>Dashboard data updated every 15 minutes</p>
            <p class="mb-0">Last updated: <strong id="lastUpdated">--</strong></p>
        </div>
    </main>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    document.getElementById('lastUpdated').textContent = new Date().toLocaleTimeString();


    // Initialize Global Weather Map
    const weatherMapBounds = L.latLngBounds([[-90, -180], [90, 180]]);
    const weatherMap = L.map('weatherMap', {
        worldCopyJump: false,
        maxBounds: weatherMapBounds,
        maxBoundsViscosity: 1.0,
        minZoom: 2
    }).setView([20, 0], 2);
    
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        noWrap: true
    }).addTo(weatherMap);

    // Use full ports database from localStorage if available
    const cachedPorts = localStorage.getItem('portsData');
    
    async function loadRealtimeWeather() {
        let allPortsToProcess = [];
        if (cachedPorts) {
            allPortsToProcess = JSON.parse(cachedPorts);
        } else {
            // Fallback to mock data if ports are not yet cached
            allPortsToProcess = [
                { lat: 14.5995, lng: 120.9842, name: 'Manila, PH' },
                { lat: 10.7626, lng: 106.6601, name: 'Ho Chi Minh, VN' },
                { lat: 25.0330, lng: 121.5654, name: 'Taipei, TW' },
                { lat: 35.6762, lng: 139.6503, name: 'Tokyo, JP' },
                { lat: 1.3521, lng: 103.8198, name: 'Singapore, SG' }
            ];
        }

        // Chunk size of 50 to avoid URL length limit
        const chunkSize = 50;
        const chunks = [];
        for (let i = 0; i < allPortsToProcess.length; i += chunkSize) {
            chunks.push(allPortsToProcess.slice(i, i + chunkSize));
        }

        try {
            // Process chunks in parallel
            await Promise.all(chunks.map(async (chunk) => {
                const lats = chunk.map(p => p.lat).join(',');
                const lngs = chunk.map(p => p.lng).join(',');
                
                const res = await fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lats}&longitude=${lngs}&current_weather=true`);
                const data = await res.json();
                
                const weatherResults = Array.isArray(data) ? data : [data];
                
                weatherResults.forEach((weatherObj, index) => {
                    const port = chunk[index];
                    if (!weatherObj.current_weather) return;
                    
                    const wcode = weatherObj.current_weather.weathercode;
                    const temp = weatherObj.current_weather.temperature;
                    const wind = weatherObj.current_weather.windspeed;
                    
                    let color = 'green';
                    let desc = 'Cerah';
                    
                    // WMO Weather interpretation codes
                    if (wcode >= 1 && wcode <= 3) { desc = 'Berawan'; }
                    else if (wcode >= 45 && wcode <= 48) { desc = 'Berkabut'; color = 'orange'; }
                    else if (wcode >= 51 && wcode <= 67) { desc = 'Hujan'; color = 'orange'; }
                    else if (wcode >= 71 && wcode <= 77) { desc = 'Salju'; color = 'orange'; }
                    else if (wcode >= 80 && wcode <= 82) { desc = 'Hujan Lebat'; color = 'red'; }
                    else if (wcode >= 95) { desc = 'Badai Petir (Thunderstorm)'; color = 'red'; }
                    
                    const circle = L.circleMarker([port.lat, port.lng], {
                        radius: 5,
                        fillColor: color === 'red' ? '#dc3545' : (color === 'orange' ? '#fd7e14' : '#28a745'),
                        color: '#fff',
                        weight: 1,
                        opacity: 1,
                        fillOpacity: 0.8
                    }).addTo(weatherMap);
                    
                    // Attach country data to marker for search filtering
                    circle.options.country = port.country || '';
                    if (!window.weatherMarkers) window.weatherMarkers = [];
                    window.weatherMarkers.push(circle);

                    circle.bindPopup(`<strong>${port.name}</strong><br>
                    Status Cuaca: <strong>${desc}</strong><br>
                    Suhu: ${temp}°C<br>
                    Kecepatan Angin: ${wind} km/h`);
                });
            }));
            
            // Search feature logic
            document.getElementById('searchWeatherCountry').addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase();
                let hasVisible = false;
                let bounds = L.latLngBounds();
                
                if (window.weatherMarkers) {
                    window.weatherMarkers.forEach(marker => {
                        const country = (marker.options.country || '').toLowerCase();
                        if (country.includes(term)) {
                            if (!weatherMap.hasLayer(marker)) weatherMap.addLayer(marker);
                            bounds.extend(marker.getLatLng());
                            hasVisible = true;
                        } else {
                            if (weatherMap.hasLayer(marker)) weatherMap.removeLayer(marker);
                        }
                    });
                }
                
                if (term && hasVisible) {
                    weatherMap.flyToBounds(bounds, { padding: [50, 50], maxZoom: 5, duration: 1.5 });
                } else if (!term) {
                    weatherMap.flyTo([20, 0], 2, { duration: 1.5 });
                }
            });
            
        } catch (error) {
            console.error('Failed to fetch realtime weather', error);
        }
    }
    
    loadRealtimeWeather();

    // Chart.js Default Configs
    Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
    Chart.defaults.color = '#888';

    // 1. Global Risk Trend (Line Chart)
    const baseRiskData = [45, 52, 48, 60, 58, 65];
    const ctxRisk = document.getElementById('riskTrendChart').getContext('2d');
    let riskChart = new Chart(ctxRisk, {
        type: 'line',
        data: {
            labels: ['January', 'February', 'March', 'April', 'May', 'June'],
            datasets: [{
                label: 'Average Global Risk Score',
                data: [...baseRiskData],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.2)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#667eea',
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, max: 100 } }
        }
    });

    // 2. Economic Impact Analysis (Combo Chart: Bar & Line)
        const allCountries = [
        { id: 'AF', name: 'Afghanistan', flag: '🇦🇫', gdp: '-0.9%', infl: '6.2%', pop: '37.2 Million', curr: 'AFN', weather: 'Partly Cloudy, 33°C', w_icon: 'bi-cloud-sun' },
        { id: 'AL', name: 'Albania', flag: '🇦🇱', gdp: '-1.2%', infl: '13.1%', pop: '2.9 Million', curr: 'ALL', weather: 'Clear Sky, 24°C', w_icon: 'bi-sun' },
        { id: 'DZ', name: 'Algeria', flag: '🇩🇿', gdp: '4.3%', infl: '9.6%', pop: '42.2 Million', curr: 'DZD', weather: 'Scattered Clouds, 33°C', w_icon: 'bi-clouds' },
        { id: 'AS', name: 'American Samoa', flag: '🇦🇸', gdp: '-0.5%', infl: '13.5%', pop: '55,465', curr: 'USD', weather: 'Sunny, 3°C', w_icon: 'bi-sun' },
        { id: 'AD', name: 'Andorra', flag: '🇦🇩', gdp: '2.0%', infl: '10.0%', pop: '77,006', curr: 'EUR', weather: 'Sunny, 37°C', w_icon: 'bi-sun' },
        { id: 'AO', name: 'Angola', flag: '🇦🇴', gdp: '-1.7%', infl: '7.4%', pop: '30.8 Million', curr: 'AOA', weather: 'Heavy Rain, 22°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'AI', name: 'Anguilla', flag: '🇦🇮', gdp: '3.9%', infl: '4.2%', pop: '15,094', curr: 'XCD', weather: 'Thunderstorms, 2°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'AQ', name: 'Antarctica', flag: '🇦🇶', gdp: '6.8%', infl: '7.8%', pop: '1,106', curr: 'XCD', weather: 'Light Rain, 22°C', w_icon: 'bi-cloud-rain' },
        { id: 'AG', name: 'Antigua and Barbuda', flag: '🇦🇬', gdp: '4.2%', infl: '12.4%', pop: '96,286', curr: 'XCD', weather: 'Partly Cloudy, 19°C', w_icon: 'bi-cloud-sun' },
        { id: 'AR', name: 'Argentina', flag: '🇦🇷', gdp: '0.2%', infl: '12.7%', pop: '44.5 Million', curr: 'ARS', weather: 'Snow, 2°C', w_icon: 'bi-snow' },
        { id: 'AM', name: 'Armenia', flag: '🇦🇲', gdp: '5.2%', infl: '14.7%', pop: '3.0 Million', curr: 'AMD', weather: 'Thunderstorms, 23°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'AW', name: 'Aruba', flag: '🇦🇼', gdp: '5.9%', infl: '0.6%', pop: '105,845', curr: 'AWG', weather: 'Partly Cloudy, 22°C', w_icon: 'bi-cloud-sun' },
        { id: 'AU', name: 'Australia', flag: '🇦🇺', gdp: '5.4%', infl: '4.3%', pop: '25.0 Million', curr: 'AUD', weather: 'Cloudy, 17°C', w_icon: 'bi-clouds' },
        { id: 'AT', name: 'Austria', flag: '🇦🇹', gdp: '3.7%', infl: '3.4%', pop: '8.8 Million', curr: 'EUR', weather: 'Sunny, 22°C', w_icon: 'bi-sun' },
        { id: 'AZ', name: 'Azerbaijan', flag: '🇦🇿', gdp: '2.6%', infl: '8.3%', pop: '9.9 Million', curr: 'AZN', weather: 'Sunny, 22°C', w_icon: 'bi-sun' },
        { id: 'BS', name: 'Bahamas', flag: '🇧🇸', gdp: '7.4%', infl: '1.3%', pop: '385,640', curr: 'BSD', weather: 'Haze, 34°C', w_icon: 'bi-cloud-haze' },
        { id: 'BH', name: 'Bahrain', flag: '🇧🇭', gdp: '0.6%', infl: '12.0%', pop: '1.6 Million', curr: 'BHD', weather: 'Thunderstorms, 19°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'BD', name: 'Bangladesh', flag: '🇧🇩', gdp: '5.0%', infl: '11.0%', pop: '161.4 Million', curr: 'BDT', weather: 'Cloudy, 25°C', w_icon: 'bi-clouds' },
        { id: 'BB', name: 'Barbados', flag: '🇧🇧', gdp: '0.0%', infl: '4.4%', pop: '286,641', curr: 'BBD', weather: 'Cloudy, 27°C', w_icon: 'bi-clouds' },
        { id: 'BY', name: 'Belarus', flag: '🇧🇾', gdp: '0.2%', infl: '0.5%', pop: '9.5 Million', curr: 'BYR', weather: 'Heavy Rain, 20°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'BE', name: 'Belgium', flag: '🇧🇪', gdp: '5.0%', infl: '5.5%', pop: '11.4 Million', curr: 'EUR', weather: 'Thunderstorms, 15°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'BZ', name: 'Belize', flag: '🇧🇿', gdp: '6.0%', infl: '1.6%', pop: '383,071', curr: 'BZD', weather: 'Scattered Clouds, 14°C', w_icon: 'bi-clouds' },
        { id: 'BJ', name: 'Benin', flag: '🇧🇯', gdp: '4.0%', infl: '12.5%', pop: '11.5 Million', curr: 'XOF', weather: 'Partly Cloudy, 27°C', w_icon: 'bi-cloud-sun' },
        { id: 'BM', name: 'Bermuda', flag: '🇧🇲', gdp: '7.5%', infl: '5.1%', pop: '63,973', curr: 'BMD', weather: 'Cloudy, 27°C', w_icon: 'bi-clouds' },
        { id: 'BT', name: 'Bhutan', flag: '🇧🇹', gdp: '5.0%', infl: '4.2%', pop: '754,394', curr: 'BTN', weather: 'Haze, 17°C', w_icon: 'bi-cloud-haze' },
        { id: 'BO', name: 'Bolivia, Plurinational State of', flag: '🇧🇴', gdp: '-0.3%', infl: '10.4%', pop: '29.8 Million', curr: 'USD', weather: 'Cloudy, 8°C', w_icon: 'bi-clouds' },
        { id: 'BQ', name: 'Bonaire, Sint Eustatius and Saba', flag: '🇧🇶', gdp: '5.5%', infl: '8.4%', pop: '29.4 Million', curr: 'USD', weather: 'Sunny, 6°C', w_icon: 'bi-sun' },
        { id: 'BA', name: 'Bosnia and Herzegovina', flag: '🇧🇦', gdp: '4.6%', infl: '13.9%', pop: '3.3 Million', curr: 'BAM', weather: 'Heavy Rain, 21°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'BW', name: 'Botswana', flag: '🇧🇼', gdp: '4.1%', infl: '11.9%', pop: '2.3 Million', curr: 'BWP', weather: 'Cloudy, 22°C', w_icon: 'bi-clouds' },
        { id: 'BV', name: 'Bouvet Island', flag: '🇧🇻', gdp: '-1.5%', infl: '12.7%', pop: '38.2 Million', curr: 'NOK', weather: 'Sunny, -2°C', w_icon: 'bi-sun' },
        { id: 'BR', name: 'Brazil', flag: '🇧🇷', gdp: '5.9%', infl: '12.7%', pop: '209.5 Million', curr: 'BRL', weather: 'Partly Cloudy, 15°C', w_icon: 'bi-cloud-sun' },
        { id: 'IO', name: 'British Indian Ocean Territory', flag: '🇮🇴', gdp: '-0.5%', infl: '13.4%', pop: '13.8 Million', curr: 'USD', weather: 'Scattered Clouds, 19°C', w_icon: 'bi-clouds' },
        { id: 'BN', name: 'Brunei Darussalam', flag: '🇧🇳', gdp: '3.4%', infl: '14.8%', pop: '1.6 Million', curr: 'USD', weather: 'Heavy Rain, 5°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'BG', name: 'Bulgaria', flag: '🇧🇬', gdp: '-0.3%', infl: '10.8%', pop: '7.0 Million', curr: 'BGN', weather: 'Partly Cloudy, 38°C', w_icon: 'bi-cloud-sun' },
        { id: 'BF', name: 'Burkina Faso', flag: '🇧🇫', gdp: '4.9%', infl: '10.6%', pop: '19.8 Million', curr: 'XOF', weather: 'Cloudy, 4°C', w_icon: 'bi-clouds' },
        { id: 'BI', name: 'Burundi', flag: '🇧🇮', gdp: '3.8%', infl: '0.4%', pop: '11.2 Million', curr: 'BIF', weather: 'Cloudy, -5°C', w_icon: 'bi-clouds' },
        { id: 'CV', name: 'Cabo Verde', flag: '🇨🇻', gdp: '4.8%', infl: '9.7%', pop: '15.0 Million', curr: 'USD', weather: 'Heavy Rain, 38°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'KH', name: 'Cambodia', flag: '🇰🇭', gdp: '1.5%', infl: '7.3%', pop: '16.2 Million', curr: 'KHR', weather: 'Light Rain, 2°C', w_icon: 'bi-cloud-rain' },
        { id: 'CM', name: 'Cameroon', flag: '🇨🇲', gdp: '0.6%', infl: '2.7%', pop: '25.2 Million', curr: 'XAF', weather: 'Cloudy, 33°C', w_icon: 'bi-clouds' },
        { id: 'CA', name: 'Canada', flag: '🇨🇦', gdp: '-1.9%', infl: '12.9%', pop: '37.1 Million', curr: 'CAD', weather: 'Scattered Clouds, 28°C', w_icon: 'bi-clouds' },
        { id: 'KY', name: 'Cayman Islands', flag: '🇰🇾', gdp: '0.6%', infl: '2.5%', pop: '64,174', curr: 'KYD', weather: 'Thunderstorms, 8°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'CF', name: 'Central African Republic', flag: '🇨🇫', gdp: '4.1%', infl: '5.6%', pop: '4.7 Million', curr: 'XAF', weather: 'Cloudy, 32°C', w_icon: 'bi-clouds' },
        { id: 'TD', name: 'Chad', flag: '🇹🇩', gdp: '7.8%', infl: '2.0%', pop: '15.5 Million', curr: 'XAF', weather: 'Partly Cloudy, 16°C', w_icon: 'bi-cloud-sun' },
        { id: 'CL', name: 'Chile', flag: '🇨🇱', gdp: '5.7%', infl: '8.9%', pop: '18.7 Million', curr: 'CLP', weather: 'Clear Sky, 16°C', w_icon: 'bi-sun' },
        { id: 'CN', name: 'China', flag: '🇨🇳', gdp: '0.8%', infl: '13.2%', pop: '1.39 Billion', curr: 'CNY', weather: 'Sunny, 13°C', w_icon: 'bi-sun' },
        { id: 'CX', name: 'Christmas Island', flag: '🇨🇽', gdp: '2.9%', infl: '14.2%', pop: '1,402', curr: 'AUD', weather: 'Haze, -4°C', w_icon: 'bi-cloud-haze' },
        { id: 'CC', name: 'Cocos (Keeling) Islands', flag: '🇨🇨', gdp: '6.1%', infl: '4.9%', pop: '596', curr: 'AUD', weather: 'Sunny, 4°C', w_icon: 'bi-sun' },
        { id: 'CO', name: 'Colombia', flag: '🇨🇴', gdp: '1.5%', infl: '7.0%', pop: '49.6 Million', curr: 'COP', weather: 'Snow, 39°C', w_icon: 'bi-snow' },
        { id: 'KM', name: 'Comoros', flag: '🇰🇲', gdp: '2.8%', infl: '7.5%', pop: '832,322', curr: 'KMF', weather: 'Haze, 26°C', w_icon: 'bi-cloud-haze' },
        { id: 'CG', name: 'Congo', flag: '🇨🇬', gdp: '-0.2%', infl: '8.2%', pop: '5.2 Million', curr: 'XAF', weather: 'Haze, 11°C', w_icon: 'bi-cloud-haze' },
        { id: 'CD', name: 'Congo, Democratic Republic of the', flag: '🇨🇩', gdp: '5.0%', infl: '2.5%', pop: '35.6 Million', curr: 'USD', weather: 'Clear Sky, 6°C', w_icon: 'bi-sun' },
        { id: 'CK', name: 'Cook Islands', flag: '🇨🇰', gdp: '5.7%', infl: '9.9%', pop: '17,379', curr: 'NZD', weather: 'Thunderstorms, 8°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'CR', name: 'Costa Rica', flag: '🇨🇷', gdp: '3.8%', infl: '9.0%', pop: '5.0 Million', curr: 'CRC', weather: 'Snow, 20°C', w_icon: 'bi-snow' },
        { id: 'HR', name: 'Croatia', flag: '🇭🇷', gdp: '0.9%', infl: '6.7%', pop: '4.1 Million', curr: 'HRK', weather: 'Heavy Rain, -1°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'CU', name: 'Cuba', flag: '🇨🇺', gdp: '3.8%', infl: '4.3%', pop: '11.3 Million', curr: 'CUP', weather: 'Clear Sky, 16°C', w_icon: 'bi-sun' },
        { id: 'CW', name: 'Curaçao', flag: '🇨🇼', gdp: '6.9%', infl: '11.1%', pop: '6.8 Million', curr: 'USD', weather: 'Heavy Rain, 37°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'CY', name: 'Cyprus', flag: '🇨🇾', gdp: '2.7%', infl: '12.6%', pop: '1.2 Million', curr: 'EUR', weather: 'Partly Cloudy, 25°C', w_icon: 'bi-cloud-sun' },
        { id: 'CZ', name: 'Czechia', flag: '🇨🇿', gdp: '5.5%', infl: '3.3%', pop: '43.1 Million', curr: 'USD', weather: 'Partly Cloudy, 21°C', w_icon: 'bi-cloud-sun' },
        { id: 'CI', name: 'Côte d\'Ivoire', flag: '🇨🇮', gdp: '-1.1%', infl: '9.0%', pop: '36.6 Million', curr: 'USD', weather: 'Heavy Rain, 11°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'DK', name: 'Denmark', flag: '🇩🇰', gdp: '4.2%', infl: '1.8%', pop: '5.8 Million', curr: 'DKK', weather: 'Partly Cloudy, 25°C', w_icon: 'bi-cloud-sun' },
        { id: 'DJ', name: 'Djibouti', flag: '🇩🇯', gdp: '-0.9%', infl: '6.8%', pop: '958,920', curr: 'DJF', weather: 'Clear Sky, 1°C', w_icon: 'bi-sun' },
        { id: 'DM', name: 'Dominica', flag: '🇩🇲', gdp: '5.6%', infl: '4.2%', pop: '71,625', curr: 'XCD', weather: 'Haze, 7°C', w_icon: 'bi-cloud-haze' },
        { id: 'DO', name: 'Dominican Republic', flag: '🇩🇴', gdp: '3.6%', infl: '7.4%', pop: '10.6 Million', curr: 'DOP', weather: 'Haze, 16°C', w_icon: 'bi-cloud-haze' },
        { id: 'EC', name: 'Ecuador', flag: '🇪🇨', gdp: '-1.5%', infl: '2.3%', pop: '17.1 Million', curr: 'ECS', weather: 'Partly Cloudy, 6°C', w_icon: 'bi-cloud-sun' },
        { id: 'EG', name: 'Egypt', flag: '🇪🇬', gdp: '1.7%', infl: '8.8%', pop: '98.4 Million', curr: 'EGP', weather: 'Clear Sky, 16°C', w_icon: 'bi-sun' },
        { id: 'SV', name: 'El Salvador', flag: '🇸🇻', gdp: '3.7%', infl: '10.5%', pop: '6.4 Million', curr: 'SVC', weather: 'Scattered Clouds, -3°C', w_icon: 'bi-clouds' },
        { id: 'GQ', name: 'Equatorial Guinea', flag: '🇬🇶', gdp: '4.8%', infl: '3.1%', pop: '1.3 Million', curr: 'XAF', weather: 'Heavy Rain, -1°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'ER', name: 'Eritrea', flag: '🇪🇷', gdp: '3.7%', infl: '6.8%', pop: '6.2 Million', curr: 'ERN', weather: 'Haze, 24°C', w_icon: 'bi-cloud-haze' },
        { id: 'EE', name: 'Estonia', flag: '🇪🇪', gdp: '2.9%', infl: '8.0%', pop: '1.3 Million', curr: 'EUR', weather: 'Cloudy, 1°C', w_icon: 'bi-clouds' },
        { id: 'SZ', name: 'Eswatini', flag: '🇸🇿', gdp: '-1.6%', infl: '8.4%', pop: '20.0 Million', curr: 'USD', weather: 'Clear Sky, 7°C', w_icon: 'bi-sun' },
        { id: 'ET', name: 'Ethiopia', flag: '🇪🇹', gdp: '-1.1%', infl: '4.3%', pop: '109.2 Million', curr: 'ETB', weather: 'Thunderstorms, 25°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'FK', name: 'Falkland Islands (Malvinas)', flag: '🇫🇰', gdp: '-1.5%', infl: '1.9%', pop: '39.9 Million', curr: 'USD', weather: 'Thunderstorms, 31°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'FO', name: 'Faroe Islands', flag: '🇫🇴', gdp: '7.2%', infl: '2.2%', pop: '48,497', curr: 'DKK', weather: 'Light Rain, 31°C', w_icon: 'bi-cloud-rain' },
        { id: 'FJ', name: 'Fiji', flag: '🇫🇯', gdp: '1.6%', infl: '13.8%', pop: '29.1 Million', curr: 'USD', weather: 'Cloudy, 20°C', w_icon: 'bi-clouds' },
        { id: 'FI', name: 'Finland', flag: '🇫🇮', gdp: '1.6%', infl: '6.9%', pop: '5.5 Million', curr: 'EUR', weather: 'Cloudy, 20°C', w_icon: 'bi-clouds' },
        { id: 'FR', name: 'France', flag: '🇫🇷', gdp: '5.7%', infl: '9.4%', pop: '67.0 Million', curr: 'EUR', weather: 'Scattered Clouds, 27°C', w_icon: 'bi-clouds' },
        { id: 'GF', name: 'French Guiana', flag: '🇬🇫', gdp: '1.9%', infl: '9.2%', pop: '290,691', curr: 'EUR', weather: 'Cloudy, 10°C', w_icon: 'bi-clouds' },
        { id: 'PF', name: 'French Polynesia', flag: '🇵🇫', gdp: '0.5%', infl: '6.9%', pop: '277,679', curr: 'XPF', weather: 'Sunny, 15°C', w_icon: 'bi-sun' },
        { id: 'TF', name: 'French Southern Territories', flag: '🇹🇫', gdp: '5.7%', infl: '9.0%', pop: '4.5 Million', curr: 'USD', weather: 'Partly Cloudy, 22°C', w_icon: 'bi-cloud-sun' },
        { id: 'GA', name: 'Gabon', flag: '🇬🇦', gdp: '2.7%', infl: '3.4%', pop: '2.1 Million', curr: 'XAF', weather: 'Light Rain, 12°C', w_icon: 'bi-cloud-rain' },
        { id: 'GM', name: 'Gambia', flag: '🇬🇲', gdp: '0.5%', infl: '11.7%', pop: '2.3 Million', curr: 'GMD', weather: 'Sunny, 24°C', w_icon: 'bi-sun' },
        { id: 'GE', name: 'Georgia', flag: '🇬🇪', gdp: '0.2%', infl: '2.4%', pop: '3.7 Million', curr: 'GEL', weather: 'Scattered Clouds, 23°C', w_icon: 'bi-clouds' },
        { id: 'DE', name: 'Germany', flag: '🇩🇪', gdp: '5.2%', infl: '9.5%', pop: '82.9 Million', curr: 'EUR', weather: 'Scattered Clouds, 13°C', w_icon: 'bi-clouds' },
        { id: 'GH', name: 'Ghana', flag: '🇬🇭', gdp: '-2.0%', infl: '12.4%', pop: '29.8 Million', curr: 'GHS', weather: 'Partly Cloudy, 21°C', w_icon: 'bi-cloud-sun' },
        { id: 'GI', name: 'Gibraltar', flag: '🇬🇮', gdp: '-1.5%', infl: '2.6%', pop: '33,718', curr: 'GIP', weather: 'Scattered Clouds, 36°C', w_icon: 'bi-clouds' },
        { id: 'GR', name: 'Greece', flag: '🇬🇷', gdp: '-1.5%', infl: '1.9%', pop: '10.7 Million', curr: 'EUR', weather: 'Snow, 26°C', w_icon: 'bi-snow' },
        { id: 'GL', name: 'Greenland', flag: '🇬🇱', gdp: '7.0%', infl: '12.9%', pop: '56,025', curr: 'DKK', weather: 'Partly Cloudy, -3°C', w_icon: 'bi-cloud-sun' },
        { id: 'GD', name: 'Grenada', flag: '🇬🇩', gdp: '2.1%', infl: '7.2%', pop: '111,454', curr: 'XCD', weather: 'Thunderstorms, -4°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'GP', name: 'Guadeloupe', flag: '🇬🇵', gdp: '0.8%', infl: '7.0%', pop: '395,700', curr: 'EUR', weather: 'Cloudy, 7°C', w_icon: 'bi-clouds' },
        { id: 'GU', name: 'Guam', flag: '🇬🇺', gdp: '-0.4%', infl: '9.1%', pop: '165,768', curr: 'USD', weather: 'Scattered Clouds, 16°C', w_icon: 'bi-clouds' },
        { id: 'GT', name: 'Guatemala', flag: '🇬🇹', gdp: '0.7%', infl: '12.7%', pop: '17.2 Million', curr: 'QTQ', weather: 'Light Rain, -5°C', w_icon: 'bi-cloud-rain' },
        { id: 'GG', name: 'Guernsey', flag: '🇬🇬', gdp: '6.9%', infl: '2.3%', pop: '49.8 Million', curr: 'USD', weather: 'Thunderstorms, 2°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'GN', name: 'Guinea', flag: '🇬🇳', gdp: '0.9%', infl: '3.6%', pop: '12.4 Million', curr: 'GNF', weather: 'Clear Sky, 30°C', w_icon: 'bi-sun' },
        { id: 'GW', name: 'Guinea-Bissau', flag: '🇬🇼', gdp: '0.1%', infl: '5.5%', pop: '1.9 Million', curr: 'CFA', weather: 'Sunny, 0°C', w_icon: 'bi-sun' },
        { id: 'GY', name: 'Guyana', flag: '🇬🇾', gdp: '4.8%', infl: '2.3%', pop: '779,004', curr: 'GYD', weather: 'Thunderstorms, 35°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'HT', name: 'Haiti', flag: '🇭🇹', gdp: '2.1%', infl: '3.7%', pop: '11.1 Million', curr: 'HTG', weather: 'Thunderstorms, 4°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'HM', name: 'Heard Island and McDonald Islands', flag: '🇭🇲', gdp: '-0.2%', infl: '0.4%', pop: '12.6 Million', curr: 'AUD', weather: 'Light Rain, 13°C', w_icon: 'bi-cloud-rain' },
        { id: 'VA', name: 'Holy See', flag: '🇻🇦', gdp: '-0.9%', infl: '0.2%', pop: '48.2 Million', curr: 'USD', weather: 'Cloudy, -2°C', w_icon: 'bi-clouds' },
        { id: 'HN', name: 'Honduras', flag: '🇭🇳', gdp: '-1.7%', infl: '14.9%', pop: '9.6 Million', curr: 'HNL', weather: 'Sunny, 31°C', w_icon: 'bi-sun' },
        { id: 'HK', name: 'Hong Kong', flag: '🇭🇰', gdp: '3.5%', infl: '1.3%', pop: '7.5 Million', curr: 'HKD', weather: 'Clear Sky, 12°C', w_icon: 'bi-sun' },
        { id: 'HU', name: 'Hungary', flag: '🇭🇺', gdp: '0.1%', infl: '12.0%', pop: '9.8 Million', curr: 'HUF', weather: 'Light Rain, 34°C', w_icon: 'bi-cloud-rain' },
        { id: 'IS', name: 'Iceland', flag: '🇮🇸', gdp: '7.0%', infl: '3.4%', pop: '352,721', curr: 'ISK', weather: 'Thunderstorms, -4°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'IN', name: 'India', flag: '🇮🇳', gdp: '2.9%', infl: '11.2%', pop: '1.35 Billion', curr: 'INR', weather: 'Snow, 0°C', w_icon: 'bi-snow' },
        { id: 'ID', name: 'Indonesia', flag: '🇮🇩', gdp: '7.1%', infl: '1.0%', pop: '267.7 Million', curr: 'IDR', weather: 'Sunny, 26°C', w_icon: 'bi-sun' },
        { id: 'IR', name: 'Iran, Islamic Republic of', flag: '🇮🇷', gdp: '0.6%', infl: '13.7%', pop: '32.7 Million', curr: 'USD', weather: 'Clear Sky, -1°C', w_icon: 'bi-sun' },
        { id: 'IQ', name: 'Iraq', flag: '🇮🇶', gdp: '4.6%', infl: '3.3%', pop: '38.4 Million', curr: 'IQD', weather: 'Snow, 8°C', w_icon: 'bi-snow' },
        { id: 'IE', name: 'Ireland', flag: '🇮🇪', gdp: '4.4%', infl: '7.5%', pop: '4.9 Million', curr: 'EUR', weather: 'Heavy Rain, 12°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'IM', name: 'Isle of Man', flag: '🇮🇲', gdp: '5.2%', infl: '4.0%', pop: '5.3 Million', curr: 'USD', weather: 'Clear Sky, 1°C', w_icon: 'bi-sun' },
        { id: 'IL', name: 'Israel', flag: '🇮🇱', gdp: '0.4%', infl: '3.7%', pop: '8.9 Million', curr: 'ILS', weather: 'Sunny, 22°C', w_icon: 'bi-sun' },
        { id: 'IT', name: 'Italy', flag: '🇮🇹', gdp: '-1.7%', infl: '3.3%', pop: '60.4 Million', curr: 'EUR', weather: 'Sunny, 8°C', w_icon: 'bi-sun' },
        { id: 'JM', name: 'Jamaica', flag: '🇯🇲', gdp: '5.7%', infl: '10.6%', pop: '2.9 Million', curr: 'JMD', weather: 'Partly Cloudy, 17°C', w_icon: 'bi-cloud-sun' },
        { id: 'JP', name: 'Japan', flag: '🇯🇵', gdp: '-0.7%', infl: '5.1%', pop: '126.5 Million', curr: 'JPY', weather: 'Cloudy, 12°C', w_icon: 'bi-clouds' },
        { id: 'JE', name: 'Jersey', flag: '🇯🇪', gdp: '5.8%', infl: '5.2%', pop: '3.3 Million', curr: 'USD', weather: 'Clear Sky, 34°C', w_icon: 'bi-sun' },
        { id: 'JO', name: 'Jordan', flag: '🇯🇴', gdp: '0.1%', infl: '3.3%', pop: '10.0 Million', curr: 'JOD', weather: 'Snow, 35°C', w_icon: 'bi-snow' },
        { id: 'KZ', name: 'Kazakhstan', flag: '🇰🇿', gdp: '5.0%', infl: '14.7%', pop: '18.3 Million', curr: 'KZT', weather: 'Scattered Clouds, 17°C', w_icon: 'bi-clouds' },
        { id: 'KE', name: 'Kenya', flag: '🇰🇪', gdp: '2.0%', infl: '3.4%', pop: '51.4 Million', curr: 'KES', weather: 'Scattered Clouds, 10°C', w_icon: 'bi-clouds' },
        { id: 'KI', name: 'Kiribati', flag: '🇰🇮', gdp: '5.3%', infl: '4.7%', pop: '115,847', curr: 'AUD', weather: 'Cloudy, 22°C', w_icon: 'bi-clouds' },
        { id: 'KP', name: 'Korea, Democratic People\'s Republic of', flag: '🇰🇵', gdp: '2.2%', infl: '10.2%', pop: '49.4 Million', curr: 'USD', weather: 'Haze, 16°C', w_icon: 'bi-cloud-haze' },
        { id: 'KR', name: 'Korea, Republic of', flag: '🇰🇷', gdp: '4.3%', infl: '13.1%', pop: '47.0 Million', curr: 'USD', weather: 'Clear Sky, -4°C', w_icon: 'bi-sun' },
        { id: 'KW', name: 'Kuwait', flag: '🇰🇼', gdp: '1.6%', infl: '2.2%', pop: '4.1 Million', curr: 'KWD', weather: 'Light Rain, 1°C', w_icon: 'bi-cloud-rain' },
        { id: 'KG', name: 'Kyrgyzstan', flag: '🇰🇬', gdp: '1.8%', infl: '1.3%', pop: '6.3 Million', curr: 'KGS', weather: 'Sunny, 29°C', w_icon: 'bi-sun' },
        { id: 'LA', name: 'Lao People\'s Democratic Republic', flag: '🇱🇦', gdp: '7.0%', infl: '2.1%', pop: '8.3 Million', curr: 'USD', weather: 'Clear Sky, 21°C', w_icon: 'bi-sun' },
        { id: 'LV', name: 'Latvia', flag: '🇱🇻', gdp: '-0.1%', infl: '4.2%', pop: '1.9 Million', curr: 'LVL', weather: 'Clear Sky, 32°C', w_icon: 'bi-sun' },
        { id: 'LB', name: 'Lebanon', flag: '🇱🇧', gdp: '5.5%', infl: '3.8%', pop: '6.8 Million', curr: 'LBP', weather: 'Haze, 29°C', w_icon: 'bi-cloud-haze' },
        { id: 'LS', name: 'Lesotho', flag: '🇱🇸', gdp: '-1.7%', infl: '12.7%', pop: '2.1 Million', curr: 'LSL', weather: 'Clear Sky, 15°C', w_icon: 'bi-sun' },
        { id: 'LR', name: 'Liberia', flag: '🇱🇷', gdp: '2.4%', infl: '7.9%', pop: '4.8 Million', curr: 'LRD', weather: 'Scattered Clouds, 33°C', w_icon: 'bi-clouds' },
        { id: 'LY', name: 'Libya', flag: '🇱🇾', gdp: '1.9%', infl: '11.8%', pop: '25.0 Million', curr: 'USD', weather: 'Haze, 23°C', w_icon: 'bi-cloud-haze' },
        { id: 'LI', name: 'Liechtenstein', flag: '🇱🇮', gdp: '6.6%', infl: '9.7%', pop: '37,910', curr: 'CHF', weather: 'Cloudy, -5°C', w_icon: 'bi-clouds' },
        { id: 'LT', name: 'Lithuania', flag: '🇱🇹', gdp: '3.7%', infl: '4.1%', pop: '2.8 Million', curr: 'LTL', weather: 'Cloudy, 18°C', w_icon: 'bi-clouds' },
        { id: 'LU', name: 'Luxembourg', flag: '🇱🇺', gdp: '1.5%', infl: '5.9%', pop: '607,950', curr: 'EUR', weather: 'Clear Sky, 1°C', w_icon: 'bi-sun' },
        { id: 'MO', name: 'Macao', flag: '🇲🇴', gdp: '5.2%', infl: '6.0%', pop: '631,636', curr: 'MOP', weather: 'Haze, 16°C', w_icon: 'bi-cloud-haze' },
        { id: 'MG', name: 'Madagascar', flag: '🇲🇬', gdp: '0.4%', infl: '12.4%', pop: '26.3 Million', curr: 'MGF', weather: 'Light Rain, 18°C', w_icon: 'bi-cloud-rain' },
        { id: 'MW', name: 'Malawi', flag: '🇲🇼', gdp: '0.2%', infl: '10.5%', pop: '18.1 Million', curr: 'MWK', weather: 'Heavy Rain, 15°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'MY', name: 'Malaysia', flag: '🇲🇾', gdp: '2.2%', infl: '13.4%', pop: '31.5 Million', curr: 'MYR', weather: 'Sunny, 4°C', w_icon: 'bi-sun' },
        { id: 'MV', name: 'Maldives', flag: '🇲🇻', gdp: '0.3%', infl: '10.2%', pop: '515,696', curr: 'MVR', weather: 'Light Rain, 22°C', w_icon: 'bi-cloud-rain' },
        { id: 'ML', name: 'Mali', flag: '🇲🇱', gdp: '4.0%', infl: '3.8%', pop: '19.1 Million', curr: 'XOF', weather: 'Heavy Rain, 19°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'MT', name: 'Malta', flag: '🇲🇹', gdp: '2.6%', infl: '4.0%', pop: '484,630', curr: 'EUR', weather: 'Sunny, 5°C', w_icon: 'bi-sun' },
        { id: 'MH', name: 'Marshall Islands', flag: '🇲🇭', gdp: '1.2%', infl: '1.6%', pop: '58,413', curr: 'USD', weather: 'Thunderstorms, 7°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'MQ', name: 'Martinique', flag: '🇲🇶', gdp: '0.8%', infl: '4.2%', pop: '376,480', curr: 'EUR', weather: 'Light Rain, 15°C', w_icon: 'bi-cloud-rain' },
        { id: 'MR', name: 'Mauritania', flag: '🇲🇷', gdp: '7.4%', infl: '7.2%', pop: '4.4 Million', curr: 'MRO', weather: 'Scattered Clouds, 14°C', w_icon: 'bi-clouds' },
        { id: 'MU', name: 'Mauritius', flag: '🇲🇺', gdp: '1.5%', infl: '14.7%', pop: '1.3 Million', curr: 'MUR', weather: 'Thunderstorms, 30°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'YT', name: 'Mayotte', flag: '🇾🇹', gdp: '2.1%', infl: '8.7%', pop: '270,372', curr: 'EUR', weather: 'Cloudy, 23°C', w_icon: 'bi-clouds' },
        { id: 'MX', name: 'Mexico', flag: '🇲🇽', gdp: '6.3%', infl: '0.3%', pop: '126.2 Million', curr: 'MXN', weather: 'Partly Cloudy, 20°C', w_icon: 'bi-cloud-sun' },
        { id: 'FM', name: 'Micronesia, Federated States of', flag: '🇫🇲', gdp: '-1.3%', infl: '10.6%', pop: '112,640', curr: 'USD', weather: 'Snow, 1°C', w_icon: 'bi-snow' },
        { id: 'MD', name: 'Moldova, Republic of', flag: '🇲🇩', gdp: '4.0%', infl: '6.2%', pop: '1.6 Million', curr: 'USD', weather: 'Snow, 13°C', w_icon: 'bi-snow' },
        { id: 'MC', name: 'Monaco', flag: '🇲🇨', gdp: '2.6%', infl: '0.4%', pop: '38,682', curr: 'EUR', weather: 'Thunderstorms, 13°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'MN', name: 'Mongolia', flag: '🇲🇳', gdp: '5.8%', infl: '11.6%', pop: '3.2 Million', curr: 'MNT', weather: 'Cloudy, -1°C', w_icon: 'bi-clouds' },
        { id: 'ME', name: 'Montenegro', flag: '🇲🇪', gdp: '-0.6%', infl: '5.4%', pop: '631,219', curr: 'USD', weather: 'Thunderstorms, 10°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'MS', name: 'Montserrat', flag: '🇲🇸', gdp: '5.6%', infl: '12.4%', pop: '5,900', curr: 'XCD', weather: 'Haze, 11°C', w_icon: 'bi-cloud-haze' },
        { id: 'MA', name: 'Morocco', flag: '🇲🇦', gdp: '6.6%', infl: '7.2%', pop: '36.0 Million', curr: 'MAD', weather: 'Snow, 2°C', w_icon: 'bi-snow' },
        { id: 'MZ', name: 'Mozambique', flag: '🇲🇿', gdp: '-1.3%', infl: '5.3%', pop: '29.5 Million', curr: 'MZN', weather: 'Clear Sky, 7°C', w_icon: 'bi-sun' },
        { id: 'MM', name: 'Myanmar', flag: '🇲🇲', gdp: '5.5%', infl: '1.6%', pop: '53.7 Million', curr: 'MMR', weather: 'Partly Cloudy, 3°C', w_icon: 'bi-cloud-sun' },
        { id: 'NA', name: 'Namibia', flag: '🇳🇦', gdp: '0.4%', infl: '0.8%', pop: '2.4 Million', curr: 'NAD', weather: 'Sunny, 34°C', w_icon: 'bi-sun' },
        { id: 'NR', name: 'Nauru', flag: '🇳🇷', gdp: '2.5%', infl: '8.3%', pop: '12,704', curr: 'AUD', weather: 'Thunderstorms, -3°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'NP', name: 'Nepal', flag: '🇳🇵', gdp: '3.1%', infl: '9.2%', pop: '28.1 Million', curr: 'NPR', weather: 'Clear Sky, 19°C', w_icon: 'bi-sun' },
        { id: 'NL', name: 'Netherlands', flag: '🇳🇱', gdp: '0.1%', infl: '14.5%', pop: '17.2 Million', curr: 'EUR', weather: 'Cloudy, -4°C', w_icon: 'bi-clouds' },
        { id: 'NC', name: 'New Caledonia', flag: '🇳🇨', gdp: '-0.3%', infl: '5.7%', pop: '284,060', curr: 'XPF', weather: 'Clear Sky, 14°C', w_icon: 'bi-sun' },
        { id: 'NZ', name: 'New Zealand', flag: '🇳🇿', gdp: '4.2%', infl: '5.6%', pop: '4.8 Million', curr: 'NZD', weather: 'Heavy Rain, -1°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'NI', name: 'Nicaragua', flag: '🇳🇮', gdp: '3.8%', infl: '13.4%', pop: '6.5 Million', curr: 'NIO', weather: 'Heavy Rain, 24°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'NE', name: 'Niger', flag: '🇳🇪', gdp: '2.2%', infl: '0.1%', pop: '22.4 Million', curr: 'XOF', weather: 'Cloudy, 22°C', w_icon: 'bi-clouds' },
        { id: 'NG', name: 'Nigeria', flag: '🇳🇬', gdp: '1.9%', infl: '14.1%', pop: '195.9 Million', curr: 'NGN', weather: 'Clear Sky, 12°C', w_icon: 'bi-sun' },
        { id: 'NU', name: 'Niue', flag: '🇳🇺', gdp: '-1.6%', infl: '0.7%', pop: '1,624', curr: 'NZD', weather: 'Cloudy, 31°C', w_icon: 'bi-clouds' },
        { id: 'NF', name: 'Norfolk Island', flag: '🇳🇫', gdp: '5.0%', infl: '0.2%', pop: '2,169', curr: 'AUD', weather: 'Partly Cloudy, 39°C', w_icon: 'bi-cloud-sun' },
        { id: 'MK', name: 'North Macedonia', flag: '🇲🇰', gdp: '0.0%', infl: '12.7%', pop: '2.1 Million', curr: 'MKD', weather: 'Partly Cloudy, 22°C', w_icon: 'bi-cloud-sun' },
        { id: 'MP', name: 'Northern Mariana Islands', flag: '🇲🇵', gdp: '1.5%', infl: '7.7%', pop: '56,882', curr: 'USD', weather: 'Thunderstorms, 15°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'NO', name: 'Norway', flag: '🇳🇴', gdp: '-0.7%', infl: '0.6%', pop: '5.3 Million', curr: 'NOK', weather: 'Partly Cloudy, 8°C', w_icon: 'bi-cloud-sun' },
        { id: 'OM', name: 'Oman', flag: '🇴🇲', gdp: '6.7%', infl: '7.5%', pop: '4.8 Million', curr: 'OMR', weather: 'Snow, 0°C', w_icon: 'bi-snow' },
        { id: 'PK', name: 'Pakistan', flag: '🇵🇰', gdp: '4.5%', infl: '1.7%', pop: '212.2 Million', curr: 'PKR', weather: 'Clear Sky, 29°C', w_icon: 'bi-sun' },
        { id: 'PW', name: 'Palau', flag: '🇵🇼', gdp: '0.9%', infl: '12.7%', pop: '17,907', curr: 'USD', weather: 'Heavy Rain, 31°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'PS', name: 'Palestine, State of', flag: '🇵🇸', gdp: '6.5%', infl: '14.2%', pop: '26.8 Million', curr: 'USD', weather: 'Partly Cloudy, 0°C', w_icon: 'bi-cloud-sun' },
        { id: 'PA', name: 'Panama', flag: '🇵🇦', gdp: '0.8%', infl: '8.9%', pop: '4.2 Million', curr: 'PAB', weather: 'Haze, 9°C', w_icon: 'bi-cloud-haze' },
        { id: 'PG', name: 'Papua New Guinea', flag: '🇵🇬', gdp: '6.8%', infl: '14.4%', pop: '8.6 Million', curr: 'PGK', weather: 'Cloudy, 33°C', w_icon: 'bi-clouds' },
        { id: 'PY', name: 'Paraguay', flag: '🇵🇾', gdp: '3.6%', infl: '9.4%', pop: '7.0 Million', curr: 'PYG', weather: 'Sunny, 34°C', w_icon: 'bi-sun' },
        { id: 'PE', name: 'Peru', flag: '🇵🇪', gdp: '7.2%', infl: '13.5%', pop: '32.0 Million', curr: 'PEN', weather: 'Thunderstorms, 17°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'PH', name: 'Philippines', flag: '🇵🇭', gdp: '6.2%', infl: '6.6%', pop: '106.7 Million', curr: 'PHP', weather: 'Snow, 13°C', w_icon: 'bi-snow' },
        { id: 'PN', name: 'Pitcairn', flag: '🇵🇳', gdp: '6.5%', infl: '2.6%', pop: '67', curr: 'NZD', weather: 'Haze, 25°C', w_icon: 'bi-cloud-haze' },
        { id: 'PL', name: 'Poland', flag: '🇵🇱', gdp: '0.4%', infl: '3.4%', pop: '38.0 Million', curr: 'PLN', weather: 'Partly Cloudy, 4°C', w_icon: 'bi-cloud-sun' },
        { id: 'PT', name: 'Portugal', flag: '🇵🇹', gdp: '-0.3%', infl: '2.2%', pop: '10.3 Million', curr: 'EUR', weather: 'Haze, 4°C', w_icon: 'bi-cloud-haze' },
        { id: 'PR', name: 'Puerto Rico', flag: '🇵🇷', gdp: '1.0%', infl: '11.1%', pop: '3.2 Million', curr: 'USD', weather: 'Haze, 36°C', w_icon: 'bi-cloud-haze' },
        { id: 'QA', name: 'Qatar', flag: '🇶🇦', gdp: '2.3%', infl: '0.7%', pop: '2.8 Million', curr: 'QAR', weather: 'Snow, 35°C', w_icon: 'bi-snow' },
        { id: 'RO', name: 'Romania', flag: '🇷🇴', gdp: '7.7%', infl: '1.8%', pop: '19.5 Million', curr: 'RON', weather: 'Thunderstorms, 37°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'RU', name: 'Russian Federation', flag: '🇷🇺', gdp: '-1.4%', infl: '11.3%', pop: '144.5 Million', curr: 'RUB', weather: 'Heavy Rain, 14°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'RW', name: 'Rwanda', flag: '🇷🇼', gdp: '-0.2%', infl: '14.3%', pop: '12.3 Million', curr: 'RWF', weather: 'Thunderstorms, 5°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'RE', name: 'Réunion', flag: '🇷🇪', gdp: '0.8%', infl: '0.0%', pop: '33.8 Million', curr: 'USD', weather: 'Light Rain, 24°C', w_icon: 'bi-cloud-rain' },
        { id: 'BL', name: 'Saint Barthélemy', flag: '🇧🇱', gdp: '3.1%', infl: '13.6%', pop: '23.9 Million', curr: 'USD', weather: 'Thunderstorms, -2°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'SH', name: 'Saint Helena, Ascension and Tristan da Cunha', flag: '🇸🇭', gdp: '3.9%', infl: '6.2%', pop: '45.7 Million', curr: 'USD', weather: 'Haze, 0°C', w_icon: 'bi-cloud-haze' },
        { id: 'KN', name: 'Saint Kitts and Nevis', flag: '🇰🇳', gdp: '-1.8%', infl: '8.6%', pop: '52,441', curr: 'XCD', weather: 'Light Rain, 38°C', w_icon: 'bi-cloud-rain' },
        { id: 'LC', name: 'Saint Lucia', flag: '🇱🇨', gdp: '2.3%', infl: '0.8%', pop: '181,889', curr: 'XCD', weather: 'Haze, 30°C', w_icon: 'bi-cloud-haze' },
        { id: 'MF', name: 'Saint Martin, (French part)', flag: '🇲🇫', gdp: '2.6%', infl: '13.9%', pop: '40.7 Million', curr: 'USD', weather: 'Snow, 4°C', w_icon: 'bi-snow' },
        { id: 'PM', name: 'Saint Pierre and Miquelon', flag: '🇵🇲', gdp: '7.4%', infl: '6.9%', pop: '5,888', curr: 'EUR', weather: 'Heavy Rain, 6°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'VC', name: 'Saint Vincent and the Grenadines', flag: '🇻🇨', gdp: '1.3%', infl: '13.4%', pop: '110,210', curr: 'XCD', weather: 'Snow, 19°C', w_icon: 'bi-snow' },
        { id: 'WS', name: 'Samoa', flag: '🇼🇸', gdp: '2.5%', infl: '13.1%', pop: '196,130', curr: 'WST', weather: 'Partly Cloudy, -4°C', w_icon: 'bi-cloud-sun' },
        { id: 'SM', name: 'San Marino', flag: '🇸🇲', gdp: '5.1%', infl: '11.4%', pop: '33,785', curr: 'EUR', weather: 'Heavy Rain, 8°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'ST', name: 'Sao Tome and Principe', flag: '🇸🇹', gdp: '7.6%', infl: '10.5%', pop: '211,028', curr: 'STD', weather: 'Cloudy, 7°C', w_icon: 'bi-clouds' },
        { id: 'SA', name: 'Saudi Arabia', flag: '🇸🇦', gdp: '3.7%', infl: '1.9%', pop: '33.7 Million', curr: 'SAR', weather: 'Heavy Rain, 19°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'SN', name: 'Senegal', flag: '🇸🇳', gdp: '-1.8%', infl: '11.7%', pop: '15.9 Million', curr: 'XOF', weather: 'Cloudy, 35°C', w_icon: 'bi-clouds' },
        { id: 'RS', name: 'Serbia', flag: '🇷🇸', gdp: '5.8%', infl: '2.7%', pop: '7.0 Million', curr: 'RSD', weather: 'Cloudy, -3°C', w_icon: 'bi-clouds' },
        { id: 'SC', name: 'Seychelles', flag: '🇸🇨', gdp: '6.8%', infl: '1.3%', pop: '96,762', curr: 'SCR', weather: 'Cloudy, 1°C', w_icon: 'bi-clouds' },
        { id: 'SL', name: 'Sierra Leone', flag: '🇸🇱', gdp: '1.8%', infl: '13.6%', pop: '7.7 Million', curr: 'SLL', weather: 'Clear Sky, -5°C', w_icon: 'bi-sun' },
        { id: 'SG', name: 'Singapore', flag: '🇸🇬', gdp: '-1.4%', infl: '14.4%', pop: '5.6 Million', curr: 'SGD', weather: 'Clear Sky, 27°C', w_icon: 'bi-sun' },
        { id: 'SX', name: 'Sint Maarten, (Dutch part)', flag: '🇸🇽', gdp: '3.1%', infl: '3.1%', pop: '42.9 Million', curr: 'USD', weather: 'Heavy Rain, 13°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'SK', name: 'Slovakia', flag: '🇸🇰', gdp: '5.9%', infl: '5.7%', pop: '5.4 Million', curr: 'EUR', weather: 'Sunny, 31°C', w_icon: 'bi-sun' },
        { id: 'SI', name: 'Slovenia', flag: '🇸🇮', gdp: '1.6%', infl: '8.5%', pop: '2.1 Million', curr: 'EUR', weather: 'Thunderstorms, 8°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'SB', name: 'Solomon Islands', flag: '🇸🇧', gdp: '3.5%', infl: '0.1%', pop: '652,858', curr: 'SBD', weather: 'Thunderstorms, 23°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'SO', name: 'Somalia', flag: '🇸🇴', gdp: '-2.0%', infl: '10.7%', pop: '15.0 Million', curr: 'SOS', weather: 'Light Rain, 7°C', w_icon: 'bi-cloud-rain' },
        { id: 'ZA', name: 'South Africa', flag: '🇿🇦', gdp: '3.5%', infl: '10.0%', pop: '57.8 Million', curr: 'ZAR', weather: 'Thunderstorms, 1°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'GS', name: 'South Georgia and the South Sandwich Islands', flag: '🇬🇸', gdp: '6.6%', infl: '9.2%', pop: '30', curr: 'GBP', weather: 'Light Rain, 8°C', w_icon: 'bi-cloud-rain' },
        { id: 'SS', name: 'South Sudan', flag: '🇸🇸', gdp: '3.9%', infl: '10.8%', pop: '11.0 Million', curr: 'SSP', weather: 'Sunny, 14°C', w_icon: 'bi-sun' },
        { id: 'ES', name: 'Spain', flag: '🇪🇸', gdp: '7.0%', infl: '11.6%', pop: '46.8 Million', curr: 'EUR', weather: 'Haze, 30°C', w_icon: 'bi-cloud-haze' },
        { id: 'LK', name: 'Sri Lanka', flag: '🇱🇰', gdp: '4.8%', infl: '5.1%', pop: '21.7 Million', curr: 'LKR', weather: 'Partly Cloudy, 30°C', w_icon: 'bi-cloud-sun' },
        { id: 'SD', name: 'Sudan', flag: '🇸🇩', gdp: '4.5%', infl: '13.9%', pop: '41.8 Million', curr: 'SDG', weather: 'Scattered Clouds, 0°C', w_icon: 'bi-clouds' },
        { id: 'SR', name: 'Suriname', flag: '🇸🇷', gdp: '4.8%', infl: '2.8%', pop: '575,991', curr: 'SRD', weather: 'Haze, 11°C', w_icon: 'bi-cloud-haze' },
        { id: 'SJ', name: 'Svalbard and Jan Mayen', flag: '🇸🇯', gdp: '1.8%', infl: '11.7%', pop: '2,572', curr: 'NOK', weather: 'Heavy Rain, 30°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'SE', name: 'Sweden', flag: '🇸🇪', gdp: '2.4%', infl: '0.9%', pop: '10.2 Million', curr: 'SEK', weather: 'Cloudy, 20°C', w_icon: 'bi-clouds' },
        { id: 'CH', name: 'Switzerland', flag: '🇨🇭', gdp: '3.6%', infl: '8.6%', pop: '8.5 Million', curr: 'CHF', weather: 'Clear Sky, -5°C', w_icon: 'bi-sun' },
        { id: 'SY', name: 'Syrian Arab Republic', flag: '🇸🇾', gdp: '6.0%', infl: '11.7%', pop: '39.3 Million', curr: 'USD', weather: 'Light Rain, 6°C', w_icon: 'bi-cloud-rain' },
        { id: 'TW', name: 'Taiwan, Province of China', flag: '🇹🇼', gdp: '1.0%', infl: '11.3%', pop: '4.1 Million', curr: 'USD', weather: 'Cloudy, 19°C', w_icon: 'bi-clouds' },
        { id: 'TJ', name: 'Tajikistan', flag: '🇹🇯', gdp: '-1.5%', infl: '11.8%', pop: '9.1 Million', curr: 'TJS', weather: 'Sunny, 2°C', w_icon: 'bi-sun' },
        { id: 'TZ', name: 'Tanzania, United Republic of', flag: '🇹🇿', gdp: '4.4%', infl: '14.1%', pop: '1.8 Million', curr: 'USD', weather: 'Heavy Rain, 15°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'TH', name: 'Thailand', flag: '🇹🇭', gdp: '2.7%', infl: '14.9%', pop: '69.4 Million', curr: 'THB', weather: 'Sunny, 5°C', w_icon: 'bi-sun' },
        { id: 'TL', name: 'Timor-Leste', flag: '🇹🇱', gdp: '7.7%', infl: '11.4%', pop: '24.5 Million', curr: 'USD', weather: 'Partly Cloudy, 1°C', w_icon: 'bi-cloud-sun' },
        { id: 'TG', name: 'Togo', flag: '🇹🇬', gdp: '7.6%', infl: '14.0%', pop: '7.9 Million', curr: 'XOF', weather: 'Light Rain, 11°C', w_icon: 'bi-cloud-rain' },
        { id: 'TK', name: 'Tokelau', flag: '🇹🇰', gdp: '-0.4%', infl: '12.2%', pop: '1,411', curr: 'NZD', weather: 'Thunderstorms, 7°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'TO', name: 'Tonga', flag: '🇹🇴', gdp: '4.8%', infl: '3.5%', pop: '103,197', curr: 'TOP', weather: 'Haze, 11°C', w_icon: 'bi-cloud-haze' },
        { id: 'TT', name: 'Trinidad and Tobago', flag: '🇹🇹', gdp: '7.4%', infl: '2.2%', pop: '1.4 Million', curr: 'TTD', weather: 'Cloudy, -3°C', w_icon: 'bi-clouds' },
        { id: 'TN', name: 'Tunisia', flag: '🇹🇳', gdp: '-1.8%', infl: '8.0%', pop: '11.6 Million', curr: 'TND', weather: 'Sunny, -5°C', w_icon: 'bi-sun' },
        { id: 'TM', name: 'Turkmenistan', flag: '🇹🇲', gdp: '1.7%', infl: '4.8%', pop: '5.9 Million', curr: 'TMT', weather: 'Scattered Clouds, 16°C', w_icon: 'bi-clouds' },
        { id: 'TC', name: 'Turks and Caicos Islands', flag: '🇹🇨', gdp: '3.6%', infl: '12.4%', pop: '37,665', curr: 'USD', weather: 'Thunderstorms, 37°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'TV', name: 'Tuvalu', flag: '🇹🇻', gdp: '7.2%', infl: '13.8%', pop: '11,508', curr: 'AUD', weather: 'Light Rain, 31°C', w_icon: 'bi-cloud-rain' },
        { id: 'TR', name: 'Türkiye', flag: '🇹🇷', gdp: '7.5%', infl: '14.7%', pop: '19.3 Million', curr: 'USD', weather: 'Heavy Rain, 1°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'UG', name: 'Uganda', flag: '🇺🇬', gdp: '0.7%', infl: '4.2%', pop: '42.7 Million', curr: 'UGX', weather: 'Clear Sky, 7°C', w_icon: 'bi-sun' },
        { id: 'UA', name: 'Ukraine', flag: '🇺🇦', gdp: '5.1%', infl: '4.9%', pop: '44.6 Million', curr: 'UAH', weather: 'Thunderstorms, 20°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'AE', name: 'United Arab Emirates', flag: '🇦🇪', gdp: '-0.1%', infl: '13.0%', pop: '9.6 Million', curr: 'AED', weather: 'Clear Sky, 1°C', w_icon: 'bi-sun' },
        { id: 'GB', name: 'United Kingdom of Great Britain and Northern Ireland', flag: '🇬🇧', gdp: '6.3%', infl: '3.9%', pop: '45.0 Million', curr: 'USD', weather: 'Thunderstorms, 4°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'UM', name: 'United States Minor Outlying Islands', flag: '🇺🇲', gdp: '-0.9%', infl: '1.0%', pop: '300', curr: 'USD', weather: 'Scattered Clouds, 0°C', w_icon: 'bi-clouds' },
        { id: 'US', name: 'United States of America', flag: '🇺🇸', gdp: '3.4%', infl: '2.9%', pop: '49.1 Million', curr: 'USD', weather: 'Haze, -4°C', w_icon: 'bi-cloud-haze' },
        { id: 'UY', name: 'Uruguay', flag: '🇺🇾', gdp: '0.5%', infl: '6.6%', pop: '3.4 Million', curr: 'UYU', weather: 'Heavy Rain, 17°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'UZ', name: 'Uzbekistan', flag: '🇺🇿', gdp: '0.3%', infl: '12.1%', pop: '33.0 Million', curr: 'UZS', weather: 'Haze, -1°C', w_icon: 'bi-cloud-haze' },
        { id: 'VU', name: 'Vanuatu', flag: '🇻🇺', gdp: '1.1%', infl: '0.4%', pop: '292,680', curr: 'VUV', weather: 'Heavy Rain, 36°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'VE', name: 'Venezuela, Bolivarian Republic of', flag: '🇻🇪', gdp: '2.0%', infl: '4.0%', pop: '13.8 Million', curr: 'USD', weather: 'Scattered Clouds, 35°C', w_icon: 'bi-clouds' },
        { id: 'VN', name: 'Viet Nam', flag: '🇻🇳', gdp: '4.0%', infl: '3.6%', pop: '20.5 Million', curr: 'USD', weather: 'Partly Cloudy, 18°C', w_icon: 'bi-cloud-sun' },
        { id: 'VG', name: 'Virgin Islands, British', flag: '🇻🇬', gdp: '-2.0%', infl: '1.3%', pop: '29,802', curr: 'USD', weather: 'Thunderstorms, 11°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'VI', name: 'Virgin Islands, U.S.', flag: '🇻🇮', gdp: '5.4%', infl: '3.4%', pop: '106,977', curr: 'USD', weather: 'Scattered Clouds, -3°C', w_icon: 'bi-clouds' },
        { id: 'WF', name: 'Wallis and Futuna', flag: '🇼🇫', gdp: '4.7%', infl: '13.3%', pop: '15,289', curr: 'XPF', weather: 'Heavy Rain, 36°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'EH', name: 'Western Sahara', flag: '🇪🇭', gdp: '1.1%', infl: '12.1%', pop: '652,271', curr: 'MAD', weather: 'Partly Cloudy, -4°C', w_icon: 'bi-cloud-sun' },
        { id: 'YE', name: 'Yemen', flag: '🇾🇪', gdp: '3.9%', infl: '11.6%', pop: '28.5 Million', curr: 'YER', weather: 'Haze, 25°C', w_icon: 'bi-cloud-haze' },
        { id: 'ZM', name: 'Zambia', flag: '🇿🇲', gdp: '0.6%', infl: '10.2%', pop: '17.4 Million', curr: 'ZMW', weather: 'Clear Sky, 23°C', w_icon: 'bi-sun' },
        { id: 'ZW', name: 'Zimbabwe', flag: '🇿🇼', gdp: '5.3%', infl: '9.4%', pop: '14.4 Million', curr: 'ZWD', weather: 'Cloudy, 22°C', w_icon: 'bi-clouds' },
        { id: 'AX', name: 'Åland Islands', flag: '🇦🇽', gdp: '0.9%', infl: '9.4%', pop: '18.2 Million', curr: 'USD', weather: 'Light Rain, 32°C', w_icon: 'bi-cloud-rain' }
    ];

    const econData = allCountries.map(c => {
        return {
            label: c.name + ' (' + c.id + ')',
            gdp: parseFloat(c.gdp) || 0,
            infl: parseFloat(c.infl) || 0
        };
    });

    // Populate Select Dropdowns
    const econSelect = document.getElementById('econCountrySelect');
    const riskSelect = document.getElementById('riskTrendCountrySelect');

    econData.forEach(item => {
        let opt = document.createElement('option');
        opt.value = item.label;
        opt.innerHTML = item.label;
        econSelect.appendChild(opt);

        let opt2 = document.createElement('option');
        opt2.value = item.label;
        opt2.innerHTML = item.label;
        riskSelect.appendChild(opt2);
    });

    // Handle Risk Trend Select Change
    riskSelect.addEventListener('change', (e) => {
        const val = e.target.value;
        const titleEl = document.getElementById('riskTrendTitle');
        
        if(val === 'GLOBAL') {
            titleEl.innerHTML = '<i class="bi bi-graph-up-arrow text-primary me-2"></i>Global Risk Trend (6 Months)';
            riskChart.data.datasets[0].data = [...baseRiskData];
            riskChart.data.datasets[0].label = 'Average Global Risk Score';
        } else {
            const countryName = val.split(' (')[0];
            titleEl.innerHTML = `<i class="bi bi-graph-up-arrow text-primary me-2"></i>${countryName} Risk Trend (6 Months)`;
            
            const cItem = econData.find(c => c.label === val);
            const seed = cItem ? cItem.infl : countryName.length;
            
            const newData = baseRiskData.map((v, i) => {
                let nv = v + (seed * 2) - 10 + (i * (seed % 3));
                if(nv > 100) nv = Math.floor(Math.random() * 10) + 90;
                if(nv < 0) nv = Math.floor(Math.random() * 10) + 5;
                return nv;
            });
            
            riskChart.data.datasets[0].data = newData;
            riskChart.data.datasets[0].label = `${countryName} Risk Score`;
        }
        riskChart.update();
    });

    const ctxEcon = document.getElementById('economicImpactChart').getContext('2d');
    let econChart = new Chart(ctxEcon, {
        type: 'line',
        data: getEconChartData(econData.slice(0, 11)), // show top 11 by default
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                tooltip: { 
                    mode: 'index', 
                    intersect: false,
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#333',
                    bodyColor: '#666',
                    borderColor: '#ddd',
                    borderWidth: 1,
                    padding: 10
                },
                legend: {
                    position: 'top',
                    labels: { usePointStyle: true, boxWidth: 8 }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false }
                },
                y: {
                    grid: { 
                        color: '#f0f0f0',
                        borderDash: [5, 5] 
                    },
                    border: { display: false }
                }
            }
        }
    });

    function getEconChartData(filteredData) {
        return {
            labels: filteredData.map(d => d.label),
            datasets: [
                {
                    type: 'line',
                    label: 'GDP Growth (%)',
                    data: filteredData.map(d => d.gdp),
                    borderColor: '#28a745', // Sharp Green
                    backgroundColor: 'rgba(40, 167, 69, 0.15)', // Lighter green fill
                    borderWidth: 2,
                    tension: 0, // 0 for sharp straight lines like stocks
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#28a745',
                    pointRadius: 0, // Hidden until hover
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#28a745',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2
                },
                {
                    type: 'line',
                    label: 'Inflation Rate (%)',
                    data: filteredData.map(d => d.infl),
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.05)', // Very faint red fill
                    borderWidth: 2,
                    tension: 0, // Sharp straight lines
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#dc3545',
                    pointRadius: 0, // Hidden until hover
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#dc3545',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2
                }
            ]
        };
    }

    // Filter Logic
    function updateEconChart() {
        const selected = econSelect.value;
        const search = document.getElementById('econSearchInput').value.toLowerCase();
        
        let filtered = econData;
        
        if (selected !== 'ALL') {
            filtered = filtered.filter(d => d.label === selected);
        }
        
        if (search) {
            filtered = filtered.filter(d => d.label.toLowerCase().includes(search));
        }
        
        if(selected === 'ALL' && !search) {
            filtered = econData.slice(0, 11);
        } else if (filtered.length === 1) {
            // Add Global Average for comparison so the chart renders properly
            const avgGdp = (econData.reduce((sum, d) => sum + d.gdp, 0) / econData.length).toFixed(1);
            const avgInfl = (econData.reduce((sum, d) => sum + d.infl, 0) / econData.length).toFixed(1);
            filtered.push({ label: 'Global Average', gdp: parseFloat(avgGdp), infl: parseFloat(avgInfl) });
        }

        econChart.data = getEconChartData(filtered);
        econChart.update();
        
        // Trigger Live News update
        loadDashboardNews(selected);
    }

    econSelect.addEventListener('change', updateEconChart);
    document.getElementById('econSearchInput').addEventListener('input', updateEconChart);
    
    // --- Live News Widget Logic ---
    async function loadDashboardNews(countryCode) {
        const container = document.getElementById('dashboardNewsContainer');
        const label = document.getElementById('newsCountryLabel');
        
        container.innerHTML = '<div class="col-12 text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>Fetching real-time updates...</div>';
        
        let endpoint = '/api/news/global?max=3';
        let displayCountry = 'Global';
        
        if (countryCode !== 'ALL') {
            const countryData = econData.find(d => d.label === countryCode);
            displayCountry = countryData ? countryData.label.split(' (')[0] : countryCode; // E.g., 'China'
            endpoint = `/api/news?country=${encodeURIComponent(displayCountry)}&max=3`;
        }
        
        label.textContent = displayCountry;
        
        try {
            const response = await fetch(endpoint);
            const result = await response.json();
            
            if (result.status === 'success' && result.data.length > 0) {
                container.innerHTML = '';
                const articles = result.data.slice(0, 3);
                
                articles.forEach(article => {
                    const badgeClass = article.sentiment_label === 'positive' ? 'bg-success' : (article.sentiment_label === 'negative' ? 'bg-danger' : 'bg-secondary');
                    
                    container.innerHTML += `
                        <div class="col-md-4">
                            <div class="card h-100 border p-3 rounded-4" style="transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'" onclick="window.open('${article.url}', '_blank')">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge ${badgeClass} bg-opacity-10 text-${badgeClass.split('-')[1]} rounded-pill">
                                        <i class="bi bi-${article.sentiment_icon} me-1"></i>${article.sentiment_label.toUpperCase()}
                                    </span>
                                    <small class="text-muted" style="font-size: 0.75rem;">${article.published_at_human}</small>
                                </div>
                                <h6 class="fw-bold mb-2 text-truncate" style="max-width: 100%; white-space: normal; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">${article.title}</h6>
                                <p class="text-muted small mb-0 text-truncate" style="max-width: 100%; white-space: normal; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">${article.description}</p>
                            </div>
                        </div>
                    `;
                });
            } else {
                container.innerHTML = '<div class="col-12 text-center py-4 text-muted">No news found for this region.</div>';
            }
        } catch (error) {
            container.innerHTML = '<div class="col-12 text-center py-4 text-danger">Failed to fetch live news.</div>';
        }
    }
    
    // Initial load for Global News
    loadDashboardNews('ALL');

    // 3. Port Congestion Distribution (Doughnut Chart)
    const ctxPort = document.getElementById('portCongestionChart').getContext('2d');
    new Chart(ctxPort, {
        type: 'doughnut',
        data: {
            labels: ['Operational (0-30%)', 'Delayed (31-60%)', 'Critical (>60%)'],
            datasets: [{
                data: [342, 28, 10],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


