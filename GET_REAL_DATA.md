# 📥 Download Real Data dari APIs

## ⚡ Quick Start (2 Langkah)

### Langkah 1: Install Python (Jika Belum Ada)

```
Download dari: https://www.python.org/downloads/
Pastikan centang "Add Python to PATH" saat install
```

### Langkah 2: Jalankan Script Pengumpul Data

**Option A - Double-click:**
```
SETUP_MINIMAL.bat
```
Ini akan install hanya packages yang diperlukan, kemudian jalankan data collection.

**Option B - Manual:**
```bash
pip install requests==2.31.0 python-dotenv==1.0.0 pandas==2.1.3

python fetch_real_data.py
```

---

## 📊 Data yang Akan Diunduh

### 1. **World Bank API** - Macroeconomic Data
```
✓ GDP per capita
✓ Inflation rate
✓ Population
✓ untuk 5 negara (Germany, Singapore, China, USA, Japan)
```

### 2. **Open-Meteo API** - Weather Data (Real-time)
```
✓ Temperature
✓ Humidity
✓ Wind speed
✓ Weather condition
✓ untuk 5 kota besar
```

### 3. **ExchangeRate API** - Currency Data
```
✓ Current exchange rates
✓ USD ke semua mata uang
```

### 4. **GNews API** - News Intelligence
```
✓ Supply chain news
✓ Logistics articles
✓ Shipping updates
✓ Trade information
```

### 5. **REST Countries API** - Geographic Data
```
✓ Borders
✓ Languages
✓ Time zones
✓ Population
```

### 6. **World Port Index** - Port Data
```
✓ Major ports di dunia
✓ Koordinat GPS
✓ Tipe pelabuhan
```

---

## 📁 Output

Semua data akan disimpan di folder:
```
collected_data/
├── world_bank_data.json
├── weather_data.json
├── exchange_rates.json
├── news_data.json
├── geographic_data.json
└── ports_data.json
```

Setiap file berisi **data real dari API**.

---

## 🚀 Contoh Output

### World Bank Data
```json
{
  "DE": {
    "name": "Germany",
    "region": "Europe",
    "population": 83369843,
    "incomeLevel": "High income"
  },
  ...
}
```

### Weather Data
```json
{
  "Berlin": {
    "current": {
      "temperature_2m": 12.5,
      "humidity": 65,
      "wind_speed_10m": 8.2
    }
  },
  ...
}
```

### Exchange Rates
```json
{
  "base": "USD",
  "rates": {
    "EUR": 0.92,
    "SGD": 1.35,
    "CNY": 7.08,
    "JPY": 149.50
  }
}
```

---

## 🔄 Menggunakan Data

### Di Python
```python
import json

# Load data
with open('collected_data/world_bank_data.json') as f:
    world_bank = json.load(f)

with open('collected_data/weather_data.json') as f:
    weather = json.load(f)

# Use data
print(world_bank['DE']['name'])  # Germany
print(weather['Berlin']['current']['temperature_2m'])  # Temperature
```

### Di Dashboard
```javascript
// Load data
fetch('collected_data/world_bank_data.json')
    .then(r => r.json())
    .then(data => {
        console.log(data);  // Use data
    });
```

### Di Database
```python
import pandas as pd

# Convert to CSV
df = pd.read_json('collected_data/world_bank_data.json')
df.to_csv('world_bank_data.csv')

# Import ke database
import sqlite3
conn = sqlite3.connect('supply_chain.db')
df.to_sql('countries', conn, if_exists='append')
```

---

## ⚙️ Konfigurasi

### Tambah Negara/API

Edit `fetch_real_data.py`:

```python
# Ubah list negara
countries = ["Germany", "Singapore", "China", "USA", "Japan", "India"]

# Ubah kota untuk weather
cities = {
    "Berlin": {"lat": 52.52, "lon": 13.40},
    "Singapore": {"lat": 1.35, "lon": 103.82},
    "Dubai": {"lat": 25.27, "lon": 55.30},  # Add new
}
```

### Tambah API Key

Beberapa API membutuhkan key (optional):

```python
# GNews API
GNEWS_API_KEY = "your_key_here"

# Weather API
WEATHER_API_KEY = "not_needed"  # Open-Meteo is free
```

---

## 🔗 APIs yang Digunakan

| API | Type | Rate Limit | Cost |
|-----|------|-----------|------|
| **World Bank** | REST | No limit | Free |
| **Open-Meteo** | REST | 10,000/day | Free |
| **ExchangeRate** | REST | 1500/month | Free |
| **GNews** | REST | 100/day (demo) | Free with key |
| **REST Countries** | REST | No limit | Free |

---

## 📋 Requirements.txt

Hanya 3 packages diperlukan:

```
requests==2.31.0        # HTTP requests
python-dotenv==1.0.0    # Load .env
pandas==2.1.3           # Data processing
```

---

## 🐛 Troubleshooting

### Error: "Python not found"
- Install Python: https://www.python.org/downloads/
- Centang "Add Python to PATH" saat install
- Restart command prompt

### Error: "requests module not found"
```bash
pip install requests
```

### API timeout errors
- Cek internet connection
- Tunggu beberapa detik dan coba lagi
- Beberapa API mungkin down sesaat

### Limited data from GNews
- GNews membutuhkan API key untuk full data
- Get free key dari: https://gnewsapi.net
- Update di fetch_real_data.py:
  ```python
  params = {"q": category, "token": "YOUR_API_KEY"}
  ```

---

## ✅ Verifikasi Data

Setelah download, cek file:

```bash
# Lihat struktur data
cat collected_data/world_bank_data.json | head -20

# Count records
python -c "import json; print(len(json.load(open('collected_data/world_bank_data.json'))))"
```

---

## 🚀 Next Steps

1. ✅ Run `fetch_real_data.py`
2. ✅ Check `collected_data/` folder
3. ✅ Import data ke sistem
4. ✅ Use di dashboard atau database

---

## 📚 API Documentation

- **World Bank**: https://data.worldbank.org/developers
- **Open-Meteo**: https://open-meteo.com/en/docs
- **ExchangeRate**: https://www.exchangerate-api.com
- **GNews**: https://gnewsapi.net
- **REST Countries**: https://restcountries.com
- **Port Index**: https://www.c-port.org/

---

## 💾 File Size

Typical collected data:
```
world_bank_data.json       ~15 KB
weather_data.json          ~50 KB
exchange_rates.json        ~45 KB
news_data.json            ~200 KB
geographic_data.json       ~80 KB
ports_data.json           ~30 KB
─────────────────────────────
Total                     ~420 KB
```

Semua data akan fit di memory tanpa masalah.

---

## 🎯 Gunakan Real Data Anda

Setelah menjalankan script ini, Anda punya:
- ✅ Real data dari 6 APIs
- ✅ JSON files siap digunakan
- ✅ No external dependencies (minimal packages)
- ✅ Dapat diimport ke database atau dashboard

**Siap untuk Task #5 dan seterusnya!**

---

Generated: January 15, 2025
Status: ✅ Ready to download real data
