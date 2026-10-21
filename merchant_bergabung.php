<?php
require_once __DIR__ . '/includes/dummy.php';

$referal = getReferalStatus($dbLocations);
if (!$referal['joined']) {
  header('Location: referal.php');
  exit;
}

$pageTitle = 'Merchant Bergabung – InterActive QRIS';
$simpleTopbar = ['title' => 'Merchant Bergabung', 'back' => 'referal.php'];

$bulanPanjang = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$fmtBulan = static fn(string $ym) => $bulanPanjang[(int)substr($ym, 5, 2) - 1] . ' ' . substr($ym, 0, 4);

// Pilihan bulan: 12 bulan terakhir
$bulanOpts = [];
for ($i = 0; $i < 12; $i++) {
  $bulanOpts[] = date('Y-m', strtotime(date('Y-m-01') . " -$i month"));
}
$bulan = in_array($_GET['bulan'] ?? '', $bulanOpts, true) ? $_GET['bulan'] : $bulanOpts[0];

$merchants = getReferalMerchants($dbLocations, $bulan);

require_once __DIR__ . '/includes/header.php';
?>

<div class="content content--settlement">

  <div class="mbg-head fi">
    <div class="ref-title">Daftar Merchant Bergabung</div>
    <div class="mbg-sub">Anda akan mendapatkan bonus dari orang yang mendaftar QRIS hingga sukses melunasi biaya registrasi melalui link Anda</div>
    <button type="button" class="mbg-month" onclick="openOv('ovMbgBulan')">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
        stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="5" width="18" height="16" rx="3" />
        <line x1="3" y1="10" x2="21" y2="10" />
        <line x1="8" y1="3" x2="8" y2="7" />
        <line x1="16" y1="3" x2="16" y2="7" />
      </svg>
      <span><?= e($fmtBulan($bulan)) ?></span>
      <svg class="mbg-month-chev" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="6 9 12 15 18 9" />
      </svg>
    </button>
  </div>

  <div class="stl-list fi">
    <?php if (empty($merchants)): ?>
      <div class="stl-empty">
        <?php require __DIR__ . '/includes/empty_illustration.php'; ?>
        <div class="stl-empty-title">Belum Ada Merchant Bergabung</div>
        <div class="stl-empty-text">Bagikan link referal Anda ke rekan bisnis untuk mulai mendapatkan bonus</div>
        <a href="referal.php#bagikan" class="btn-red mbg-share">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <circle cx="18" cy="5" r="3" /><circle cx="6" cy="12" r="3" /><circle cx="18" cy="19" r="3" />
            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" /><line x1="15.41" y1="6.51" x2="8.59" y2="10.49" />
          </svg>
          Bagikan Link Referal
        </a>
      </div>
    <?php else: ?>
      <?php foreach ($merchants as $m): ?>
        <div class="mbg-item">
          <div class="mbg-avatar">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
              stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="8" r="4" />
              <path d="M4 21a8 8 0 0 1 16 0" />
            </svg>
          </div>
          <div>
            <div class="mbg-name"><?= e($m['nama']) ?> <span>/ <?= e($m['hp']) ?></span></div>
            <div class="mbg-date">Tanggal Bergabung : <?= e(fmtTglId($m['tanggal'])) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div><!-- /content -->

<div class="overlay" id="ovMbgBulan" onclick="closeOv(event,'ovMbgBulan')">
  <div class="sheet">
    <div class="handle"></div>
    <div class="sheet-title">Pilih Bulan</div>
    <?php foreach ($bulanOpts as $ym): ?>
      <div class="p-opt" onclick="window.location.search = '?bulan=<?= e($ym) ?>'">
        <div class="p-opt-name"><?= e($fmtBulan($ym)) ?></div>
        <div class="p-radio <?= $ym === $bulan ? 'checked' : '' ?>"></div>
      </div>
    <?php endforeach; ?>
    <div style="height:16px"></div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script src="assets/js/main.js"></script>
