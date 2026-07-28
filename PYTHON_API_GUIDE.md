# Python Flask API Integration Guide

## Overview

This guide explains how to run the Global Supply Chain Risk Intelligence Platform using the Python Flask backend with full API integration.

## Architecture

```
┌─────────────────────────────────────────┐
│      Frontend (HTML/JS)                 │
│  (dashboard.html + api-integration.js)   │
└──────────────────┬──────────────────────┘
                   │ HTTP/REST API
                   ↓
┌─────────────────────────────────────────┐
│      Flask API Server (app.py)          │
│  - REST endpoints                        │
│  - Database models                       │
│  - Risk calculation engine               │
└──────────────────┬──────────────────────┘
                   │
                   ↓
┌─────────────────────────────────────────┐
│      External APIs                       │
│  - Open-Meteo (Weather)                  │
│  - World Bank (Economic data)            │
│  - REST Countries (Geographic)           │
│  - ExchangeRate (Currency)               │
│  - GNews (News)                          │
└─────────────────────────────────────────┘
```

## Prerequisites

- Python 3.8 or higher
- pip (Python package manager)
- Modern web browser

## Quick Start (Windows)

### Option 1: Using Startup Script (Recommended)

1. Double-click `start.bat`
2. Wait for dependencies to install
3. Open browser to `http://localhost:5000`

### Option 2: Manual Setup

```bash
# 1. Create virtual environment
python -m venv venv

# 2. Activate virtual environment
venv\Scripts\activate

# 3. Install dependencies
pip install -r requirements.txt

# 4. Initialize database
python -c "from app import app, db; app.app_context().push(); db.create_all()"

# 5. Start server
python app.py
```

## Quick Start (Linux/Mac)

```bash
# 1. Create virtual environment
python3 -m venv venv

# 2. Activate virtual environment
source venv/bin/activate

# 3. Install dependencies
pip install -r requirements.txt

# 4. Initialize database
python -c "from app import app, db; app.app_context().push(); db.create_all()"

# 5. Start server
python app.py
```

## API Endpoints

### Country Dashboard

**GET** `/api/country/<country_name>`

Get comprehensive dashboard data for a country.

**Example:**
```bash
curl http://localhost:5000/api/country/Germany
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "country_name": "Germany",
    "iso_code": "DE",
    "capital": "Berlin",
    "population": 83200000,
    "currency": {
      "code": "EUR",
      "name": "Euro",
      "exchange_rate_usd": 0.92,
      "volatility_30d": 0.0184,
      "trend": "stable"
    },
    "economic": {
      "gdp_usd": 4260000000000,
      "inflation_rate": 2.1,
      "population": 83200000,
      "exports_usd": 1650000000000,
      "imports_usd": 1570000000000
    },
    "weather": {
      "temperature": 15.5,
      "precipitation": 0.0,
      "wind_speed": 12.3,
      "condition": "clear",
      "risk_score": 5.0
    },
    "news": [...]
  }
}
```

### Risk Calculation

**GET** `/api/risk/<country_name>`

Calculate risk score for a country.

**Example:**
```bash
curl http://localhost:5000/api/risk/Germany
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "country_name": "Germany",
    "risk_score": 22,
    "risk_category": "Low Risk"
  }
}
```

### Country Comparison

**POST** `/api/compare`

Compare two countries across 5 metrics.

**Example:**
```bash
curl -X POST http://localhost:5000/api/compare \
  -H "Content-Type: application/json" \
  -d '{"country_a": "Germany", "country_b": "China"}'
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "country_a": {
      "name": "Germany",
      "gdp_usd": 4260000000000,
      "inflation_rate": 2.1,
      "risk_score": 22,
      "risk_category": "Low Risk",
      "temperature": 15,
      "currency_code": "EUR",
      "exchange_rate_usd": 0.92
    },
    "country_b": {
      "name": "China",
      "gdp_usd": 17960000000000,
      "inflation_rate": 2.5,
      "risk_score": 47,
      "risk_category": "Medium Risk",
      "temperature": 22,
      "currency_code": "CNY",
      "exchange_rate_usd": 0.1389
    },
    "comparison_metrics": {
      "gdp_winner": "China",
      "lower_inflation": "Germany",
      "lower_risk": "Germany",
      "warmer": "China"
    }
  }
}
```

### Favorites

**GET** `/api/favorites`
- Get all favorite countries

**POST** `/api/favorites`
- Add country to favorites
- Body: `{"country_name": "Germany"}`

**DELETE** `/api/favorites/<id>`
- Remove country from favorites

### Ports

**GET** `/api/ports?search=<term>&country=<country>`
- Get all ports or filter by search/country

**POST** `/api/ports`
- Add new port (admin only)
- Body: `{"name": "Port Name", "country": "Country", "latitude": 0, "longitude": 0}`

**DELETE** `/api/ports/<id>`
- Delete port (admin only)

### Historical Data

**GET** `/api/historical/<country_name>?metric_type=<type>&days=<days>`
- Get historical data for charts
- metric_type: gdp, inflation, currency, risk_score, or all
- days: number of days to look back (default: 30)

### Admin Endpoints

**GET** `/api/admin/users`
- Get all users

**POST** `/api/admin/users`
- Create new user
- Body: `{"username": "user", "email": "user@example.com", "role": "viewer"}`

**GET** `/api/admin/articles`
- Get all articles

**POST** `/api/admin/articles`
- Create new article
- Body: `{"title": "Title", "content": "Content", "category": "Category"}`

**PUT** `/api/admin/articles/<id>`
- Update article

## Database Schema

### Users
- id, username, email, role, created_at, last_login

### Favorite Countries
- id, user_id, country_name, created_at

### Historical Data
- id, country_name, metric_type, value, recorded_at

### Ports
- id, name, country, latitude, longitude, port_type, updated_at

### Articles
- id, title, content, category, author_id, published, created_at, published_at

## Features Implemented

### ✅ All 10 Features

1. **Global Country Dashboard** - Select country, view comprehensive stats
2. **Risk Scoring Engine** - Dynamic risk calculation (Weather + Inflation + Currency + News)
3. **Global Weather Monitoring** - Interactive map with weather overlays
4. **Currency Impact Dashboard** - Real-time exchange rates with trends
5. **News Intelligence Module** - GNews API integration for logistics/trade/economy news
6. **Port Location Dashboard** - Search and filter ports on map
7. **Data Visualization Dashboard** - Chart.js graphs for GDP, Inflation, Currency, Risk Score
8. **Country Comparison Engine** - Side-by-side comparison of 5 metrics
9. **Favorite Monitoring List** - Save and monitor priority countries
10. **Admin Dashboard** - User management, Port dataset, Article management

## Real-Time Updates

The platform supports automatic data refresh:

- **Polling**: Auto-refresh every 5 minutes (configurable)
- **Caching**: Browser-side caching reduces API calls
- **Historical Data**: Automatic storage of data points for trend analysis

To enable auto-refresh, uncomment in `api-integration.js`:
```javascript
enableAutoRefresh(5); // Refresh every 5 minutes
```

## Configuration

### API Key Configuration

For GNews API, edit `supply_chain_risk_platform.py`:
```python
self.gnews_client = GNewsClient(gnews_api_key="YOUR_API_KEY")
```

### Database Configuration

Edit `app.py` to change database:
```python
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///supply_chain.db'
# Or use PostgreSQL:
# app.config['SQLALCHEMY_DATABASE_URI'] = 'postgresql://user:password@localhost/dbname'
```

### Server Configuration

Edit `app.py` to change server settings:
```python
app.run(debug=True, host='0.0.0.0', port=5000)
```

## Troubleshooting

### Server won't start
- Check Python version (3.8+ required)
- Ensure virtual environment is activated
- Verify all dependencies installed: `pip list`

### API returns errors
- Check Flask logs for error messages
- Verify external APIs are accessible
- Check internet connection for external API calls

### Database errors
- Delete `supply_chain.db` and restart server to recreate
- Check file permissions for database directory

### Frontend not loading data
- Check browser console (F12) for errors
- Verify API server is running on port 5000
- Check CORS configuration in `app.py`

## Development

### Adding New API Endpoints

1. Add route in `app.py`:
```python
@app.route('/api/your-endpoint', methods=['GET'])
def your_function():
    # Your logic here
    return jsonify({'status': 'success', 'data': result})
```

2. Add API client method in `api-integration.js`:
```javascript
static async yourMethod() {
    return this.request('/your-endpoint');
}
```

### Adding New Database Models

1. Define model in `app.py`:
```python
class YourModel(db.Model):
    __tablename__ = 'your_table'
    id = db.Column(db.Integer, primary_key=True)
    # Add fields
```

2. Create migration:
```python
with app.app_context():
    db.create_all()
```

## Production Deployment

### Using Gunicorn (Linux)

```bash
pip install gunicorn
gunicorn -w 4 -b 0.0.0.0:5000 app:app
```

### Using Waitress (Windows)

```bash
pip install waitress
waitress-serve --port=5000 app:app
```

### Environment Variables

Create `.env` file:
```
FLASK_ENV=production
FLASK_DEBUG=0
DATABASE_URL=postgresql://...
GNEWS_API_KEY=your_key
```

Load in `app.py`:
```python
from dotenv import load_dotenv
load_dotenv()
```

## Security Notes

- **API Keys**: Never commit API keys to version control
- **CORS**: Configure allowed origins in production
- **Authentication**: Implement user authentication for admin endpoints
- **Rate Limiting**: Add rate limiting for API endpoints
- **SQL Injection**: Use SQLAlchemy ORM (already implemented)

## Performance Optimization

- **Database Indexing**: Add indexes to frequently queried columns
- **Caching**: Implement Redis for production caching
- **CDN**: Serve static files via CDN
- **Compression**: Enable gzip compression
- **Connection Pooling**: Configure database connection pool

## Support

For issues or questions:
1. Check browser console (F12)
2. Check Flask server logs
3. Verify API endpoints with curl/Postman
4. Review this documentation

## License

MIT License - See LICENSE file for details

## Version

- **Version**: 2.0
- **Status**: Production Ready
- **Last Updated**: 2026-07-20
