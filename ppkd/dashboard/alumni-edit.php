<?php
// ============================================================
// Tambah / Ubah Alumni (Admin) — dashboard/alumni-edit.php
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db    = getDB();
$user  = currentUser();
$id    = (int) ($_GET['id'] ?? 0);
$isEdit= ($id > 0);

$errors = [];
$alumni = null;

if ($isEdit) {
    $stmt = $db->prepare('SELECT * FROM alumni WHERE id = ?');
    $stmt->execute([$id]);
    $alumni = $stmt->fetch();

    if (!$alumni) {
        setFlash('danger', 'Data alumni tidak ditemukan.');
        header('Location: alumni.php');
        exit;
    }
} else {
    // Default next nomor urut
    $nextNo = (int) $db->query('SELECT COALESCE(MAX(nomor_urut), 0) + 1 FROM alumni')->fetchColumn();
    $alumni = [
        'id'           => 0,
        'nomor_urut'   => $nextNo,
        'nama_lengkap' => '',
        'jurusan_id'   => 1,
        'tahun_lulus'  => (int) date('Y'),
        'pekerjaan'    => '',
        'kota'         => '',
        'deskripsi'    => '',
        'foto'         => null,
        'is_published' => 1,
    ];
}

$allJurusan = getAllJurusan();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrf($token)) {
        $errors[] = 'Token keamanan tidak valid. Silakan coba lagi.';
    }

    $nomorUrut   = (int) ($_POST['nomor_urut'] ?? 0);
    $namaLengkap = trim($_POST['nama_lengkap'] ?? '');
    $jurusanId   = (int) ($_POST['jurusan_id'] ?? 0);
    $tahunLulus  = (int) ($_POST['tahun_lulus'] ?? 0);
    $pekerjaan   = trim($_POST['pekerjaan'] ?? '');
    $kota        = trim($_POST['kota'] ?? '');
    $deskripsi   = trim($_POST['deskripsi'] ?? '');
    $isPublished = isset($_POST['is_published']) ? 1 : 0;
    $hapusFoto   = isset($_POST['hapus_foto']) && $_POST['hapus_foto'] === '1';

    // Validations
    if ($nomorUrut <= 0) {
        $errors[] = 'Nomor urut harus berupa angka positif.';
    } else {
        // Check uniqueness of nomor_urut
        $checkSql = 'SELECT id FROM alumni WHERE nomor_urut = ?' . ($isEdit ? ' AND id != ?' : '');
        $checkParams = $isEdit ? [$nomorUrut, $id] : [$nomorUrut];
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->execute($checkParams);
        if ($checkStmt->fetch()) {
            $errors[] = 'Nomor urut ' . $nomorUrut . ' sudah digunakan oleh alumni lain.';
        }
    }

    if (empty($namaLengkap)) {
        $errors[] = 'Nama lengkap alumni wajib diisi.';
    }

    if ($jurusanId <= 0) {
        $errors[] = 'Pilih program keahlian (jurusan) yang valid.';
    }

    if ($tahunLulus < 1980 || $tahunLulus > ((int)date('Y') + 2)) {
        $errors[] = 'Tahun kelulusan tidak valid.';
    }

    // Photo Upload Handling
    $newFotoFilename = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileType     = mime_content_type($_FILES['foto']['tmp_name']);
        $fileSize     = $_FILES['foto']['size'];

        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = 'Format foto tidak didukung. Harap gunakan format JPG, PNG, atau WebP.';
        } elseif ($fileSize > MAX_FILE_SIZE) {
            $errors[] = 'Ukuran foto maksimal adalah 2MB.';
        } else {
            $ext             = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $newFotoFilename = 'alumni_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destPath        = UPLOAD_PATH . 'alumni/' . $newFotoFilename;

            if (!is_dir(UPLOAD_PATH . 'alumni/')) {
                mkdir(UPLOAD_PATH . 'alumni/', 0755, true);
            }

            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $destPath)) {
                $errors[] = 'Gagal menyimpan foto yang diunggah ke server.';
                $newFotoFilename = null;
            }
        }
    }

    if (empty($errors)) {
        if ($isEdit) {
            // Determine final photo filename
            $finalFoto = $alumni['foto'];

            if ($newFotoFilename) {
                // Delete old photo if exists
                if (!empty($alumni['foto']) && file_exists(UPLOAD_PATH . 'alumni/' . $alumni['foto'])) {
                    @unlink(UPLOAD_PATH . 'alumni/' . $alumni['foto']);
                }
                $finalFoto = $newFotoFilename;
            } elseif ($hapusFoto) {
                if (!empty($alumni['foto']) && file_exists(UPLOAD_PATH . 'alumni/' . $alumni['foto'])) {
                    @unlink(UPLOAD_PATH . 'alumni/' . $alumni['foto']);
                }
                $finalFoto = null;
            }

            $updateStmt = $db->prepare(
                'UPDATE alumni SET 
                    nomor_urut = ?,
                    nama_lengkap = ?,
                    jurusan_id = ?,
                    tahun_lulus = ?,
                    pekerjaan = ?,
                    kota = ?,
                    deskripsi = ?,
                    foto = ?,
                    is_published = ?
                 WHERE id = ?'
            );
            $updateStmt->execute([
                $nomorUrut,
                $namaLengkap,
                $jurusanId,
                $tahunLulus,
                $pekerjaan,
                $kota,
                $deskripsi,
                $finalFoto,
                $isPublished,
                $id,
            ]);

            setFlash('success', 'Data alumni "' . $namaLengkap . '" berhasil diperbarui!');
            header('Location: alumni.php');
            exit;
        } else {
            // Insert
            $insertStmt = $db->prepare(
                'INSERT INTO alumni (nomor_urut, nama_lengkap, jurusan_id, tahun_lulus, foto, deskripsi, kota, pekerjaan, is_published)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $insertStmt->execute([
                $nomorUrut,
                $namaLengkap,
                $jurusanId,
                $tahunLulus,
                $newFotoFilename,
                $deskripsi,
                $kota,
                $pekerjaan,
                $isPublished,
            ]);

            setFlash('success', 'Alumni baru "' . $namaLengkap . '" berhasil ditambahkan!');
            header('Location: alumni.php');
            exit;
        }
    }

    // Keep submitted values on error
    $alumni['nomor_urut']   = $nomorUrut;
    $alumni['nama_lengkap'] = $namaLengkap;
    $alumni['jurusan_id']   = $jurusanId;
    $alumni['tahun_lulus']  = $tahunLulus;
    $alumni['pekerjaan']    = $pekerjaan;
    $alumni['kota']         = $kota;
    $alumni['deskripsi']    = $deskripsi;
    $alumni['is_published'] = $isPublished;
}

$pageTitle = ($isEdit ? 'Ubah Data Alumni' : 'Tambah Alumni Baru') . ' — ' . SITE_NAME;
$bodyClass = 'dashboard-page';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="padding-top:70px;">
<div class="dashboard-layout">

  <!-- Sidebar -->
  <?php $activePage = 'alumni'; require __DIR__ . '/sidebar.php'; ?>

  <!-- Main Content -->
  <div class="dashboard-main">

    <!-- Breadcrumb & Back -->
    <div style="margin-bottom:1.5rem;">
      <a href="alumni.php" style="display:inline-flex;align-items:center;gap:0.4rem;font-size:var(--text-sm);font-weight:600;color:var(--gray-500);text-decoration:none;">
        <i data-lucide="arrow-left" style="width:16px;height:16px"></i> Kembali ke Daftar Alumni
      </a>
    </div>

    <!-- Header -->
    <div style="margin-bottom:2rem;">
      <h1 style="font-size:var(--text-2xl);font-weight:800;color:var(--gray-900);margin:0 0 0.25rem;">
        <?= $isEdit ? 'Ubah Data Alumni' : 'Tambah Alumni Baru' ?>
      </h1>
      <p style="color:var(--gray-500);font-size:var(--text-sm);margin:0;">
        <?= $isEdit ? 'Perbarui nama lengkap, nomor urut, jurusan, atau riwayat alumni.' : 'Masukkan informasi alumni baru untuk didaftarkan ke sistem.' ?>
      </p>
    </div>

    <?php if ($errors): ?>
    <div class="alert alert-danger" style="margin-bottom:1.5rem;">
      <i data-lucide="alert-circle" style="width:18px;height:18px;flex-shrink:0;"></i>
      <div>
        <strong>Periksa kembali formulir:</strong>
        <ul style="margin:0.25rem 0 0 1.25rem;padding:0;">
          <?php foreach ($errors as $err): ?>
          <li><?= e($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" action="alumni-edit.php<?= $isEdit ? '?id='.$id : '' ?>" enctype="multipart/form-data" id="alumniForm">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

      <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;align-items:start;">
        
        <!-- Left: Primary Info -->
        <div>
          <!-- Biodata Card -->
          <div class="admin-card">
            <div class="admin-card-header">
              <h2 style="font-size:var(--text-base);font-weight:700;margin:0;color:var(--gray-900);">Informasi Biodata</h2>
            </div>
            <div class="admin-card-body">
              
              <div style="display:grid;grid-template-columns:120px 1fr;gap:1rem;margin-bottom:1.25rem;">
                <div class="form-group" style="margin:0;">
                  <label class="form-label" for="nomorUrut">No. Urut <span style="color:var(--danger)">*</span></label>
                  <input type="number" id="nomorUrut" name="nomor_urut" class="form-input"
                         value="<?= e($alumni['nomor_urut']) ?>" min="1" required>
                </div>
                <div class="form-group" style="margin:0;">
                  <label class="form-label" for="namaLengkap">Nama Lengkap Alumni <span style="color:var(--danger)">*</span></label>
                  <input type="text" id="namaLengkap" name="nama_lengkap" class="form-input"
                         placeholder="Contoh: Ahmad Fauzi Ramadhan"
                         value="<?= e($alumni['nama_lengkap']) ?>" required style="font-weight:600;">
                  <p style="font-size:var(--text-xs);color:var(--gray-400);margin:0.25rem 0 0;">Ubah nama lengkap alumni yang akan tampil di direktori publik.</p>
                </div>
              </div>

              <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem;">
                <div class="form-group" style="margin:0;">
                  <label class="form-label" for="jurusanId">Program Keahlian (Jurusan) <span style="color:var(--danger)">*</span></label>
                  <select id="jurusanId" name="jurusan_id" class="form-select" required>
                    <option value="">-- Pilih Jurusan --</option>
                    <?php foreach ($allJurusan as $j): ?>
                    <option value="<?= $j['id'] ?>" <?= (int)$alumni['jurusan_id'] === (int)$j['id'] ? 'selected' : '' ?>>
                      <?= e($j['nama_jurusan']) ?> (<?= e($j['kode']) ?>)
                    </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group" style="margin:0;">
                  <label class="form-label" for="tahunLulus">Tahun Kelulusan (Angkatan) <span style="color:var(--danger)">*</span></label>
                  <input type="number" id="tahunLulus" name="tahun_lulus" class="form-input"
                         min="1990" max="<?= (int)date('Y')+2 ?>"
                         value="<?= e($alumni['tahun_lulus']) ?>" required>
                </div>
              </div>

              <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem;">
                <div class="form-group" style="margin:0;">
                  <label class="form-label" for="pekerjaan">Pekerjaan / Profesi Saat Ini</label>
                  <input type="text" id="pekerjaan" name="pekerjaan" class="form-input"
                         placeholder="Contoh: Network Engineer / Freelancer"
                         value="<?= e($alumni['pekerjaan'] ?? '') ?>">
                </div>
                <div class="form-group" style="margin:0;">
                  <label class="form-label" for="kota">Kota Domisili</label>
                  <input type="text" id="kota" name="kota" class="form-input"
                         placeholder="Contoh: Jakarta"
                         value="<?= e($alumni['kota'] ?? '') ?>">
                </div>
              </div>

              <div class="form-group" style="margin:0;">
                <label class="form-label" for="deskripsi">Deskripsi / Profil Singkat</label>
                <textarea id="deskripsi" name="deskripsi"
                          style="width:100%;min-height:120px;border:1.5px solid var(--gray-200);border-radius:var(--radius);font-family:var(--font-sans);font-size:var(--text-sm);padding:0.75rem 1rem;resize:vertical;"
                          placeholder="Ceritakan pengalaman, keahlian, atau portofolio alumni…"><?= e($alumni['deskripsi'] ?? '') ?></textarea>
              </div>

            </div>
          </div>
        </div>

        <!-- Right: Photo & Publish Settings -->
        <div>
          <!-- Status Publikasi -->
          <div class="admin-card">
            <div class="admin-card-header">
              <h2 style="font-size:var(--text-base);font-weight:700;margin:0;color:var(--gray-900);">Status Publikasi</h2>
            </div>
            <div class="admin-card-body">
              <label style="display:flex;align-items:flex-start;gap:0.75rem;cursor:pointer;">
                <input type="checkbox" name="is_published" value="1" <?= $alumni['is_published'] ? 'checked' : '' ?>
                       style="width:18px;height:18px;accent-color:var(--accent);margin-top:2px;">
                <div>
                  <div style="font-weight:700;color:var(--gray-900);font-size:var(--text-sm);">Publikasikan ke Direktori</div>
                  <div style="font-size:var(--text-xs);color:var(--gray-500);margin-top:0.2rem;">
                    Jika dicentang, profil alumni dapat dilihat secara publik di halaman direktori alumni.
                  </div>
                </div>
              </label>

              <div style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--gray-150);display:flex;flex-direction:column;gap:0.75rem;">
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;" id="btnSimpanAlumni">
                  <i data-lucide="check" style="width:16px;height:16px"></i>
                  <?= $isEdit ? 'Simpan Perubahan' : 'Simpan Alumni Baru' ?>
                </button>
                <a href="alumni.php" class="btn btn-ghost" style="width:100%;justify-content:center;">
                  Batal
                </a>
              </div>
            </div>
          </div>

          <!-- Foto Card -->
          <div class="admin-card">
            <div class="admin-card-header">
              <h2 style="font-size:var(--text-base);font-weight:700;margin:0;color:var(--gray-900);">Foto Alumni</h2>
            </div>
            <div class="admin-card-body" style="text-align:center;">
              <div style="margin-bottom:1.25rem;">
                <img id="photoPreview"
                     src="<?= alumniPhoto($alumni['foto'], $alumni['nama_lengkap']) ?>"
                     alt="Foto Alumni"
                     style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:3px solid var(--gray-200);margin:0 auto;box-shadow:var(--shadow-sm);">
              </div>

              <div>
                <label for="fotoInput" class="btn btn-outline btn-sm" style="cursor:pointer;width:100%;justify-content:center;">
                  <i data-lucide="upload" style="width:14px;height:14px"></i>
                  <?= !empty($alumni['foto']) ? 'Ganti Foto' : 'Pilih Foto' ?>
                </label>
                <input type="file" id="fotoInput" name="foto" accept="image/jpeg,image/png,image/webp" style="display:none;">
                <p style="font-size:var(--text-xs);color:var(--gray-400);margin:0.5rem 0 0;">
                  Format: JPG, PNG, WebP. Maks 2MB.
                </p>
              </div>

              <?php if (!empty($alumni['foto'])): ?>
              <div style="margin-top:1rem;padding-top:0.75rem;border-top:1px dashed var(--gray-200);">
                <label style="display:inline-flex;align-items:center;gap:0.5rem;font-size:var(--text-xs);color:var(--danger);cursor:pointer;">
                  <input type="checkbox" name="hapus_foto" value="1" id="hapusFotoCheck">
                  Hapus foto saat ini
                </label>
              </div>
              <?php endif; ?>

            </div>
          </div>

        </div>

      </div>
    </form>

  </div><!-- /.dashboard-main -->
</div><!-- /.dashboard-layout -->
</div>

<script>
// Live Preview on file select
document.getElementById('fotoInput')?.addEventListener('change', function() {
  const file = this.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('photoPreview').src = e.target.result;
    };
    reader.readAsDataURL(file);
    const hapusCheck = document.getElementById('hapusFotoCheck');
    if (hapusCheck) hapusCheck.checked = false;
  }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

