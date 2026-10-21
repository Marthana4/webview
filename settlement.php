<?php
require_once __DIR__ . '/includes/dummy.php';

$pageTitle = 'Riwayat Settlement – InterActive QRIS';
$simpleTopbar = ['title' => 'Riwayat Settlement', 'back' => 'report.php'];

$tokoId = (int)($_GET['toko_id'] ?? 0);

$isDate = static fn($v) => is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v);
$dateFrom = $isDate($_GET['from'] ?? null) ? $_GET['from'] : date('Y-m-01');
$dateTo = $isDate($_GET['to'] ?? null) ? $_GET['to'] : date('Y-m-t');
if ($dateFrom > $dateTo) {
  [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
}
$status = isset(SETTLEMENT_STATUS[$_GET['status'] ?? '']) ? $_GET['status'] : 'semua';

$all = getSettlements($dbLocations, $tokoId);

// TODO: saldo & "belum mencapai minimal" belum punya sumber data (lihat saldo_qris)
$isDummy = empty($dbLocations);
$saldo = $isDummy ? (int)($DUMMY['saldo_qris'] ?? 0) : null;
$belumMinimal = $isDummy ? 0 : null;
$antreanPencairan = array_sum(array_column(
  array_filter($all, static fn($r) => $r['status'] === 'proses'),
  'total'
));

$settlements = array_filter($all, static fn($r) =>
  $r['tanggal'] >= $dateFrom && $r['tanggal'] <= $dateTo
  && ($status === 'semua' || $r['status'] === $status));
usort($settlements, static fn($a, $b) => strcmp($b['tanggal'], $a['tanggal']));

$grouped = [];
foreach ($settlements as $row) {
  $grouped[$row['tanggal']][] = $row;
}

$statusOpts = ['semua' => 'Semua Status'] + array_map(static fn($s) => $s['label'], SETTLEMENT_STATUS);

require_once __DIR__ . '/includes/header.php';
?>

<div class="content content--settlement">

  <div class="stl-summary fi">
    <div class="stl-saldo">
      Saldo QRIS <b><?= $saldo === null ? '-' : fmtRp($saldo) ?></b>
      <span class="info-i" onclick="openOv('ovSaldoInfo')">!</span>
    </div>
    <div class="stl-boxes">
      <div class="stl-box">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--gray)" stroke-width="1.6"
          stroke-linecap="round" stroke-linejoin="round">
          <path d="M5 3h14v18l-3-2-2 2-2-2-2 2-2-2-3 2z" />
          <line x1="9" y1="8" x2="15" y2="8" />
          <line x1="9" y1="12" x2="15" y2="12" />
        </svg>
        <div class="stl-box-lbl">Dalam antrean pencairan</div>
        <div class="stl-box-val"><?= fmtRp($antreanPencairan) ?></div>
      </div>
      <div class="stl-box">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--gray)" stroke-width="1.6"
          stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="9" />
          <polyline points="12 7 12 12 15 14" />
        </svg>
        <div class="stl-box-lbl">Belum mencapai minimal pencairan</div>
        <div class="stl-box-val"><?= $belumMinimal === null ? '-' : fmtRp($belumMinimal) ?></div>
      </div>
    </div>
  </div>

  <div class="stl-filter fi">
    <button type="button" class="stl-pill" onclick="openOv('ovStlDate')">
      <?= e(fmtRangeId($dateFrom, $dateTo)) ?>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
        stroke-linecap="round" stroke-linejoin="round">
        <polyline points="6 9 12 15 18 9" />
      </svg>
    </button>
    <button type="button" class="stl-pill" onclick="openOv('ovStlStatus')">
      <?= e($statusOpts[$status]) ?>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
        stroke-linecap="round" stroke-linejoin="round">
        <polyline points="6 9 12 15 18 9" />
      </svg>
    </button>
  </div>

  <div class="stl-list fi">
    <?php if (empty($grouped)): ?>
      <div class="stl-empty">
        <?php require __DIR__ . '/includes/empty_illustration.php'; ?>
        <div class="stl-empty-title">Belum Ada Settlement</div>
        <div class="stl-empty-text">Settlement pada periode yang dipilih akan ditampilkan di sini.</div>
      </div>
    <?php else: ?>
      <?php foreach ($grouped as $tanggal => $rows): ?>
        <div class="stl-date"><?= e(fmtTglId($tanggal, true, true)) ?></div>
        <?php foreach ($rows as $row): $st = SETTLEMENT_STATUS[$row['status']]; ?>
          <a class="stl-item" href="settlement_detail.php?id=<?= urlencode($row['id']) ?>&toko_id=<?= $tokoId ?>">
            <div class="stl-item-head">
              <span>Bank <?= e($row['bank']) ?></span>
              <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><polygon points="8 4 18 12 8 20" /></svg>
            </div>
            <div class="stl-item-grid">
              <div>
                <div class="stl-item-lbl">Total Settlement</div>
                <div class="stl-item-total"><?= fmtRp($row['total']) ?></div>
              </div>
              <div class="stl-item-right">
                <div class="stl-item-lbl">Status</div>
                <div class="<?= $st['class'] ?>"><?= e($st['label']) ?></div>
              </div>
              <div>
                <div class="stl-item-lbl">ID Settlement</div>
                <div class="stl-item-val"><?= e($row['id']) ?></div>
              </div>
              <div class="stl-item-right">
                <div class="stl-item-lbl">Pemilik Rekening</div>
                <div class="stl-item-val"><?= e($row['pemilik_rekening']) ?></div>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div><!-- /content -->

<div class="overlay" id="ovSaldoInfo" onclick="closeOv(event,'ovSaldoInfo')">
  <div class="sheet">
    <div class="handle"></div>
    <div class="klaim-sheet-title">Saldo QRIS</div>
    <div class="klaim-body">
      Saldo QRIS adalah total dana transaksi QRIS Anda yang belum dicairkan (belum ter-settle) ke rekening.
    </div>
    <div style="padding:0 16px 16px">
      <button class="btn-red" onclick="closeOvById('ovSaldoInfo')">Mengerti</button>
    </div>
  </div>
</div>

<div class="overlay" id="ovStlDate" onclick="closeOv(event,'ovStlDate')">
  <div class="sheet">
    <div class="handle"></div>
    <div class="sheet-title">Pilih Periode</div>
    <div class="atur-row" style="padding:0 16px 4px">
      <div class="atur-box">
        <div class="atur-lbl">Dari</div>
        <input type="date" class="atur-input" id="stlDateFrom" value="<?= e($dateFrom) ?>">
      </div>
      <div class="atur-box">
        <div class="atur-lbl">Sampai</div>
        <input type="date" class="atur-input" id="stlDateTo" value="<?= e($dateTo) ?>">
      </div>
    </div>
    <div style="padding:16px">
      <button class="btn-red" onclick="applyStlDate()">Pilih</button>
    </div>
  </div>
</div>

<div class="overlay" id="ovStlStatus" onclick="closeOv(event,'ovStlStatus')">
  <div class="sheet">
    <div class="handle"></div>
    <div class="sheet-title">Pilih Status</div>
    <?php foreach ($statusOpts as $key => $label): ?>
      <div class="p-opt" onclick="reloadStl({ status: '<?= $key ?>' })">
        <div class="p-opt-name"><?= e($label) ?></div>
        <div class="p-radio <?= $key === $status ? 'checked' : '' ?>"></div>
      </div>
    <?php endforeach; ?>
    <div style="height:16px"></div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script src="assets/js/main.js"></script>
<script>
  // Filter dikirim ke server lewat query string; halaman dimuat ulang
  function reloadStl(changes) {
    const params = new URLSearchParams(window.location.search);
    params.set('toko_id', <?= $tokoId ?>);
    Object.entries(changes).forEach(([k, v]) => params.set(k, v));
    window.location.search = params.toString();
  }

  function applyStlDate() {
    const from = document.getElementById('stlDateFrom').value;
    const to = document.getElementById('stlDateTo').value;
    if (!from || !to) return toast('Tanggal awal dan akhir wajib diisi.', true);
    if (from > to) return toast('Tanggal awal harus sebelum tanggal akhir.', true);
    reloadStl({ from, to });
  }
</script>
