-- ============================================================
-- Ikatan Alumni PPKD — Database Schema
-- MySQL / MariaDB | UTF8MB4
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";

CREATE DATABASE IF NOT EXISTS `ppkd_alumni`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `ppkd_alumni`;

-- ------------------------------------------------------------
-- Tabel: users
-- ------------------------------------------------------------
CREATE TABLE `users` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `nama`         VARCHAR(150)    NOT NULL,
  `email`        VARCHAR(200)    NOT NULL,
  `password`     VARCHAR(255)    NOT NULL,
  `role`         ENUM('alumni','instruktur','admin') NOT NULL DEFAULT 'alumni',
  `foto`         VARCHAR(255)    DEFAULT NULL,
  `is_verified`  TINYINT(1)      NOT NULL DEFAULT 0,
  `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: jurusan
-- ------------------------------------------------------------
CREATE TABLE `jurusan` (
  `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `nama_jurusan`  VARCHAR(150)    NOT NULL,
  `kode`          VARCHAR(20)     NOT NULL,
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kode` (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: alumni
-- ------------------------------------------------------------
CREATE TABLE `alumni` (
  `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `user_id`       INT UNSIGNED    DEFAULT NULL,
  `nomor_urut`    INT UNSIGNED    NOT NULL,
  `nama_lengkap`  VARCHAR(200)    NOT NULL,
  `jurusan_id`    INT UNSIGNED    NOT NULL,
  `tahun_lulus`   YEAR(4)         NOT NULL,
  `foto`          VARCHAR(255)    DEFAULT NULL,
  `deskripsi`     TEXT            DEFAULT NULL,
  `kota`          VARCHAR(100)    DEFAULT NULL,
  `pekerjaan`     VARCHAR(200)    DEFAULT NULL,
  `is_published`  TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_nomor_urut` (`nomor_urut`),
  KEY `idx_jurusan` (`jurusan_id`),
  KEY `idx_tahun` (`tahun_lulus`),
  KEY `idx_nama` (`nama_lengkap`),
  CONSTRAINT `fk_alumni_jurusan` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: berita
-- ------------------------------------------------------------
CREATE TABLE `berita` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `judul`        VARCHAR(255)    NOT NULL,
  `slug`         VARCHAR(255)    NOT NULL,
  `konten`       LONGTEXT        NOT NULL,
  `ringkasan`    TEXT            DEFAULT NULL,
  `thumbnail`    VARCHAR(255)    DEFAULT NULL,
  `kategori`     VARCHAR(100)    DEFAULT 'Umum',
  `author_id`    INT UNSIGNED    DEFAULT NULL,
  `is_published` TINYINT(1)      NOT NULL DEFAULT 1,
  `published_at` DATETIME        DEFAULT CURRENT_TIMESTAMP,
  `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_slug` (`slug`),
  KEY `idx_kategori` (`kategori`),
  KEY `idx_published` (`is_published`, `published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: kontak_pesan
-- ------------------------------------------------------------
CREATE TABLE `kontak_pesan` (
  `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama`      VARCHAR(150) NOT NULL,
  `email`     VARCHAR(200) NOT NULL,
  `subjek`    VARCHAR(255) NOT NULL,
  `pesan`     TEXT         NOT NULL,
  `is_read`   TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Jurusan
INSERT INTO `jurusan` (`nama_jurusan`, `kode`) VALUES
('Teknik Komputer dan Jaringan',  'TKJ'),
('Multimedia',                     'MM'),
('Akuntansi',                      'AK'),
('Administrasi Perkantoran',       'AP'),
('Pemasaran',                      'PM'),
('Teknik Elektronika Industri',    'TEI'),
('Desain Grafis',                  'DG'),
('Animasi',                        'AN');

-- Admin user (password: admin123)
INSERT INTO `users` (`nama`, `email`, `password`, `role`, `is_verified`) VALUES
('Administrator PPKD', 'admin@ppkd.ac.id', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

-- Alumni seed data
INSERT INTO `alumni` (`nomor_urut`, `nama_lengkap`, `jurusan_id`, `tahun_lulus`, `foto`, `deskripsi`, `kota`, `pekerjaan`, `is_published`) VALUES
(1,  'Ahmad Fauzi Ramadhan',      1, 2020, NULL, 'Alumni TKJ angkatan 2020. Saat ini bekerja sebagai Network Engineer di salah satu perusahaan teknologi di Jakarta.', 'Jakarta', 'Network Engineer', 1),
(2,  'Siti Nur Aisyah',           2, 2020, NULL, 'Lulusan Multimedia yang kini aktif sebagai Content Creator dan Videographer freelance.', 'Bandung', 'Content Creator', 1),
(3,  'Budi Santoso',              3, 2021, NULL, 'Alumni Akuntansi yang bekerja di kantor akuntan publik di Jakarta Selatan.', 'Jakarta', 'Staff Akuntansi', 1),
(4,  'Dewi Kusuma Wardani',       1, 2021, NULL, 'Teknisi jaringan berpengalaman dengan spesialisasi di bidang keamanan jaringan.', 'Surabaya', 'IT Security Analyst', 1),
(5,  'Rizky Pratama Putra',       4, 2022, NULL, 'Bekerja sebagai sekretaris eksekutif di perusahaan multinasional.', 'Jakarta', 'Executive Secretary', 1),
(6,  'Nurul Hidayati',            2, 2022, NULL, 'Desainer grafis dan videografer berbakat yang aktif di industri kreatif.', 'Yogyakarta', 'Graphic Designer', 1),
(7,  'Muhammad Iqbal',            1, 2022, NULL, 'Web developer dengan pengalaman di berbagai proyek teknologi informasi.', 'Jakarta', 'Web Developer', 1),
(8,  'Indah Permatasari',         3, 2023, NULL, 'Staff keuangan yang berdedikasi dengan latar belakang akuntansi yang kuat.', 'Bekasi', 'Staff Keuangan', 1),
(9,  'Dimas Aditya Nugroho',      5, 2023, NULL, 'Marketing specialist yang berpengalaman di bidang digital marketing.', 'Jakarta', 'Digital Marketing Specialist', 1),
(10, 'Fitri Handayani',           6, 2023, NULL, 'Teknisi elektronika yang bekerja di industri manufaktur.', 'Karawang', 'Teknisi Elektronika', 1),
(11, 'Yanuar Setiawan',           7, 2024, NULL, 'Desainer grafis muda yang berbakat dengan portofolio yang beragam.', 'Jakarta', 'Junior Graphic Designer', 1),
(12, 'Mega Nur Fadhilah',         2, 2024, NULL, 'Animator 2D dan 3D yang aktif di studio animasi lokal.', 'Depok', 'Animator', 1),
(13, 'Arief Rahman Hakim',        1, 2024, NULL, 'Fresh graduate TKJ yang sedang membangun karir di bidang IT.', 'Tangerang', 'IT Support', 1),
(14, 'Putri Amelia Sari',         4, 2024, NULL, 'Administrasi perkantoran yang teliti dan berpengalaman.', 'Jakarta', 'Admin Staff', 1),
(15, 'Fajar Hidayat',             8, 2024, NULL, 'Animator berbakat yang baru lulus dan aktif mengerjakan proyek freelance.', 'Bogor', 'Freelance Animator', 1);

-- Berita seed data
INSERT INTO `berita` (`judul`, `slug`, `konten`, `ringkasan`, `thumbnail`, `kategori`, `is_published`, `published_at`) VALUES
(
  'Reuni Akbar Alumni PPKD 2025 Resmi Diumumkan',
  'reuni-akbar-alumni-ppkd-2025',
  '<p>Ikatan Alumni PPKD dengan bangga mengumumkan pelaksanaan Reuni Akbar Alumni PPKD 2025 yang akan diselenggarakan pada bulan Agustus mendatang. Acara ini merupakan momen istimewa bagi seluruh keluarga besar alumni untuk berkumpul kembali, mempererat silaturahmi, dan berbagi cerita perjalanan karir masing-masing.</p><p>Reuni akbar ini dijadwalkan akan dihadiri oleh ratusan alumni dari berbagai angkatan dan jurusan. Berbagai rangkaian acara menarik telah disiapkan, mulai dari seminar karir, sesi networking, hingga malam keakraban yang penuh kenangan.</p><p>Pendaftaran peserta sudah dibuka dan dapat dilakukan melalui website resmi Ikatan Alumni PPKD. Kapasitas tempat terbatas, jadi segera daftarkan diri Anda.</p>',
  'Ikatan Alumni PPKD mengumumkan Reuni Akbar 2025 yang akan mempertemukan ratusan alumni dari berbagai angkatan. Pendaftaran sudah dibuka.',
  NULL,
  'Pengumuman',
  1,
  '2026-10-28 09:00:00'
),
(
  'Program Beasiswa Alumni PPKD Kembali Dibuka untuk Tahun Ajaran Baru',
  'program-beasiswa-alumni-ppkd-2025',
  '<p>Melalui program Corporate Social Responsibility (CSR) para alumni sukses, Ikatan Alumni PPKD kembali membuka program beasiswa untuk siswa berprestasi dari keluarga kurang mampu yang ingin melanjutkan pendidikan ke jenjang lebih tinggi.</p><p>Program beasiswa ini mencakup bantuan biaya pendidikan, biaya hidup, serta pendampingan akademik dari mentor yang merupakan alumni PPKD berpengalaman di bidangnya.</p><p>Kriteria penerima beasiswa meliputi prestasi akademik minimal rata-rata 8,0, berasal dari keluarga dengan penghasilan di bawah UMR, dan memiliki motivasi tinggi untuk berkembang.</p>',
  'Program beasiswa dari Ikatan Alumni PPKD kembali dibuka untuk membantu siswa berprestasi melanjutkan pendidikan ke jenjang lebih tinggi.',
  NULL,
  'Beasiswa',
  1,
  '2026-10-25 10:30:00'
),
(
  'Alumni PPKD Raih Penghargaan Nasional di Bidang Teknologi Informasi',
  'alumni-ppkd-raih-penghargaan-nasional-ti',
  '<p>Kebanggaan bagi keluarga besar Ikatan Alumni PPKD, salah satu alumni jurusan Teknik Komputer dan Jaringan berhasil meraih penghargaan nasional dalam ajang kompetisi inovasi teknologi informasi yang diselenggarakan oleh Kementerian Komunikasi dan Informatika.</p><p>Alumni tersebut berhasil mengembangkan aplikasi manajemen jaringan berbasis kecerdasan buatan yang mampu mendeteksi ancaman keamanan siber secara real-time. Inovasi ini mendapat apresiasi tinggi dari para juri yang terdiri dari pakar teknologi terkemuka.</p><p>Prestasi ini membuktikan bahwa pendidikan vokasi di PPKD mampu menghasilkan lulusan yang kompeten dan berdaya saing di tingkat nasional.</p>',
  'Alumni TKJ PPKD berhasil meraih penghargaan nasional dalam ajang kompetisi inovasi teknologi yang diselenggarakan Kemenkominfo.',
  NULL,
  'Prestasi',
  1,
  '2026-10-20 14:00:00'
),
(
  'Workshop Digital Marketing untuk Alumni: Peluang Karir di Era Digital',
  'workshop-digital-marketing-alumni-ppkd',
  '<p>Ikatan Alumni PPKD menyelenggarakan workshop intensif bertema Digital Marketing yang terbuka bagi seluruh alumni. Workshop ini menghadirkan narasumber berpengalaman dari industri digital marketing terkemuka di Indonesia.</p><p>Peserta akan mendapatkan pemahaman mendalam tentang strategi pemasaran digital, pengelolaan media sosial untuk bisnis, teknik SEO dan SEM, serta cara memanfaatkan data analitik untuk pengambilan keputusan bisnis.</p><p>Workshop berlangsung selama dua hari penuh dan peserta akan mendapatkan sertifikat kehadiran serta kesempatan bergabung dalam komunitas alumni yang aktif di bidang digital marketing.</p>',
  'Workshop Digital Marketing intensif digelar oleh Ikatan Alumni PPKD dengan menghadirkan praktisi berpengalaman dari industri digital.',
  NULL,
  'Event',
  1,
  '2026-10-15 08:00:00'
);
