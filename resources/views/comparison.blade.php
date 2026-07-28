<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Country Comparison - Risk Intelligence</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            background: #f0f2f5;
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
            padding: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 16px;
            border-left: 4px solid #667eea;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card.gdp { border-left-color: #28a745; }
        .stat-card.inflation { border-left-color: #dc3545; }
        .stat-card.population { border-left-color: #fd7e14; }
        .stat-card.currency { border-left-color: #20c997; }
        .stat-card.weather { border-left-color: #0dcaf0; }
        .stat-card.risk { border-left-color: #6f42c1; }
        .stat-card.sentiment { border-left-color: #e83e8c; }
        
        .stat-value {
            font-size: 24px;
            font-weight: 800;
            color: #1a1d2e;
            margin-top: 4px;
        }
        
        .stat-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        
        .stat-icon {
            font-size: 32px;
            position: absolute;
            right: 15px;
            bottom: 10px;
            opacity: 0.1;
        }

        .select-wrapper {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 24px;
        }
        
        .comparison-col {
            border-right: 1px dashed #dee2e6;
        }
        .comparison-col:last-child {
            border-right: none;
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
        
        <div class="text-uppercase text-secondary fw-bold mb-3" style="font-size: 11px; letter-spacing: 1px;">Core Modules</div>
        
        <ul class="nav flex-column mb-5">
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
                <a href="/compare" class="nav-link active" title="Country Comparison">
                    <i class="bi bi-arrow-left-right me-2"></i> Country Comparison
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
        </ul>
        
        @if(Auth::check())
        <div class="mt-auto pt-4 border-top" style="border-color: rgba(255,255,255,0.1) !important;">
            <div class="d-flex align-items-center mb-3">
                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold me-3" style="width: 40px; height: 40px;">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div>
                    <div class="fw-bold text-white" style="font-size: 14px;">{{ Auth::user()->name }}</div>
                    <div class="text-secondary" style="font-size: 12px;">{{ ucfirst(Auth::user()->role) }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light w-100" style="border-color: rgba(255,255,255,0.2);">
                    <i class="bi bi-box-arrow-right me-1"></i> Sign Out
                </button>
            </form>
        </div>
        @else
        <div class="mt-auto pt-4 border-top" style="border-color: rgba(255,255,255,0.1) !important;">
            <a href="{{ route('login') }}" class="btn btn-sm btn-primary w-100">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
            </a>
        </div>
        @endif
    </nav>
    @endif
    
    <!-- Main Content -->
    <main class="flex-grow-1 p-4" id="main-content">
        <!-- Header -->
        <div class="header-section">
            <h2><i class="bi bi-layout-split me-2"></i>Country Comparison Engine</h2>
            <p>Compare macroeconomic and risk factors between two nations</p>
        </div>

        <div class="row">
            <!-- Country A -->
            <div class="col-md-6 comparison-col">
                <div class="select-wrapper d-flex align-items-center gap-3">
                    <label for="countrySelectA" class="fw-bold mb-0 text-dark">Country A:</label>
                    <select id="countrySelectA" class="form-select flex-grow-1 border-primary shadow-sm">
                        <!-- Populated by JS -->
                    </select>
                </div>

                <div class="text-center mb-4 mt-3">
                    <div id="flagContainerA" style="font-size: 64px; line-height: 1;"></div>
                    <h3 id="countryNameTitleA" class="fw-bold text-primary mb-0 mt-2"></h3>
                </div>

                <div class="stat-card gdp">
                    <div class="stat-label">GDP Growth</div>
                    <div class="stat-value" id="valGdpA">--</div>
                    <i class="bi bi-graph-up-arrow stat-icon text-success"></i>
                </div>
                
                <div class="stat-card inflation">
                    <div class="stat-label">Inflation Rate</div>
                    <div class="stat-value" id="valInflA">--</div>
                    <i class="bi bi-arrow-up-right-circle stat-icon text-danger"></i>
                </div>

                <div class="stat-card currency">
                    <div class="stat-label">Local Currency</div>
                    <div class="stat-value" id="valCurrA">--</div>
                    <i class="bi bi-cash-stack stat-icon text-info"></i>
                </div>

                <div class="stat-card weather">
                    <div class="stat-label">Current Weather</div>
                    <div class="stat-value" id="valWeathA">--</div>
                    <i class="bi bi-cloud-sun stat-icon text-info" id="weathIconA"></i>
                </div>

                <div class="stat-card risk">
                    <div class="stat-label">Overall Risk Score</div>
                    <div class="stat-value text-purple" id="valRiskScoreA" style="color: #6f42c1;">--</div>
                    <i class="bi bi-shield-exclamation stat-icon" style="color: #6f42c1;"></i>
                    <div class="mt-1 text-muted" style="font-size: 13px;" id="riskLabelA">Loading...</div>
                </div>

                <div class="stat-card sentiment">
                    <div class="stat-label">News Sentiment</div>
                    <div class="stat-value" id="valSentimentA" style="color: #e83e8c;">--</div>
                    <i class="bi bi-newspaper stat-icon" style="color: #e83e8c;"></i>
                </div>
            </div>

            <!-- Country B -->
            <div class="col-md-6 comparison-col">
                <div class="select-wrapper d-flex align-items-center gap-3">
                    <label for="countrySelectB" class="fw-bold mb-0 text-dark">Country B:</label>
                    <select id="countrySelectB" class="form-select flex-grow-1 border-primary shadow-sm">
                        <!-- Populated by JS -->
                    </select>
                </div>

                <div class="text-center mb-4 mt-3">
                    <div id="flagContainerB" style="font-size: 64px; line-height: 1;"></div>
                    <h3 id="countryNameTitleB" class="fw-bold text-primary mb-0 mt-2"></h3>
                </div>

                <div class="stat-card gdp">
                    <div class="stat-label">GDP Growth</div>
                    <div class="stat-value" id="valGdpB">--</div>
                    <i class="bi bi-graph-up-arrow stat-icon text-success"></i>
                </div>
                
                <div class="stat-card inflation">
                    <div class="stat-label">Inflation Rate</div>
                    <div class="stat-value" id="valInflB">--</div>
                    <i class="bi bi-arrow-up-right-circle stat-icon text-danger"></i>
                </div>

                <div class="stat-card currency">
                    <div class="stat-label">Local Currency</div>
                    <div class="stat-value" id="valCurrB">--</div>
                    <i class="bi bi-cash-stack stat-icon text-info"></i>
                </div>

                <div class="stat-card weather">
                    <div class="stat-label">Current Weather</div>
                    <div class="stat-value" id="valWeathB">--</div>
                    <i class="bi bi-cloud-sun stat-icon text-info" id="weathIconB"></i>
                </div>

                <div class="stat-card risk">
                    <div class="stat-label">Overall Risk Score</div>
                    <div class="stat-value text-purple" id="valRiskScoreB" style="color: #6f42c1;">--</div>
                    <i class="bi bi-shield-exclamation stat-icon" style="color: #6f42c1;"></i>
                    <div class="mt-1 text-muted" style="font-size: 13px;" id="riskLabelB">Loading...</div>
                </div>

                <div class="stat-card sentiment">
                    <div class="stat-label">News Sentiment</div>
                    <div class="stat-value" id="valSentimentB" style="color: #e83e8c;">--</div>
                    <i class="bi bi-newspaper stat-icon" style="color: #e83e8c;"></i>
                </div>
            </div>
        </div>
        
    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Comprehensive Country Dataset
    const countryData = [
        { id: 'CN', name: 'China', flag: '🇨🇳', gdp: '5.2%', infl: '0.2%', pop: '1.41 Billion', curr: 'CNY (Yuan)', weather: 'Scattered Clouds, 22°C', w_icon: 'bi-clouds' },
        { id: 'US', name: 'United States', flag: '🇺🇸', gdp: '2.1%', infl: '3.4%', pop: '333 Million', curr: 'USD ($)', weather: 'Clear Sky, 25°C', w_icon: 'bi-sun' },
        { id: 'DE', name: 'Germany', flag: '🇩🇪', gdp: '-0.1%', infl: '2.2%', pop: '83 Million', curr: 'EUR (€)', weather: 'Light Rain, 14°C', w_icon: 'bi-cloud-rain' },
        { id: 'BR', name: 'Brazil', flag: '🇧🇷', gdp: '2.9%', infl: '4.5%', pop: '214 Million', curr: 'BRL (R$)', weather: 'Thunderstorms, 28°C', w_icon: 'bi-cloud-lightning-rain' },
        { id: 'ZA', name: 'South Africa', flag: '🇿🇦', gdp: '0.9%', infl: '5.1%', pop: '60 Million', curr: 'ZAR (R)', weather: 'Sunny, 19°C', w_icon: 'bi-sun' },
        { id: 'AU', name: 'Australia', flag: '🇦🇺', gdp: '1.5%', infl: '4.1%', pop: '26 Million', curr: 'AUD (A$)', weather: 'Partly Cloudy, 18°C', w_icon: 'bi-cloud-sun' },
        { id: 'JP', name: 'Japan', flag: '🇯🇵', gdp: '1.2%', infl: '2.8%', pop: '125 Million', curr: 'JPY (¥)', weather: 'Heavy Rain, 20°C', w_icon: 'bi-cloud-rain-heavy' },
        { id: 'GB', name: 'United Kingdom', flag: '🇬🇧', gdp: '0.8%', infl: '4.0%', pop: '67 Million', curr: 'GBP (£)', weather: 'Overcast, 12°C', w_icon: 'bi-clouds' },
        { id: 'ID', name: 'Indonesia', flag: '🇮🇩', gdp: '5.0%', infl: '2.5%', pop: '273 Million', curr: 'IDR (Rp)', weather: 'Tropical Storm, 30°C', w_icon: 'bi-hurricane' },
        { id: 'IN', name: 'India', flag: '🇮🇳', gdp: '7.2%', infl: '4.4%', pop: '1.40 Billion', curr: 'INR (₹)', weather: 'Haze, 34°C', w_icon: 'bi-cloud-haze' },
        { id: 'CA', name: 'Canada', flag: '🇨🇦', gdp: '1.5%', infl: '2.9%', pop: '38 Million', curr: 'CAD (C$)', weather: 'Snow Showers, -2°C', w_icon: 'bi-snow' },
        { id: 'EG', name: 'Egypt', flag: '🇪🇬', gdp: '3.8%', infl: '33.7%', pop: '109 Million', curr: 'EGP (E£)', weather: 'Clear Sky, 35°C', w_icon: 'bi-sun' },
        { id: 'AR', name: 'Argentina', flag: '🇦🇷', gdp: '-1.2%', infl: '95.0%', pop: '45 Million', curr: 'ARS ($)', weather: 'Partly Cloudy, 24°C', w_icon: 'bi-cloud-sun' },
        { id: 'FR', name: 'France', flag: '🇫🇷', gdp: '0.7%', infl: '2.3%', pop: '67 Million', curr: 'EUR (€)', weather: 'Clear, 16°C', w_icon: 'bi-sun' }
    ];

    document.addEventListener('DOMContentLoaded', () => {
        const selectA = document.getElementById('countrySelectA');
        const selectB = document.getElementById('countrySelectB');
        
        // Sort alphabetically
        countryData.sort((a,b) => a.name.localeCompare(b.name));

        // Populate dropdowns
        countryData.forEach(country => {
            const optA = document.createElement('option');
            optA.value = country.id;
            optA.textContent = country.name;
            selectA.appendChild(optA);

            const optB = document.createElement('option');
            optB.value = country.id;
            optB.textContent = country.name;
            selectB.appendChild(optB);
        });

        // Event Listeners
        selectA.addEventListener('change', (e) => {
            const selected = countryData.find(c => c.id === e.target.value);
            if(selected) updateUI(selected, 'A');
        });

        selectB.addEventListener('change', (e) => {
            const selected = countryData.find(c => c.id === e.target.value);
            if(selected) updateUI(selected, 'B');
        });

        // Initialize with first two countries (Germany vs Australia as per spec example)
        let cA = countryData.find(c => c.name === 'Germany') || countryData[0];
        let cB = countryData.find(c => c.name === 'Australia') || countryData[1];

        selectA.value = cA.id;
        selectB.value = cB.id;
        updateUI(cA, 'A');
        updateUI(cB, 'B');
    });

    function updateUI(country, side) {
        document.getElementById(`flagContainer${side}`).textContent = country.flag;
        document.getElementById(`countryNameTitle${side}`).textContent = country.name;
        
        document.getElementById(`valGdp${side}`).textContent = country.gdp;
        document.getElementById(`valInfl${side}`).textContent = country.infl;
        document.getElementById(`valCurr${side}`).textContent = country.curr;
        document.getElementById(`valWeath${side}`).textContent = country.weather;
        
        const weathIcon = document.getElementById(`weathIcon${side}`);
        weathIcon.className = `bi ${country.w_icon} stat-icon weath-icon`;

        // Fetch Risk Score and Sentiment dynamically
        fetchRiskData(country.id, side);
    }

    function fetchRiskData(iso2, side) {
        document.getElementById(`valRiskScore${side}`).textContent = '...';
        document.getElementById(`riskLabel${side}`).textContent = 'Calculating...';
        document.getElementById(`valSentiment${side}`).textContent = '...';

        fetch(`/api/risk/score/${iso2}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const score = data.data.total_score;
                    const label = data.data.label;
                    const sentiment = data.data.components.news_sentiment;

                    document.getElementById(`valRiskScore${side}`).textContent = score + ' / 100';
                    document.getElementById(`riskLabel${side}`).innerHTML = `<strong>Status:</strong> ${label}`;
                    document.getElementById(`valSentiment${side}`).textContent = sentiment;
                }
            })
            .catch(err => {
                document.getElementById(`valRiskScore${side}`).textContent = 'N/A';
                document.getElementById(`riskLabel${side}`).textContent = 'Failed to load';
                document.getElementById(`valSentiment${side}`).textContent = 'N/A';
            });
    }
</script>
</body>
</html>

