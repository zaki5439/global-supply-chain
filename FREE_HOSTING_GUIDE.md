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

### Langkah 2: Deploy ke RENDER.COM (Sangat Direkomendasikan & Mudah)
Render adalah platform cloud yang sangat mudah digunakan dan 100% gratis.
1. Buka link ini di browser Anda: 👉 **[https://dashboard.render.com](https://dashboard.render.com)**
2. Daftar atau *Login* menggunakan akun **GitHub** Anda.
3. Setelah masuk ke *Dashboard*, klik tombol **"New +"** (di pojok kanan atas) lalu pilih **"Web Service"**.
4. Pilih opsi **"Build and deploy from a Git repository"**.
5. Klik tombol **Connect** di sebelah nama repositori Anda (`supply-chain-app`).
6. Di halaman pengaturan, isi seperti ini:
   - **Name:** Bebas (misal: `supply-chain-web`)
   - **Environment:** Pilih **Docker** (Ini sangat penting! Jangan pilih Python/PHP).
   - **Instance Type:** Pilih **Free** ($0/month).
7. *Scroll* ke paling bawah dan klik tombol **Create Web Service**.

*Selesai! Render sekarang sedang menginstal Nginx, PHP, Python, dan Redis untuk Anda. Proses ini memakan waktu sekitar 5-10 menit. Setelah selesai, Anda bisa mengklik link URL yang diberikan Render di pojok kiri atas.*

---

## Catatan Penting
- Karena Anda menggunakan *Free Tier*, jika aplikasi tidak diakses selama 15-30 menit, server mungkin akan "tidur". Saat Anda membukanya lagi pertama kali, butuh waktu sekitar 30 detik untuk *loading*.
- Data cache di Redis akan hilang jika server direstart. Hal ini normal untuk infrastruktur gratis.
- Anda bisa mengakses aplikasi melalui URL publik yang diberikan oleh Koyeb / Render!
