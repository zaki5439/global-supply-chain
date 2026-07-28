# FASE 1: ARSITEKTUR SISTEM & PEMETAAN ALIRAN DATA
## Global Supply Chain Risk Intelligence Platform

---

## 1. RINGKASAN ARSITEKTUR (HIGH-LEVEL OVERVIEW)

Platform ini mengintegrasikan **6 sumber data eksternal** dengan **Risk Engine kalkulatif** dan **UI Dashboard interaktif** untuk memberikan intelligence real-time tentang risiko rantai pasok global.

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    GLOBAL SUPPLY CHAIN RISK INTELLIGENCE PLATFORM            │
└─────────────────────────────────────────────────────────────────────────────┘

LAYER 1: DATA ACQUISITION (SUMBER DATA EKSTERNAL)
├── Open-Meteo API (Weather Data)
├── World Bank API (Macro Economic Indicators)
├── REST Countries API (Geographic Metadata)
├── ExchangeRate API (Currency Conversion)
├── GNews API (News Intelligence)
└── World Port Index Dataset (Maritime Infrastructure)

        ↓ (HTTP/REST Requests)

LAYER 2: DATA INGESTION & TRANSFORMATION (PYTHON BACKEND)
├── API Client Manager (requests + error handling)
├── Data Normalizer (standardisasi format)
├── Risk Calculator Engine (Scoring Logic)
├── Caching Layer (Redis/In-Memory untuk performance)
└── Database ORM (SQLAlchemy/SQLite)

        ↓ (JSON/REST API Responses)

LAYER 3: APPLICATION LOGIC (FASTAPI/FLASK ENDPOINTS)
├── /api/country/{name} → Country Dashboard
├── /api/risk/{country} → Risk Score Calculation
├── /api/compare → Country Comparison
├── /api/ports → Port Location Search
├── /api/news → News Intelligence
├── /api/admin/* → Admin Panel Endpoints
└── /api/favorites → User Bookmark Management

        ↓ (JSON Payloads)

LAYER 4: FRONTEND & VISUALIZATION (HTML/CSS/JS + LIBRARIES)
├── UI Components
│   ├── Country Dashboard (Stats + Cards)
│   ├── Risk Gauge & Status Indicators
│   ├── Weather Monitoring Map (Leaflet.js)
│   ├── Port Location Dashboard (Interactive Markers)
│   ├── Currency Impact Dashboard (Real-time rates)
│   ├── News Intelligence Module (Feed/Alerts)
│   ├── Country Comparison Engine (Side-by-side metrics)
│   ├── Data Visualization (Chart.js Graphs)
│   ├── Favorite Monitoring List (Bookmarks)
│   └── Admin Control Panel (User Management)
│
└── Integrations
    ├── Leaflet.js (OpenStreetMap Layer)
    ├── Chart.js (Historical Trends)
    └── WebSocket (Real-time updates - optional)
```

---

## 2. DATA FLOW DIAGRAM (ALIRAN DATA LENGKAP)

### A. Inisialisasi Sistem (System Startup)

```
User Opens Dashboard
    ↓
Frontend loads index.html + CSS + JS libraries
    ↓
JavaScript initializes:
  - Leaflet map (OpenStreetMap tiles)
  - Chart.js canvas elements
  - Event listeners (filters, searches, toggles)
    ↓
On Page Load → Triggers initial API calls:
    ├── GET /api/countries/list (dropdown population)
    ├── GET /api/ports/all (port markers cache)
    ├── GET /api/news?category=all (recent news feed)
    └── GET /api/exchange-rates (base currency data)
    ↓
Backend responses cached in browser localStorage
    ↓
UI renders with initial dataset
```

### B. User Interaction Flow (Contoh: Country Selection)

```
User selects "Germany" from Country Dropdown
    ↓ (onChange event)
Frontend sends: GET /api/country/Germany
    ↓
Backend execution:
    1. Receive country name parameter
    2. Query cache (if data <15 min old, return cached)
    3. If no cache:
       a. World Bank API → Fetch GDP, Inflation, Population
       b. REST Countries API → Fetch currency, region
       c. ExchangeRate API → Fetch current exchange rates
       d. Open-Meteo API → Fetch weather data at country center
       e. GNews API → Fetch relevant news (keywords: country + supply chain)
    4. Normalize all data into standard format
    5. Calculate Risk Score (using Risk Engine)
    6. Format response JSON
    7. Cache response for 15 minutes
    8. Return to Frontend
    ↓
Frontend receives response + updates UI:
    ├── Update Country Stats Card (GDP, Inflation, Pop, Currency)
    ├── Update Risk Gauge visualization
    ├── Update Weather widget
    ├── Redraw map with weather overlay
    ├── Update News feed
    └── Refresh Chart.js graphs with historical data
```

### C. Risk Score Calculation Flow

```
calculate_country_risk(country_name) invoked
    ↓
Step 1: Fetch Real-time Data
  weather_risk = get_weather_risk(country_center_coords)  → [0-100]
  inflation_risk = get_inflation_risk(country_name)       → [0-100]
  currency_risk = get_currency_volatility(country_name)   → [0-100]
  news_risk = get_news_sentiment_risk(country_name)       → [0-100]
    ↓
Step 2: Normalize & Weight
  W_weather = 0.25
  W_inflation = 0.25
  W_currency = 0.30
  W_news = 0.20
    ↓
Step 3: Calculate Composite Score
  risk_score = (weather_risk * W_weather) +
               (inflation_risk * W_inflation) +
               (currency_risk * W_currency) +
               (news_risk * W_news)
    ↓
Step 4: Determine Risk Category
  if risk_score < 30:
    category = "Low Risk" (Green)
  elif risk_score < 60:
    category = "Medium Risk" (Yellow)
  else:
    category = "High Risk" (Red)
    ↓
Return: { score: 45, category: "Medium Risk", breakdown: {...} }
```

### D. Port Search & Filter Flow

```
User enters port name in search box or selects country filter
    ↓
Frontend sends: GET /api/ports/search?query=Singapore&country=Singapore
    ↓
Backend:
  1. Query World Port Index dataset (in-memory or DB)
  2. Filter by name (fuzzy match) + country
  3. Return matched ports with coordinates
    ↓
Frontend updates map:
  - Clear previous markers
  - Add new Leaflet circleMarkers for each result
  - Bind popups with port info (name, lat/lng, facilities)
  - Fit map bounds to show all results
```

---

## 3. ENTITY-RELATIONSHIP DIAGRAM (ERD) & SCHEMA DATABASE

### A. Relational Schema (SQLite/PostgreSQL)

```sql
-- TABEL 1: users
CREATE TABLE users (
  user_id INTEGER PRIMARY KEY AUTOINCREMENT,
  username VARCHAR(100) UNIQUE NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin', 'analyst', 'viewer') DEFAULT 'viewer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  is_active BOOLEAN DEFAULT TRUE
);

-- TABEL 2: countries
CREATE TABLE countries (
  country_id INTEGER PRIMARY KEY AUTOINCREMENT,
  name VARCHAR(100) UNIQUE NOT NULL,
  iso_code VARCHAR(3) UNIQUE NOT NULL,
  region VARCHAR(50) NOT NULL,
  center_lat FLOAT,
  center_lng FLOAT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- TABEL 3: country_macroeconomic_data
CREATE TABLE country_macroeconomic_data (
  macro_id INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id INTEGER NOT NULL,
  year INTEGER NOT NULL,
  gdp_usd BIGINT,
  inflation_rate FLOAT,
  population INTEGER,
  exports_usd BIGINT,
  imports_usd BIGINT,
  currency_code VARCHAR(3),
  currency_exchange_rate FLOAT,
  data_source VARCHAR(50),
  retrieved_at TIMESTAMP,
  FOREIGN KEY (country_id) REFERENCES countries(country_id),
  UNIQUE(country_id, year)
);

-- TABEL 4: weather_data
CREATE TABLE weather_data (
  weather_id INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id INTEGER NOT NULL,
  temperature_celsius FLOAT,
  humidity_percent INTEGER,
  rainfall_mm FLOAT,
  wind_speed_kmh FLOAT,
  weather_condition VARCHAR(50),
  is_severe_warning BOOLEAN DEFAULT FALSE,
  warning_type VARCHAR(100),
  retrieved_at TIMESTAMP,
  FOREIGN KEY (country_id) REFERENCES countries(country_id)
);

-- TABEL 5: ports
CREATE TABLE ports (
  port_id INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id INTEGER NOT NULL,
  port_name VARCHAR(150) UNIQUE NOT NULL,
  latitude FLOAT NOT NULL,
  longitude FLOAT NOT NULL,
  port_type VARCHAR(50),
  annual_container_throughput INTEGER,
  facilities TEXT,
  importance_score FLOAT,
  FOREIGN KEY (country_id) REFERENCES countries(country_id)
);

-- TABEL 6: news_articles
CREATE TABLE news_articles (
  article_id INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id INTEGER,
  category VARCHAR(50),
  title VARCHAR(300) NOT NULL,
  description TEXT,
  url VARCHAR(500),
  source VARCHAR(100),
  publication_date TIMESTAMP,
  sentiment_score FLOAT,
  retrieved_at TIMESTAMP,
  FOREIGN KEY (country_id) REFERENCES countries(country_id)
);

-- TABEL 7: risk_scores
CREATE TABLE risk_scores (
  risk_id INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id INTEGER NOT NULL,
  weather_risk FLOAT,
  inflation_risk FLOAT,
  currency_risk FLOAT,
  news_risk FLOAT,
  composite_risk_score FLOAT,
  risk_category VARCHAR(20),
  calculated_at TIMESTAMP,
  FOREIGN KEY (country_id) REFERENCES countries(country_id)
);

-- TABEL 8: user_favorites
CREATE TABLE user_favorites (
  favorite_id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  country_id INTEGER NOT NULL,
  added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id),
  FOREIGN KEY (country_id) REFERENCES countries(country_id),
  UNIQUE(user_id, country_id)
);

-- TABEL 9: admin_articles
CREATE TABLE admin_articles (
  article_id INTEGER PRIMARY KEY AUTOINCREMENT,
  author_id INTEGER NOT NULL,
  title VARCHAR(300) NOT NULL,
  content TEXT NOT NULL,
  country_tags VARCHAR(500),
  published BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (author_id) REFERENCES users(user_id)
);

-- TABEL 10: currency_historical_data
CREATE TABLE currency_historical_data (
  currency_history_id INTEGER PRIMARY KEY AUTOINCREMENT,
  country_id INTEGER NOT NULL,
  currency_code VARCHAR(3),
  base_currency VARCHAR(3) DEFAULT 'USD',
  exchange_rate FLOAT NOT NULL,
  date_recorded DATE NOT NULL,
  volatility_30d FLOAT,
  FOREIGN KEY (country_id) REFERENCES countries(country_id),
  UNIQUE(country_id, date_recorded)
);
```

### B. Entity-Relationship Diagram (Text Representation)

```
┌──────────────┐
│    USERS     │
├──────────────┤
│ user_id (PK) │
│ username     │
│ email        │
│ role         │
│ created_at   │
└──────────────┘
       │
       │ 1:N
       │
  ┌────┴─────────────────────────────────┐
  │                                       │
┌─┴──────────────────┐            ┌─────┴──────────────┐
│  USER_FAVORITES    │            │  ADMIN_ARTICLES    │
├────────────────────┤            ├────────────────────┤
│ favorite_id (PK)   │            │ article_id (PK)    │
│ user_id (FK)       │            │ author_id (FK)     │
│ country_id (FK)    │            │ title              │
│ added_at           │            │ content            │
└────────┬───────────┘            │ country_tags       │
         │                        │ published          │
         │ N:1                    └────────────────────┘
         │
         └────────────────┬────────────────────┬──────────────────┐
                          │                    │                  │
                    ┌─────┴────────────┐  ┌────┴──────────┐  ┌────┴────────────┐
                    │   COUNTRIES      │  │ WEATHER_DATA │  │ PORTS            │
                    ├──────────────────┤  ├───────────────┤  ├──────────────────┤
                    │ country_id (PK)  │  │ weather_id(PK)│  │ port_id (PK)     │
                    │ name             │  │ country_id(FK)│  │ country_id (FK)  │
                    │ iso_code         │  │ temperature   │  │ port_name        │
                    │ region           │  │ humidity      │  │ latitude         │
                    │ center_lat/lng   │  │ rainfall      │  │ longitude        │
                    └────────┬─────────┘  │ wind_speed    │  │ facilities       │
                             │            │ weather_cond. │  │ throughput       │
                             │            │ retrieved_at  │  └──────────────────┘
                             │            └───────────────┘
                             │
          ┌──────────────────┼──────────────────┐
          │                  │                  │
    ┌─────┴─────────────┐  ┌─┴──────────────┐  ┌┴────────────────┐
    │ MACRO_ECONOMIC    │  │ NEWS_ARTICLES  │  │ RISK_SCORES    │
    │     _DATA         │  ├────────────────┤  ├────────────────┤
    ├───────────────────┤  │ article_id(PK) │  │ risk_id (PK)   │
    │ macro_id (PK)     │  │ country_id(FK) │  │ country_id(FK) │
    │ country_id (FK)   │  │ category       │  │ weather_risk   │
    │ year              │  │ title          │  │ inflation_risk │
    │ gdp_usd           │  │ description    │  │ currency_risk  │
    │ inflation_rate    │  │ sentiment_score│  │ news_risk      │
    │ population        │  │ retrieved_at   │  │ composite_risk │
    │ exports_usd       │  └────────────────┘  │ risk_category  │
    │ imports_usd       │                      │ calculated_at  │
    │ currency_code     │                      └────────────────┘
    │ exchange_rate     │
    └───────────────────┘
```

---

## 4. API ORCHESTRATION FLOW

### A. Request-Response Cycle untuk Country Dashboard

```
REQUEST: GET /api/country/Germany
├── Parameters: name=Germany, include_historical=true

BACKEND PROCESSING:
├── 1. Validate input (sanitize country name)
├── 2. Check cache (redis key: "country:Germany:dashboard")
│      if exists and age < 15 minutes:
│        return cached_response
│
├── 3. If cache miss, parallel fetch:
│      ┌─────────────────────────────────────────────┐
│      │ Parallel API Calls (concurrent)              │
│      ├─────────────────────────────────────────────┤
│      │ Thread 1: World Bank API                     │
│      │   GET /indicator/NY.GDP.MKTP.CD?country=DE  │
│      │   Timeout: 5s, Retry: 2                     │
│      │                                              │
│      │ Thread 2: REST Countries API                 │
│      │   GET /name/Germany                         │
│      │   Timeout: 3s                               │
│      │                                              │
│      │ Thread 3: Open-Meteo API                     │
│      │   GET /forecast?lat=51.5&lon=10.0           │
│      │   Timeout: 5s                               │
│      │                                              │
│      │ Thread 4: ExchangeRate API                   │
│      │   GET /latest?base=EUR                      │
│      │   Timeout: 3s                               │
│      │                                              │
│      │ Thread 5: GNews API                          │
│      │   GET /search?q=Germany supply chain         │
│      │   Timeout: 5s                               │
│      └─────────────────────────────────────────────┘
│
├── 4. Normalize responses:
│      {
│        "gdp": 3846410000000,
│        "inflation": 2.6,
│        "population": 83408654,
│        "currency": "EUR",
│        "exchange_rate_to_usd": 1.08,
│        "temperature": 15.2,
│        "weather_condition": "Cloudy",
│        "news_count": 42,
│        "latest_news": [...]
│      }
│
├── 5. Calculate risk scores:
│      invoke calculate_country_risk(Germany)
│      returns: {score: 28, category: "Low Risk", breakdown: {...}}
│
├── 6. Retrieve historical data (last 24 months):
│      GDP trend, Inflation trend, Exchange rate trend
│
├── 7. Format final response:
│      {
│        "country": "Germany",
│        "data": {...},
│        "risk": {...},
│        "historical": {...},
│        "timestamp": "2026-07-21T02:30:00Z"
│      }
│
├── 8. Cache response for 15 minutes
└── 9. Return to client

RESPONSE: 200 OK
{
  "country": "Germany",
  "iso_code": "DE",
  "region": "Europe",
  "current": {
    "gdp_usd": 3846410000000,
    "inflation_percent": 2.6,
    "population": 83408654,
    "currency": "EUR",
    "exchange_rate_to_usd": 1.08
  },
  "weather": {
    "temperature_c": 15.2,
    "condition": "Cloudy",
    "risk_level": "Low"
  },
  "risk_score": {
    "composite": 28,
    "category": "Low Risk",
    "components": {
      "weather": 15,
      "inflation": 25,
      "currency": 32,
      "news": 20
    }
  },
  "news": {
    "recent_count": 42,
    "articles": [...]
  },
  "historical": {
    "gdp_trend": [...],
    "inflation_trend": [...],
    "exchange_trend": [...]
  }
}
```

### B. Event-Driven Cache Invalidation

```
When data needs refresh (e.g., new news published):

Event: "news_article_published"
├── Check affected countries
├── Invalidate cache keys:
│   ├── country:{country_name}:dashboard
│   ├── country:{country_name}:risk
│   └── news:{category}
└── Next request fetches fresh data

Alternative: Scheduled refresh (every 15 minutes)
├── Cron job runs /admin/refresh-cache
├── Updates all 196 countries simultaneously
└── Prioritizes frequently viewed countries first
```

---

## 5. CACHING STRATEGY

### A. Multi-Level Caching

```
Level 1: Browser Cache (localStorage)
├── Expires: 5 minutes
├── Data: Country stats, port list
└── Auto-refresh on page revisit

Level 2: Application Cache (Python in-memory)
├── Expires: 15 minutes
├── Data: API responses, calculated risk scores
├── LRU eviction (keep 500 most recent)
└── Fallback to database if memory miss

Level 3: Database (Persistent)
├── Hourly snapshots of key metrics
├── Historical data retention: 24 months
└── Bulk queries for trends/comparisons
```

### B. Cache Keys Convention

```
country:{name}:dashboard       → Full country dashboard data
country:{name}:risk            → Risk score calculation
weather:{country_id}:current   → Current weather
ports:{country_id}:list        → Ports in country
news:{category}:recent         → Latest news by category
exchange:{currency_code}       → Exchange rates
admin:articles:published       → Published admin articles
```

---

## 6. ERROR HANDLING & RESILIENCE

### A. Timeout & Retry Strategy

```
API Call Execution Pattern:

for attempt in range(1, max_retries=3):
  try:
    response = requests.get(url, timeout=5)
    if response.status_code == 200:
      return parse(response)
  except (Timeout, ConnectionError) as e:
    wait_time = exponential_backoff(attempt, base=1)
    sleep(wait_time)
    continue
  except (ValueError, KeyError) as e:
    log_error(f"Parse error: {e}")
    return fallback_data()

# If all retries fail:
if cache_exists(cache_key):
  return stale_cached_data(warn_user=True)
else:
  return default_placeholder_data()
```

### B. Circuit Breaker Pattern (untuk API eksternal)

```
state = "CLOSED" (normal)

if consecutive_failures > threshold (3):
  state = "OPEN"
  reject_requests_immediately()
  schedule_health_check(after=60s)

if health_check_passes:
  state = "HALF_OPEN"
  allow_limited_requests()
  if all_requests_succeed:
    state = "CLOSED"
```

---

## 7. DATA FRESHNESS & UPDATE FREQUENCY

```
┌─────────────────────────────────────┬────────────────┬──────────┐
│ Data Type                           │ Update Freq    │ Cache TTL│
├─────────────────────────────────────┼────────────────┼──────────┤
│ Weather (current)                   │ Real-time      │ 30 min   │
│ Exchange Rates                      │ Real-time      │ 1 hour   │
│ News Articles                       │ Real-time      │ 15 min   │
│ GDP & Inflation (macro)             │ Monthly/Annual │ 24 hours │
│ Port Infrastructure                 │ Yearly         │ 7 days   │
│ Risk Scores (calculated)            │ Every 6 hours  │ 6 hours  │
│ Historical Trends                   │ Daily snapshot │ 24 hours │
│ User Favorites                      │ Immediate      │ 5 min    │
│ Admin Articles                      │ On-demand      │ 1 hour   │
└─────────────────────────────────────┴────────────────┴──────────┘
```

---

## KESIMPULAN FASE 1

**Arsitektur yang telah didefinisikan:**

1. ✅ **Data Flow**: Clear path dari eksternal API → Backend Processing → Frontend Visualization
2. ✅ **ERD & Schema**: 10 normalized tables mendukung 10 fitur utama
3. ✅ **API Orchestration**: Parallel requests, caching, error handling
4. ✅ **Scalability**: Multi-layer caching, circuit breakers, async operations
5. ✅ **Resilience**: Retry logic, fallback data, graceful degradation

**Ready untuk FASE 2: Risk Scoring Algorithm Design**
