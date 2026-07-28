# News & Sentiment Analysis API Documentation

## Overview

Endpoint ini mengintegrasikan **GNews API** dengan **Lexicon-Based Sentiment Analysis** untuk memberikan real-time news dengan analisis sentimen untuk risk intelligence platform.

## Endpoint

### GET `/api/news`

Fetch berita terkait negara dengan analisis sentimen otomatis.

#### Request Parameters

| Parameter | Type   | Required | Description                    | Example       |
|-----------|--------|----------|--------------------------------|---------------|
| `country` | string | Yes      | Nama negara untuk pencarian     | `Indonesia`   |

#### Query Example

```bash
GET http://127.0.0.1:8000/api/news?country=Indonesia
```

#### Response Format - Success (200 OK)

```json
{
  "status": "success",
  "data": [
    {
      "title": "Indonesia's Port Authority Announces New Shipping Protocols",
      "source": "Maritime News Daily",
      "url": "https://example.com/article/123",
      "published_at": "2026-07-20",
      "description": "The port authority in Jakarta announced new shipping protocols...",
      "image_url": "https://example.com/image.jpg",
      "sentiment_analysis": {
        "positive_score": 3,
        "negative_score": 1,
        "sentiment": "Positive",
        "breakdown": {
          "positive": 75,
          "neutral": 15,
          "negative": 10
        }
      }
    },
    {
      "title": "Trade Tensions Impact Suez Canal Traffic",
      "source": "Global Trade Monitor",
      "url": "https://example.com/article/456",
      "published_at": "2026-07-19",
      "description": "Recent geopolitical tensions have impacted...",
      "image_url": null,
      "sentiment_analysis": {
        "positive_score": 1,
        "negative_score": 4,
        "sentiment": "Negative",
        "breakdown": {
          "positive": 20,
          "neutral": 30,
          "negative": 50
        }
      }
    }
  ],
  "count": 2,
  "country": "Indonesia",
  "timestamp": "2026-07-20T10:30:45.000000Z"
}
```

#### Response Format - No News Found (200 OK)

```json
{
  "status": "success",
  "data": [],
  "message": "No news found for the requested country",
  "count": 0,
  "country": "UnknownCountry",
  "timestamp": "2026-07-20T10:30:45.000000Z"
}
```

#### Response Format - Missing Parameter (400 Bad Request)

```json
{
  "status": "error",
  "message": "Country parameter is required",
  "code": "MISSING_PARAMETER"
}
```

#### Response Format - Invalid Parameter (400 Bad Request)

```json
{
  "status": "error",
  "message": "Country name is too long",
  "code": "INVALID_PARAMETER"
}
```

#### Response Format - API Quota Exceeded (429 Too Many Requests)

```json
{
  "status": "error",
  "message": "GNews API quota exceeded. Please try again in a few moments.",
  "code": "QUOTA_EXCEEDED"
}
```

#### Response Format - Server Error (500 Internal Server Error)

```json
{
  "status": "error",
  "message": "An error occurred while fetching news. Please try again later.",
  "code": "SERVER_ERROR"
}
```

## Search Query Logic

Query yang dikirim ke GNews API dibangun secara otomatis:

```
{Country} AND (logistics OR trade OR shipping OR economy OR port OR cargo)
```

**Contoh untuk Indonesia:**
```
Indonesia AND (logistics OR trade OR shipping OR economy OR port OR cargo)
```

Ini memastikan hasil berita relevan dengan supply chain dan ekonomi.

## Sentiment Analysis Algorithm

### Proses

1. **Text Preprocessing**: Hapus semua karakter non-alfabet, konversi ke lowercase
2. **Tokenization**: Pisahkan teks menjadi kata-kata individual
3. **Lexicon Matching**: Bandingkan setiap kata dengan database `positive_words` dan `negative_words`
4. **Score Calculation**:
   - **positive_score**: Jumlah kata positif yang ditemukan
   - **negative_score**: Jumlah kata negatif yang ditemukan
   - **sentiment**: Klasifikasi berdasarkan perbandingan score
   - **breakdown**: Persentase komposisi sentimen (positive, neutral, negative)

### Klasifikasi Sentimen

| Kondisi | Label |
|---------|-------|
| `positive_score > negative_score + 1` | **Positive** |
| `negative_score > positive_score + 1` | **Negative** |
| Lainnya | **Neutral** |

### Contoh Analisis

**Text**: "The logistics company reported strong growth but faces inflation challenges"

1. **Words extracted**: `[logistics, company, reported, strong, growth, faces, inflation, challenges]`
2. **Positive words found**: `strong` (1), `growth` (1) = 2 positive
3. **Negative words found**: `challenges` (1) = 1 negative
4. **Sentiment**: Positive (2 > 1 + 1 = true)
5. **Breakdown**: 
   - Total sentiment words: 3
   - Positive: 2/3 = 66%
   - Negative: 1/3 = 33%
   - Neutral: 0%

## Caching Strategy

- **Cache Duration**: 6 jam
- **Cache Key**: Hash SHA256 dari search query
- **Cache Storage**: Database table `news_cache`
- **Fallback**: Jika API error, menggunakan cache yang sudah expired

### Cache Benefits

1. Mengurangi hit ke GNews API (menghemat quota)
2. Response lebih cepat untuk query yang sama
3. Resilience jika API mengalami downtime

## Error Handling

### 1. Missing API Key

**Condition**: `GNEWS_API_KEY` tidak diatur di `.env`

**Behavior**: Menggunakan `demo_key` (terbatas)

**Solution**: Set API key di `.env`:
```
GNEWS_API_KEY=your_actual_api_key_here
```

### 2. API Quota Exceeded

**HTTP Status**: `429 Too Many Requests`

**Response Code**: `QUOTA_EXCEEDED`

**Handling**:
- Log error ke system
- Mengembalikan cached data jika tersedia
- User di-inform untuk mencoba lagi nanti

### 3. Invalid API Key

**HTTP Status**: `500 Internal Server Error`

**Response Code**: `AUTH_ERROR`

**Solution**: Verify API key di GNews dashboard

### 4. Timeout Error

**Default Timeout**: 10 detik

**Behavior**: Return error dengan code `SERVER_ERROR`

## Configuration

### Environment Variables

```env
# .env
GNEWS_API_KEY=your_gnews_api_key_here
```

### Get GNews API Key

1. Visit: https://gnews.io
2. Sign up untuk free account
3. Copy API key dari dashboard
4. Tambahkan ke `.env` file

## Database Tables Used

### news_cache
```
- query_hash: SHA256 hash dari search query
- source_api: 'gnews'
- request_params: JSON parameters
- response_payload: Full GNews API response
- fetched_at: Timestamp fetch
- expires_at: Cache expiration time
- created_at, updated_at: Timestamps
```

### positive_words
```
- word: Kata positif
- weight: Bobot score (default: 1.0)
```

### negative_words
```
- word: Kata negatif
- weight: Bobot score (default: 1.0)
```

## Testing Examples

### cURL

```bash
# Fetch news for Indonesia
curl "http://127.0.0.1:8000/api/news?country=Indonesia"

# Fetch news for Vietnam
curl "http://127.0.0.1:8000/api/news?country=Vietnam"

# Fetch news for Singapore
curl "http://127.0.0.1:8000/api/news?country=Singapore"
```

### JavaScript/Fetch API

```javascript
const country = 'Indonesia';
const response = await fetch(`/api/news?country=${encodeURIComponent(country)}`);
const data = await response.json();

if (data.status === 'success') {
  console.log(`Found ${data.count} articles`);
  data.data.forEach(article => {
    console.log(`${article.title} - ${article.sentiment_analysis.sentiment}`);
  });
}
```

### JavaScript/Axios

```javascript
import axios from 'axios';

async function getNews(country) {
  try {
    const response = await axios.get('/api/news', {
      params: { country }
    });
    
    if (response.data.status === 'success') {
      return response.data.data;
    }
  } catch (error) {
    if (error.response?.status === 429) {
      console.error('API quota exceeded');
    } else {
      console.error('Error fetching news:', error.message);
    }
  }
}

// Usage
const articles = await getNews('Indonesia');
articles.forEach(article => {
  console.log(`${article.title} (${article.sentiment_analysis.sentiment})`);
});
```

## Performance Metrics

- **Average Response Time**: 200-500ms (with cache)
- **API Call Time**: 2-5 seconds (without cache)
- **Max Articles per Call**: 10
- **Cache Hit Rate**: ~70% (estimated for typical usage)

## Future Enhancements

1. **Filtering**: Add filter by sentiment, date range, source
2. **Pagination**: Support untuk large result sets
3. **Real-time Updates**: WebSocket untuk live updates
4. **Historical Analysis**: Store articles di database untuk trend analysis
5. **Multi-language**: Support untuk berita dalam bahasa Indonesia
6. **Advanced NLP**: Integrate dengan ML models untuk sentiment lebih akurat

## Support

Untuk pertanyaan atau issues, silakan:
1. Check `.env` configuration
2. Verify GNews API key di https://gnews.io/dashboard
3. Check aplikasi logs di `storage/logs/`
4. Contact development team
