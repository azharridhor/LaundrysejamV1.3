# LaundrySejam

Website profil dan dashboard untuk Laundry KOIN Sejam Yogyakarta. Proyek ini dibuat dengan PHP dan MySQL untuk menampilkan informasi layanan laundry, promo, lokasi outlet, testimoni pelanggan, serta fitur login admin.

## Fitur Utama

- Halaman beranda modern dengan hero section, promo, dan informasi layanan
- Menampilkan fitur unggulan laundry
- Informasi lokasi outlet yang tersedia di Yogyakarta
- Bagian testimoni pelanggan
- Halaman login dan dashboard admin untuk mengelola konten dasar

## Teknologi yang Digunakan

- PHP
- MySQL / MariaDB
- PDO untuk koneksi database
- HTML, CSS, dan JavaScript
- Tailwind CSS (melalui kelas utility pada halaman)

## Persyaratan Sistem

Sebelum menjalankan aplikasi, pastikan Anda sudah menyiapkan:

- XAMPP, WAMP, atau server lokal lain dengan Apache dan MySQL
- PHP 7.4+ atau versi yang kompatibel
- Browser modern

## Cara Menjalankan

1. Letakkan folder proyek ini di direktori server lokal, misalnya:
   - XAMPP: `C:\xampp\htdocs\Laundrysejam`
2. Jalankan Apache dan MySQL dari XAMPP.
3. Buat database dan isi data awal dengan membuka:
   - http://localhost/Laundrysejam/setup_database.php
4. Buka aplikasi di browser:
   - http://localhost/Laundrysejam/

## Login Admin

Setelah menjalankan setup_database.php, Anda dapat login dengan akun berikut:

- Username: `admin`
- Password: `admin123`

Disarankan untuk mengubah password setelah login pertama.

## Struktur Folder

- `index.php` — halaman utama website
- `dashboard.php` — halaman dashboard admin
- `login.php` dan `logout.php` — proses autentikasi pengguna
- `db.php` — konfigurasi koneksi database
- `setup_database.php` — pembuatan database dan data awal
- `css/` dan `js/` — file styling dan script

## Catatan

Jika aplikasi tidak dapat terhubung ke database, pastikan:

- MySQL sedang berjalan
- Nama database `laundrysejam` tersedia
- Kredensial database di `db.php` sesuai dengan konfigurasi lokal Anda
