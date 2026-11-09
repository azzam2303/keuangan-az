# 💰 Keuangan-AZ

Aplikasi manajemen keuangan berbasis web yang dibangun menggunakan **Laravel** dan frontend bundler **Vite**. Proyek ini dirancang untuk mempermudah pencatatan, pemantauan, dan pengelolaan data keuangan secara efisien.

---

## 📌 Fitur Utama
*(Silakan sesuaikan/tambahkan fitur asli dari aplikasi Anda di bawah ini)*
- 📈 **Manajemen Transaksi:** Mencatat pemasukan dan pengeluaran secara real-time.
- 📊 **Laporan Keuangan:** Visualisasi data ringkasan keuangan bulanan atau tahunan.
- 🔐 **Autentikasi Pengguna:** Sistem login dan registrasi pengguna yang aman.
- 📱 **Responsif & Cepat:** Antarmuka modern yang di-bundle menggunakan Vite untuk performa optimal.

---

## 🛠️ Teknologi yang Digunakan
- **Backend Framework:** [Laravel](https://laravel.com) (PHP)
- **Frontend Build Tool:** [Vite](https://vitejs.dev) (JavaScript)
- **Dependency Manager:** [Composer](https://getcomposer.org) & [NPM](https://npmjs.com)
- **Database:** MySQL / PostgreSQL *(sesuaikan dengan yang Anda pakai)*

---

## 🚀 Memulai Proyek (Local Development)

Ikuti langkah-langkah di bawah ini untuk menjalankan proyek ini di komputer lokal Anda.

### Prasyarat (Prerequisites)
Pastikan Anda sudah menginstal:
- PHP (Versi 8.x sesuai kebutuhan Laravel Anda)
- Composer
- Node.js & NPM
- Web Server & Database (XAMPP / Laragon / Docker)

### Langkah Instalasi

1. **Clone Repositori:**
   ```bash
   git clone https://github.com/azzam2303/keuangan-az.git
   cd keuangan-az
   ```

2. **Instal Dependensi Backend (PHP):**
   ```bash
   composer install
   ```

3. **Instal Dependensi Frontend (JavaScript):**
   ```bash
   npm install
   ```

4. **Konfigurasi Environment File:**
   Salin file `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   ```
   Buka file `.env` yang baru dibuat dan sesuaikan konfigurasi database Anda:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=nama_database_anda
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

6. **Jalankan Migrasi Database (dan Seeders jika ada):**
   ```bash
   php artisan migrate
   ```

---

## 💻 Menjalankan Aplikasi

Anda perlu menjalankan dua perintah berikut di terminal terpisah untuk memulai server pengembangan:

**Terminal 1 (Menjalankan Server Laravel PHP):**
```bash
php artisan serve
```
Aplikasi Anda akan dapat diakses di: [http://127.0.0.1:8000](http://127.0.0.1:8000)

**Terminal 2 (Menjalankan Asset Compiler Vite):**
```bash
npm run dev
```

---

## 📄 Lisensi
Proyek ini bersifat open-source di bawah naungan [Lisensi MIT](LICENSE).
