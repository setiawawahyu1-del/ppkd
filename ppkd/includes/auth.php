<?php
// ============================================================
// Auth Helpers
// ============================================================
require_once __DIR__ . '/config.php';

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'id'          => $_SESSION['user_id'],
        'nama'        => $_SESSION['user_nama'],
        'email'       => $_SESSION['user_email'],
        'role'        => $_SESSION['user_role'],
        'foto'        => $_SESSION['user_foto'] ?? null,
        'is_verified' => $_SESSION['user_verified'] ?? 0,
    ];
}

function isAdmin(): bool {
    return isLoggedIn() && ($_SESSION['user_role'] === 'admin');
}

function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        if (function_exists('setFlash')) {
            setFlash('danger', 'Akses ditolak. Fitur ini hanya dapat diakses oleh Administrator.');
        }
        header('Location: ' . SITE_URL . '/dashboard/');
        exit;
    }
}

function requireLogin(string $redirect = '/ppkd/login.php'): void {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function loginUser(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id']       = $user['id'];
    $_SESSION['user_nama']     = $user['nama'];
    $_SESSION['user_email']    = $user['email'];
    $_SESSION['user_role']     = $user['role'];
    $_SESSION['user_foto']     = $user['foto'];
    $_SESSION['user_verified'] = $user['is_verified'];
}

function logoutUser(): void {
    session_unset();
    session_destroy();
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
