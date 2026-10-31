// ============================================================
// Alumni Filter — AJAX with Debounce
// ============================================================

(function () {
  'use strict';

  const grid      = document.getElementById('alumniGrid');
  const countEl   = document.getElementById('resultsCount');
  const paginEl   = document.getElementById('paginationWrap');
  const loadingEl = document.getElementById('alumniLoading');
  const emptyEl   = document.getElementById('alumniEmpty');

  const inputNama    = document.getElementById('filterNama');
  const inputUrutan  = document.getElementById('filterUrutan');
  const selJurusan   = document.getElementById('filterJurusan');
  const selTahun     = document.getElementById('filterTahun');
  const btnReset     = document.getElementById('btnResetFilter');

  let currentPage = 1;
  let debounceTimer = null;

  function getParams(page) {
    const params = new URLSearchParams();
    if (inputNama?.value.trim())   params.set('nama',    inputNama.value.trim());
    if (inputUrutan?.value.trim()) params.set('urutan',  inputUrutan.value.trim());
    if (selJurusan?.value)         params.set('jurusan', selJurusan.value);
    if (selTahun?.value)           params.set('tahun',   selTahun.value);
    params.set('page', page || 1);
    return params;
  }

  function setLoading(on) {
    if (loadingEl) loadingEl.style.display = on ? 'flex' : 'none';
    if (grid)      grid.style.opacity      = on ? '0.4' : '1';
  }

  function fetchAlumni(page) {
    currentPage = page || 1;
    setLoading(true);
    if (emptyEl) emptyEl.style.display = 'none';

    const params = getParams(currentPage);

    fetch('/ppkd/api/alumni.php?' + params.toString())
      .then(r => r.json())
      .then(data => {
        setLoading(false);
        renderGrid(data.alumni);
        renderPagination(data.pagination);
        if (countEl) {
          countEl.innerHTML = `Menampilkan <strong>${data.pagination.total}</strong> alumni`;
        }
        // Re-init Lucide icons in new content
        if (window.lucide) lucide.createIcons();
      })
      .catch(() => {
        setLoading(false);
      });
  }

  function renderGrid(alumni) {
    if (!grid) return;
    if (!alumni || alumni.length === 0) {
      grid.innerHTML = '';
      if (emptyEl) emptyEl.style.display = 'block';
      return;
    }
    grid.innerHTML = alumni.map(a => `
      <a href="/ppkd/alumni-detail.php?id=${a.id}" class="dir-alumni-card" id="alumni-card-${a.id}">
        <div class="dir-avatar-wrap">
          <img src="${a.foto_url}" alt="${escHtml(a.nama_lengkap)}"
               class="dir-avatar"
               onerror="this.src='/ppkd/assets/img/avatar-placeholder.svg'">
          <span class="dir-number-badge">#${a.nomor_urut}</span>
        </div>
        <div class="dir-alumni-name">${escHtml(a.nama_lengkap)}</div>
        <div class="dir-alumni-jurusan">${escHtml(a.nama_jurusan)}</div>
        <div class="dir-alumni-year">Lulus ${a.tahun_lulus}</div>
      </a>
    `).join('');
  }

  function renderPagination(p) {
    if (!paginEl) return;
    if (!p || p.total_pages <= 1) { paginEl.innerHTML = ''; return; }

    let html = '<nav class="pagination-nav"><ul class="pagination">';

    if (p.current > 1) {
      html += `<li class="page-item"><a class="page-link page-btn" href="#" data-page="${p.current - 1}"><i data-lucide="chevron-left" style="width:16px;height:16px"></i></a></li>`;
    }

    for (let i = 1; i <= p.total_pages; i++) {
      if (i === 1 || i === p.total_pages || Math.abs(i - p.current) <= 2) {
        html += `<li class="page-item${i === p.current ? ' active' : ''}"><a class="page-link page-btn" href="#" data-page="${i}">${i}</a></li>`;
      } else if (Math.abs(i - p.current) === 3) {
        html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
      }
    }

    if (p.current < p.total_pages) {
      html += `<li class="page-item"><a class="page-link page-btn" href="#" data-page="${p.current + 1}"><i data-lucide="chevron-right" style="width:16px;height:16px"></i></a></li>`;
    }

    html += '</ul></nav>';
    paginEl.innerHTML = html;

    paginEl.querySelectorAll('.page-btn').forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        fetchAlumni(parseInt(this.dataset.page));
        const section = document.getElementById('directorySection');
        if (section) {
          window.scrollTo({ top: section.offsetTop - 90, behavior: 'smooth' });
        }
      });
    });
  }

  function escHtml(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str || ''));
    return d.innerHTML;
  }

  function onFilterChange() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchAlumni(1), 350);
  }

  // Attach events
  if (inputNama)   inputNama.addEventListener('input', onFilterChange);
  if (inputUrutan) inputUrutan.addEventListener('input', onFilterChange);
  if (selJurusan)  selJurusan.addEventListener('change', onFilterChange);
  if (selTahun)    selTahun.addEventListener('change', onFilterChange);

  if (btnReset) {
    btnReset.addEventListener('click', function () {
      if (inputNama)   inputNama.value = '';
      if (inputUrutan) inputUrutan.value = '';
      if (selJurusan)  selJurusan.value = '';
      if (selTahun)    selTahun.value = '';
      fetchAlumni(1);
    });
  }

  // Initial load
  if (grid) fetchAlumni(1);

})();
