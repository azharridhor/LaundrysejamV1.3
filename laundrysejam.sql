-- SQL dump for Laundrysejam database
-- Import this file di phpMyAdmin atau MySQL CLI.

DROP DATABASE IF EXISTS `laundrysejam`;
CREATE DATABASE `laundrysejam` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `laundrysejam`;

CREATE TABLE `admin` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `layanan` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_layanan` VARCHAR(255) NOT NULL,
  `harga` INT UNSIGNED NOT NULL,
  `durasi` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `lokasi` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_lokasi` VARCHAR(255) NOT NULL,
  `alamat` TEXT NOT NULL,
  `image_url` VARCHAR(255) DEFAULT NULL,
  `maps_url` VARCHAR(255) DEFAULT NULL,
  `latitude` DECIMAL(10,7) NOT NULL DEFAULT 0.0,
  `longitude` DECIMAL(10,7) NOT NULL DEFAULT 0.0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `promo` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `judul_promo` VARCHAR(255) NOT NULL,
  `deskripsi` TEXT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `testimoni` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_pelanggan` VARCHAR(255) NOT NULL,
  `isi_testimoni` TEXT NOT NULL,
  `rating` TINYINT UNSIGNED NOT NULL DEFAULT 5,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `members` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `member_id` INT UNSIGNED NOT NULL,
  `layanan_id` INT UNSIGNED NOT NULL,
  `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
  `lokasi_id` INT UNSIGNED DEFAULT NULL,
  `delivery_type` ENUM('dropoff','pickup','selfservice') NOT NULL,
  `pickup_distance` DECIMAL(6,1) NOT NULL DEFAULT 0.0,
  `payment_method` ENUM('qris','transfer','cash','branch') NOT NULL,
  `payment_proof` VARCHAR(255) DEFAULT NULL,
  `delivery_fee` INT UNSIGNED NOT NULL DEFAULT 0,
  `soap_addon` TINYINT(1) NOT NULL DEFAULT 0,
  `fragrance_addon` TINYINT(1) NOT NULL DEFAULT 0,
  `soap_addon_qty` INT UNSIGNED NOT NULL DEFAULT 0,
  `fragrance_addon_qty` INT UNSIGNED NOT NULL DEFAULT 0,
  `total_price` INT UNSIGNED NOT NULL,
  `status` VARCHAR(100) NOT NULL DEFAULT 'Pembayaran Selesai',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `member_id_idx` (`member_id`),
  KEY `layanan_id_idx` (`layanan_id`),
  KEY `lokasi_id_idx` (`lokasi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admin` (`username`, `password`) VALUES
  ('admin', '$2y$10$UsK2Mg3CFPkWtd596gwKPenbTc84Rn2Iox.Rs8S5gkUK55Wq0ePzC');

INSERT INTO `members` (`username`, `password`, `name`, `email`) VALUES
  ('member1', '$2y$10$StV0CnyGJMWNrUdLRXrdKuVkg.MWDMz9W.zRKtqzvYnfMjmaEIfMS', 'Member Satu', 'member1@example.com');

INSERT INTO `layanan` (`nama_layanan`, `harga`, `durasi`) VALUES
  ('Cuci Kering', 15000, '45 Menit'),
  ('Cuci Express', 25000, '30 Menit');

INSERT INTO `lokasi` (`nama_lokasi`, `alamat`, `image_url`, `maps_url`) VALUES
  ('Cabang UMY', 'Jl. Lingkar Selatan, Tamantirto, Kasihan, Bantul (Depan Kampus Terpadu UMY)', 'https://lh3.googleusercontent.com/aida-public/AB6AXuDMoZ6bvRbYbzRpZCxdAKld3TOD8e-C-FXXhNofzOspvQ_ZOrLnWfBHH4dcVGTyakgj6CY4QJBT84yDxLYu0QGVZvTyiHQ5OF93c5XIC9kFycxvLMse99GGJtJ5GK7qEqk2Tk6x1_5JaekBZ0BqGT9CRq7vWtxbFMu7CN7mq0x1r5ESu3Np4mjE6wnYovO-hfnbYzCNDyuMlL7Xa38ennk_0LuE66uYZhlT_uSUriUZjSnkbe9fciIHrC7NY1k9Dt1SG8ifN0oKEUzZ', 'https://www.google.com/maps/search/?api=1&query=Jl.+Lingkar+Selatan,+Tamantirto,+Kasihan,+Bantul'),
  ('Cabang Nologaten', 'Jl. Wahid Hasyim No. 12, Nologaten, Caturtunggal (Dekat Amplaz)', 'https://lh3.googleusercontent.com/aida-public/AB6AXuDSD9XD9xcd2jFZxKIf585zX-ewIKp1JftmH09LUfG8YuUeI9Nr-HpjlusXz5rsrkdIEkeJO5JW83gz2i0e5AXN254jV6s4Mb1tvZPRqjxlS_e-KQAZYOz5Y77FeD8L9bKgsBvJhSh7_7-R8bcitSJBpW2bbcSQ1mW5m4E5dLIXgwrsuC6H1d-VmQAWClcmk3KysMPw8KMyfaQEeBdZW1j4YBubVVIGyIBu-207w5Pf_Wl3HgljH-JyDSVsVzI1FYVhgSYM9oQJZ01s', 'https://www.google.com/maps/search/?api=1&query=Jl.+Wahid+Hasyim+No.+12,+Nologaten,+Caturtunggal'),
  ('Cabang Balong', 'Jl. Balong - Degolan, Ngemplak, Sleman (Area Kost UII)', 'https://lh3.googleusercontent.com/aida-public/AB6AXuDfAzODZ11Y1hBThAgtKSiyFqZBg3WSxYXcQW19mc4f_yuwjDpgQSYAQspQk86let6vSj5xVeFnhOECNLqxmmTH-4hRKOhEQ9j4fa_davD2LIDVIAPvYq3mZSdDNbZJ5HdjmI7aDGRI5lk4SwIlKI9Ulc3pLoGvxxlhWm5Y6XTOtBOGOs6oGTNFf-r-ugN4B7CYtFV65hQ1AMkmvVFKLv_ObGVnnUkGfkH-cI9vW3nRnemZIjwcERHyIe-G-AiW0Y_aiV7eEC6zio5y', 'https://www.google.com/maps/search/?api=1&query=Jl.+Balong+-+Degolan,+Ngemplak,+Sleman');

INSERT INTO `promo` (`judul_promo`, `deskripsi`) VALUES
  ('Diskon 20% untuk cuci express', 'Diskon 20% untuk layanan cuci express (paket express selesai 1 jam).'),
  ('Promo Spesial Hari Ini', 'Promo spesial dan paket hemat untuk pelanggan baru dan member.');

INSERT INTO `testimoni` (`nama_pelanggan`, `isi_testimoni`, `rating`) VALUES
  ('Sari Wijaya', 'Mesinnya baru semua, kenceng banget puterannya jadi bajunya hampir kering pas keluar. Sabunnya juga wangi. Recommended!', 5),
  ('Budi Santoso', 'Paling suka karena 1 mesin 1 pelanggan, jadi gak takut baju ketuker atau gak higienis. Pelayanan staf juga ramah.', 5),
  ('Dina Rahma', 'Sangat membantu banget buat anak kost kaya aku. Harganya murah, tempatnya bersih, dan yang paling penting cepet banget selesainya!', 5);
