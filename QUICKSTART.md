# QUICK START GUIDE - Global Supply Chain Risk Intelligence Platform

## 🚀 ACCESS THE DASHBOARD

### Option 1: Direct Browser Access (RECOMMENDED)
Open your browser and navigate to:
```
http://localhost:8002/dashboard.html
```

### Option 2: Using Terminal
```powershell
# From supply-chain-app directory
start http://localhost:8002/dashboard.html
```

---

## ✨ FEATURES TOUR

### 1. Global Country Dashboard
- **Input:** Search country in the left sidebar
- **Output:** 6 stat cards with real-time data:
  - GDP (USD)
  - Inflation Rate (%)
  - Population
  - Currency Code
  - Exchange Rate to USD
  - Risk Score (0-100)

**Try these countries:**
- Germany (Low Risk: 28)
- China (Medium Risk: 52)
- USA (Medium Risk: 35)
- Singapore (Low Risk: 22)
- India (Medium Risk: 58)
- Brazil (High Risk: 65)

### 2. Interactive World Map
- Shows selected country with risk score marker
- Color coding:
  - 🟢 Green: Low Risk (0-30)
  - 🟡 Yellow: Medium Risk (30-60)
  - 🔴 Red: High Risk (60-100)
- Click marker to view country details

### 3. Weather Monitoring
- Real-time temperature & conditions
- Humidity and wind speed
- Weather alerts (if applicable)

### 4. Historical Charts (Click tabs)
- **GDP Trend** - 12-month GDP history
- **Inflation Trend** - Monthly inflation rates
- **Exchange Rate** - Currency volatility
- **Risk Score** - Composite risk evolution

### 5. News Feed
- Supply chain related news
- Categories: All News, Logistics, Trade, Shipping
- Sentiment indicators (positive/neutral/negative)

### 6. Port Search
- Find ports by country or port name
- Details: Port type, region, coordinates
- Top 10 results displayed

### 7. Country Comparison
**Steps:**
1. Click **"Compare"** in navigation bar
2. Enter two country names (e.g., Germany & China)
3. Click **"Compare"** button
4. View side-by-side metrics

### 8. Favorites List
- Click **"Add to Favorites"** button in sidebar
- Favorite countries stored in browser
- Click favorites to quickly switch countries
- Favorites persist across sessions

### 9. Admin Panel
**Login Credentials:**
```
Username: admin
Password: admin123
```

**Available Features:**
- User management table
- Port dataset upload
- Article publishing form

---

## 🎮 INTERACTIVE DEMO

### Step 1: Initial Load
Website opens with Germany as default country
- Shows GDP: $4.08T, Inflation: 2.6%, Population: 83M
- Map centers on Germany
- Risk Score: 28 (Low Risk) 🟢

### Step 2: Search Another Country
1. Click search box in sidebar
2. Type "Singapore"
3. Press Enter or click Search button
4. Dashboard updates instantly:
   - New stats displayed
   - Map pans to Singapore
   - Charts refresh
   - News updates
   - Ports list shows Singapore ports

### Step 3: Compare Countries
1. Click **"Compare"** tab in navigation
2. Country A: Type "Germany"
3. Country B: Type "Brazil"
4. Click **"Compare"** button
5. View side-by-side comparison:
   ```
   Germany vs Brazil
   ├─ GDP: $4.08T vs $1.84T
   ├─ Inflation: 2.6% vs 7.8%
   ├─ Population: 83M vs 215M
   ├─ Currency: EUR vs BRL
   └─ Risk: 28 vs 65
   ```

### Step 4: Add to Favorites
1. Select a country (e.g., "Singapore")
2. Click **"Add to Favorites"** button (red heart)
3. Button changes to **"Remove from Favorites"**
4. Scroll down sidebar to see favorites list
5. Click favorite button to quick-switch

### Step 5: View Charts
1. Scroll down dashboard
2. Click chart tabs:
   - **GDP Trend** - Shows 12-month variation
   - **Inflation Trend** - Monthly inflation changes
   - **Exchange Rate** - Currency volatility
   - **Risk Score** - Risk evolution
3. Charts animate on switch

### Step 6: Admin Access
1. Click **"Admin"** tab in navigation
2. Enter credentials:
   - Username: `admin`
   - Password: `admin123`
3. Click "Login"
4. View:
   - User management section
   - Port upload form
   - Article publisher

---

## 📊 DATA SOURCES

### Pre-loaded Data:
- **8 Sample Countries** - Germany, China, USA, Japan, India, Singapore, Brazil, more...
- **380+ Ports** - From ports-complete.json database
- **Mock News Feed** - Sample articles with timestamps
- **Weather Data** - Simulated weather for each country

### Real API Integration Ready:
- Open-Meteo API (weather)
- World Bank API (economics)
- REST Countries API (geographic data)
- ExchangeRate API (currency rates)
- GNews API (news feeds)

---

## ⚙️ CONFIGURATION

### Change Default Country
Edit `dashboard.html`, line ~450:
```javascript
// Load default country
await selectCountry('Germany');  // Change to any country name
```

### Add New Country Data
Edit the `getMockCountryData()` function around line ~350:
```javascript
'NewCountry': {
    name: 'NewCountry',
    gdp: 1.5e12,
    inflation: 3.2,
    population: 50000000,
    currency: 'XXX',
    exchangeRate: 1.5,
    riskScore: 45,
    region: 'Region Name',
    coordinates: [lat, lng],
    weather: { temp: 20, condition: 'Sunny', humidity: 60, wind: 3 }
}
```

### Change Risk Score Thresholds
Edit the `RISK_THRESHOLDS` object around line ~310:
```javascript
const RISK_THRESHOLDS = {
    LOW: { min: 0, max: 30, label: 'Low', color: '#28a745' },
    MEDIUM: { min: 30, max: 60, label: 'Medium', color: '#ffc107' },
    HIGH: { min: 60, max: 100, label: 'High', color: '#dc3545' }
};
```

---

## 🐛 TROUBLESHOOTING

### Map not visible?
- Check browser console (F12 > Console)
- Ensure Leaflet CDN is loading
- Try refreshing page

### Charts not showing?
- Click different chart tabs to trigger rendering
- Check if JavaScript is enabled
- Clear browser cache (Ctrl+Shift+Delete)

### Favorites not saving?
- Check if localStorage is enabled in browser
- Try in non-private/incognito window
- Check browser storage quota

### Search returns no results?
- Check spelling of country name
- Try partial country name (e.g., "Ger" for Germany)
- Use country selector dropdown

---

## 🔧 BACKEND INTEGRATION

### To connect real backend:
1. Deploy `supply_chain_risk_engine.py` as API service (FastAPI/Flask)
2. Update API endpoints in `dashboard.html`:
   ```javascript
   // Around line 320 in APIClient
   const response = await fetch(`http://your-api-domain.com/api/country/${countryName}`);
   ```
3. Configure CORS headers on backend
4. Update localhost:8002 to production domain

### API Endpoint Format:
```javascript
GET /api/country/{name}
Returns: {
  name: "Country",
  gdp: 1.5e12,
  inflation: 3.2,
  population: 50000000,
  currency: "XXX",
  exchangeRate: 1.5,
  riskScore: 45,
  region: "Region",
  coordinates: [lat, lng],
  weather: { temp, condition, humidity, wind }
}
```

---

## 📱 MOBILE ACCESS

The dashboard is fully responsive:

- **Desktop (1200px+):** Full layout, all features visible
- **Tablet (768px-1199px):** Stacked layout, smaller map
- **Mobile (<768px):** Single column, collapsible sections

Test on mobile:
1. Open Dashboard
2. Press F12 (Developer Tools)
3. Click "Toggle Device Toolbar" or Ctrl+Shift+M
4. Select device (iPhone, iPad, etc.)
5. Navigate sections using navigation tabs

---

## 💾 LOCAL STORAGE

Favorites are saved in browser's localStorage:
- Key: `favorites`
- Format: JSON array
- Persists across browser sessions
- Survives browser restart

To clear favorites:
```javascript
// In browser console (F12)
localStorage.removeItem('favorites');
```

---

## 📞 SUPPORT

### For Issues:
1. Check IMPLEMENTATION_SUMMARY.md for detailed documentation
2. Review ARCHITECTURE_PHASE1.md for system design
3. Check RISK_ALGORITHM_PHASE2.md for risk scoring logic
4. See supply_chain_risk_engine.py for backend logic

### Files to Review:
- `public/dashboard.html` - Main application
- `ports-complete.json` - Port database
- `WEBAPP_PHASE4.md` - UI specifications

---

## 🎯 NEXT STEPS

1. **Test all 10 features** - Verify functionality
2. **Deploy backend** - Replace mock data with real APIs
3. **Connect database** - Set up PostgreSQL
4. **Add authentication** - Implement user login
5. **Monitor performance** - Set up logging

---

**Happy analyzing! 🚀**
