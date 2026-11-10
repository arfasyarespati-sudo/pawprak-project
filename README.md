# Nama Project

Project web berbasis Laravel dengan Blade templating engine.

---

## 📋 Daftar Isi
- [Prasyarat Sistem](#-prasyarat-sistem)
- [Inisialisasi Project (Pembuatan Awal)](#-inisialisasi-project-pembuatan-awal)
- [Panduan Setup untuk Collaborator / Tim](#-panduan-setup-untuk-collaborator--tim)
- [Menjalankan Server Lokal](#-menjalankan-server-lokal)

---

## 🛠️ Prasyarat Sistem
Sebelum memulai, pastikan komputer/laptop sudah menginstal:
- **PHP** (Versi 8.2 atau 8.3, disarankan menggunakan Laragon)
- **Composer**
- **Git**

---

## ⚙️ Inisialisasi Project (Pembuatan Awal)
*(Langkah ini sudah dilakukan oleh pembuat repository)*

1. **Membuat Project via Composer:**
   ```bash
   composer create-project laravel/laravel nama-project
   cd nama-project
   ```

2. **Inisialisasi Git & Hubungkan ke GitHub:**
   ```bash
   git init
   git add .
   git commit -m "Initial Laravel Blade setup"
   git branch -M main
   git remote add origin [https://github.com/username-kamu/nama-repo.git](https://github.com/username-kamu/nama-repo.git)
   git push -u origin main
   ```

---

## 👥 Panduan Setup untuk Collaborator / Tim
Jika teman satu tim ingin ikut mengembangkan project ini dari repository GitHub, ikuti langkah-langkah di bawah ini:

### 1. Clone Repository
```bash
git clone [https://github.com/username-kamu/nama-repo.git](https://github.com/username-kamu/nama-repo.git)
cd nama-repo
```

### 2. Install Dependensi PHP
```bash
composer install
```

### 3. Setup File Environment (`.env`)
Duplikat file `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
*(Atau copy-paste file `.env.example` secara manual dan ganti namanya menjadi `.env`)*

Sesuaikan konfigurasi database lokal masing-masing di dalam file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database_lokal_kalian
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Jalankan Database Migration
Pastikan service MySQL (Laragon/XAMPP) sudah aktif dan database sudah dibuat, lalu jalankan:
```bash
php artisan migrate
```

---

## 🚀 Menjalankan Server Lokal
Nyalakan server development Laravel dengan perintah:
```bash
php artisan serve
```
Buka link yang tertera di terminal (biasanya `http://127.0.0.1:8000`) di browser kamu.