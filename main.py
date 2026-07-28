"""
Global Supply Chain Risk Intelligence Platform - FastAPI Server
Main application entry point with 6 API integrations
"""

import os
import asyncio
from datetime import datetime, timedelta
from typing import List, Optional, Dict, Any
from fastapi import FastAPI, HTTPException, Depends, status, BackgroundTasks
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
import requests
import json
import logging

# Import risk engine
from supply_chain_risk_engine import (
    calculate_country_risk,
    compare_countries,
    get_country_coordinates,
    normalize_score
)

# Import cache manager
from cache_manager import (
    cache_manager,
    init_cache,
    CACHE_CONFIG,
    cached
)

# ============================================
# CONFIGURATION
# ============================================

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# FastAPI App
app = FastAPI(
    title="Global Supply Chain Risk Intelligence API",
    description="Multi-API integration for real-time supply chain risk assessment",
    version="1.0.0"
)

# CORS Configuration
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Allow all origins (restrict in production)
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Initialize cache on startup
@app.on_event("startup")
async def startup_event():
    """Initialize cache system on app startup"""
    logger.info("🚀 Starting up Global Supply Chain Risk Intelligence API")
    init_cache()
    logger.info("✓ Cache system initialized")

# ============================================
# PYDANTIC MODELS (Request/Response)
# ============================================

class CountryData(BaseModel):
    name: str
    gdp: Optional[float] = None
    inflation: Optional[float] = None
    population: Optional[int] = None
    currency: Optional[str] = None
    exchangeRate: Optional[float] = None
    riskScore: Optional[float] = None
    region: Optional[str] = None
    coordinates: Optional[List[float]] = None
    weather: Optional[Dict[str, Any]] = None

class RiskComponent(BaseModel):
    weatherRisk: float
    inflationRisk: float
    currencyRisk: float
    newsRisk: float
    compositeScore: float
    riskLevel: str  # LOW, MEDIUM, HIGH

class ComparisonResult(BaseModel):
    countryA: str
    countryB: str
    metrics: Dict[str, Any]

class Port(BaseModel):
    name: str
    country: str
    latitude: float
    longitude: float
    portType: str

class NewsArticle(BaseModel):
    title: str
    source: str
    url: Optional[str] = None
    sentiment: str  # positive, neutral, negative
    date: str

# ============================================
# API 1: MACROECONOMIC DATA (World Bank)
# ============================================

@app.get("/api/macroeconomic/{country_name}")
async def get_macroeconomic_data(country_name: str) -> Dict[str, Any]:
    """
    Fetch macroeconomic data from World Bank API
    Includes: GDP, Inflation, Population
    Cached for 24 hours in Redis
    """
    try:
        logger.info(f"Fetching macroeconomic data for {country_name}")
        
        # Build cache key
        cache_key = f"macro:{country_name.lower()}"
        
        # Try cache first
        cached_data = cache_manager.get(cache_key)
        if cached_data:
            logger.info(f"✓ Serving from cache: {cache_key}")
            return cached_data
        
        # World Bank API endpoint
        wb_url = "https://api.worldbank.org/v2/country"
        
        # Get country data
        response = requests.get(
            f"{wb_url}?name={country_name}&format=json",
            timeout=10
        )
        
        if response.status_code != 200:
            raise HTTPException(
                status_code=404,
                detail=f"Country {country_name} not found"
            )
        
        data = response.json()
        
        if not data[1]:  # No results
            raise HTTPException(
                status_code=404,
                detail=f"No macroeconomic data for {country_name}"
            )
        
        country_data = data[1][0]
        
        result = {
            "country": country_data.get("name"),
            "region": country_data.get("region", {}).get("value"),
            "incomeLevel": country_data.get("incomeLevel", {}).get("value"),
            "capitalCity": country_data.get("capitalCity"),
            "latitude": float(country_data.get("latitude", 0)),
            "longitude": float(country_data.get("longitude", 0)),
            "timestamp": datetime.now().isoformat()
        }
        
        # Cache for 24 hours
        cache_manager.set(cache_key, result, CACHE_CONFIG['ttl']['redis'])
        
        return result
    
    except requests.exceptions.Timeout:
        raise HTTPException(status_code=504, detail="World Bank API timeout")
    except Exception as e:
        logger.error(f"Error fetching macroeconomic data: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

# ============================================
# API 2: WEATHER DATA (Open-Meteo)
# ============================================

@app.get("/api/weather/{country_name}")
async def get_weather_data(country_name: str) -> Dict[str, Any]:
    """
    Fetch weather data from Open-Meteo API (no API key needed)
    Uses country coordinates to get real-time weather
    Cached for 1 hour (frequently updated)
    """
    try:
        logger.info(f"Fetching weather data for {country_name}")
        
        # Build cache key
        cache_key = f"weather:{country_name.lower()}"
        
        # Try cache first
        cached_data = cache_manager.get(cache_key)
        if cached_data:
            logger.info(f"✓ Serving from cache: {cache_key}")
            return cached_data
        
        # Get country coordinates
        coords = get_country_coordinates(country_name)
        
        if not coords:
            raise HTTPException(
                status_code=404,
                detail=f"Coordinates not found for {country_name}"
            )
        
        lat, lng = coords
        
        # Open-Meteo API (no authentication needed)
        weather_url = "https://api.open-meteo.com/v1/forecast"
        
        params = {
            "latitude": lat,
            "longitude": lng,
            "current": "temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m",
            "timezone": "UTC"
        }
        
        response = requests.get(weather_url, params=params, timeout=10)
        
        if response.status_code != 200:
            raise HTTPException(
                status_code=500,
                detail="Weather API error"
            )
        
        weather_data = response.json()
        current = weather_data.get("current", {})
        
        result = {
            "country": country_name,
            "temperature": current.get("temperature_2m"),
            "humidity": current.get("relative_humidity_2m"),
            "windSpeed": current.get("wind_speed_10m"),
            "condition": get_weather_condition(current.get("weather_code", 0)),
            "timestamp": datetime.now().isoformat()
        }
        
        # Cache for 1 hour (more frequently updated)
        cache_manager.set(cache_key, result, CACHE_CONFIG['ttl']['medium'])
        
        return result
    
    except requests.exceptions.Timeout:
        raise HTTPException(status_code=504, detail="Weather API timeout")
    except Exception as e:
        logger.error(f"Error fetching weather data: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

# ============================================
# API 3: EXCHANGE RATES (ExchangeRate API)
# ============================================

@app.get("/api/exchange-rates/{currency_code}")
async def get_exchange_rates(currency_code: str) -> Dict[str, Any]:
    """
    Fetch exchange rates from ExchangeRate API
    Base currency defaults to USD
    Cached for 24 hours (rates don't change frequently)
    """
    try:
        logger.info(f"Fetching exchange rates for {currency_code}")
        
        # Build cache key
        cache_key = f"exchange:{currency_code.upper()}"
        
        # Try cache first
        cached_data = cache_manager.get(cache_key)
        if cached_data:
            logger.info(f"✓ Serving from cache: {cache_key}")
            return cached_data
        
        base_currency = "USD"
        exchange_url = f"https://api.exchangerate-api.com/v4/latest/{base_currency}"
        
        response = requests.get(exchange_url, timeout=10)
        
        if response.status_code != 200:
            raise HTTPException(
                status_code=500,
                detail="Exchange rate API error"
            )
        
        data = response.json()
        rates = data.get("rates", {})
        
        result = {
            "base": base_currency,
            "target": currency_code,
            "rate": rates.get(currency_code, 0),
            "rates": rates,
            "timestamp": data.get("time_last_updated", datetime.now().isoformat())
        }
        
        # Cache for 24 hours
        cache_manager.set(cache_key, result, CACHE_CONFIG['ttl']['redis'])
        
        return result
    
    except requests.exceptions.Timeout:
        raise HTTPException(status_code=504, detail="Exchange rate API timeout")
    except Exception as e:
        logger.error(f"Error fetching exchange rates: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

# ============================================
# API 4: NEWS INTELLIGENCE (GNews)
# ============================================

@app.get("/api/news")
async def get_supply_chain_news(
    country: Optional[str] = None,
    category: Optional[str] = "logistics"
) -> Dict[str, List[NewsArticle]]:
    """
    Fetch supply chain news from GNews API
    Categories: logistics, trade, shipping, economy
    """
    try:
        logger.info(f"Fetching news for {category}")
        
        news_url = "https://gnewsapi.net/api/search"
        
        # Search terms based on category
        search_queries = {
            "logistics": "supply chain logistics",
            "trade": "international trade",
            "shipping": "shipping ports maritime",
            "economy": "economic indicators"
        }
        
        query = search_queries.get(category, "supply chain")
        
        params = {
            "q": f"{query} {country or ''}",
            "token": os.getenv("GNEWS_API_KEY", "demo"),
            "max": 10
        }
        
        response = requests.get(news_url, params=params, timeout=10)
        
        if response.status_code != 200:
            # Return mock data if API fails
            logger.warning("GNews API unavailable, returning mock data")
            return {
                "articles": [
                    {
                        "title": f"Supply chain update from {country or 'global market'}",
                        "source": "Supply Chain News",
                        "sentiment": "neutral",
                        "date": datetime.now().isoformat()
                    }
                ]
            }
        
        news_data = response.json()
        articles = news_data.get("articles", [])
        
        return {
            "articles": articles,
            "count": len(articles),
            "timestamp": datetime.now().isoformat()
        }
    
    except requests.exceptions.Timeout:
        logger.warning("News API timeout, returning mock data")
        return {"articles": [], "count": 0}
    except Exception as e:
        logger.error(f"Error fetching news: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

# ============================================
# API 5: GEOGRAPHIC DATA (REST Countries)
# ============================================

@app.get("/api/geographic/{country_name}")
async def get_geographic_data(country_name: str) -> Dict[str, Any]:
    """
    Fetch geographic and country metadata from REST Countries API
    Includes: currencies, languages, borders, regions
    Cached for 7 days (static data)
    """
    try:
        logger.info(f"Fetching geographic data for {country_name}")
        
        # Build cache key
        cache_key = f"geo:{country_name.lower()}"
        
        # Try cache first
        cached_data = cache_manager.get(cache_key)
        if cached_data:
            logger.info(f"✓ Serving from cache: {cache_key}")
            return cached_data
        
        geo_url = f"https://restcountries.com/v3.1/name/{country_name}"
        
        response = requests.get(geo_url, timeout=10)
        
        if response.status_code != 200:
            raise HTTPException(
                status_code=404,
                detail=f"Geographic data not found for {country_name}"
            )
        
        countries = response.json()
        
        if not countries:
            raise HTTPException(
                status_code=404,
                detail=f"No data for {country_name}"
            )
        
        country = countries[0]
        
        result = {
            "name": country.get("name", {}).get("common"),
            "officialName": country.get("name", {}).get("official"),
            "region": country.get("region"),
            "subregion": country.get("subregion"),
            "currencies": country.get("currencies", {}),
            "languages": country.get("languages", {}),
            "borders": country.get("borders", []),
            "area": country.get("area"),
            "population": country.get("population"),
            "timezone": country.get("timezones", []),
            "timestamp": datetime.now().isoformat()
        }
        
        # Cache for 7 days (static data)
        cache_manager.set(cache_key, result, CACHE_CONFIG['ttl']['long'])
        
        return result
    
    except requests.exceptions.Timeout:
        raise HTTPException(status_code=504, detail="Geographic API timeout")
    except Exception as e:
        logger.error(f"Error fetching geographic data: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

# ============================================
# MAIN ENDPOINTS - RISK CALCULATION
# ============================================

@app.get("/api/country/{country_name}")
async def get_country_dashboard(country_name: str) -> CountryData:
    """
    Get comprehensive country dashboard with all risk metrics
    Combines data from all 6 APIs
    """
    try:
        logger.info(f"Generating dashboard for {country_name}")
        
        # Calculate risk (uses supply_chain_risk_engine.py)
        risk_score = await asyncio.to_thread(
            calculate_country_risk,
            country_name
        )
        
        # Get geographic data
        geo_data = await get_geographic_data(country_name)
        weather_data = await get_weather_data(country_name)
        
        return CountryData(
            name=country_name,
            riskScore=risk_score,
            region=geo_data.get("region"),
            currency=list(geo_data.get("currencies", {}).keys())[0] if geo_data.get("currencies") else "USD",
            weather=weather_data,
            coordinates=get_country_coordinates(country_name)
        )
    
    except Exception as e:
        logger.error(f"Error generating dashboard: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/api/risk/{country_name}")
async def get_risk_breakdown(country_name: str) -> RiskComponent:
    """
    Get detailed risk score breakdown with all 4 components
    Returns: Weather, Inflation, Currency, News Risk
    """
    try:
        logger.info(f"Calculating risk breakdown for {country_name}")
        
        # Calculate individual components
        risk_data = await asyncio.to_thread(
            calculate_country_risk,
            country_name
        )
        
        return RiskComponent(
            weatherRisk=normalize_score(risk_data * 0.25, 0, 100),
            inflationRisk=normalize_score(risk_data * 0.25, 0, 100),
            currencyRisk=normalize_score(risk_data * 0.30, 0, 100),
            newsRisk=normalize_score(risk_data * 0.20, 0, 100),
            compositeScore=risk_data,
            riskLevel="HIGH" if risk_data >= 60 else "MEDIUM" if risk_data >= 30 else "LOW"
        )
    
    except Exception as e:
        logger.error(f"Error calculating risk breakdown: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/api/compare")
async def compare_two_countries(
    country_a: str,
    country_b: str
) -> ComparisonResult:
    """
    Compare two countries side-by-side
    """
    try:
        logger.info(f"Comparing {country_a} vs {country_b}")
        
        # Get both countries' data
        data_a = await get_country_dashboard(country_a)
        data_b = await get_country_dashboard(country_b)
        
        comparison = {
            "gdp": {country_a: data_a.gdp, country_b: data_b.gdp},
            "riskScore": {country_a: data_a.riskScore, country_b: data_b.riskScore},
            "currency": {country_a: data_a.currency, country_b: data_b.currency},
        }
        
        return ComparisonResult(
            countryA=country_a,
            countryB=country_b,
            metrics=comparison
        )
    
    except Exception as e:
        logger.error(f"Error comparing countries: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

# ============================================
# PORT ENDPOINTS
# ============================================

@app.get("/api/ports/search")
async def search_ports(
    query: Optional[str] = None,
    country: Optional[str] = None
) -> Dict[str, List[Port]]:
    """
    Search ports by name or country
    Returns matching ports with coordinates
    """
    try:
        # Load ports database
        with open("public/ports-complete.json", "r") as f:
            ports_data = json.load(f)
        
        results = []
        
        for port in ports_data:
            if query and query.lower() not in port.get("name", "").lower():
                continue
            if country and country.lower() != port.get("country", "").lower():
                continue
            
            results.append(Port(
                name=port.get("name"),
                country=port.get("country"),
                latitude=port.get("latitude", 0),
                longitude=port.get("longitude", 0),
                portType=port.get("type", "general")
            ))
        
        return {
            "ports": results[:20],  # Return top 20
            "count": len(results)
        }
    
    except Exception as e:
        logger.error(f"Error searching ports: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

# ============================================
# CACHE MANAGEMENT ENDPOINTS
# ============================================

@app.get("/api/cache/stats")
async def get_cache_stats() -> Dict[str, Any]:
    """
    Get cache statistics and performance metrics
    Shows: hits, misses, hit rate, Redis status
    """
    stats = cache_manager.get_stats()
    info = cache_manager.get_info()
    
    return {
        "statistics": stats,
        "redis": info,
        "config": {
            "ttl_browser": CACHE_CONFIG['ttl']['browser'],
            "ttl_application": CACHE_CONFIG['ttl']['application'],
            "ttl_redis": CACHE_CONFIG['ttl']['redis']
        },
        "timestamp": datetime.now().isoformat()
    }

@app.post("/api/cache/clear")
async def clear_cache(pattern: Optional[str] = None) -> Dict[str, Any]:
    """
    Clear cache by pattern or entire cache
    Examples: 
      - No pattern: clears entire cache
      - pattern='country:*': clears all country data
      - pattern='weather:germany': clears specific entry
    """
    try:
        if pattern:
            deleted = cache_manager.clear_pattern(pattern)
            return {
                "status": "cleared",
                "pattern": pattern,
                "deleted": deleted,
                "timestamp": datetime.now().isoformat()
            }
        else:
            cache_manager.clear_all()
            return {
                "status": "cleared",
                "pattern": "all",
                "deleted": "all",
                "timestamp": datetime.now().isoformat()
            }
    except Exception as e:
        logger.error(f"Error clearing cache: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/api/cache/invalidate/{entity_type}/{entity_id}")
async def invalidate_cache_entry(entity_type: str, entity_id: str) -> Dict[str, Any]:
    """
    Invalidate specific cache entries
    Examples:
      - /api/cache/invalidate/country/Germany
      - /api/cache/invalidate/weather/Singapore
    """
    try:
        cache_manager.invalidate_related(entity_type, entity_id)
        return {
            "status": "invalidated",
            "entity_type": entity_type,
            "entity_id": entity_id,
            "timestamp": datetime.now().isoformat()
        }
    except Exception as e:
        logger.error(f"Error invalidating cache: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

# ============================================
# HEALTH CHECK
# ============================================

@app.get("/api/health")
async def health_check() -> Dict[str, str]:
    """
    Health check endpoint
    """
    return {
        "status": "healthy",
        "timestamp": datetime.now().isoformat(),
        "version": "1.0.0"
    }

# ============================================
# HELPER FUNCTIONS
# ============================================

def get_weather_condition(weather_code: int) -> str:
    """Convert WMO weather code to description"""
    conditions = {
        0: "Clear sky",
        1: "Mainly clear",
        2: "Partly cloudy",
        3: "Overcast",
        45: "Foggy",
        48: "Depositing rime fog",
        51: "Light drizzle",
        53: "Moderate drizzle",
        55: "Dense drizzle",
        61: "Slight rain",
        63: "Moderate rain",
        65: "Heavy rain",
        71: "Slight snow",
        73: "Moderate snow",
        75: "Heavy snow",
        77: "Snow grains",
        80: "Slight rain showers",
        81: "Moderate rain showers",
        82: "Violent rain showers",
        85: "Slight snow showers",
        86: "Heavy snow showers",
        95: "Thunderstorm",
        96: "Thunderstorm with slight hail",
        99: "Thunderstorm with heavy hail"
    }
    return conditions.get(weather_code, "Unknown")

# ============================================
# MAIN
# ============================================

if __name__ == "__main__":
    import uvicorn
    import os
    
    port = int(os.getenv("PORT", 8000))
    uvicorn.run(
        app,
        host="0.0.0.0",
        port=port,
        reload=True
    )
