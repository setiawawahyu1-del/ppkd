<?php
// ============================================================
// Berita Detail — berita-detail.php
// ============================================================
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
    header('Location: ' . SITE_URL . '/berita.php');
    exit;
}

$db   = getDB();
$stmt = $db->prepare('SELECT * FROM berita WHERE slug = ? AND is_published = 1');
$stmt->execute([$slug]);
$berita = $stmt->fetch();

if (!$berita) {
    header('HTTP/1.0 404 Not Found');
    $pageTitle = 'Berita Tidak Ditemukan — ' . SITE_NAME;
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container" style="padding:8rem 0;text-align:center;">
        <h2>Berita tidak ditemukan</h2>
        <p class="text-muted">Berita yang Anda cari tidak tersedia.</p>
        <a href="' . SITE_URL . '/berita.php" class="btn btn-primary" style="margin-top:1rem;">Kembali ke Berita</a>
    </div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Related news
$relStmt = $db->prepare(
    'SELECT * FROM berita
     WHERE id != ? AND is_published = 1 AND kategori = ?
     ORDER BY published_at DESC LIMIT 3'
);
$relStmt->execute([$berita['id'], $berita['kategori']]);
$related = $relStmt->fetchAll();

$pageTitle       = e($berita['judul']) . ' — ' . SITE_NAME;
$pageDescription = $berita['ringkasan'] ?? excerpt($berita['konten'], 160);

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <div class="page-header-inner">
      <nav class="breadcrumb-nav" aria-label="Breadcrumb">
        <a href="<?= SITE_URL ?>">Beranda</a>
        <span class="sep"><i data-lucide="chevron-right" style="width:13px;height:13px"></i></span>
        <a href="<?= SITE_URL ?>/berita.php">Berita</a>
        <span class="sep"><i data-lucide="chevron-right" style="width:13px;height:13px"></i></span>
        <span class="current"><?= e($berita['kategori']) ?></span>
      </nav>
    </div>
  </div>
</div>

<section class="section bg-gray" aria-label="Detail berita">
  <div class="container">
    <div style="max-width:800px;margin:0 auto;">

      <?php if (isAdmin()): ?>
      <!-- Admin Quick Action Banner -->
      <div class="admin-toolbar-banner" style="margin-bottom:1.5rem;">
        <div style="display:flex;align-items:center;gap:0.75rem;">
          <span class="badge-pill badge-role-admin" style="font-size:0.7rem;">ADMIN CONTROL</span>
          <span style="font-size:var(--text-sm);font-weight:600;">Mode Administrator: Anda dapat mengubah isi artikel atau judul berita ini.</span>
        </div>
        <div style="display:flex;gap:0.5rem;">
          <a href="<?= SITE_URL ?>/dashboard/berita-edit.php?id=<?= $berita['id'] ?>" class="btn btn-primary btn-sm">
            <i data-lucide="edit-3" style="width:14px;height:14px"></i> Ubah Berita Ini
          </a>
          <a href="<?= SITE_URL ?>/dashboard/berita.php" class="btn btn-outline-light btn-sm">
            Kelola Semua
          </a>
        </div>
      </div>
      <?php endif; ?>

      <!-- Article -->
      <article id="articleContent">
        <!-- Category & meta -->
        <div style="margin-bottom:1.5rem;">
          <span class="berita-kategori" style="font-size:var(--text-xs);"><?= e($berita['kategori']) ?></span>
          <h1 style="font-size:var(--text-3xl);font-weight:800;line-height:1.25;letter-spacing:-0.02em;margin:0.75rem 0 1rem;color:var(--gray-900);">
            <?= e($berita['judul']) ?>
          </h1>
          <div class="berita-meta-row" style="font-size:var(--text-sm);">
            <i data-lucide="calendar" style="width:14px;height:14px"></i>
            <span><?= formatDate($berita['published_at'], 'd F Y') ?></span>
            <span style="color:var(--gray-300);">·</span>
            <i data-lucide="clock" style="width:14px;height:14px"></i>
            <span><?= timeAgo($berita['published_at']) ?></span>
          </div>
        </div>

        <!-- Thumbnail -->
        <img src="<?= beritaThumb($berita['thumbnail']) ?>"
             alt="<?= e($berita['judul']) ?>"
             style="width:100%;height:380px;object-fit:cover;border-radius:var(--radius-lg);margin-bottom:2rem;background:var(--gray-100);"
             onerror="this.src='<?= SITE_URL ?>/assets/img/news-placeholder.svg'">

        <!-- Content -->
        <div class="article-body" style="
          font-size:var(--text-base);
          line-height:1.85;
          color:var(--gray-700);
        ">
          <?= $berita['konten'] /* Trusted HTML from DB — sanitize on input */ ?>
        </div>
      </article>

      <!-- Back -->
      <div style="margin-top:2.5rem;padding-top:2rem;border-top:1px solid var(--gray-200);">
        <a href="<?= SITE_URL ?>/berita.php" class="btn btn-outline" id="btnBackToBerita">
          <i data-lucide="arrow-left" style="width:15px;height:15px"></i>
          Kembali ke Berita
        </a>
      </div>

      <!-- Related -->
      <?php if ($related): ?>
      <div style="margin-top:3rem;">
        <h2 style="font-size:var(--text-xl);font-weight:700;margin-bottom:1.5rem;">Berita Terkait</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.25rem;">
          <?php foreach ($related as $r): ?>
          <a href="<?= SITE_URL ?>/berita-detail.php?slug=<?= e($r['slug']) ?>"
             class="berita-card"
             id="related-<?= $r['id'] ?>">
            <img src="<?= beritaThumb($r['thumbnail']) ?>"
                 alt="<?= e($r['judul']) ?>"
                 class="berita-card-img"
                 style="height:140px;"
                 onerror="this.src='<?= SITE_URL ?>/assets/img/news-placeholder.svg'">
            <div class="berita-card-body">
              <span class="berita-kategori"><?= e($r['kategori']) ?></span>
              <h4 class="berita-judul"><?= e($r['judul']) ?></h4>
              <div class="berita-meta-row">
                <span><?= formatDate($r['published_at']) ?></span>
              </div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>
</section>

<style>
.article-body p { margin-bottom: 1.25rem; }
.article-body h2, .article-body h3 { color: var(--gray-900); margin-top: 2rem; margin-bottom: 1rem; }
.article-body ul, .article-body ol { padding-left: 1.5rem; margin-bottom: 1.25rem; }
.article-body li { margin-bottom: 0.5rem; }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
