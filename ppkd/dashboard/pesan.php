<?php
// ============================================================
// Pesan Kontak Masuk (Admin) — dashboard/pesan.php
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db   = getDB();
$user = currentUser();
$csrf = csrfToken();

// Handle Actions (Mark read / unread / delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrf($token)) {
        setFlash('danger', 'Token CSRF tidak valid.');
        header('Location: pesan.php');
        exit;
    }

    $action = $_POST['action'] ?? '';
    $id     = (int) ($_POST['id'] ?? 0);

    if ($id > 0) {
        if ($action === 'read') {
            $db->prepare('UPDATE kontak_pesan SET is_read = 1 WHERE id = ?')->execute([$id]);
            setFlash('success', 'Pesan ditandai sebagai sudah dibaca.');
        } elseif ($action === 'unread') {
            $db->prepare('UPDATE kontak_pesan SET is_read = 0 WHERE id = ?')->execute([$id]);
            setFlash('success', 'Pesan ditandai sebagai belum dibaca.');
        } elseif ($action === 'delete') {
            $db->prepare('DELETE FROM kontak_pesan WHERE id = ?')->execute([$id]);
            setFlash('success', 'Pesan berhasil dihapus.');
        }
    }
    header('Location: pesan.php');
    exit;
}

// Filter & Pagination
$filter = trim($_GET['status'] ?? '');
$page   = max(1, (int) ($_GET['page'] ?? 1));
$perPage= 15;
$offset = ($page - 1) * $perPage;

$where  = [];
$params = [];
if ($filter === 'unread') {
    $where[] = 'is_read = 0';
} elseif ($filter === 'read') {
    $where[] = 'is_read = 1';
}
$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$cntStmt = $db->query("SELECT COUNT(*) FROM kontak_pesan $whereClause");
$total   = (int) $cntStmt->fetchColumn();

$stmt = $db->prepare("SELECT * FROM kontak_pesan $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->execute(array_merge($params, [$perPage, $offset]));
$pesanList = $stmt->fetchAll();

$pagination = paginate($total, $perPage, $page, '?status=' . urlencode($filter));

$pageTitle = 'Pesan Kontak Masuk — ' . SITE_NAME;
$bodyClass = 'dashboard-page';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="padding-top:70px;">
<div class="dashboard-layout">

  <!-- Sidebar -->
  <?php $activePage = 'pesan'; require __DIR__ . '/sidebar.php'; ?>

  <!-- Main Content -->
  <div class="dashboard-main">

    <!-- Page Header -->
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:2rem;">
      <div>
        <h1 style="font-size:var(--text-2xl);font-weight:800;color:var(--gray-900);margin:0 0 0.25rem;">
          Pesan Masuk dari Pengunjung
        </h1>
        <p style="color:var(--gray-500);font-size:var(--text-sm);margin:0;">
          Daftar pesan, pertanyaan, dan saran yang dikirim pengunjung melalui halaman Hubungi Kami.
        </p>
      </div>

      <!-- Quick Filter Pills -->
      <div style="display:inline-flex;gap:0.5rem;">
        <a href="pesan.php" class="btn btn-sm <?= $filter === '' ? 'btn-primary' : 'btn-ghost' ?>" style="border:1.5px solid var(--gray-200);">
          Semua
        </a>
        <a href="pesan.php?status=unread" class="btn btn-sm <?= $filter === 'unread' ? 'btn-primary' : 'btn-ghost' ?>" style="border:1.5px solid var(--gray-200);">
          Belum Dibaca
        </a>
        <a href="pesan.php?status=read" class="btn btn-sm <?= $filter === 'read' ? 'btn-primary' : 'btn-ghost' ?>" style="border:1.5px solid var(--gray-200);">
          Sudah Dibaca
        </a>
      </div>
    </div>

    <!-- Data Table Card -->
    <div class="admin-card">
      <div class="admin-card-header">
        <span style="font-weight:700;color:var(--gray-800);font-size:var(--text-sm);">
          Total: <?= number_format($total) ?> Pesan
        </span>
        <span style="font-size:var(--text-xs);color:var(--gray-500);">
          Halaman <?= $pagination['current'] ?> dari <?= max(1, $pagination['total_pages']) ?>
        </span>
      </div>

      <div class="admin-table-wrap">
        <?php if ($pesanList): ?>
        <table class="admin-table">
          <thead>
            <tr>
              <th style="width:50px;">Status</th>
              <th style="width:200px;">Pengirim</th>
              <th>Subjek &amp; Pesan</th>
              <th style="width:150px;">Waktu Kirim</th>
              <th style="width:140px;text-align:center;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pesanList as $p): ?>
            <tr style="<?= $p['is_read'] ? '' : 'background-color:#eff6ff;font-weight:600;' ?>">
              <td>
                <?php if ($p['is_read']): ?>
                  <span title="Sudah dibaca" style="color:var(--gray-400);"><i data-lucide="mail-open" style="width:16px;height:16px"></i></span>
                <?php else: ?>
                  <span title="Pesan Baru" style="color:var(--accent);"><i data-lucide="mail" style="width:16px;height:16px"></i></span>
                <?php endif; ?>
              </td>
              <td>
                <div style="font-weight:700;color:var(--gray-900);"><?= e($p['nama']) ?></div>
                <a href="mailto:<?= e($p['email']) ?>" style="font-size:var(--text-xs);color:var(--accent);text-decoration:none;">
                  <?= e($p['email']) ?>
                </a>
              </td>
              <td>
                <div style="color:var(--gray-900);font-weight:700;margin-bottom:0.25rem;">
                  <?= e($p['subjek']) ?>
                </div>
                <div style="font-size:var(--text-xs);color:var(--gray-600);white-space:pre-line;line-height:1.5;">
                  <?= e($p['pesan']) ?>
                </div>
              </td>
              <td>
                <div style="font-size:var(--text-xs);color:var(--gray-700);">
                  <?= formatDate($p['created_at'], 'd M Y, H:i') ?>
                </div>
                <div style="font-size:var(--text-xs);color:var(--gray-400);">
                  <?= timeAgo($p['created_at']) ?>
                </div>
              </td>
              <td style="text-align:center;">
                <div style="display:inline-flex;gap:0.35rem;align-items:center;">
                  <!-- Toggle Read/Unread -->
                  <form method="POST" action="pesan.php" style="margin:0;">
                    <input type="hidden" name="action" value="<?= $p['is_read'] ? 'unread' : 'read' ?>">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <button type="submit" class="admin-btn-action" title="<?= $p['is_read'] ? 'Tandai Belum Dibaca' : 'Tandai Sudah Dibaca' ?>">
                      <i data-lucide="<?= $p['is_read'] ? 'eye-off' : 'check' ?>" style="width:13px;height:13px"></i>
                    </button>
                  </form>

                  <!-- Reply via email client -->
                  <a href="mailto:<?= e($p['email']) ?>?subject=<?= urlencode('Re: ' . $p['subjek']) ?>"
                     class="admin-btn-action" title="Balas via Email">
                    <i data-lucide="send" style="width:13px;height:13px"></i>
                  </a>

                  <!-- Delete -->
                  <form method="POST" action="pesan.php" style="margin:0;"
                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus pesan ini?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <button type="submit" class="admin-btn-action admin-btn-danger" title="Hapus Pesan">
                      <i data-lucide="trash-2" style="width:13px;height:13px"></i>
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
            <i data-lucide="inbox" style="width:24px;height:24px"></i>
          </div>
          <h3 style="font-size:var(--text-base);font-weight:700;color:var(--gray-700);margin:0 0 0.5rem;">Tidak ada pesan</h3>
          <p style="color:var(--gray-400);font-size:var(--text-sm);margin:0;">
            Belum ada pesan yang masuk dari formulir kontak.
          </p>
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

