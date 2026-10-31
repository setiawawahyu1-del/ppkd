<?php
// ============================================================
// Dashboard — dashboard/index.php
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();
$user = currentUser();
$db   = getDB();

$pageTitle = 'Dashboard — ' . SITE_NAME;
$bodyClass = 'dashboard-page';

// Fetch alumni data if linked and role is alumni
$alumniData = null;
if ($user['role'] === 'alumni') {
    $stmt = $db->prepare('SELECT a.*, j.nama_jurusan FROM alumni a JOIN jurusan j ON a.jurusan_id = j.id WHERE a.user_id = ?');
    $stmt->execute([$user['id']]);
    $alumniData = $stmt->fetch();
}

// Fetch Admin Specific Data
$adminStats = null;
$latestAlumni = [];
$latestBerita = [];
if (isAdmin()) {
    $adminStats = getAdminStats();

    // Latest 5 Alumni
    $alumniStmt = $db->query(
        'SELECT a.*, j.nama_jurusan 
         FROM alumni a 
         JOIN jurusan j ON a.jurusan_id = j.id 
         ORDER BY a.created_at DESC, a.id DESC 
         LIMIT 5'
    );
    $latestAlumni = $alumniStmt->fetchAll();

    // Latest 5 Berita
    $beritaStmt = $db->query(
        'SELECT * FROM berita 
         ORDER BY published_at DESC, id DESC 
         LIMIT 5'
    );
    $latestBerita = $beritaStmt->fetchAll();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div style="padding-top:70px;">
<div class="dashboard-layout">

  <!-- Sidebar -->
  <?php $activePage = 'dashboard'; require __DIR__ . '/sidebar.php'; ?>

  <!-- Main -->
  <div class="dashboard-main">

    <!-- Header -->
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:2rem;">
      <div>
        <h1 style="font-size:var(--text-2xl);font-weight:800;margin-bottom:0.25rem;color:var(--gray-900);">
          Selamat datang, <?= e(explode(' ', $user['nama'])[0]) ?>! 👋
        </h1>
        <p style="color:var(--gray-500);font-size:var(--text-sm);margin:0;">
          <?= date('l, d F Y') ?> · Portal Manajemen Ikatan Alumni PPKD
        </p>
      </div>

      <?php if (isAdmin()): ?>
      <div style="display:inline-flex;gap:0.75rem;">
        <a href="<?= SITE_URL ?>/dashboard/alumni-edit.php" class="btn btn-outline" style="border-color:var(--accent);color:var(--accent);">
          <i data-lucide="user-plus" style="width:15px;height:15px"></i>
          + Tambah Alumni
        </a>
        <a href="<?= SITE_URL ?>/dashboard/berita-edit.php" class="btn btn-primary">
          <i data-lucide="plus-circle" style="width:15px;height:15px"></i>
          + Tulis Berita
        </a>
      </div>
      <?php endif; ?>
    </div>

    <?php if (isAdmin()): ?>
    <!-- ===================== ADMIN VIEW ===================== -->

    <!-- Stats Cards -->
    <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:1.25rem;margin-bottom:2.5rem;">
      
      <!-- Total Alumni -->
      <a href="<?= SITE_URL ?>/dashboard/alumni.php" style="background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius-lg);padding:1.5rem;display:flex;align-items:center;gap:1.25rem;text-decoration:none;transition:transform var(--transition), box-shadow var(--transition);"
         onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='var(--shadow-md)'"
         onmouseout="this.style.transform='none';this.style.boxShadow='none'">
        <div style="width:48px;height:48px;background:#dbeafe;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#1d4ed8;">
          <i data-lucide="users" style="width:22px;height:22px"></i>
        </div>
        <div>
          <div style="font-size:var(--text-2xl);font-weight:800;color:var(--gray-900);line-height:1;"><?= number_format($adminStats['total_alumni']) ?></div>
          <div style="font-size:var(--text-xs);color:var(--gray-500);margin-top:0.25rem;font-weight:600;">Total Alumni</div>
        </div>
      </a>

      <!-- Total Berita -->
      <a href="<?= SITE_URL ?>/dashboard/berita.php" style="background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius-lg);padding:1.5rem;display:flex;align-items:center;gap:1.25rem;text-decoration:none;transition:transform var(--transition), box-shadow var(--transition);"
         onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='var(--shadow-md)'"
         onmouseout="this.style.transform='none';this.style.boxShadow='none'">
        <div style="width:48px;height:48px;background:#fef3c7;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#d97706;">
          <i data-lucide="newspaper" style="width:22px;height:22px"></i>
        </div>
        <div>
          <div style="font-size:var(--text-2xl);font-weight:800;color:var(--gray-900);line-height:1;"><?= number_format($adminStats['total_berita']) ?></div>
          <div style="font-size:var(--text-xs);color:var(--gray-500);margin-top:0.25rem;font-weight:600;">Berita &amp; Artikel</div>
        </div>
      </a>

      <!-- Program Keahlian -->
      <div style="background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius-lg);padding:1.5rem;display:flex;align-items:center;gap:1.25rem;">
        <div style="width:48px;height:48px;background:#d1fae5;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#059669;">
          <i data-lucide="book-open" style="width:22px;height:22px"></i>
        </div>
        <div>
          <div style="font-size:var(--text-2xl);font-weight:800;color:var(--gray-900);line-height:1;"><?= number_format($adminStats['total_jurusan']) ?></div>
          <div style="font-size:var(--text-xs);color:var(--gray-500);margin-top:0.25rem;font-weight:600;">Program Keahlian</div>
        </div>
      </div>

      <!-- Pesan Masuk -->
      <a href="<?= SITE_URL ?>/dashboard/pesan.php" style="background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius-lg);padding:1.5rem;display:flex;align-items:center;gap:1.25rem;text-decoration:none;transition:transform var(--transition), box-shadow var(--transition);"
         onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='var(--shadow-md)'"
         onmouseout="this.style.transform='none';this.style.boxShadow='none'">
        <div style="width:48px;height:48px;background:<?= $adminStats['unread_pesan'] > 0 ? '#fee2e2' : '#f1f5f9' ?>;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:<?= $adminStats['unread_pesan'] > 0 ? '#dc2626' : '#64748b' ?>;">
          <i data-lucide="mail" style="width:22px;height:22px"></i>
        </div>
        <div>
          <div style="font-size:var(--text-2xl);font-weight:800;color:var(--gray-900);line-height:1;">
            <?= number_format($adminStats['unread_pesan']) ?>
            <?php if ($adminStats['unread_pesan'] > 0): ?>
              <span style="font-size:var(--text-xs);color:var(--danger);font-weight:700;">Baru</span>
            <?php endif; ?>
          </div>
          <div style="font-size:var(--text-xs);color:var(--gray-500);margin-top:0.25rem;font-weight:600;">Pesan Kontak</div>
        </div>
      </a>

    </div>

    <!-- Quick Management Sections Grid -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;margin-bottom:2.5rem;">

      <!-- Recent Alumni with Quick Edit -->
      <div class="admin-card" style="margin-bottom:0;">
        <div class="admin-card-header">
          <div style="display:flex;align-items:center;gap:0.5rem;">
            <i data-lucide="users" style="width:18px;height:18px;color:var(--accent);"></i>
            <h2 style="font-size:var(--text-base);font-weight:700;margin:0;color:var(--gray-900);">Alumni Terbaru</h2>
          </div>
          <a href="<?= SITE_URL ?>/dashboard/alumni.php" style="font-size:var(--text-xs);font-weight:700;color:var(--accent);text-decoration:none;">
            Kelola Semua Alumni &rarr;
          </a>
        </div>
        <div class="admin-table-wrap">
          <?php if ($latestAlumni): ?>
          <table class="admin-table">
            <thead>
              <tr>
                <th>Nama Alumni</th>
                <th>Jurusan</th>
                <th style="width:110px;text-align:center;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($latestAlumni as $a): ?>
              <tr>
                <td>
                  <div style="display:flex;align-items:center;gap:0.75rem;">
                    <img src="<?= alumniPhoto($a['foto'], $a['nama_lengkap']) ?>"
                         alt="<?= e($a['nama_lengkap']) ?>"
                         style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:1px solid var(--gray-200);flex-shrink:0;">
                    <div>
                      <div style="font-weight:700;color:var(--gray-900);font-size:var(--text-sm);line-height:1.3;">
                        <?= e($a['nama_lengkap']) ?>
                      </div>
                      <div style="font-size:var(--text-xs);color:var(--gray-400);">
                        #<?= e($a['nomor_urut']) ?> · Lulus <?= e($a['tahun_lulus']) ?>
                      </div>
                    </div>
                  </div>
                </td>
                <td>
                  <span style="font-size:var(--text-xs);font-weight:600;color:var(--gray-700);"><?= e($a['nama_jurusan']) ?></span>
                </td>
                <td style="text-align:center;">
                  <a href="<?= SITE_URL ?>/dashboard/alumni-edit.php?id=<?= $a['id'] ?>"
                     class="admin-btn-action admin-btn-edit" title="Ubah Nama &amp; Data Alumni">
                    <i data-lucide="edit-3" style="width:13px;height:13px"></i> Ubah
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php else: ?>
          <p style="text-align:center;padding:2rem;color:var(--gray-400);margin:0;">Belum ada data alumni.</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Recent Berita with Quick Edit -->
      <div class="admin-card" style="margin-bottom:0;">
        <div class="admin-card-header">
          <div style="display:flex;align-items:center;gap:0.5rem;">
            <i data-lucide="newspaper" style="width:18px;height:18px;color:var(--warning);"></i>
            <h2 style="font-size:var(--text-base);font-weight:700;margin:0;color:var(--gray-900);">Berita Terbaru</h2>
          </div>
          <a href="<?= SITE_URL ?>/dashboard/berita.php" style="font-size:var(--text-xs);font-weight:700;color:var(--accent);text-decoration:none;">
            Kelola Semua Berita &rarr;
          </a>
        </div>
        <div class="admin-table-wrap">
          <?php if ($latestBerita): ?>
          <table class="admin-table">
            <thead>
              <tr>
                <th>Judul Berita</th>
                <th>Kategori</th>
                <th style="width:110px;text-align:center;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($latestBerita as $b): ?>
              <tr>
                <td>
                  <div style="font-weight:700;color:var(--gray-900);font-size:var(--text-sm);line-height:1.3;max-width:240px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    <?= e($b['judul']) ?>
                  </div>
                  <div style="font-size:var(--text-xs);color:var(--gray-400);margin-top:0.2rem;">
                    <?= formatDate($b['published_at']) ?>
                  </div>
                </td>
                <td>
                  <span class="badge-pill" style="background:var(--accent-xs);color:var(--accent);font-size:0.7rem;">
                    <?= e($b['kategori']) ?>
                  </span>
                </td>
                <td style="text-align:center;">
                  <a href="<?= SITE_URL ?>/dashboard/berita-edit.php?id=<?= $b['id'] ?>"
                     class="admin-btn-action admin-btn-edit" title="Ubah Konten &amp; Judul Berita">
                    <i data-lucide="edit-3" style="width:13px;height:13px"></i> Ubah
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php else: ?>
          <p style="text-align:center;padding:2rem;color:var(--gray-400);margin:0;">Belum ada berita.</p>
          <?php endif; ?>
        </div>
      </div>

    </div>

    <?php else: ?>
    <!-- ===================== ALUMNI VIEW ===================== -->

    <!-- Stats Cards -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem;margin-bottom:2.5rem;">
      <?php
      $stats = getSiteStats();
      $cards = [
        ['icon'=>'users',     'label'=>'Total Alumni',       'value'=>$stats['alumni'],  'color'=>'#1d4ed8', 'bg'=>'#dbeafe'],
        ['icon'=>'book-open', 'label'=>'Program Keahlian',   'value'=>$stats['jurusan'], 'color'=>'#059669', 'bg'=>'#d1fae5'],
        ['icon'=>'newspaper', 'label'=>'Berita Tersedia',    'value'=>$stats['berita'],  'color'=>'#d97706', 'bg'=>'#fef3c7'],
      ];
      foreach ($cards as $c): ?>
      <div style="background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius-lg);padding:1.5rem;display:flex;align-items:center;gap:1.25rem;">
        <div style="width:48px;height:48px;background:<?= $c['bg'] ?>;border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:<?= $c['color'] ?>;">
          <i data-lucide="<?= $c['icon'] ?>" style="width:22px;height:22px"></i>
        </div>
        <div>
          <div style="font-size:var(--text-2xl);font-weight:800;color:var(--gray-900);line-height:1;"><?= number_format($c['value']) ?></div>
          <div style="font-size:var(--text-xs);color:var(--gray-500);margin-top:0.25rem;font-weight:500;"><?= $c['label'] ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Alumni Profile Status -->
    <div style="background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius-lg);padding:2rem;margin-bottom:2rem;">
      <h2 style="font-size:var(--text-lg);font-weight:700;margin-bottom:1.25rem;">Status Profil Alumni</h2>

      <?php if ($alumniData): ?>
      <div style="display:flex;align-items:center;gap:1.25rem;padding:1.25rem;background:var(--gray-50);border-radius:var(--radius-md);">
        <img src="<?= alumniPhoto($alumniData['foto'], $alumniData['nama_lengkap']) ?>"
             alt="<?= e($alumniData['nama_lengkap']) ?>"
             style="width:56px;height:56px;border-radius:50%;object-fit:cover;border:2px solid var(--white);box-shadow:var(--shadow);">
        <div style="flex:1;">
          <div style="font-weight:700;color:var(--gray-900);"><?= e($alumniData['nama_lengkap']) ?></div>
          <div style="font-size:var(--text-sm);color:var(--gray-500);"><?= e($alumniData['nama_jurusan']) ?> · Lulus <?= e($alumniData['tahun_lulus']) ?></div>
        </div>
        <a href="<?= SITE_URL ?>/alumni-detail.php?id=<?= $alumniData['id'] ?>"
           class="btn btn-outline btn-sm" id="btnViewProfile">Lihat Profil</a>
      </div>
      <?php else: ?>
      <div style="text-align:center;padding:2rem;border:2px dashed var(--gray-200);border-radius:var(--radius-md);">
        <i data-lucide="user-x" style="width:32px;height:32px;color:var(--gray-300);margin-bottom:0.75rem;"></i>
        <p style="font-size:var(--text-sm);color:var(--gray-500);margin:0 0 1rem;">
          Profil alumni Anda belum terhubung.<br>
          Hubungi admin untuk menghubungkan akun dengan data alumni.
        </p>
        <a href="<?= SITE_URL ?>/kontak.php" class="btn btn-primary btn-sm" id="btnKontakAdmin">Hubungi Admin</a>
      </div>
      <?php endif; ?>
    </div>

    <!-- Quick Links -->
    <div style="background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius-lg);padding:2rem;">
      <h2 style="font-size:var(--text-lg);font-weight:700;margin-bottom:1.25rem;">Menu Cepat</h2>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;">
        <?php
        $links = [
          ['href'=>SITE_URL.'/alumni.php',  'icon'=>'users',    'label'=>'Direktori Alumni', 'desc'=>'Cari sesama alumni'],
          ['href'=>SITE_URL.'/berita.php',  'icon'=>'newspaper','label'=>'Baca Berita',       'desc'=>'Informasi terbaru'],
          ['href'=>SITE_URL.'/kontak.php',  'icon'=>'mail',     'label'=>'Hubungi Kami',      'desc'=>'Kirim pesan'],
        ];
        foreach ($links as $l): ?>
        <a href="<?= $l['href'] ?>" style="display:flex;align-items:center;gap:0.875rem;padding:1.25rem;background:var(--gray-50);border:1px solid var(--gray-200);border-radius:var(--radius-md);color:inherit;transition:all var(--transition);text-decoration:none;" 
           onmouseover="this.style.borderColor='var(--accent-l)';this.style.background='var(--accent-xs)'"
           onmouseout="this.style.borderColor='var(--gray-200)';this.style.background='var(--gray-50)'">
          <i data-lucide="<?= $l['icon'] ?>" style="width:20px;height:20px;color:var(--accent);flex-shrink:0;"></i>
          <div>
            <div style="font-size:var(--text-sm);font-weight:600;color:var(--gray-800);"><?= $l['label'] ?></div>
            <div style="font-size:var(--text-xs);color:var(--gray-400);"><?= $l['desc'] ?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <?php endif; ?>

  </div><!-- /.dashboard-main -->
</div><!-- /.dashboard-layout -->
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
