<?php
// import_db.php — Jalankan untuk mengimpor laundrysejam.sql ke MySQL lokal.
// Cara pakai: buka http://localhost/Laundrysejam/import_db.php di browser atau jalankan `php import_db.php` di terminal.

require_once __DIR__ . '/includes/koneksi.php';

$sqlFile = __DIR__ . '/laundrysejam.sql';
if (!file_exists($sqlFile)) {
    echo "File SQL tidak ditemukan: $sqlFile";
    exit;
}

$adminConn = new mysqli($db_host, $db_user, $db_pass);
if ($adminConn->connect_error) {
    echo 'Koneksi MySQL gagal: ' . $adminConn->connect_error;
    exit;
}

if (!$adminConn->query("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    echo 'Gagal membuat database jika belum ada: ' . $adminConn->error;
    exit;
}

$adminConn->select_db($db_name);

$sql = file_get_contents($sqlFile);
if ($sql === false) {
    echo "Gagal membaca file SQL.";
    exit;
}

$statements = preg_split('/;\s*(?:\r?\n|$)/', $sql);
foreach ($statements as $statement) {
    $statement = trim($statement);
    if (empty($statement)) {
        continue;
    }

    $statement = preg_replace('/^DROP\s+DATABASE.*$/mi', '', $statement);
    $statement = preg_replace('/^CREATE\s+DATABASE.*$/mi', '', $statement);
    $statement = preg_replace('/^USE\s+`?[^`\s]+`?.*$/mi', '', $statement);
    $statement = preg_replace('/^DROP\s+TABLE.*$/mi', '', $statement);
    $statement = preg_replace('/^CREATE\s+TABLE\s+(IF\s+NOT\s+EXISTS\s+)?/mi', 'CREATE TABLE IF NOT EXISTS ', $statement);
    $statement = preg_replace('/^INSERT\s+INTO\s+/mi', 'INSERT IGNORE INTO ', $statement);
    $statement = trim($statement);
    if ($statement === '') {
        continue;
    }

    if (preg_match('/^INSERT\s+IGNORE\s+INTO\s+`?([a-zA-Z0-9_]+)`?/mi', $statement, $match)) {
        $targetTable = $match[1];
        $checkResult = $adminConn->query("SELECT 1 FROM `$targetTable` LIMIT 1");
        if ($checkResult && mysqli_num_rows($checkResult) > 0) {
            continue;
        }
    }

    if (!$adminConn->query($statement)) {
        echo "Gagal menjalankan statement: " . htmlspecialchars($statement) . "<br>" . $adminConn->error . "<br><br>";
    }
}

// Tambahkan field lokasi latitude/longitude jika belum ada
$adminConn->query("ALTER TABLE lokasi ADD COLUMN IF NOT EXISTS latitude DECIMAL(10,7) NOT NULL DEFAULT 0.0");
$adminConn->query("ALTER TABLE lokasi ADD COLUMN IF NOT EXISTS longitude DECIMAL(10,7) NOT NULL DEFAULT 0.0");

// Pastikan tipe pickup_distance mendukung angka desimal
$adminConn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS quantity INT UNSIGNED NOT NULL DEFAULT 1");
$adminConn->query("ALTER TABLE orders MODIFY COLUMN pickup_distance DECIMAL(6,1) NOT NULL DEFAULT 0.0");
// Tambahkan dukungan selfservice dan addon produk
$adminConn->query("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('qris','transfer','cash','branch') NOT NULL");
$adminConn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_proof VARCHAR(255) DEFAULT NULL");
$adminConn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS soap_addon TINYINT(1) NOT NULL DEFAULT 0");
$adminConn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS fragrance_addon TINYINT(1) NOT NULL DEFAULT 0");
$adminConn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS soap_addon_qty INT UNSIGNED NOT NULL DEFAULT 0");
$adminConn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS fragrance_addon_qty INT UNSIGNED NOT NULL DEFAULT 0");

echo "Import selesai tanpa menghapus data lama. Silakan periksa database '$db_name'.";

$adminConn->close();

?>
