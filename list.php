<?php
require_once __DIR__ . '/includes/dummy.php';

$pageTitle = 'Akun Pegawai – InterActive QRIS';
$activeTab = 'pegawai';

$allToko = $DUMMY['toko'];
$pegawai = $DUMMY['pegawai'];

// Ambil daftar lokasi unik dari pegawai untuk filter
$lokasiList = array_unique(array_column($pegawai, 'toko_nama'));
sort($lokasiList);

require_once __DIR__ . '/includes/header.php';
?>

<div class="content">

  <!-- FILTER LOKASI -->
  <div class="pegawai-filter fi">
    <button class="lokasi-select" onclick="openLokasiFilter()">
      <span id="lokasiFilterLabel">Semua lokasi</span>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="6 9 12 15 18 9"/>
      </svg>
    </button>
  </div>

  <!-- LIST PEGAWAI -->
  <div class="pegawai-list" id="pegawaiList">
    <?php foreach ($pegawai as $i => $pg): ?>
    <div class="pegawai-item fi"
         style="animation-delay:<?= $i * 0.05 ?>s"
         data-toko="<?= e($pg['toko_nama']) ?>">
      <div>
        <div class="peg-name">
          <?= e($pg['nama']) ?>
          <?php if ($pg['jabatan'] === 'owner'): ?>
            <span class="peg-role">(Owner)</span>
          <?php endif; ?>
        </div>
        <div class="peg-addr"><?= e($pg['toko_alamat']) ?></div>
      </div>
      <div class="<?= $pg['status'] === 'aktif' ? 'badge-aktif' : 'badge-nonaktif' ?>">
        <?= $pg['status'] === 'aktif' ? 'Aktif' : 'Nonaktif' ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- FOOTER BUTTON -->
  <div class="pegawai-foot fi">
    <a href="add_user.php" class="btn-red">Buat Akun Pegawai</a>
  </div>

</div><!-- /content -->

<!-- FILTER LOKASI SHEET (dibuat dinamis JS) -->
<div class="overlay" id="ovLocFilter" onclick="closeOv(event,'ovLocFilter')">
  <div class="sheet">
    <div class="handle"></div>
    <div class="sheet-title">Pilih Lokasi</div>
    <div style="padding:0 16px 24px" id="locFilterList"></div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script src="assets/js/main.js"></script>
<script>
// ─── DATA ─────────────────────────────────────────
const ALL_TOKO      = <?= json_encode($allToko) ?>;
const LOKASI_LIST   = ['Semua lokasi', <?= implode(',', array_map(fn($l) => json_encode($l), $lokasiList)) ?>];
let   selLokasiFilter = 'Semua lokasi';

// ─── FILTER PEGAWAI ───────────────────────────────
function filterPegawai(val) {
  selLokasiFilter = val;
  document.getElementById('lokasiFilterLabel').textContent = val;
  document.querySelectorAll('.pegawai-item').forEach(el => {
    const toko = el.dataset.toko || '';
    el.style.display = (val === 'Semua lokasi' || toko === val) ? '' : 'none';
  });
}

// ─── LOKASI FILTER SHEET ──────────────────────────
function openLokasiFilter() {
  const list = document.getElementById('locFilterList');
  list.innerHTML = LOKASI_LIST.map(l => `
    <div onclick="pickLocFilter('${l.replace(/'/g,"\\'")}')"
         style="padding:12px 0;border-bottom:1px solid var(--border);
                display:flex;align-items:center;justify-content:space-between;
                cursor:pointer;font-size:14px;
                font-weight:${l === selLokasiFilter ? '700' : '500'};
                color:${l === selLokasiFilter ? 'var(--red)' : 'var(--dark)'}">
      ${l}
      ${l === selLokasiFilter
        ? `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
             <polyline points="20 6 9 17 4 12"/>
           </svg>`
        : ''}
    </div>`).join('');
  openOv('ovLocFilter');
}

function pickLocFilter(val) {
  closeOvById('ovLocFilter');
  filterPegawai(val);
}
</script>
