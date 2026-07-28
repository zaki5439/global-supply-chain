"""
Simple File-Based Cache Server (No Redis required)
Global Supply Chain Risk Intelligence Platform
Task #4: Redis Caching Layer Alternative Implementation
"""

import json
import os
import time
from datetime import datetime, timedelta
from pathlib import Path

# Simple Flask-like alternative using built-in http.server
from http.server import HTTPServer, BaseHTTPRequestHandler
import urllib.parse

# ============================================
# CACHE STORAGE
# ============================================

CACHE_DIR = Path("cache_data")
CACHE_DIR.mkdir(exist_ok=True)

class SimpleCache:
    """File-based cache system (Alternative to Redis)"""
    
    def __init__(self):
        self.cache_dir = CACHE_DIR
        self.stats = {"hits": 0, "misses": 0, "sets": 0}
    
    def get(self, key: str):
        """Get value from cache"""
        cache_file = self.cache_dir / f"{key}.json"
        
        if not cache_file.exists():
            self.stats["misses"] += 1
            return None
        
        try:
            with open(cache_file, 'r') as f:
                data = json.load(f)
            
            # Check if expired
            if "ttl" in data and "created_at" in data:
                created = datetime.fromisoformat(data["created_at"])
                if datetime.now() > created + timedelta(seconds=data["ttl"]):
                    cache_file.unlink()  # Delete expired file
                    self.stats["misses"] += 1
                    return None
            
            self.stats["hits"] += 1
            return data.get("value")
        except Exception as e:
            print(f"Cache read error: {e}")
            self.stats["misses"] += 1
            return None
    
    def set(self, key: str, value, ttl: int = 86400):
        """Set value in cache"""
        cache_file = self.cache_dir / f"{key}.json"
        
        try:
            data = {
                "value": value,
                "ttl": ttl,
                "created_at": datetime.now().isoformat()
            }
            with open(cache_file, 'w') as f:
                json.dump(data, f)
            self.stats["sets"] += 1
            return True
        except Exception as e:
            print(f"Cache write error: {e}")
            return False
    
    def get_stats(self):
        """Get cache statistics"""
        total = self.stats["hits"] + self.stats["misses"]
        hit_rate = (self.stats["hits"] / total * 100) if total > 0 else 0
        
        return {
            "hits": self.stats["hits"],
            "misses": self.stats["misses"],
            "sets": self.stats["sets"],
            "total_requests": total,
            "hit_rate": f"{hit_rate:.2f}%",
            "cache_type": "FILE-BASED (No Redis)"
        }
    
    def clear_all(self):
        """Clear all cache"""
        for f in self.cache_dir.glob("*.json"):
            f.unlink()

cache = SimpleCache()

# ============================================
# MOCK DATA (Simulated API responses)
# ============================================

MOCK_DATA = {
    "Germany": {
        "name": "Germany",
        "gdp": 4080000000000,
        "inflation": 3.8,
        "population": 83369843,
        "currency": "EUR",
        "region": "Europe",
        "risk_score": 45.2
    },
    "Singapore": {
        "name": "Singapore",
        "gdp": 525000000000,
        "inflation": 2.1,
        "population": 5917600,
        "currency": "SGD",
        "region": "Asia",
        "risk_score": 32.5
    },
    "China": {
        "name": "China",
        "gdp": 17736000000000,
        "inflation": 2.0,
        "population": 1425887337,
        "currency": "CNY",
        "region": "Asia",
        "risk_score": 68.3
    }
}

# ============================================
# HTTP REQUEST HANDLER
# ============================================

class CacheServerHandler(BaseHTTPRequestHandler):
    """Simple HTTP server handler for cache API"""
    
    def do_GET(self):
        """Handle GET requests"""
        path = self.path.split('?')[0]
        
        # Health check
        if path == "/api/health":
            self.send_response(200)
            self.send_header('Content-type', 'application/json')
            self.send_header('Access-Control-Allow-Origin', '*')
            self.end_headers()
            response = {
                "status": "healthy",
                "timestamp": datetime.now().isoformat(),
                "version": "1.0.0",
                "cache_type": "FILE-BASED"
            }
            self.wfile.write(json.dumps(response).encode())
        
        # Cache statistics
        elif path == "/api/cache/stats":
            self.send_response(200)
            self.send_header('Content-type', 'application/json')
            self.send_header('Access-Control-Allow-Origin', '*')
            self.end_headers()
            stats = cache.get_stats()
            self.wfile.write(json.dumps(stats).encode())
        
        # Country data (with caching)
        elif path.startswith("/api/country/"):
            country = path.split("/")[-1]
            
            # Try cache first
            cached = cache.get(f"country:{country}")
            if cached:
                self.send_response(200)
                self.send_header('Content-type', 'application/json')
                self.send_header('X-Cache', 'HIT')
                self.send_header('Access-Control-Allow-Origin', '*')
                self.end_headers()
                self.wfile.write(json.dumps(cached).encode())
                return
            
            # Get from mock data
            if country in MOCK_DATA:
                data = MOCK_DATA[country]
                # Cache for 24 hours
                cache.set(f"country:{country}", data, 86400)
                
                self.send_response(200)
                self.send_header('Content-type', 'application/json')
                self.send_header('X-Cache', 'MISS')
                self.send_header('Access-Control-Allow-Origin', '*')
                self.end_headers()
                self.wfile.write(json.dumps(data).encode())
            else:
                self.send_response(404)
                self.send_header('Content-type', 'application/json')
                self.send_header('Access-Control-Allow-Origin', '*')
                self.end_headers()
                self.wfile.write(json.dumps({"error": "Country not found"}).encode())
        
        # Weather (with caching)
        elif path.startswith("/api/weather/"):
            country = path.split("/")[-1]
            
            cached = cache.get(f"weather:{country}")
            if cached:
                self.send_response(200)
                self.send_header('Content-type', 'application/json')
                self.send_header('X-Cache', 'HIT')
                self.send_header('Access-Control-Allow-Origin', '*')
                self.end_headers()
                self.wfile.write(json.dumps(cached).encode())
                return
            
            weather_data = {
                "country": country,
                "temperature": 15 + (hash(country) % 10),
                "humidity": 60,
                "condition": "Partly Cloudy"
            }
            cache.set(f"weather:{country}", weather_data, 3600)  # 1 hour
            
            self.send_response(200)
            self.send_header('Content-type', 'application/json')
            self.send_header('X-Cache', 'MISS')
            self.send_header('Access-Control-Allow-Origin', '*')
            self.end_headers()
            self.wfile.write(json.dumps(weather_data).encode())
        
        # Default 404
        else:
            self.send_response(404)
            self.send_header('Content-type', 'application/json')
            self.send_header('Access-Control-Allow-Origin', '*')
            self.end_headers()
            self.wfile.write(json.dumps({"error": "Not found"}).encode())
    
    def do_POST(self):
        """Handle POST requests"""
        path = self.path.split('?')[0]
        
        # Clear cache
        if path == "/api/cache/clear":
            cache.clear_all()
            self.send_response(200)
            self.send_header('Content-type', 'application/json')
            self.send_header('Access-Control-Allow-Origin', '*')
            self.end_headers()
            response = {"status": "cleared", "message": "Cache cleared"}
            self.wfile.write(json.dumps(response).encode())
        else:
            self.send_response(404)
            self.send_header('Access-Control-Allow-Origin', '*')
            self.end_headers()
    
    def do_OPTIONS(self):
        """Handle CORS preflight requests"""
        self.send_response(200)
        self.send_header('Access-Control-Allow-Origin', '*')
        self.send_header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
        self.send_header('Access-Control-Allow-Headers', 'Content-Type')
        self.end_headers()
    
    def log_message(self, format, *args):
        """Suppress default logging"""
        return

# ============================================
# START SERVER
# ============================================

if __name__ == "__main__":
    port = 8000
    server_address = ("", port)
    httpd = HTTPServer(server_address, CacheServerHandler)
    
    print("\n" + "="*70)
    print("  Simple Cache Server (No Redis Required)")
    print("  Global Supply Chain Risk Intelligence Platform")
    print("="*70)
    print(f"\n✅ Server running on http://localhost:{port}")
    print(f"📁 Cache directory: {CACHE_DIR.absolute()}")
    print("\n📍 Endpoints:")
    print("   GET  /api/health              - Health check")
    print("   GET  /api/cache/stats         - Cache statistics")
    print("   GET  /api/country/{country}   - Get country data (cached)")
    print("   GET  /api/weather/{country}   - Get weather data (cached)")
    print("   POST /api/cache/clear         - Clear all cache")
    print("\n🚀 Test:")
    print("   curl http://localhost:8000/api/health")
    print("   curl http://localhost:8000/api/country/Germany")
    print("   curl http://localhost:8000/api/cache/stats")
    print("\n⏹️  Press Ctrl+C to stop\n")
    
    try:
        httpd.serve_forever()
    except KeyboardInterrupt:
        print("\n\n✓ Server stopped")
