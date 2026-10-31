<?php
// ============================================================
// Register — register.php
// ============================================================
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/dashboard/');
    exit;
}

$pageTitle = 'Daftar Akun — ' . SITE_NAME;
$errors    = [];
$data      = ['nama' => '', 'email' => '', 'role' => 'alumni'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['nama']     = trim($_POST['nama']     ?? '');
    $data['email']    = trim($_POST['email']    ?? '');
    $data['role']     = in_array($_POST['role'] ?? '', ['alumni', 'instruktur']) ? $_POST['role'] : 'alumni';
    $password         = trim($_POST['password'] ?? '');
    $passwordConfirm  = trim($_POST['password_confirm'] ?? '');

    if (empty($data['nama']))  $errors[] = 'Nama wajib diisi.';
    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
    if (strlen($password) < 6) $errors[] = 'Password minimal 6 karakter.';
    if ($password !== $passwordConfirm) $errors[] = 'Konfirmasi password tidak sesuai.';

    if (empty($errors)) {
        $db   = getDB();
        $check = $db->prepare('SELECT id FROM users WHERE email = ?');
        $check->execute([$data['email']]);
        if ($check->fetch()) {
            $errors[] = 'Email sudah digunakan. Coba dengan email lain.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $db->prepare('INSERT INTO users (nama, email, password, role) VALUES (?,?,?,?)');
            $stmt->execute([$data['nama'], $data['email'], $hash, $data['role']]);

            $userId = $db->lastInsertId();
            $user   = $db->prepare('SELECT * FROM users WHERE id = ?');
            $user->execute([$userId]);
            loginUser($user->fetch());

            setFlash('success', 'Akun berhasil dibuat! Selamat datang, ' . $data['nama'] . '.');
            header('Location: ' . SITE_URL . '/dashboard/');
            exit;
        }
    }
}

$bodyClass = 'auth-page';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-layout">

  <!-- Left Panel -->
  <div class="auth-panel">
    <div class="auth-panel-content">
      <a href="<?= SITE_URL ?>" style="display:inline-flex;align-items:center;gap:0.75rem;margin-bottom:3rem;">
        <div class="brand-logo-wrap" style="width:40px;height:40px;">
          <img src="<?= SITE_URL ?>/assets/img/logo-placeholder.svg" alt="Logo" width="28" height="28">
        </div>
        <div class="brand-text">
          <span class="brand-name" style="font-size:var(--text-base);">Ikatan Alumni</span>
          <span class="brand-sub">PPKD</span>
        </div>
      </a>

      <h2>Bergabunglah dengan Komunitas Alumni PPKD</h2>
      <p>Buat akun dan jadilah bagian dari ribuan alumni yang sudah terhubung dalam satu platform digital.</p>

      <div style="margin-top:3rem;background:rgba(255,255,255,0.06);border-radius:var(--radius-lg);padding:1.5rem;">
        <p style="font-size:var(--text-xs);text-transform:uppercase;letter-spacing:0.07em;color:rgba(255,255,255,0.5);margin-bottom:1rem;font-weight:600;">Keuntungan bergabung</p>
        <?php
        $benefits = [
          'Profil alumni yang dapat dilihat komunitas',
          'Akses ke berita dan informasi eksklusif',
          'Terhubung langsung dengan sesama alumni',
          'Informasi beasiswa dan peluang karir',
          'Undangan ke event dan reuni resmi',
        ];
        foreach ($benefits as $b): ?>
        <div style="display:flex;align-items:center;gap:0.75rem;color:rgba(255,255,255,0.75);font-size:var(--text-sm);margin-bottom:0.75rem;">
          <i data-lucide="check-circle" style="width:15px;height:15px;color:#34d399;flex-shrink:0;"></i>
          <?= e($b) ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Right: Form -->
  <div class="auth-form-panel">
    <div class="auth-form-wrap">
      <div class="auth-form-header">
        <h3>Buat Akun Baru</h3>
        <p>Sudah punya akun? <a href="<?= SITE_URL ?>/login.php">Masuk sekarang</a></p>
      </div>

      <?php if ($errors): ?>
      <div class="alert alert-danger">
        <i data-lucide="alert-circle" style="width:16px;height:16px"></i>
        <div><?= implode('<br>', array_map('e', $errors)) ?></div>
      </div>
      <?php endif; ?>

      <form method="POST" action="register.php" id="registerForm" novalidate>

        <div class="form-group">
          <label class="form-label" for="regNama">Nama Lengkap <span style="color:var(--danger)">*</span></label>
          <input type="text" id="regNama" name="nama" class="form-input"
                 placeholder="Nama lengkap Anda"
                 value="<?= e($data['nama']) ?>" autocomplete="name" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="regEmail">Alamat Email <span style="color:var(--danger)">*</span></label>
          <input type="email" id="regEmail" name="email" class="form-input"
                 placeholder="email@anda.com"
                 value="<?= e($data['email']) ?>" autocomplete="email" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="regRole">Saya adalah</label>
          <select id="regRole" name="role" class="form-input" style="cursor:pointer;">
            <option value="alumni"     <?= $data['role'] === 'alumni'     ? 'selected' : '' ?>>Alumni PPKD</option>
            <option value="instruktur" <?= $data['role'] === 'instruktur' ? 'selected' : '' ?>>Instruktur PPKD</option>
          </select>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
          <div class="form-group">
            <label class="form-label" for="regPassword">Password <span style="color:var(--danger)">*</span></label>
            <input type="password" id="regPassword" name="password" class="form-input"
                   placeholder="Min. 6 karakter" autocomplete="new-password" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="regPasswordConfirm">Konfirmasi Password</label>
            <input type="password" id="regPasswordConfirm" name="password_confirm" class="form-input"
                   placeholder="Ulangi password" autocomplete="new-password" required>
          </div>
        </div>

        <button type="submit" class="btn-auth" id="btnRegister">
          <i data-lucide="user-plus" style="width:16px;height:16px"></i>
          Buat Akun
        </button>

      </form>

      <p class="auth-footer-text">
        Dengan mendaftar, Anda menyetujui
        <a href="#">Ketentuan Penggunaan</a> dan
        <a href="#">Kebijakan Privasi</a> kami.
      </p>
    </div>
  </div>

</div>

<style>
.auth-page main { padding-top: 0; }
.auth-page .site-header { display: none; }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
