<?php
// ============================================================
// API — Alumni Search/Filter (AJAX)
// ============================================================
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Sanitize input
$nama    = trim($_GET['nama']    ?? '');
$urutan  = trim($_GET['urutan']  ?? '');
$jurusan = (int) ($_GET['jurusan'] ?? 0);
$tahun   = (int) ($_GET['tahun']   ?? 0);
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = ALUMNI_PER_PAGE;
$offset  = ($page - 1) * $perPage;

$db = getDB();

// Build WHERE clauses
$where  = ['a.is_published = 1'];
$params = [];

if ($nama !== '') {
    $where[]  = 'a.nama_lengkap LIKE ?';
    $params[] = '%' . $nama . '%';
}

if ($urutan !== '') {
    $where[]  = 'a.nomor_urut = ?';
    $params[] = (int) $urutan;
}

if ($jurusan > 0) {
    $where[]  = 'a.jurusan_id = ?';
    $params[] = $jurusan;
}

if ($tahun > 0) {
    $where[]  = 'a.tahun_lulus = ?';
    $params[] = $tahun;
}

$whereClause = 'WHERE ' . implode(' AND ', $where);

// Count total
$countSql  = "SELECT COUNT(*) FROM alumni a JOIN jurusan j ON a.jurusan_id = j.id $whereClause";
$countStmt = $db->prepare($countSql);
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();

// Fetch data
$dataSql  = "SELECT a.id, a.nomor_urut, a.nama_lengkap, a.foto, a.tahun_lulus, j.nama_jurusan
             FROM alumni a
             JOIN jurusan j ON a.jurusan_id = j.id
             $whereClause
             ORDER BY a.nomor_urut ASC
             LIMIT ? OFFSET ?";
$dataStmt = $db->prepare($dataSql);
$dataStmt->execute(array_merge($params, [$perPage, $offset]));
$rows = $dataStmt->fetchAll();

// Add photo URL
foreach ($rows as &$row) {
    $file = $row['foto'];
    if ($file && file_exists(UPLOAD_PATH . 'alumni/' . $file)) {
        $row['foto_url'] = UPLOAD_URL . 'alumni/' . $file;
    } else {
        $row['foto_url'] = SITE_URL . '/assets/img/avatar-placeholder.svg';
    }
}
unset($row);

$totalPages = (int) ceil($total / $perPage);
$response = [
    'alumni' => $rows,
    'pagination' => [
        'total'       => $total,
        'per_page'    => $perPage,
        'current'     => $page,
        'total_pages' => $totalPages,
        'has_prev'    => $page > 1,
        'has_next'    => $page < $totalPages,
    ],
];

echo json_encode($response, JSON_UNESCAPED_UNICODE);
