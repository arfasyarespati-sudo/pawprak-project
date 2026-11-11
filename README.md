# DrawChaos

Project web berbasis Laravel dengan Blade templating engine dan Tailwind CSS.

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
- **Node.js & NPM** (untuk Tailwind CSS / Vite)
- **Git**

---

## ⚙️ Inisialisasi Project (Pembuatan Awal)
*(Langkah ini dilakukan saat membuat repository dari awal)*

1. **Membuat Project via Composer:**
   ```bash
   composer create-project laravel/laravel PAWPRAK-project
   cd PAWPRAK-project
   ```

2. **Setup Tailwind CSS (v4 + Vite):**
   ```bash
   npm install tailwindcss @tailwindcss/vite
   ```
   * Tambahkan plugin `@tailwindcss/vite` di file `vite.config.js`:
     ```javascript
     import { defineConfig } from 'vite';
     import laravel from 'laravel-vite-plugin';
     import tailwindcss from '@tailwindcss/vite';

     export default defineConfig({
         plugins: [
             laravel({
                 input: ['resources/css/app.css', 'resources/js/app.js'],
                 refresh: true,
             }),
             tailwindcss(),
         ],
     });
     ```
   * Tambahkan direktif Tailwind di `resources/css/app.css`:
     ```css
     @import "tailwindcss";
     ```
   * Panggil direktif `@vite` di tag `<head>` file Blade (`resources/views/welcome.blade.php`):
     ```html
     @vite(['resources/css/app.css', 'resources/js/app.js'])
     ```

3. **Inisialisasi Git & Hubungkan ke GitHub:**
   ```bash
   git init
   git add .
   git commit -m "Initial DrawChaos setup with Laravel Blade & Tailwind CSS"
   git branch -M main
   git remote add origin [https://github.com/arfasyarespati-sudo/pawprak-project.git](https://github.com/arfasyarespati-sudo/pawprak-project.git)
   git push -u origin main
   ```

---

## 👥 Panduan Setup untuk Collaborator / Tim
Jika teman satu tim ingin ikut mengembangkan project **DrawChaos** ini dari repository GitHub, ikuti langkah-langkah di bawah ini:

### 1. Clone Repository
```bash
git clone [https://github.com/arfasyarespati-sudo/pawprak-project.git](https://github.com/arfasyarespati-sudo/pawprak-project.git)
cd pawprak-project
```

### 2. Install Dependensi PHP (Composer)
```bash
composer install
```

### 3. Install Dependensi Frontend (NPM)
```bash
npm install
```

### 4. Setup File Environment (`.env`)
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
DB_DATABASE=drawchaos_db
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Generate Application Key
```bash
php artisan key:generate
```

### 6. Jalankan Database Migration
Pastikan service MySQL (Laragon/XAMPP) sudah aktif dan database `drawchaos_db` sudah dibuat, lalu jalankan:
```bash
php artisan migrate
```

---

## 🚀 Menjalankan Server Lokal
Untuk menjalankan server Laravel sekaligus bundler Tailwind CSS (Vite), buka **dua terminal** terpisah di folder project:

* **Terminal 1** (Server Backend Laravel):
  ```bash
  php artisan serve
  ```
* **Terminal 2** (Asset Compiler Tailwind CSS):
  ```bash
  npm run dev
  ```

Buka link yang tertera di terminal (biasanya `http://127.0.0.1:8000`) di browser kamu.