"""
Flask API Server for Global Supply Chain Risk Intelligence Platform
====================================================================
Integrates Python backend with HTML frontend via REST API endpoints.
"""

from flask import Flask, jsonify, request, render_template, send_from_directory
from flask_cors import CORS
from flask_sqlalchemy import SQLAlchemy
from datetime import datetime, timedelta
import os
import json

# Import the existing platform
from supply_chain_risk_platform import SupplyChainRiskPlatform

# Initialize Flask app
app = Flask(__name__, static_folder='public', template_folder='public')
CORS(app)

# Database configuration
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///supply_chain.db'
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False
db = SQLAlchemy(app)

# Initialize the platform
platform = SupplyChainRiskPlatform(gnews_api_key=None)

# ============================================================================
# DATABASE MODELS
# ============================================================================

class User(db.Model):
    """User model for admin dashboard"""
    __tablename__ = 'users'
    
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(50), unique=True, nullable=False)
    email = db.Column(db.String(100), unique=True, nullable=False)
    role = db.Column(db.String(20), default='viewer')  # admin, editor, viewer
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    last_login = db.Column(db.DateTime)
    
    def to_dict(self):
        return {
            'id': self.id,
            'username': self.username,
            'email': self.email,
            'role': self.role,
            'created_at': self.created_at.isoformat() if self.created_at else None,
            'last_login': self.last_login.isoformat() if self.last_login else None
        }


class FavoriteCountry(db.Model):
    """Favorite countries for monitoring"""
    __tablename__ = 'favorite_countries'
    
    id = db.Column(db.Integer, primary_key=True)
    user_id = db.Column(db.Integer, db.ForeignKey('users.id'), nullable=True)
    country_name = db.Column(db.String(100), nullable=False)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    
    def to_dict(self):
        return {
            'id': self.id,
            'user_id': self.user_id,
            'country_name': self.country_name,
            'created_at': self.created_at.isoformat() if self.created_at else None
        }


class HistoricalData(db.Model):
    """Historical data for charts and trends"""
    __tablename__ = 'historical_data'
    
    id = db.Column(db.Integer, primary_key=True)
    country_name = db.Column(db.String(100), nullable=False)
    metric_type = db.Column(db.String(50), nullable=False)  # gdp, inflation, currency, risk_score
    value = db.Column(db.Float, nullable=False)
    recorded_at = db.Column(db.DateTime, default=datetime.utcnow)
    
    def to_dict(self):
        return {
            'id': self.id,
            'country_name': self.country_name,
            'metric_type': self.metric_type,
            'value': self.value,
            'recorded_at': self.recorded_at.isoformat() if self.recorded_at else None
        }


class Port(db.Model):
    """Port data for geospatial mapping"""
    __tablename__ = 'ports'
    
    id = db.Column(db.Integer, primary_key=True)
    name = db.Column(db.String(200), nullable=False)
    country = db.Column(db.String(100), nullable=False)
    latitude = db.Column(db.Float, nullable=False)
    longitude = db.Column(db.Float, nullable=False)
    port_type = db.Column(db.String(50))
    updated_at = db.Column(db.DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    
    def to_dict(self):
        return {
            'id': self.id,
            'name': self.name,
            'country': self.country,
            'latitude': self.latitude,
            'longitude': self.longitude,
            'port_type': self.port_type,
            'updated_at': self.updated_at.isoformat() if self.updated_at else None
        }


class Article(db.Model):
    """Analysis articles for admin dashboard"""
    __tablename__ = 'articles'
    
    id = db.Column(db.Integer, primary_key=True)
    title = db.Column(db.String(200), nullable=False)
    content = db.Column(db.Text, nullable=False)
    category = db.Column(db.String(50))
    author_id = db.Column(db.Integer, db.ForeignKey('users.id'))
    published = db.Column(db.Boolean, default=False)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    published_at = db.Column(db.DateTime)
    
    def to_dict(self):
        return {
            'id': self.id,
            'title': self.title,
            'content': self.content,
            'category': self.category,
            'author_id': self.author_id,
            'published': self.published,
            'created_at': self.created_at.isoformat() if self.created_at else None,
            'published_at': self.published_at.isoformat() if self.published_at else None
        }


# ============================================================================
# API ENDPOINTS - COUNTRY DASHBOARD
# ============================================================================

@app.route('/')
def index():
    """Serve the main dashboard HTML"""
    return send_from_directory('public', 'dashboard.html')


@app.route('/api/countries', methods=['GET'])
def get_all_countries():
    """Get all available countries from REST Countries API"""
    try:
        countries = []
        response = platform.countries_client.make_request(
            'https://restcountries.com/v3.1/all',
            params={'fields': 'name,cca2,cca3,region,subregion,latlng,capital,population,currencies'}
        )
        
        if response and len(response) > 0:
            for country in response:
                name = country.get('name', {}).get('common', '')
                if name:
                    countries.append({
                        'name': name,
                        'cca2': country.get('cca2', ''),
                        'cca3': country.get('cca3', ''),
                        'region': country.get('region', ''),
                        'subregion': country.get('subregion', ''),
                        'latlng': country.get('latlng', [0, 0]),
                        'capital': country.get('capital', [''])[0] if country.get('capital') else '',
                        'population': country.get('population', 0),
                        'currencies': country.get('currencies', {})
                    })
            countries.sort(key=lambda x: x['name'])
        
        return jsonify({
            'status': 'success',
            'count': len(countries),
            'data': countries
        })
    except Exception as e:
        return jsonify({'status': 'error', 'message': str(e)}), 500


@app.route('/api/country/<country_name>', methods=['GET'])
def get_country_dashboard(country_name):
    """Get comprehensive dashboard data for a country"""
    try:
        dashboard = platform.get_country_dashboard_data(country_name)
        
        if not dashboard:
            return jsonify({
                'status': 'error',
                'message': f'Country not found: {country_name}'
            }), 404
        
        # Convert to dictionary
        response = {
            'status': 'success',
            'data': {
                'country_name': dashboard.country_name,
                'iso_code': dashboard.iso_code,
                'capital': dashboard.capital,
                'population': dashboard.population,
                'currency': {
                    'code': dashboard.currency.currency_code,
                    'name': dashboard.currency.currency_name,
                    'exchange_rate_usd': dashboard.currency.exchange_rate_usd,
                    'volatility_30d': dashboard.currency.volatility_30d,
                    'trend': dashboard.currency.trend
                },
                'economic': {
                    'gdp_usd': dashboard.economic.gdp_usd,
                    'inflation_rate': dashboard.economic.inflation_rate,
                    'population': dashboard.economic.population,
                    'exports_usd': dashboard.economic.exports_usd,
                    'imports_usd': dashboard.economic.imports_usd
                },
                'weather': {
                    'temperature': dashboard.weather.temperature,
                    'precipitation': dashboard.weather.precipitation,
                    'wind_speed': dashboard.weather.wind_speed,
                    'condition': dashboard.weather.weather_condition,
                    'risk_score': dashboard.weather.risk_score
                },
                'news': [
                    {
                        'title': news.title,
                        'description': news.description,
                        'url': news.url,
                        'published_at': news.published_at,
                        'sentiment': news.sentiment_score,
                        'category': news.category
                    }
                    for news in dashboard.news
                ]
            }
        }
        
        # Store historical data
        store_historical_data(country_name, dashboard)
        
        return jsonify(response)
    
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500


@app.route('/api/risk/<country_name>', methods=['GET'])
def get_country_risk(country_name):
    """Calculate and return risk score for a country"""
    try:
        risk_string = platform.calculate_country_risk(country_name)
        
        if not risk_string:
            return jsonify({
                'status': 'error',
                'message': f'Cannot calculate risk for: {country_name}'
            }), 404
        
        # Parse the risk string
        # Format: "CountryName : Score (RiskCategory)"
        parts = risk_string.split(' : ')
        country = parts[0]
        score_part = parts[1].split(' (')
        score = int(score_part[0])
        category = score_part[1].rstrip(')')
        
        return jsonify({
            'status': 'success',
            'data': {
                'country_name': country,
                'risk_score': score,
                'risk_category': category
            }
        })
    
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500


@app.route('/api/compare', methods=['POST'])
def compare_countries():
    """Compare two countries across 5 key metrics"""
    try:
        data = request.get_json()
        country_a = data.get('country_a')
        country_b = data.get('country_b')
        
        if not country_a or not country_b:
            return jsonify({
                'status': 'error',
                'message': 'Both country_a and country_b are required'
            }), 400
        
        comparison = platform.compare_countries(country_a, country_b)
        
        if not comparison:
            return jsonify({
                'status': 'error',
                'message': 'Comparison failed'
            }), 404
        
        return jsonify({
            'status': 'success',
            'data': comparison
        })
    
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500


# ============================================================================
# API ENDPOINTS - FAVORITES
# ============================================================================

@app.route('/api/favorites', methods=['GET'])
def get_favorites():
    """Get all favorite countries"""
    try:
        favorites = FavoriteCountry.query.all()
        return jsonify({
            'status': 'success',
            'data': [fav.to_dict() for fav in favorites]
        })
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500


@app.route('/api/favorites', methods=['POST'])
def add_favorite():
    """Add a country to favorites"""
    try:
        data = request.get_json()
        country_name = data.get('country_name')
        
        if not country_name:
            return jsonify({
                'status': 'error',
                'message': 'country_name is required'
            }), 400
        
        # Check if already exists
        existing = FavoriteCountry.query.filter_by(country_name=country_name).first()
        if existing:
            return jsonify({
                'status': 'error',
                'message': 'Country already in favorites'
            }), 400
        
        favorite = FavoriteCountry(country_name=country_name)
        db.session.add(favorite)
        db.session.commit()
        
        return jsonify({
            'status': 'success',
            'data': favorite.to_dict()
        }), 201
    
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500


@app.route('/api/favorites/<int:id>', methods=['DELETE'])
def remove_favorite(id):
    """Remove a country from favorites"""
    try:
        favorite = FavoriteCountry.query.get_or_404(id)
        db.session.delete(favorite)
        db.session.commit()
        
        return jsonify({
            'status': 'success',
            'message': 'Favorite removed successfully'
        })
    
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500


# ============================================================================
# API ENDPOINTS - PORTS
# ============================================================================

@app.route('/api/ports', methods=['GET'])
def get_ports():
    """Get all ports or filter by search"""
    try:
        search = request.args.get('search', '')
        country = request.args.get('country', '')
        
        query = Port.query
        
        if search:
            query = query.filter(Port.name.ilike(f'%{search}%'))
        
        if country:
            query = query.filter(Port.country.ilike(f'%{country}%'))
        
        ports = query.all()
        
        return jsonify({
            'status': 'success',
            'data': [port.to_dict() for port in ports]
        })
    
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500


@app.route('/api/ports', methods=['POST'])
def add_port():
    """Add a new port (admin only)"""
    try:
        data = request.get_json()
        
        port = Port(
            name=data.get('name'),
            country=data.get('country'),
            latitude=data.get('latitude'),
            longitude=data.get('longitude'),
            port_type=data.get('port_type', 'general')
        )
        
        db.session.add(port)
        db.session.commit()
        
        return jsonify({
            'status': 'success',
            'data': port.to_dict()
        }), 201
    
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500


@app.route('/api/ports/<int:id>', methods=['DELETE'])
def delete_port(id):
    """Delete a port (admin only)"""
    try:
        port = Port.query.get_or_404(id)
        db.session.delete(port)
        db.session.commit()
        
        return jsonify({
            'status': 'success',
            'message': 'Port deleted successfully'
        })
    
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500


# ============================================================================
# API ENDPOINTS - HISTORICAL DATA
# ============================================================================

@app.route('/api/historical/<country_name>', methods=['GET'])
def get_historical_data(country_name):
    """Get historical data for a country"""
    try:
        metric_type = request.args.get('metric_type', 'all')
        days = request.args.get('days', 30)
        
        since_date = datetime.utcnow() - timedelta(days=int(days))
        
        query = HistoricalData.query.filter(
            HistoricalData.country_name == country_name,
            HistoricalData.recorded_at >= since_date
        )
        
        if metric_type != 'all':
            query = query.filter(HistoricalData.metric_type == metric_type)
        
        historical = query.order_by(HistoricalData.recorded_at.asc()).all()
        
        return jsonify({
            'status': 'success',
            'data': [h.to_dict() for h in historical]
        })
    
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500


# ============================================================================
# API ENDPOINTS - ADMIN DASHBOARD
# ============================================================================

@app.route('/api/admin/users', methods=['GET'])
def get_users():
    """Get all users (admin only)"""
    try:
        users = User.query.all()
        return jsonify({
            'status': 'success',
            'data': [user.to_dict() for user in users]
        })
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500


@app.route('/api/admin/users', methods=['POST'])
def create_user():
    """Create a new user (admin only)"""
    try:
        data = request.get_json()
        
        user = User(
            username=data.get('username'),
            email=data.get('email'),
            role=data.get('role', 'viewer')
        )
        
        db.session.add(user)
        db.session.commit()
        
        return jsonify({
            'status': 'success',
            'data': user.to_dict()
        }), 201
    
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500


@app.route('/api/admin/articles', methods=['GET'])
def get_articles():
    """Get all articles"""
    try:
        articles = Article.query.all()
        return jsonify({
            'status': 'success',
            'data': [article.to_dict() for article in articles]
        })
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500


@app.route('/api/admin/articles', methods=['POST'])
def create_article():
    """Create a new article"""
    try:
        data = request.get_json()
        
        article = Article(
            title=data.get('title'),
            content=data.get('content'),
            category=data.get('category'),
            author_id=data.get('author_id'),
            published=data.get('published', False)
        )
        
        if article.published:
            article.published_at = datetime.utcnow()
        
        db.session.add(article)
        db.session.commit()
        
        return jsonify({
            'status': 'success',
            'data': article.to_dict()
        }), 201
    
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500


@app.route('/api/admin/articles/<int:id>', methods=['PUT'])
def update_article(id):
    """Update an article"""
    try:
        article = Article.query.get_or_404(id)
        data = request.get_json()
        
        article.title = data.get('title', article.title)
        article.content = data.get('content', article.content)
        article.category = data.get('category', article.category)
        
        published = data.get('published')
        if published is not None and published != article.published:
            article.published = published
            if published:
                article.published_at = datetime.utcnow()
        
        db.session.commit()
        
        return jsonify({
            'status': 'success',
            'data': article.to_dict()
        })
    
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500


# ============================================================================
# UTILITY FUNCTIONS
# ============================================================================

def store_historical_data(country_name, dashboard):
    """Store current data as historical record"""
    try:
        # Store GDP
        if dashboard.economic.gdp_usd:
            gdp_hist = HistoricalData(
                country_name=country_name,
                metric_type='gdp',
                value=dashboard.economic.gdp_usd
            )
            db.session.add(gdp_hist)
        
        # Store inflation
        if dashboard.economic.inflation_rate:
            inf_hist = HistoricalData(
                country_name=country_name,
                metric_type='inflation',
                value=dashboard.economic.inflation_rate
            )
            db.session.add(inf_hist)
        
        # Store currency
        if dashboard.currency.exchange_rate_usd:
            curr_hist = HistoricalData(
                country_name=country_name,
                metric_type='currency',
                value=dashboard.currency.exchange_rate_usd
            )
            db.session.add(curr_hist)
        
        # Calculate and store risk score
        risk = platform.risk_engine.calculate_total_risk(
            dashboard.weather, dashboard.economic,
            dashboard.currency, dashboard.news
        )
        risk_hist = HistoricalData(
            country_name=country_name,
            metric_type='risk_score',
            value=risk.total_score
        )
        db.session.add(risk_hist)
        
        db.session.commit()
    except Exception as e:
        print(f"Error storing historical data: {e}")


def init_sample_data():
    """Initialize sample data for ports"""
    sample_ports = [
        {"name": "Port of Shanghai", "country": "China", "latitude": 31.2304, "longitude": 121.4737, "port_type": "container"},
        {"name": "Port of Singapore", "country": "Singapore", "latitude": 1.3521, "longitude": 103.8198, "port_type": "container"},
        {"name": "Port of Rotterdam", "country": "Netherlands", "latitude": 51.9225, "longitude": 4.47917, "port_type": "container"},
        {"name": "Port of Los Angeles", "country": "United States", "latitude": 33.7313, "longitude": -118.2824, "port_type": "container"},
        {"name": "Port of Hamburg", "country": "Germany", "latitude": 53.5511, "longitude": 9.9937, "port_type": "container"},
        {"name": "Port of Antwerp", "country": "Belgium", "latitude": 51.2605, "longitude": 4.4034, "port_type": "container"},
        {"name": "Port of Dubai", "country": "UAE", "latitude": 25.2048, "longitude": 55.0783, "port_type": "container"},
        {"name": "Port of Sydney", "country": "Australia", "latitude": -33.8688, "longitude": 151.2093, "port_type": "container"},
        {"name": "Port of Santos", "country": "Brazil", "latitude": -23.9629, "longitude": -46.3337, "port_type": "container"},
        {"name": "Port of Mumbai", "country": "India", "latitude": 19.0760, "longitude": 72.8777, "port_type": "container"},
        {"name": "Port of Jakarta", "country": "Indonesia", "latitude": -6.2088, "longitude": 106.8456, "port_type": "container"},
        {"name": "Port of Buenos Aires", "country": "Argentina", "latitude": -34.6037, "longitude": -58.3816, "port_type": "container"}
    ]
    
    if Port.query.count() == 0:
        for port_data in sample_ports:
            port = Port(**port_data)
            db.session.add(port)
        db.session.commit()
        print("Sample ports initialized")


# ============================================================================
# INITIALIZATION
# ============================================================================

if __name__ == '__main__':
    with app.app_context():
        db.create_all()
        init_sample_data()
    
    app.run(debug=True, host='0.0.0.0', port=5000)
