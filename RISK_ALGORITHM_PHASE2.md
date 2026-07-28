# FASE 2: ALGORITMA RISK SCORING ENGINE
## Desain Logika Matematika & Normalisasi Data

---

## 1. RISK SCORING FRAMEWORK

### A. Definisi Komposit Risk Score

```
RISK SCORE FORMULA (Composite):

Risk_Total = (W_weather × Risk_weather) + 
             (W_inflation × Risk_inflation) + 
             (W_currency × Risk_currency) + 
             (W_news × Risk_news)

Dimana:
  W_weather = 0.25 (25% weight)
  W_inflation = 0.25 (25% weight)
  W_currency = 0.30 (30% weight)
  W_news = 0.20 (20% weight)
  ∑ W = 1.00 (normalized)

Output Range: 0 - 100 (float precision 2 decimals)

Risk Categories (Thresholds):
  [0, 30)       → "Low Risk" (Green)    #28a745
  [30, 60)      → "Medium Risk" (Yellow) #ffc107
  [60, 100]     → "High Risk" (Red)     #dc3545
```

### B. Rationale Pemberian Bobot

| Faktor | Bobot | Rationale |
|--------|-------|-----------|
| **Weather** | 25% | Cuaca ekstrem langsung impact pengiriman/pelabuhan; dampak: delays 2-7 hari |
| **Inflation** | 25% | Inflasi tinggi meningkatkan biaya produksi & logistics; dampak: cost +10-30% |
| **Currency** | 30% | Volatilitas mata uang langsung affect cost-benefit import; dampak: margin loss 5-20% |
| **News Sentiment** | 20% | Berita geopolitik/trade memberikan early warning; dampak: market disruption dalam 1-3 hari |

---

## 2. SUB-COMPONENT RISK CALCULATIONS

### A. WEATHER RISK SCORE (Risk_weather: 0-100)

**Data Source**: Open-Meteo API

**Factors**:
```
weather_risk = (W_temp × temp_score) + 
               (W_precip × precip_score) + 
               (W_wind × wind_score) + 
               (W_severe × severe_score)

Weights:
  W_temp = 0.15 (Temperature extremes)
  W_precip = 0.25 (Heavy rainfall → flooding → port closure)
  W_wind = 0.30 (Strong winds → shipping delays)
  W_severe = 0.30 (Storm warnings → supply disruption)
```

**Temperature Score Normalization**:
```python
def temperature_risk(temp_celsius):
    # Optimal range: 10-25°C (normal operations)
    # Risk increases at extremes
    
    if 10 <= temp <= 25:
        return 0  # No risk
    elif 5 <= temp < 10:
        return 20  # Cool, slight impact
    elif 25 < temp <= 35:
        return 20  # Warm, slight impact
    elif 0 <= temp < 5:
        return 50  # Cold, moderate impact
    elif 35 < temp <= 45:
        return 50  # Hot, moderate impact
    else:
        return 100  # Extreme temperatures
```

**Precipitation Score Normalization**:
```python
def precipitation_risk(rainfall_mm):
    # Measured: daily rainfall in millimeters
    # Normal: 0-10 mm/day
    # Extreme: >50 mm/day (flooding risk)
    
    if rainfall_mm <= 10:
        return 0  # Normal
    elif 10 < rainfall_mm <= 25:
        return 30  # Moderate
    elif 25 < rainfall_mm <= 50:
        return 70  # Heavy
    else:
        return 100  # Severe (flooding, port closure)
```

**Wind Speed Score Normalization**:
```python
def wind_risk(wind_kmh):
    # Normal maritime shipping: wind < 50 km/h
    # Gale force: > 62 km/h (shipping halted)
    
    if wind_kmh <= 40:
        return 0  # Safe
    elif 40 < wind_kmh <= 60:
        return 40  # Moderate risk
    elif 60 < wind_kmh <= 80:
        return 75  # High risk (delays 12-24h)
    else:
        return 100  # Extreme (port closure)
```

**Severe Weather Warning Score**:
```python
def severe_weather_risk(warning_type):
    # Boolean: is_severe_warning (from API response)
    # warning_type: "Storm", "Flood", "Hail", etc.
    
    warning_scores = {
        "None": 0,
        "Wind Alert": 40,
        "Rain Alert": 50,
        "Storm Warning": 85,
        "Severe Storm": 95,
        "Cyclone/Typhoon": 100
    }
    
    return warning_scores.get(warning_type, 0)
```

**Final Weather Risk (Composite)**:
```
weather_risk = (0.15 × temp_score) + 
               (0.25 × precip_score) + 
               (0.30 × wind_score) + 
               (0.30 × severe_warning_score)

Range: 0-100
```

---

### B. INFLATION RISK SCORE (Risk_inflation: 0-100)

**Data Source**: World Bank API

**Baseline Calibration**:
```
Global average inflation (2023): 4.8%
Acceptable threshold: <6%
Elevated threshold: 6-10%
High inflation: >10%

Formula:
inflation_risk = min(100, (inflation_rate / 20.0) × 100)

Rationale:
  - Inflation 2%: risk = 10
  - Inflation 4%: risk = 20
  - Inflation 8%: risk = 40
  - Inflation 16%: risk = 80
  - Inflation 20%+: risk = 100
```

**Normalized Inflation Risk Mapping**:
```python
def calculate_inflation_risk(inflation_percentage):
    """
    Maps inflation rate to risk score [0-100]
    
    Examples:
      2% inflation → 10 risk score (low)
      6% inflation → 30 risk score (moderate)
      15% inflation → 75 risk score (high)
    """
    
    if inflation_percentage < 0:
        return 0  # Deflation (rare, treat as benign)
    elif inflation_percentage <= 3:
        return 10  # Very low (optimal)
    elif inflation_percentage <= 6:
        return 30  # Low (normal range)
    elif inflation_percentage <= 10:
        return 50  # Moderate (concerning)
    elif inflation_percentage <= 15:
        return 75  # High (risk)
    else:
        return 100  # Hyper-inflation (critical)
```

---

### C. CURRENCY VOLATILITY RISK (Risk_currency: 0-100)

**Data Source**: ExchangeRate API (current + historical 30-day data)

**Volatility Calculation**:
```python
def calculate_currency_volatility_risk(rates_30day):
    """
    Calculate standard deviation of exchange rates over 30 days
    
    Inputs:
      rates_30day: list of 30 daily exchange rates (base USD to target currency)
    
    Outputs:
      volatility_score: [0-100] risk score
    
    Interpretation:
      Low volatility (±2%): safe for import planning
      Medium volatility (2-5%): moderate risk
      High volatility (5-10%): significant risk
      Extreme volatility (>10%): critical risk
    """
    
    import numpy as np
    
    # Calculate daily percentage changes
    daily_changes = [
        (rates_30day[i] - rates_30day[i-1]) / rates_30day[i-1] * 100
        for i in range(1, len(rates_30day))
    ]
    
    # Calculate standard deviation (volatility)
    volatility_percent = np.std(daily_changes)
    
    # Normalize to risk score [0-100]
    if volatility_percent <= 1:
        volatility_risk = 10  # Very stable
    elif volatility_percent <= 2:
        volatility_risk = 25  # Stable
    elif volatility_percent <= 3:
        volatility_risk = 40  # Moderate
    elif volatility_percent <= 5:
        volatility_risk = 65  # High
    elif volatility_percent <= 8:
        volatility_risk = 85  # Very high
    else:
        volatility_risk = 100  # Extreme
    
    return volatility_risk
```

**Combined Currency Risk** (Price Impact + Volatility):
```python
def calculate_currency_risk(current_rate, rate_30day_avg, volatility):
    """
    Combines rate deviation + volatility into single score
    
    Example calculation:
      Current rate: 1.08 USD/EUR
      30-day avg:   1.05 USD/EUR
      Deviation:    2.9% (above average - unfavorable for imports)
      Volatility:   3.2% (moderate)
      
      Price impact risk: 40
      Volatility risk:   65
      Combined: (0.4 × 40) + (0.6 × 65) = 57
    """
    
    # Rate deviation from 30-day average
    deviation_pct = abs((current_rate - rate_30day_avg) / rate_30day_avg * 100)
    
    if deviation_pct <= 1:
        price_impact_risk = 10
    elif deviation_pct <= 3:
        price_impact_risk = 30
    elif deviation_pct <= 5:
        price_impact_risk = 50
    else:
        price_impact_risk = 80
    
    # Combined: 40% weight on price impact, 60% on volatility
    currency_risk = (0.4 * price_impact_risk) + (0.6 * volatility)
    
    return min(100, currency_risk)
```

---

### D. NEWS SENTIMENT RISK (Risk_news: 0-100)

**Data Source**: GNews API (filtered supply chain keywords)

**News Categories & Sentiment Weights**:
```
Search Keywords (by category):
  1. Logistics: "port strike", "logistics delay", "shipping disruption"
  2. Trade: "trade war", "tariff", "embargo", "trade agreement"
  3. Shipping: "vessel collision", "port congestion", "maritime accident"
  4. Economy: "recession", "market crash", "currency crisis"

Risk Scoring by Sentiment & Category:
┌────────────┬────────────────┬────────────────┬──────────────┐
│ Sentiment  │ Logistics News │ Trade News     │ Economy News │
├────────────┼────────────────┼────────────────┼──────────────┤
│ Negative   │ +30 risk pts   │ +40 risk pts   │ +35 risk pts │
│ Neutral    │ +10 risk pts   │ +15 risk pts   │ +10 risk pts │
│ Positive   │ 0 risk pts     │ -5 risk pts    │ 0 risk pts   │
└────────────┴────────────────┴────────────────┴──────────────┘
```

**News Aggregation Logic**:
```python
def calculate_news_sentiment_risk(country_name, news_articles):
    """
    Aggregate sentiment from recent news articles (last 7 days)
    
    Inputs:
      news_articles: [
        {
          "title": "...",
          "category": "Trade",
          "sentiment_score": -0.8,  # [-1.0 (very negative) to +1.0 (very positive)]
          "source": "Reuters",
          "published_date": "2026-07-20"
        },
        ...
      ]
    """
    
    import datetime
    
    # Filter articles from last 7 days
    cutoff_date = datetime.datetime.now() - datetime.timedelta(days=7)
    recent_articles = [
        a for a in news_articles 
        if datetime.datetime.fromisoformat(a['published_date']) > cutoff_date
    ]
    
    if not recent_articles:
        return 20  # Default low-risk if no recent news
    
    # Category weights (importance)
    category_weights = {
        "Logistics": 0.30,
        "Trade": 0.35,
        "Shipping": 0.20,
        "Economy": 0.15
    }
    
    news_risk = 0.0
    
    for article in recent_articles:
        category = article.get("category", "Logistics")
        sentiment = article.get("sentiment_score", 0)  # [-1.0, +1.0]
        
        # Convert sentiment to base risk points
        if sentiment < -0.6:  # Strongly negative
            base_risk = 80
        elif sentiment < -0.2:  # Negative
            base_risk = 50
        elif sentiment < 0.2:  # Neutral
            base_risk = 20
        else:  # Positive
            base_risk = 5
        
        # Apply category weight
        category_weight = category_weights.get(category, 0.25)
        weighted_risk = base_risk * category_weight
        
        news_risk += weighted_risk
    
    # Average across all articles
    avg_news_risk = news_risk / len(recent_articles)
    
    # Cap at 100
    return min(100, avg_news_risk)
```

**Sentiment Analysis** (if native sentiment not available):
```python
def simple_sentiment_analysis(text):
    """
    Quick sentiment scoring based on keyword matching
    (alternative to external sentiment API)
    """
    
    negative_keywords = [
        "crisis", "collapse", "crash", "fail", "loss",
        "strike", "halt", "shutdown", "warning", "alert",
        "danger", "risk", "recession", "volatile", "conflict"
    ]
    
    positive_keywords = [
        "growth", "recovery", "agreement", "stability",
        "improvement", "efficient", "success", "boost"
    ]
    
    text_lower = text.lower()
    
    negative_count = sum(1 for kw in negative_keywords if kw in text_lower)
    positive_count = sum(1 for kw in positive_keywords if kw in text_lower)
    
    if negative_count > positive_count:
        return -0.5 - (0.1 * (negative_count - positive_count))
    elif positive_count > negative_count:
        return 0.3 + (0.1 * (positive_count - negative_count))
    else:
        return 0.0
```

---

## 3. COMPOSITE RISK SCORE CALCULATION (MASTER FORMULA)

```python
def calculate_country_risk_score(country_name):
    """
    MASTER FUNCTION: Computes composite risk score [0-100]
    
    Step 1: Collect raw data from all API sources
    Step 2: Normalize each component to [0-100]
    Step 3: Apply weighted formula
    Step 4: Determine risk category
    Step 5: Return formatted result
    """
    
    # Step 1: Data Collection
    try:
        # Fetch all required data
        weather_data = get_weather_data(country_name)
        macro_data = get_macroeconomic_data(country_name)
        exchange_data = get_exchange_rate_data(country_name)
        news_data = get_news_articles(country_name)
        
    except APIError as e:
        logger.error(f"API fetch failed for {country_name}: {e}")
        return fallback_risk_score(country_name)
    
    # Step 2: Calculate Sub-Component Risks
    weather_risk = calculate_weather_risk(weather_data)
    inflation_risk = calculate_inflation_risk(macro_data['inflation_rate'])
    currency_risk = calculate_currency_risk(exchange_data)
    news_risk = calculate_news_sentiment_risk(country_name, news_data)
    
    # Step 3: Composite Calculation
    weights = {
        'weather': 0.25,
        'inflation': 0.25,
        'currency': 0.30,
        'news': 0.20
    }
    
    composite_risk = (
        weights['weather'] * weather_risk +
        weights['inflation'] * inflation_risk +
        weights['currency'] * currency_risk +
        weights['news'] * news_risk
    )
    
    # Ensure range [0, 100]
    composite_risk = max(0, min(100, composite_risk))
    composite_risk = round(composite_risk, 2)
    
    # Step 4: Determine Category
    if composite_risk < 30:
        category = "Low Risk"
        color = "#28a745"
    elif composite_risk < 60:
        category = "Medium Risk"
        color = "#ffc107"
    else:
        category = "High Risk"
        color = "#dc3545"
    
    # Step 5: Format Response
    result = {
        'country': country_name,
        'composite_risk_score': composite_risk,
        'risk_category': category,
        'color_hex': color,
        'components': {
            'weather': round(weather_risk, 2),
            'inflation': round(inflation_risk, 2),
            'currency': round(currency_risk, 2),
            'news': round(news_risk, 2)
        },
        'breakdown': {
            'weather_factors': weather_data,
            'macro_factors': macro_data,
            'exchange_factors': exchange_data,
            'news_summary': f"{len(news_data)} articles in last 7 days"
        },
        'timestamp': datetime.datetime.utcnow().isoformat(),
        'recommendations': generate_recommendations(composite_risk, category)
    }
    
    return result
```

---

## 4. RECOMMENDATIONS ENGINE (berdasarkan Risk Score)

```python
def generate_recommendations(score, category):
    """
    Auto-generate actionable recommendations based on risk level
    """
    
    recommendations = {
        'Low Risk': [
            "✓ Proceed with standard import planning",
            "✓ Normal payment terms acceptable",
            "✓ Minimal supply chain contingencies needed"
        ],
        'Medium Risk': [
            "⚠ Monitor weather forecasts closely",
            "⚠ Consider currency hedging for large orders",
            "⚠ Build 5-10% buffer into delivery timeline",
            "⚠ Prepare alternative ports/routes"
        ],
        'High Risk': [
            "🔴 Defer non-urgent shipments if possible",
            "🔴 Implement currency swap or hedging contracts",
            "🔴 Activate alternative supplier networks",
            "🔴 Increase inventory buffer to 2-3 weeks",
            "🔴 Coordinate with logistics partner on contingencies"
        ]
    }
    
    return recommendations.get(category, [])
```

---

## 5. COMPARISON ENGINE (untuk Country Comparison Feature)

```python
def compare_countries(country_a_name, country_b_name):
    """
    Side-by-side comparison of two countries across 5 key metrics
    
    Returns:
      {
        "country_a": {...},
        "country_b": {...},
        "metrics": {
          "gdp": {...},
          "inflation": {...},
          "risk_score": {...},
          "weather": {...},
          "currency": {...}
        },
        "winner": {
          "gdp": "country_a",
          "inflation": "country_b",
          ...
        }
      }
    """
    
    # Fetch data for both countries
    data_a = get_country_dashboard_data(country_a_name)
    data_b = get_country_dashboard_data(country_b_name)
    
    comparison = {
        'countries': [country_a_name, country_b_name],
        'metrics': {
            'gdp_usd': {
                country_a_name: data_a['gdp'],
                country_b_name: data_b['gdp'],
                'winner': country_a_name if data_a['gdp'] > data_b['gdp'] else country_b_name,
                'difference_pct': abs(
                    (data_a['gdp'] - data_b['gdp']) / data_b['gdp'] * 100
                )
            },
            'inflation_percent': {
                country_a_name: data_a['inflation'],
                country_b_name: data_b['inflation'],
                'winner': country_a_name if data_a['inflation'] < data_b['inflation'] else country_b_name,
                'difference_pct': abs(
                    (data_a['inflation'] - data_b['inflation'])
                )
            },
            'risk_score': {
                country_a_name: data_a['risk_score'],
                country_b_name: data_b['risk_score'],
                'winner': country_a_name if data_a['risk_score'] < data_b['risk_score'] else country_b_name,
                'difference_pts': abs(
                    data_a['risk_score'] - data_b['risk_score']
                )
            },
            'weather_risk': {
                country_a_name: data_a['weather_risk'],
                country_b_name: data_b['weather_risk'],
                'winner': country_a_name if data_a['weather_risk'] < data_b['weather_risk'] else country_b_name
            },
            'currency_volatility': {
                country_a_name: data_a['currency_volatility'],
                country_b_name: data_b['currency_volatility'],
                'winner': country_a_name if data_a['currency_volatility'] < data_b['currency_volatility'] else country_b_name
            }
        },
        'timestamp': datetime.datetime.utcnow().isoformat()
    }
    
    return comparison
```

---

## KESIMPULAN FASE 2

**Risk Scoring Algorithm yang telah didefinisikan:**

1. ✅ **Weather Risk (25%)**: Temperature, precipitation, wind, severe warnings
2. ✅ **Inflation Risk (25%)**: Normalized against 20% threshold
3. ✅ **Currency Risk (30%)**: Volatility + price deviation calculation
4. ✅ **News Sentiment (20%)**: Multi-category aggregation
5. ✅ **Composite Formula**: Weighted average → 0-100 score
6. ✅ **Category Thresholds**: Low (<30), Medium (30-60), High (60+)
7. ✅ **Recommendations**: Auto-generated based on risk level
8. ✅ **Comparison Engine**: 5-metric side-by-side analysis

**Ready untuk FASE 3: Python Implementation**
