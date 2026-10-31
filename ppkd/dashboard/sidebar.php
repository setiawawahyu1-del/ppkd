<?php
// ============================================================
// Dashboard Sidebar — dashboard/sidebar.php
// ============================================================
$activePage = $activePage ?? '';
$user       = currentUser();
$db         = getDB();

$unreadPesan = 0;
if (isAdmin()) {
    try {
        $unreadPesan = (int) $db->query('SELECT COUNT(*) FROM kontak_pesan WHERE is_read=0')->fetchColumn();
    } catch (\Throwable $e) {
        $unreadPesan = 0;
    }
}
?>
<aside class="sidebar" id="dashboardSidebar">
  <div style="margin-bottom:1.5rem;padding:0 0.5rem;">
    <div style="display:flex;align-items:center;gap:0.875rem;margin-bottom:1.5rem;">
      <img src="<?= $user['foto'] ? profilPhoto($user['foto']) : SITE_URL . '/assets/img/avatar-placeholder.svg' ?>"
           alt="<?= e($user['nama']) ?>"
           style="width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid var(--gray-200);flex-shrink:0;">
      <div style="overflow:hidden;">
        <div style="font-size:var(--text-sm);font-weight:700;color:var(--gray-900);line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?= e($user['nama']) ?>">
          <?= e($user['nama']) ?>
        </div>
        <div style="margin-top:0.25rem;">
          <?php if (isAdmin()): ?>
            <span class="badge-pill badge-role-admin" style="font-size:0.65rem;padding:0.15rem 0.5rem;">
              <i data-lucide="shield-check" style="width:11px;height:11px;"></i> ADMIN
            </span>
          <?php else: ?>
            <span style="font-size:var(--text-xs);color:var(--gray-500);text-transform:capitalize;font-weight:600;">
              <?= e($user['role']) ?>
            </span>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div style="height:1px;background:var(--gray-100);margin-bottom:1.25rem;"></div>
  </div>

  <ul class="sidebar-nav">
    <li>
      <a href="<?= SITE_URL ?>/dashboard/" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>" id="sidebarDashboard">
        <i data-lucide="layout-dashboard" style="width:16px;height:16px"></i> Dashboard
      </a>
    </li>

    <?php if (isAdmin()): ?>
    <!-- Admin Management Section -->
    <li style="padding:0.875rem 0.5rem 0.25rem;font-size:var(--text-xs);font-weight:700;text-transform:uppercase;letter-spacing:0.07em;color:var(--accent);">
      Kelola Konten
    </li>
    <li>
      <a href="<?= SITE_URL ?>/dashboard/alumni.php" class="<?= $activePage === 'alumni' ? 'active' : '' ?>" id="sidebarAdminAlumni">
        <i data-lucide="users" style="width:16px;height:16px"></i> Kelola Alumni
      </a>
    </li>
    <li>
      <a href="<?= SITE_URL ?>/dashboard/berita.php" class="<?= $activePage === 'berita' ? 'active' : '' ?>" id="sidebarAdminBerita">
        <i data-lucide="newspaper" style="width:16px;height:16px"></i> Kelola Berita
      </a>
    </li>
    <li>
      <a href="<?= SITE_URL ?>/dashboard/pesan.php" class="<?= $activePage === 'pesan' ? 'active' : '' ?>" id="sidebarAdminPesan" style="justify-content:space-between;">
        <span style="display:inline-flex;align-items:center;gap:0.75rem;">
          <i data-lucide="mail" style="width:16px;height:16px"></i> Pesan Masuk
        </span>
        <?php if ($unreadPesan > 0): ?>
          <span style="background:var(--danger);color:#fff;font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px;">
            <?= $unreadPesan ?>
          </span>
        <?php endif; ?>
      </a>
    </li>
    <?php endif; ?>

    <li style="padding:0.875rem 0.5rem 0.25rem;font-size:var(--text-xs);font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:var(--gray-400);">
      Akun
    </li>
    <li>
      <a href="<?= SITE_URL ?>/dashboard/profil.php" class="<?= $activePage === 'profil' ? 'active' : '' ?>" id="sidebarProfil">
        <i data-lucide="user" style="width:16px;height:16px"></i> Profil Saya
      </a>
    </li>

    <li style="padding:0.875rem 0.5rem 0.25rem;font-size:var(--text-xs);font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:var(--gray-400);">
      Website Publik
    </li>
    <li>
      <a href="<?= SITE_URL ?>/alumni.php" id="sidebarPublicAlumni" target="_blank">
        <i data-lucide="external-link" style="width:16px;height:16px"></i> Direktori Alumni
      </a>
    </li>
    <li>
      <a href="<?= SITE_URL ?>/berita.php" id="sidebarPublicBerita" target="_blank">
        <i data-lucide="external-link" style="width:16px;height:16px"></i> Portal Berita
      </a>
    </li>

    <li style="margin-top:0.75rem;border-top:1px solid var(--gray-100);padding-top:0.75rem;">
      <a href="<?= SITE_URL ?>/logout.php" id="sidebarLogout" style="color:var(--danger);">
        <i data-lucide="log-out" style="width:16px;height:16px"></i> Keluar
      </a>
    </li>
  </ul>
</aside>

