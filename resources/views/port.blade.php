<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Port Monitoring - Risk Intelligence</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Leaflet CSS for Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
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
        
        .port-card {
            background: white;
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 20px;
            transition: all 0.3s ease;
            margin-bottom: 16px;
        }
        
        .port-card:hover {
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }
        
        .port-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 12px;
        }
        
        .port-name {
            font-size: 18px;
            font-weight: 700;
            color: #1a1d2e;
        }
        
        .port-location {
            font-size: 13px;
            color: #888;
            display: flex;
            align-items: center;
            gap: 4px;
            margin-top: 4px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-operational {
            background: rgba(40, 167, 69, 0.15);
            color: #28a745;
        }
        
        .status-delayed {
            background: rgba(255, 193, 7, 0.15);
            color: #ffc107;
        }
        
        .status-critical {
            background: rgba(220, 53, 69, 0.15);
            color: #dc3545;
        }
        
        .port-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 12px;
            margin: 12px 0;
            padding: 12px 0;
            border-top: 1px solid rgba(0,0,0,0.05);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 20px;
            font-weight: 700;
            color: #667eea;
        }
        
        .stat-label {
            font-size: 11px;
            color: #888;
            text-transform: uppercase;
            margin-top: 4px;
        }
        
        .port-activity {
            font-size: 13px;
            color: #666;
            margin-top: 12px;
            padding: 12px;
            background: rgba(102,126,234,0.05);
            border-radius: 8px;
            border-left: 3px solid #667eea;
        }
        
        .map-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 24px;
            height: 400px;
        }
        
        #portMap {
            width: 100%;
            height: 100%;
        }
        
        .filters-section {
            background: white;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .filters-section select, .filters-section input {
            border: 2px solid rgba(102,126,234,0.2) !important;
            border-radius: 8px;
            padding: 10px 12px !important;
        }
        
        .filters-section select:focus, .filters-section input:focus {
            border-color: #667eea !important;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1) !important;
        }
        
        @keyframes flowDash {
            to {
                stroke-dashoffset: -20;
            }
        }
        
        .animated-route {
            animation: flowDash 1s linear infinite;
        }

        @media (max-width: 768px) {
            #sidebar { width: 100% !important; }
            .header-section { padding: 16px; }
            .header-section h2 { font-size: 22px; }
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
                <a href="/" class="nav-link" title="Dashboard">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="/country" class="nav-link" title="Country Intelligence">
                    <i class="bi bi-globe me-2"></i> Country Intelligence
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/port" class="nav-link active" title="Port Monitoring">
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
            <h2><i class="bi bi-geo-alt me-2"></i>Port Monitoring</h2>
            <p>Real-time port operations and congestion tracking</p>
        </div>

        <!-- Filters -->
        <div class="filters-section">
            <div class="row g-2 mb-3">
                <div class="col-md-3">
                    <label class="form-label" style="font-size: 12px; font-weight: 600;">Filter by Country</label>
                    <select id="countryFilter" class="form-select" style="font-size: 14px;">
                        <option value="">All Countries</option>
                        <option value="China">China</option>
                        <option value="Singapore">Singapore</option>
                        <option value="Malaysia">Malaysia</option>
                        <option value="Indonesia">Indonesia</option>
                        <option value="Vietnam">Vietnam</option>
                        <option value="Thailand">Thailand</option>
                        <option value="Philippines">Philippines</option>
                        <option value="Hong Kong">Hong Kong</option>
                        <option value="South Korea">South Korea</option>
                        <option value="Sri Lanka">Sri Lanka</option>
                        <option value="UAE">UAE</option>
                        <option value="Netherlands">Netherlands</option>
                        <option value="Germany">Germany</option>
                        <option value="Belgium">Belgium</option>
                        <option value="United Kingdom">United Kingdom</option>
                        <option value="Spain">Spain</option>
                        <option value="USA">USA</option>
                        <option value="Canada">Canada</option>
                        <option value="Brazil">Brazil</option>
                        <option value="Argentina">Argentina</option>
                        <option value="Peru">Peru</option>
                        <option value="Egypt">Egypt</option>
                        <option value="South Africa">South Africa</option>
                        <option value="Nigeria">Nigeria</option>
                        <option value="Australia">Australia</option>
                        <option value="New Zealand">New Zealand</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" style="font-size: 12px; font-weight: 600;">Filter by Region</label>
                    <select id="regionFilter" class="form-select" style="font-size: 14px;">
                        <option value="">All Regions</option>
                        <option value="East Asia">East Asia</option>
                        <option value="Southeast Asia">Southeast Asia</option>
                        <option value="South Asia">South Asia</option>
                        <option value="Middle East">Middle East</option>
                        <option value="Europe">Europe</option>
                        <option value="North America">North America</option>
                        <option value="South America">South America</option>
                        <option value="Africa">Africa</option>
                        <option value="Oceania">Oceania</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" style="font-size: 12px; font-weight: 600;">Search Port</label>
                    <input type="text" id="searchPort" class="form-control" placeholder="Search by port name..." style="font-size: 14px;">
                </div>
                <div class="col-md-2" style="display: flex; flex-direction: column; gap: 5px;">
                    <div style="display: flex; gap: 5px;">
                        <button id="toggleRoutesBtn" class="btn btn-sm btn-outline-primary" style="flex: 1; font-size: 12px;">
                            <i class="bi bi-diagram-3 me-1"></i>Routes
                        </button>
                        <button id="toggleWeatherBtn" class="btn btn-sm btn-outline-warning" style="flex: 1; font-size: 12px;">
                            <i class="bi bi-cloud-lightning-rain me-1"></i>Weather
                        </button>
                    </div>
                    <button class="btn btn-sm btn-outline-info w-100" style="font-size: 12px;" data-bs-toggle="modal" data-bs-target="#distanceModal">
                        <i class="bi bi-geo-alt me-1"></i>Kalkulator Jarak
                    </button>
                </div>
        </div>

        <!-- Map -->
        <div class="map-container position-relative">
            <div id="portMap"></div>
            <div class="position-absolute bottom-0 start-0 m-3 z-3 bg-white p-2 rounded shadow-sm" style="font-size: 12px; z-index: 999; pointer-events: none; opacity: 0.8;">
                <i class="bi bi-arrows-move me-1"></i> Tip: Click and drag to pan the map anywhere
            </div>
            
            <!-- Map Legend -->
            <div class="position-absolute bottom-0 end-0 m-3 z-3 bg-white p-2 rounded shadow-sm border" style="font-size: 12px; z-index: 999; opacity: 0.9;">
                <div class="fw-bold mb-1 border-bottom pb-1"><i class="bi bi-info-circle me-1"></i>Status Pelabuhan</div>
                <div class="d-flex align-items-center mb-1"><span class="d-inline-block rounded-circle me-2" style="width:10px; height:10px; background-color:#28a745;"></span> Beroperasi Normal</div>
                <div class="d-flex align-items-center mb-1"><span class="d-inline-block rounded-circle me-2" style="width:10px; height:10px; background-color:#ffc107;"></span> Mengalami Penundaan</div>
                <div class="d-flex align-items-center"><span class="d-inline-block rounded-circle me-2" style="width:10px; height:10px; background-color:#dc3545;"></span> Status Kritis</div>
            </div>
        </div>

        <!-- Port List -->
        <h4 style="color: #1a1d2e; font-weight: 700; margin-bottom: 16px;">
            <i class="bi bi-list-ul me-2" style="color: #667eea;"></i>Major Ports Status
        </h4>
        <div id="portsList"></div>

        <!-- Footer -->
        <div class="mt-5 pt-4 border-top text-center text-muted small">
            <p><i class="bi bi-info-circle me-1"></i>Port data updated every 15 minutes</p>
            <p class="mb-0">Last updated: <strong id="lastUpdated">--</strong></p>
        </div>
    </main>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Global variables
    let ports = [];
    let map;
    let markers = [];
    window.shippingRoutesVisible = false;
    window.shippingRoutePolylines = [];
    window.weatherVisible = false;
    window.weatherZones = [];



    // Major shipping routes with realistic waypoints
    const shippingRoutes = [
        // Asia-Europe routes
        {from: 'Port of Shanghai', to: 'Port of Rotterdam', color: '#1f77b4', waypoints: [[1.2, 103.8], [5.9, 80.2], [12.5, 43.3], [31.2, 32.3], [36.0, -5.5], [46.0, -8.0]]},
        {from: 'Port of Shanghai', to: 'Port of Hamburg', color: '#1f77b4', waypoints: [[1.2, 103.8], [12.5, 43.3], [31.2, 32.3], [36.0, -5.5], [50.0, -2.0]]},
        {from: 'Port of Singapore', to: 'Port of Rotterdam', color: '#1f77b4', waypoints: [[5.9, 80.2], [12.5, 43.3], [31.2, 32.3], [36.0, -5.5], [46.0, -8.0]]},
        {from: 'Port of Singapore', to: 'Port of Hamburg', color: '#1f77b4', waypoints: [[12.5, 43.3], [31.2, 32.3], [36.0, -5.5], [50.0, -2.0]]},
        {from: 'Port of Busan', to: 'Port of Rotterdam', color: '#1f77b4', waypoints: [[22.0, 115.0], [1.2, 103.8], [12.5, 43.3], [31.2, 32.3], [36.0, -5.5]]},
        
        // Intra-Asia routes
        {from: 'Port of Shanghai', to: 'Port of Singapore', color: '#ff7f0e', waypoints: [[22.0, 115.0], [10.0, 110.0]]},
        {from: 'Port of Shanghai', to: 'Port of Bangkok', color: '#ff7f0e', waypoints: [[22.0, 115.0], [10.0, 101.0]]},
        {from: 'Port of Busan', to: 'Port of Shanghai', color: '#ff7f0e'},
        {from: 'Port of Hong Kong', to: 'Port of Singapore', color: '#ff7f0e'},
        
        // Europe-Africa routes
        {from: 'Port of Rotterdam', to: 'Port of Port Said', color: '#2ca02c', waypoints: [[50.0, -2.0], [36.0, -5.5], [35.0, 15.0]]},
        {from: 'Port of Hamburg', to: 'Port of Port Said', color: '#2ca02c', waypoints: [[50.0, -2.0], [36.0, -5.5], [35.0, 15.0]]},
        {from: 'Port of Rotterdam', to: 'Port of Durban', color: '#2ca02c', waypoints: [[46.0, -8.0], [10.0, -20.0], [-30.0, 15.0], [-35.0, 20.0]]},
        
        // Americas routes
        {from: 'Port of Los Angeles', to: 'Port of Shanghai', color: '#d62728', waypoints: [[35.0, 175.0]]},
        {from: 'Port of Los Angeles', to: 'Port of Singapore', color: '#d62728', waypoints: [[20.0, 170.0], [10.0, 130.0]]},
        {from: 'Port of Houston', to: 'Port of Rotterdam', color: '#d62728', waypoints: [[25.0, -80.0], [40.0, -40.0]]},
        {from: 'Port of New York/New Jersey', to: 'Port of Rotterdam', color: '#d62728', waypoints: [[45.0, -40.0], [50.0, -20.0]]},
        
        // South America Routes (including Argentina)
        {from: 'Port of Buenos Aires', to: 'Port of Santos', color: '#e377c2', waypoints: [[-35.0, -55.0], [-25.0, -45.0]]},
        {from: 'Port of Buenos Aires', to: 'Port of Rotterdam', color: '#e377c2', waypoints: [[-35.0, -55.0], [-10.0, -30.0], [15.0, -25.0], [40.0, -15.0]]},
        {from: 'Port of Buenos Aires', to: 'Port of Shanghai', color: '#e377c2', waypoints: [[-40.0, -50.0], [-40.0, 0.0], [-38.0, 25.0], [-10.0, 80.0], [0.0, 105.0]]},
        {from: 'Port of Santos', to: 'Port of Rotterdam', color: '#e377c2', waypoints: [[-10.0, -30.0], [15.0, -25.0], [40.0, -15.0]]},
        {from: 'Port of Santos', to: 'Port of Shanghai', color: '#e377c2', waypoints: [[-30.0, -20.0], [-38.0, 25.0], [-10.0, 80.0], [0.0, 105.0]]},
        
        // Suez/Panama Canal routes
        {from: 'Port of Port Said', to: 'Port of Dubai Jebel Ali', color: '#9467bd', waypoints: [[12.5, 43.3], [15.0, 55.0], [25.0, 56.0]]},
        {from: 'Port of Dubai Jebel Ali', to: 'Port of Singapore', color: '#9467bd', waypoints: [[25.0, 56.0], [10.0, 70.0], [5.9, 80.2]]},
        {from: 'Port of Colon', to: 'Port of Los Angeles', color: '#9467bd', waypoints: [[15.0, -90.0], [25.0, -110.0]]},
        {from: 'Port of Colon', to: 'Port of Houston', color: '#9467bd', waypoints: [[20.0, -85.0]]}
    ];

    document.addEventListener('DOMContentLoaded', () => {
        console.time('Map Initialization');
        initializeMap();
        console.timeEnd('Map Initialization');
        
        // Load ports with caching
        const cacheKey = 'portsData';
        const cachedData = localStorage.getItem(cacheKey);
        
        if (cachedData) {
            console.time('Parse Cached Data');
            ports = JSON.parse(cachedData);
            console.timeEnd('Parse Cached Data');
            console.log(`Loaded ${ports.length} ports from cache`);
            
            console.time('Display Ports');
            displayPorts(ports);
            console.timeEnd('Display Ports');
            
            fetchAndApplyRealtimeWeather(ports);
            
            setupFilters();
            updateCountryDropdown('');
            document.getElementById('lastUpdated').textContent = new Date().toLocaleTimeString();
        } else {
            console.time('Fetch JSON');
            fetch('/ports-complete.json')
                .then(response => {
                    console.timeEnd('Fetch JSON');
                    return response.json();
                })
                .then(data => {
                    console.time('Process & Cache Data');
                    ports = data;
                    localStorage.setItem(cacheKey, JSON.stringify(data));
                    console.timeEnd('Process & Cache Data');
                    
                    console.log(`Successfully loaded and cached ${ports.length} ports`);
                    
                    console.time('Display Ports');
                    displayPorts(ports);
                    console.timeEnd('Display Ports');
                    
                    fetchAndApplyRealtimeWeather(ports);
                    
                    setupFilters();
                    updateCountryDropdown('');
                    document.getElementById('lastUpdated').textContent = new Date().toLocaleTimeString();
                })
                .catch(error => {
                    console.error('Error loading ports data:', error);
                    document.getElementById('portsList').innerHTML = '<div class="alert alert-danger">Error loading port data. Please refresh the page.</div>';
                });
        }
    });

    function initializeMap() {
        console.log('Initializing map...');
        
        // Define strict map bounds to prevent infinite scrolling (single Earth view)
        const bounds = L.latLngBounds([
            [-90, -180],
            [90, 180]
        ]);

        map = L.map('portMap', {
            dragging: true,
            worldCopyJump: false,
            maxBounds: bounds,
            maxBoundsViscosity: 1.0,
            minZoom: 2
        }).setView([20, 0], 2);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19,
            noWrap: true
        }).addTo(map);
        
        console.log('Map initialized successfully');
    }

    async function fetchAndApplyRealtimeWeather(portsArray) {
        const chunkSize = 50;
        const chunks = [];
        for (let i = 0; i < portsArray.length; i += chunkSize) {
            chunks.push({ data: portsArray.slice(i, i + chunkSize), offset: i });
        }

        try {
            await Promise.all(chunks.map(async (chunk) => {
                const lats = chunk.data.map(p => p.lat).join(',');
                const lngs = chunk.data.map(p => p.lng).join(',');
                
                const res = await fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lats}&longitude=${lngs}&current_weather=true`);
                const data = await res.json();
                
                const weatherResults = Array.isArray(data) ? data : [data];
                
                weatherResults.forEach((weatherObj, index) => {
                    const port = portsArray[chunk.offset + index];
                    if (!weatherObj.current_weather) return;
                    
                    const wcode = weatherObj.current_weather.weathercode;
                    port.weatherTemp = weatherObj.current_weather.temperature;
                    port.weatherWind = weatherObj.current_weather.windspeed;
                    port.weatherWcode = wcode;
                    
                    let color = 'green';
                    let desc = 'Cerah';
                    let type = 'Normal';
                    let severity = 'normal';
                    
                    if (wcode >= 1 && wcode <= 3) { desc = 'Berawan'; }
                    else if (wcode >= 45 && wcode <= 48) { desc = 'Berkabut'; color = 'orange'; type = 'Kabut Tebal'; severity = 'warning'; }
                    else if (wcode >= 51 && wcode <= 67) { desc = 'Hujan'; color = 'orange'; type = 'Hujan Deras'; severity = 'warning'; }
                    else if (wcode >= 71 && wcode <= 77) { desc = 'Salju'; color = 'orange'; type = 'Badai Salju'; severity = 'warning'; }
                    else if (wcode >= 80 && wcode <= 82) { desc = 'Hujan Lebat'; color = 'red'; type = 'Badai Tropis'; severity = 'critical'; }
                    else if (wcode >= 95) { desc = 'Badai Petir'; color = 'red'; type = 'Topan/Badai Petir'; severity = 'critical'; }
                    
                    port.weatherColor = color;
                    port.weatherDesc = desc;
                    port.weatherType = type;
                    port.weatherSeverity = severity;
                });
            }));
            
            // Re-render markers with new weather data
            addMarkersToMap(portsArray);
            
            // Also redraw weather zones if they are currently visible
            if (window.weatherVisible) {
                document.getElementById('toggleWeatherBtn').click(); // Turn off
                document.getElementById('toggleWeatherBtn').click(); // Turn back on to redraw
            }
        } catch (error) {
            console.error('Failed to fetch realtime weather', error);
        }
    }

    function addMarkersToMap(portsToDisplay) {
        // Clear existing markers
        markers.forEach(marker => map.removeLayer(marker));
        markers = [];

        // Display all ports
        portsToDisplay.forEach(port => {
            const color = port.status === 'operational' ? '#28a745' : (port.status === 'delayed' ? '#ffc107' : '#dc3545');
            
            // Check weather impact
            let weatherImpact = port.weatherDesc ? 
                `${port.weatherDesc} (${port.weatherTemp}°C, ${port.weatherWind} km/h)` : 
                'Memuat...';
            if (port.weatherSeverity && port.weatherSeverity !== 'normal') {
                weatherImpact = `<span style="color:${port.weatherSeverity==='critical'?'#dc3545':'#ffc107'}"><i class="bi bi-cloud-lightning-rain"></i> ${port.weatherType} (${port.weatherTemp}°C)</span>`;
            }

            const marker = L.circleMarker([port.lat, port.lng], {
                radius: 7,
                fillColor: color,
                color: '#fff',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.9
            }).bindTooltip(port.name, {
                permanent: false,
                direction: 'top',
                className: 'fw-bold'
            }).bindPopup(`<strong>${port.name}</strong><br>${port.country}<br>Kontainer: <span id="map-teu-${port.id}" class="fw-bold text-success">${((port.liveContainers || port.containers)/1000000).toFixed(6)}</span>M TEU<br>Status: ${port.status === 'operational' ? 'Beroperasi' : (port.status === 'delayed' ? 'Tertunda' : 'Kritis')}<br>Cuaca: ${weatherImpact}`).addTo(map);
            
            markers.push(marker);
        });
        
        // Draw shipping routes if visible
        drawShippingRoutes(portsToDisplay);
    }

    function drawShippingRoutes(portsToDisplay) {
        // Clear existing polylines (routes)
        if (window.shippingRoutePolylines) {
            window.shippingRoutePolylines.forEach(polyline => map.removeLayer(polyline));
        }
        window.shippingRoutePolylines = [];

        // Only draw if user toggled routes ON
        if (window.shippingRoutesVisible !== true) {
            return;
        }

        // We use the global 'ports' array to ensure we have coordinates for all ports
        // even if they are not currently filtered.
        const globalPortMap = {};
        ports.forEach(port => {
            globalPortMap[port.name] = {lat: port.lat, lng: port.lng};
        });

        // Set of currently displayed port names
        const displayedPortNames = new Set(portsToDisplay.map(p => p.name));

        shippingRoutes.forEach(route => {
            // Draw route if AT LEAST ONE of the ports (from or to) is currently displayed
            // Or if all ports are currently displayed (no active filter)
            if (displayedPortNames.has(route.from) || displayedPortNames.has(route.to) || portsToDisplay.length === ports.length) {
                const fromPort = globalPortMap[route.from];
                const toPort = globalPortMap[route.to];

                if (fromPort && toPort) {
                    const latLngs = [
                        [fromPort.lat, fromPort.lng]
                    ];
                    
                    if (route.waypoints && route.waypoints.length > 0) {
                        latLngs.push(...route.waypoints);
                    }
                    
                    latLngs.push([toPort.lat, toPort.lng]);

                    const polyline = L.polyline(latLngs, {
                        color: route.color,
                        weight: 3,
                        opacity: 0.8,
                        dashArray: '10, 10',
                        className: 'animated-route'
                    }).addTo(map);

                    window.shippingRoutePolylines.push(polyline);
                }
            }
        });
    }

    function displayPorts(filteredPorts) {
        const portsList = document.getElementById('portsList');
        
        // Add markers to map
        if (map) {
            addMarkersToMap(filteredPorts);
        }
        
        if (filteredPorts.length === 0) {
            portsList.innerHTML = '<div class="text-center text-muted py-4">No ports found</div>';
            return;
        }

        const isAuth = {{ Auth::check() ? 'true' : 'false' }};

        portsList.innerHTML = filteredPorts.map(port => {
            const statusClass = port.status === 'operational' ? 'status-operational' : 
                               (port.status === 'delayed' ? 'status-delayed' : 'status-critical');
            
            const watchlistBtn = isAuth ? `
                <button class="btn btn-sm btn-outline-warning" onclick="addToWatchlist('${port.name.replace(/'/g, "\\'")}', this)" style="border-radius: 20px; font-weight: 600;">
                    <i class="bi bi-star"></i> Pantau
                </button>
            ` : '';

            return `
                <div class="port-card">
                    <div class="port-header">
                        <div>
                            <div class="port-name">${port.name}</div>
                            <div class="port-location">
                                <i class="bi bi-geo-alt-fill"></i>
                                ${port.country} • ${port.region}
                            </div>
                        </div>
                        <span class="status-badge ${statusClass}">
                            ${port.status}
                        </span>
                    </div>
                    
                    <div class="port-stats">
                        <div class="stat-item">
                            <div class="stat-value"><span id="list-teu-${port.id}" class="text-success">${((port.liveContainers || port.containers)/1000000).toFixed(6)}</span>M</div>
                            <div class="stat-label">Containers (TEU)</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">${port.ships}</div>
                            <div class="stat-label">Ships</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">${port.congestion}%</div>
                            <div class="stat-label">Congestion</div>
                        </div>
                    </div>
                    
                    <div class="port-activity mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                        <div class="text-muted small"><i class="bi bi-info-circle me-1"></i> ${port.activity}</div>
                        ${watchlistBtn}
                    </div>
                </div>
            `;
        }).join('');
    }

    // Add to Watchlist logic
    @if(Auth::check())
    function addToWatchlist(portId, btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        
        fetch('/watchlists', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                watchable_id: portId,
                watchable_type: 'App\\Models\\Port'
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                btn.innerHTML = '<i class="bi bi-star-fill"></i> Dipantau';
                btn.classList.replace('btn-outline-warning', 'btn-warning');
                btn.classList.add('text-white');
            } else {
                alert(data.message || 'Gagal menyimpan ke daftar pantauan.');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-star"></i> Pantau';
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan jaringan.');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-star"></i> Pantau';
        });
    }
    @endif

    function setupFilters() {
        document.getElementById('countryFilter').addEventListener('change', () => {
            const selectedCountry = document.getElementById('countryFilter').value;
            
            // If country is selected, set region to that country's region
            if (selectedCountry) {
                const port = ports.find(p => p.country === selectedCountry);
                if (port) {
                    document.getElementById('regionFilter').value = port.region;
                }
            }
            applyFilters();
        });
        
        document.getElementById('regionFilter').addEventListener('change', () => {
            const selectedRegion = document.getElementById('regionFilter').value;
            
            // If region is selected, reset country to empty
            if (selectedRegion) {
                document.getElementById('countryFilter').value = '';
                updateCountryDropdown(selectedRegion);
            } else {
                // If "All Regions" is selected, show all countries
                updateCountryDropdown('');
            }
            applyFilters();
        });
        
        document.getElementById('searchPort').addEventListener('input', applyFilters);
        
        // Toggle shipping routes button
        document.getElementById('toggleRoutesBtn').addEventListener('click', function() {
            const isShown = window.shippingRoutesVisible === true;
            window.shippingRoutesVisible = !isShown;
            
            if (window.shippingRoutesVisible) {
                this.classList.add('active');
                this.innerHTML = '<i class="bi bi-diagram-3 me-1"></i>Hide Routes';
                // Redraw with routes
                const country = document.getElementById('countryFilter').value;
                const region = document.getElementById('regionFilter').value;
                const search = document.getElementById('searchPort').value.toLowerCase();
                const filtered = ports.filter(port => {
                    const matchCountry = !country || port.country === country;
                    const matchRegion = !region || port.region === region;
                    const matchSearch = !search || port.name.toLowerCase().includes(search);
                    if (country) {
                        return matchCountry && matchSearch;
                    } else {
                        return matchRegion && matchSearch;
                    }
                });
                displayPorts(filtered);
            } else {
                this.classList.remove('active');
                this.innerHTML = '<i class="bi bi-diagram-3 me-1"></i>Show Routes';
                // Clear routes
                if (window.shippingRoutePolylines) {
                    window.shippingRoutePolylines.forEach(polyline => map.removeLayer(polyline));
                    window.shippingRoutePolylines = [];
                }
            }
        });
        
        // Toggle Weather button
        document.getElementById('toggleWeatherBtn').addEventListener('click', function() {
            const isShown = window.weatherVisible === true;
            window.weatherVisible = !isShown;
            
            if (window.weatherVisible) {
                this.classList.add('active');
                
                // Draw real-time weather zones
                ports.forEach(port => {
                    if (port.weatherSeverity && port.weatherSeverity !== 'normal') {
                        const color = port.weatherSeverity === 'critical' ? '#dc3545' : '#ffc107';
                        // Radius: 300km for critical, 150km for warning
                        const radius = port.weatherSeverity === 'critical' ? 300000 : 150000;
                        const circle = L.circle([port.lat, port.lng], {
                            color: color,
                            fillColor: color,
                            fillOpacity: 0.3,
                            radius: radius
                        }).bindPopup(`<strong>${port.weatherType} di ${port.name}</strong><br>Suhu: ${port.weatherTemp}°C<br>Angin: ${port.weatherWind} km/h`).addTo(map);
                        
                        window.weatherZones.push(circle);
                    }
                });
            } else {
                this.classList.remove('active');
                // Clear weather zones
                if (window.weatherZones) {
                    window.weatherZones.forEach(zone => map.removeLayer(zone));
                    window.weatherZones = [];
                }
            }
        });
    }
    
    function updateCountryDropdown(region) {
        const countrySelect = document.getElementById('countryFilter');
        
        // Get all unique countries sorted
        const allCountries = [...new Set(ports.map(p => p.country))].sort();
        
        // Filter countries based on region if provided
        const filteredCountries = region 
            ? allCountries.filter(country => {
                const port = ports.find(p => p.country === country);
                return port && port.region === region;
              })
            : allCountries;
        
        // Get current selected value
        const currentValue = countrySelect.value;
        
        // Rebuild options (keep "All Countries" option)
        const options = '<option value="">All Countries</option>' + 
            filteredCountries.map(country => 
                `<option value="${country}" ${currentValue === country ? 'selected' : ''}>${country}</option>`
            ).join('');
        
        countrySelect.innerHTML = options;
    }

    function applyFilters() {
        const country = document.getElementById('countryFilter').value;
        const region = document.getElementById('regionFilter').value;
        const search = document.getElementById('searchPort').value.toLowerCase();

        const filtered = ports.filter(port => {
            const matchCountry = !country || port.country === country;
            const matchRegion = !region || port.region === region;
            const matchSearch = !search || port.name.toLowerCase().includes(search);
            
            if (country) {
                return matchCountry && matchSearch;
            } else {
                return matchRegion && matchSearch;
            }
        });

        displayPorts(filtered);

        // Auto pan/zoom to fit the filtered ports
        if (filtered.length > 0 && map && (country || region || search)) {
            const bounds = L.latLngBounds(filtered.map(p => [p.lat, p.lng]));
            map.flyToBounds(bounds, { padding: [50, 50], maxZoom: 5, duration: 1.5 });
        } else if (map && !country && !region && !search) {
            // Reset to world view if no filters
            map.flyTo([10, 100], 3, { duration: 1.5 });
        }
    }

        // Distance Calculator Logic
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('distanceModal');
            if(modal) {
                modal.addEventListener('show.bs.modal', function () {
                    // Populate selects if empty
                    const originSel = document.getElementById('calcOrigin');
                    const destSel = document.getElementById('calcDestination');
                    if (originSel.options.length === 0) {
                        ports.forEach((p, idx) => {
                            originSel.add(new Option(p.name + ' (' + p.country + ')', idx));
                            destSel.add(new Option(p.name + ' (' + p.country + ')', idx));
                        });
                        
                        // Default Indonesian port if available
                        const indoIndex = ports.findIndex(p => p.country === 'Indonesia');
                        if (indoIndex !== -1) {
                            originSel.value = indoIndex;
                        }
                    }
                });

                document.getElementById('btnCalculate').addEventListener('click', () => {
                    const idx1 = document.getElementById('calcOrigin').value;
                    const idx2 = document.getElementById('calcDestination').value;
                    if (idx1 !== "" && idx2 !== "") {
                        const p1 = ports[idx1];
                        const p2 = ports[idx2];
                        
                        // Calculate distance in meters
                        const distMeters = map.distance([p1.lat, p1.lng], [p2.lat, p2.lng]);
                        // Convert to Nautical Miles (1 meter = 0.000539957 NM)
                        const distNM = distMeters * 0.000539957;
                        
                        // Assume 20 knots (Nautical miles per hour)
                        const hours = distNM / 20;
                        const days = hours / 24;

                        document.getElementById('resDistance').innerHTML = `${distNM.toFixed(0)} NM <span class="text-muted small">(${(distMeters/1000).toFixed(0)} km)</span>`;
                        
                        let timeStr = "";
                        if (days >= 1) {
                            timeStr = `${Math.floor(days)} Hari ${Math.round(hours % 24)} Jam`;
                        } else {
                            timeStr = `${Math.round(hours)} Jam`;
                        }
                        
                        document.getElementById('resTime').innerText = timeStr;
                        document.getElementById('calcResult').classList.remove('d-none');
                    }
                });
            }
        });
    // Live Cargo Engine
    function initLiveCargoEngine() {
        setInterval(() => {
            ports.forEach(port => {
                // Calculate throughput per second based on annual TEU
                // Annual to seconds: 365 * 24 * 60 * 60 = 31,536,000 seconds
                const teuPerSecond = port.containers / 31536000;
                
                // Weather multiplier
                let multiplier = 1.0;
                if (port.weatherSeverity === 'critical') multiplier = 0.0;
                else if (port.weatherSeverity === 'warning') multiplier = 0.5;
                
                if(!port.liveContainers) {
                    port.liveContainers = port.containers; 
                }
                port.liveContainers += (teuPerSecond * multiplier);
                
                // Update DOM elements if they exist
                const mapSpan = document.getElementById(`map-teu-${port.id}`);
                const listSpan = document.getElementById(`list-teu-${port.id}`);
                const formattedTEU = (port.liveContainers / 1000000).toFixed(6);
                
                if (mapSpan) mapSpan.innerText = formattedTEU;
                if (listSpan) listSpan.innerText = formattedTEU;
            });
        }, 1000); 
    }
    
    // Call engine
    initLiveCargoEngine();

</script>

<!-- Distance Calculator Modal -->
<div class="modal fade" id="distanceModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white border-0">
        <h5 class="modal-title fs-6"><i class="bi bi-geo-alt me-2"></i>Kalkulator Jarak & Waktu Tempuh</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="mb-3">
          <label class="form-label small text-muted fw-bold">Pelabuhan Asal</label>
          <select id="calcOrigin" class="form-select shadow-sm"></select>
        </div>
        <div class="mb-4">
          <label class="form-label small text-muted fw-bold">Pelabuhan Tujuan</label>
          <select id="calcDestination" class="form-select shadow-sm"></select>
        </div>
        <div class="text-center mb-4">
            <button id="btnCalculate" class="btn btn-primary px-4 shadow-sm" style="border-radius: 20px;">Hitung Jarak</button>
        </div>
        
        <div id="calcResult" class="d-none bg-light p-3 rounded text-center border">
            <h6 class="text-primary fw-bold mb-3">Hasil Kalkulasi</h6>
            <div class="row text-start mb-2 align-items-center">
                <div class="col-5 text-muted small">Jarak Laut (est)</div>
                <div class="col-7 fw-bold" id="resDistance" style="font-size: 1.1rem;">--</div>
            </div>
            <div class="row text-start align-items-center">
                <div class="col-5 text-muted small">Waktu Tempuh</div>
                <div class="col-7 fw-bold text-success" id="resTime" style="font-size: 1.1rem;">--</div>
            </div>
            <div class="mt-3 text-muted text-start" style="font-size: 10px; line-height: 1.3;">
                <i class="bi bi-info-circle me-1"></i>Asumsi kecepatan rata-rata kapal kargo: 20 Knots (~37 km/jam). Menghitung jarak garis lurus (*Great Circle*).
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>

