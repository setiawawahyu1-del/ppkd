<?php
// ============================================================
// Login — login.php
// ============================================================
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/dashboard/');
    exit;
}

$pageTitle = 'Masuk — ' . SITE_NAME;
$errors    = [];
$email     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email tidak valid.';
    } elseif (empty($password)) {
        $errors[] = 'Password wajib diisi.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            loginUser($user);
            $redirect = $_GET['redirect'] ?? SITE_URL . '/dashboard/';
            header('Location: ' . $redirect);
            exit;
        } else {
            $errors[] = 'Email atau password salah. Silakan coba lagi.';
        }
    }
}

$bodyClass = 'auth-page';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-layout">

  <!-- Left Panel -->
  <div class="auth-panel" id="authPanel">
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

      <h2>Selamat Datang Kembali</h2>
      <p>Masuk ke akun Anda untuk mengakses direktori alumni, berita eksklusif, dan fitur lainnya.</p>

      <div style="margin-top:3rem;display:flex;flex-direction:column;gap:1.25rem;">
        <?php
        $features = [
          ['icon'=>'users',      'text'=>'Terhubung dengan ribuan alumni'],
          ['icon'=>'newspaper',  'text'=>'Akses berita dan informasi terbaru'],
          ['icon'=>'user-check', 'text'=>'Kelola profil alumni Anda'],
          ['icon'=>'shield',     'text'=>'Konten eksklusif terverifikasi'],
        ];
        foreach ($features as $f): ?>
        <div style="display:flex;align-items:center;gap:0.875rem;color:rgba(255,255,255,0.8);font-size:var(--text-sm);">
          <div style="width:32px;height:32px;background:rgba(255,255,255,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="<?= $f['icon'] ?>" style="width:14px;height:14px"></i>
          </div>
          <?= e($f['text']) ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Right: Form -->
  <div class="auth-form-panel">
    <div class="auth-form-wrap">
      <div class="auth-form-header">
        <h3>Masuk ke Akun</h3>
        <p>Belum punya akun? <a href="<?= SITE_URL ?>/register.php">Daftar sekarang</a></p>
      </div>

      <?php if ($errors): ?>
      <div class="alert alert-danger">
        <i data-lucide="alert-circle" style="width:16px;height:16px"></i>
        <?= e($errors[0]) ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="login.php<?= isset($_GET['redirect']) ? '?redirect='.urlencode($_GET['redirect']) : '' ?>" id="loginForm" novalidate>

        <div class="form-group">
          <label class="form-label" for="loginEmail">Alamat Email</label>
          <input type="email" id="loginEmail" name="email" class="form-input"
                 placeholder="email@anda.com"
                 value="<?= e($email) ?>"
                 autocomplete="email" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="loginPassword"
                 style="display:flex;justify-content:space-between;">
            <span>Password</span>
            <a href="#" style="font-size:var(--text-xs);color:var(--accent);">Lupa password?</a>
          </label>
          <input type="password" id="loginPassword" name="password" class="form-input"
                 placeholder="Masukkan password"
                 autocomplete="current-password" required>
        </div>

        <button type="submit" class="btn-auth" id="btnLogin">
          <i data-lucide="log-in" style="width:16px;height:16px"></i>
          Masuk
        </button>

      </form>

      <p class="auth-footer-text">
        Dengan masuk, Anda menyetujui
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
