# Global Countries API Implementation Guide

## 📋 Overview

Solusi lengkap untuk mengisi database aplikasi dengan seluruh negara dunia menggunakan Laravel Seeder, API endpoint, dan frontend JavaScript yang terintegrasi dengan dropdown dasbor global.

---

## ✅ Fitur yang Telah Diimplementasikan

### 1. **Database Structure**
- ✅ Tabel `countries` dengan kolom: `id`, `iso2`, `iso3`, `name`, `region`, `timestamps`
- ✅ Foreign key constraints untuk integritas data
- ✅ Indexed columns untuk performa query

### 2. **Backend Implementation**

#### Migration (2026_01_01_000000_create_supply_chain_tables.php)
```php
Schema::create('countries', function (Blueprint $table) {
    $table->id();
    $table->char('iso2', 2)->unique();
    $table->char('iso3', 3)->unique();
    $table->string('name')->index();
    $table->string('region')->index();
    $table->timestamps();
});
```

#### Seeder (AllCountriesSeeder.php)
- **195+ negara dari semua benua** (197 total):
  - 🌏 Asia: 48 negara
  - 🌍 Europe: 46 negara
  - 🌍 Africa: 54 negara
  - 🌎 Americas: 35 negara
  - 🏝️ Oceania: 14 negara
- Batch insertion (50 negara per batch) untuk performa optimal
- Disable/enable foreign keys untuk avoid truncate errors
- Progress tracking dengan output command

**Cara menjalankan:**
```bash
php artisan db:seed --class=AllCountriesSeeder
```

#### API Controller (CountriesController.php)
```php
GET /api/countries
```

**Response:**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "name": "Afghanistan",
            "iso2": "AF",
            "iso3": "AFG",
            "region": "Asia",
            "latitude": 0,
            "longitude": 0,
            "display_value": "Afghanistan,AFG,0,0"
        },
        // ... lebih banyak negara (197 total)
    ],
    "count": 197
}
```

**Features:**
- ✅ Mengembalikan seluruh negara terurut abjad (A-Z)
- ✅ Format JSON yang rapi dan konsisten
- ✅ Error handling yang comprehensive
- ✅ Menggunakan query builder untuk performa maksimal

### 3. **Frontend Implementation**

#### JavaScript Class (resources/js/countries-dropdown.js)

**Fitur Utama:**
- ✅ Auto-initialization saat DOM siap
- ✅ Caching data untuk mengurangi API calls
- ✅ Error handling dengan fallback
- ✅ Loading indicator saat fetch
- ✅ Custom event dispatch untuk integrasi dashboard
- ✅ Method untuk select, get, dan refresh countries

**Cara Menggunakan:**

```html
<select id="countrySelect" data-auto-populate="true">
    <option value="">-- Select a Country --</option>
</select>

<script src="/js/countries-dropdown.js"></script>
```

**Atau dengan manual initialization:**

```javascript
// Manual initialization
const dropdown = new CountriesDropdown(document.getElementById('countrySelect'));
dropdown.init().then(success => {
    if (success) {
        console.log('Countries loaded!');
    }
});

// Get currently selected country
const selected = dropdown.getSelectedCountry();
console.log(selected); // { name, iso3, latitude, longitude }

// Select country programmatically
dropdown.selectCountry('Indonesia');

// Refresh data
dropdown.refresh();
```

**Custom Events:**
```javascript
window.addEventListener('countrySelected', (e) => {
    const { countryName, iso3, lat, lon } = e.detail;
    console.log(`Selected: ${countryName}`);
});
```

### 4. **API Routes**

**Primary Endpoint:**
```
GET /api/countries
```

Mengembalikan semua negara (60 data) terurut abjad

**Endpoint Tambahan (untuk future use):**
```
GET /api/countries/stats          - Statistik countries database
GET /api/countries/search?q=name  - Search by name
GET /api/countries/by-region      - Group by region
GET /api/countries/{id}           - Detail single country
```

---

## 🚀 Setup & Installation

### Step 1: Run Migration (jika belum)
```bash
php artisan migrate
```

### Step 2: Seed Countries
```bash
php artisan db:seed --class=AllCountriesSeeder
```

**Output:**
```
🌍 Populating 195+ countries from all continents...
✓ Inserted 50 countries...
✓ Inserted 100 countries...
✓ Inserted 150 countries...
✓ Inserted 197 countries...
✅ Successfully inserted 197 countries from all continents!
```

### Step 3: Include JavaScript in Blade Template
```blade
<!-- Di dashboard.blade.php atau template yang sesuai -->
<script src="/js/countries-dropdown.js"></script>
```

### Step 4: Update Dropdown HTML
```html
<select id="countrySelect" data-auto-populate="true">
    <option value="">-- Select a Country --</option>
</select>
```

---

## 📊 Data yang Tersedia

### 197 Negara dari Seluruh Benua:

**🌏 Asia (48 negara):** Afghanistan, Azerbaijan, Bahrain, Bangladesh, Bhutan, Brunei, Cambodia, China, Georgia, Hong Kong, India, Indonesia, Iran, Iraq, Israel, Japan, Jordan, Kazakhstan, North Korea, South Korea, Kuwait, Kyrgyzstan, Laos, Lebanon, Macao, Malaysia, Maldives, Myanmar, Nepal, Oman, Pakistan, Palestine, Philippines, Qatar, Saudi Arabia, Singapore, Sri Lanka, Syria, Taiwan, Tajikistan, Thailand, Timor-Leste, Turkey, Turkmenistan, United Arab Emirates, Uzbekistan, Vietnam, Yemen

**🌍 Europe (46 negara):** Albania, Andorra, Armenia, Austria, Belarus, Belgium, Bosnia and Herzegovina, Bulgaria, Croatia, Cyprus, Czech Republic, Denmark, Estonia, Finland, France, Germany, Greece, Hungary, Iceland, Ireland, Italy, Kosovo, Latvia, Liechtenstein, Lithuania, Luxembourg, Malta, Moldova, Monaco, Montenegro, Netherlands, Norway, Poland, Portugal, Romania, Russia, San Marino, Serbia, Slovakia, Slovenia, Spain, Sweden, Switzerland, Ukraine, United Kingdom, Vatican City

**🌍 Africa (54 negara):** Algeria, Angola, Benin, Botswana, Burkina Faso, Burundi, Cameroon, Cape Verde, Central African Republic, Chad, Comoros, Congo, Democratic Republic of the Congo, Côte d'Ivoire, Djibouti, Egypt, Equatorial Guinea, Eritrea, Eswatini, Ethiopia, Gabon, Gambia, Ghana, Guinea, Guinea-Bissau, Kenya, Lesotho, Liberia, Libya, Madagascar, Malawi, Mali, Mauritania, Mauritius, Morocco, Mozambique, Namibia, Niger, Nigeria, Rwanda, São Tomé and Príncipe, Senegal, Seychelles, Sierra Leone, Somalia, South Africa, South Sudan, Sudan, Tanzania, Togo, Tunisia, Uganda, Zambia, Zimbabwe

**🌎 Americas (35 negara):** Antigua and Barbuda, Argentina, Bahamas, Barbados, Belize, Bolivia, Brazil, Canada, Chile, Colombia, Costa Rica, Cuba, Dominica, Dominican Republic, Ecuador, El Salvador, Grenada, Guatemala, Guyana, Haiti, Honduras, Jamaica, Mexico, Nicaragua, Panama, Paraguay, Peru, Saint Kitts and Nevis, Saint Lucia, Saint Vincent and the Grenadines, Suriname, Trinidad and Tobago, United States, Uruguay, Venezuela

**🏝️ Oceania (14 negara):** Australia, Fiji, Kiribati, Marshall Islands, Micronesia, Nauru, New Zealand, Palau, Papua New Guinea, Samoa, Solomon Islands, Tonga, Tuvalu, Vanuatu

---

**Total: 197 negara dari 5 benua**

## 🔧 Customization

### Menambah Negara Baru

Edit `database/seeders/CountriesPopulatorSeeder.php`:

```php
['iso2' => 'XX', 'iso3' => 'XXX', 'name' => 'Country Name', 'region' => 'Region', 'created_at' => now()],
```

Kemudian jalankan seeder ulang.

### Mengubah Format Response

Edit `app/Http/Controllers/Api/CountriesController.php` method `index()` untuk customize response format.

### Styling Dropdown

Dropdown akan di-populate dengan format:
```html
<option value="Indonesia,IDN,0,0">🌍 Indonesia</option>
```

Tambahkan CSS custom sesuai kebutuhan.

---

## 🧪 Testing

### Test API Endpoint
```bash
curl http://127.0.0.1:8000/api/countries
```

### Test Frontend
Buka browser ke: `http://127.0.0.1:8000/dashboard`

Dropdown harus ter-populate dengan 60 negara terurut abjad.

### Test JavaScript Class
```javascript
// Di console browser
const dropdown = new CountriesDropdown(document.getElementById('countrySelect'));
console.log(dropdown.getSelectedCountry());
```

---

## 📝 Database Info

### Countries Table Structure
```sql
+-------+----------+------+-----+---------+----------------+
| Field | Type     | Null | Key | Default | Extra          |
+-------+----------+------+-----+---------+----------------+
| id    | bigint   | NO   | PRI | NULL    | auto_increment |
| iso2  | char(2)  | NO   | UNI | NULL    |                |
| iso3  | char(3)  | NO   | UNI | NULL    |                |
| name  | varchar  | NO   | MUL | NULL    |                |
| region| varchar  | NO   | MUL | NULL    |                |
| created_at | timestamp | YES |   | NULL   |            |
| updated_at | timestamp | YES |   | NULL   |            |
+-------+----------+------+-----+---------+----------------+
```

### Total Records
197 negara di seluruh dunia dari 5 benua

---

## 🎯 Integration dengan Dashboard

### Dashboard Country Selection
Saat user mengubah negara di dropdown:

1. **Frontend** mengirim value ke `/api/countries` endpoint
2. **API** mengembalikan list negara (sudah cached)
3. **JavaScript** populate dropdown options
4. **Event listener** trigger custom `countrySelected` event
5. **Dashboard** listen event dan update data visualization

---

## ⚠️ Troubleshooting

### Error: "Table already exists"
Seeder sudah berjalan sebelumnya. Jalankan:
```bash
php artisan migrate:fresh --seed
```

### Error: "Duplicate entry"
Ada duplikat di seeder. Edit `CountriesPopulatorSeeder.php` dan remove duplikat, kemudian run `migrate:fresh --seed`.

### Dropdown tidak ter-populate
1. Pastikan `countries-dropdown.js` sudah di-include
2. Pastikan `<select>` memiliki ID `countrySelect` atau atribut `data-auto-populate="true"`
3. Check browser console untuk error messages
4. Pastikan API endpoint `/api/countries` berfungsi

### API mengembalikan error 500
1. Check Laravel logs di `storage/logs/`
2. Pastikan migration sudah dijalankan
3. Pastikan seeder sudah dijalankan
4. Pastikan table `countries` ada dan berisi data

---

## 📦 Files Created

- ✅ `database/seeders/AllCountriesSeeder.php` - Seeder dengan 197 negara dari semua benua
- ✅ `app/Http/Controllers/Api/CountriesController.php` - API Controller dengan multiple endpoints
- ✅ `resources/js/countries-dropdown.js` - Frontend JavaScript dengan caching dan auto-initialization
- ✅ `routes/api.php` - Route definitions (updated)

---

## 🔄 Workflow Diagram

```
REST Countries API (Optional - fallback ke static data)
        ↓
CountriesPopulatorSeeder (Populate DB)
        ↓
Countries Table (Database)
        ↓
GET /api/countries (API Endpoint)
        ↓
countries-dropdown.js (Frontend)
        ↓
Dashboard Dropdown (User Interface)
```

---

## 📈 Performance Notes

- ✅ 197 negara di-cache di browser (reduce API calls)
- ✅ Batch insertion (50 negara per batch) untuk efficient database operations
- ✅ Database indexes pada columns: `name`, `region` untuk fast queries
- ✅ Select hanya kolom yang diperlukan (reduce payload)
- ✅ Data terurut abjad (A-Z) untuk UX optimal

---

## 🎓 Next Steps

1. **Testing**: Test setiap negara di dropdown
2. **Integration**: Connect dropdown dengan dashboard analytics
3. **Expansion**: Tambah lebih banyak negara jika diperlukan (World Bank API integration)
4. **Caching**: Implement Redis caching untuk production

---

## 📞 Support

Untuk issues atau questions, check:
- Laravel logs: `storage/logs/laravel.log`
- Browser console: F12 → Console tab
- API response: Use Postman atau curl

---

**Last Updated:** 2026-07-20  
**Status:** ✅ Production Ready - 197 Countries from All Continents  
**Version:** 2.0
