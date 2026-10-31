<?php
// ============================================================
// Kelola Alumni (Admin) — dashboard/alumni.php
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db    = getDB();
$user  = currentUser();
$csrf  = csrfToken();

// Handle Delete Alumni
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrf($token)) {
        setFlash('danger', 'Token CSRF tidak valid.');
        header('Location: alumni.php');
        exit;
    }

    $deleteId = (int) ($_POST['id'] ?? 0);
    if ($deleteId > 0) {
        $findStmt = $db->prepare('SELECT foto, nama_lengkap FROM alumni WHERE id = ?');
        $findStmt->execute([$deleteId]);
        $target = $findStmt->fetch();

        if ($target) {
            if (!empty($target['foto'])) {
                $filePath = UPLOAD_PATH . 'alumni/' . $target['foto'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
            $delStmt = $db->prepare('DELETE FROM alumni WHERE id = ?');
            $delStmt->execute([$deleteId]);

            setFlash('success', 'Data alumni "' . $target['nama_lengkap'] . '" berhasil dihapus.');
        } else {
            setFlash('danger', 'Data alumni tidak ditemukan.');
        }
    }
    header('Location: alumni.php');
    exit;
}

// Filters & Pagination
$search  = trim($_GET['q'] ?? '');
$jurusan = (int) ($_GET['jurusan'] ?? 0);
$status  = trim($_GET['status'] ?? '');
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 15;
$offset  = ($page - 1) * $perPage;

$where   = [];
$params  = [];

if ($search !== '') {
    if (is_numeric($search)) {
        $where[]  = '(a.nama_lengkap LIKE ? OR a.nomor_urut = ?)';
        $params[] = '%' . $search . '%';
        $params[] = (int) $search;
    } else {
        $where[]  = 'a.nama_lengkap LIKE ?';
        $params[] = '%' . $search . '%';
    }
}

if ($jurusan > 0) {
    $where[]  = 'a.jurusan_id = ?';
    $params[] = $jurusan;
}

if ($status === '1') {
    $where[] = 'a.is_published = 1';
} elseif ($status === '0') {
    $where[] = 'a.is_published = 0';
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Count Total
$cntSql = "SELECT COUNT(*) FROM alumni a $whereClause";
$cntStmt = $db->prepare($cntSql);
$cntStmt->execute($params);
$total = (int) $cntStmt->fetchColumn();

// Fetch Data
$sql = "SELECT a.*, j.nama_jurusan, j.kode
        FROM alumni a
        JOIN jurusan j ON a.jurusan_id = j.id
        $whereClause
        ORDER BY a.nomor_urut ASC
        LIMIT ? OFFSET ?";
$stmt = $db->prepare($sql);
$stmt->execute(array_merge($params, [$perPage, $offset]));
$alumniList = $stmt->fetchAll();

$queryParam = http_build_query([
    'q'       => $search,
    'jurusan' => $jurusan ?: '',
    'status'  => $status,
]);
$pagination = paginate($total, $perPage, $page, '?' . $queryParam);

$allJurusan = getAllJurusan();

$pageTitle = 'Kelola Alumni — ' . SITE_NAME;
$bodyClass = 'dashboard-page';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="padding-top:70px;">
<div class="dashboard-layout">

  <!-- Sidebar -->
  <?php $activePage = 'alumni'; require __DIR__ . '/sidebar.php'; ?>

  <!-- Main Content -->
  <div class="dashboard-main">
    
    <!-- Page Header & Action -->
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:2rem;">
      <div>
        <h1 style="font-size:var(--text-2xl);font-weight:800;color:var(--gray-900);margin:0 0 0.25rem;">
          Kelola Data Alumni
        </h1>
        <p style="color:var(--gray-500);font-size:var(--text-sm);margin:0;">
          Daftar seluruh alumni. Anda dapat menambah, mengubah nama, informasi, dan foto alumni.
        </p>
      </div>
      <div>
        <a href="<?= SITE_URL ?>/dashboard/alumni-edit.php" class="btn btn-primary" id="btnTambahAlumni">
          <i data-lucide="user-plus" style="width:16px;height:16px"></i>
          Tambah Alumni Baru
        </a>
      </div>
    </div>

    <!-- Filter Card -->
    <div class="admin-card" style="margin-bottom:1.5rem;">
      <div class="admin-card-body" style="padding:1.25rem 1.5rem;">
        <form method="GET" action="alumni.php" style="display:grid;grid-template-columns:2fr 1.5fr 1fr auto;gap:1rem;align-items:end;">
          <div>
            <label class="form-label" style="font-size:var(--text-xs);margin-bottom:0.35rem;" for="searchAlumni">Cari Nama / No. Urut</label>
            <input type="text" id="searchAlumni" name="q" class="form-input" style="padding:0.5rem 0.875rem;"
                   placeholder="Contoh: Ahmad atau 15…" value="<?= e($search) ?>">
          </div>
          <div>
            <label class="form-label" style="font-size:var(--text-xs);margin-bottom:0.35rem;" for="filterJurusan">Program Keahlian</label>
            <select id="filterJurusan" name="jurusan" class="form-select" style="padding:0.5rem 0.875rem;font-size:var(--text-sm);">
              <option value="">Semua Jurusan</option>
              <?php foreach ($allJurusan as $j): ?>
              <option value="<?= $j['id'] ?>" <?= $jurusan === (int)$j['id'] ? 'selected' : '' ?>><?= e($j['nama_jurusan']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="form-label" style="font-size:var(--text-xs);margin-bottom:0.35rem;" for="filterStatus">Status</label>
            <select id="filterStatus" name="status" class="form-select" style="padding:0.5rem 0.875rem;font-size:var(--text-sm);">
              <option value="">Semua Status</option>
              <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Dipublikasikan</option>
              <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Draf (Disembunyikan)</option>
            </select>
          </div>
          <div style="display:flex;gap:0.5rem;">
            <button type="submit" class="btn btn-primary" style="padding:0.5rem 1rem;">
              <i data-lucide="search" style="width:14px;height:14px"></i> Cari
            </button>
            <?php if ($search !== '' || $jurusan > 0 || $status !== ''): ?>
            <a href="alumni.php" class="btn btn-ghost" title="Reset filter" style="padding:0.5rem 0.75rem;">
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
          Total: <?= number_format($total) ?> Alumni
        </span>
        <span style="font-size:var(--text-xs);color:var(--gray-500);">
          Halaman <?= $pagination['current'] ?> dari <?= max(1, $pagination['total_pages']) ?>
        </span>
      </div>

      <div class="admin-table-wrap">
        <?php if ($alumniList): ?>
        <table class="admin-table">
          <thead>
            <tr>
              <th style="width:70px;">Urut</th>
              <th>Data Alumni</th>
              <th>Jurusan</th>
              <th>Angkatan</th>
              <th>Pekerjaan &amp; Kota</th>
              <th>Status</th>
              <th style="width:170px;text-align:center;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($alumniList as $a): ?>
            <tr>
              <td>
                <span style="font-weight:700;color:var(--gray-500);font-size:var(--text-sm);">
                  #<?= e($a['nomor_urut']) ?>
                </span>
              </td>
              <td>
                <div style="display:flex;align-items:center;gap:0.875rem;">
                  <img src="<?= alumniPhoto($a['foto'], $a['nama_lengkap']) ?>"
                       alt="<?= e($a['nama_lengkap']) ?>"
                       style="width:42px;height:42px;border-radius:50%;object-fit:cover;border:1.5px solid var(--gray-200);flex-shrink:0;">
                  <div>
                    <div style="font-weight:700;color:var(--gray-900);line-height:1.3;">
                      <?= e($a['nama_lengkap']) ?>
                    </div>
                    <?php if ($a['deskripsi']): ?>
                    <div style="font-size:var(--text-xs);color:var(--gray-400);max-width:260px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                      <?= e($a['deskripsi']) ?>
                    </div>
                    <?php endif; ?>
                  </div>
                </div>
              </td>
              <td>
                <span style="font-weight:600;color:var(--gray-800);"><?= e($a['nama_jurusan']) ?></span>
                <span style="font-size:var(--text-xs);color:var(--gray-400);display:block;"><?= e($a['kode']) ?></span>
              </td>
              <td>
                <span style="font-weight:600;color:var(--gray-800);"><?= e($a['tahun_lulus']) ?></span>
              </td>
              <td>
                <div style="font-size:var(--text-sm);color:var(--gray-700);">
                  <?= e($a['pekerjaan'] ?: '-') ?>
                </div>
                <div style="font-size:var(--text-xs);color:var(--gray-400);">
                  <?= e($a['kota'] ?: '-') ?>
                </div>
              </td>
              <td>
                <?php if ($a['is_published']): ?>
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
                  <a href="<?= SITE_URL ?>/alumni-detail.php?id=<?= $a['id'] ?>"
                     target="_blank"
                     class="admin-btn-action"
                     title="Lihat Halaman Publik">
                    <i data-lucide="eye" style="width:14px;height:14px"></i>
                  </a>

                  <!-- Edit -->
                  <a href="<?= SITE_URL ?>/dashboard/alumni-edit.php?id=<?= $a['id'] ?>"
                     class="admin-btn-action admin-btn-edit"
                     title="Ubah Data Alumni">
                    <i data-lucide="edit-3" style="width:14px;height:14px"></i> Ubah
                  </a>

                  <!-- Delete -->
                  <form method="POST" action="alumni.php" style="margin:0;"
                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus data alumni &quot;<?= e($a['nama_lengkap']) ?>&quot;? Tindakan ini tidak dapat dibatalkan.');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <button type="submit" class="admin-btn-action admin-btn-danger" title="Hapus Data">
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
            <i data-lucide="users" style="width:24px;height:24px"></i>
          </div>
          <h3 style="font-size:var(--text-base);font-weight:700;color:var(--gray-700);margin:0 0 0.5rem;">Tidak ada data alumni</h3>
          <p style="color:var(--gray-400);font-size:var(--text-sm);margin:0 0 1.5rem;">
            <?= ($search !== '' || $jurusan > 0 || $status !== '') ? 'Tidak ada alumni yang sesuai dengan filter pencarian.' : 'Belum ada data alumni yang tersimpan.' ?>
          </p>
          <a href="<?= SITE_URL ?>/dashboard/alumni-edit.php" class="btn btn-primary btn-sm">
            <i data-lucide="user-plus" style="width:14px;height:14px"></i> Tambah Alumni Sekarang
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

