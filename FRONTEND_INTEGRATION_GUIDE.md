# Frontend Integration Guide

## Overview

The frontend dashboard now connects to the FastAPI backend server. All data is fetched from real APIs instead of mock data.

---

## Quick Start

### 1. Start FastAPI Backend

```bash
python main.py
```

Server runs on: `http://localhost:8000`

API docs available at: `http://localhost:8000/docs`

### 2. Start PHP Frontend Server

```bash
php -S localhost:8002 -t public
```

Frontend runs on: `http://localhost:8002`

### 3. Access Integrated Dashboard

```
http://localhost:8002/dashboard-integrated.html
```

---

## Files

### Frontend Files

```
public/
├── dashboard-integrated.html    ← NEW: Connected dashboard
├── js/
│   └── api-client.js            ← NEW: API client library
├── dashboard-new.html           ← Previous version (still works)
└── index-simple.html            ← Simple port map (still works)
```

### API Client Library

**File:** `public/js/api-client.js`

Features:
- ✓ Automatic HTTP request handling
- ✓ Built-in caching (15 minutes TTL)
- ✓ Error handling with fallbacks
- ✓ Timeout protection (10 seconds)
- ✓ Batch operations for parallel requests
- ✓ Debug logging to console

---

## API Client Usage

### Basic Usage

```javascript
// Initialize (automatic on page load)
const apiClient = new APIClient('http://localhost:8000/api');

// Get country dashboard
const data = await apiClient.getCountryDashboard('Germany');
console.log(data);

// Get risk breakdown
const risk = await apiClient.getCountryRisk('China');
console.log(risk);

// Search ports
const ports = await apiClient.searchPorts('Singapore');
console.log(ports);
```

### Advanced Usage

```javascript
// Get all data for country (parallel requests)
const allData = await apiClient.getAllCountryData('Germany');
console.log(allData);

// Get analytics for multiple countries
const analytics = await apiClient.getMultiCountryAnalytics(
    ['Germany', 'China', 'Singapore', 'Japan']
);
console.log(analytics);

// Compare countries
const comparison = await apiClient.compareCountries('Germany', 'China');
console.log(comparison);

// Clear cache
apiClient.clearCache();

// Set custom API URL
apiClient.setBaseURL('http://api.example.com/api');
```

### Caching

All GET requests are automatically cached for 15 minutes:

```javascript
// First call: fetches from API
await apiClient.getCountryDashboard('Germany');

// Second call (within 15 min): returns from cache
await apiClient.getCountryDashboard('Germany');  // Returns from cache ✓

// After 15 minutes: fetches fresh data
```

Clear cache:
```javascript
apiClient.clearCache();
```

---

## Backend Configuration

### API Base URL

Default: `http://localhost:8000/api`

Change in dashboard:

```javascript
// Option 1: Initialize with custom URL
const apiClient = new APIClient('http://api.example.com/api');

// Option 2: Set URL after initialization
apiClient.setBaseURL('http://api.example.com/api');
```

### Environment Variables (.env)

For production, set backend URL:

```
REACT_APP_API_URL=https://api.production.com/api
```

### CORS Configuration

Backend allows all origins (production: restrict this!)

```python
# main.py - CORS setup
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # CHANGE IN PRODUCTION
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)
```

---

## Available API Endpoints

### Country Data
```javascript
// Get complete dashboard
GET /api/country/{country_name}
Response: {gdp, inflation, population, currency, riskScore, weather, coordinates}

// Get risk breakdown
GET /api/risk/{country_name}
Response: {weatherRisk, inflationRisk, currencyRisk, newsRisk, compositeScore, riskLevel}
```

### Macroeconomic Data
```javascript
GET /api/macroeconomic/{country_name}
Response: {region, incomeLevel, capitalCity, coordinates}
```

### Weather
```javascript
GET /api/weather/{country_name}
Response: {temperature, humidity, windSpeed, condition}
```

### Exchange Rates
```javascript
GET /api/exchange-rates/{currency_code}
Response: {base, target, rate, rates, timestamp}
```

### News
```javascript
GET /api/news?country={country}&category={category}
Response: {articles: [...], count, timestamp}
```

### Geographic Data
```javascript
GET /api/geographic/{country_name}
Response: {name, region, currencies, languages, borders, timezone}
```

### Port Search
```javascript
GET /api/ports/search?query={query}&country={country}
Response: {ports: [...], count}
```

### Compare Countries
```javascript
POST /api/compare?country_a=Germany&country_b=China
Response: {countryA, countryB, metrics}
```

### Health Check
```javascript
GET /api/health
Response: {status, timestamp, version}
```

---

## Error Handling

The API client handles errors gracefully:

```javascript
try {
    const data = await apiClient.getCountryDashboard('Germany');
} catch (error) {
    console.error('Failed to fetch:', error.message);
    // Use fallback data or show error to user
}
```

Common errors:

| Error | Cause | Solution |
|-------|-------|----------|
| Connection refused | Backend not running | Start: `python main.py` |
| 404 Not Found | Invalid country name | Check spelling |
| 504 Timeout | API slow | Increase timeout in api-client.js |
| CORS error | Backend CORS not enabled | Check CORS config in main.py |

---

## Debugging

### Enable Console Logging

All API calls log to browser console:

```
✓ API Response from /api/country/Germany: {...}
✓ Cache HIT for country:Germany
⟳ Cache MISS, fetching risk:China
✗ API Error [/api/country/InvalidCountry]: HTTP 404: Not Found
```

Open browser DevTools (F12) to see logs:

```
Console Tab → Shows all API activity
Network Tab → Shows all HTTP requests
Application Tab → Shows cached data in localStorage
```

### Check Backend Health

```javascript
// Check if backend is connected
const health = await apiClient.checkHealth();
console.log(health);  // {status: "healthy", version: "1.0.0"}
```

### Network Inspection

1. Open DevTools (F12)
2. Go to Network tab
3. Make an API call
4. Click the request to see:
   - Request headers
   - Response body
   - Response headers
   - Timing information

---

## Performance Tips

### 1. Batch Requests

Instead of:
```javascript
// 5 separate calls = slow
await apiClient.getCountryDashboard('Germany');
await apiClient.getCountryRisk('Germany');
await apiClient.getWeatherData('Germany');
await apiClient.getMacroeconomicData('Germany');
await apiClient.getGeographicData('Germany');
```

Use batch:
```javascript
// 1 parallel call = fast
const data = await apiClient.getAllCountryData('Germany');
```

### 2. Leverage Caching

Cache automatically stores results for 15 minutes:
```javascript
// First call: fetches from API (slow)
const data1 = await apiClient.getCountryDashboard('Germany');

// Second call: returns from cache (instant)
const data2 = await apiClient.getCountryDashboard('Germany');
```

### 3. Monitor Network Usage

Check Network tab in DevTools to see:
- Request sizes
- Response times
- Failed requests
- Cached responses (marked as "from ServiceWorker")

---

## Troubleshooting

### Issue: API requests fail with "Connection refused"

**Solution:** Ensure backend is running
```bash
# Terminal 1: Backend
python main.py

# Terminal 2: Frontend
php -S localhost:8002 -t public
```

### Issue: "CORS error" in browser

**Solution:** Check CORS configuration in backend
```python
# main.py should have
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)
```

### Issue: Countries return "404 Not Found"

**Solution:** Check country name spelling
- Use exact names: "Germany" (not "germany")
- Try sample countries: Germany, China, USA, Singapore, Japan
- See available countries in database

### Issue: Map not showing after data load

**Solution:** Verify coordinates in response
```javascript
console.log(data.coordinates);  // Should be [latitude, longitude]
```

### Issue: "Backend offline" message

**Solution:** 
1. Check backend is running: `python main.py`
2. Verify URL is correct: `http://localhost:8000`
3. Check firewall not blocking port 8000
4. Try in different browser

---

## Next Steps

1. ✅ API Client created
2. ✅ Connected dashboard built
3. ⏭ Next: Implement Redis caching layer (Task #4)
4. ⏭ Then: Add user authentication (Task #5)

---

## Production Checklist

Before deploying to production:

- [ ] Change API base URL to production server
- [ ] Restrict CORS origins (not "*")
- [ ] Enable HTTPS
- [ ] Add API authentication (JWT tokens)
- [ ] Implement rate limiting
- [ ] Set up monitoring/alerting
- [ ] Configure SSL certificates
- [ ] Set up backup strategy
- [ ] Performance test with load testing
- [ ] Security audit

---

## Support

For issues or questions:

1. Check browser console (F12)
2. Check Network tab for failed requests
3. Verify backend logs: `python main.py` output
4. Check API docs: `http://localhost:8000/docs`

---

**Frontend is now connected to backend!** 🎉
