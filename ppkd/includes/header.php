<?php
// ============================================================
// Site Header — includes/header.php
// ============================================================
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$user = currentUser();

// Determine active page
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
function navActive(string $page, string $current): string {
    return $page === $current ? ' active' : '';
}

// Page-specific meta (set before including header)
$pageTitle       = $pageTitle       ?? SITE_NAME;
$pageDescription = $pageDescription ?? 'Platform informasi dan pusat pendataan alumni PPKD. Menghubungkan seluruh alumni, instruktur, dan pihak terkait dalam satu platform digital.';
$pageKeywords    = $pageKeywords    ?? 'alumni PPKD, ikatan alumni, direktori alumni, berita alumni';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= e($pageDescription) ?>">
  <meta name="keywords" content="<?= e($pageKeywords) ?>">
  <meta name="robots" content="index, follow">
  <meta property="og:title" content="<?= e($pageTitle) ?>">
  <meta property="og:description" content="<?= e($pageDescription) ?>">
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= SITE_URL ?>">
  <title><?= e($pageTitle) ?></title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Main CSS with cache buster -->
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/main.css?v=<?= time() ?>">

  <!-- Enforce horizontal navbar layout -->
  <style>
    .navbar-inner {
      display: flex !important;
      flex-direction: row !important;
      align-items: center !important;
      justify-content: space-between !important;
      height: 70px !important;
      gap: 1.5rem !important;
      flex-wrap: nowrap !important;
    }
    .navbar-nav,
    #mainNav {
      display: flex !important;
      flex-direction: row !important;
      align-items: center !important;
      gap: 0.35rem !important;
      list-style: none !important;
      margin: 0 !important;
      padding: 0 !important;
      flex-wrap: nowrap !important;
      flex-shrink: 0 !important;
    }
    .navbar-nav > li,
    #mainNav > li {
      display: inline-flex !important;
      align-items: center !important;
      list-style: none !important;
      margin: 0 !important;
      padding: 0 !important;
    }
    .navbar-nav .nav-link,
    #mainNav .nav-link {
      display: inline-flex !important;
      align-items: center !important;
      white-space: nowrap !important;
      padding: 0.5rem 0.75rem !important;
      font-size: 0.875rem !important;
    }
    @media (max-width: 768px) {
      .navbar-nav, #mainNav, .navbar-actions {
        display: none !important;
      }
      .navbar-toggler {
        display: flex !important;
      }
    }
  </style>

  <!-- Lucide Icons (loaded via JS at end of body) -->
</head>
<body class="<?= e($bodyClass ?? '') ?>">

<!-- ===== SITE HEADER ===== -->
<header class="site-header" id="siteHeader">
  <div class="container">
    <nav class="navbar-inner">

      <!-- Brand -->
      <a href="<?= SITE_URL ?>" class="navbar-brand" id="navBrand">
        <div class="brand-logo-wrap" title="Logo Ikatan Alumni PPKD">
          <img src="<?= SITE_URL ?>/assets/img/logo-placeholder.svg" alt="Logo PPKD" width="32" height="32">
        </div>
        <div class="brand-text">
          <span class="brand-name">Ikatan Alumni</span>
          <span class="brand-sub">PPKD</span>
        </div>
      </a>

      <!-- Desktop Nav -->
      <ul class="navbar-nav flex-row align-items-center flex-nowrap" id="mainNav">
        <li class="nav-item"><a class="nav-link<?= navActive('index', $currentPage) ?>" href="<?= SITE_URL ?>">Beranda</a></li>
        <li class="nav-item"><a class="nav-link<?= navActive('alumni', $currentPage) ?>" href="<?= SITE_URL ?>/alumni.php">Direktori Alumni</a></li>
        <li class="nav-item"><a class="nav-link<?= navActive('berita', $currentPage) ?>" href="<?= SITE_URL ?>/berita.php">Berita</a></li>
        <li class="nav-item"><a class="nav-link<?= navActive('tentang', $currentPage) ?>" href="<?= SITE_URL ?>/tentang.php">Tentang Kami</a></li>
        <li class="nav-item"><a class="nav-link<?= navActive('kontak', $currentPage) ?>" href="<?= SITE_URL ?>/kontak.php">Kontak</a></li>
      </ul>

      <!-- Desktop Actions -->
      <div class="navbar-actions">
        <?php if ($user): ?>
          <div class="user-dropdown dropdown">
            <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="userDropdownBtn">
              <img src="<?= $user['foto'] ? profilPhoto($user['foto']) : SITE_URL . '/assets/img/avatar-placeholder.svg' ?>"
                   alt="<?= e($user['nama']) ?>"
                   class="user-avatar">
              <span><?= e(explode(' ', $user['nama'])[0]) ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item" href="<?= SITE_URL ?>/dashboard/">
                  <i data-lucide="layout-dashboard" style="width:15px;height:15px"></i> Dashboard
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="<?= SITE_URL ?>/dashboard/profil.php">
                  <i data-lucide="user" style="width:15px;height:15px"></i> Profil Saya
                </a>
              </li>
              <?php if (isAdmin()): ?>
              <li><hr class="dropdown-divider"></li>
              <li class="dropdown-header" style="font-size:0.7rem;font-weight:700;color:var(--accent);text-transform:uppercase;letter-spacing:0.05em;padding:0.25rem 1rem;">
                Panel Admin
              </li>
              <li>
                <a class="dropdown-item" href="<?= SITE_URL ?>/dashboard/alumni.php">
                  <i data-lucide="users" style="width:15px;height:15px"></i> Kelola Alumni
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="<?= SITE_URL ?>/dashboard/berita.php">
                  <i data-lucide="newspaper" style="width:15px;height:15px"></i> Kelola Berita
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="<?= SITE_URL ?>/dashboard/pesan.php">
                  <i data-lucide="mail" style="width:15px;height:15px"></i> Pesan Masuk
                </a>
              </li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item text-danger" href="<?= SITE_URL ?>/logout.php">
                  <i data-lucide="log-out" style="width:15px;height:15px"></i> Keluar
                </a>
              </li>
            </ul>
          </div>
        <?php else: ?>
          <a href="<?= SITE_URL ?>/login.php" class="btn btn-nav-login" id="btnNavLogin">Masuk</a>
          <a href="<?= SITE_URL ?>/register.php" class="btn btn-nav-register" id="btnNavRegister">Daftar</a>
        <?php endif; ?>
      </div>

      <!-- Mobile Toggler -->
      <button class="navbar-toggler" id="navToggler" aria-label="Buka menu navigasi">
        <span class="toggler-bar"></span>
        <span class="toggler-bar"></span>
        <span class="toggler-bar"></span>
      </button>

    </nav>
  </div>
</header>

<!-- ===== MOBILE NAV ===== -->
<div class="mobile-nav" id="mobileNav" role="dialog" aria-label="Menu navigasi mobile">
  <button class="mobile-nav-close" id="mobileNavClose" aria-label="Tutup menu">
    <i data-lucide="x" style="width:18px;height:18px"></i>
  </button>

  <ul class="mobile-nav-links">
    <li><a href="<?= SITE_URL ?>">Beranda</a></li>
    <li><a href="<?= SITE_URL ?>/alumni.php">Direktori Alumni</a></li>
    <li><a href="<?= SITE_URL ?>/berita.php">Berita</a></li>
    <li><a href="<?= SITE_URL ?>/tentang.php">Tentang Kami</a></li>
    <li><a href="<?= SITE_URL ?>/kontak.php">Kontak</a></li>
  </ul>

  <div class="mobile-nav-actions">
    <?php if ($user): ?>
      <a href="<?= SITE_URL ?>/dashboard/" class="btn btn-outline-light" style="flex:1;justify-content:center">
        <i data-lucide="layout-dashboard" style="width:15px;height:15px"></i> Dashboard
      </a>
      <a href="<?= SITE_URL ?>/logout.php" class="btn btn-ghost" style="color:rgba(255,255,255,0.6)">
        Keluar
      </a>
    <?php else: ?>
      <a href="<?= SITE_URL ?>/login.php" class="btn btn-outline-light" style="flex:1;justify-content:center">Masuk</a>
      <a href="<?= SITE_URL ?>/register.php" class="btn btn-nav-register" style="flex:1;justify-content:center">Daftar</a>
    <?php endif; ?>
  </div>
</div>

<!-- Flash Message -->
<?php $flash = getFlash(); if ($flash): ?>
<div style="position:fixed;top:80px;right:1.5rem;z-index:9999;min-width:300px;max-width:400px;">
  <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible" role="alert">
    <?= e($flash['msg']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" style="padding:0.75rem 1rem;font-size:0.75rem;"></button>
  </div>
</div>
<?php endif; ?>

<!-- Main content starts after header -->
<main id="main-content">
