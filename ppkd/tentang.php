<?php
// ============================================================
// Tentang Kami — tentang.php
// ============================================================
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'Tentang Kami — ' . SITE_NAME;
$pageDescription = 'Pelajari lebih lanjut tentang Ikatan Alumni PPKD, visi, misi, dan sejarah organisasi.';

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <div class="page-header-inner">
      <nav class="breadcrumb-nav" aria-label="Breadcrumb">
        <a href="<?= SITE_URL ?>">Beranda</a>
        <span class="sep"><i data-lucide="chevron-right" style="width:13px;height:13px"></i></span>
        <span class="current">Tentang Kami</span>
      </nav>
      <h1>Tentang Ikatan Alumni PPKD</h1>
      <p class="subtitle">Mengenal lebih dekat organisasi alumni resmi PPKD</p>
    </div>
  </div>
</div>

<!-- Sejarah -->
<section class="section" aria-labelledby="sejarahTitle">
  <div class="container">
    <div class="about-grid" style="gap:4rem;">
      <div class="about-visual">
        <img src="<?= SITE_URL ?>/assets/img/about-placeholder.svg"
             alt="Foto gedung PPKD"
             class="about-img-primary"
             style="height:400px;">
        <div class="about-badge">
          <div class="about-badge-icon">
            <i data-lucide="landmark" style="width:18px;height:18px"></i>
          </div>
          <div class="about-badge-text">
            <span>Didirikan pada</span>
            <strong>Tahun 2010</strong>
          </div>
        </div>
      </div>
      <div>
        <span class="label-tag">Sejarah Singkat</span>
        <h2 class="section-title" id="sejarahTitle">Perjalanan Ikatan Alumni PPKD</h2>
        <div style="color:var(--gray-600);line-height:1.85;margin-top:1rem;">
          <p>
            Ikatan Alumni PPKD berdiri pada tahun 2010 sebagai wadah resmi yang menaungi
            seluruh alumni dari berbagai program keahlian di Pusat Pelatihan Kerja Daerah (PPKD).
            Didirikan atas dasar kesamaan almamater dan semangat kebersamaan, organisasi ini
            terus berkembang hingga memiliki ribuan anggota aktif di seluruh Indonesia.
          </p>
          <p>
            Selama lebih dari satu dekade, Ikatan Alumni PPKD telah menyelenggarakan berbagai
            program yang bermanfaat bagi anggota dan masyarakat, mulai dari pelatihan vokasi,
            program beasiswa, hingga kegiatan sosial kemasyarakatan.
          </p>
          <p>
            Dengan hadirnya platform digital ini, kami berharap dapat semakin mempererat
            hubungan antar alumni dan mempermudah akses informasi bagi seluruh anggota
            komunitas alumni PPKD.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Visi Misi -->
<section class="section bg-gray" aria-labelledby="visiMisiTitle">
  <div class="container">
    <div class="text-center" style="max-width:640px;margin:0 auto 3.5rem;">
      <span class="label-tag">Arah &amp; Tujuan</span>
      <h2 class="section-title" id="visiMisiTitle">Visi &amp; Misi</h2>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;max-width:900px;margin:0 auto;">
      <!-- Visi -->
      <div style="background:var(--navy-800);color:var(--white);border-radius:var(--radius-lg);padding:2.5rem;">
        <div style="width:48px;height:48px;background:rgba(255,255,255,0.1);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;margin-bottom:1.5rem;">
          <i data-lucide="eye" style="width:22px;height:22px"></i>
        </div>
        <h3 style="color:var(--white);font-size:var(--text-xl);margin-bottom:1rem;">Visi</h3>
        <p style="color:rgba(255,255,255,0.75);line-height:1.8;margin:0;">
          Menjadi organisasi alumni yang solid, profesional, dan berkontribusi nyata
          bagi pembangunan sumber daya manusia Indonesia melalui kolaborasi dan
          pengembangan berkelanjutan.
        </p>
      </div>

      <!-- Misi -->
      <div style="background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius-lg);padding:2.5rem;">
        <div style="width:48px;height:48px;background:var(--accent-xs);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;margin-bottom:1.5rem;color:var(--accent);">
          <i data-lucide="target" style="width:22px;height:22px"></i>
        </div>
        <h3 style="font-size:var(--text-xl);margin-bottom:1rem;color:var(--gray-900);">Misi</h3>
        <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:0.75rem;">
          <?php
          $missions = [
            'Mempererat tali silaturahmi antar alumni PPKD',
            'Meningkatkan kualitas sumber daya alumni melalui pelatihan',
            'Membangun jaringan profesional yang kuat dan bermanfaat',
            'Berkontribusi positif bagi almamater dan masyarakat',
            'Menyediakan informasi dan layanan bagi seluruh alumni',
          ];
          foreach ($missions as $m): ?>
          <li style="display:flex;align-items:flex-start;gap:0.625rem;font-size:var(--text-sm);color:var(--gray-600);line-height:1.6;">
            <span style="width:18px;height:18px;background:var(--accent-xs);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--accent);flex-shrink:0;margin-top:1px;">
              <i data-lucide="check" style="width:10px;height:10px"></i>
            </span>
            <?= e($m) ?>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- Program -->
<section class="section" aria-labelledby="programTitle">
  <div class="container">
    <div class="text-center" style="max-width:580px;margin:0 auto 3.5rem;">
      <span class="label-tag">Program Unggulan</span>
      <h2 class="section-title" id="programTitle">Apa yang Kami Lakukan</h2>
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;" id="programGrid">
      <?php
      $programs = [
        ['icon'=>'users','title'=>'Jaringan Alumni','desc'=>'Menghubungkan alumni dari berbagai angkatan dan program keahlian dalam satu komunitas yang solid.'],
        ['icon'=>'book-open','title'=>'Program Beasiswa','desc'=>'Memberikan dukungan beasiswa bagi alumni dan siswa berprestasi yang membutuhkan bantuan pendidikan.'],
        ['icon'=>'briefcase','title'=>'Bursa Kerja','desc'=>'Menyediakan informasi lowongan pekerjaan dan kesempatan karir khusus bagi anggota alumni.'],
        ['icon'=>'calendar','title'=>'Event &amp; Reuni','desc'=>'Menyelenggarakan berbagai kegiatan pertemuan, reuni, dan acara sosial yang mempererat persaudaraan.'],
        ['icon'=>'trending-up','title'=>'Pengembangan Karir','desc'=>'Pelatihan, workshop, dan seminar untuk meningkatkan kompetensi dan daya saing alumni di dunia kerja.'],
        ['icon'=>'heart','title'=>'Kegiatan Sosial','desc'=>'Bakti sosial dan kegiatan pengabdian masyarakat sebagai wujud kepedulian alumni PPKD.'],
      ];
      foreach ($programs as $prog): ?>
      <div style="background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius-lg);padding:2rem;">
        <div style="width:44px;height:44px;background:var(--accent-xs);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;color:var(--accent);margin-bottom:1.25rem;">
          <i data-lucide="<?= $prog['icon'] ?>" style="width:20px;height:20px"></i>
        </div>
        <h3 style="font-size:var(--text-base);font-weight:700;margin-bottom:0.625rem;color:var(--gray-900);"><?= $prog['title'] ?></h3>
        <p style="font-size:var(--text-sm);color:var(--gray-500);line-height:1.7;margin:0;"><?= $prog['desc'] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="section cta-section">
  <div class="container">
    <div class="cta-inner">
      <h2>Jadilah Bagian dari Komunitas</h2>
      <p>Daftarkan dirimu dan bergabunglah dengan ribuan alumni PPKD yang sudah terhubung.</p>
      <div class="cta-actions">
        <a href="<?= SITE_URL ?>/register.php" class="btn btn-primary btn-lg" id="btnTentangRegister">
          <i data-lucide="user-plus" style="width:17px;height:17px"></i>
          Daftar Sekarang
        </a>
        <a href="<?= SITE_URL ?>/kontak.php" class="btn btn-outline-light btn-lg" id="btnTentangKontak">Hubungi Kami</a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
