/**
 * Global Supply Chain Risk Intelligence - API Client
 * Connects frontend to FastAPI backend
 * Handles all API calls, caching, and error handling
 */

class APIClient {
    constructor(baseURL = 'http://localhost:8000/api') {
        this.baseURL = baseURL;
        this.cache = new Map();
        this.cacheTTL = 15 * 60 * 1000; // 15 minutes
        this.timeout = 10000; // 10 seconds
    }

    /**
     * Set backend API URL
     */
    setBaseURL(url) {
        this.baseURL = url;
        console.log(`✓ API Base URL set to: ${url}`);
    }

    /**
     * Make HTTP request with timeout and error handling
     */
    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), this.timeout);

        try {
            const response = await fetch(url, {
                ...options,
                signal: controller.signal,
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                }
            });

            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            console.log(`✓ API Response from ${endpoint}:`, data);
            return data;

        } catch (error) {
            clearTimeout(timeoutId);
            console.error(`✗ API Error [${endpoint}]:`, error.message);
            throw error;
        }
    }

    /**
     * Get from cache or fetch
     */
    async getOrCache(key, fetchFn) {
        // Check cache
        const cached = this.cache.get(key);
        if (cached && Date.now() - cached.timestamp < this.cacheTTL) {
            console.log(`✓ Cache HIT for ${key}`);
            return cached.data;
        }

        // Fetch fresh data
        console.log(`⟳ Cache MISS, fetching ${key}`);
        const data = await fetchFn();

        // Store in cache
        this.cache.set(key, {
            data: data,
            timestamp: Date.now()
        });

        return data;
    }

    /**
     * Clear cache
     */
    clearCache() {
        this.cache.clear();
        console.log('✓ Cache cleared');
    }

    // ============================================
    // HEALTH CHECK
    // ============================================

    async checkHealth() {
        try {
            return await this.request('/health');
        } catch (error) {
            console.error('Backend health check failed:', error);
            return null;
        }
    }

    // ============================================
    // COUNTRY ENDPOINTS
    // ============================================

    /**
     * Get comprehensive country dashboard
     */
    async getCountryDashboard(countryName) {
        const cacheKey = `country:${countryName}`;
        
        return await this.getOrCache(cacheKey, async () => {
            return await this.request(`/country/${encodeURIComponent(countryName)}`);
        });
    }

    /**
     * Get country risk breakdown
     */
    async getCountryRisk(countryName) {
        const cacheKey = `risk:${countryName}`;
        
        return await this.getOrCache(cacheKey, async () => {
            return await this.request(`/risk/${encodeURIComponent(countryName)}`);
        });
    }

    /**
     * Compare two countries
     */
    async compareCountries(countryA, countryB) {
        try {
            const params = new URLSearchParams({
                country_a: countryA,
                country_b: countryB
            });
            
            return await this.request(`/compare?${params.toString()}`, {
                method: 'POST'
            });
        } catch (error) {
            console.error('Error comparing countries:', error);
            throw error;
        }
    }

    // ============================================
    // MACROECONOMIC DATA
    // ============================================

    /**
     * Get macroeconomic data (GDP, Inflation, etc)
     */
    async getMacroeconomicData(countryName) {
        const cacheKey = `macro:${countryName}`;
        
        return await this.getOrCache(cacheKey, async () => {
            return await this.request(`/macroeconomic/${encodeURIComponent(countryName)}`);
        });
    }

    // ============================================
    // WEATHER DATA
    // ============================================

    /**
     * Get weather data for country
     */
    async getWeatherData(countryName) {
        const cacheKey = `weather:${countryName}`;
        
        return await this.getOrCache(cacheKey, async () => {
            return await this.request(`/weather/${encodeURIComponent(countryName)}`);
        });
    }

    // ============================================
    // EXCHANGE RATES
    // ============================================

    /**
     * Get exchange rates for currency
     */
    async getExchangeRates(currencyCode) {
        const cacheKey = `rates:${currencyCode}`;
        
        return await this.getOrCache(cacheKey, async () => {
            return await this.request(`/exchange-rates/${currencyCode}`);
        });
    }

    // ============================================
    // NEWS INTELLIGENCE
    // ============================================

    /**
     * Get supply chain news
     */
    async getNews(country = null, category = 'logistics') {
        const params = new URLSearchParams();
        if (country) params.append('country', country);
        params.append('category', category);
        
        const cacheKey = `news:${country}:${category}`;
        
        return await this.getOrCache(cacheKey, async () => {
            return await this.request(`/news?${params.toString()}`);
        });
    }

    // ============================================
    // PORT SEARCH
    // ============================================

    /**
     * Search ports by query or country
     */
    async searchPorts(query = null, country = null) {
        const params = new URLSearchParams();
        if (query) params.append('query', query);
        if (country) params.append('country', country);
        
        try {
            const response = await this.request(`/ports/search?${params.toString()}`);
            console.log(`✓ Found ${response.ports?.length || 0} ports`);
            return response.ports || [];
        } catch (error) {
            console.error('Error searching ports:', error);
            return [];
        }
    }

    // ============================================
    // GEOGRAPHIC DATA
    // ============================================

    /**
     * Get geographic data for country
     */
    async getGeographicData(countryName) {
        const cacheKey = `geographic:${countryName}`;
        
        return await this.getOrCache(cacheKey, async () => {
            return await this.request(`/geographic/${encodeURIComponent(countryName)}`);
        });
    }

    // ============================================
    // BATCH OPERATIONS
    // ============================================

    /**
     * Get all data for a country (batch call)
     */
    async getAllCountryData(countryName) {
        console.log(`📊 Fetching all data for ${countryName}...`);
        
        try {
            const [dashboard, risk, weather, macro, geographic] = await Promise.all([
                this.getCountryDashboard(countryName),
                this.getCountryRisk(countryName),
                this.getWeatherData(countryName),
                this.getMacroeconomicData(countryName),
                this.getGeographicData(countryName)
            ]);

            console.log(`✓ All data fetched for ${countryName}`);
            
            return {
                dashboard,
                risk,
                weather,
                macro,
                geographic
            };
        } catch (error) {
            console.error('Error fetching all country data:', error);
            throw error;
        }
    }

    /**
     * Get analytics for multiple countries
     */
    async getMultiCountryAnalytics(countryNames) {
        console.log(`📈 Fetching analytics for ${countryNames.length} countries...`);
        
        try {
            const results = await Promise.all(
                countryNames.map(country => this.getCountryDashboard(country))
            );
            
            return results.map((result, index) => ({
                country: countryNames[index],
                ...result
            }));
        } catch (error) {
            console.error('Error fetching multi-country analytics:', error);
            throw error;
        }
    }

    // ============================================
    // STATISTICS & AGGREGATIONS
    // ============================================

    /**
     * Get global statistics
     */
    async getGlobalStatistics() {
        const cacheKey = 'global:stats';
        
        return await this.getOrCache(cacheKey, async () => {
            try {
                // This would call a backend endpoint
                // For now, calculate from available data
                return {
                    timestamp: new Date().toISOString(),
                    status: 'ready'
                };
            } catch (error) {
                console.error('Error fetching global statistics:', error);
                throw error;
            }
        });
    }
}

// ============================================
// EXPORT & INITIALIZE
// ============================================

// Create global API client instance
const apiClient = new APIClient();

// Check backend health on load
window.addEventListener('load', async () => {
    const health = await apiClient.checkHealth();
    if (health) {
        console.log('✓ Backend connection successful');
        document.body.setAttribute('data-backend-connected', 'true');
    } else {
        console.warn('⚠ Backend connection failed - using mock data');
        document.body.setAttribute('data-backend-connected', 'false');
    }
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = APIClient;
}
