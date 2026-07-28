# 📥 Real Data Collection - Minimal Setup

## 🎯 Goal

Download **real data** from 6 APIs tanpa perlu menginstall sistem yang kompleks.

---

## ✅ Apa yang Dibutuhkan

Hanya 3 hal:
1. **Python 3.8+** (installed)
2. **Internet connection**
3. **2 menit waktu**

---

## 🚀 Jalankan Sekarang

### Option 1: Paling Mudah (Double-click)
```
MULAI.bat
```
Ini akan:
- ✅ Check Python
- ✅ Install 3 packages
- ✅ Download data dari 6 APIs
- ✅ Save ke folder `collected_data/`

### Option 2: Manual
```bash
pip install requests python-dotenv pandas

python fetch_real_data.py
```

---

## 📊 Data yang Dikumpulkan

### 1️⃣ World Bank (Macroeconomic)
```
- GDP
- Inflation
- Population
- Income Level
```
**Countries:** Germany, Singapore, China, USA, Japan

### 2️⃣ Open-Meteo (Weather)
```
- Temperature
- Humidity
- Wind Speed
- Condition
```
**Cities:** Berlin, Singapore, Beijing, New York, Tokyo

### 3️⃣ ExchangeRate API (Currency)
```
- All exchange rates vs USD
- EUR, SGD, CNY, JPY, dll
```

### 4️⃣ GNews (News)
```
- Supply chain news
- Logistics articles
- Shipping info
- Trade updates
```

### 5️⃣ REST Countries (Geographic)
```
- Borders
- Languages
- Time zones
- Area
```

### 6️⃣ Port Data (World Ports)
```
- Major ports globally
- Coordinates
- Type
- Country
```

---

## 📁 Output Structure

```
collected_data/
├── world_bank_data.json      (~15 KB)
├── weather_data.json         (~50 KB)
├── exchange_rates.json       (~45 KB)
├── news_data.json           (~200 KB)
├── geographic_data.json      (~80 KB)
└── ports_data.json          (~30 KB)

Total: ~420 KB
```

---

## 🔄 Menggunakan Data

### Python
```python
import json

# Load
with open('collected_data/world_bank_data.json') as f:
    data = json.load(f)

# Use
print(data['DE'])  # Germany data
```

### JavaScript
```javascript
fetch('collected_data/world_bank_data.json')
    .then(r => r.json())
    .then(data => console.log(data));
```

### CSV/Excel
```python
import pandas as pd

# Convert to CSV
for file in ['world_bank_data.json', 'weather_data.json']:
    df = pd.read_json(f'collected_data/{file}')
    df.to_csv(f'collected_data/{file.replace(".json", ".csv")}')
```

### Database
```python
import sqlite3
import json

conn = sqlite3.connect('supply_chain.db')

# Import World Bank data
with open('collected_data/world_bank_data.json') as f:
    data = json.load(f)
    for country, info in data.items():
        conn.execute(
            'INSERT INTO countries VALUES (?, ?, ?)',
            (country, info.get('name'), info.get('region'))
        )
conn.commit()
```

---

## 📋 Persyaratan Sistem

### Minimum
- Python 3.8+
- 500 MB free disk
- Internet connection

### Packages (auto-installed)
```
requests==2.31.0        # HTTP requests
python-dotenv==1.0.0    # Environment config
pandas==2.1.3           # Data manipulation
```

### Size
```
Script:         ~20 KB
Dependencies:   ~100 MB (installed globally)
Data:           ~420 KB (downloaded)
Total:          ~100.5 MB
```

---

## 🐛 Troubleshooting

### "Python not found"
**Solution:** Install from https://www.python.org/downloads/
- Centang "Add Python to PATH"
- Restart computer

### "pip not found"
**Solution:** 
```bash
python -m pip install --upgrade pip
```

### "requests module not found"
**Solution:**
```bash
pip install requests
```

### API timeout/error
**Solution:**
- Check internet connection
- Wait and retry (APIs might be temporarily down)
- Check `collected_data/` - some data may have been saved

### "Access denied" on collected_data
**Solution:** Delete folder and rerun:
```bash
rmdir /s collected_data
python fetch_real_data.py
```

---

## 🔑 Optional: Add API Keys

Untuk mendapatkan lebih banyak data, tambahkan API keys:

### GNews API Key
1. Daftar di: https://gnewsapi.net
2. Get free API key
3. Edit `fetch_real_data.py`:
```python
GNEWS_API_KEY = "your_key_here"
```

### Weather API (Optional)
Open-Meteo tidak butuh key, tapi untuk extended:
- https://open-meteo.com (free tier available)

---

## 📊 Sample Data

### world_bank_data.json
```json
{
  "DE": {
    "id": "DE",
    "name": "Germany",
    "region": {
      "id": "EUE",
      "value": "Europe"
    },
    "capitalCity": "Berlin",
    "latitude": "51.1657",
    "longitude": "10.4515",
    "incomeLevel": {
      "id": "HIC",
      "value": "High income"
    },
    "population": 83369843
  }
}
```

### weather_data.json
```json
{
  "Berlin": {
    "latitude": 52.5,
    "longitude": 13.4,
    "generationtime_ms": 0.5,
    "utc_offset_seconds": 3600,
    "current": {
      "time": "2025-01-15T10:30",
      "temperature_2m": 12.5,
      "relative_humidity_2m": 65,
      "weather_code": 1,
      "wind_speed_10m": 8.2
    }
  }
}
```

### exchange_rates.json
```json
{
  "base": "USD",
  "last_updated": "2025-01-15",
  "rates": {
    "EUR": 0.9203,
    "GBP": 0.7857,
    "JPY": 149.5,
    "CNY": 7.0825,
    "SGD": 1.3525
  }
}
```

---

## ⏱️ Timing

Typical execution:
```
pip install:    ~30 seconds
Data download:  ~60 seconds
Total:          ~2 minutes
```

Depending on internet speed and API availability.

---

## ✅ Verification

After running:

```bash
# Check files exist
dir collected_data

# Check file sizes
for %f in (collected_data\*) do echo %f %~zf

# Check JSON validity
python -m json.tool collected_data/world_bank_data.json > nul && echo ✓ Valid JSON
```

---

## 🎯 Next Steps

1. ✅ Run `MULAI.bat` or `python fetch_real_data.py`
2. ✅ Check `collected_data/` folder
3. ✅ Use data in your app/dashboard
4. ✅ Import to database if needed
5. ✅ Continue with next task

---

## 📚 API Documentation

- **World Bank**: https://data.worldbank.org/developers
- **Open-Meteo**: https://open-meteo.com/en/docs
- **ExchangeRate**: https://www.exchangerate-api.com/docs
- **GNews**: https://gnewsapi.net/api-keys
- **REST Countries**: https://restcountries.com
- **Ports**: https://www.c-port.org

---

## 💡 Tips

### Automate Updates
Create `update_data.bat`:
```batch
@echo off
python fetch_real_data.py
```

Schedule with Windows Task Scheduler to run daily.

### Cache Results
Data is saved as JSON - reuse without re-downloading:
```python
# Load cached data
with open('collected_data/world_bank_data.json') as f:
    data = json.load(f)
```

### Filter Data
```python
# Get specific country
germany = data.get('DE')

# Filter by region
european_countries = {k: v for k, v in data.items() 
                     if 'Europe' in str(v)}
```

---

## 🚀 Ready?

**Run:**
```
MULAI.bat
```

**Or manually:**
```bash
python fetch_real_data.py
```

**Data will be in:**
```
collected_data/
```

---

## 📝 License

All data collected from public APIs. Check individual API terms of service.

---

**Generated:** January 15, 2025  
**Status:** ✅ Ready for minimal data collection  
**Size:** ~420 KB total data
