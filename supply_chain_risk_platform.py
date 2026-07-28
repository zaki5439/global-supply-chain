"""
Global Supply Chain Risk Intelligence Platform
==============================================
Modular Python implementation for multi-API data integration and risk scoring.

Author: Enterprise Solutions Architect
Version: 1.0.0
"""

import requests
import pandas as pd
import json
import time
from datetime import datetime, timedelta
from typing import Dict, List, Optional, Tuple
from dataclasses import dataclass
import logging

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)


# ============================================================================
# DATA CLASSES
# ============================================================================

@dataclass
class WeatherData:
    """Weather data structure"""
    temperature: float
    precipitation: float
    wind_speed: float
    weather_condition: str
    risk_score: float


@dataclass
class MacroEconomicData:
    """Macroeconomic data structure"""
    gdp_usd: float
    inflation_rate: float
    population: int
    exports_usd: float
    imports_usd: float


@dataclass
class CurrencyData:
    """Currency data structure"""
    currency_code: str
    currency_name: str
    exchange_rate_usd: float
    volatility_30d: float
    trend: str


@dataclass
class NewsData:
    """News data structure"""
    title: str
    description: str
    url: str
    published_at: str
    sentiment_score: float
    category: str


@dataclass
class CountryDashboard:
    """Complete country dashboard data"""
    country_name: str
    iso_code: str
    capital: str
    population: int
    currency: CurrencyData
    economic: MacroEconomicData
    weather: WeatherData
    news: List[NewsData]


@dataclass
class CountryRisk:
    """Country risk assessment"""
    country_name: str
    total_score: float
    weather_score: float
    inflation_score: float
    currency_score: float
    news_score: float
    risk_category: str


# ============================================================================
# API CLIENTS
# ============================================================================

class BaseAPIClient:
    """Base API client with common functionality"""
    
    def __init__(self, timeout: int = 30, max_retries: int = 3):
        self.timeout = timeout
        self.max_retries = max_retries
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'SupplyChainRiskPlatform/1.0'
        })
    
    def make_request(
        self, 
        url: str, 
        params: Optional[Dict] = None,
        headers: Optional[Dict] = None
    ) -> Optional[Dict]:
        """
        Make HTTP request with retry logic and error handling.
        
        Args:
            url: API endpoint URL
            params: Query parameters
            headers: Additional headers
            
        Returns:
            JSON response data or None if failed
        """
        request_headers = self.session.headers.copy()
        if headers:
            request_headers.update(headers)
        
        for attempt in range(self.max_retries):
            try:
                response = self.session.get(
                    url,
                    params=params,
                    headers=request_headers,
                    timeout=self.timeout
                )
                response.raise_for_status()
                return response.json()
                
            except requests.exceptions.Timeout:
                logger.warning(f"Timeout on attempt {attempt + 1}/{self.max_retries} for {url}")
                if attempt < self.max_retries - 1:
                    time.sleep(2 ** attempt)  # Exponential backoff
                    
            except requests.exceptions.HTTPError as e:
                logger.error(f"HTTP error for {url}: {e}")
                return None
                
            except requests.exceptions.RequestException as e:
                logger.error(f"Request error for {url}: {e}")
                if attempt < self.max_retries - 1:
                    time.sleep(2 ** attempt)
                    
            except json.JSONDecodeError as e:
                logger.error(f"JSON decode error for {url}: {e}")
                return None
        
        logger.error(f"Max retries exceeded for {url}")
        return None


class OpenMeteoClient(BaseAPIClient):
    """Open-Meteo API client for weather data"""
    
    BASE_URL = "https://api.open-meteo.com/v1/forecast"
    
    def get_weather_by_coordinates(
        self, 
        latitude: float, 
        longitude: float
    ) -> Optional[WeatherData]:
        """
        Get current weather data for specific coordinates.
        
        Args:
            latitude: Latitude coordinate
            longitude: Longitude coordinate
            
        Returns:
            WeatherData object or None
        """
        params = {
            'latitude': latitude,
            'longitude': longitude,
            'current': 'temperature_2m,precipitation,weather_code,wind_speed_10m',
            'timezone': 'auto'
        }
        
        data = self.make_request(self.BASE_URL, params)
        if not data or 'current' not in data:
            logger.error("Invalid weather data response")
            return None
        
        current = data['current']
        
        # Map WMO weather codes to conditions
        weather_code_map = {
            0: 'clear', 1: 'clear', 2: 'clouds', 3: 'clouds',
            45: 'fog', 48: 'fog',
            51: 'rain', 53: 'rain', 55: 'rain',
            61: 'rain', 63: 'rain', 65: 'rain',
            71: 'snow', 73: 'snow', 75: 'snow',
            77: 'snow',
            80: 'rain', 81: 'rain', 82: 'rain',
            85: 'snow', 86: 'snow',
            95: 'storm', 96: 'storm', 99: 'storm'
        }
        
        weather_code = current.get('weather_code', 0)
        weather_condition = weather_code_map.get(weather_code, 'clear')
        
        weather_data = WeatherData(
            temperature=current.get('temperature_2m', 0),
            precipitation=current.get('precipitation', 0),
            wind_speed=current.get('wind_speed_10m', 0),
            weather_condition=weather_condition,
            risk_score=0.0  # Will be calculated by risk engine
        )
        
        return weather_data


class WorldBankClient(BaseAPIClient):
    """World Bank API client for macroeconomic data"""
    
    BASE_URL = "https://api.worldbank.org/v2"
    
    def get_country_indicator(
        self, 
        country_code: str, 
        indicator: str, 
        year: int = 2022
    ) -> Optional[float]:
        """
        Get specific indicator for a country.
        
        Args:
            country_code: ISO country code
            indicator: World Bank indicator code
            year: Year of data
            
        Returns:
            Indicator value or None
        """
        url = f"{self.BASE_URL}/country/{country_code}/indicator/{indicator}"
        params = {
            'format': 'json',
            'date': f'{year}:{year}',
            'per_page': 1
        }
        
        data = self.make_request(url, params)
        if not data or len(data) < 2:
            logger.error(f"Invalid World Bank response for {indicator}")
            return None
        
        records = data[1]
        if not records or len(records) == 0:
            logger.warning(f"No data found for {indicator} in {year}")
            return None
        
        value = records[0].get('value')
        return float(value) if value is not None else None
    
    def get_macro_economic_data(
        self, 
        country_code: str
    ) -> Optional[MacroEconomicData]:
        """
        Get comprehensive macroeconomic data for a country.
        
        Args:
            country_code: ISO country code
            
        Returns:
            MacroEconomicData object or None
        """
        # World Bank indicator codes
        indicators = {
            'gdp': 'NY.GDP.MKTP.CD',  # GDP (current US$)
            'inflation': 'FP.CPI.TOTL.ZG',  # Inflation, consumer prices
            'population': 'SP.POP.TOTL',  # Population, total
            'exports': 'TX.VAL.MRCH.CD.WD',  # Exports of goods and services
            'imports': 'TM.VAL.MRCH.CD.WD'  # Imports of goods and services
        }
        
        gdp = self.get_country_indicator(country_code, indicators['gdp'])
        inflation = self.get_country_indicator(country_code, indicators['inflation'])
        population = self.get_country_indicator(country_code, indicators['population'])
        exports = self.get_country_indicator(country_code, indicators['exports'])
        imports = self.get_country_indicator(country_code, indicators['imports'])
        
        if gdp is None and inflation is None:
            logger.error(f"No macroeconomic data available for {country_code}")
            return None
        
        return MacroEconomicData(
            gdp_usd=gdp or 0.0,
            inflation_rate=inflation or 0.0,
            population=int(population) if population else 0,
            exports_usd=exports or 0.0,
            imports_usd=imports or 0.0
        )


class RESTCountriesClient(BaseAPIClient):
    """REST Countries API client for geographic metadata"""
    
    BASE_URL = "https://restcountries.com/v3.1"
    
    def get_country_by_name(self, country_name: str) -> Optional[Dict]:
        """
        Get country data by name.
        
        Args:
            country_name: Country name
            
        Returns:
            Country data dictionary or None
        """
        url = f"{self.BASE_URL}/name/{country_name}"
        params = {'fullText': 'false'}
        
        data = self.make_request(url, params)
        if not data or len(data) == 0:
            logger.error(f"Country not found: {country_name}")
            return None
        
        return data[0]
    
    def get_country_coordinates(self, country_name: str) -> Optional[Tuple[float, float]]:
        """
        Get latitude and longitude for a country.
        
        Args:
            country_name: Country name
            
        Returns:
            Tuple of (latitude, longitude) or None
        """
        country_data = self.get_country_by_name(country_name)
        if not country_data:
            return None
        
        latlng = country_data.get('latlng', [])
        if len(latlng) >= 2:
            return (latlng[0], latlng[1])
        
        return None


class ExchangeRateClient(BaseAPIClient):
    """ExchangeRate API client for currency data"""
    
    BASE_URL = "https://api.exchangerate-api.com/v4/latest"
    
    def get_exchange_rates(self, base_currency: str = "USD") -> Optional[Dict]:
        """
        Get current exchange rates.
        
        Args:
            base_currency: Base currency code
            
        Returns:
            Exchange rates dictionary or None
        """
        url = f"{self.BASE_URL}/{base_currency}"
        data = self.make_request(url)
        
        if not data or 'rates' not in data:
            logger.error("Invalid exchange rate response")
            return None
        
        return data
    
    def get_currency_data(
        self, 
        currency_code: str
    ) -> Optional[CurrencyData]:
        """
        Get currency data for a specific currency.
        
        Args:
            currency_code: Currency code (e.g., EUR, CNY)
            
        Returns:
            CurrencyData object or None
        """
        rates_data = self.get_exchange_rates("USD")
        if not rates_data:
            return None
        
        rates = rates_data.get('rates', {})
        rate = rates.get(currency_code)
        
        if rate is None:
            logger.warning(f"Currency rate not found: {currency_code}")
            return None
        
        # Calculate inverse rate (to USD)
        rate_to_usd = 1.0 / rate if rate != 0 else 0
        
        # Simulate volatility and trend (in production, use historical data)
        volatility_30d = abs(rate * 0.02)  # Simulated 2% volatility
        trend = "stable"
        
        currency_names = {
            'EUR': 'Euro', 'GBP': 'British Pound', 'JPY': 'Japanese Yen',
            'CNY': 'Chinese Yuan', 'INR': 'Indian Rupee', 'AUD': 'Australian Dollar',
            'CAD': 'Canadian Dollar', 'CHF': 'Swiss Franc', 'KRW': 'South Korean Won',
            'BRL': 'Brazilian Real', 'RUB': 'Russian Ruble', 'MXN': 'Mexican Peso',
            'IDR': 'Indonesian Rupiah', 'MYR': 'Malaysian Ringgit', 'THB': 'Thai Baht',
            'VND': 'Vietnamese Dong', 'PHP': 'Philippine Peso', 'SGD': 'Singapore Dollar',
            'ARS': 'Argentine Peso', 'TRY': 'Turkish Lira', 'ZAR': 'South African Rand'
        }
        
        return CurrencyData(
            currency_code=currency_code,
            currency_name=currency_names.get(currency_code, currency_code),
            exchange_rate_usd=rate_to_usd,
            volatility_30d=volatility_30d,
            trend=trend
        )


class GNewsClient(BaseAPIClient):
    """GNews API client for news data"""
    
    # Note: GNews requires API key. Using free tier placeholder.
    BASE_URL = "https://gnews.io/api/v4/search"
    
    def __init__(self, api_key: Optional[str] = None, timeout: int = 30, max_retries: int = 3):
        super().__init__(timeout, max_retries)
        self.api_key = api_key or "YOUR_GNEWS_API_KEY"  # Replace with actual API key
    
    def get_news_by_topic(
        self, 
        topic: str, 
        country: Optional[str] = None,
        max_results: int = 10
    ) -> List[NewsData]:
        """
        Get news articles by topic.
        
        Args:
            topic: Search topic (logistics, trade, shipping, economy)
            country: Optional country filter
            max_results: Maximum number of articles
            
        Returns:
            List of NewsData objects
        """
        params = {
            'q': topic,
            'token': self.api_key,
            'max': max_results,
            'lang': 'en'
        }
        
        if country:
            params['country'] = country
        
        data = self.make_request(self.BASE_URL, params)
        if not data or 'articles' not in data:
            logger.error(f"No news data found for topic: {topic}")
            return []
        
        articles = []
        for article in data['articles'][:max_results]:
            # Simple sentiment analysis (in production, use NLP library)
            sentiment_score = self._analyze_sentiment(article.get('title', ''))
            
            news_data = NewsData(
                title=article.get('title', ''),
                description=article.get('description', ''),
                url=article.get('url', ''),
                published_at=article.get('publishedAt', ''),
                sentiment_score=sentiment_score,
                category=topic
            )
            articles.append(news_data)
        
        return articles
    
    def _analyze_sentiment(self, text: str) -> float:
        """
        Simple sentiment analysis based on keyword matching.
        
        Args:
            text: Text to analyze
            
        Returns:
            Sentiment score (-1.0 to 1.0)
        """
        positive_words = ['growth', 'increase', 'improve', 'success', 'recovery', 'boost']
        negative_words = ['crisis', 'decline', 'risk', 'disruption', 'shortage', 'delay']
        
        text_lower = text.lower()
        positive_count = sum(1 for word in positive_words if word in text_lower)
        negative_count = sum(1 for word in negative_words if word in text_lower)
        
        total = positive_count + negative_count
        if total == 0:
            return 0.0
        
        return (positive_count - negative_count) / total


# ============================================================================
# RISK SCORING ENGINE
# ============================================================================

class RiskScoringEngine:
    """Engine for calculating country risk scores"""
    
    def calculate_weather_risk(self, weather: WeatherData) -> float:
        """
        Calculate weather risk score (0-25).
        
        Args:
            weather: WeatherData object
            
        Returns:
            Weather risk score
        """
        base_risk = 5.0
        
        # Precipitation factor
        precip = weather.precipitation
        if precip <= 10:
            precip_factor = 0
        elif precip <= 25:
            precip_factor = 3
        elif precip <= 50:
            precip_factor = 6
        elif precip <= 100:
            precip_factor = 10
        else:
            precip_factor = 15
        
        # Wind factor
        wind = weather.wind_speed
        if wind <= 20:
            wind_factor = 0
        elif wind <= 40:
            wind_factor = 2
        elif wind <= 60:
            wind_factor = 5
        elif wind <= 80:
            wind_factor = 8
        else:
            wind_factor = 12
        
        # Condition factor
        condition_map = {
            'clear': 0, 'clouds': 2, 'rain': 5,
            'storm': 10, 'snow': 7, 'extreme': 15, 'fog': 3
        }
        condition_factor = condition_map.get(weather.weather_condition, 0)
        
        weather_risk = base_risk + precip_factor + wind_factor + condition_factor
        return min(weather_risk, 25.0)
    
    def calculate_inflation_risk(self, economic: MacroEconomicData) -> float:
        """
        Calculate inflation risk score (0-25).
        
        Args:
            economic: MacroEconomicData object
            
        Returns:
            Inflation risk score
        """
        inflation = economic.inflation_rate
        
        # Rate factor
        if inflation <= 2:
            rate_factor = 0
        elif inflation <= 4:
            rate_factor = 3
        elif inflation <= 6:
            rate_factor = 6
        elif inflation <= 10:
            rate_factor = 10
        elif inflation <= 20:
            rate_factor = 15
        else:
            rate_factor = 20
        
        # Trend factor (simulated - in production use historical trend)
        trend_factor = 0  # Assume stable
        
        # Volatility factor (simulated)
        volatility_factor = 0  # Assume low volatility
        
        inflation_risk = rate_factor + trend_factor + volatility_factor
        return max(0.0, min(inflation_risk, 25.0))
    
    def calculate_currency_risk(self, currency: CurrencyData) -> float:
        """
        Calculate currency volatility risk score (0-25).
        
        Args:
            currency: CurrencyData object
            
        Returns:
            Currency risk score
        """
        volatility = currency.volatility_30d
        
        # Volatility factor
        if volatility < 1:
            volatility_factor = 0
        elif volatility < 3:
            volatility_factor = 3
        elif volatility < 5:
            volatility_factor = 6
        elif volatility < 10:
            volatility_factor = 10
        else:
            volatility_factor = 15
        
        # Trend factor
        trend_map = {'appreciating': 2, 'stable': 0, 'depreciating': 5}
        trend_factor = trend_map.get(currency.trend, 0)
        
        # Spread factor (simulated)
        spread_factor = 0  # Assume tight spread
        
        currency_risk = volatility_factor + trend_factor + spread_factor
        return max(0.0, min(currency_risk, 25.0))
    
    def calculate_news_sentiment_risk(self, news: List[NewsData]) -> float:
        """
        Calculate news sentiment risk score (0-25).
        
        Args:
            news: List of NewsData objects
            
        Returns:
            News sentiment risk score
        """
        if not news:
            return 8.0  # Default medium risk if no news
        
        # Calculate average sentiment
        avg_sentiment = sum(n.sentiment_score for n in news) / len(news)
        
        # Sentiment factor
        if avg_sentiment > 0.6:
            sentiment_factor = 0
        elif avg_sentiment > 0.3:
            sentiment_factor = 3
        elif avg_sentiment > -0.3:
            sentiment_factor = 8
        elif avg_sentiment > -0.6:
            sentiment_factor = 12
        else:
            sentiment_factor = 18
        
        # Volume factor
        article_count = len(news)
        if article_count <= 5:
            volume_factor = 0
        elif article_count <= 15:
            volume_factor = 2
        elif article_count <= 30:
            volume_factor = 4
        else:
            volume_factor = 7
        
        # Relevance factor (simulated - assume 80% relevant)
        relevance_factor = 0
        
        news_risk = sentiment_factor + volume_factor + relevance_factor
        return max(0.0, min(news_risk, 25.0))
    
    def calculate_total_risk(
        self,
        weather: WeatherData,
        economic: MacroEconomicData,
        currency: CurrencyData,
        news: List[NewsData]
    ) -> CountryRisk:
        """
        Calculate total country risk score.
        
        Args:
            weather: WeatherData object
            economic: MacroEconomicData object
            currency: CurrencyData object
            news: List of NewsData objects
            
        Returns:
            CountryRisk object
        """
        weather_score = self.calculate_weather_risk(weather)
        inflation_score = self.calculate_inflation_risk(economic)
        currency_score = self.calculate_currency_risk(currency)
        news_score = self.calculate_news_sentiment_risk(news)
        
        total_score = weather_score + inflation_score + currency_score + news_score
        
        # Determine risk category
        if total_score <= 30:
            risk_category = "Low Risk"
        elif total_score <= 60:
            risk_category = "Medium Risk"
        else:
            risk_category = "High Risk"
        
        return CountryRisk(
            country_name="",  # Will be set by caller
            total_score=round(total_score, 2),
            weather_score=round(weather_score, 2),
            inflation_score=round(inflation_score, 2),
            currency_score=round(currency_score, 2),
            news_score=round(news_score, 2),
            risk_category=risk_category
        )


# ============================================================================
# MAIN ORCHESTRATOR
# ============================================================================

class SupplyChainRiskPlatform:
    """Main platform orchestrator"""
    
    def __init__(self, gnews_api_key: Optional[str] = None):
        self.weather_client = OpenMeteoClient()
        self.world_bank_client = WorldBankClient()
        self.countries_client = RESTCountriesClient()
        self.exchange_client = ExchangeRateClient()
        self.gnews_client = GNewsClient(gnews_api_key)
        self.risk_engine = RiskScoringEngine()
    
    def get_country_dashboard_data(self, country_name: str) -> Optional[CountryDashboard]:
        """
        Get comprehensive dashboard data for a country.
        
        Args:
            country_name: Name of the country
            
        Returns:
            CountryDashboard object or None
        """
        logger.info(f"Fetching dashboard data for: {country_name}")
        
        # Get country metadata
        country_data = self.countries_client.get_country_by_name(country_name)
        if not country_data:
            logger.error(f"Country not found: {country_name}")
            return None
        
        # Extract basic info
        name = country_data.get('name', {}).get('common', country_name)
        iso_code = country_data.get('cca2', '')
        capital = country_data.get('capital', [''])[0] if country_data.get('capital') else ''
        population = country_data.get('population', 0)
        
        # Get currency info
        currencies = country_data.get('currencies', {})
        currency_code = list(currencies.keys())[0] if currencies else 'USD'
        currency_data = self.exchange_client.get_currency_data(currency_code)
        
        if not currency_data:
            logger.warning(f"Currency data not available for {currency_code}, using USD")
            currency_data = CurrencyData('USD', 'US Dollar', 1.0, 0.0, 'stable')
        
        # Get coordinates for weather
        coordinates = self.countries_client.get_country_coordinates(country_name)
        if not coordinates:
            logger.warning(f"Coordinates not found for {country_name}")
            return None
        
        lat, lon = coordinates
        
        # Get weather data
        weather_data = self.weather_client.get_weather_by_coordinates(lat, lon)
        if not weather_data:
            logger.warning(f"Weather data not available for {country_name}")
            weather_data = WeatherData(0, 0, 0, 'clear', 0)
        
        # Get macroeconomic data
        economic_data = self.world_bank_client.get_macro_economic_data(iso_code)
        if not economic_data:
            logger.warning(f"Macroeconomic data not available for {iso_code}")
            economic_data = MacroEconomicData(0, 0, 0, 0, 0)
        
        # Get news data (for all 4 topics)
        all_news = []
        topics = ['logistics', 'trade', 'shipping', 'economy']
        for topic in topics:
            try:
                topic_news = self.gnews_client.get_news_by_topic(topic, max_results=3)
                all_news.extend(topic_news)
            except Exception as e:
                logger.warning(f"Failed to fetch news for {topic}: {e}")
        
        # Create dashboard
        dashboard = CountryDashboard(
            country_name=name,
            iso_code=iso_code,
            capital=capital,
            population=population,
            currency=currency_data,
            economic=economic_data,
            weather=weather_data,
            news=all_news[:10]  # Limit to 10 articles
        )
        
        return dashboard
    
    def calculate_country_risk(self, country_name: str) -> Optional[str]:
        """
        Calculate risk score for a country and return formatted string.
        
        Args:
            country_name: Name of the country
            
        Returns:
            Formatted string: "CountryName : Score (RiskCategory)"
        """
        logger.info(f"Calculating risk for: {country_name}")
        
        dashboard = self.get_country_dashboard_data(country_name)
        if not dashboard:
            logger.error(f"Cannot calculate risk: dashboard data unavailable for {country_name}")
            return None
        
        risk = self.risk_engine.calculate_total_risk(
            dashboard.weather,
            dashboard.economic,
            dashboard.currency,
            dashboard.news
        )
        
        risk.country_name = dashboard.country_name
        
        # Format output as specified
        output = f"{risk.country_name} : {int(risk.total_score)} ({risk.risk_category})"
        return output
    
    def compare_countries(self, country_a: str, country_b: str) -> Optional[Dict]:
        """
        Compare two countries across 5 key metrics.
        
        Args:
            country_a: First country name
            country_b: Second country name
            
        Returns:
            Dictionary with comparison data
        """
        logger.info(f"Comparing countries: {country_a} vs {country_b}")
        
        dashboard_a = self.get_country_dashboard_data(country_a)
        dashboard_b = self.get_country_dashboard_data(country_b)
        
        if not dashboard_a or not dashboard_b:
            logger.error("Cannot compare: dashboard data unavailable")
            return None
        
        # Calculate risk scores
        risk_a = self.risk_engine.calculate_total_risk(
            dashboard_a.weather, dashboard_a.economic,
            dashboard_a.currency, dashboard_a.news
        )
        risk_a.country_name = dashboard_a.country_name
        
        risk_b = self.risk_engine.calculate_total_risk(
            dashboard_b.weather, dashboard_b.economic,
            dashboard_b.currency, dashboard_b.news
        )
        risk_b.country_name = dashboard_b.country_name
        
        # Build comparison structure
        comparison = {
            'country_a': {
                'name': dashboard_a.country_name,
                'gdp_usd': dashboard_a.economic.gdp_usd,
                'inflation_rate': dashboard_a.economic.inflation_rate,
                'risk_score': risk_a.total_score,
                'risk_category': risk_a.risk_category,
                'temperature': dashboard_a.weather.temperature,
                'currency_code': dashboard_a.currency.currency_code,
                'exchange_rate_usd': dashboard_a.currency.exchange_rate_usd
            },
            'country_b': {
                'name': dashboard_b.country_name,
                'gdp_usd': dashboard_b.economic.gdp_usd,
                'inflation_rate': dashboard_b.economic.inflation_rate,
                'risk_score': risk_b.total_score,
                'risk_category': risk_b.risk_category,
                'temperature': dashboard_b.weather.temperature,
                'currency_code': dashboard_b.currency.currency_code,
                'exchange_rate_usd': dashboard_b.currency.exchange_rate_usd
            },
            'comparison_metrics': {
                'gdp_winner': dashboard_a.country_name if dashboard_a.economic.gdp_usd > dashboard_b.economic.gdp_usd else dashboard_b.country_name,
                'lower_inflation': dashboard_a.country_name if dashboard_a.economic.inflation_rate < dashboard_b.economic.inflation_rate else dashboard_b.country_name,
                'lower_risk': dashboard_a.country_name if risk_a.total_score < risk_b.total_score else dashboard_b.country_name,
                'warmer': dashboard_a.country_name if dashboard_a.weather.temperature > dashboard_b.weather.temperature else dashboard_b.country_name
            }
        }
        
        return comparison


# ============================================================================
# UTILITY FUNCTIONS
# ============================================================================

def format_currency_value(value: float) -> str:
    """Format currency value for display"""
    if value >= 1_000_000_000:
        return f"${value / 1_000_000_000:.2f}B"
    elif value >= 1_000_000:
        return f"${value / 1_000_000:.2f}M"
    else:
        return f"${value:,.2f}"


def format_population(population: int) -> str:
    """Format population for display"""
    if population >= 1_000_000_000:
        return f"{population / 1_000_000_000:.2f}B"
    elif population >= 1_000_000:
        return f"{population / 1_000_000:.2f}M"
    else:
        return f"{population:,}"


# ============================================================================
# MAIN EXECUTION
# ============================================================================

if __name__ == "__main__":
    # Initialize platform (replace with actual GNews API key if available)
    platform = SupplyChainRiskPlatform(gnews_api_key=None)
    
    print("=" * 80)
    print("GLOBAL SUPPLY CHAIN RISK INTELLIGENCE PLATFORM")
    print("=" * 80)
    print()
    
    # Test 1: Get Country Dashboard Data
    print("TEST 1: Country Dashboard Data")
    print("-" * 80)
    test_country = "Germany"
    dashboard = platform.get_country_dashboard_data(test_country)
    
    if dashboard:
        print(f"Country: {dashboard.country_name}")
        print(f"Capital: {dashboard.capital}")
        print(f"Population: {format_population(dashboard.population)}")
        print(f"Currency: {dashboard.currency.currency_name} ({dashboard.currency.currency_code})")
        print(f"Exchange Rate (to USD): {dashboard.currency.exchange_rate_usd:.4f}")
        print(f"GDP: {format_currency_value(dashboard.economic.gdp_usd)}")
        print(f"Inflation Rate: {dashboard.economic.inflation_rate:.2f}%")
        print(f"Temperature: {dashboard.weather.temperature}°C")
        print(f"Weather Condition: {dashboard.weather.weather_condition}")
        print(f"News Articles: {len(dashboard.news)}")
    else:
        print(f"Failed to fetch dashboard data for {test_country}")
    
    print()
    
    # Test 2: Calculate Country Risk
    print("TEST 2: Country Risk Calculation")
    print("-" * 80)
    test_countries = ["Germany", "China", "Argentina"]
    
    for country in test_countries:
        risk_output = platform.calculate_country_risk(country)
        if risk_output:
            print(risk_output)
        else:
            print(f"Failed to calculate risk for {country}")
    
    print()
    
    # Test 3: Compare Countries
    print("TEST 3: Country Comparison")
    print("-" * 80)
    comparison = platform.compare_countries("Germany", "Australia")
    
    if comparison:
        print(f"Comparing {comparison['country_a']['name']} vs {comparison['country_b']['name']}")
        print()
        print(f"GDP: {format_currency_value(comparison['country_a']['gdp_usd'])} vs {format_currency_value(comparison['country_b']['gdp_usd'])}")
        print(f"Inflation: {comparison['country_a']['inflation_rate']:.2f}% vs {comparison['country_b']['inflation_rate']:.2f}%")
        print(f"Risk Score: {comparison['country_a']['risk_score']:.1f} ({comparison['country_a']['risk_category']}) vs {comparison['country_b']['risk_score']:.1f} ({comparison['country_b']['risk_category']})")
        print(f"Temperature: {comparison['country_a']['temperature']:.1f}°C vs {comparison['country_b']['temperature']:.1f}°C")
        print(f"Currency: {comparison['country_a']['currency_code']} vs {comparison['country_b']['currency_code']}")
        print()
        print("Winners:")
        print(f"  - Higher GDP: {comparison['comparison_metrics']['gdp_winner']}")
        print(f"  - Lower Inflation: {comparison['comparison_metrics']['lower_inflation']}")
        print(f"  - Lower Risk: {comparison['comparison_metrics']['lower_risk']}")
        print(f"  - Warmer: {comparison['comparison_metrics']['warmer']}")
    else:
        print("Failed to compare countries")
    
    print()
    print("=" * 80)
    print("TESTS COMPLETED")
    print("=" * 80)
