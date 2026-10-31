<?php
// ============================================================
// Dashboard Profil — dashboard/profil.php
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();
$user   = currentUser();
$db     = getDB();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = trim($_POST['nama']    ?? '');
    $newPass = trim($_POST['new_password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if (empty($nama)) $errors[] = 'Nama wajib diisi.';
    if ($newPass !== '' && strlen($newPass) < 6) $errors[] = 'Password minimal 6 karakter.';
    if ($newPass !== $confirm) $errors[] = 'Konfirmasi password tidak sesuai.';

    // Handle photo upload
    $fotoFilename = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileType     = mime_content_type($_FILES['foto']['tmp_name']);
        $fileSize     = $_FILES['foto']['size'];

        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = 'Format foto tidak didukung. Gunakan JPG, PNG, atau WebP.';
        } elseif ($fileSize > MAX_FILE_SIZE) {
            $errors[] = 'Ukuran foto maksimal 2MB.';
        } else {
            $ext          = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $fotoFilename = 'profil_' . $user['id'] . '_' . time() . '.' . $ext;
            $destPath     = UPLOAD_PATH . 'profil/' . $fotoFilename;
            if (!is_dir(UPLOAD_PATH . 'profil/')) mkdir(UPLOAD_PATH . 'profil/', 0755, true);
            move_uploaded_file($_FILES['foto']['tmp_name'], $destPath);
        }
    }

    if (empty($errors)) {
        if ($newPass !== '') {
            $hash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
            if ($fotoFilename) {
                $db->prepare('UPDATE users SET nama=?, password=?, foto=? WHERE id=?')
                   ->execute([$nama, $hash, $fotoFilename, $user['id']]);
            } else {
                $db->prepare('UPDATE users SET nama=?, password=? WHERE id=?')
                   ->execute([$nama, $hash, $user['id']]);
            }
        } else {
            if ($fotoFilename) {
                $db->prepare('UPDATE users SET nama=?, foto=? WHERE id=?')
                   ->execute([$nama, $fotoFilename, $user['id']]);
            } else {
                $db->prepare('UPDATE users SET nama=? WHERE id=?')
                   ->execute([$nama, $user['id']]);
            }
        }
        // Refresh session
        $updated = $db->prepare('SELECT * FROM users WHERE id=?');
        $updated->execute([$user['id']]);
        loginUser($updated->fetch());
        $user = currentUser();

        setFlash('success', 'Profil berhasil diperbarui!');
        header('Location: profil.php');
        exit;
    }
}

$pageTitle = 'Profil Saya — ' . SITE_NAME;
$bodyClass = 'dashboard-page';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="padding-top:70px;">
<div class="dashboard-layout">

  <!-- Sidebar -->
  <?php $activePage = 'profil'; require __DIR__ . '/sidebar.php'; ?>

  <!-- Main -->
  <div class="dashboard-main">
    <div style="max-width:600px;">
      <h1 style="font-size:var(--text-2xl);font-weight:800;margin-bottom:0.25rem;color:var(--gray-900);">Profil Saya</h1>
      <p style="color:var(--gray-500);font-size:var(--text-sm);margin-bottom:2rem;">Perbarui informasi akun Anda</p>

      <?php if ($errors): ?>
      <div class="alert alert-danger" style="margin-bottom:1.5rem;">
        <i data-lucide="alert-circle" style="width:16px;height:16px"></i>
        <div><?= implode('<br>', array_map('e', $errors)) ?></div>
      </div>
      <?php endif; ?>

      <form method="POST" action="profil.php" enctype="multipart/form-data" id="profileForm">
        <div style="background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius-lg);padding:2rem;margin-bottom:1.5rem;">
          <h2 style="font-size:var(--text-base);font-weight:700;margin-bottom:1.5rem;color:var(--gray-900);">Foto Profil</h2>

          <div style="display:flex;align-items:center;gap:1.5rem;">
            <img id="avatarPreview"
                 src="<?= $user['foto'] ? profilPhoto($user['foto']) : SITE_URL . '/assets/img/avatar-placeholder.svg' ?>"
                 alt="Foto profil"
                 style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid var(--gray-100);">
            <div>
              <label for="fotoInput" class="btn btn-outline btn-sm" style="cursor:pointer;">
                <i data-lucide="upload" style="width:14px;height:14px"></i>
                Ganti Foto
              </label>
              <input type="file" id="fotoInput" name="foto" accept="image/*" style="display:none;">
              <p style="font-size:var(--text-xs);color:var(--gray-400);margin:0.5rem 0 0;">JPG, PNG, WebP. Maks. 2MB.</p>
            </div>
          </div>
        </div>

        <div style="background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius-lg);padding:2rem;margin-bottom:1.5rem;">
          <h2 style="font-size:var(--text-base);font-weight:700;margin-bottom:1.5rem;color:var(--gray-900);">Informasi Akun</h2>

          <div class="form-group">
            <label class="form-label" for="profilNama">Nama Lengkap</label>
            <input type="text" id="profilNama" name="nama" class="form-input"
                   value="<?= e($user['nama']) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" class="form-input"
                   value="<?= e($user['email']) ?>"
                   disabled style="background:var(--gray-50);cursor:not-allowed;color:var(--gray-500);">
            <p style="font-size:var(--text-xs);color:var(--gray-400);margin:0.375rem 0 0;">Email tidak dapat diubah.</p>
          </div>
        </div>

        <div style="background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius-lg);padding:2rem;margin-bottom:1.5rem;">
          <h2 style="font-size:var(--text-base);font-weight:700;margin-bottom:0.375rem;color:var(--gray-900);">Ubah Password</h2>
          <p style="font-size:var(--text-sm);color:var(--gray-400);margin-bottom:1.5rem;">Kosongkan jika tidak ingin mengubah password.</p>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group">
              <label class="form-label" for="newPassword">Password Baru</label>
              <input type="password" id="newPassword" name="new_password" class="form-input" placeholder="Min. 6 karakter">
            </div>
            <div class="form-group">
              <label class="form-label" for="confirmPassword">Konfirmasi Password</label>
              <input type="password" id="confirmPassword" name="confirm_password" class="form-input" placeholder="Ulangi password baru">
            </div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary" id="btnSaveProfil">
          <i data-lucide="save" style="width:15px;height:15px"></i>
          Simpan Perubahan
        </button>
      </form>
    </div>
  </div>

</div><!-- /.dashboard-layout -->
</div>

<script>
// Avatar preview on file select
document.getElementById('fotoInput').addEventListener('change', function() {
  const file = this.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = e => { document.getElementById('avatarPreview').src = e.target.result; };
    reader.readAsDataURL(file);
  }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
