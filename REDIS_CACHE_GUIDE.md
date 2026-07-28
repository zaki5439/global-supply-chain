# Redis Caching Layer - Integration Guide

## Overview
Multi-layer caching strategy for Global Supply Chain Risk Intelligence Platform optimizes performance with graceful fallback.

**Caching Layers:**
- **Browser**: 5 minutes (client-side HTTP caching)
- **Application**: 15 minutes (in-memory app cache)
- **Redis**: 24 hours (distributed cache)

---

## Architecture

### Cache Flow
```
Request
  ↓
Browser Cache (5 min)
  ↓ (miss)
App Memory (15 min)
  ↓ (miss)
Redis (24h)
  ↓ (miss)
External APIs
  ↓
Store in Redis
  ↓
Response to Client
```

### TTL Policy
| Data Type | TTL | Reason |
|-----------|-----|--------|
| Static Geographic Data | 7 days | Borders, languages, timezones rarely change |
| Exchange Rates | 24 hours | Daily market updates sufficient |
| Macroeconomic Data | 24 hours | Published monthly/quarterly |
| Weather Data | 1 hour | Frequently updated |
| Risk Scores | 15 min | Calculated from multiple sources |
| News Articles | 5 min | Most volatile data |

### Cache Prefixes
```
country:germany          → Country metadata
risk:germany             → Risk scores
weather:germany          → Weather data
macro:germany            → Macroeconomic indicators
exchange:eur             → Exchange rates
news:singapore:logistics → News articles
ports:singapore          → Port data
geo:germany              → Geographic boundaries
compare:de-sg            → Comparison results
stats:platform           → Platform statistics
```

---

## Installation & Setup

### Prerequisites
- Python 3.8+
- Redis server (localhost:6379)
- FastAPI backend running

### Install Redis

#### Windows (WSL/Docker Recommended)
```bash
# Using Docker (recommended)
docker run -d -p 6379:6379 redis:latest

# Or use WSL
wsl
sudo apt-get install redis-server
sudo service redis-server start
```

#### macOS
```bash
brew install redis
brew services start redis
```

#### Linux
```bash
sudo apt-get install redis-server
sudo systemctl start redis-server
sudo systemctl enable redis-server
```

### Verify Redis Installation
```bash
redis-cli ping
# Expected output: PONG
```

### Environment Configuration
Add to `.env`:
```
REDIS_HOST=localhost
REDIS_PORT=6379
REDIS_DB=0
REDIS_PASSWORD=  # Leave empty if no password
```

---

## API Endpoints

### Cache Statistics
**GET** `/api/cache/stats`

Returns cache performance metrics:
```json
{
  "statistics": {
    "hits": 245,
    "misses": 87,
    "sets": 156,
    "deletes": 12,
    "total_requests": 332,
    "hit_rate": "73.80%",
    "available": true
  },
  "redis": {
    "status": "connected",
    "used_memory": "2.5M",
    "used_memory_peak": "5.2M",
    "total_connections": 8,
    "total_commands": 1024,
    "uptime_seconds": 86400
  },
  "config": {
    "ttl_browser": 300,
    "ttl_application": 900,
    "ttl_redis": 86400
  },
  "timestamp": "2025-01-15T10:30:45.123Z"
}
```

### Clear Cache by Pattern
**POST** `/api/cache/clear`

Query Parameters:
- `pattern` (optional): Glob pattern to match keys

Examples:
```bash
# Clear all cache
curl -X POST http://localhost:8000/api/cache/clear

# Clear country data
curl -X POST http://localhost:8000/api/cache/clear?pattern=country:*

# Clear specific country
curl -X POST http://localhost:8000/api/cache/clear?pattern=country:germany

# Clear weather data
curl -X POST http://localhost:8000/api/cache/clear?pattern=weather:*
```

Response:
```json
{
  "status": "cleared",
  "pattern": "country:*",
  "deleted": 18,
  "timestamp": "2025-01-15T10:35:12.456Z"
}
```

### Invalidate Specific Entry
**POST** `/api/cache/invalidate/{entity_type}/{entity_id}`

Entity Types: `country`, `risk`, `weather`, `macro`, `exchange`, `news`, `ports`, `geographic`

Examples:
```bash
# Invalidate Germany country data
curl -X POST http://localhost:8000/api/cache/invalidate/country/Germany

# Invalidate Singapore weather
curl -X POST http://localhost:8000/api/cache/invalidate/weather/Singapore

# Invalidate EUR exchange rates
curl -X POST http://localhost:8000/api/cache/invalidate/exchange/EUR
```

Response:
```json
{
  "status": "invalidated",
  "entity_type": "country",
  "entity_id": "Germany",
  "timestamp": "2025-01-15T10:40:22.789Z"
}
```

---

## Integration with FastAPI

### Automatic Caching
All major endpoints automatically cache responses:

```python
@app.get("/api/country/{country_name}")
async def get_country_dashboard(country_name: str):
    # Automatically tries cache first
    # Falls back to API if cache miss
    # Stores result in Redis
```

### Cache Hit Indicators
Monitor cache performance in browser console:
```
✓ Cache HIT: country:germany (served from Redis in 2ms)
⟳ Cache MISS: weather:singapore (fetched from API in 342ms)
✓ Cache SET: macro:india (stored for 24h)
```

### Manual Cache Operations
```python
from cache_manager import cache_manager, CACHE_CONFIG

# Get from cache
data = cache_manager.get("country:germany")

# Set in cache (24h TTL)
cache_manager.set("country:germany", data, CACHE_CONFIG['ttl']['redis'])

# Delete entry
cache_manager.delete("country:germany")

# Clear by pattern
cache_manager.clear_pattern("weather:*")

# Get or set (if not cached)
data = cache_manager.get_or_set(
    "country:singapore",
    lambda: fetch_from_api("Singapore"),
    CACHE_CONFIG['ttl']['redis']
)
```

---

## Frontend Integration

### Cache Headers
All responses include cache control headers:
```
Cache-Control: public, max-age=900
X-Cache: HIT (from Redis)
X-Cache-Key: country:germany
X-Cache-TTL: 86400
```

### API Client Caching
Update `public/js/api-client.js`:
```javascript
const apiClient = {
    // Browser cache (IndexedDB) - 5 minutes
    cacheTime: 5 * 60 * 1000,
    
    async get(endpoint) {
        // Check browser cache first
        const cached = await this.getFromCache(endpoint);
        if (cached && !this.isCacheExpired(cached)) {
            console.log('✓ Served from browser cache');
            return cached.data;
        }
        
        // Fetch from backend (which uses Redis)
        const response = await fetch(endpoint);
        const data = await response.json();
        
        // Store in browser cache
        await this.saveToCache(endpoint, data);
        
        return data;
    }
};
```

---

## Monitoring & Debugging

### Redis CLI Commands
```bash
# Connect to Redis
redis-cli

# Check all keys
KEYS *

# Get key info
INFO key_name

# Get memory usage
INFO memory

# Monitor real-time commands
MONITOR

# Get Redis statistics
INFO stats

# Clear all cache (dangerous!)
FLUSHALL

# Exit
EXIT
```

### Cache Statistics Dashboard
Access at: `http://localhost:8000/api/cache/stats`

Monitor:
- **Hit Rate**: Target > 70%
- **Memory Usage**: Monitor for growth
- **Total Requests**: Performance baseline

### Performance Targets
| Metric | Target | Threshold |
|--------|--------|-----------|
| Cache Hit Rate | > 70% | > 90% excellent |
| Response Time (cached) | < 50ms | < 100ms acceptable |
| Response Time (uncached) | < 1000ms | > 2000ms investigate |
| Memory Usage | < 100MB | > 500MB investigate |

---

## Cache Invalidation Strategy

### Automatic Invalidation
Cache entries expire automatically based on TTL:
- Geographic data: 7 days
- Exchange rates: 24 hours
- Weather data: 1 hour

### Manual Invalidation
Invalidate when data changes:
```bash
# After updating country data
curl -X POST http://localhost:8000/api/cache/invalidate/country/Germany

# Before major system update
curl -X POST http://localhost:8000/api/cache/clear

# Clear specific service
curl -X POST http://localhost:8000/api/cache/clear?pattern=weather:*
```

### Smart Invalidation
Invalidate related entries:
```python
# Invalidates all entries matching pattern
cache_manager.invalidate_related('country', 'Germany')
# Removes:
#   - country:germany
#   - risk:germany
#   - weather:germany
#   - compare:*germany*
```

---

## Troubleshooting

### Redis Connection Failed
```
✗ Redis connection failed: Connection refused
```
**Solution:**
```bash
# Check if Redis is running
redis-cli ping

# Start Redis
redis-server

# Verify .env configuration
REDIS_HOST=localhost
REDIS_PORT=6379
```

### High Memory Usage
```bash
# Check memory
redis-cli INFO memory

# Clear cache
curl -X POST http://localhost:8000/api/cache/clear

# Reduce TTL values in cache_manager.py
```

### Low Cache Hit Rate
- Verify Redis is connected: `curl http://localhost:8000/api/cache/stats`
- Check TTL values match usage patterns
- Increase TTL for frequently accessed data

### Cache Invalidation Not Working
```bash
# Verify pattern syntax
redis-cli KEYS "country:*"

# Check if keys exist
redis-cli DBSIZE

# View recent commands
redis-cli MONITOR
```

---

## Performance Tuning

### Optimize TTL
```python
# Adjust CACHE_CONFIG in cache_manager.py
CACHE_CONFIG = {
    'ttl': {
        'browser': 5 * 60,          # Increase for stable data
        'application': 15 * 60,     # Adjust based on usage
        'redis': 24 * 60 * 60,      # Match data update frequency
        'short': 5 * 60,            # For rapidly changing data
        'medium': 60 * 60,          # For moderately changing data
        'long': 7 * 24 * 60 * 60    # For static data
    }
}
```

### Enable Cache Compression
```python
# For large responses, enable compression
import gzip
json_data = gzip.compress(json.dumps(data).encode())
cache_manager.set(key, json_data)
```

### Batch Operations
```python
# Cache multiple countries at once
countries = ['Germany', 'Singapore', 'China']
for country in countries:
    data = fetch_country(country)
    cache_manager.set(f'country:{country.lower()}', data)
```

---

## Security Considerations

### Redis Security
```bash
# Set password in Redis config
requirepass your_secure_password

# Use in .env
REDIS_PASSWORD=your_secure_password
```

### Cache Data Privacy
- ✓ Sensitive data (API keys) not cached
- ✓ User credentials excluded from cache
- ✓ PII subject to data retention policy

### Cache Invalidation on Data Change
```python
# Auto-invalidate on user action
@app.post("/api/favorites/{country}")
async def add_favorite(country: str):
    # Add to database
    db.add_favorite(country)
    
    # Invalidate related caches
    cache_manager.invalidate_related('country', country)
    cache_manager.invalidate_related('comparison', country)
    
    return {"status": "added"}
```

---

## Testing Cache

### Load Testing
```bash
# Install Apache Bench
sudo apt-get install apache2-utils

# Test without cache
ab -n 100 -c 10 http://localhost:8000/api/country/Germany

# Test with cache (should be faster)
ab -n 100 -c 10 http://localhost:8000/api/country/Germany
```

### Cache Hit Rate Test
```python
# Run from tests directory
python -m pytest tests/test_cache.py -v

# Expected output:
# test_cache_hit_rate[70%] PASSED
# test_cache_performance[<50ms] PASSED
# test_cache_invalidation PASSED
```

---

## Production Deployment

### Redis Cluster (High Availability)
```yaml
# docker-compose.yml
version: '3'
services:
  redis:
    image: redis:latest
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data
    command: redis-server --appendonly yes
    
  redis-sentinel:
    image: redis:latest
    ports:
      - "26379:26379"
    command: redis-sentinel /etc/redis/sentinel.conf
    
volumes:
  redis_data:
```

### Persistence
```python
# Enable AOF (Append-Only File)
# In redis.conf:
# appendonly yes
# appendfsync everysec

# Enable RDB snapshots
# save 900 1    # after 900 sec with 1 change
# save 300 10   # after 300 sec with 10 changes
# save 60 10000 # after 60 sec with 10000 changes
```

### Monitoring
```bash
# Install Redis exporter for Prometheus
docker run -d -p 9121:9121 oliver006/redis_exporter

# Configure Grafana dashboard
# Import: https://grafana.com/grafana/dashboards/763
```

---

## Next Steps
1. **Task #5**: User Authentication & JWT Tokens
2. **Integration**: Full end-to-end testing
3. **Production**: Docker deployment with Redis cluster
4. **Monitoring**: Set up Prometheus + Grafana dashboards

---

## Support
- Redis Documentation: https://redis.io/docs/
- FastAPI Caching: https://fastapi.tiangolo.com/advanced/middleware/
- Performance Tuning: https://redis.io/topics/performance-tuning
