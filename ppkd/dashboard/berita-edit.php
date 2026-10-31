<?php
// ============================================================
// Tambah / Ubah Berita (Admin) — dashboard/berita-edit.php
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db     = getDB();
$user   = currentUser();
$id     = (int) ($_GET['id'] ?? 0);
$isEdit = ($id > 0);

$errors = [];
$berita = null;

if ($isEdit) {
    $stmt = $db->prepare('SELECT * FROM berita WHERE id = ?');
    $stmt->execute([$id]);
    $berita = $stmt->fetch();

    if (!$berita) {
        setFlash('danger', 'Berita tidak ditemukan.');
        header('Location: berita.php');
        exit;
    }
} else {
    $berita = [
        'id'           => 0,
        'judul'        => '',
        'slug'         => '',
        'kategori'     => 'Umum',
        'ringkasan'    => '',
        'konten'       => '',
        'thumbnail'    => null,
        'is_published' => 1,
        'published_at' => date('Y-m-d H:i:s'),
    ];
}

// Default categories
$standardCategories = ['Umum', 'Pengumuman', 'Beasiswa', 'Prestasi', 'Event', 'Karir', 'Kegiatan'];

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrf($token)) {
        $errors[] = 'Token keamanan tidak valid. Silakan coba lagi.';
    }

    $judul       = trim($_POST['judul'] ?? '');
    $slug        = trim($_POST['slug'] ?? '');
    $kategori    = trim($_POST['kategori'] ?? 'Umum');
    $kategoriBaru= trim($_POST['kategori_baru'] ?? '');
    $ringkasan   = trim($_POST['ringkasan'] ?? '');
    $konten      = trim($_POST['konten'] ?? '');
    $publishedAt = trim($_POST['published_at'] ?? date('Y-m-d H:i:s'));
    $isPublished = isset($_POST['is_published']) ? 1 : 0;
    $hapusThumb  = isset($_POST['hapus_thumb']) && $_POST['hapus_thumb'] === '1';

    if ($kategori === '__baru__' && !empty($kategoriBaru)) {
        $kategori = $kategoriBaru;
    }

    // Validations
    if (empty($judul)) {
        $errors[] = 'Judul berita wajib diisi.';
    }

    if (empty($slug)) {
        $slug = slugify($judul);
    } else {
        $slug = slugify($slug);
    }

    if (empty($slug)) {
        $slug = 'berita-' . time();
    }

    // Check slug uniqueness
    $slugSql = 'SELECT id FROM berita WHERE slug = ?' . ($isEdit ? ' AND id != ?' : '');
    $slugParams = $isEdit ? [$slug, $id] : [$slug];
    $slugStmt = $db->prepare($slugSql);
    $slugStmt->execute($slugParams);
    if ($slugStmt->fetch()) {
        $slug .= '-' . time();
    }

    if (empty($konten)) {
        $errors[] = 'Konten berita wajib diisi.';
    }

    if (empty($ringkasan) && !empty($konten)) {
        $ringkasan = excerpt($konten, 180);
    }

    // Thumbnail Upload Handling
    $newThumbFilename = null;
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileType     = mime_content_type($_FILES['thumbnail']['tmp_name']);
        $fileSize     = $_FILES['thumbnail']['size'];

        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = 'Format thumbnail tidak didukung. Harap gunakan format JPG, PNG, atau WebP.';
        } elseif ($fileSize > MAX_FILE_SIZE) {
            $errors[] = 'Ukuran gambar maksimal adalah 2MB.';
        } else {
            $ext              = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
            $newThumbFilename = 'berita_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destPath         = UPLOAD_PATH . 'berita/' . $newThumbFilename;

            if (!is_dir(UPLOAD_PATH . 'berita/')) {
                mkdir(UPLOAD_PATH . 'berita/', 0755, true);
            }

            if (!move_uploaded_file($_FILES['thumbnail']['tmp_name'], $destPath)) {
                $errors[] = 'Gagal menyimpan gambar thumbnail ke server.';
                $newThumbFilename = null;
            }
        }
    }

    if (empty($errors)) {
        if ($isEdit) {
            // Determine final thumbnail
            $finalThumb = $berita['thumbnail'];

            if ($newThumbFilename) {
                if (!empty($berita['thumbnail']) && file_exists(UPLOAD_PATH . 'berita/' . $berita['thumbnail'])) {
                    @unlink(UPLOAD_PATH . 'berita/' . $berita['thumbnail']);
                }
                $finalThumb = $newThumbFilename;
            } elseif ($hapusThumb) {
                if (!empty($berita['thumbnail']) && file_exists(UPLOAD_PATH . 'berita/' . $berita['thumbnail'])) {
                    @unlink(UPLOAD_PATH . 'berita/' . $berita['thumbnail']);
                }
                $finalThumb = null;
            }

            $updateStmt = $db->prepare(
                'UPDATE berita SET 
                    judul = ?,
                    slug = ?,
                    kategori = ?,
                    ringkasan = ?,
                    konten = ?,
                    thumbnail = ?,
                    is_published = ?,
                    published_at = ?
                 WHERE id = ?'
            );
            $updateStmt->execute([
                $judul,
                $slug,
                $kategori,
                $ringkasan,
                $konten,
                $finalThumb,
                $isPublished,
                $publishedAt,
                $id,
            ]);

            setFlash('success', 'Berita "' . $judul . '" berhasil diperbarui!');
            header('Location: berita.php');
            exit;
        } else {
            // Insert
            $insertStmt = $db->prepare(
                'INSERT INTO berita (judul, slug, kategori, ringkasan, konten, thumbnail, is_published, published_at, author_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $insertStmt->execute([
                $judul,
                $slug,
                $kategori,
                $ringkasan,
                $konten,
                $newThumbFilename,
                $isPublished,
                $publishedAt,
                $user['id'],
            ]);

            setFlash('success', 'Berita baru "' . $judul . '" berhasil diterbitkan!');
            header('Location: berita.php');
            exit;
        }
    }

    // Keep state on validation error
    $berita['judul']        = $judul;
    $berita['slug']         = $slug;
    $berita['kategori']     = $kategori;
    $berita['ringkasan']    = $ringkasan;
    $berita['konten']       = $konten;
    $berita['published_at'] = $publishedAt;
    $berita['is_published'] = $isPublished;
}

$pageTitle = ($isEdit ? 'Ubah Berita' : 'Tulis Berita Baru') . ' — ' . SITE_NAME;
$bodyClass = 'dashboard-page';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="padding-top:70px;">
<div class="dashboard-layout">

  <!-- Sidebar -->
  <?php $activePage = 'berita'; require __DIR__ . '/sidebar.php'; ?>

  <!-- Main Content -->
  <div class="dashboard-main">

    <!-- Breadcrumb & Back -->
    <div style="margin-bottom:1.5rem;">
      <a href="berita.php" style="display:inline-flex;align-items:center;gap:0.4rem;font-size:var(--text-sm);font-weight:600;color:var(--gray-500);text-decoration:none;">
        <i data-lucide="arrow-left" style="width:16px;height:16px"></i> Kembali ke Daftar Berita
      </a>
    </div>

    <!-- Header -->
    <div style="margin-bottom:2rem;">
      <h1 style="font-size:var(--text-2xl);font-weight:800;color:var(--gray-900);margin:0 0 0.25rem;">
        <?= $isEdit ? 'Ubah Berita' : 'Tulis Berita Baru' ?>
      </h1>
      <p style="color:var(--gray-500);font-size:var(--text-sm);margin:0;">
        <?= $isEdit ? 'Ubah judul, kategori, teks konten, gambar thumbnail, atau status tayang berita.' : 'Buat publikasi berita atau pengumuman baru untuk dibagikan kepada alumni.' ?>
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
    <form method="POST" action="berita-edit.php<?= $isEdit ? '?id='.$id : '' ?>" enctype="multipart/form-data" id="beritaForm">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

      <div style="display:grid;grid-template-columns:2.2fr 1fr;gap:1.5rem;align-items:start;">

        <!-- Left: Main Article Content -->
        <div>
          <div class="admin-card">
            <div class="admin-card-header">
              <h2 style="font-size:var(--text-base);font-weight:700;margin:0;color:var(--gray-900);">Konten Berita</h2>
            </div>
            <div class="admin-card-body">

              <!-- Judul -->
              <div class="form-group" style="margin-bottom:1.25rem;">
                <label class="form-label" for="judulBerita">Judul Berita / Pengumuman <span style="color:var(--danger)">*</span></label>
                <input type="text" id="judulBerita" name="judul" class="form-input"
                       placeholder="Masukkan judul berita yang menarik…"
                       value="<?= e($berita['judul']) ?>" required
                       style="font-size:var(--text-base);font-weight:700;padding:0.75rem 1rem;">
              </div>

              <!-- Slug URL -->
              <div class="form-group" style="margin-bottom:1.25rem;">
                <label class="form-label" for="slugBerita">URL Slug (Tautan Halaman)</label>
                <div style="display:flex;align-items:center;background:var(--gray-50);border:1.5px solid var(--gray-200);border-radius:var(--radius);overflow:hidden;">
                  <span style="padding:0 0.875rem;font-size:var(--text-xs);color:var(--gray-400);background:var(--gray-100);line-height:2.6;border-right:1px solid var(--gray-200);">
                    <?= SITE_URL ?>/berita-detail.php?slug=
                  </span>
                  <input type="text" id="slugBerita" name="slug"
                         style="border:none;background:transparent;padding:0.5rem 0.875rem;font-size:var(--text-sm);width:100%;outline:none;"
                         placeholder="otomatis-dibuat-dari-judul"
                         value="<?= e($berita['slug']) ?>">
                </div>
                <p style="font-size:var(--text-xs);color:var(--gray-400);margin:0.25rem 0 0;">Dapat dikosongkan agar otomatis dibuat dari judul berita.</p>
              </div>

              <!-- Ringkasan -->
              <div class="form-group" style="margin-bottom:1.25rem;">
                <label class="form-label" for="ringkasanBerita">Ringkasan Singkat (Lead / Excerpt)</label>
                <textarea id="ringkasanBerita" name="ringkasan"
                          style="width:100%;min-height:75px;border:1.5px solid var(--gray-200);border-radius:var(--radius);font-family:var(--font-sans);font-size:var(--text-sm);padding:0.75rem 1rem;resize:vertical;"
                          placeholder="Ringkasan 1-2 kalimat untuk kartu berita dan meta preview…"><?= e($berita['ringkasan'] ?? '') ?></textarea>
              </div>

              <!-- Konten / Artikel Lengkap -->
              <div class="form-group" style="margin:0;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
                  <label class="form-label" for="kontenBerita" style="margin:0;">
                    Isi Artikel Lengkap <span style="color:var(--danger)">*</span>
                  </label>
                  <!-- Quick formatting helper tools -->
                  <div style="display:inline-flex;gap:0.25rem;">
                    <button type="button" class="btn btn-ghost btn-sm" style="padding:0.2rem 0.5rem;font-size:var(--text-xs);" onclick="insertTag('p')">
                      &lt;p&gt;
                    </button>
                    <button type="button" class="btn btn-ghost btn-sm" style="padding:0.2rem 0.5rem;font-size:var(--text-xs);" onclick="insertTag('h3')">
                      &lt;h3&gt;
                    </button>
                    <button type="button" class="btn btn-ghost btn-sm" style="padding:0.2rem 0.5rem;font-size:var(--text-xs);" onclick="insertTag('strong')">
                      &lt;b&gt;
                    </button>
                    <button type="button" class="btn btn-ghost btn-sm" style="padding:0.2rem 0.5rem;font-size:var(--text-xs);" onclick="insertTag('ul')">
                      &lt;ul&gt;
                    </button>
                  </div>
                </div>
                <textarea id="kontenBerita" name="konten"
                          style="width:100%;min-height:300px;border:1.5px solid var(--gray-200);border-radius:var(--radius);font-family:var(--font-sans);font-size:var(--text-sm);line-height:1.7;padding:1rem;resize:vertical;"
                          placeholder="Tuliskan isi berita atau pengumuman secara detail di sini… (Mendukung paragraf HTML seperti <p>, <h3>, <ul>, <li>)"
                          required><?= e($berita['konten']) ?></textarea>
              </div>

            </div>
          </div>
        </div>

        <!-- Right: Settings & Thumbnail -->
        <div>

          <!-- Publishing Settings Card -->
          <div class="admin-card">
            <div class="admin-card-header">
              <h2 style="font-size:var(--text-base);font-weight:700;margin:0;color:var(--gray-900);">Pengaturan Publikasi</h2>
            </div>
            <div class="admin-card-body">

              <label style="display:flex;align-items:flex-start;gap:0.75rem;cursor:pointer;margin-bottom:1.25rem;">
                <input type="checkbox" name="is_published" value="1" <?= $berita['is_published'] ? 'checked' : '' ?>
                       style="width:18px;height:18px;accent-color:var(--accent);margin-top:2px;">
                <div>
                  <div style="font-weight:700;color:var(--gray-900);font-size:var(--text-sm);">Tayangkan Berita (Publik)</div>
                  <div style="font-size:var(--text-xs);color:var(--gray-500);margin-top:0.2rem;">
                    Bila tidak dicentang, berita tersimpan sebagai draf dan tidak akan tampil untuk umum.
                  </div>
                </div>
              </label>

              <!-- Kategori -->
              <div class="form-group" style="margin-bottom:1.25rem;">
                <label class="form-label" for="kategoriBerita">Kategori Berita</label>
                <select id="kategoriBerita" name="kategori" class="form-select" onchange="toggleCustomKategori(this.value)">
                  <?php foreach ($standardCategories as $cat): ?>
                  <option value="<?= e($cat) ?>" <?= $berita['kategori'] === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                  <?php endforeach; ?>
                  <?php if (!in_array($berita['kategori'], $standardCategories) && !empty($berita['kategori'])): ?>
                  <option value="<?= e($berita['kategori']) ?>" selected><?= e($berita['kategori']) ?></option>
                  <?php endif; ?>
                  <option value="__baru__">+ Tambah Kategori Baru…</option>
                </select>
                <div id="kategoriBaruWrap" style="display:none;margin-top:0.5rem;">
                  <input type="text" name="kategori_baru" class="form-input" placeholder="Nama kategori baru…">
                </div>
              </div>

              <!-- Tanggal Publikasi -->
              <div class="form-group" style="margin-bottom:1.5rem;">
                <label class="form-label" for="publishedAt">Tanggal Publikasi</label>
                <input type="datetime-local" id="publishedAt" name="published_at" class="form-input"
                       value="<?= date('Y-m-d\TH:i', strtotime($berita['published_at'])) ?>">
              </div>

              <!-- Action buttons -->
              <div style="padding-top:1.25rem;border-top:1px solid var(--gray-150);display:flex;flex-direction:column;gap:0.75rem;">
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;" id="btnSimpanBerita">
                  <i data-lucide="check" style="width:16px;height:16px"></i>
                  <?= $isEdit ? 'Simpan Perubahan Berita' : 'Terbitkan Berita' ?>
                </button>
                <a href="berita.php" class="btn btn-ghost" style="width:100%;justify-content:center;">
                  Batal
                </a>
              </div>

            </div>
          </div>

          <!-- Thumbnail Image Card -->
          <div class="admin-card">
            <div class="admin-card-header">
              <h2 style="font-size:var(--text-base);font-weight:700;margin:0;color:var(--gray-900);">Gambar Thumbnail</h2>
            </div>
            <div class="admin-card-body" style="text-align:center;">

              <div style="margin-bottom:1.25rem;background:var(--gray-100);border-radius:var(--radius);overflow:hidden;border:1px solid var(--gray-200);">
                <img id="thumbPreview"
                     src="<?= beritaThumb($berita['thumbnail']) ?>"
                     alt="Preview thumbnail"
                     style="width:100%;height:160px;object-fit:cover;"
                     onerror="this.src='<?= SITE_URL ?>/assets/img/news-placeholder.svg'">
              </div>

              <div>
                <label for="thumbInput" class="btn btn-outline btn-sm" style="cursor:pointer;width:100%;justify-content:center;">
                  <i data-lucide="image" style="width:14px;height:14px"></i>
                  <?= !empty($berita['thumbnail']) ? 'Ganti Gambar' : 'Pilih Gambar' ?>
                </label>
                <input type="file" id="thumbInput" name="thumbnail" accept="image/jpeg,image/png,image/webp" style="display:none;">
                <p style="font-size:var(--text-xs);color:var(--gray-400);margin:0.5rem 0 0;">
                  Format: JPG, PNG, WebP. Maks 2MB.
                </p>
              </div>

              <?php if (!empty($berita['thumbnail'])): ?>
              <div style="margin-top:1rem;padding-top:0.75rem;border-top:1px dashed var(--gray-200);">
                <label style="display:inline-flex;align-items:center;gap:0.5rem;font-size:var(--text-xs);color:var(--danger);cursor:pointer;">
                  <input type="checkbox" name="hapus_thumb" value="1" id="hapusThumbCheck">
                  Hapus gambar saat ini
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
// Live Thumbnail Preview
document.getElementById('thumbInput')?.addEventListener('change', function() {
  const file = this.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('thumbPreview').src = e.target.result;
    };
    reader.readAsDataURL(file);
    const hapusCheck = document.getElementById('hapusThumbCheck');
    if (hapusCheck) hapusCheck.checked = false;
  }
});

// Auto slug generation from title if slug not manually customized
const judulInput = document.getElementById('judulBerita');
const slugInput  = document.getElementById('slugBerita');
let slugManuallyEdited = <?= $isEdit ? 'true' : 'false' ?>;

slugInput?.addEventListener('input', function() {
  slugManuallyEdited = true;
});

judulInput?.addEventListener('input', function() {
  if (!slugManuallyEdited) {
    const val = this.value.toLowerCase()
      .replace(/[^a-z0-9\s-]/g, '')
      .trim()
      .replace(/[\s-]+/g, '-');
    slugInput.value = val;
  }
});

function toggleCustomKategori(val) {
  const wrap = document.getElementById('kategoriBaruWrap');
  if (wrap) {
    wrap.style.display = (val === '__baru__') ? 'block' : 'none';
  }
}

// Quick insert helper
function insertTag(tag) {
  const textarea = document.getElementById('kontenBerita');
  if (!textarea) return;
  const start = textarea.selectionStart;
  const end = textarea.selectionEnd;
  const selected = textarea.value.substring(start, end);

  let replacement = '';
  if (tag === 'p') {
    replacement = '<p>' + (selected || 'Teks paragraf baru…') + '</p>\n';
  } else if (tag === 'h3') {
    replacement = '<h3>' + (selected || 'Judul Bagian') + '</h3>\n';
  } else if (tag === 'strong') {
    replacement = '<strong>' + (selected || 'Teks tebal') + '</strong>';
  } else if (tag === 'ul') {
    replacement = '<ul>\n  <li>' + (selected || 'Poin pertama') + '</li>\n  <li>Poin kedua</li>\n</ul>\n';
  }

  textarea.setRangeText(replacement, start, end, 'end');
  textarea.focus();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

