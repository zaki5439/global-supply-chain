# Real-Time News Setup Guide

## 🔴 PENTING: Update GNews API Key

Saat ini menggunakan `GNEWS_API_KEY=demo` yang hanya menampilkan data dummy.  
Untuk mendapatkan **berita real-time yang sebenarnya**, ikuti langkah-langkah di bawah:

---

## 📋 Langkah 1: Daftar GNews API (Gratis)

1. Kunjungi: **https://gnews.io**
2. Klik "Get Free API Key"
3. Isi form dengan email Anda
4. Verifikasi email Anda
5. Anda akan menerima **API Key gratis** (100 requests/hari)

**Paket Gratis GNews:**
- ✓ 100 requests per hari
- ✓ Maksimal 10 artikel per request
- ✓ 300 hari history
- ✓ Real-time news dari 8000+ sumber
- ✓ Support untuk 70+ bahasa

---

## 🔧 Langkah 2: Update .env File

**File:** `.env`

```env
# Ganti ini:
GNEWS_API_KEY=demo

# Dengan API key Anda:
GNEWS_API_KEY=YOUR_ACTUAL_API_KEY_HERE
```

**Contoh:**
```env
GNEWS_API_KEY=a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6
```

---

## 🔄 Langkah 3: Clear Cache

```bash
php artisan cache:clear
php artisan config:clear
```

---

## ✅ Langkah 4: Verifikasi Real-Time News

### Option A: Via Browser
```
http://127.0.0.1:8000/news
```
- Pilih negara dari dropdown
- Klik tab "Country News" atau "Global Supply Chain"
- Lihat berita real-time dengan timestamp terbaru

### Option B: Via API
```bash
# Test News API
curl "http://127.0.0.1:8000/api/news?country=Indonesia&max=10"

# Test Global News
curl "http://127.0.0.1:8000/api/news/global?max=15"
```

### Option C: Artisan Tinker
```bash
php artisan tinker

# Kemudian di dalam tinker:
$service = app(\App\Services\NewsService::class);
$news = $service->getNewsByCountry('Indonesia', 5);
echo count($news) . " articles fetched";
```

---

## 📊 Expected Output (Real-Time)

Setelah update API key, Anda akan melihat:

```json
{
  "status": "success",
  "data": [
    {
      "id": "hash123",
      "title": "Breaking: Indonesia Port Implements New Supply Chain Protocol",
      "description": "A comprehensive new protocol has been...",
      "url": "https://realnewssite.com/article",
      "image": "https://realnewssite.com/image.jpg",
      "source": "Reuters",
      "published_at": "2026-07-20T09:30:00Z",
      "published_at_human": "3 hours ago",
      "sentiment_label": "positive",
      "sentiment_score": 45,
      "sentiment_icon": "bi-emoji-smile"
    },
    ...
  ],
  "count": 10,
  "timestamp": "2026-07-20T10:35:17+00:00"
}
```

**Ciri-ciri Real-Time News:**
- ✓ Timestamp sangat baru (jam/menit ini)
- ✓ URL valid mengarah ke situs berita asli
- ✓ Images dari sumber asli
- ✓ Source dari Reuters, AP, Bloomberg, dll (bukan "Demo")
- ✓ Judul berita berbeda setiap kali request
- ✓ Sentiment score bervariasi (-100 sampai +100)

---

## 🔍 Verifikasi Cache System

NewsService menggunakan 2-tier caching:

1. **Database Cache (6-jam TTL)**
   ```php
   'expires_at' => now()->addHours(6)
   ```
   - Mengurangi API calls
   - Data tetap fresh selama 6 jam
   - Otomatis expire dan refetch

2. **Browser Cache (Dynamic)**
   - Setiap request ke `/api/news` mengecek cache
   - Jika cache expired → fetch fresh data dari GNews
   - Jika valid → return cached data

---

## 📝 Testing Checklist

Setelah update API key, verifikasi:

- [ ] `.env` sudah update dengan API key
- [ ] `php artisan cache:clear` sudah dijalankan
- [ ] Server masih running (`http://127.0.0.1:8000` accessible)
- [ ] Dashboard page loading dengan normal
- [ ] News page menampilkan artikel real
- [ ] Sentiment labels ada (positive/negative/neutral)
- [ ] Timestamps menunjukkan waktu terbaru
- [ ] URLs bisa diklik dan valid

---

## 🚨 Troubleshooting

### Problem: "Masih melihat data Demo"

**Solution:**
```bash
# 1. Clear semua cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# 2. Restart server
php artisan serve
```

### Problem: "API Key Invalid"

**Check:**
- API key sudah benar di `.env`?
- Belum expired?
- Tidak ada spasi atau karakter extra?

**Solution:**
- Regenerate API key di https://gnews.io/dashboard
- Update `.env` dengan key baru
- Clear cache

### Problem: "Quota Exceeded"

GNews gratis = 100 requests/hari

**Solution:**
- Tunggu sampai jam 00:00 UTC (reset daily)
- Atau upgrade ke paket berbayar
- Cache berfungsi untuk mengurangi API calls

---

## 🎯 Konfigurasi Lanjutan

### Ubah Cache Duration
**File:** `app/Services/NewsService.php`

```php
protected $cacheDuration = 6; // ubah ke nilai lain (jam)
```

### Ubah Default Max Results
**File:** `app/Services/NewsService.php`

```php
public function getNewsByCountry(string $countryName, int $maxResults = 10): array
// ubah 10 ke nilai lain
```

### Tambah Keyword Search
**File:** `app/Services/NewsService.php`

```php
$keyword = "supply chain logistics economy {$countryName}";
// tambah keyword lain sesuai kebutuhan
```

---

## 📱 Live Demo URLs

Setelah setup selesai:

- **Dashboard:** http://127.0.0.1:8000/dashboard
- **News Page:** http://127.0.0.1:8000/news
- **Countries API:** http://127.0.0.1:8000/api/countries
- **News API:** http://127.0.0.1:8000/api/news?country=Indonesia
- **Global News API:** http://127.0.0.1:8000/api/news/global

---

## ✨ Fitur Real-Time News

✅ **Real-Time Data** - Berita dari 8000+ sumber global  
✅ **Sentiment Analysis** - AI-powered positive/negative/neutral  
✅ **Multi-Country** - Support 196 negara  
✅ **Caching Strategy** - 6-hour TTL untuk performa optimal  
✅ **Error Handling** - Fallback graceful jika API down  
✅ **Responsive UI** - Modern dashboard dengan real-time updates  

---

## 📞 Support

Jika ada masalah:

1. Cek `.env` file sudah benar
2. Clear cache: `php artisan cache:clear`
3. Check GNews API status: https://gnews.io/status
4. Review logs: `storage/logs/laravel.log`

---

**Status:** ✅ Ready for Real-Time News  
**Last Updated:** 2026-07-20  
**API:** GNews.io  
**Cache:** 6 hours  
