<?php
// ============================================================
// Alumni Directory — alumni.php
// ============================================================
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'Direktori Alumni — ' . SITE_NAME;
$pageDescription = 'Cari dan temukan profil alumni PPKD dari berbagai angkatan dan program keahlian. Filter berdasarkan nama, jurusan, dan tahun kelulusan.';

// Fetch filter options for dropdowns
$allJurusan = getAllJurusan();
$allTahun   = getAllTahunLulus();

require_once __DIR__ . '/includes/header.php';
?>

<!-- ===== PAGE HEADER ===== -->
<div class="page-header" id="pageHeader">
  <div class="container">
    <div class="page-header-inner">
      <nav class="breadcrumb-nav" aria-label="Breadcrumb">
        <a href="<?= SITE_URL ?>">Beranda</a>
        <span class="sep"><i data-lucide="chevron-right" style="width:13px;height:13px"></i></span>
        <span class="current">Direktori Alumni</span>
      </nav>
      <h1>Direktori Alumni PPKD</h1>
      <p class="subtitle">Temukan dan terhubung dengan sesama alumni dari seluruh angkatan</p>
    </div>
  </div>
</div>

<!-- ===== DIRECTORY ===== -->
<section class="section bg-gray" id="directorySection" aria-labelledby="directoryTitle">
  <div class="container">

    <!-- Filter Bar -->
    <div class="filter-bar" id="filterBar" role="search" aria-label="Filter alumni">
      <div class="filter-grid">

        <div class="filter-field">
          <label for="filterNama">Cari Nama</label>
          <input type="text"
                 id="filterNama"
                 class="form-control"
                 placeholder="Cari nama alumni…"
                 autocomplete="off"
                 aria-label="Cari berdasarkan nama">
        </div>

        <div class="filter-field">
          <label for="filterUrutan">Nomor Urut</label>
          <input type="number"
                 id="filterUrutan"
                 class="form-control"
                 placeholder="Contoh: 15"
                 min="1"
                 aria-label="Cari berdasarkan nomor urut">
        </div>

        <div class="filter-field">
          <label for="filterJurusan">Program Keahlian</label>
          <select id="filterJurusan" class="form-select" aria-label="Filter jurusan">
            <option value="">Semua Jurusan</option>
            <?php foreach ($allJurusan as $j): ?>
            <option value="<?= $j['id'] ?>"><?= e($j['nama_jurusan']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="filter-field">
          <label for="filterTahun">Tahun Lulus</label>
          <select id="filterTahun" class="form-select" aria-label="Filter tahun kelulusan">
            <option value="">Semua Angkatan</option>
            <?php foreach ($allTahun as $t): ?>
            <option value="<?= e($t) ?>"><?= e($t) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="filter-actions">
          <button type="button" class="btn btn-ghost" id="btnResetFilter" aria-label="Reset semua filter" title="Reset filter">
            <i data-lucide="x" style="width:15px;height:15px"></i>
            Reset
          </button>
        </div>

      </div>
    </div>

    <!-- Results Meta -->
    <div class="results-meta" id="resultsMeta">
      <span class="results-count" id="resultsCount">Memuat data…</span>
    </div>

    <!-- Loading State -->
    <div id="alumniLoading" style="display:none;justify-content:center;padding:4rem;align-items:center;gap:0.75rem;color:var(--gray-400);">
      <div class="spinner-border spinner-border-sm" role="status" style="color:var(--accent)"></div>
      <span>Memuat data alumni…</span>
    </div>

    <!-- Alumni Grid -->
    <div class="dir-alumni-grid" id="alumniGrid" aria-live="polite" aria-label="Daftar alumni">
      <!-- Filled via AJAX -->
    </div>

    <!-- Empty State -->
    <div id="alumniEmpty" class="empty-state" style="display:none;" role="status">
      <div class="empty-state-icon">
        <i data-lucide="search-x" style="width:28px;height:28px"></i>
      </div>
      <h3>Alumni tidak ditemukan</h3>
      <p>Coba ubah filter pencarian atau gunakan kata kunci lain</p>
    </div>

    <!-- Pagination -->
    <div id="paginationWrap" aria-label="Navigasi halaman"></div>

  </div>
</section>

<!-- Alumni Filter Script -->
<script src="<?= SITE_URL ?>/assets/js/alumni-filter.js" defer></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
