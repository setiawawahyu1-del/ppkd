<?php
// ============================================================
// Alumni Detail — alumni-detail.php
// ============================================================
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . SITE_URL . '/alumni.php');
    exit;
}

$db = getDB();
$stmt = $db->prepare(
    'SELECT a.*, j.nama_jurusan, j.kode
     FROM alumni a
     JOIN jurusan j ON a.jurusan_id = j.id
     WHERE a.id = ? AND a.is_published = 1'
);
$stmt->execute([$id]);
$alumni = $stmt->fetch();

if (!$alumni) {
    header('HTTP/1.0 404 Not Found');
    $pageTitle = 'Alumni Tidak Ditemukan — ' . SITE_NAME;
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container" style="padding:8rem 0;text-align:center;">
        <h2>Alumni tidak ditemukan</h2>
        <p class="text-muted">Data yang Anda cari tidak tersedia atau belum dipublikasikan.</p>
        <a href="' . SITE_URL . '/alumni.php" class="btn btn-primary" style="margin-top:1rem;">Kembali ke Direktori</a>
    </div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle       = e($alumni['nama_lengkap']) . ' — Alumni PPKD';
$pageDescription = 'Profil ' . $alumni['nama_lengkap'] . ', alumni PPKD jurusan ' . $alumni['nama_jurusan'] . ' tahun ' . $alumni['tahun_lulus'] . '.';

require_once __DIR__ . '/includes/header.php';
?>

<!-- ===== DETAIL HEADER ===== -->
<div class="alumni-detail-header" id="alumniDetailHeader">
  <div class="container">
    <nav class="breadcrumb-nav" aria-label="Breadcrumb">
      <a href="<?= SITE_URL ?>">Beranda</a>
      <span class="sep"><i data-lucide="chevron-right" style="width:13px;height:13px"></i></span>
      <a href="<?= SITE_URL ?>/alumni.php">Direktori Alumni</a>
      <span class="sep"><i data-lucide="chevron-right" style="width:13px;height:13px"></i></span>
      <span class="current"><?= e($alumni['nama_lengkap']) ?></span>
    </nav>
  </div>
</div>

<!-- ===== DETAIL CONTENT ===== -->
<section class="section bg-gray" aria-label="Profil alumni">
  <div class="container">
    <div style="max-width:860px;margin:0 auto;">

      <?php if (isAdmin()): ?>
      <!-- Admin Quick Action Banner -->
      <div class="admin-toolbar-banner" style="margin-bottom:1.5rem;">
        <div style="display:flex;align-items:center;gap:0.75rem;">
          <span class="badge-pill badge-role-admin" style="font-size:0.7rem;">ADMIN CONTROL</span>
          <span style="font-size:var(--text-sm);font-weight:600;">Mode Administrator: Anda dapat mengubah data alumni ini secara langsung.</span>
        </div>
        <div style="display:flex;gap:0.5rem;">
          <a href="<?= SITE_URL ?>/dashboard/alumni-edit.php?id=<?= $alumni['id'] ?>" class="btn btn-primary btn-sm">
            <i data-lucide="edit-3" style="width:14px;height:14px"></i> Ubah Data Alumni
          </a>
          <a href="<?= SITE_URL ?>/dashboard/alumni.php" class="btn btn-outline-light btn-sm">
            Kelola Semua
          </a>
        </div>
      </div>
      <?php endif; ?>

      <!-- Card Header -->
      <div class="alumni-detail-card" id="alumniDetailCard">
        <img src="<?= alumniPhoto($alumni['foto'], $alumni['nama_lengkap']) ?>"
             alt="Foto profil <?= e($alumni['nama_lengkap']) ?>"
             class="alumni-detail-avatar"
             onerror="this.src='<?= SITE_URL ?>/assets/img/avatar-placeholder.svg'">

        <div class="alumni-detail-info" style="flex:1;">
          <h1 id="alumniDetailName"><?= e($alumni['nama_lengkap']) ?></h1>

          <div class="alumni-detail-tags">
            <span class="tag tag-navy">
              <i data-lucide="hash" style="width:11px;height:11px"></i>
              Nomor Urut <?= e($alumni['nomor_urut']) ?>
            </span>
            <span class="tag tag-gray">
              <i data-lucide="graduation-cap" style="width:11px;height:11px"></i>
              <?= e($alumni['nama_jurusan']) ?>
            </span>
            <span class="tag tag-gray">
              <i data-lucide="calendar" style="width:11px;height:11px"></i>
              Lulus <?= e($alumni['tahun_lulus']) ?>
            </span>
            <?php if ($alumni['kota']): ?>
            <span class="tag tag-gray">
              <i data-lucide="map-pin" style="width:11px;height:11px"></i>
              <?= e($alumni['kota']) ?>
            </span>
            <?php endif; ?>
          </div>

          <?php if ($alumni['pekerjaan']): ?>
          <p style="font-size:var(--text-sm);color:var(--gray-600);margin:0;font-weight:500;">
            <i data-lucide="briefcase" style="width:14px;height:14px;margin-right:0.375rem;vertical-align:-2px"></i>
            <?= e($alumni['pekerjaan']) ?>
          </p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Description -->
      <?php if ($alumni['deskripsi']): ?>
      <div style="background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius-lg);padding:2rem;margin-top:1.5rem;">
        <h2 style="font-size:var(--text-lg);margin-bottom:1rem;">Tentang</h2>
        <p style="font-size:var(--text-base);color:var(--gray-600);line-height:1.8;margin:0;">
          <?= nl2br(e($alumni['deskripsi'])) ?>
        </p>
      </div>
      <?php endif; ?>

      <!-- Info Grid -->
      <div style="background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius-lg);padding:2rem;margin-top:1.5rem;">
        <h2 style="font-size:var(--text-lg);margin-bottom:1.5rem;">Informasi Alumni</h2>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
          <div>
            <span style="font-size:var(--text-xs);font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--gray-400);">Nomor Urut</span>
            <p style="font-size:var(--text-sm);font-weight:600;color:var(--gray-800);margin:0.25rem 0 0;">#<?= e($alumni['nomor_urut']) ?></p>
          </div>
          <div>
            <span style="font-size:var(--text-xs);font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--gray-400);">Program Keahlian</span>
            <p style="font-size:var(--text-sm);font-weight:600;color:var(--gray-800);margin:0.25rem 0 0;"><?= e($alumni['nama_jurusan']) ?></p>
          </div>
          <div>
            <span style="font-size:var(--text-xs);font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--gray-400);">Tahun Lulus</span>
            <p style="font-size:var(--text-sm);font-weight:600;color:var(--gray-800);margin:0.25rem 0 0;"><?= e($alumni['tahun_lulus']) ?></p>
          </div>
          <?php if ($alumni['kota']): ?>
          <div>
            <span style="font-size:var(--text-xs);font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--gray-400);">Kota</span>
            <p style="font-size:var(--text-sm);font-weight:600;color:var(--gray-800);margin:0.25rem 0 0;"><?= e($alumni['kota']) ?></p>
          </div>
          <?php endif; ?>
          <?php if ($alumni['pekerjaan']): ?>
          <div>
            <span style="font-size:var(--text-xs);font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--gray-400);">Pekerjaan</span>
            <p style="font-size:var(--text-sm);font-weight:600;color:var(--gray-800);margin:0.25rem 0 0;"><?= e($alumni['pekerjaan']) ?></p>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Back button -->
      <div style="margin-top:2rem;">
        <a href="<?= SITE_URL ?>/alumni.php" class="btn btn-outline" id="btnBackToDirectory">
          <i data-lucide="arrow-left" style="width:15px;height:15px"></i>
          Kembali ke Direktori
        </a>
      </div>

    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
