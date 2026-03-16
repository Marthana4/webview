<?php
require_once __DIR__ . '/includes/dummy.php';

$pageTitle = 'Laporan – InterActive QRIS';
$activeTab = 'laporan';

$toko = $DUMMY['toko_aktif'];
$allToko = $DUMMY['toko'];
$tx = $DUMMY['transaksi_hari_ini'];
$lap = $DUMMY['laporan'];
$klaim = $DUMMY['klaim'];
$chart = $DUMMY['chart'];
$jam = $DUMMY['jam'];
$period = 'kemarin'; // default tampilan

require_once __DIR__ . '/includes/header.php';
?>

<div class="content">

  <!-- ── STORE ROW ── -->
  <div class="store-row fi">
    <div>
      <div class="store-name" id="storeName"><?= e($toko['nama']) ?></div>
      <div class="store-addr" id="storeAddr">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
          <circle cx="12" cy="10" r="3" />
        </svg>
        <?= e($toko['alamat']) ?>
      </div>
    </div>
    <button class="ubah-btn" onclick="openOv('ovLokasi')">Ubah</button>
  </div>

  <!-- ── TRANSAKSI HARI INI ── -->
  <div class="tx-card fi">
    <div class="tx-label">Nilai transaksi hari ini</div>
    <div class="tx-val"><?= fmtRp($tx['total_nilai']) ?></div>
    <div class="tx-count">= <?= $tx['jumlah_transaksi'] ?> transaksi</div>
  </div>

  <!-- ── LAPORAN BULANAN ── -->
  <div class="laporan-wrap fi">
    <div class="laporan-head">
      <div>
        <div class="laporan-title">Laporan Bulanan</div>
        <div class="laporan-date"><?= e($lap['periode_str']) ?></div>
      </div>
      <button class="period-btn" id="periodBtn" onclick="openOv('ovPeriod')">
        Kemarin
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
          stroke-linecap="round" stroke-linejoin="round">
          <polyline points="6 9 12 15 18 9" />
        </svg>
      </button>
    </div>
    <div class="divider"></div>
    <div class="klaim-row">
      <div class="klaim-box" onclick="openOv('ovKlaim')">
        <div class="klaim-label">Klaim user <span class="info-i">i</span></div>
        <div class="klaim-val"><?= $klaim['user'] ?></div>
      </div>
      <div class="klaim-box">
        <div class="klaim-label">Klaim merchant <span class="info-i">i</span></div>
        <div class="klaim-val"><?= $klaim['merchant'] ?></div>
      </div>
    </div>
  </div>

  <!-- ── LINE CHART ── -->
  <div class="chart-card fi">
    <div class="chart-title">Grafik transaksi QRIS:</div>
    <div class="chart-outer">
      <div class="chart-y" id="chartY"></div>
      <div class="chart-inner">
        <canvas id="lineChart" height="140"></canvas>
        <div class="chart-x-lbl">Tanggal</div>
      </div>
    </div>
  </div><!-- /chart-card TUTUP DI SINI -->

  <!-- STAT CARDS -->
  <div class="stat-wrap">
    <?php
    $stats = [
      [
        'icon' => '<rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>',
        'lbl' => 'Total transaksi',
        'val' => number_format($lap['total_tx']),
        'pct' => $lap['pct_tx'],
      ],
      [
        'icon' => '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
        'lbl' => 'Rata rata nilai transaksi harian',
        'val' => fmtRp($lap['avg_harian']),
        'pct' => $lap['pct_avg'],
      ],
      [
        'icon' => '<rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>',
        'lbl' => 'Total saldo dicairkan',
        'val' => fmtRp($lap['saldo_cair']),
        'pct' => $lap['pct_saldo'],
      ],
    ];
    foreach ($stats as $s):
      $up = $s['pct'] >= 0;
      $badge = $up ? 'badge-up' : 'badge-down';
      $arrow = $up
        ? '<polyline points="18 15 12 9 6 15"/>'
        : '<polyline points="6 9 12 15 18 9"/>';
      ?>
      <div class="stat-item">
        <div class="stat-left">
          <div class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--gray)" stroke-width="1.8"
              stroke-linecap="round" stroke-linejoin="round">
              <?= $s['icon'] ?>
            </svg>
          </div>
          <div>
            <div class="stat-lbl"><?= e($s['lbl']) ?></div>
            <div class="stat-v"><?= e($s['val']) ?></div>
          </div>
        </div>
        <div class="<?= $badge ?>">
          <?= abs($s['pct']) ?>%
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round">
            <?= $arrow ?>
          </svg>
        </div>
      </div>
    <?php endforeach; ?>
  </div><!-- /stat-wrap -->

  <!-- ── BANNER KUNING ── -->
  <div class="bnr-yellow-wrap">
    <div class="bnr-yellow fi">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2"
        stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:2px">
        <circle cx="12" cy="12" r="10" />
        <line x1="12" y1="8" x2="12" y2="12" />
        <line x1="12" y1="16" x2="12.01" y2="16" />
      </svg>
      <span>Peningkatan atau penurunan omzet penjualan merupakan perbandingan data sebelumnya sesuai dengan filter yang
        dipilih</span>
    </div>
  </div>

  <!-- ── BAR CHART JAM ── -->
  <div class="bar-card fi">
    <div class="jam-title">Jam favorit: <b><?= e($jam['jam_fav']) ?></b></div>
    <div class="bar-wrap" id="barWrap"></div>
    <div class="dots">
      <?php for ($i = 0; $i < 5; $i++): ?>
        <div class="dot <?= $i === 0 ? 'on' : '' ?>" onclick="dotClick(<?= $i ?>)"></div>
      <?php endfor; ?>
    </div>
  </div>

</div><!-- /content -->

<!-- ════ SHEETS ════ -->

<!-- LOKASI SHEET -->
<div class="overlay" id="ovLokasi" onclick="closeOv(event,'ovLokasi')">
  <div class="sheet">
    <div class="handle"></div>
    <div
      style="text-align:center;font-size:11px;font-weight:700;color:var(--gray);letter-spacing:.5px;padding-bottom:4px">
      LOKASI</div>
    <div class="divider"></div>
    <div class="sheet-title" style="padding-top:14px">Pilih Lokasi</div>
    <div class="search-row">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--gray2)" stroke-width="2"
        stroke-linecap="round" stroke-linejoin="round">
        <circle cx="11" cy="11" r="8" />
        <line x1="21" y1="21" x2="16.65" y2="16.65" />
      </svg>
      <input type="text" placeholder="Cari lokasi..." id="locSearch" oninput="filterLoc(this.value)">
    </div>
    <div class="loc-list" id="locList"></div>
    <div class="sheet-foot">
      <button class="btn-red" onclick="closeOvById('ovLokasi')">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"
          stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="5" x2="12" y2="19" />
          <line x1="5" y1="12" x2="19" y2="12" />
        </svg>
        Tambah QRIS
      </button>
    </div>
  </div>
</div>

<!-- KLAIM SHEET -->
<div class="overlay" id="ovKlaim" onclick="closeOv(event,'ovKlaim')">
  <div class="sheet">
    <div class="handle"></div>
    <div class="klaim-sheet-title">Klaim User</div>
    <div class="klaim-body">
      Klaim User adalah permintaan dari pelanggan Anda untuk mengembalikan dana transaksinya karena suatu alasan.
      Anda perlu memberikan tanggapan hingga batas waktu. <a href="#">Pergi ke Dashboard</a>
    </div>
    <div class="klaim-bnr">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2"
        stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:2px">
        <circle cx="12" cy="12" r="10" />
        <line x1="12" y1="8" x2="12" y2="12" />
        <line x1="12" y1="16" x2="12.01" y2="16" />
      </svg>
      <span>Jika terlambat menanggapi, maka Anda dianggap menyetujui klaim user, dan dana QRIS Anda akan
        terpotong</span>
    </div>
    <div style="padding:0 16px">
      <button class="btn-red" onclick="closeOvById('ovKlaim')">Mengerti</button>
    </div>
  </div>
</div>

<!-- PERIOD SHEET -->
<div class="overlay" id="ovPeriod" onclick="closeOv(event,'ovPeriod')">
  <div class="sheet" style="padding-bottom:0">
    <div class="handle"></div>
    <div style="font-size:15px;font-weight:700;padding:4px 16px 12px;border-bottom:1px solid var(--border)">Filter Date
      Options</div>
    <div class="p-section-lbl">PILIH TANGGAL</div>

    <?php
    $periodOpts = [
      'kemarin' => 'Kemarin',
      'bulan' => 'Bulan Ini',
      '7' => '7 Hari terakhir',
      '30' => '30 Hari terakhir',
    ];
    foreach ($periodOpts as $key => $label):
      ?>
      <div class="p-opt" onclick="selPeriod('<?= $key ?>')">
        <div>
          <div class="p-opt-name"><?= $label ?></div>
          <div class="p-opt-date" id="plbl-<?= $key ?>">—</div>
        </div>
        <div class="p-radio <?= $period === $key ? 'checked' : '' ?>" id="pradio-<?= $key ?>"></div>
      </div>
    <?php endforeach; ?>

    <div class="p-opt" onclick="selPeriod('atur')" style="flex-direction:column;align-items:flex-start">
      <div style="display:flex;align-items:center;justify-content:space-between;width:100%">
        <div class="p-opt-name">Atur tanggal</div>
        <div class="p-radio" id="pradio-atur"></div>
      </div>
      <div id="aturInputs" class="atur-row" style="display:none">
        <div class="atur-box">
          <div class="atur-lbl">Dari</div>
          <input type="date" class="atur-input" id="dateFrom">
        </div>
        <div class="atur-box">
          <div class="atur-lbl">Sampai</div>
          <input type="date" class="atur-input" id="dateTo">
        </div>
      </div>
    </div>

    <div style="padding:16px">
      <button class="btn-red" onclick="applyPeriod()">Pilih</button>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<!-- SCRIPTS -->
<script src="assets/js/main.js"></script>
<script src="assets/js/chart.js"></script>
<script>
  // ─── DATA FROM PHP ────────────────────────────────
  const ALL_TOKO = <?= json_encode($allToko) ?>;
  let selToko = <?= (int) $toko['id'] ?>;
  let curPeriod = 'kemarin';

  const CHART_VALS = <?= json_encode($chart['values']) ?>;
  const CHART_LBLS = <?= json_encode($chart['labels']) ?>;
  const JAM_VALS = <?= json_encode($jam['values']) ?>;
  const JAM_LBLS = <?= json_encode($jam['labels']) ?>;

  // ─── LOKASI ───────────────────────────────────────
  function pickLoc(id, nama, alamat) {
    selToko = parseInt(id);
    document.getElementById('storeName').textContent = nama;
    document.getElementById('storeAddr').innerHTML =
      `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
     </svg>${alamat}`;
    renderLocs('');
    setTimeout(() => closeOvById('ovLokasi'), 300);
  }

  // ─── PERIOD ───────────────────────────────────────
  function selPeriod(v) {
    curPeriod = v;
    updateRadios(v);
  }

  function applyPeriod() {
    const btn = document.getElementById('periodBtn');
    btn.innerHTML = `${PERIOD_LBLS[curPeriod] || curPeriod}
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
      <polyline points="6 9 12 15 18 9"/>
    </svg>`;
    closeOvById('ovPeriod');
    // Dengan DB nyata: fetch data baru sesuai period
  }

  // ─── INIT ─────────────────────────────────────────
  initPeriodLabels('kemarin');
  drawLineChart(CHART_VALS, CHART_LBLS);
  drawBarChart(JAM_VALS, JAM_LBLS);
</script>