<?php
require_once __DIR__ . '/includes/dummy.php';

$pageTitle = 'Akun Pegawai – InterActive QRIS';
$activeTab = 'pegawai';

$allToko = $DUMMY['toko'];
$pegawai = getPegawaiFromDb($WEBVIEW_AUTH_EMAIL, $dbLocations);
$hasEmployee = count(array_filter($pegawai, static fn($p) => $p['jabatan'] !== 'owner')) > 0;

// Ambil daftar lokasi unik dari pegawai untuk filter
$lokasiList = array_unique(array_column($pegawai, 'toko_nama'));
sort($lokasiList);

require_once __DIR__ . '/includes/header.php';
?>

<div class="content content--pegawai">

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

  <!-- EMPTY STATE: belum ada pegawai -->
  <?php if (!$hasEmployee): ?>
  <div class="empty-state fi" style="text-align:center;padding:24px 16px;color:var(--gray)">
    <p style="margin-bottom:12px">Anda belum memiliki akun pegawai. Buat akun pegawai untuk memberikan akses ke lokasi usaha Anda.</p>
  </div>
  <?php endif; ?>

  <!-- FOOTER BUTTON -->
  <div class="pegawai-foot fi">
    <a href="add_user.php" class="btn-red">Buat Akun Pegawai</a>
  </div>

</div><!-- /content -->

<!-- FILTER LOKASI SHEET (samain desain dgn ovLokasi di halaman Laporan) -->
<div class="overlay" id="ovLocFilter" onclick="closeOv(event,'ovLocFilter')">
  <div class="sheet">
    <div class="handle"></div>
    <div class="sheet-title">Pilih Lokasi</div>
    <div class="search-row">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--gray2)" stroke-width="2"
        stroke-linecap="round" stroke-linejoin="round">
        <circle cx="11" cy="11" r="8" />
        <line x1="21" y1="21" x2="16.65" y2="16.65" />
      </svg>
      <input type="text" placeholder="Cari lokasi..." id="locFilterSearch" oninput="filterLocFilterList(this.value)">
    </div>
    <div class="loc-list" id="locFilterList" style="padding-bottom:16px"></div>
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
  document.getElementById('locFilterSearch').value = '';
  renderLocFilterList('');
  openOv('ovLocFilter');
}

function renderLocFilterList(filter) {
  const list = document.getElementById('locFilterList');
  const f = (filter || '').toLowerCase();
  const filtered = LOKASI_LIST.filter(l => l.toLowerCase().includes(f));
  list.innerHTML = filtered.map(l => `
    <div class="loc-item" onclick="pickLocFilter('${l.replace(/'/g,"\\'")}')">
      <div class="loc-name" style="margin-bottom:0;color:${l === selLokasiFilter ? 'var(--red)' : 'var(--dark)'}">${l}</div>
      ${l === selLokasiFilter
        ? `<div class="loc-check">
             <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
               <polyline points="20 6 9 17 4 12"/>
             </svg>
           </div>`
        : ''}
    </div>`).join('');
}

function filterLocFilterList(val) {
  renderLocFilterList(val);
}

function pickLocFilter(val) {
  closeOvById('ovLocFilter');
  filterPegawai(val);
}
</script>
