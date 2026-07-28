# Backend Setup Guide - FastAPI Server

## Quick Start

### 1. Install Python Dependencies

```bash
pip install -r requirements.txt
```

### 2. Start FastAPI Server

```bash
python main.py
```

Or with uvicorn directly:

```bash
uvicorn main:app --reload --host 0.0.0.0 --port 8000
```

### 3. Access API Documentation

```
http://localhost:8000/docs          # Interactive Swagger UI
http://localhost:8000/redoc         # ReDoc documentation
```

---

## API Endpoints

### Health Check
```
GET /api/health
```

### Country Dashboard (All 6 APIs)
```
GET /api/country/{country_name}
```
Response: GDP, Inflation, Population, Currency, Risk Score, Weather

### Risk Breakdown
```
GET /api/risk/{country_name}
```
Response: Weather Risk, Inflation Risk, Currency Risk, News Risk, Composite Score

### Compare Countries
```
POST /api/compare?country_a=Germany&country_b=China
```
Response: Side-by-side comparison

### Search Ports
```
GET /api/ports/search?query=Singapore&country=Singapore
```
Response: List of matching ports with coordinates

---

## API Integrations (6 Total)

### 1. Open-Meteo (Weather)
- Endpoint: https://api.open-meteo.com/v1/forecast
- No API key needed
- Data: Temperature, Humidity, Wind Speed, Weather Condition

### 2. World Bank (Macroeconomic)
- Endpoint: https://api.worldbank.org/v2
- No API key needed
- Data: GDP, Inflation, Population, Region

### 3. REST Countries (Geographic)
- Endpoint: https://restcountries.com/v3.1
- No API key needed
- Data: Currencies, Languages, Borders, Area

### 4. ExchangeRate API (Currency)
- Endpoint: https://api.exchangerate-api.com/v4/latest
- No API key needed (for demo)
- Data: Real-time exchange rates

### 5. GNews (News Intelligence)
- Endpoint: https://gnewsapi.net/api/search
- Optional API key: Set in .env as GNEWS_API_KEY
- Data: Supply chain news articles with sentiment

### 6. World Port Index (Local)
- Source: public/ports-complete.json
- Data: 380+ ports with coordinates and types

---

## Environment Variables

Create `.env` file:

```
GNEWS_API_KEY=your_api_key_here
DATABASE_URL=postgresql://user:password@localhost/supply_chain
REDIS_URL=redis://localhost:6379
DEBUG=True
```

---

## Testing Endpoints

### Using cURL

```bash
# Health check
curl http://localhost:8000/api/health

# Get country data
curl http://localhost:8000/api/country/Germany

# Get risk breakdown
curl http://localhost:8000/api/risk/China

# Search ports
curl "http://localhost:8000/api/ports/search?query=Singapore"
```

### Using Python

```python
import requests

# Get country data
response = requests.get("http://localhost:8000/api/country/Germany")
print(response.json())

# Get risk breakdown
response = requests.get("http://localhost:8000/api/risk/China")
print(response.json())
```

---

## Frontend Integration

### Connect Frontend to Backend

Update `dashboard.html` API client:

```javascript
// Replace localhost:8002 with backend URL
const API_URL = "http://localhost:8000/api";

class APIClient {
    static async fetchCountryData(countryName) {
        const response = await fetch(`${API_URL}/country/${countryName}`);
        return await response.json();
    }
    
    static async fetchRisk(countryName) {
        const response = await fetch(`${API_URL}/risk/${countryName}`);
        return await response.json();
    }
    
    static async searchPorts(query) {
        const response = await fetch(`${API_URL}/ports/search?query=${query}`);
        return await response.json();
    }
}
```

---

## Performance Optimization

### Enable Caching (with Redis)

```python
from redis import Redis
import json

redis_client = Redis(host='localhost', port=6379, db=0)

# Cache country data for 15 minutes
cache_key = f"country:{country_name}"
cached_data = redis_client.get(cache_key)

if cached_data:
    return json.loads(cached_data)

# Fetch fresh data
data = calculate_country_risk(country_name)
redis_client.setex(cache_key, 900, json.dumps(data))
```

### Async Operations

All API calls use asyncio for parallel execution:

```python
import asyncio

async def get_all_data(country):
    # Execute 6 API calls in parallel
    results = await asyncio.gather(
        get_weather_data(country),
        get_macroeconomic_data(country),
        get_geographic_data(country),
        get_exchange_rates(country),
        get_supply_chain_news(country),
        search_ports(country)
    )
    return results
```

---

## Troubleshooting

### Issue: "Connection refused"
- Ensure server is running: `python main.py`
- Check port 8000 is not in use
- Try different port: `--port 8001`

### Issue: "ModuleNotFoundError"
- Install dependencies: `pip install -r requirements.txt`
- Use virtual environment: `python -m venv venv`

### Issue: "API timeout"
- Check internet connection
- External APIs might be slow
- Increase timeout in requests: `timeout=30`

### Issue: "CORS error in frontend"
- CORS is enabled in FastAPI setup
- If still issues, check browser console for details

---

## Next Steps

1. ✅ **Deploy FastAPI** (DONE)
2. **Set up PostgreSQL** - Database integration
3. **Connect Frontend** - Replace mock data with real APIs
4. **Redis Caching** - Performance optimization
5. **User Authentication** - JWT tokens and login

---

## Production Deployment

### Using Docker

```dockerfile
FROM python:3.11-slim

WORKDIR /app

COPY requirements.txt .
RUN pip install -r requirements.txt

COPY . .

EXPOSE 8000

CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8000"]
```

### Using Gunicorn

```bash
pip install gunicorn
gunicorn -w 4 -k uvicorn.workers.UvicornWorker main:app --bind 0.0.0.0:8000
```

---

## API Rate Limits

- World Bank: 150 requests per minute
- Open-Meteo: 10,000 requests per day (free)
- REST Countries: Unlimited
- ExchangeRate API: 1,500 per month (free)
- GNews: 100 requests per day (free)

---

## Monitoring

Add logging:

```python
import logging

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

logger.info(f"Processing country: {country_name}")
logger.error(f"API error: {error_message}")
```

Monitor with:
```bash
# Watch server logs
tail -f server.log

# Check API response times
curl -w "@curl-format.txt" http://localhost:8000/api/health
```

---

**Backend is now ready for production use!** 🚀
