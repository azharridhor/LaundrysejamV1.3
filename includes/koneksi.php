<?php
// koneksi.php
// Sesuaikan konfigurasi database sesuai environment Anda.

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'laundrysejam';

$koneksi = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$koneksi) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

mysqli_set_charset($koneksi, 'utf8mb4');
/** @var mysqli $koneksi */

/**
 * Jalankan query dan kembalikan hasilnya.
 *
 * @param mysqli $koneksi
 * @param string $sql
 * @return mysqli_result|bool
 */
function query(mysqli $koneksi, string $sql)
{
    return mysqli_query($koneksi, $sql);
}

/**
 * Escape input untuk mencegah SQL injection.
 *
 * @param mysqli $koneksi
 * @param string $value
 * @return string
 */
function esc(mysqli $koneksi, string $value): string
{
    return mysqli_real_escape_string($koneksi, trim($value));
}

/**
 * Format angka menjadi rupiah.
 *
 * @param int|float $value
 * @return string
 */
function formatRupiah($value)
{
    return 'Rp ' . number_format((float) $value, 0, ',', '.');
}
