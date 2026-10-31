<?php
// ============================================================
// Berita Listing — berita.php
// ============================================================
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'Berita & Informasi — ' . SITE_NAME;
$pageDescription = 'Berita terbaru, pengumuman, dan informasi dari Ikatan Alumni PPKD.';

$db       = getDB();
$kategori = trim($_GET['kategori'] ?? '');
$page     = max(1, (int) ($_GET['page'] ?? 1));
$perPage  = BERITA_PER_PAGE;
$offset   = ($page - 1) * $perPage;

$where  = ['is_published = 1'];
$params = [];
if ($kategori !== '') {
    $where[]  = 'kategori = ?';
    $params[] = $kategori;
}
$whereClause = 'WHERE ' . implode(' AND ', $where);

$total     = (int) $db->prepare("SELECT COUNT(*) FROM berita $whereClause")->execute($params) ?
             $db->prepare("SELECT COUNT(*) FROM berita $whereClause")->execute($params) : 0;

// Re-run count properly
$cntStmt = $db->prepare("SELECT COUNT(*) FROM berita $whereClause");
$cntStmt->execute($params);
$total = (int) $cntStmt->fetchColumn();

$stmt = $db->prepare("SELECT * FROM berita $whereClause ORDER BY published_at DESC LIMIT ? OFFSET ?");
$stmt->execute(array_merge($params, [$perPage, $offset]));
$beritas = $stmt->fetchAll();

$pagination = paginate($total, $perPage, $page, '?kategori=' . urlencode($kategori));

// Fetch categories for filter
$catStmt = $db->query("SELECT DISTINCT kategori FROM berita WHERE is_published=1 ORDER BY kategori");
$categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <div class="page-header-inner">
      <nav class="breadcrumb-nav" aria-label="Breadcrumb">
        <a href="<?= SITE_URL ?>">Beranda</a>
        <span class="sep"><i data-lucide="chevron-right" style="width:13px;height:13px"></i></span>
        <span class="current">Berita &amp; Informasi</span>
      </nav>
      <h1>Berita &amp; Informasi</h1>
      <p class="subtitle">Informasi terbaru dari Ikatan Alumni PPKD</p>
    </div>
  </div>
</div>

<section class="section bg-gray" aria-labelledby="beritaListTitle">
  <div class="container">

    <!-- Category Filter -->
    <?php if ($categories): ?>
    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:2rem;" role="navigation" aria-label="Filter kategori berita">
      <a href="?kategori="
         class="btn btn-sm <?= $kategori === '' ? 'btn-primary' : 'btn-ghost' ?>"
         style="border:1.5px solid var(--gray-200);"
         id="filter-all">Semua</a>
      <?php foreach ($categories as $cat): ?>
      <a href="?kategori=<?= urlencode($cat) ?>"
         class="btn btn-sm <?= $kategori === $cat ? 'btn-primary' : 'btn-ghost' ?>"
         style="border:1.5px solid var(--gray-200);"
         id="filter-<?= slugify($cat) ?>"><?= e($cat) ?></a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <h2 id="beritaListTitle" class="visually-hidden">Daftar Berita</h2>

    <?php if ($beritas): ?>
    <div class="berita-list-grid" id="beritaGrid">
      <?php foreach ($beritas as $b): ?>
      <a href="<?= SITE_URL ?>/berita-detail.php?slug=<?= e($b['slug']) ?>"
         class="berita-card"
         id="berita-card-<?= $b['id'] ?>">
        <img src="<?= beritaThumb($b['thumbnail']) ?>"
             alt="<?= e($b['judul']) ?>"
             class="berita-card-img"
             onerror="this.src='<?= SITE_URL ?>/assets/img/news-placeholder.svg'">
        <div class="berita-card-body">
          <span class="berita-kategori"><?= e($b['kategori']) ?></span>
          <h3 class="berita-judul"><?= e($b['judul']) ?></h3>
          <div class="berita-meta-row" style="margin-bottom:0.625rem;">
            <i data-lucide="calendar" style="width:12px;height:12px"></i>
            <span><?= formatDate($b['published_at']) ?></span>
          </div>
          <?php if ($b['ringkasan']): ?>
          <p class="alumni-desc" style="-webkit-line-clamp:3;"><?= e($b['ringkasan']) ?></p>
          <?php endif; ?>
          <span class="read-more" style="margin-top:0.875rem;">
            Baca selengkapnya
            <i data-lucide="arrow-right" style="width:13px;height:13px"></i>
          </span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    <?= paginationHtml($pagination) ?>

    <?php else: ?>
    <div class="empty-state">
      <div class="empty-state-icon">
        <i data-lucide="newspaper" style="width:28px;height:28px"></i>
      </div>
      <h3>Belum ada berita</h3>
      <p>Berita akan ditampilkan di sini</p>
    </div>
    <?php endif; ?>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
