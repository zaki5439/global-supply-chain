# 🚀 Panduan Hosting Gratis All-in-One (Docker)

Anda telah memilih **Opsi 1 (All-in-One Docker)**. Ini adalah cara yang sangat rapi untuk meng-hosting **PHP (Frontend), Python (Backend), dan Redis (Cache)** secara bersamaan di dalam 1 wadah (Docker container).

Karena semuanya ada di dalam 1 wadah, Anda **hanya perlu mendaftar ke 1 layanan cloud gratis** (seperti Koyeb.com atau Render.com).

---

## Yang Telah Saya (AI) Kerjakan:
1. **Membuat `Dockerfile` Utama**: Instruksi untuk menginstal Nginx, PHP, Python, dan Redis dalam satu OS Ubuntu.
2. **Membuat `supervisord.conf`**: Program yang akan memastikan Nginx (Web Server), PHP-FPM, Uvicorn (FastAPI), dan Redis-Server menyala bersamaan.
3. **Membuat `nginx.conf`**: Mengatur jalur lalu lintas. Jika *user* mengakses `/api/`, Nginx akan meneruskannya ke Python. Sisanya akan diarahkan ke PHP.
4. **Mengubah JavaScript Frontend**: Menghilangkan teks `http://localhost:8000` menjadi format *relative URL* (`/api/...`) agar dinamis dan bisa diakses dari mana saja.

---

## TAHAP DEPLOYMENT (Langkah Anda)

### Langkah 1: Simpan ke GitHub
Pastikan Anda sudah meng-*commit* dan *push* semua kode terbaru (termasuk file `Dockerfile`, `nginx.conf`, dan `supervisord.conf`) ke GitHub.
```bash
git add .
git commit -m "Setup All-in-One Docker untuk Hosting"
git push origin main
```

### Langkah 2: Deploy ke KOYEB (Sangat Direkomendasikan untuk Docker)
Koyeb sangat bersahabat untuk Docker dan memberikan gratis 1 service.
1. Buka [https://www.koyeb.com/](https://www.koyeb.com/) dan buat akun (bisa *Sign in with GitHub*).
2. Di Dashboard, klik **Create Service**.
3. Pilih **GitHub** dan hubungkan akun Anda.
4. Pilih repositori `supply-chain-app`.
5. Di bagian **Builder**, pilih **Dockerfile**. (Penting: Koyeb akan otomatis membaca `Dockerfile` yang baru kita buat).
6. Di bagian **Regions**, pilih wilayah terdekat (misal: *Singapore* atau *Frankfurt*).
7. Di bagian **Instance**, pastikan memilih opsi **Eco (Free)**.
8. Di bagian **Exposed ports**, pastikan port diatur ke **80** (karena Nginx kita akan berjalan di port 80).
9. Beri nama aplikasi Anda dan klik **Deploy**.

*Koyeb akan mulai membangun OS Ubuntu Anda, menginstal semua komponen, dan menjalankan aplikasi. Proses ini mungkin memakan waktu 5-10 menit.*

### Langkah 3 (Alternatif): Deploy ke RENDER
Jika Anda tidak bisa menggunakan Koyeb, Anda bisa menggunakan Render:
1. Buka [https://render.com](https://render.com) dan buat **New Web Service**.
2. Pilih repo GitHub Anda.
3. Di bagian **Environment**, pastikan Anda memilih **Docker**.
4. Instance Type: **Free**.
5. Klik **Create Web Service**.

---

## Catatan Penting
- Karena Anda menggunakan *Free Tier*, jika aplikasi tidak diakses selama 15-30 menit, server mungkin akan "tidur". Saat Anda membukanya lagi pertama kali, butuh waktu sekitar 30 detik untuk *loading*.
- Data cache di Redis akan hilang jika server direstart. Hal ini normal untuk infrastruktur gratis.
- Anda bisa mengakses aplikasi melalui URL publik yang diberikan oleh Koyeb / Render!
