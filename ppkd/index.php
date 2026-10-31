<?php
// ============================================================
// Homepage — index.php
// ============================================================
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'Ikatan Alumni PPKD — Menghubungkan Alumni, Membangun Masa Depan';
$pageDescription = 'Platform informasi dan pusat pendataan alumni PPKD. Temukan direktori alumni, berita terbaru, dan informasi organisasi di satu tempat.';
$bodyClass       = 'home-page';

// Fetch data
$stats       = getSiteStats();
$latestAlumni = getLatestAlumni(6);
$latestBerita = getLatestBerita(4);

require_once __DIR__ . '/includes/header.php';
?>

<!-- ===== HERO ===== -->
<section class="hero" id="hero" aria-labelledby="heroTitle">
  <div class="hero-bg" id="heroBg"></div>
  <div class="hero-overlay"></div>
  <div class="hero-pattern"></div>

  <div class="container">
    <div class="hero-content">

      <div class="hero-badge" id="heroBadge">
        <i data-lucide="users" style="width:13px;height:13px"></i>
        Selamat Datang di Ikatan Alumni PPKD
      </div>

      <h1 class="hero-title" id="heroTitle">
        Menghubungkan Alumni,<br>
        <em>Membangun Masa Depan.</em>
      </h1>

      <p class="hero-desc">
        Menjadi ruang bersama bagi keluarga besar alumni PPKD untuk tetap
        terhubung, berbagi informasi, dan membangun hubungan yang lebih erat.
      </p>

      <div class="hero-actions">
        <a href="<?= SITE_URL ?>/alumni.php" class="btn btn-primary btn-lg" id="btnJelajahiAlumni">
          <i data-lucide="search" style="width:17px;height:17px"></i>
          Jelajahi Alumni
        </a>
        <a href="<?= SITE_URL ?>/berita.php" class="btn btn-outline-light btn-lg" id="btnLihatBerita">
          <i data-lucide="newspaper" style="width:17px;height:17px"></i>
          Lihat Berita
        </a>
      </div>

    </div>
  </div>

  <div class="hero-scroll" aria-hidden="true">
    <div class="scroll-line"></div>
    <span>Gulir</span>
  </div>
</section>

<!-- ===== STATS ===== -->
<section class="stats-section" aria-label="Statistik Alumni">
  <div class="stats-inner">
    <div class="stat-item">
      <span class="stat-number">
        <span class="counter" data-target="<?= $stats['alumni'] ?>"><?= $stats['alumni'] ?></span>
      </span>
      <span class="stat-label">Total Alumni Terdaftar</span>
    </div>
    <div class="stat-item">
      <span class="stat-number">
        <span class="counter" data-target="<?= $stats['jurusan'] ?>"><?= $stats['jurusan'] ?></span>
      </span>
      <span class="stat-label">Program Keahlian</span>
    </div>
    <div class="stat-item">
      <span class="stat-number">
        <span class="counter" data-target="<?= $stats['angkatan'] ?>"><?= $stats['angkatan'] ?></span>
      </span>
      <span class="stat-label">Tahun Angkatan</span>
    </div>
    <div class="stat-item">
      <span class="stat-number">
        <span class="counter" data-target="<?= $stats['berita'] ?>"><?= $stats['berita'] ?></span>
      </span>
      <span class="stat-label">Berita &amp; Informasi</span>
    </div>
  </div>
</section>

<!-- ===== ABOUT ===== -->
<section class="section about-section" id="tentang" aria-labelledby="tentangTitle">
  <div class="container">
    <div class="about-grid">

      <!-- Visual -->
      <div class="about-visual">
        <img src="<?= SITE_URL ?>/assets/img/about-placeholder.svg"
             alt="Foto kegiatan alumni PPKD"
             class="about-img-primary"
             id="aboutImgPrimary">

        <div class="about-badge" id="aboutBadge">
          <div class="about-badge-icon">
            <i data-lucide="award" style="width:18px;height:18px"></i>
          </div>
          <div class="about-badge-text">
            <span>Berdiri sejak</span>
            <strong>Tahun 2010</strong>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="about-content">
        <span class="label-tag">Tentang Kami</span>
        <h2 class="section-title" id="tentangTitle">
          Ikatan Alumni PPKD
        </h2>
        <p class="section-subtitle" style="margin-top:1rem;">
          Organisasi alumni yang menjadi wadah resmi bagi seluruh lulusan
          Pusat Pelatihan Kerja Daerah (PPKD) untuk bersatu, berkontribusi,
          dan saling mendukung dalam perjalanan karir masing-masing.
        </p>

        <ul class="about-list">
          <li>
            <span class="check"><i data-lucide="check" style="width:11px;height:11px"></i></span>
            Menghubungkan ribuan alumni dari berbagai angkatan dan program keahlian
          </li>
          <li>
            <span class="check"><i data-lucide="check" style="width:11px;height:11px"></i></span>
            Menyediakan informasi beasiswa, pelatihan, dan pengembangan karir
          </li>
          <li>
            <span class="check"><i data-lucide="check" style="width:11px;height:11px"></i></span>
            Memfasilitasi jaringan profesional antar sesama alumni PPKD
          </li>
          <li>
            <span class="check"><i data-lucide="check" style="width:11px;height:11px"></i></span>
            Mendukung kegiatan sosial dan pengabdian masyarakat
          </li>
          <li>
            <span class="check"><i data-lucide="check" style="width:11px;height:11px"></i></span>
            Menjaga hubungan erat antara alumni dan almamater PPKD
          </li>
        </ul>

        <a href="<?= SITE_URL ?>/tentang.php" class="btn btn-primary" id="btnReadAbout">
          Selengkapnya
          <i data-lucide="arrow-right" style="width:15px;height:15px"></i>
        </a>
      </div>

    </div>
  </div>
</section>

<!-- ===== ALUMNI TERBARU ===== -->
<section class="section alumni-section" aria-labelledby="alumniSectionTitle">
  <div class="container">
    <div class="section-header">
      <div class="section-header-row">
        <div>
          <span class="label-tag">Direktori Alumni</span>
          <h2 class="section-title" id="alumniSectionTitle">Alumni Terbaru</h2>
        </div>
        <a href="<?= SITE_URL ?>/alumni.php" class="btn btn-outline" id="btnAllAlumni">
          Lihat Semua
          <i data-lucide="arrow-right" style="width:15px;height:15px"></i>
        </a>
      </div>
    </div>

    <?php if ($latestAlumni): ?>
    <div class="alumni-grid" id="homeAlumniGrid">
      <?php foreach ($latestAlumni as $al): ?>
      <a href="<?= SITE_URL ?>/alumni-detail.php?id=<?= $al['id'] ?>"
         class="alumni-card"
         id="alumni-home-<?= $al['id'] ?>">
        <div class="alumni-card-header">
          <img src="<?= alumniPhoto($al['foto'], $al['nama_lengkap']) ?>"
               alt="Foto <?= e($al['nama_lengkap']) ?>"
               class="alumni-avatar"
               onerror="this.src='<?= SITE_URL ?>/assets/img/avatar-placeholder.svg'">
          <div class="alumni-meta">
            <h3><?= e($al['nama_lengkap']) ?></h3>
            <span class="alumni-badge">#<?= e($al['nomor_urut']) ?></span>
          </div>
        </div>
        <div class="alumni-card-body">
          <div class="alumni-info-row">
            <div class="alumni-info-item">
              <i data-lucide="graduation-cap" style="width:13px;height:13px"></i>
              <span><?= e($al['nama_jurusan']) ?></span>
            </div>
            <div class="alumni-info-item">
              <i data-lucide="calendar" style="width:13px;height:13px"></i>
              <span>Lulus <?= e($al['tahun_lulus']) ?></span>
            </div>
            <?php if ($al['kota']): ?>
            <div class="alumni-info-item">
              <i data-lucide="map-pin" style="width:13px;height:13px"></i>
              <span><?= e($al['kota']) ?></span>
            </div>
            <?php endif; ?>
          </div>
          <?php if ($al['deskripsi']): ?>
          <p class="alumni-desc"><?= e(excerpt($al['deskripsi'], 100)) ?></p>
          <?php endif; ?>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
      <div class="empty-state-icon">
        <i data-lucide="users" style="width:28px;height:28px"></i>
      </div>
      <h3>Belum ada alumni terdaftar</h3>
      <p>Data alumni akan ditampilkan di sini</p>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ===== BERITA TERBARU ===== -->
<section class="section berita-section" aria-labelledby="beritaSectionTitle">
  <div class="container">
    <div class="section-header">
      <div class="section-header-row">
        <div>
          <span class="label-tag">Berita &amp; Informasi</span>
          <h2 class="section-title" id="beritaSectionTitle">Berita Terbaru</h2>
        </div>
        <a href="<?= SITE_URL ?>/berita.php" class="btn btn-outline" id="btnAllBerita">
          Semua Berita
          <i data-lucide="arrow-right" style="width:15px;height:15px"></i>
        </a>
      </div>
    </div>

    <?php if ($latestBerita): ?>
    <?php $featured = $latestBerita[0]; $rest = array_slice($latestBerita, 1); ?>
    <div class="berita-grid">

      <!-- Featured -->
      <a href="<?= SITE_URL ?>/berita-detail.php?slug=<?= e($featured['slug']) ?>"
         class="berita-featured"
         id="berita-featured-<?= $featured['id'] ?>">
        <img src="<?= beritaThumb($featured['thumbnail']) ?>"
             alt="<?= e($featured['judul']) ?>"
             class="berita-featured-img"
             onerror="this.src='<?= SITE_URL ?>/assets/img/news-placeholder.svg'">
        <div class="berita-featured-body">
          <span class="berita-kategori"><?= e($featured['kategori']) ?></span>
          <h3 class="berita-judul"><?= e($featured['judul']) ?></h3>
          <div class="berita-meta-row">
            <i data-lucide="calendar" style="width:12px;height:12px"></i>
            <span><?= formatDate($featured['published_at']) ?></span>
          </div>
          <?php if ($featured['ringkasan']): ?>
          <p class="berita-ringkasan"><?= e($featured['ringkasan']) ?></p>
          <?php endif; ?>
          <span class="read-more">
            Baca selengkapnya
            <i data-lucide="arrow-right" style="width:14px;height:14px"></i>
          </span>
        </div>
      </a>

      <!-- Stack -->
      <div class="berita-stack">
        <?php foreach ($rest as $b): ?>
        <a href="<?= SITE_URL ?>/berita-detail.php?slug=<?= e($b['slug']) ?>"
           class="berita-item"
           id="berita-item-<?= $b['id'] ?>">
          <img src="<?= beritaThumb($b['thumbnail']) ?>"
               alt="<?= e($b['judul']) ?>"
               class="berita-item-img"
               onerror="this.src='<?= SITE_URL ?>/assets/img/news-placeholder.svg'">
          <div class="berita-item-body">
            <span class="berita-kategori"><?= e($b['kategori']) ?></span>
            <h4 class="berita-judul"><?= e($b['judul']) ?></h4>
            <div class="berita-meta-row">
              <i data-lucide="calendar" style="width:11px;height:11px"></i>
              <span><?= formatDate($b['published_at']) ?></span>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>

    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ===== CTA ===== -->
<section class="section cta-section" aria-labelledby="ctaTitle">
  <div class="container">
    <div class="cta-inner">
      <span class="label-tag" style="color:rgba(255,255,255,0.6);margin-bottom:1rem;">Bergabung Bersama Kami</span>
      <h2 id="ctaTitle">Apakah kamu alumni PPKD?</h2>
      <p>
        Daftarkan dirimu sekarang dan jadilah bagian dari komunitas alumni
        yang terus berkembang. Tetap terhubung, berbagi inspirasi, dan
        berkontribusi untuk almamater.
      </p>
      <div class="cta-actions">
        <a href="<?= SITE_URL ?>/register.php" class="btn btn-primary btn-lg" id="btnCtaRegister">
          <i data-lucide="user-plus" style="width:17px;height:17px"></i>
          Daftar Sekarang
        </a>
        <a href="<?= SITE_URL ?>/alumni.php" class="btn btn-outline-light btn-lg" id="btnCtaDirectory">
          Lihat Direktori
        </a>
      </div>
    </div>
  </div>
</section>

<style>
/* Hero background placeholder (replace with actual image) */
.hero-bg {
  background: linear-gradient(
    135deg,
    var(--navy-900) 0%,
    var(--navy-700) 60%,
    var(--navy-500) 100%
  );
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
