/**
 * API Integration for Global Supply Chain Risk Intelligence Platform
 * =================================================================
 * Handles all API communication between frontend and Flask backend
 */

const API_BASE_URL = 'http://localhost:5000/api';

// ============================================================================
// API CLIENT
// ============================================================================

class APIClient {
    static async request(endpoint, options = {}) {
        const url = `${API_BASE_URL}${endpoint}`;
        
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
            },
        };
        
        const config = { ...defaultOptions, ...options };
        
        try {
            const response = await fetch(url, config);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error(`API request failed: ${url}`, error);
            throw error;
        }
    }
    
    // Countries List
    static async getCountries() {
        return this.request('/countries');
    }
    
    // Country Dashboard
    static async getCountryDashboard(countryName) {
        return this.request(`/country/${encodeURIComponent(countryName)}`);
    }
    
    static async getCountryRisk(countryName) {
        return this.request(`/risk/${encodeURIComponent(countryName)}`);
    }
    
    // Comparison
    static async compareCountries(countryA, countryB) {
        return this.request('/compare', {
            method: 'POST',
            body: JSON.stringify({ country_a: countryA, country_b: countryB })
        });
    }
    
    // Favorites
    static async getFavorites() {
        return this.request('/favorites');
    }
    
    static async addFavorite(countryName) {
        return this.request('/favorites', {
            method: 'POST',
            body: JSON.stringify({ country_name: countryName })
        });
    }
    
    static async removeFavorite(id) {
        return this.request(`/favorites/${id}`, {
            method: 'DELETE'
        });
    }
    
    // Ports
    static async getPorts(search = '', country = '') {
        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (country) params.append('country', country);
        
        return this.request(`/ports?${params.toString()}`);
    }
    
    // Historical Data
    static async getHistoricalData(countryName, metricType = 'all', days = 30) {
        const params = new URLSearchParams();
        params.append('metric_type', metricType);
        params.append('days', days);
        
        return this.request(`/historical/${encodeURIComponent(countryName)}?${params.toString()}`);
    }
    
    // Admin
    static async getUsers() {
        return this.request('/admin/users');
    }
    
    static async createUser(userData) {
        return this.request('/admin/users', {
            method: 'POST',
            body: JSON.stringify(userData)
        });
    }
    
    static async getArticles() {
        return this.request('/admin/articles');
    }
    
    static async createArticle(articleData) {
        return this.request('/admin/articles', {
            method: 'POST',
            body: JSON.stringify(articleData)
        });
    }
    
    static async updateArticle(id, articleData) {
        return this.request(`/admin/articles/${id}`, {
            method: 'PUT',
            body: JSON.stringify(articleData)
        });
    }
}

// ============================================================================
// DATA MANAGER
// ============================================================================

class DataManager {
    constructor() {
        this.cache = new Map();
        this.cacheTimeout = 5 * 60 * 1000; // 5 minutes
    }
    
    set(key, data) {
        this.cache.set(key, {
            data,
            timestamp: Date.now()
        });
    }
    
    get(key) {
        const cached = this.cache.get(key);
        if (!cached) return null;
        
        if (Date.now() - cached.timestamp > this.cacheTimeout) {
            this.cache.delete(key);
            return null;
        }
        
        return cached.data;
    }
    
    clear() {
        this.cache.clear();
    }
}

const dataManager = new DataManager();

// ============================================================================
// COUNTRY DATA LOADING (REAL API)
// ============================================================================

async function loadCountryDataReal() {
    const countrySelect = document.getElementById('countrySelect');
    const countryName = countrySelect.value;
    
    if (!countryName) {
        alert('Please select a country');
        return;
    }
    
    currentCountry = countryName;
    
    // Show loading state
    const statsContainer = document.getElementById('countryStats');
    statsContainer.innerHTML = '<div class="loading">Loading data...</div>';
    
    try {
        // Try cache first
        let dashboardData = dataManager.get(`country_${countryName}`);
        
        if (!dashboardData) {
            // Fetch from API
            const response = await APIClient.getCountryDashboard(countryName);
            
            if (response.status !== 'success') {
                throw new Error(response.message || 'Failed to load country data');
            }
            
            dashboardData = response.data;
            dataManager.set(`country_${countryName}`, dashboardData);
        }
        
        // Update country statistics
        updateCountryStats(dashboardData);
        
        // Update charts with historical data
        await updateChartsWithHistoricalData(countryName);
        
        // Update news
        updateNewsDisplay(dashboardData.news || []);
        
        // Focus map on country
        focusMapOnCountry(countryName);
        
    } catch (error) {
        console.error('Error loading country data:', error);
        statsContainer.innerHTML = `
            <div class="loading" style="color: #ef4444;">
                Failed to load data: ${error.message}<br>
                <small>Please try again or check if the API server is running.</small>
            </div>
        `;
    }
}

function updateCountryStats(data) {
    const statsContainer = document.getElementById('countryStats');
    
    statsContainer.innerHTML = `
        <div class="stat-card">
            <div class="label">Capital</div>
            <div class="value">${data.capital || 'N/A'}</div>
        </div>
        <div class="stat-card">
            <div class="label">Population</div>
            <div class="value">${formatPopulation(data.population || 0)}</div>
        </div>
        <div class="stat-card">
            <div class="label">Currency</div>
            <div class="value">${data.currency?.name || 'N/A'} (${data.currency?.code || 'N/A'})</div>
        </div>
        <div class="stat-card">
            <div class="label">Exchange Rate (to USD)</div>
            <div class="value">${data.currency?.exchange_rate_usd?.toFixed(4) || 'N/A'}</div>
        </div>
        <div class="stat-card">
            <div class="label">GDP</div>
            <div class="value">${formatCurrency(data.economic?.gdp_usd || 0)}</div>
        </div>
        <div class="stat-card">
            <div class="label">Inflation Rate</div>
            <div class="value">${(data.economic?.inflation_rate || 0).toFixed(1)}%</div>
        </div>
        <div class="stat-card">
            <div class="label">Temperature</div>
            <div class="value">${data.weather?.temperature || 0}°C</div>
        </div>
        <div class="stat-card">
            <div class="label">Weather</div>
            <div class="value">${data.weather?.condition || 'N/A'}</div>
        </div>
    `;
    
    // Load and display risk score
    loadAndDisplayRiskScore(data.country_name);
}

async function loadAndDisplayRiskScore(countryName) {
    try {
        const response = await APIClient.getCountryRisk(countryName);
        
        if (response.status === 'success') {
            const riskData = response.data;
            const statsContainer = document.getElementById('countryStats');
            
            // Add risk score card
            const riskCard = document.createElement('div');
            riskCard.className = 'stat-card';
            riskCard.innerHTML = `
                <div class="label">Risk Score</div>
                <div class="value">${riskData.risk_score}</div>
                <span class="risk-badge ${getRiskClass(riskData.risk_category)}">${riskData.risk_category}</span>
            `;
            
            statsContainer.appendChild(riskCard);
        }
    } catch (error) {
        console.error('Error loading risk score:', error);
    }
}

async function updateChartsWithHistoricalData(countryName) {
    try {
        // Fetch historical data for each metric
        const [gdpData, inflationData, currencyData, riskData] = await Promise.all([
            APIClient.getHistoricalData(countryName, 'gdp', 30),
            APIClient.getHistoricalData(countryName, 'inflation', 30),
            APIClient.getHistoricalData(countryName, 'currency', 30),
            APIClient.getHistoricalData(countryName, 'risk_score', 30)
        ]);
        
        // Update GDP chart
        if (gdpData.status === 'success' && gdpData.data.length > 0) {
            const gdpValues = gdpData.data.map(d => d.value / 1e12); // Convert to trillions
            const labels = gdpData.data.map(d => new Date(d.recorded_at).toLocaleDateString());
            
            charts.gdp.data.labels = labels.slice(-10); // Last 10 points
            charts.gdp.data.datasets[0].data = gdpValues.slice(-10);
            charts.gdp.update();
        }
        
        // Update inflation chart
        if (inflationData.status === 'success' && inflationData.data.length > 0) {
            const inflationValues = inflationData.data.map(d => d.value);
            const labels = inflationData.data.map(d => new Date(d.recorded_at).toLocaleDateString());
            
            charts.inflation.data.labels = labels.slice(-10);
            charts.inflation.data.datasets[0].data = inflationValues.slice(-10);
            charts.inflation.update();
        }
        
        // Update currency chart
        if (currencyData.status === 'success' && currencyData.data.length > 0) {
            const currencyValues = currencyData.data.map(d => d.value);
            const labels = currencyData.data.map(d => new Date(d.recorded_at).toLocaleDateString());
            
            charts.currency.data.labels = labels.slice(-10);
            charts.currency.data.datasets[0].data = currencyValues.slice(-10);
            charts.currency.update();
        }
        
        // Update risk chart
        if (riskData.status === 'success' && riskData.data.length > 0) {
            const riskValues = riskData.data.map(d => d.value);
            const labels = riskData.data.map(d => new Date(d.recorded_at).toLocaleDateString());
            
            charts.risk.data.labels = labels.slice(-10);
            charts.risk.data.datasets[0].data = riskValues.slice(-10);
            charts.risk.update();
        }
        
    } catch (error) {
        console.error('Error loading historical data:', error);
        // Keep sample data if API fails
    }
}

function updateNewsDisplay(newsItems) {
    const newsContainer = document.getElementById('newsContainer');
    
    if (!newsItems || newsItems.length === 0) {
        newsContainer.innerHTML = '<div class="loading">No news available</div>';
        return;
    }
    
    newsContainer.innerHTML = newsItems.map(news => `
        <div class="news-item">
            <h4>${news.title}</h4>
            <p>${news.description || 'No description'}</p>
            <div class="meta">${news.category || 'General'} • ${formatDate(news.published_at)}</div>
        </div>
    `).join('');
}

function focusMapOnCountry(countryName) {
    const countryCoordinates = {
        "Germany": [51.1657, 10.4515],
        "China": [35.8617, 104.1954],
        "United States": [37.0902, -95.7129],
        "Japan": [36.2048, 138.2529],
        "Australia": [-25.2744, 133.7751],
        "Brazil": [-14.2350, -51.9253],
        "India": [20.5937, 78.9629],
        "Indonesia": [-0.7893, 113.9213],
        "Argentina": [-38.4161, -63.6167],
        "United Kingdom": [55.3781, -3.4360],
        "Singapore": [1.3521, 103.8198],
        "Netherlands": [52.1326, 5.2913],
        "Belgium": [50.5039, 4.4699],
        "UAE": [23.4241, 53.8478]
    };
    
    const coords = countryCoordinates[countryName];
    if (coords && map) {
        map.setView(coords, 5);
    }
}

// ============================================================================
// COUNTRY COMPARISON (REAL API)
// ============================================================================

async function compareCountriesReal() {
    const countryA = document.getElementById('compareCountryA').value;
    const countryB = document.getElementById('compareCountryB').value;
    
    if (!countryA || !countryB) {
        alert('Please select both countries to compare');
        return;
    }
    
    const comparisonResults = document.getElementById('comparisonResults');
    comparisonResults.innerHTML = '<div class="loading">Comparing countries...</div>';
    
    try {
        const response = await APIClient.compareCountries(countryA, countryB);
        
        if (response.status !== 'success') {
            throw new Error(response.message || 'Comparison failed');
        }
        
        const data = response.data;
        
        comparisonResults.innerHTML = `
            <div class="comparison-card">
                <h4>${data.country_a.name}</h4>
                <div class="comparison-metric">
                    <span class="metric-label">GDP</span>
                    <span class="metric-value">${formatCurrency(data.country_a.gdp_usd)}</span>
                </div>
                <div class="comparison-metric">
                    <span class="metric-label">Inflation</span>
                    <span class="metric-value">${data.country_a.inflation_rate.toFixed(1)}%</span>
                </div>
                <div class="comparison-metric">
                    <span class="metric-label">Risk Score</span>
                    <span class="metric-value">${data.country_a.risk_score}</span>
                </div>
                <div class="comparison-metric">
                    <span class="metric-label">Risk Category</span>
                    <span class="metric-value">
                        <span class="risk-badge ${getRiskClass(data.country_a.risk_category)}">${data.country_a.risk_category}</span>
                    </span>
                </div>
                <div class="comparison-metric">
                    <span class="metric-label">Temperature</span>
                    <span class="metric-value">${data.country_a.temperature}°C</span>
                </div>
                <div class="comparison-metric">
                    <span class="metric-label">Currency</span>
                    <span class="metric-value">${data.country_a.currency_code}</span>
                </div>
                <div class="comparison-metric">
                    <span class="metric-label">Exchange Rate</span>
                    <span class="metric-value">${data.country_a.exchange_rate_usd.toFixed(4)}</span>
                </div>
            </div>
            <div class="comparison-card">
                <h4>${data.country_b.name}</h4>
                <div class="comparison-metric">
                    <span class="metric-label">GDP</span>
                    <span class="metric-value">${formatCurrency(data.country_b.gdp_usd)}</span>
                </div>
                <div class="comparison-metric">
                    <span class="metric-label">Inflation</span>
                    <span class="metric-value">${data.country_b.inflation_rate.toFixed(1)}%</span>
                </div>
                <div class="comparison-metric">
                    <span class="metric-label">Risk Score</span>
                    <span class="metric-value">${data.country_b.risk_score}</span>
                </div>
                <div class="comparison-metric">
                    <span class="metric-label">Risk Category</span>
                    <span class="metric-value">
                        <span class="risk-badge ${getRiskClass(data.country_b.risk_category)}">${data.country_b.risk_category}</span>
                    </span>
                </div>
                <div class="comparison-metric">
                    <span class="metric-label">Temperature</span>
                    <span class="metric-value">${data.country_b.temperature}°C</span>
                </div>
                <div class="comparison-metric">
                    <span class="metric-label">Currency</span>
                    <span class="metric-value">${data.country_b.currency_code}</span>
                </div>
                <div class="comparison-metric">
                    <span class="metric-label">Exchange Rate</span>
                    <span class="metric-value">${data.country_b.exchange_rate_usd.toFixed(4)}</span>
                </div>
            </div>
            <div class="comparison-card" style="grid-column: span 2; background: rgba(0, 212, 255, 0.1);">
                <h4>🏆 Comparison Summary</h4>
                <div class="comparison-metric">
                    <span class="metric-label">Higher GDP</span>
                    <span class="metric-value">${data.comparison_metrics.gdp_winner}</span>
                </div>
                <div class="comparison-metric">
                    <span class="metric-label">Lower Inflation</span>
                    <span class="metric-value">${data.comparison_metrics.lower_inflation}</span>
                </div>
                <div class="comparison-metric">
                    <span class="metric-label">Lower Risk</span>
                    <span class="metric-value">${data.comparison_metrics.lower_risk}</span>
                </div>
                <div class="comparison-metric">
                    <span class="metric-label">Warmer Climate</span>
                    <span class="metric-value">${data.comparison_metrics.warmer}</span>
                </div>
            </div>
        `;
        
    } catch (error) {
        console.error('Error comparing countries:', error);
        comparisonResults.innerHTML = `
            <div class="loading" style="color: #ef4444;">
                Failed to compare: ${error.message}
            </div>
        `;
    }
}

// ============================================================================
// FAVORITES MANAGEMENT (REAL API)
// ============================================================================

async function loadFavoritesReal() {
    try {
        const response = await APIClient.getFavorites();
        
        if (response.status === 'success') {
            favorites = response.data.map(f => f.country_name);
            updateFavoritesGrid();
        }
    } catch (error) {
        console.error('Error loading favorites:', error);
    }
}

async function addToFavoritesReal() {
    if (!currentCountry) {
        alert('Please select a country first');
        return;
    }
    
    try {
        const response = await APIClient.addFavorite(currentCountry);
        
        if (response.status === 'success') {
            favorites.push(currentCountry);
            updateFavoritesGrid();
            alert(`${currentCountry} added to favorites!`);
        } else {
            alert(response.message || 'Failed to add to favorites');
        }
    } catch (error) {
        console.error('Error adding favorite:', error);
        alert('Failed to add to favorites. Please try again.');
    }
}

async function removeFromFavoritesReal(id) {
    try {
        const response = await APIClient.removeFavorite(id);
        
        if (response.status === 'success') {
            await loadFavoritesReal(); // Reload from server
        }
    } catch (error) {
        console.error('Error removing favorite:', error);
    }
}

function updateFavoritesGrid() {
    const grid = document.getElementById('favoritesGrid');
    
    if (favorites.length === 0) {
        grid.innerHTML = '<div class="loading">No favorites added yet</div>';
        return;
    }
    
    grid.innerHTML = favorites.map((country, index) => `
        <div class="favorite-card">
            <span class="country-name">${country}</span>
            <button class="remove-btn" onclick="removeFromFavoritesReal(${index})">×</button>
        </div>
    `).join('');
}

// ============================================================================
// PORT SEARCH (REAL API)
// ============================================================================

async function searchPortsReal() {
    const searchTerm = document.getElementById('portSearchInput').value;
    
    try {
        const response = await APIClient.getPorts(searchTerm);
        
        if (response.status === 'success') {
            const ports = response.data;
            addPortMarkers(ports);
            
            if (ports.length > 0) {
                map.setView([ports[0].latitude, ports[0].longitude], 6);
            }
        }
    } catch (error) {
        console.error('Error searching ports:', error);
    }
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

function formatDate(dateString) {
    if (!dateString) return 'Unknown';
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 60) return `${diffMins} minutes ago`;
    if (diffHours < 24) return `${diffHours} hours ago`;
    if (diffDays < 7) return `${diffDays} days ago`;
    
    return date.toLocaleDateString();
}

function formatCurrency(value) {
    if (value >= 1e12) return `$${(value / 1e12).toFixed(2)}T`;
    if (value >= 1e9) return `$${(value / 1e9).toFixed(2)}B`;
    if (value >= 1e6) return `$${(value / 1e6).toFixed(2)}M`;
    return `$${value.toLocaleString()}`;
}

function formatPopulation(value) {
    if (value >= 1e9) return `${(value / 1e9).toFixed(2)}B`;
    if (value >= 1e6) return `${(value / 1e6).toFixed(2)}M`;
    return value.toLocaleString();
}

function getRiskClass(category) {
    switch (category) {
        case 'Low Risk': return 'risk-low';
        case 'Medium Risk': return 'risk-medium';
        case 'High Risk': return 'risk-high';
        default: return '';
    }
}

// ============================================================================
// AUTO-REFRESH (POLLING)
// ============================================================================

let autoRefreshInterval = null;

function enableAutoRefresh(intervalMinutes = 5) {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
    
    autoRefreshInterval = setInterval(async () => {
        if (currentCountry) {
            console.log('Auto-refreshing data...');
            dataManager.clear();
            await loadCountryDataReal();
        }
    }, intervalMinutes * 60 * 1000);
    
    console.log(`Auto-refresh enabled (every ${intervalMinutes} minutes)`);
}

function disableAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
        console.log('Auto-refresh disabled');
    }
}

// ============================================================================
// INITIALIZATION
// ============================================================================

// Override original functions with API versions
window.loadCountryData = loadCountryDataReal;
window.compareCountries = compareCountriesReal;
window.addToFavorites = addToFavoritesReal;
window.searchPorts = searchPortsReal;

// ============================================================================
// POPULATE COUNTRIES DROPDOWN
// ============================================================================

async function populateCountriesDropdown() {
    try {
        const response = await APIClient.getCountries();
        
        if (response.status === 'success') {
            const select = document.getElementById('countrySelect');
            const compareSelectA = document.getElementById('compareCountryA');
            const compareSelectB = document.getElementById('compareCountryB');
            
            // Clear existing options (keep first option)
            select.innerHTML = '<option value="">Select Country</option>';
            compareSelectA.innerHTML = '<option value="">Country A</option>';
            compareSelectB.innerHTML = '<option value="">Country B</option>';
            
            // Add countries to dropdowns
            response.data.forEach(country => {
                const option = `<option value="${country.name}">${country.name}</option>`;
                select.innerHTML += option;
                compareSelectA.innerHTML += option;
                compareSelectB.innerHTML += option;
            });
            
            console.log(`Loaded ${response.count} countries`);
        }
    } catch (error) {
        console.error('Error loading countries:', error);
        // Fallback to sample countries if API fails
        const sampleCountries = ['Germany', 'China', 'United States', 'Japan', 'Australia', 'Brazil', 'India', 'Indonesia', 'Argentina', 'United Kingdom'];
        const select = document.getElementById('countrySelect');
        select.innerHTML = '<option value="">Select Country</option>';
        sampleCountries.forEach(country => {
            select.innerHTML += `<option value="${country}">${country}</option>`;
        });
    }
}

 document.addEventListener('DOMContentLoaded', function() {
    // Populate countries dropdown on startup
    populateCountriesDropdown();
    
    // Load favorites on startup
    loadFavoritesReal();
    
    // Enable auto-refresh (optional)
    // enableAutoRefresh(5); // Refresh every 5 minutes
    
    console.log('API Integration loaded successfully');
});
