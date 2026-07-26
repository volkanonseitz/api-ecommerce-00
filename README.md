# API E-commerce

API E-commerce adalah layanan backend untuk aplikasi e-commerce.

## Fitur Utama

- Manajemen produk
- Manajemen pesanan
- Autentikasi pengguna
- Keranjang belanja
- Integrasi pembayaran

## Teknologi yang Digunakan

- Laravel (PHP Framework)
- MySQL (Database)

## Instalasi

1. Clone repositori:
   ```bash
   git clone https://github.com/irwanwahyudi333/api-ecommerce-00.git
   cd api-ecommerce
   ```

2. Install dependensi Composer:
   ```bash
   composer install
   ```

3. Salin file `.env.example` ke `.env` dan konfigurasikan database Anda:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Jalankan migrasi database:
   ```bash
   php artisan migrate
   ```

5. Jalankan server:
   ```bash
   php artisan serve
   ```

Aplikasi akan berjalan di `http://127.0.0.1:8000`.

## Dokumentasi API

Lihat dokumentasi API untuk detail endpoint dan penggunaannya.
