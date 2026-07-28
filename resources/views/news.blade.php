<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News & Sentiment - Risk Intelligence</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
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
            margin: 0;
        }
        
        .header-section p {
            color: #666;
            margin: 4px 0 0 0;
            font-size: 14px;
        }
        
        #countrySelect {
            border: 2px solid rgba(102,126,234,0.3);
            border-radius: 8px;
            padding: 10px 16px;
            font-weight: 500;
            background: white;
            transition: all 0.3s ease;
        }
        
        #countrySelect:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
            outline: none;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #1a1d2e;
        }
        
        .stat-label {
            font-size: 12px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-top: 8px;
        }
        
        .stat-icon {
            font-size: 28px;
            margin-bottom: 12px;
        }
        
        .news-card {
            background: white;
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .news-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .news-card img {
            transition: transform 0.3s ease;
        }
        
        .news-card:hover img {
            transform: scale(1.02);
        }
        
        .sentiment-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            font-weight: 600;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 12px;
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .sentiment-positive {
            background: rgba(40, 167, 69, 0.9);
            color: white;
        }
        
        .sentiment-negative {
            background: rgba(220, 53, 69, 0.9);
            color: white;
        }
        
        .sentiment-neutral {
            background: rgba(108, 117, 125, 0.9);
            color: white;
        }
        
        .sentiment-chart {
            display: flex;
            gap: 4px;
            height: 8px;
            margin: 8px 0;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .sentiment-bar {
            height: 100%;
            border-radius: 4px;
        }
        
        .loading-container {
            min-height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }
        
        .empty-state-icon {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 16px;
        }
        
        .btn-refresh {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-refresh:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .card-title-link {
            color: #1a1d2e;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .card-title-link:hover {
            color: #667eea;
        }
        
        .article-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            color: #888;
            margin: 12px 0;
        }
        
        .article-meta i {
            color: #667eea;
        }
        
        .tab-toggle {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
        }
        
        .tab-btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: 2px solid rgba(102,126,234,0.3);
            background: white;
            color: #666;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .tab-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
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
            <i class="bi bi-newspaper"></i>
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
                <a href="/port" class="nav-link" title="Port Monitoring">
                    <i class="bi bi-geo-alt me-2"></i> Port Monitoring
                </a>
            </li>
            <li class="nav-item">
                <a href="/news" class="nav-link active" title="News & Sentiment">
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
        <!-- Header Section -->
        <div class="header-section">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="bi bi-newspaper me-2"></i>News & Sentiment Analysis</h2>
                    <p>Real-time supply chain news with AI-powered sentiment analysis</p>
                </div>
                <div style="width: 320px;">
                    <label class="form-label text-muted mb-2" style="font-size: 12px; font-weight: 600;">Select Country</label>
                    <select id="countrySelect" class="form-select" style="width: 100%;" data-auto-populate="true">
                        <option value="" selected disabled>-- Choose a Country --</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Tab Toggle -->
        <div class="tab-toggle">
            <button class="tab-btn active" onclick="switchTab('country')">
                <i class="bi bi-flag me-2"></i>Country News
            </button>
            <button class="tab-btn" onclick="switchTab('global')">
                <i class="bi bi-globe me-2"></i>Global Supply Chain
            </button>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon" style="color: #667eea;"><i class="bi bi-newspaper"></i></div>
                    <div class="stat-label">Total Articles</div>
                    <div class="stat-number" id="totalArticles">0</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon" style="color: #28a745;"><i class="bi bi-emoji-smile"></i></div>
                    <div class="stat-label">Positive Sentiment</div>
                    <div class="stat-number text-success" id="positiveCount">0</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon" style="color: #dc3545;"><i class="bi bi-emoji-frown"></i></div>
                    <div class="stat-label">Negative Sentiment</div>
                    <div class="stat-number text-danger" id="negativeCount">0</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon" style="color: #6c757d;"><i class="bi bi-dash-circle"></i></div>
                    <div class="stat-label">Neutral Sentiment</div>
                    <div class="stat-number" id="neutralCount">0</div>
                </div>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div id="loadingSpinner" class="loading-container d-none">
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                <h5 class="text-muted">Loading news...</h5>
                <p class="text-muted small">Fetching data from GNews API</p>
            </div>
        </div>

        <!-- News Grid -->
        <div id="newsGrid" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <!-- Empty State -->
            <div class="col-12">
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="bi bi-newspaper"></i></div>
                    <h5 class="text-muted">Select a country to view news</h5>
                    <p class="text-muted">Choose a country from the dropdown above to see real-time news and sentiment analysis</p>
                </div>
            </div>
        </div>

        <!-- Error Message -->
        <div id="errorMessage" class="alert alert-danger d-none mt-4" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            <span id="errorText">Error loading news</span>
        </div>

        <!-- Footer -->
        <div class="mt-5 pt-4 border-top text-center text-muted small">
            <p><i class="bi bi-info-circle me-1"></i>Powered by GNews API with AI-based Sentiment Analysis</p>
            <p class="mb-0">Last updated: <strong id="lastUpdated">--</strong></p>
        </div>
    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/js/countries-dropdown.js"></script>

<script>
    let currentMode = 'country';
    let currentCountry = '';

    document.addEventListener('DOMContentLoaded', () => {
        // Get country from URL or session storage
        const urlParams = new URLSearchParams(window.location.search);
        const countryParam = urlParams.get('country');
        const sessionCountry = sessionStorage.getItem('selectedCountry');
        
        if (countryParam) {
            currentCountry = decodeURIComponent(countryParam);
            document.getElementById('countrySelect').value = currentCountry;
            loadNews(currentCountry);
        } else if (sessionCountry) {
            currentCountry = sessionCountry;
            document.getElementById('countrySelect').value = currentCountry;
            loadNews(currentCountry);
        }
        
        document.getElementById('countrySelect').addEventListener('change', (e) => {
            currentCountry = e.target.value;
            if (currentCountry) {
                loadNews(currentCountry);
            }
        });
    });

    function switchTab(mode) {
        currentMode = mode;
        
        // Update button states
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        event.target.closest('.tab-btn').classList.add('active');
        
        if (mode === 'global') {
            loadGlobalNews();
        } else if (currentCountry) {
            loadNews(currentCountry);
        }
    }

    async function loadNews(country) {
        if (!country) return;
        
        currentCountry = country;
        currentMode = 'country';
        
        // Update tab buttons
        document.querySelectorAll('.tab-btn').forEach((btn, idx) => {
            btn.classList.toggle('active', idx === 0);
        });
        
        showLoading();
        
        try {
            const response = await fetch(`/api/news?country=${encodeURIComponent(country)}&max=12`);
            const data = await response.json();
            
            if (data.status === 'success') {
                displayNews(data.data);
                updateStats(data.data);
                document.getElementById('lastUpdated').textContent = new Date().toLocaleTimeString();
            } else {
                showError(data.message || 'Failed to load news');
            }
        } catch (error) {
            console.error('Error:', error);
            showError('Failed to load news: ' + error.message);
        } finally {
            hideLoading();
        }
    }

    async function loadGlobalNews() {
        showLoading();
        
        try {
            const response = await fetch(`/api/news/global?max=12`);
            const data = await response.json();
            
            if (data.status === 'success') {
                displayNews(data.data);
                updateStats(data.data);
                document.getElementById('lastUpdated').textContent = new Date().toLocaleTimeString();
            } else {
                showError('Failed to load global news');
            }
        } catch (error) {
            console.error('Error:', error);
            showError('Failed to load global news: ' + error.message);
        } finally {
            hideLoading();
        }
    }

    function displayNews(articles) {
        const newsGrid = document.getElementById('newsGrid');
        
        if (!articles || articles.length === 0) {
            newsGrid.innerHTML = `
                <div class="col-12">
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="bi bi-search"></i></div>
                        <h5 class="text-muted">No news found</h5>
                        <p class="text-muted">Try selecting a different country or check back later</p>
                    </div>
                </div>
            `;
            return;
        }
        
        newsGrid.innerHTML = articles.map(article => {
            const sentiment = article.sentiment_label;
            const sentimentScore = article.sentiment_score || 0;
            
            let badgeClass = 'sentiment-neutral';
            let sentimentIcon = 'bi-dash-circle';
            
            if (sentiment === 'positive') {
                badgeClass = 'sentiment-positive';
                sentimentIcon = 'bi-emoji-smile';
            } else if (sentiment === 'negative') {
                badgeClass = 'sentiment-negative';
                sentimentIcon = 'bi-emoji-frown';
            }
            
            return `
                <div class="col">
                    <div class="card news-card">
                        <span class="sentiment-badge ${badgeClass}" style="position: absolute; top: 12px; right: 12px; z-index: 10;">
                            <i class="bi ${sentimentIcon}"></i>
                            ${sentiment.charAt(0).toUpperCase() + sentiment.slice(1)}
                        </span>
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex gap-3 mb-3">
                                <div style="flex: 1;">
                                    <h5 class="card-title mb-2" style="font-size: 15px; font-weight: 700; line-height: 1.4;">
                                        <a href="${article.url}" target="_blank" class="card-title-link">
                                            ${article.title}
                                        </a>
                                    </h5>
                                    
                                    <p class="card-text text-muted" style="font-size: 13px; line-height: 1.6; margin-bottom: 12px;">
                                        ${article.description ? article.description.substring(0, 100) + '...' : 'No description available'}
                                    </p>
                                </div>
                                <div style="flex-shrink: 0; width: 120px;">
                                    <img src="${article.image || 'https://via.placeholder.com/120x100?text=News'}" 
                                         alt="${article.title}"
                                         onerror="this.src='https://via.placeholder.com/120x100?text=News'"
                                         style="width: 100%; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid rgba(102,126,234,0.1);">
                                </div>
                            </div>
                            
                            <div class="article-meta" style="margin-top: auto; padding-top: 12px; border-top: 1px solid rgba(0,0,0,0.05);">
                                <i class="bi bi-calendar-event"></i>
                                <span style="font-size: 12px;">${article.published_at_human || 'Recently'}</span>
                                <span style="color: #ddd; margin: 0 6px;">•</span>
                                <i class="bi bi-building"></i>
                                <span style="font-size: 12px;">${article.source}</span>
                            </div>
                            
                            <div class="sentiment-chart" style="margin-top: 12px;">
                                ${Array.from({length: Math.abs(sentimentScore)}).map((_, i) => {
                                    if (sentimentScore > 0) return '<div class="sentiment-bar" style="background: #28a745; flex: 1;"></div>';
                                    else return '<div class="sentiment-bar" style="background: #dc3545; flex: 1;"></div>';
                                }).join('')}
                            </div>
                            
                            <a href="${article.url}" target="_blank" class="btn btn-sm btn-outline-primary mt-3" style="font-size: 12px;">
                                <i class="bi bi-box-arrow-up-right me-1"></i> Read Article
                            </a>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function updateStats(articles) {
        const stats = articles.reduce((acc, article) => {
            if (article.sentiment_label === 'positive') acc.positive++;
            else if (article.sentiment_label === 'negative') acc.negative++;
            else acc.neutral++;
            return acc;
        }, { positive: 0, negative: 0, neutral: 0 });
        
        document.getElementById('totalArticles').textContent = articles.length;
        document.getElementById('positiveCount').textContent = stats.positive;
        document.getElementById('negativeCount').textContent = stats.negative;
        document.getElementById('neutralCount').textContent = stats.neutral;
    }

    function showLoading() {
        document.getElementById('loadingSpinner').classList.remove('d-none');
        document.getElementById('errorMessage').classList.add('d-none');
    }

    function hideLoading() {
        document.getElementById('loadingSpinner').classList.add('d-none');
    }

    function showError(message) {
        document.getElementById('errorText').textContent = message;
        document.getElementById('errorMessage').classList.remove('d-none');
    }
</script>
</body>
</html>

