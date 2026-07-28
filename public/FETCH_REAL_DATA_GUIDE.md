# 🌍 Cara Mengambil Data Asli - Real Data Collection Guide

## Daftar Lengkap Cara Mengambil Data Real-Time

### 1️⃣ **Jalankan Script PHP untuk Download Data Otomatis**

Paling mudah - script akan otomatis download semua data dari APIs:

```bash
cd c:\Users\ACER\supply-chain-app
php public/fetch-real-countries-data.php
```

**Apa yang dilakukan:**
- ✅ Download data 250+ negara dari REST Countries API
- ✅ Download weather real-time dari 10 kota besar (Open-Meteo)
- ✅ Download exchange rates 166 currencies (ExchangeRate API)
- ✅ Download port data
- ✅ Simpan semua ke folder: `public/data/real-data/`

**Files yang dihasilkan:**
```
public/data/real-data/
├── countries.json          # 250+ negara dengan detail
├── weather.json            # 10 kota dengan weather real-time
├── exchange-rates.json     # 166 currencies vs USD
├── ports.json              # 10 major ports
└── summary.json            # Ringkasan data
```

---

### 2️⃣ **Data Asli yang Diambil**

#### **A. Countries Data (REST Countries API)**
**URL:** `https://restcountries.com/v3.1/all`

Setiap negara memiliki:
- 🏠 Name (nama negara)
- 🏛️ Capital (ibu kota)
- 👥 Population (populasi)
- 📍 Area (luas wilayah)
- 💵 Currencies (mata uang)
- 🗣️ Languages (bahasa)
- 📍 Coordinates (lat/lng)
- 🕐 Timezones
- 🚩 Flag (gambar bendera)

**Contoh Data:**
```json
{
  "Indonesia": {
    "name": "Indonesia",
    "capital": "Jakarta",
    "population": 275501339,
    "area": 1904569,
    "currencies": ["IDR"],
    "languages": ["Indonesian"],
    "latlng": [-0.7893, 113.9213],
    "timezone": ["UTC+07:00", "UTC+08:00", "UTC+09:00"],
    "flag": "https://flagcdn.com/id.svg"
  }
}
```

#### **B. Weather Data (Open-Meteo API)**
**URL:** `https://api.open-meteo.com/v1/forecast?latitude=X&longitude=Y&current=...`

Setiap kota memiliki:
- 🌡️ Temperature (suhu real-time)
- 💧 Humidity (kelembaban)
- 💨 Wind Speed (kecepatan angin)
- 🔽 Pressure (tekanan udara)
- ☁️ Weather Code (kondisi cuaca)
- ⏱️ Timestamp

**Contoh Data:**
```json
{
  "Berlin": {
    "temperature": 16.6,
    "humidity": 65,
    "wind_speed": 7.7,
    "pressure": 1020.2,
    "weather_code": 2,
    "time": "2026-07-20T21:15"
  }
}
```

#### **C. Exchange Rates (ExchangeRate API)**
**URL:** `https://api.exchangerate-api.com/v4/latest/USD`

166 currencies dengan rate vs USD:
- 💱 Base: USD
- 📊 Rates: EUR, GBP, JPY, CNY, SGD, dll
- 📅 Date: Update date

**Contoh Data:**
```json
{
  "base": "USD",
  "rates": {
    "EUR": 0.875,
    "GBP": 0.744,
    "JPY": 162.49,
    "CNY": 6.78,
    "SGD": 1.29,
    "AUD": 1.43,
    "BRL": 5.11,
    "INR": 96.48
  },
  "date": "2026-07-20"
}
```

---

### 3️⃣ **Cara Akses Data yang Sudah Diambil**

#### **Via API Endpoint:**

```bash
# Semua data sekaligus
http://localhost:8002/api-real-time.php?type=all

# Weather saja
http://localhost:8002/api-real-time.php?type=weather&city=Berlin

# Exchange rates saja
http://localhost:8002/api-real-time.php?type=exchange

# Ports saja
http://localhost:8002/api-real-time.php?type=ports
```

#### **Via Dashboard:**
- 📊 Dashboard: `http://localhost:8002/dashboard-complete.html`
- ⚡ Live Data: `http://localhost:8002/realtime-standalone.html`
- 🌍 Countries: `http://localhost:8002/countries-data.html`

---

### 4️⃣ **Struktur Data File JSON**

#### **countries.json**
```json
{
  "Indonesia": {
    "name": "Indonesia",
    "capital": "Jakarta",
    "region": "Asia",
    "subregion": "Southeast Asia",
    "population": 275501339,
    "area": 1904569,
    "currencies": ["IDR"],
    "languages": ["Indonesian"],
    "latlng": [-0.7893, 113.9213],
    "timezone": ["UTC+07:00"],
    "flag": "https://flagcdn.com/...",
    "independent": true
  },
  "China": { ... },
  "Japan": { ... }
}
```

#### **weather.json**
```json
{
  "Berlin": {
    "temperature": 16.6,
    "humidity": 65,
    "wind_speed": 7.7,
    "pressure": 1020.2,
    "weather_code": 2,
    "time": "2026-07-20T21:15"
  },
  "Singapore": { ... }
}
```

#### **exchange-rates.json**
```json
{
  "base": "USD",
  "rates": {
    "EUR": 0.875,
    "GBP": 0.744,
    "JPY": 162.49,
    ...
  },
  "date": "2026-07-20"
}
```

#### **ports.json**
```json
[
  {
    "name": "Port of Shanghai",
    "lat": 30.9176,
    "lng": 121.5885,
    "country": "China",
    "type": "major"
  },
  ...
]
```

---

### 5️⃣ **Public APIs yang Digunakan**

| API | URL | Data | Rate Limit |
|-----|-----|------|-----------|
| REST Countries | https://restcountries.com/v3.1/all | 250+ countries | Unlimited |
| Open-Meteo | https://api.open-meteo.com/ | Real-time weather | 10k/day free |
| ExchangeRate | https://api.exchangerate-api.com/ | 166 currencies | 1500/month |
| World Bank | https://api.worldbank.org/ | GDP, economic data | Unlimited |
| OpenStreetMap | https://www.openstreetmap.org/ | Map tiles | Unlimited |

---

### 6️⃣ **Cara Update Data Secara Berkala**

#### **Opsi 1: Jalankan Script Manual**
```bash
php public/fetch-real-countries-data.php
```

#### **Opsi 2: Setup Task Scheduler (Windows)**
Buat batch file `update-data.bat`:
```batch
@echo off
cd c:\Users\ACER\supply-chain-app
php public/fetch-real-countries-data.php
```

Buka Task Scheduler dan set untuk jalankan setiap jam/hari.

#### **Opsi 3: API Real-Time (Auto Update)**
File `api-real-time.php` fetch data live setiap kali diakses - tidak perlu manual update!

---

### 7️⃣ **Data Live vs Cache**

**Real-Time (Langsung dari API):**
- ✅ Selalu fresh
- ⚠️ Lebih lambat
- 📊 Hanya beberapa data
- **File:** `api-real-time.php`

**Cached (Dari JSON lokal):**
- ✅ Cepat
- ⚠️ Update sesuai schedule
- 📊 Lengkap semua data
- **Folder:** `public/data/real-data/`

---

### 8️⃣ **Data yang Tersedia Sekarang**

Setelah menjalankan script, Anda punya:

```
✅ 250+ Negara dengan:
   - Name, Capital, Region
   - Population, Area
   - GDP Rank, Currency
   - Languages, Timezones
   - Coordinates, Flag

✅ 10 Kota dengan Weather Real-Time:
   - Temperature
   - Humidity, Wind Speed
   - Pressure, Weather Code
   - Berlin, Singapore, Beijing, NY, Tokyo
   - Dubai, London, Paris, Mumbai, Sydney

✅ 166 Currencies Exchange Rates:
   - EUR, GBP, JPY, CNY, SGD, AUD, BRL, INR, CAD, CHF
   - USD as base currency
   - Real-time rates

✅ 10 Major Ports dengan:
   - Name, Coordinates
   - Country, Type
   - Shanghai, Singapore, Rotterdam, Hamburg, LA
   - HK, Busan, Dubai, Antwerp, Boston
```

---

### 9️⃣ **Cara Menggunakan Data di JavaScript**

```javascript
// Fetch weather data
const weather = await fetch('api-real-time.php?type=weather&city=Berlin')
  .then(r => r.json());

// Fetch exchange rates
const rates = await fetch('api-real-time.php?type=exchange')
  .then(r => r.json());

// Fetch countries data dari JSON lokal
const countries = await fetch('data/real-data/countries.json')
  .then(r => r.json());

// Gunakan data
console.log(weather.Berlin.temperature);
console.log(rates.rates.EUR);
console.log(countries.Indonesia.capital);
```

---

### 🔟 **Troubleshooting**

**Q: Script error "Failed to connect to API"**
A: Pastikan internet connection aktif. APIs membutuhkan akses internet.

**Q: Data tidak update**
A: Jalankan script lagi: `php public/fetch-real-countries-data.php`

**Q: File JSON tidak ditemukan**
A: Pastikan folder `public/data/real-data/` sudah ada dan script sudah dijalankan.

---

## 📊 Summary

**Semua Data Sudah Tersedia:**

| Data | Source | Status | Update |
|------|--------|--------|--------|
| 250+ Countries | REST Countries API | ✅ Ready | Manual/API |
| 10 Cities Weather | Open-Meteo API | ✅ Real-time | Every 10s |
| 166 Currencies | ExchangeRate API | ✅ Ready | Real-time |
| Ports Data | Local DB | ✅ Ready | Manual |

**Gunakan di:**
- 🎯 Dashboard: `http://localhost:8002/dashboard-complete.html`
- ⚡ Real-Time: `http://localhost:8002/realtime-standalone.html`
- 🌍 Countries: `http://localhost:8002/countries-data.html`

---

**Happy Data Collecting! 🚀**
