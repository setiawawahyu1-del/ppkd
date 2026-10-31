<?php
// ============================================================
// Kontak — kontak.php
// ============================================================
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'Hubungi Kami — ' . SITE_NAME;
$pageDescription = 'Hubungi Ikatan Alumni PPKD untuk pertanyaan, saran, atau kerjasama.';

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = trim($_POST['nama']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $subjek  = trim($_POST['subjek']  ?? '');
    $pesan   = trim($_POST['pesan']   ?? '');

    if (empty($nama))   $errors[] = 'Nama wajib diisi.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
    if (empty($subjek)) $errors[] = 'Subjek wajib diisi.';
    if (empty($pesan))  $errors[] = 'Pesan wajib diisi.';

    if (empty($errors)) {
        $db   = getDB();
        $stmt = $db->prepare('INSERT INTO kontak_pesan (nama,email,subjek,pesan) VALUES (?,?,?,?)');
        $stmt->execute([$nama, $email, $subjek, $pesan]);
        $success = true;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <div class="page-header-inner">
      <nav class="breadcrumb-nav" aria-label="Breadcrumb">
        <a href="<?= SITE_URL ?>">Beranda</a>
        <span class="sep"><i data-lucide="chevron-right" style="width:13px;height:13px"></i></span>
        <span class="current">Kontak</span>
      </nav>
      <h1>Hubungi Kami</h1>
      <p class="subtitle">Kami siap membantu dan merespons pertanyaan Anda</p>
    </div>
  </div>
</div>

<section class="section bg-gray" aria-labelledby="kontakTitle">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1.4fr;gap:4rem;align-items:start;max-width:1060px;margin:0 auto;">

      <!-- Info -->
      <div>
        <h2 id="kontakTitle" style="font-size:var(--text-2xl);font-weight:800;margin-bottom:1.5rem;">Informasi Kontak</h2>

        <div style="display:flex;flex-direction:column;gap:1.5rem;margin-bottom:2.5rem;">
          <?php
          $infos = [
            ['icon'=>'map-pin', 'label'=>'Alamat', 'value'=>SITE_ADDRESS],
            ['icon'=>'phone',   'label'=>'Telepon', 'value'=>SITE_PHONE],
            ['icon'=>'mail',    'label'=>'Email',   'value'=>SITE_EMAIL],
            ['icon'=>'clock',   'label'=>'Jam Kerja','value'=>'Senin – Jumat, 08.00 – 16.00 WIB'],
          ];
          foreach ($infos as $info): ?>
          <div style="display:flex;gap:1rem;align-items:flex-start;">
            <div style="width:42px;height:42px;background:var(--accent-xs);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;color:var(--accent);flex-shrink:0;">
              <i data-lucide="<?= $info['icon'] ?>" style="width:18px;height:18px"></i>
            </div>
            <div>
              <span style="font-size:var(--text-xs);font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--gray-400);"><?= $info['label'] ?></span>
              <p style="font-size:var(--text-sm);font-weight:500;color:var(--gray-700);margin:0.2rem 0 0;line-height:1.6;"><?= e($info['value']) ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Social -->
        <h3 style="font-size:var(--text-sm);font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--gray-400);margin-bottom:1rem;">Media Sosial</h3>
        <div class="social-links">
          <a href="#" class="social-link" style="background:var(--gray-100);border-color:var(--gray-200);color:var(--gray-600);" title="Instagram">
            <i data-lucide="instagram" style="width:16px;height:16px"></i>
          </a>
          <a href="#" class="social-link" style="background:var(--gray-100);border-color:var(--gray-200);color:var(--gray-600);" title="Facebook">
            <i data-lucide="facebook" style="width:16px;height:16px"></i>
          </a>
          <a href="#" class="social-link" style="background:var(--gray-100);border-color:var(--gray-200);color:var(--gray-600);" title="YouTube">
            <i data-lucide="youtube" style="width:16px;height:16px"></i>
          </a>
        </div>
      </div>

      <!-- Form -->
      <div style="background:var(--white);border:1px solid var(--gray-200);border-radius:var(--radius-xl);padding:2.5rem;">
        <h2 style="font-size:var(--text-xl);font-weight:800;margin-bottom:0.375rem;color:var(--gray-900);">Kirim Pesan</h2>
        <p style="font-size:var(--text-sm);color:var(--gray-500);margin-bottom:2rem;">Isi formulir di bawah dan kami akan merespons sesegera mungkin.</p>

        <?php if ($success): ?>
        <div class="alert alert-success alert-static" role="status">
          <i data-lucide="check-circle" style="width:16px;height:16px"></i>
          Pesan Anda berhasil dikirim! Kami akan segera merespons.
        </div>
        <?php elseif ($errors): ?>
        <div class="alert alert-danger">
          <i data-lucide="alert-circle" style="width:16px;height:16px"></i>
          <div><?= implode('<br>', array_map('e', $errors)) ?></div>
        </div>
        <?php endif; ?>

        <form method="POST" action="kontak.php" id="contactForm" novalidate>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
            <div class="form-group" style="margin:0;">
              <label class="form-label" for="kontakNama">Nama Lengkap <span style="color:var(--danger)">*</span></label>
              <input type="text" id="kontakNama" name="nama" class="form-input"
                     placeholder="Nama Anda"
                     value="<?= e($_POST['nama'] ?? '') ?>" required>
            </div>
            <div class="form-group" style="margin:0;">
              <label class="form-label" for="kontakEmail">Email <span style="color:var(--danger)">*</span></label>
              <input type="email" id="kontakEmail" name="email" class="form-input"
                     placeholder="email@anda.com"
                     value="<?= e($_POST['email'] ?? '') ?>" required>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="kontakSubjek">Subjek <span style="color:var(--danger)">*</span></label>
            <input type="text" id="kontakSubjek" name="subjek" class="form-input"
                   placeholder="Perihal pesan"
                   value="<?= e($_POST['subjek'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="kontakPesan">Pesan <span style="color:var(--danger)">*</span></label>
            <textarea id="kontakPesan" name="pesan"
                      style="width:100%;min-height:140px;border:1.5px solid var(--gray-200);border-radius:var(--radius);font-family:var(--font-sans);font-size:var(--text-sm);padding:0.75rem 1rem;resize:vertical;transition:border-color var(--transition);"
                      placeholder="Tuliskan pesan Anda di sini…"
                      required><?= e($_POST['pesan'] ?? '') ?></textarea>
          </div>
          <button type="submit" class="btn-auth" id="btnSubmitKontak">
            <i data-lucide="send" style="width:16px;height:16px"></i>
            Kirim Pesan
          </button>
        </form>
      </div>

    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
