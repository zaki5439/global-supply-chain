"""
SQLAlchemy Database Models
Global Supply Chain Risk Intelligence Platform
10 normalized tables with proper relationships
"""

from sqlalchemy import create_engine, Column, Integer, String, Float, DateTime, Text, ForeignKey, Boolean, JSON
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import relationship, sessionmaker
from datetime import datetime
import os

# Database Configuration
DATABASE_URL = os.getenv(
    "DATABASE_URL",
    "postgresql://postgres:password@localhost:5432/supply_chain"
)

engine = create_engine(DATABASE_URL, echo=True)
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)
Base = declarative_base()

# ============================================
# TABLE 1: Users
# ============================================
class User(Base):
    __tablename__ = "users"
    
    id = Column(Integer, primary_key=True, index=True)
    username = Column(String(100), unique=True, index=True, nullable=False)
    email = Column(String(120), unique=True, index=True, nullable=False)
    full_name = Column(String(255))
    hashed_password = Column(String(255), nullable=False)
    role = Column(String(50), default="user")  # admin, analyst, trader, viewer
    is_active = Column(Boolean, default=True)
    is_verified = Column(Boolean, default=False)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    last_login = Column(DateTime)
    
    # Relationships
    favorites = relationship("UserFavorite", back_populates="user")
    alerts = relationship("Alert", back_populates="user")
    
    def __repr__(self):
        return f"<User {self.username}>"

# ============================================
# TABLE 2: Countries
# ============================================
class Country(Base):
    __tablename__ = "countries"
    
    id = Column(Integer, primary_key=True, index=True)
    name = Column(String(100), unique=True, index=True, nullable=False)
    iso_code = Column(String(3), unique=True, index=True)
    iso_3166_2 = Column(String(5), unique=True)
    region = Column(String(100), index=True)
    subregion = Column(String(100))
    latitude = Column(Float)
    longitude = Column(Float)
    capital_city = Column(String(100))
    population = Column(Integer)
    area = Column(Float)
    currency_code = Column(String(3))
    currency_name = Column(String(100))
    timezone = Column(String(50))
    languages = Column(JSON)  # Array of language codes
    borders = Column(JSON)   # Array of bordering country codes
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    
    # Relationships
    macroeconomic_data = relationship("MacroeconomicData", back_populates="country", cascade="all, delete-orphan")
    weather_data = relationship("WeatherData", back_populates="country", cascade="all, delete-orphan")
    ports = relationship("Port", back_populates="country", cascade="all, delete-orphan")
    news_articles = relationship("NewsArticle", back_populates="country", cascade="all, delete-orphan")
    risk_scores = relationship("RiskScore", back_populates="country", cascade="all, delete-orphan")
    currency_data = relationship("CurrencyHistoricalData", back_populates="country", cascade="all, delete-orphan")
    
    def __repr__(self):
        return f"<Country {self.name}>"

# ============================================
# TABLE 3: Macroeconomic Data
# ============================================
class MacroeconomicData(Base):
    __tablename__ = "macroeconomic_data"
    
    id = Column(Integer, primary_key=True, index=True)
    country_id = Column(Integer, ForeignKey("countries.id"), index=True, nullable=False)
    year = Column(Integer, index=True)
    quarter = Column(Integer)  # Q1-Q4
    gdp = Column(Float)  # USD Billions
    gdp_growth = Column(Float)  # Percentage
    inflation_rate = Column(Float)  # Annual percentage
    unemployment_rate = Column(Float)  # Percentage
    trade_balance = Column(Float)  # USD Billions
    imports = Column(Float)  # USD Billions
    exports = Column(Float)  # USD Billions
    foreign_debt = Column(Float)  # USD Billions
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    
    # Relationships
    country = relationship("Country", back_populates="macroeconomic_data")
    
    def __repr__(self):
        return f"<MacroeconomicData {self.country_id} {self.year}>"

# ============================================
# TABLE 4: Weather Data
# ============================================
class WeatherData(Base):
    __tablename__ = "weather_data"
    
    id = Column(Integer, primary_key=True, index=True)
    country_id = Column(Integer, ForeignKey("countries.id"), index=True, nullable=False)
    date = Column(DateTime, index=True)
    temperature = Column(Float)  # Celsius
    temperature_min = Column(Float)
    temperature_max = Column(Float)
    humidity = Column(Float)  # Percentage
    precipitation = Column(Float)  # mm
    wind_speed = Column(Float)  # m/s
    wind_direction = Column(String(10))  # N, S, E, W, etc
    weather_condition = Column(String(50))  # Clear, Cloudy, Rainy, Snowy
    visibility = Column(Float)  # km
    pressure = Column(Float)  # hPa
    created_at = Column(DateTime, default=datetime.utcnow)
    
    # Relationships
    country = relationship("Country", back_populates="weather_data")
    
    def __repr__(self):
        return f"<WeatherData {self.country_id} {self.date}>"

# ============================================
# TABLE 5: Ports
# ============================================
class Port(Base):
    __tablename__ = "ports"
    
    id = Column(Integer, primary_key=True, index=True)
    country_id = Column(Integer, ForeignKey("countries.id"), index=True, nullable=False)
    name = Column(String(255), index=True, nullable=False)
    port_code = Column(String(10), unique=True, index=True)
    unlocode = Column(String(10), unique=True)  # UN/LOCODE
    latitude = Column(Float, nullable=False)
    longitude = Column(Float, nullable=False)
    port_type = Column(String(50), index=True)  # container, bulk, breakbulk, multipurpose, ro-ro
    capacity = Column(Float)  # TEU (Twenty-foot Equivalent Units)
    container_capacity = Column(Float)
    depth = Column(Float)  # meters
    region = Column(String(100))
    is_major = Column(Boolean, default=False)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    
    # Relationships
    country = relationship("Country", back_populates="ports")
    
    def __repr__(self):
        return f"<Port {self.name}>"

# ============================================
# TABLE 6: News Articles
# ============================================
class NewsArticle(Base):
    __tablename__ = "news_articles"
    
    id = Column(Integer, primary_key=True, index=True)
    country_id = Column(Integer, ForeignKey("countries.id"), index=True)
    title = Column(String(500), nullable=False)
    content = Column(Text)
    source = Column(String(200), index=True)
    url = Column(String(500))
    category = Column(String(50), index=True)  # logistics, trade, shipping, economy
    sentiment = Column(String(20), index=True)  # positive, neutral, negative
    sentiment_score = Column(Float)  # -1.0 to 1.0
    impact_level = Column(String(20))  # low, medium, high
    published_date = Column(DateTime, index=True)
    fetched_date = Column(DateTime, default=datetime.utcnow)
    created_at = Column(DateTime, default=datetime.utcnow)
    
    # Relationships
    country = relationship("Country", back_populates="news_articles")
    
    def __repr__(self):
        return f"<NewsArticle {self.title[:50]}>"

# ============================================
# TABLE 7: Risk Scores
# ============================================
class RiskScore(Base):
    __tablename__ = "risk_scores"
    
    id = Column(Integer, primary_key=True, index=True)
    country_id = Column(Integer, ForeignKey("countries.id"), index=True, nullable=False)
    date = Column(DateTime, index=True, default=datetime.utcnow)
    
    # Individual risk components (0-100)
    weather_risk = Column(Float)
    inflation_risk = Column(Float)
    currency_risk = Column(Float)
    news_sentiment_risk = Column(Float)
    
    # Composite score and categorization
    composite_score = Column(Float, index=True)  # 0-100
    risk_level = Column(String(20), index=True)  # LOW, MEDIUM, HIGH
    
    # Component weights
    weather_weight = Column(Float, default=0.25)
    inflation_weight = Column(Float, default=0.25)
    currency_weight = Column(Float, default=0.30)
    news_weight = Column(Float, default=0.20)
    
    # Additional metrics
    trend = Column(String(20))  # UP, DOWN, STABLE
    volatility = Column(Float)  # Standard deviation
    recommendations = Column(JSON)  # Array of mitigation recommendations
    
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    
    # Relationships
    country = relationship("Country", back_populates="risk_scores")
    
    def __repr__(self):
        return f"<RiskScore {self.country_id} {self.composite_score}>"

# ============================================
# TABLE 8: User Favorites
# ============================================
class UserFavorite(Base):
    __tablename__ = "user_favorites"
    
    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey("users.id"), index=True, nullable=False)
    country_id = Column(Integer, ForeignKey("countries.id"), index=True, nullable=False)
    added_date = Column(DateTime, default=datetime.utcnow)
    notes = Column(String(500))  # User's custom notes
    priority = Column(Integer, default=0)  # For sorting
    
    # Relationships
    user = relationship("User", back_populates="favorites")
    
    def __repr__(self):
        return f"<UserFavorite user={self.user_id} country={self.country_id}>"

# ============================================
# TABLE 9: Admin Articles
# ============================================
class AdminArticle(Base):
    __tablename__ = "admin_articles"
    
    id = Column(Integer, primary_key=True, index=True)
    title = Column(String(500), nullable=False)
    content = Column(Text, nullable=False)
    author_id = Column(Integer, ForeignKey("users.id"))
    category = Column(String(100), index=True)
    tags = Column(JSON)  # Array of tags
    is_published = Column(Boolean, default=False)
    views = Column(Integer, default=0)
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    published_date = Column(DateTime)
    
    def __repr__(self):
        return f"<AdminArticle {self.title[:50]}>"

# ============================================
# TABLE 10: Currency Historical Data
# ============================================
class CurrencyHistoricalData(Base):
    __tablename__ = "currency_historical_data"
    
    id = Column(Integer, primary_key=True, index=True)
    country_id = Column(Integer, ForeignKey("countries.id"), index=True, nullable=False)
    date = Column(DateTime, index=True)
    currency_code = Column(String(3), index=True)
    exchange_rate_usd = Column(Float, nullable=False)  # Against USD
    exchange_rate_eur = Column(Float)  # Against EUR
    exchange_rate_gbp = Column(Float)  # Against GBP
    volatility = Column(Float)  # Daily percentage change
    volume = Column(Float)  # Trading volume
    high = Column(Float)  # Daily high
    low = Column(Float)   # Daily low
    open = Column(Float)  # Opening rate
    close = Column(Float) # Closing rate
    created_at = Column(DateTime, default=datetime.utcnow)
    
    # Relationships
    country = relationship("Country", back_populates="currency_data")
    
    def __repr__(self):
        return f"<CurrencyHistoricalData {self.currency_code} {self.date}>"

# ============================================
# ADDITIONAL TABLES FOR MONITORING
# ============================================

class Alert(Base):
    __tablename__ = "alerts"
    
    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey("users.id"), index=True, nullable=False)
    country_id = Column(Integer, ForeignKey("countries.id"), index=True)
    alert_type = Column(String(50))  # risk_spike, weather_warning, news_alert
    title = Column(String(255))
    message = Column(Text)
    severity = Column(String(20))  # low, medium, high, critical
    is_read = Column(Boolean, default=False)
    created_at = Column(DateTime, default=datetime.utcnow)
    
    # Relationships
    user = relationship("User", back_populates="alerts")
    
    def __repr__(self):
        return f"<Alert {self.title}>"

# ============================================
# DATABASE INITIALIZATION
# ============================================

def init_db():
    """Initialize database tables"""
    Base.metadata.create_all(bind=engine)
    print("✓ Database tables created successfully")

def drop_db():
    """Drop all tables (for testing)"""
    Base.metadata.drop_all(bind=engine)
    print("✓ All tables dropped")

if __name__ == "__main__":
    init_db()
