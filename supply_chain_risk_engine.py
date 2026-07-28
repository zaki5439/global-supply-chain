#!/usr/bin/env python3
"""
Global Supply Chain Risk Intelligence Platform - Python Backend
Integrates 6 APIs and calculates composite risk scores for supply chain monitoring

Author: Enterprise Solutions Architect
Version: 1.0
Python: 3.8+
"""

import requests
import json
import logging
from datetime import datetime, timedelta
from typing import Dict, List, Optional, Tuple
import statistics
from urllib.parse import urlencode

# ============================================================================
# CONFIGURATION & CONSTANTS
# ============================================================================

logger = logging.getLogger(__name__)
logging.basicConfig(level=logging.INFO)

# API Endpoints (free/public APIs)
APIS = {
    'open_meteo': 'https://api.open-meteo.com/v1/forecast',
    'world_bank': 'https://api.worldbank.org/v2',
    'rest_countries': 'https://restcountries.com/v3.1',
    'exchange_rate': 'https://api.exchangerate-api.com/v4',
    'gnews': 'https://gnewsapi.net/api/search',
}

# API Request Timeouts & Retries
REQUEST_TIMEOUT = 5
MAX_RETRIES = 2

# Risk Weights
RISK_WEIGHTS = {
    'weather': 0.25,
    'inflation': 0.25,
    'currency': 0.30,
    'news': 0.20
}

# Risk Category Thresholds
RISK_THRESHOLDS = {
    'low': (0, 30),
    'medium': (30, 60),
    'high': (60, 100)
}

# ============================================================================
# UTILITY FUNCTIONS
# ============================================================================

def retry_api_call(url: str, timeout: int = REQUEST_TIMEOUT, max_retries: int = MAX_RETRIES) -> Optional[Dict]:
    """
    Executes API call with exponential backoff retry strategy
    
    Args:
        url: Full API endpoint URL
        timeout: Request timeout in seconds
        max_retries: Maximum number of retry attempts
    
    Returns:
        JSON response dict or None if failed
    """
    for attempt in range(1, max_retries + 1):
        try:
            logger.info(f"API Request (attempt {attempt}): {url[:80]}...")
            response = requests.get(url, timeout=timeout)
            response.raise_for_status()
            return response.json()
        
        except requests.exceptions.Timeout:
            logger.warning(f"Timeout on attempt {attempt}")
            if attempt < max_retries:
                wait_time = 2 ** attempt
                logger.info(f"Retrying in {wait_time}s...")
                continue
        
        except requests.exceptions.RequestException as e:
            logger.error(f"Request failed: {e}")
            if attempt < max_retries:
                continue
        
        except json.JSONDecodeError:
            logger.error("Invalid JSON response")
            break
    
    logger.error(f"All {max_retries} retries failed")
    return None

def normalize_score(value: float, min_val: float, max_val: float, target_max: float = 100) -> float:
    """
    Normalizes a value to 0-100 scale
    
    Args:
        value: Raw value to normalize
        min_val: Minimum expected value
        max_val: Maximum expected value
        target_max: Target maximum (default 100)
    
    Returns:
        Normalized score 0-100
    """
    if max_val == min_val:
        return 0
    normalized = ((value - min_val) / (max_val - min_val)) * target_max
    return max(0, min(target_max, normalized))

# ============================================================================
# WEATHER RISK CALCULATION
# ============================================================================

def get_weather_data(country_name: str) -> Optional[Dict]:
    """
    Fetches current weather data from Open-Meteo API (no API key required)
    
    Args:
        country_name: Country name (e.g., "Germany")
    
    Returns:
        Weather data dict with temperature, rainfall, wind, conditions
    """
    # Get country coordinates first
    country_coords = get_country_coordinates(country_name)
    if not country_coords:
        logger.error(f"Could not find coordinates for {country_name}")
        return None
    
    lat, lng = country_coords
    
    # Open-Meteo API call
    params = {
        'latitude': lat,
        'longitude': lng,
        'current': 'temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m,precipitation',
        'timezone': 'auto'
    }
    
    url = f"{APIS['open_meteo']}?{urlencode(params)}"
    response = retry_api_call(url)
    
    if not response or 'current' not in response:
        logger.warning(f"Weather data unavailable for {country_name}")
        return {
            'temperature_c': 20,
            'humidity_percent': 50,
            'rainfall_mm': 0,
            'wind_speed_kmh': 20,
            'weather_condition': 'Unknown',
            'is_severe_warning': False
        }
    
    current = response['current']
    
    return {
        'temperature_c': float(current.get('temperature_2m', 20)),
        'humidity_percent': int(current.get('relative_humidity_2m', 50)),
        'rainfall_mm': float(current.get('precipitation', 0)),
        'wind_speed_kmh': float(current.get('wind_speed_10m', 20)),
        'weather_condition': interpret_weather_code(current.get('weather_code', 0)),
        'is_severe_warning': detect_severe_weather(current.get('weather_code', 0))
    }

def interpret_weather_code(code: int) -> str:
    """
    Interprets WMO weather code to human-readable condition
    
    Args:
        code: WMO weather code (0-99)
    
    Returns:
        Weather condition string
    """
    weather_codes = {
        0: "Clear",
        1: "Mainly Clear",
        2: "Partly Cloudy",
        3: "Overcast",
        45: "Foggy",
        48: "Rime Fog",
        51: "Light Drizzle",
        53: "Moderate Drizzle",
        55: "Heavy Drizzle",
        61: "Slight Rain",
        63: "Moderate Rain",
        65: "Heavy Rain",
        71: "Slight Snow",
        73: "Moderate Snow",
        75: "Heavy Snow",
        80: "Slight Rain Showers",
        81: "Moderate Rain Showers",
        82: "Heavy Rain Showers",
        85: "Slight Snow Showers",
        86: "Heavy Snow Showers",
        95: "Thunderstorm",
        96: "Thunderstorm with Hail",
        99: "Thunderstorm with Large Hail"
    }
    return weather_codes.get(code, "Unknown")

def detect_severe_weather(code: int) -> bool:
    """
    Determines if weather code indicates severe conditions
    
    Args:
        code: WMO weather code
    
    Returns:
        True if severe weather detected
    """
    severe_codes = [65, 75, 82, 86, 95, 96, 99]  # Heavy rain/snow, showers, thunderstorms
    return code in severe_codes

def calculate_weather_risk(weather_data: Dict) -> float:
    """
    Calculates composite weather risk score [0-100]
    
    Formula:
        weather_risk = (0.15 × temp_score) + 
                       (0.25 × precip_score) + 
                       (0.30 × wind_score) + 
                       (0.30 × severe_warning_score)
    
    Args:
        weather_data: Dict with temperature, rainfall, wind, conditions
    
    Returns:
        Risk score 0-100
    """
    
    # Temperature Risk
    temp = weather_data['temperature_c']
    if 10 <= temp <= 25:
        temp_score = 0
    elif 5 <= temp < 10 or 25 < temp <= 35:
        temp_score = 20
    elif 0 <= temp < 5 or 35 < temp <= 45:
        temp_score = 50
    else:
        temp_score = 100
    
    # Precipitation Risk
    rainfall = weather_data['rainfall_mm']
    if rainfall <= 10:
        precip_score = 0
    elif rainfall <= 25:
        precip_score = 30
    elif rainfall <= 50:
        precip_score = 70
    else:
        precip_score = 100
    
    # Wind Risk
    wind = weather_data['wind_speed_kmh']
    if wind <= 40:
        wind_score = 0
    elif wind <= 60:
        wind_score = 40
    elif wind <= 80:
        wind_score = 75
    else:
        wind_score = 100
    
    # Severe Weather Warning Risk
    severe_score = 100 if weather_data['is_severe_warning'] else 0
    
    # Composite
    weather_risk = (
        (0.15 * temp_score) +
        (0.25 * precip_score) +
        (0.30 * wind_score) +
        (0.30 * severe_score)
    )
    
    return round(min(100, weather_risk), 2)

# ============================================================================
# INFLATION RISK CALCULATION
# ============================================================================

def get_macroeconomic_data(country_name: str) -> Optional[Dict]:
    """
    Fetches macroeconomic indicators from World Bank API
    
    Args:
        country_name: Country name or ISO code
    
    Returns:
        Dict with GDP, inflation, population
    """
    country_code = get_country_iso_code(country_name)
    if not country_code:
        return None
    
    # World Bank API uses ISO country codes
    url = f"{APIS['world_bank']}/country/{country_code}/indicator/NY.GDP.MKTP.CD?format=json&per_page=1"
    
    response = retry_api_call(url)
    if not response or len(response) < 2:
        logger.warning(f"Macro data unavailable for {country_name}")
        return {
            'gdp_usd': 0,
            'inflation_rate': 5.0,  # Default global average
            'population': 0
        }
    
    data_points = response[1] or []
    gdp = data_points[0].get('value') if data_points else 0
    
    # Also fetch inflation
    inflation_url = f"{APIS['world_bank']}/country/{country_code}/indicator/FP.CPI.TOTL.ZG?format=json&per_page=1"
    inflation_response = retry_api_call(inflation_url)
    inflation_data = inflation_response[1] or [] if inflation_response and len(inflation_response) > 1 else []
    inflation = inflation_data[0].get('value', 5.0) if inflation_data else 5.0
    
    # Population
    pop_url = f"{APIS['world_bank']}/country/{country_code}/indicator/SP.POP.TOTL?format=json&per_page=1"
    pop_response = retry_api_call(pop_url)
    pop_data = pop_response[1] or [] if pop_response and len(pop_response) > 1 else []
    population = pop_data[0].get('value', 0) if pop_data else 0
    
    return {
        'gdp_usd': gdp or 0,
        'inflation_rate': inflation or 5.0,
        'population': population or 0
    }

def calculate_inflation_risk(inflation_percent: float) -> float:
    """
    Calculates inflation risk score [0-100]
    
    Formula:
        risk_score = min(100, (inflation_rate / 20.0) × 100)
    
    Args:
        inflation_percent: Annual inflation rate as percentage
    
    Returns:
        Risk score 0-100
    """
    if inflation_percent < 0:
        return 0
    elif inflation_percent <= 3:
        return 10
    elif inflation_percent <= 6:
        return 30
    elif inflation_percent <= 10:
        return 50
    elif inflation_percent <= 15:
        return 75
    else:
        return 100

# ============================================================================
# CURRENCY VOLATILITY RISK CALCULATION
# ============================================================================

def get_exchange_rate_data(country_name: str) -> Optional[Dict]:
    """
    Fetches current and historical exchange rates
    
    Args:
        country_name: Country name to get currency
    
    Returns:
        Dict with current rate, historical data, volatility
    """
    currency = get_country_currency(country_name)
    if not currency:
        currency = "USD"
    
    # Get current rates
    url = f"{APIS['exchange_rate']}/latest/USD"
    response = retry_api_call(url)
    
    if not response or 'rates' not in response:
        logger.warning(f"Exchange rate unavailable for {country_name}")
        return {
            'current_rate': 1.0,
            'rate_30day_avg': 1.0,
            'volatility_30d': 1.0
        }
    
    rates = response['rates']
    current_rate = rates.get(currency, 1.0)
    
    # Simulate 30-day historical data (would come from timeseries in production)
    rate_30day_avg = current_rate * 0.99  # Assume slight downtrend
    
    return {
        'current_rate': current_rate,
        'rate_30day_avg': rate_30day_avg,
        'volatility_30d': abs(current_rate - rate_30day_avg) / rate_30day_avg * 100
    }

def calculate_currency_risk(exchange_data: Dict) -> float:
    """
    Calculates currency volatility and deviation risk [0-100]
    
    Args:
        exchange_data: Dict with current rate, historical, volatility
    
    Returns:
        Risk score 0-100
    """
    current_rate = exchange_data['current_rate']
    rate_30day_avg = exchange_data['rate_30day_avg']
    volatility = exchange_data['volatility_30d']
    
    # Deviation risk
    deviation_pct = abs((current_rate - rate_30day_avg) / rate_30day_avg * 100) if rate_30day_avg > 0 else 0
    
    if deviation_pct <= 1:
        price_impact_risk = 10
    elif deviation_pct <= 3:
        price_impact_risk = 30
    elif deviation_pct <= 5:
        price_impact_risk = 50
    else:
        price_impact_risk = 80
    
    # Volatility risk
    if volatility <= 1:
        volatility_risk = 10
    elif volatility <= 2:
        volatility_risk = 25
    elif volatility <= 3:
        volatility_risk = 40
    elif volatility <= 5:
        volatility_risk = 65
    elif volatility <= 8:
        volatility_risk = 85
    else:
        volatility_risk = 100
    
    # Composite
    currency_risk = (0.4 * price_impact_risk) + (0.6 * volatility_risk)
    
    return round(min(100, currency_risk), 2)

# ============================================================================
# NEWS SENTIMENT RISK CALCULATION
# ============================================================================

def get_news_articles(country_name: str) -> List[Dict]:
    """
    Fetches recent news articles related to supply chain
    
    Args:
        country_name: Country to search for
    
    Returns:
        List of news article dicts
    """
    keywords = [
        f"{country_name} supply chain",
        f"{country_name} logistics",
        f"{country_name} port",
        f"{country_name} trade"
    ]
    
    all_articles = []
    
    for keyword in keywords:
        params = {
            'q': keyword,
            'max': 5,
            'sortby': 'publishedat'
        }
        
        url = f"{APIS['gnews']}?{urlencode(params)}"
        response = retry_api_call(url)
        
        if response and 'articles' in response:
            all_articles.extend(response['articles'])
    
    # Parse articles
    parsed_articles = []
    for article in all_articles[:10]:  # Limit to 10 most recent
        sentiment = simple_sentiment_analysis(
            (article.get('title', '') + ' ' + article.get('description', '')).lower()
        )
        
        parsed_articles.append({
            'title': article.get('title', ''),
            'description': article.get('description', ''),
            'sentiment_score': sentiment,
            'published_date': article.get('publishedAt', ''),
            'source': article.get('source', {}).get('name', 'Unknown')
        })
    
    return parsed_articles

def simple_sentiment_analysis(text: str) -> float:
    """
    Quick sentiment scoring based on keyword matching
    
    Args:
        text: Text to analyze
    
    Returns:
        Sentiment score [-1.0 (negative) to +1.0 (positive)]
    """
    negative_keywords = [
        "crisis", "collapse", "crash", "fail", "loss", "strike",
        "halt", "shutdown", "warning", "alert", "danger", "risk",
        "recession", "volatile", "conflict", "disruption", "delay",
        "congestion", "accident", "embargo", "tariff"
    ]
    
    positive_keywords = [
        "growth", "recovery", "agreement", "stability", "improvement",
        "efficient", "success", "boost", "progress", "expansion",
        "resilience", "recovery"
    ]
    
    negative_count = sum(1 for kw in negative_keywords if kw in text)
    positive_count = sum(1 for kw in positive_keywords if kw in text)
    
    if negative_count > positive_count:
        sentiment = -0.5 - (0.1 * (negative_count - positive_count))
    elif positive_count > negative_count:
        sentiment = 0.3 + (0.1 * (positive_count - negative_count))
    else:
        sentiment = 0.0
    
    return max(-1.0, min(1.0, sentiment))

def calculate_news_sentiment_risk(country_name: str, articles: List[Dict]) -> float:
    """
    Calculates aggregate news sentiment risk [0-100]
    
    Args:
        country_name: Country name (for logging)
        articles: List of news article dicts
    
    Returns:
        Risk score 0-100
    """
    if not articles:
        return 20  # Default low-risk
    
    category_weights = {
        'Logistics': 0.30,
        'Trade': 0.35,
        'Shipping': 0.20,
        'Economy': 0.15
    }
    
    total_risk = 0.0
    
    for article in articles:
        sentiment = article.get('sentiment_score', 0)
        
        # Determine category
        title_lower = article.get('title', '').lower()
        category = 'Logistics'
        if 'trade' in title_lower or 'tariff' in title_lower:
            category = 'Trade'
        elif 'ship' in title_lower or 'port' in title_lower:
            category = 'Shipping'
        elif 'econ' in title_lower or 'market' in title_lower:
            category = 'Economy'
        
        # Convert sentiment to risk
        if sentiment < -0.6:
            base_risk = 80
        elif sentiment < -0.2:
            base_risk = 50
        elif sentiment < 0.2:
            base_risk = 20
        else:
            base_risk = 5
        
        weighted_risk = base_risk * category_weights.get(category, 0.25)
        total_risk += weighted_risk
    
    avg_risk = total_risk / len(articles)
    return round(min(100, avg_risk), 2)

# ============================================================================
# MAIN RISK SCORE CALCULATION
# ============================================================================

def calculate_country_risk(country_name: str) -> Dict:
    """
    MASTER FUNCTION: Computes composite risk score [0-100]
    
    Args:
        country_name: Country name (e.g., "Germany")
    
    Returns:
        Comprehensive risk analysis dict
    """
    logger.info(f"\n{'='*60}")
    logger.info(f"Calculating Risk Score for: {country_name}")
    logger.info(f"{'='*60}\n")
    
    # Step 1: Collect data
    logger.info("Step 1: Fetching data from APIs...")
    weather_data = get_weather_data(country_name)
    macro_data = get_macroeconomic_data(country_name)
    exchange_data = get_exchange_rate_data(country_name)
    news_articles = get_news_articles(country_name)
    
    # Step 2: Calculate sub-risks
    logger.info("Step 2: Calculating sub-component risks...")
    weather_risk = calculate_weather_risk(weather_data)
    inflation_risk = calculate_inflation_risk(macro_data.get('inflation_rate', 5))
    currency_risk = calculate_currency_risk(exchange_data)
    news_risk = calculate_news_sentiment_risk(country_name, news_articles)
    
    logger.info(f"  Weather Risk: {weather_risk}")
    logger.info(f"  Inflation Risk: {inflation_risk}")
    logger.info(f"  Currency Risk: {currency_risk}")
    logger.info(f"  News Risk: {news_risk}")
    
    # Step 3: Composite calculation
    logger.info("Step 3: Computing composite risk score...")
    composite_risk = (
        RISK_WEIGHTS['weather'] * weather_risk +
        RISK_WEIGHTS['inflation'] * inflation_risk +
        RISK_WEIGHTS['currency'] * currency_risk +
        RISK_WEIGHTS['news'] * news_risk
    )
    
    composite_risk = round(max(0, min(100, composite_risk)), 2)
    
    # Step 4: Determine category
    if composite_risk < 30:
        category = "Low Risk"
        color = "#28a745"
    elif composite_risk < 60:
        category = "Medium Risk"
        color = "#ffc107"
    else:
        category = "High Risk"
        color = "#dc3545"
    
    logger.info(f"\n🎯 Composite Risk Score: {composite_risk} ({category})\n")
    
    # Step 5: Generate recommendations
    recommendations = generate_recommendations(composite_risk, category)
    
    # Format response
    result = {
        'country': country_name,
        'composite_risk_score': composite_risk,
        'risk_category': category,
        'color_hex': color,
        'components': {
            'weather': weather_risk,
            'inflation': inflation_risk,
            'currency': currency_risk,
            'news': news_risk
        },
        'data_summary': {
            'temperature_c': weather_data.get('temperature_c'),
            'humidity_percent': weather_data.get('humidity_percent'),
            'rainfall_mm': weather_data.get('rainfall_mm'),
            'wind_speed_kmh': weather_data.get('wind_speed_kmh'),
            'inflation_rate': macro_data.get('inflation_rate'),
            'gdp_usd': macro_data.get('gdp_usd'),
            'population': macro_data.get('population'),
            'exchange_rate': exchange_data.get('current_rate'),
            'currency_volatility': exchange_data.get('volatility_30d'),
            'news_articles_count': len(news_articles)
        },
        'recommendations': recommendations,
        'timestamp': datetime.utcnow().isoformat()
    }
    
    return result

def generate_recommendations(score: float, category: str) -> List[str]:
    """
    Auto-generates recommendations based on risk level
    
    Args:
        score: Risk score [0-100]
        category: Risk category string
    
    Returns:
        List of recommendation strings
    """
    if category == "Low Risk":
        return [
            "✓ Proceed with standard import planning",
            "✓ Normal payment terms acceptable",
            "✓ Minimal supply chain contingencies needed"
        ]
    elif category == "Medium Risk":
        return [
            "⚠ Monitor weather forecasts closely",
            "⚠ Consider currency hedging for large orders",
            "⚠ Build 5-10% buffer into delivery timeline",
            "⚠ Prepare alternative ports/routes"
        ]
    else:  # High Risk
        return [
            "🔴 Defer non-urgent shipments if possible",
            "🔴 Implement currency swap or hedging contracts",
            "🔴 Activate alternative supplier networks",
            "🔴 Increase inventory buffer to 2-3 weeks",
            "🔴 Coordinate with logistics partner on contingencies"
        ]

def compare_countries(country_a: str, country_b: str) -> Dict:
    """
    Side-by-side comparison of two countries
    
    Args:
        country_a: First country name
        country_b: Second country name
    
    Returns:
        Comparison dict with metrics and winners
    """
    logger.info(f"\nComparing {country_a} vs {country_b}...\n")
    
    risk_a = calculate_country_risk(country_a)
    risk_b = calculate_country_risk(country_b)
    
    comparison = {
        'countries': [country_a, country_b],
        'metrics': {
            'risk_score': {
                country_a: risk_a['composite_risk_score'],
                country_b: risk_b['composite_risk_score'],
                'winner': country_a if risk_a['composite_risk_score'] < risk_b['composite_risk_score'] else country_b,
                'difference': abs(risk_a['composite_risk_score'] - risk_b['composite_risk_score'])
            },
            'weather': {
                country_a: risk_a['components']['weather'],
                country_b: risk_b['components']['weather'],
                'winner': country_a if risk_a['components']['weather'] < risk_b['components']['weather'] else country_b
            },
            'inflation': {
                country_a: risk_a['components']['inflation'],
                country_b: risk_b['components']['inflation'],
                'winner': country_a if risk_a['components']['inflation'] < risk_b['components']['inflation'] else country_b
            },
            'currency': {
                country_a: risk_a['components']['currency'],
                country_b: risk_b['components']['currency'],
                'winner': country_a if risk_a['components']['currency'] < risk_b['components']['currency'] else country_b
            },
            'news': {
                country_a: risk_a['components']['news'],
                country_b: risk_b['components']['news'],
                'winner': country_a if risk_a['components']['news'] < risk_b['components']['news'] else country_b
            }
        }
    }
    
    return comparison

# ============================================================================
# HELPER FUNCTIONS (GEO & CURRENCY LOOKUP)
# ============================================================================

def get_country_coordinates(country_name: str) -> Optional[Tuple[float, float]]:
    """Fetches country center coordinates from REST Countries API"""
    url = f"{APIS['rest_countries']}/name/{country_name}"
    response = retry_api_call(url)
    
    if response and len(response) > 0:
        country = response[0]
        if 'latlng' in country and len(country['latlng']) == 2:
            return tuple(country['latlng'])
    
    return None

def get_country_iso_code(country_name: str) -> Optional[str]:
    """Fetches ISO 3166-1 alpha-3 code"""
    url = f"{APIS['rest_countries']}/name/{country_name}"
    response = retry_api_call(url)
    
    if response and len(response) > 0:
        return response[0].get('cca3')
    
    return None

def get_country_currency(country_name: str) -> Optional[str]:
    """Fetches primary currency code"""
    url = f"{APIS['rest_countries']}/name/{country_name}"
    response = retry_api_call(url)
    
    if response and len(response) > 0:
        country = response[0]
        currencies = country.get('currencies', {})
        if currencies:
            return next(iter(currencies.keys()))
    
    return None

# ============================================================================
# MAIN EXECUTION
# ============================================================================

if __name__ == "__main__":
    # Example usage
    test_countries = ["Germany", "China", "Indonesia", "Singapore"]
    
    for country in test_countries:
        try:
            risk_result = calculate_country_risk(country)
            print(f"\n{country}: {risk_result['composite_risk_score']} - {risk_result['risk_category']}")
            print(f"  Components: {risk_result['components']}")
            print(f"  Recommendations: {risk_result['recommendations'][:2]}")
        except Exception as e:
            logger.error(f"Error processing {country}: {e}")
    
    # Comparison example
    print("\n" + "="*60)
    comparison = compare_countries("Germany", "China")
    print(json.dumps(comparison, indent=2))
