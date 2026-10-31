<?php
// ============================================================
// Global Helper Functions
// ============================================================
require_once __DIR__ . '/db.php';

// ---- String & Output Helpers --------------------------------

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function excerpt(string $text, int $limit = 150): string {
    $stripped = strip_tags($text);
    if (mb_strlen($stripped) <= $limit) return $stripped;
    return mb_substr($stripped, 0, $limit) . '…';
}

function slugify(string $text): string {
    $text = mb_strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function formatDate(string $date, string $format = 'd M Y'): string {
    static $months = [
        'Jan'=>'Jan','Feb'=>'Feb','Mar'=>'Mar','Apr'=>'Apr',
        'May'=>'Mei','Jun'=>'Jun','Jul'=>'Jul','Aug'=>'Agt',
        'Sep'=>'Sep','Oct'=>'Okt','Nov'=>'Nov','Dec'=>'Des',
    ];
    $formatted = date($format, strtotime($date));
    return str_replace(array_keys($months), array_values($months), $formatted);
}

function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'Baru saja';
    if ($diff < 3600)   return floor($diff/60) . ' menit lalu';
    if ($diff < 86400)  return floor($diff/3600) . ' jam lalu';
    if ($diff < 604800) return floor($diff/86400) . ' hari lalu';
    return formatDate($datetime);
}

// ---- Image Helpers ------------------------------------------

function alumniPhoto(string $foto = null, string $nama = ''): string {
    if ($foto && file_exists(UPLOAD_PATH . 'alumni/' . $foto)) {
        return UPLOAD_URL . 'alumni/' . e($foto);
    }
    return SITE_URL . '/assets/img/avatar-placeholder.svg';
}

function beritaThumb(string $thumb = null): string {
    if ($thumb && file_exists(UPLOAD_PATH . 'berita/' . $thumb)) {
        return UPLOAD_URL . 'berita/' . e($thumb);
    }
    return SITE_URL . '/assets/img/news-placeholder.svg';
}

function profilPhoto(string $foto = null): string {
    if ($foto && file_exists(UPLOAD_PATH . 'profil/' . $foto)) {
        return UPLOAD_URL . 'profil/' . e($foto);
    }
    return SITE_URL . '/assets/img/avatar-placeholder.svg';
}

// ---- Pagination ---------------------------------------------

function paginate(int $total, int $perPage, int $current, string $baseUrl): array {
    $totalPages = (int) ceil($total / $perPage);
    $current    = max(1, min($current, $totalPages));
    return [
        'total'       => $total,
        'per_page'    => $perPage,
        'current'     => $current,
        'total_pages' => $totalPages,
        'has_prev'    => $current > 1,
        'has_next'    => $current < $totalPages,
        'base_url'    => $baseUrl,
    ];
}

function paginationHtml(array $p): string {
    if ($p['total_pages'] <= 1) return '';
    $html = '<nav class="pagination-nav" aria-label="Navigasi halaman"><ul class="pagination">';
    // Prev
    if ($p['has_prev']) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $p['base_url'] . '&page=' . ($p['current']-1) . '" aria-label="Sebelumnya"><i data-lucide="chevron-left"></i></a></li>';
    }
    // Pages
    for ($i = 1; $i <= $p['total_pages']; $i++) {
        if ($i === 1 || $i === $p['total_pages'] || abs($i - $p['current']) <= 2) {
            $active = $i === $p['current'] ? ' active' : '';
            $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $p['base_url'] . '&page=' . $i . '">' . $i . '</a></li>';
        } elseif (abs($i - $p['current']) === 3) {
            $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
        }
    }
    // Next
    if ($p['has_next']) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $p['base_url'] . '&page=' . ($p['current']+1) . '" aria-label="Berikutnya"><i data-lucide="chevron-right"></i></a></li>';
    }
    $html .= '</ul></nav>';
    return $html;
}

// ---- Database Queries --------------------------------------

function getSiteStats(): array {
    $db = getDB();
    $alumni  = $db->query('SELECT COUNT(*) FROM alumni WHERE is_published=1')->fetchColumn();
    $jurusan = $db->query('SELECT COUNT(DISTINCT jurusan_id) FROM alumni WHERE is_published=1')->fetchColumn();
    $angkatan= $db->query('SELECT COUNT(DISTINCT tahun_lulus) FROM alumni WHERE is_published=1')->fetchColumn();
    $berita  = $db->query('SELECT COUNT(*) FROM berita WHERE is_published=1')->fetchColumn();
    return [
        'alumni'   => (int) $alumni,
        'jurusan'  => (int) $jurusan,
        'angkatan' => (int) $angkatan,
        'berita'   => (int) $berita,
    ];
}

function getAdminStats(): array {
    $db = getDB();
    $totalAlumni     = (int) $db->query('SELECT COUNT(*) FROM alumni')->fetchColumn();
    $publishedAlumni = (int) $db->query('SELECT COUNT(*) FROM alumni WHERE is_published=1')->fetchColumn();
    $totalBerita     = (int) $db->query('SELECT COUNT(*) FROM berita')->fetchColumn();
    $publishedBerita = (int) $db->query('SELECT COUNT(*) FROM berita WHERE is_published=1')->fetchColumn();
    $totalJurusan    = (int) $db->query('SELECT COUNT(*) FROM jurusan')->fetchColumn();
    $totalUsers      = (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $unreadPesan     = (int) $db->query('SELECT COUNT(*) FROM kontak_pesan WHERE is_read=0')->fetchColumn();
    return [
        'total_alumni'     => $totalAlumni,
        'published_alumni' => $publishedAlumni,
        'total_berita'     => $totalBerita,
        'published_berita' => $publishedBerita,
        'total_jurusan'    => $totalJurusan,
        'total_users'      => $totalUsers,
        'unread_pesan'     => $unreadPesan,
    ];
}

function getLatestAlumni(int $limit = 6): array {
    $db = getDB();
    $stmt = $db->prepare(
        'SELECT a.*, j.nama_jurusan FROM alumni a
         JOIN jurusan j ON a.jurusan_id = j.id
         WHERE a.is_published = 1
         ORDER BY a.created_at DESC
         LIMIT ?'
    );
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getLatestBerita(int $limit = 4): array {
    $db = getDB();
    $stmt = $db->prepare(
        'SELECT * FROM berita
         WHERE is_published = 1
         ORDER BY published_at DESC
         LIMIT ?'
    );
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getAllJurusan(): array {
    $db = getDB();
    return $db->query('SELECT * FROM jurusan ORDER BY nama_jurusan')->fetchAll();
}

function getAllTahunLulus(): array {
    $db = getDB();
    return $db->query(
        'SELECT DISTINCT tahun_lulus FROM alumni WHERE is_published=1 ORDER BY tahun_lulus DESC'
    )->fetchAll(PDO::FETCH_COLUMN);
}

function setFlash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function flashHtml(): string {
    $flash = getFlash();
    if (!$flash) return '';
    $icon = $flash['type'] === 'success' ? 'check-circle' : 'alert-circle';
    return '<div class="alert alert-' . e($flash['type']) . ' alert-dismissible fade show" role="alert">
        <i data-lucide="' . $icon . '"></i> ' . e($flash['msg']) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
}
