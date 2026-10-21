<?php
require_once __DIR__ . '/includes/dummy.php';

$tokoId = (int)($_GET['toko_id'] ?? 0);
$row = findSettlement($dbLocations, (string)($_GET['id'] ?? ''), $tokoId);

$pageTitle = 'Detail Settlement – InterActive QRIS';
$simpleTopbar = ['title' => 'Detail Settlement', 'back' => 'settlement.php?toko_id=' . $tokoId];

$notes = [
  'proses' => 'Dana akan cair ke rekening terdaftar maksimal 2x24 jam dari tanggal settlement.',
  'berhasil' => 'Dana berhasil terkirim ke rekening terdaftar. Nominal telah dikurangi biaya settlement, MDR, dan SKN (jika ada).',
  'gagal' => 'Pencairan dana gagal. Silakan hubungi tim InterActive QRIS untuk bantuan.',
];

// Potongan ditampilkan dengan tanda minus, termasuk saat nilainya 0
$minus = static fn(int $v) => '–' . str_replace(' ', '', fmtRp($v));
$rp = static fn(int $v) => str_replace(' ', '', fmtRp($v));

require_once __DIR__ . '/includes/header.php';
?>

<div class="content content--settlement">
  <?php if (!$row): ?>
    <div class="stl-list">
      <div class="stl-empty">
        <div class="stl-empty-title">Settlement tidak ditemukan</div>
        <div class="stl-empty-text">Data settlement ini tidak tersedia atau bukan milik akun Anda.</div>
      </div>
    </div>
  <?php else: $st = SETTLEMENT_STATUS[$row['status']]; ?>

    <div class="stl-section fi">
      <div class="stl-card">
        <div class="stl-row"><span>Status</span><span class="<?= $st['class'] ?>"><?= e($st['label']) ?></span></div>
        <div class="stl-row"><span>Tanggal Settlement</span><span><?= e(fmtTglId($row['tanggal'])) ?></span></div>
        <div class="stl-row"><span>ID Settlement</span><span><?= e($row['id']) ?></span></div>
        <div class="stl-row"><span>Periode Transaksi</span><span><?= e(fmtRangeId($row['periode_dari'], $row['periode_sampai'])) ?></span></div>
      </div>
      <div class="stl-card">
        <div class="stl-row"><span>Total Settlement</span><b><?= $rp($row['total']) ?></b></div>
      </div>
    </div>

    <div class="stl-section fi">
      <div class="stl-section-title">Rincian Dana</div>
      <div class="stl-card">
        <div class="stl-row"><span>Total Transaksi</span><span><?= $rp($row['total_transaksi']) ?></span></div>
        <div class="stl-row"><span>Biaya Settlement</span><span><?= $minus($row['biaya_settlement']) ?></span></div>
        <div class="stl-row"><span>MDR</span><span><?= $minus($row['mdr']) ?></span></div>
        <div class="stl-row"><span>Biaya SKN</span><span><?= $minus($row['biaya_skn']) ?></span></div>
        <div class="stl-row"><span>Penyesuaian</span><span><?= $minus($row['penyesuaian']) ?></span></div>
      </div>
    </div>

    <div class="stl-section stl-section--grow fi">
      <div class="stl-section-title">Rekening Tujuan</div>
      <div class="stl-card">
        <div class="stl-row"><span>Rekening Tujuan</span><span><?= e($row['bank']) ?></span></div>
        <div class="stl-row"><span>Pemilik Rekening</span><span><?= e($row['pemilik_rekening']) ?></span></div>
      </div>
      <div class="stl-note stl-note--<?= e($row['status']) ?>"><?= e($notes[$row['status']]) ?></div>
    </div>

  <?php endif; ?>
</div><!-- /content -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script src="assets/js/main.js"></script>
