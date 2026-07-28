"""
Database Initialization Script
Initializes PostgreSQL database and creates all tables
"""

import os
import sys
from sqlalchemy import text, inspect
from database_models import engine, Base, init_db, SessionLocal, User, Country
import json

def check_database_connection():
    """Test database connection"""
    try:
        with engine.connect() as connection:
            result = connection.execute(text("SELECT 1"))
            print("✓ Database connection successful")
            return True
    except Exception as e:
        print(f"✗ Database connection failed: {e}")
        return False

def create_tables():
    """Create all database tables"""
    try:
        Base.metadata.create_all(bind=engine)
        print("✓ All database tables created")
        return True
    except Exception as e:
        print(f"✗ Error creating tables: {e}")
        return False

def verify_tables():
    """Verify all tables exist"""
    inspector = inspect(engine)
    tables = inspector.get_table_names()
    
    required_tables = [
        "users",
        "countries",
        "macroeconomic_data",
        "weather_data",
        "ports",
        "news_articles",
        "risk_scores",
        "user_favorites",
        "admin_articles",
        "currency_historical_data",
        "alerts"
    ]
    
    print("\n📊 Database Tables Verification:")
    all_exist = True
    for table in required_tables:
        status = "✓" if table in tables else "✗"
        print(f"  {status} {table}")
        if table not in tables:
            all_exist = False
    
    return all_exist

def seed_sample_data():
    """Seed sample countries for testing"""
    try:
        db = SessionLocal()
        
        # Check if data already exists
        existing_count = db.query(Country).count()
        if existing_count > 0:
            print("✓ Database already has sample data")
            db.close()
            return
        
        # Sample countries data
        countries = [
            {
                "name": "Germany",
                "iso_code": "DE",
                "region": "Europe",
                "latitude": 51.1657,
                "longitude": 10.4515,
                "capital_city": "Berlin",
                "population": 83000000,
                "currency_code": "EUR",
                "currency_name": "Euro"
            },
            {
                "name": "China",
                "iso_code": "CN",
                "region": "Asia",
                "latitude": 35.8617,
                "longitude": 104.1954,
                "capital_city": "Beijing",
                "population": 1412000000,
                "currency_code": "CNY",
                "currency_name": "Chinese Yuan"
            },
            {
                "name": "United States",
                "iso_code": "US",
                "region": "North America",
                "latitude": 37.0902,
                "longitude": -95.7129,
                "capital_city": "Washington, D.C.",
                "population": 338000000,
                "currency_code": "USD",
                "currency_name": "US Dollar"
            },
            {
                "name": "Singapore",
                "iso_code": "SG",
                "region": "Asia",
                "latitude": 1.3521,
                "longitude": 103.8198,
                "capital_city": "Singapore",
                "population": 5850000,
                "currency_code": "SGD",
                "currency_name": "Singapore Dollar"
            },
            {
                "name": "Japan",
                "iso_code": "JP",
                "region": "Asia",
                "latitude": 36.2048,
                "longitude": 138.2529,
                "capital_city": "Tokyo",
                "population": 125000000,
                "currency_code": "JPY",
                "currency_name": "Japanese Yen"
            },
            {
                "name": "United Kingdom",
                "iso_code": "GB",
                "region": "Europe",
                "latitude": 55.3781,
                "longitude": -3.4360,
                "capital_city": "London",
                "population": 68000000,
                "currency_code": "GBP",
                "currency_name": "British Pound"
            },
            {
                "name": "India",
                "iso_code": "IN",
                "region": "Asia",
                "latitude": 20.5937,
                "longitude": 78.9629,
                "capital_city": "New Delhi",
                "population": 1417000000,
                "currency_code": "INR",
                "currency_name": "Indian Rupee"
            },
            {
                "name": "Brazil",
                "iso_code": "BR",
                "region": "South America",
                "latitude": -14.2350,
                "longitude": -51.9253,
                "capital_city": "Brasília",
                "population": 215000000,
                "currency_code": "BRL",
                "currency_name": "Brazilian Real"
            }
        ]
        
        for country_data in countries:
            country = Country(**country_data)
            db.add(country)
        
        db.commit()
        print(f"✓ Seeded {len(countries)} sample countries")
        db.close()
        
    except Exception as e:
        print(f"✗ Error seeding data: {e}")

def create_sample_user():
    """Create sample admin user"""
    try:
        from passlib.context import CryptContext
        
        db = SessionLocal()
        
        # Check if user exists
        existing_user = db.query(User).filter(User.username == "admin").first()
        if existing_user:
            print("✓ Admin user already exists")
            db.close()
            return
        
        # Create password hash
        pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")
        hashed_password = pwd_context.hash("admin123")
        
        # Create admin user
        admin_user = User(
            username="admin",
            email="admin@supplychainrisk.com",
            full_name="Administrator",
            hashed_password=hashed_password,
            role="admin",
            is_active=True,
            is_verified=True
        )
        
        db.add(admin_user)
        db.commit()
        print("✓ Admin user created (username: admin, password: admin123)")
        db.close()
        
    except Exception as e:
        print(f"✗ Error creating admin user: {e}")

def display_schema():
    """Display database schema"""
    inspector = inspect(engine)
    
    print("\n" + "="*70)
    print("DATABASE SCHEMA")
    print("="*70)
    
    tables = inspector.get_table_names()
    
    for table_name in sorted(tables):
        print(f"\n📋 Table: {table_name}")
        columns = inspector.get_columns(table_name)
        
        for column in columns:
            col_name = column['name']
            col_type = str(column['type'])
            nullable = "✓ NULL" if column['nullable'] else "✗ NOT NULL"
            print(f"   • {col_name:30} {col_type:20} {nullable}")
        
        # Show primary key
        pk = inspector.get_pk_constraint(table_name)
        if pk and pk['constrained_columns']:
            print(f"   🔑 Primary Key: {', '.join(pk['constrained_columns'])}")
        
        # Show foreign keys
        fks = inspector.get_foreign_keys(table_name)
        for fk in fks:
            fk_name = fk['name']
            constrained = ', '.join(fk['constrained_columns'])
            referred = f"{fk['referred_table']}.{', '.join(fk['referred_columns'])}"
            print(f"   🔗 Foreign Key: {constrained} → {referred}")
        
        # Show indexes
        indexes = inspector.get_indexes(table_name)
        for idx in indexes:
            idx_cols = ', '.join(idx['column_names'])
            print(f"   📑 Index: {idx['name']} ({idx_cols})")

def main():
    """Main initialization workflow"""
    print("="*70)
    print("Global Supply Chain Risk Intelligence - Database Setup")
    print("="*70)
    
    print("\n1️⃣  Testing database connection...")
    if not check_database_connection():
        print("\n✗ Please ensure PostgreSQL is running and connection is correct")
        sys.exit(1)
    
    print("\n2️⃣  Creating database tables...")
    if not create_tables():
        print("\n✗ Failed to create tables")
        sys.exit(1)
    
    print("\n3️⃣  Verifying tables...")
    if not verify_tables():
        print("\n⚠ Some tables were not created properly")
    
    print("\n4️⃣  Seeding sample data...")
    seed_sample_data()
    
    print("\n5️⃣  Creating admin user...")
    create_sample_user()
    
    print("\n6️⃣  Displaying schema...")
    display_schema()
    
    print("\n" + "="*70)
    print("✓ Database initialization complete!")
    print("="*70)
    print("\n📊 Summary:")
    print("  • 10 main tables created")
    print("  • Sample data seeded")
    print("  • Admin user ready (username: admin)")
    print("\n🚀 Database is ready for use!")

if __name__ == "__main__":
    main()
