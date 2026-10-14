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
  <?php
  $cssPath = __DIR__ . '/../assets/css/main.css';
  $cssVer = is_file($cssPath) ? filemtime($cssPath) : time();
  ?>
  <link rel="stylesheet" href="assets/css/main.css?v=<?= $cssVer ?>">
</head>

<body>

  <!-- HEADER -->
  <?php if (!empty($simpleTopbar)): ?>
    <!-- Topbar simple: back-arrow + judul (dipakai halaman form spt Buat Akun Pegawai) -->
    <div class="header">
      <div class="form-header">
        <a href="<?= e($simpleTopbar['back'] ?? 'javascript:history.back()') ?>" class="back-btn" style="position:static">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6" />
          </svg>
        </a>
        <span class="form-header-title"><?= e($simpleTopbar['title'] ?? '') ?></span>
      </div>
    </div>
  <?php else: ?>
    <div class="header">
      <!-- <header class="header-top"> -->
      <!-- <div class="header-inner"> -->

      <!-- Back Button -->
      <!-- <a href="javascript:history.back()" class="back-btn" aria-label="Back">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
            stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
        </a> -->

      <!-- Logo Center -->
      <!-- <div class="header-logo">
          <img src="https://storage.googleapis.com/storage_interactive/interactive/webview/QRIS-Logo.png" alt="Interactive QRIS Logo">
        </div> -->

      <!-- Empty right space (agar logo tetap center) -->
      <!-- <div class="header-right"></div> -->

      <!-- </div> -->
      <!-- </header> -->
      <div class="banner-red">
        <span class="banner-red-text">
          Dapatkan informasi lebih lengkap
        </span>

        <button type="button" class="banner-red-btn" onclick="openBackOfficeDashboard()">
          Kunjungi Dashboard
        </button>
      </div>

      <div class="tabs">
        <a href="report.php" class="tab <?= ($activeTab ?? '') === 'laporan' ? 'active' : '' ?>">Laporan</a>
        <a href="list.php" class="tab <?= ($activeTab ?? '') === 'pegawai' ? 'active' : '' ?>">Akun Pegawai</a>
        <a href="referal.php" class="tab <?= ($activeTab ?? '') === 'referal' ? 'active' : '' ?>">Referal</a>
      </div>
    </div>
  <?php endif; ?>

  <div class="tip" id="tip"></div>
  <div class="toast" id="toast"></div>

  <script>
    function openBackOfficeDashboard() {
      if (window.flutter_inappwebview) {
        window.flutter_inappwebview.callHandler('openBackOfficeDashboard');
      } else {
        // Fallback jika dibuka di browser biasa
        window.open(
          'https://merchant.qris.interactive.co.id/',
          '_blank'
        );
      }
    }
  </script>