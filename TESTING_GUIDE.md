# End-to-End Testing Guide

## Overview

This guide provides step-by-step instructions to test all 10 features of the Global Supply Chain Risk Intelligence Platform.

## Prerequisites

- Python 3.8+ installed
- All dependencies installed (run `start.bat` or follow setup guide)
- Flask server running on `http://localhost:5000`

## Starting the Server

### Windows
```bash
# Double-click start.bat
# OR run manually:
start.bat
```

### Linux/Mac
```bash
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
python -c "from app import app, db; app.app_context().push(); db.create_all()"
python app.py
```

Server should start on `http://localhost:5000`

## Testing Checklist

### Feature 1: Global Country Dashboard ✅

**Test Steps:**
1. Open browser to `http://localhost:5000`
2. Select a country from dropdown (e.g., "Germany")
3. Click "Load" button
4. Verify sidebar displays:
   - Capital city
   - Population
   - Currency name and code
   - Exchange rate to USD
   - GDP value
   - Inflation rate
   - Temperature
   - Weather condition
   - Risk score with category badge

**Expected Result:**
- All statistics display correctly
- Data fetched from real APIs (Open-Meteo, World Bank, ExchangeRate)
- Loading state shown during API calls

**API Test:**
```bash
curl http://localhost:5000/api/country/Germany
```

---

### Feature 2: Risk Scoring Engine ✅

**Test Steps:**
1. Load any country data (Feature 1)
2. Observe Risk Score in sidebar
3. Verify risk category badge color:
   - Green for Low Risk (≤30)
   - Yellow for Medium Risk (31-60)
   - Red for High Risk (>60)

**Test Different Countries:**
- Germany: Should show Low Risk (~22)
- China: Should show Medium Risk (~47)
- Argentina: Should show High Risk (~95)

**Expected Result:**
- Risk score calculated from: Weather + Inflation + Currency + News
- Category matches score range
- Badge color correct

**API Test:**
```bash
curl http://localhost:5000/api/risk/Germany
```

---

### Feature 3: Global Weather Monitoring ✅

**Test Steps:**
1. Look at the map container on main dashboard
2. Verify map loads with OpenStreetMap tiles
3. Check for port markers (12 sample ports)
4. Click on any port marker
5. Verify popup shows port name and country
6. Click "Focus" button in popup
7. Verify map zooms to port location

**Expected Result:**
- Map renders correctly
- Markers display at correct coordinates
- Popups work interactively
- Focus function zooms appropriately

---

### Feature 4: Currency Impact Dashboard ✅

**Test Steps:**
1. Load country data (Feature 1)
2. Look at "Currency Trend" chart in charts grid
3. Verify chart shows exchange rate history
4. Hover over chart points to see values
5. Load different country to see different currency

**Expected Result:**
- Chart displays currency trend line
- Historical data from API or database
- Updates when country changes
- Tooltip shows exact values

**API Test:**
```bash
curl "http://localhost:5000/api/historical/Germany?metric_type=currency&days=30"
```

---

### Feature 5: News Intelligence Module ✅

**Test Steps:**
1. Look at "Latest News" section in sidebar
2. Verify news articles display
3. Check each article shows:
   - Title
   - Description
   - Category (logistics, trade, shipping, economy)
   - Time ago

**Expected Result:**
- News items display in sidebar
- Categories match supply chain topics
- Timestamps formatted correctly
- Clicking title opens article URL

**Note:** GNews API may require API key for real data. Without key, sample data displays.

---

### Feature 6: Port Location Dashboard ✅

**Test Steps:**
1. Look at port search input above map
2. Type "Shanghai" in search box
3. Click "Search" button
4. Verify only Shanghai port marker shows
5. Map zooms to Shanghai location
6. Clear search and search "China"
7. Verify all Chinese ports show

**Expected Result:**
- Search filters ports by name
- Search filters ports by country
- Map updates to show filtered results
- Markers clear and re-add correctly

**API Test:**
```bash
curl "http://localhost:5000/api/ports?search=Shanghai"
curl "http://localhost:5000/api/ports?country=China"
```

---

### Feature 7: Data Visualization Dashboard ✅

**Test Steps:**
1. Look at charts grid (4 charts)
2. Verify all 4 charts display:
   - GDP Trend (line chart)
   - Inflation Rate (bar chart)
   - Currency Trend (line chart)
   - Risk Score History (line chart)
3. Load different country
4. Verify charts update with new data
5. Hover over charts to see tooltips

**Expected Result:**
- All 4 charts render correctly
- Charts update when country changes
- Historical data displays properly
- Chart colors match theme
- Tooltips show data points

**API Tests:**
```bash
curl "http://localhost:5000/api/historical/Germany?metric_type=gdp"
curl "http://localhost:5000/api/historical/Germany?metric_type=inflation"
curl "http://localhost:5000/api/historical/Germany?metric_type=risk_score"
```

---

### Feature 8: Country Comparison Engine ✅

**Test Steps:**
1. Scroll to "Country Comparison Engine" section
2. Select "Germany" for Country A
3. Select "China" for Country B
4. Click "Compare" button
5. Verify side-by-side comparison shows:
   - GDP values
   - Inflation rates
   - Risk scores
   - Risk categories (with badges)
   - Temperatures
   - Currency codes
   - Exchange rates
6. Verify summary section shows winners:
   - Higher GDP
   - Lower inflation
   - Lower risk
   - Warmer climate

**Expected Result:**
- Both countries display side-by-side
- All 5 metrics show correctly
- Comparison summary highlights winners
- Risk badges colored correctly

**API Test:**
```bash
curl -X POST http://localhost:5000/api/compare \
  -H "Content-Type: application/json" \
  -d '{"country_a": "Germany", "country_b": "China"}'
```

---

### Feature 9: Favorite Monitoring List ✅

**Test Steps:**
1. Load a country (e.g., "Germany")
2. Click "⭐ Add to Favorites" button
3. Scroll to "Favorite Monitoring List" section
4. Verify Germany appears in favorites grid
5. Load another country (e.g., "China")
6. Add to favorites
7. Verify both countries show in list
8. Click "×" button on Germany
9. Verify Germany removed from list

**Expected Result:**
- Countries add to favorites
- Favorites persist (stored in database)
- Multiple favorites display
- Remove function works
- Grid updates dynamically

**API Tests:**
```bash
# Get favorites
curl http://localhost:5000/api/favorites

# Add favorite
curl -X POST http://localhost:5000/api/favorites \
  -H "Content-Type: application/json" \
  -d '{"country_name": "Germany"}'

# Remove favorite (use ID from GET response)
curl -X DELETE http://localhost:5000/api/favorites/1
```

---

### Feature 10: Admin Dashboard ✅

**Test Steps:**
1. Scroll to "Admin Control Panel" section
2. Verify 3 admin cards display:
   - User Management
   - Port Dataset
   - Analysis Articles
3. Click buttons on each card
4. Verify buttons are clickable (UI placeholder)

**Note:** Full admin functionality requires authentication implementation. Current version provides UI structure and API endpoints.

**API Tests:**
```bash
# Get users
curl http://localhost:5000/api/admin/users

# Create user
curl -X POST http://localhost:5000/api/admin/users \
  -H "Content-Type: application/json" \
  -d '{"username": "testuser", "email": "test@example.com", "role": "viewer"}'

# Get articles
curl http://localhost:5000/api/admin/articles

# Create article
curl -X POST http://localhost:5000/api/admin/articles \
  -H "Content-Type: application/json" \
  -d '{"title": "Test Article", "content": "Test content", "category": "analysis"}'
```

---

## Real-Time Updates Test

**Test Steps:**
1. Edit `public/js/api-integration.js`
2. Uncomment line at bottom: `enableAutoRefresh(5);`
3. Reload page
4. Load a country
5. Wait 5 minutes
6. Verify data refreshes automatically

**Expected Result:**
- Data refreshes without manual reload
- Console shows refresh messages
- Charts update with new data

---

## Database Verification

**Check Database Contents:**

```bash
# Python shell
python
>>> from app import app, db
>>> from app import User, FavoriteCountry, HistoricalData, Port, Article
>>> app.app_context().push()
>>> 
>>> # Check ports
>>> Port.query.count()
12
>>> 
>>> # Check historical data (after loading countries)
>>> HistoricalData.query.count()
> 0
>>> 
>>> # Check favorites (after adding)
>>> FavoriteCountry.query.count()
> 0
```

---

## Performance Testing

**Load Test:**
1. Open browser DevTools (F12)
2. Go to Network tab
3. Load country data
4. Check API response times:
   - Should be < 5 seconds for country dashboard
   - Should be < 2 seconds for risk calculation
   - Should be < 1 second for comparison

**Cache Test:**
1. Load Germany
2. Load China
3. Load Germany again
4. Second load should be faster (from cache)

---

## Error Handling Tests

**Test Invalid Country:**
```bash
curl http://localhost:5000/api/country/InvalidCountry
```
Expected: 404 error with message

**Test Missing Parameters:**
```bash
curl -X POST http://localhost:5000/api/compare \
  -H "Content-Type: application/json" \
  -d '{"country_a": "Germany"}'
```
Expected: 400 error with message

**Test Empty Search:**
```bash
curl http://localhost:5000/api/ports?search=
```
Expected: Returns all ports

---

## Browser Compatibility Test

Test in multiple browsers:
- ✅ Chrome/Edge (Chromium)
- ✅ Firefox
- ✅ Safari

Verify:
- Map renders correctly
- Charts display
- API calls work
- UI responsive

---

## Mobile Responsiveness Test

1. Open DevTools (F12)
2. Toggle device toolbar (Ctrl+Shift+M)
3. Test different screen sizes:
   - Mobile (375x667)
   - Tablet (768x1024)
   - Desktop (1920x1080)

Verify:
- Layout adapts correctly
- Grid changes to single column on mobile
- All features accessible
- Touch interactions work

---

## Integration Test Summary

| Feature | Status | Notes |
|---------|--------|-------|
| 1. Country Dashboard | ✅ | All APIs integrated |
| 2. Risk Scoring Engine | ✅ | 4-component calculation |
| 3. Weather Monitoring | ✅ | Leaflet + OpenStreetMap |
| 4. Currency Dashboard | ✅ | Historical trends |
| 5. News Intelligence | ✅ | GNews API (requires key) |
| 6. Port Location | ✅ | Search & filter working |
| 7. Data Visualization | ✅ | 4 Chart.js charts |
| 8. Country Comparison | ✅ | Side-by-side comparison |
| 9. Favorite List | ✅ | Database persistence |
| 10. Admin Dashboard | ✅ | API endpoints ready |

---

## Common Issues & Solutions

### Issue: Server won't start
**Solution:** Check Python version, activate venv, install dependencies

### Issue: API returns 500 error
**Solution:** Check Flask logs, verify external APIs accessible

### Issue: Map doesn't load
**Solution:** Check internet connection (OpenStreetMap requires internet)

### Issue: Charts don't update
**Solution:** Check browser console for errors, verify Chart.js loaded

### Issue: Favorites not saving
**Solution:** Check database permissions, verify SQLite file writable

### Issue: CORS errors
**Solution:** Verify Flask-CORS installed, check app.py configuration

---

## Production Deployment Checklist

Before deploying to production:

- [ ] Set `debug=False` in `app.py`
- [ ] Use production database (PostgreSQL)
- [ ] Implement authentication for admin endpoints
- [ ] Add rate limiting
- [ ] Enable HTTPS
- [ ] Configure CORS for specific domains
- [ ] Set up logging
- [ ] Add monitoring/alerting
- [ ] Backup database regularly
- [ ] Use production WSGI server (Gunicorn/Waitress)

---

## Success Criteria

Platform is fully functional when:

✅ All 10 features work as expected
✅ API endpoints respond correctly
✅ Database stores and retrieves data
✅ Frontend integrates with backend seamlessly
✅ Real-time updates function (when enabled)
✅ Error handling works properly
✅ UI is responsive and accessible
✅ Performance is acceptable (< 5s for complex queries)

---

## Next Steps

After successful testing:

1. **Add Authentication**: Implement user login for admin features
2. **Enhance Caching**: Add Redis for production caching
3. **Add More Ports**: Import full World Port Index dataset
4. **Implement WebSocket**: Replace polling with real-time WebSocket
5. **Add Email Alerts**: Send notifications for high-risk countries
6. **Create Reports**: Generate PDF reports for country analysis
7. **Add Mobile App**: Develop React Native mobile application

---

**Testing Status: ✅ READY FOR TESTING**

Run the server and follow this guide to verify all features work correctly.
