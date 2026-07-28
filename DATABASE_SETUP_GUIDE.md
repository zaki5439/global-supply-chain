# Database Setup Guide - PostgreSQL

## Quick Start

### 1. Install PostgreSQL

#### Windows
Download from: https://www.postgresql.org/download/windows/

```bash
# After installation, verify
psql --version
```

#### macOS
```bash
brew install postgresql
brew services start postgresql
```

#### Linux (Ubuntu)
```bash
sudo apt-get update
sudo apt-get install postgresql postgresql-contrib
sudo service postgresql start
```

### 2. Create Database

```bash
# Connect to PostgreSQL
psql -U postgres

# Inside psql console
CREATE DATABASE supply_chain;
CREATE USER supply_chain_user WITH PASSWORD 'your_secure_password';
GRANT ALL PRIVILEGES ON DATABASE supply_chain TO supply_chain_user;
\q
```

### 3. Set Environment Variables

Create `.env` file:

```
DATABASE_URL=postgresql://supply_chain_user:your_secure_password@localhost:5432/supply_chain
```

### 4. Initialize Database

```bash
python init_database.py
```

Expected output:
```
✓ Database connection successful
✓ All database tables created
✓ Table verification complete
✓ Seeded 8 sample countries
✓ Admin user created
✓ Database initialization complete!
```

---

## Database Schema (10 Tables)

### Table 1: users
```
id (PK)             - User identifier
username            - Unique username
email               - Unique email address
full_name           - User's full name
hashed_password     - Bcrypt hashed password
role                - admin|analyst|trader|viewer
is_active           - Active status
is_verified         - Email verified
created_at          - Account creation date
updated_at          - Last update
last_login          - Last login timestamp
```

### Table 2: countries
```
id (PK)             - Country identifier
name                - Country name (unique)
iso_code            - ISO 3166-1 alpha-2 code
iso_3166_2          - ISO 3166-1 alpha-3 code
region              - Geographic region
subregion           - Sub-region
latitude            - Country center latitude
longitude           - Country center longitude
capital_city        - Capital city name
population          - Population count
area                - Area in km²
currency_code       - ISO 4217 currency code
currency_name       - Currency name
timezone            - Primary timezone
languages           - JSON array of language codes
borders             - JSON array of bordering country codes
created_at          - Record creation date
updated_at          - Last update
```

### Table 3: macroeconomic_data
```
id (PK)             - Record identifier
country_id (FK)     - Reference to country
year                - Year of data
quarter             - Quarter (Q1-Q4)
gdp                 - GDP in USD billions
gdp_growth          - GDP growth percentage
inflation_rate      - Annual inflation %
unemployment_rate   - Unemployment %
trade_balance       - Trade balance in USD billions
imports             - Import value in USD billions
exports             - Export value in USD billions
foreign_debt        - Foreign debt in USD billions
created_at          - Record creation date
updated_at          - Last update
```

### Table 4: weather_data
```
id (PK)             - Record identifier
country_id (FK)     - Reference to country
date                - Date of weather data
temperature         - Temperature in Celsius
temperature_min     - Minimum temperature
temperature_max     - Maximum temperature
humidity            - Humidity percentage
precipitation       - Precipitation in mm
wind_speed          - Wind speed in m/s
wind_direction      - Wind direction (N,S,E,W)
weather_condition   - Clear|Cloudy|Rainy|Snowy
visibility          - Visibility in km
pressure            - Pressure in hPa
created_at          - Record creation date
```

### Table 5: ports
```
id (PK)             - Port identifier
country_id (FK)     - Reference to country
name                - Port name
port_code           - Port code (unique)
unlocode            - UN/LOCODE (unique)
latitude            - Port latitude
longitude           - Port longitude
port_type           - container|bulk|breakbulk|multipurpose|ro-ro
capacity            - Capacity in TEU (20ft containers)
container_capacity  - Container capacity
depth               - Port depth in meters
region              - Region within country
is_major            - Is major trading port
created_at          - Record creation date
updated_at          - Last update
```

### Table 6: news_articles
```
id (PK)             - Article identifier
country_id (FK)     - Reference to country
title               - Article title
content             - Article content
source              - News source
url                 - Article URL
category            - logistics|trade|shipping|economy
sentiment           - positive|neutral|negative
sentiment_score     - Sentiment score (-1.0 to 1.0)
impact_level        - low|medium|high
published_date      - Publication date
fetched_date        - When article was fetched
created_at          - Record creation date
```

### Table 7: risk_scores
```
id (PK)             - Score identifier
country_id (FK)     - Reference to country
date                - Score date
weather_risk        - Weather risk component (0-100)
inflation_risk      - Inflation risk component (0-100)
currency_risk       - Currency risk component (0-100)
news_sentiment_risk - News sentiment risk (0-100)
composite_score     - Composite risk score (0-100)
risk_level          - LOW|MEDIUM|HIGH
weather_weight      - Weight for weather (default 0.25)
inflation_weight    - Weight for inflation (default 0.25)
currency_weight     - Weight for currency (default 0.30)
news_weight         - Weight for news (default 0.20)
trend               - UP|DOWN|STABLE
volatility          - Standard deviation
recommendations     - JSON array of recommendations
created_at          - Record creation date
updated_at          - Last update
```

### Table 8: user_favorites
```
id (PK)             - Favorite identifier
user_id (FK)        - Reference to user
country_id (FK)     - Reference to country
added_date          - When added to favorites
notes               - User's notes
priority            - Sort priority
```

### Table 9: admin_articles
```
id (PK)             - Article identifier
title               - Article title
content             - Article content
author_id (FK)      - Reference to user (author)
category            - Article category
tags                - JSON array of tags
is_published        - Publication status
views               - View count
created_at          - Creation date
updated_at          - Last update
published_date      - Publication date
```

### Table 10: currency_historical_data
```
id (PK)             - Record identifier
country_id (FK)     - Reference to country
date                - Date of data
currency_code       - Currency code
exchange_rate_usd   - Exchange rate to USD
exchange_rate_eur   - Exchange rate to EUR
exchange_rate_gbp   - Exchange rate to GBP
volatility          - Daily % change
volume              - Trading volume
high                - Daily high
low                 - Daily low
open                - Opening rate
close               - Closing rate
created_at          - Record creation date
```

### Bonus Table: alerts
```
id (PK)             - Alert identifier
user_id (FK)        - Reference to user
country_id (FK)     - Reference to country
alert_type          - risk_spike|weather_warning|news_alert
title               - Alert title
message             - Alert message
severity            - low|medium|high|critical
is_read             - Read status
created_at          - Creation date
```

---

## Relationships

```
users
  ├─ 1:N → user_favorites
  └─ 1:N → alerts

countries
  ├─ 1:N → macroeconomic_data
  ├─ 1:N → weather_data
  ├─ 1:N → ports
  ├─ 1:N → news_articles
  ├─ 1:N → risk_scores
  └─ 1:N → currency_historical_data

user_favorites
  └─ N:1 ← countries

admin_articles
  └─ N:1 → users (author_id)
```

---

## Indexing Strategy

All foreign keys are indexed for fast joins:

```sql
CREATE INDEX idx_macro_country ON macroeconomic_data(country_id);
CREATE INDEX idx_weather_country ON weather_data(country_id);
CREATE INDEX idx_ports_country ON ports(country_id);
CREATE INDEX idx_news_country ON news_articles(country_id);
CREATE INDEX idx_risk_country ON risk_scores(country_id);
CREATE INDEX idx_currency_country ON currency_historical_data(country_id);
CREATE INDEX idx_favorites_user ON user_favorites(user_id);
CREATE INDEX idx_alerts_user ON alerts(user_id);

-- Date indexes for time-series queries
CREATE INDEX idx_weather_date ON weather_data(date);
CREATE INDEX idx_risk_date ON risk_scores(date);
CREATE INDEX idx_news_date ON news_articles(published_date);
CREATE INDEX idx_currency_date ON currency_historical_data(date);

-- Category/lookup indexes
CREATE INDEX idx_news_category ON news_articles(category);
CREATE INDEX idx_risk_level ON risk_scores(risk_level);
CREATE INDEX idx_port_type ON ports(port_type);
```

---

## Backup & Restore

### Backup Database

```bash
# Full database backup
pg_dump supply_chain > backup.sql

# With password
PGPASSWORD=password pg_dump -U supply_chain_user supply_chain > backup.sql

# Binary format (faster for large databases)
pg_dump -Fc supply_chain > backup.dump
```

### Restore Database

```bash
# From SQL file
psql supply_chain < backup.sql

# From binary dump
pg_restore -d supply_chain backup.dump
```

### Automated Daily Backups

```bash
#!/bin/bash
# backup.sh

BACKUP_DIR="/backups/supply_chain"
DATE=$(date +"%Y%m%d_%H%M%S")

mkdir -p $BACKUP_DIR

pg_dump -Fc supply_chain > $BACKUP_DIR/supply_chain_$DATE.dump

# Keep only last 30 days
find $BACKUP_DIR -name "*.dump" -mtime +30 -delete
```

---

## Common Queries

### Get country risk summary
```sql
SELECT 
  c.name,
  rs.composite_score,
  rs.risk_level,
  rs.weather_risk,
  rs.inflation_risk,
  rs.currency_risk,
  rs.news_sentiment_risk
FROM countries c
JOIN risk_scores rs ON c.id = rs.country_id
ORDER BY rs.date DESC
LIMIT 1 BY c.id;
```

### Get ports by country
```sql
SELECT name, latitude, longitude, port_type, capacity
FROM ports
WHERE country_id = (SELECT id FROM countries WHERE name = 'Singapore')
ORDER BY capacity DESC;
```

### User's favorite countries
```sql
SELECT c.name, c.region, rs.composite_score
FROM user_favorites uf
JOIN countries c ON uf.country_id = c.id
LEFT JOIN risk_scores rs ON c.id = rs.country_id
WHERE uf.user_id = 1
ORDER BY uf.added_date DESC;
```

### Recent news articles
```sql
SELECT c.name, title, source, sentiment, published_date
FROM news_articles na
JOIN countries c ON na.country_id = c.id
ORDER BY published_date DESC
LIMIT 10;
```

---

## Monitoring

### Database Size
```sql
SELECT 
  schemaname,
  tablename,
  pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) AS size
FROM pg_tables
WHERE schemaname = 'public'
ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC;
```

### Slow Queries
```sql
SELECT query, calls, total_time, mean_time
FROM pg_stat_statements
ORDER BY mean_time DESC
LIMIT 10;
```

### Active Connections
```sql
SELECT datname, usename, state, count(*)
FROM pg_stat_activity
GROUP BY datname, usename, state;
```

---

## Performance Tuning

### Connection Pooling (pgBouncer)

```ini
# pgbouncer.ini
[databases]
supply_chain = host=localhost port=5432 dbname=supply_chain

[pgbouncer]
pool_mode = transaction
max_client_conn = 1000
default_pool_size = 25
min_pool_size = 10
```

### Query Optimization

```sql
-- Use EXPLAIN to analyze queries
EXPLAIN ANALYZE
SELECT c.name, rs.composite_score
FROM countries c
JOIN risk_scores rs ON c.id = rs.country_id
WHERE rs.date > NOW() - INTERVAL '7 days'
ORDER BY rs.composite_score DESC;
```

---

## Troubleshooting

### Connection Refused
```bash
# Check PostgreSQL is running
sudo systemctl status postgresql

# Start PostgreSQL
sudo systemctl start postgresql
```

### Permission Denied
```sql
-- Grant privileges
GRANT ALL PRIVILEGES ON DATABASE supply_chain TO supply_chain_user;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO supply_chain_user;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO supply_chain_user;
```

### Disk Space Full
```sql
-- Find large tables
SELECT schemaname, tablename, pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename))
FROM pg_tables
ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC;
```

---

## Next Steps

1. ✅ Set up PostgreSQL database
2. Run init_database.py
3. Connect FastAPI backend to database
4. Populate with real data
5. Set up Redis caching
6. Implement user authentication

**Database is ready for production use!** 🚀
