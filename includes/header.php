<?php
$base_url = "http://localhost/webview";
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title><?= e($pageTitle ?? 'InterActive QRIS') ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="assets/css/main.css">
</head>

<body>

  <!-- HEADER -->
  <div class="header">
    <header class="header-top">
      <div class="header-inner">

        <!-- Back Button -->
        <a href="javascript:history.back()" class="back-btn" aria-label="Back">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
            stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
        </a>

        <!-- Logo Center -->
        <div class="header-logo">
          <img src="<?= $base_url ?>/assets/images/logo.png" alt="Interactive QRIS Logo">
        </div>

        <!-- Empty right space (agar logo tetap center) -->
        <div class="header-right"></div>

      </div>
    </header>
    <div class="banner-red">
      <span class="banner-red-text">Dapatkan informasi lebih lengkap</span>
      <span class="banner-red-link">Kunjungi Dashboard</span>
    </div>
    <div class="tabs">
      <a href="report.php" class="tab <?= ($activeTab ?? '') === 'laporan' ? 'active' : '' ?>">Laporan</a>
      <a href="list.php" class="tab <?= ($activeTab ?? '') === 'pegawai' ? 'active' : '' ?>">Akun Pegawai</a>
    </div>
  </div>

  <div class="tip" id="tip"></div>
  <div class="toast" id="toast"></div>