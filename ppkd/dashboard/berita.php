<?php
// ============================================================
// Kelola Berita (Admin) — dashboard/berita.php
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db    = getDB();
$user  = currentUser();
$csrf  = csrfToken();

// Handle Delete Berita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrf($token)) {
        setFlash('danger', 'Token CSRF tidak valid.');
        header('Location: berita.php');
        exit;
    }

    $deleteId = (int) ($_POST['id'] ?? 0);
    if ($deleteId > 0) {
        $findStmt = $db->prepare('SELECT thumbnail, judul FROM berita WHERE id = ?');
        $findStmt->execute([$deleteId]);
        $target = $findStmt->fetch();

        if ($target) {
            if (!empty($target['thumbnail'])) {
                $filePath = UPLOAD_PATH . 'berita/' . $target['thumbnail'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
            $delStmt = $db->prepare('DELETE FROM berita WHERE id = ?');
            $delStmt->execute([$deleteId]);

            setFlash('success', 'Berita "' . $target['judul'] . '" berhasil dihapus.');
        } else {
            setFlash('danger', 'Berita tidak ditemukan.');
        }
    }
    header('Location: berita.php');
    exit;
}

// Filters & Pagination
$search   = trim($_GET['q'] ?? '');
$kategori = trim($_GET['kategori'] ?? '');
$status   = trim($_GET['status'] ?? '');
$page     = max(1, (int) ($_GET['page'] ?? 1));
$perPage  = 10;
$offset   = ($page - 1) * $perPage;

$where   = [];
$params  = [];

if ($search !== '') {
    $where[]  = 'judul LIKE ?';
    $params[] = '%' . $search . '%';
}

if ($kategori !== '') {
    $where[]  = 'kategori = ?';
    $params[] = $kategori;
}

if ($status === '1') {
    $where[] = 'is_published = 1';
} elseif ($status === '0') {
    $where[] = 'is_published = 0';
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Count Total
$cntSql = "SELECT COUNT(*) FROM berita $whereClause";
$cntStmt = $db->prepare($cntSql);
$cntStmt->execute($params);
$total = (int) $cntStmt->fetchColumn();

// Fetch Data
$sql = "SELECT * FROM berita
        $whereClause
        ORDER BY published_at DESC, id DESC
        LIMIT ? OFFSET ?";
$stmt = $db->prepare($sql);
$stmt->execute(array_merge($params, [$perPage, $offset]));
$beritaList = $stmt->fetchAll();

$queryParam = http_build_query([
    'q'        => $search,
    'kategori' => $kategori,
    'status'   => $status,
]);
$pagination = paginate($total, $perPage, $page, '?' . $queryParam);

// Existing categories for filter
$catStmt = $db->query("SELECT DISTINCT kategori FROM berita WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori");
$allCategories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Kelola Berita & Informasi — ' . SITE_NAME;
$bodyClass = 'dashboard-page';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="padding-top:70px;">
<div class="dashboard-layout">

  <!-- Sidebar -->
  <?php $activePage = 'berita'; require __DIR__ . '/sidebar.php'; ?>

  <!-- Main Content -->
  <div class="dashboard-main">

    <!-- Page Header & Action -->
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:2rem;">
      <div>
        <h1 style="font-size:var(--text-2xl);font-weight:800;color:var(--gray-900);margin:0 0 0.25rem;">
          Kelola Berita &amp; Informasi
        </h1>
        <p style="color:var(--gray-500);font-size:var(--text-sm);margin:0;">
          Daftar berita, pengumuman, dan artikel. Anda dapat mengubah isi, judul, foto, dan mempublikasikan berita.
        </p>
      </div>
      <div>
        <a href="<?= SITE_URL ?>/dashboard/berita-edit.php" class="btn btn-primary" id="btnTambahBerita">
          <i data-lucide="plus-circle" style="width:16px;height:16px"></i>
          Tulis Berita Baru
        </a>
      </div>
    </div>

    <!-- Filter Card -->
    <div class="admin-card" style="margin-bottom:1.5rem;">
      <div class="admin-card-body" style="padding:1.25rem 1.5rem;">
        <form method="GET" action="berita.php" style="display:grid;grid-template-columns:2fr 1.5fr 1fr auto;gap:1rem;align-items:end;">
          <div>
            <label class="form-label" style="font-size:var(--text-xs);margin-bottom:0.35rem;" for="searchBerita">Cari Judul Berita</label>
            <input type="text" id="searchBerita" name="q" class="form-input" style="padding:0.5rem 0.875rem;"
                   placeholder="Ketik judul berita…" value="<?= e($search) ?>">
          </div>
          <div>
            <label class="form-label" style="font-size:var(--text-xs);margin-bottom:0.35rem;" for="filterKategori">Kategori</label>
            <select id="filterKategori" name="kategori" class="form-select" style="padding:0.5rem 0.875rem;font-size:var(--text-sm);">
              <option value="">Semua Kategori</option>
              <?php foreach ($allCategories as $c): ?>
              <option value="<?= e($c) ?>" <?= $kategori === $c ? 'selected' : '' ?>><?= e($c) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="form-label" style="font-size:var(--text-xs);margin-bottom:0.35rem;" for="filterStatus">Status</label>
            <select id="filterStatus" name="status" class="form-select" style="padding:0.5rem 0.875rem;font-size:var(--text-sm);">
              <option value="">Semua Status</option>
              <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Dipublikasikan</option>
              <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Draf</option>
            </select>
          </div>
          <div style="display:flex;gap:0.5rem;">
            <button type="submit" class="btn btn-primary" style="padding:0.5rem 1rem;">
              <i data-lucide="search" style="width:14px;height:14px"></i> Filter
            </button>
            <?php if ($search !== '' || $kategori !== '' || $status !== ''): ?>
            <a href="berita.php" class="btn btn-ghost" title="Reset filter" style="padding:0.5rem 0.75rem;">
              <i data-lucide="rotate-ccw" style="width:14px;height:14px"></i>
            </a>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>

    <!-- Data Table Card -->
    <div class="admin-card">
      <div class="admin-card-header">
        <span style="font-weight:700;color:var(--gray-800);font-size:var(--text-sm);">
          Total: <?= number_format($total) ?> Berita
        </span>
        <span style="font-size:var(--text-xs);color:var(--gray-500);">
          Halaman <?= $pagination['current'] ?> dari <?= max(1, $pagination['total_pages']) ?>
        </span>
      </div>

      <div class="admin-table-wrap">
        <?php if ($beritaList): ?>
        <table class="admin-table">
          <thead>
            <tr>
              <th style="width:90px;">Gambar</th>
              <th>Judul &amp; Ringkasan Berita</th>
              <th style="width:130px;">Kategori</th>
              <th style="width:150px;">Tanggal Rilis</th>
              <th style="width:100px;">Status</th>
              <th style="width:170px;text-align:center;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($beritaList as $b): ?>
            <tr>
              <td>
                <img src="<?= beritaThumb($b['thumbnail']) ?>"
                     alt="<?= e($b['judul']) ?>"
                     style="width:72px;height:48px;border-radius:var(--radius-sm);object-fit:cover;border:1px solid var(--gray-200);background:var(--gray-100);"
                     onerror="this.src='<?= SITE_URL ?>/assets/img/news-placeholder.svg'">
              </td>
              <td>
                <div style="font-weight:700;color:var(--gray-900);line-height:1.4;margin-bottom:0.25rem;">
                  <?= e($b['judul']) ?>
                </div>
                <div style="font-size:var(--text-xs);color:var(--gray-400);line-height:1.4;max-width:400px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                  <?= e($b['ringkasan'] ?? excerpt($b['konten'], 120)) ?>
                </div>
              </td>
              <td>
                <span class="badge-pill" style="background:var(--accent-xs);color:var(--accent);font-weight:600;">
                  <?= e($b['kategori']) ?>
                </span>
              </td>
              <td>
                <div style="font-size:var(--text-sm);color:var(--gray-700);font-weight:500;">
                  <?= formatDate($b['published_at']) ?>
                </div>
                <div style="font-size:var(--text-xs);color:var(--gray-400);">
                  <?= timeAgo($b['published_at']) ?>
                </div>
              </td>
              <td>
                <?php if ($b['is_published']): ?>
                  <span class="badge-pill badge-published">
                    <i data-lucide="check" style="width:11px;height:11px"></i> Publik
                  </span>
                <?php else: ?>
                  <span class="badge-pill badge-draft">
                    <i data-lucide="eye-off" style="width:11px;height:11px"></i> Draf
                  </span>
                <?php endif; ?>
              </td>
              <td style="text-align:center;">
                <div style="display:inline-flex;gap:0.35rem;align-items:center;">
                  <!-- View Live -->
                  <a href="<?= SITE_URL ?>/berita-detail.php?slug=<?= e($b['slug']) ?>"
                     target="_blank"
                     class="admin-btn-action"
                     title="Lihat Berita Publik">
                    <i data-lucide="eye" style="width:14px;height:14px"></i>
                  </a>

                  <!-- Edit -->
                  <a href="<?= SITE_URL ?>/dashboard/berita-edit.php?id=<?= $b['id'] ?>"
                     class="admin-btn-action admin-btn-edit"
                     title="Ubah Berita">
                    <i data-lucide="edit-3" style="width:14px;height:14px"></i> Ubah
                  </a>

                  <!-- Delete -->
                  <form method="POST" action="berita.php" style="margin:0;"
                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus berita &quot;<?= e($b['judul']) ?>&quot;? Tindakan ini tidak dapat dibatalkan.');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <button type="submit" class="admin-btn-action admin-btn-danger" title="Hapus Berita">
                      <i data-lucide="trash-2" style="width:14px;height:14px"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?>
        <div style="text-align:center;padding:3.5rem 1rem;">
          <div style="width:48px;height:48px;background:var(--gray-100);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;color:var(--gray-400);margin-bottom:1rem;">
            <i data-lucide="newspaper" style="width:24px;height:24px"></i>
          </div>
          <h3 style="font-size:var(--text-base);font-weight:700;color:var(--gray-700);margin:0 0 0.5rem;">Tidak ada berita</h3>
          <p style="color:var(--gray-400);font-size:var(--text-sm);margin:0 0 1.5rem;">
            <?= ($search !== '' || $kategori !== '' || $status !== '') ? 'Tidak ada berita yang sesuai dengan filter.' : 'Belum ada berita yang diterbitkan.' ?>
          </p>
          <a href="<?= SITE_URL ?>/dashboard/berita-edit.php" class="btn btn-primary btn-sm">
            <i data-lucide="plus-circle" style="width:14px;height:14px"></i> Tulis Berita Sekarang
          </a>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Pagination -->
    <?= paginationHtml($pagination) ?>

  </div><!-- /.dashboard-main -->
</div><!-- /.dashboard-layout -->
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

