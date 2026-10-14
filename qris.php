<?php
require_once __DIR__ . '/includes/dummy.php';

$pageTitle = 'QRIS – InterActive QRIS';
$simpleTopbar = ['title' => 'QRIS', 'back' => 'report.php'];

// Lokasi hanya boleh dipilih dari daftar milik user yang login
$tokoId = (int)($_GET['toko_id'] ?? 0);
$toko = $DUMMY['toko_aktif'];
foreach ($DUMMY['toko'] as $item) {
  if ((int)$item['id'] === $tokoId) {
    $toko = $item;
    break;
  }
}

// TODO: NMID & isi QR statis belum punya sumber data. Contoh di bawah hanya
// dipakai saat tanpa lokasi (mode dummy); untuk merchant asli QR belum tampil.
$qris = empty($dbLocations)
  ? ['nmid' => 'ID1234567890123', 'payload' => 'CONTOH-QRIS-' . $toko['nama']]
  : ['nmid' => '', 'payload' => ''];

require_once __DIR__ . '/includes/header.php';
?>

<div class="content content--qris">

  <div class="qris-wrap fi">
    <div class="qris-card">
      <div class="qris-brand">InterActive <b>QRIS</b></div>

      <div class="qris-paper">
        <div class="qris-paper-head">
          <span class="qris-mark">QRIS</span>
          <span class="qris-mark qris-mark--gpn">GPN</span>
        </div>
        <div class="qris-name"><?= e($toko['nama']) ?></div>
        <?php if ($qris['payload'] !== ''): ?>
          <div class="qris-nmid">NMID : <?= e($qris['nmid']) ?></div>
          <div class="qris-code" id="qrisCode"></div>
        <?php else: ?>
          <div class="qris-empty">QRIS untuk lokasi ini belum tersedia.</div>
        <?php endif; ?>
      </div>

      <div class="qris-brand qris-brand--foot">InterActive</div>
    </div>
  </div>

  <div class="qris-foot">
    <button type="button" class="btn-red" id="btnUnduhQris" onclick="unduhQris()" <?= $qris['payload'] === '' ? 'disabled' : '' ?>>
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
        <polyline points="7 10 12 15 17 10" />
        <line x1="12" y1="15" x2="12" y2="3" />
      </svg>
      Unduh QRIS
    </button>
    <button type="button" class="btn-outline" id="btnBagikanQris" onclick="bagikanQris()" <?= $qris['payload'] === '' ? 'disabled' : '' ?>>
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round" stroke-linejoin="round">
        <circle cx="18" cy="5" r="3" />
        <circle cx="6" cy="12" r="3" />
        <circle cx="18" cy="19" r="3" />
        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" />
        <line x1="15.41" y1="6.51" x2="8.59" y2="10.49" />
      </svg>
      Bagikan QRIS
    </button>
  </div>

</div><!-- /content -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script src="assets/js/main.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
  const QRIS = <?= json_encode(['nama' => $toko['nama'], 'nmid' => $qris['nmid'], 'payload' => $qris['payload']], JSON_UNESCAPED_UNICODE) ?>;
  const QRIS_FILENAME = 'QRIS-' + QRIS.nama.replace(/[^a-z0-9]+/gi, '-') + '.png';

  const qrisEl = document.getElementById('qrisCode');
  if (qrisEl && QRIS.payload && typeof QRCode !== 'undefined') {
    new QRCode(qrisEl, { text: QRIS.payload, width: 480, height: 480, correctLevel: QRCode.CorrectLevel.M });
  }

  // Gambar yang diunduh/dibagikan: nama merchant + NMID + QR di atas latar putih
  function buildQrisImage() {
    const qr = qrisEl ? qrisEl.querySelector('canvas') : null;
    if (!qr) return null;

    const c = document.createElement('canvas');
    c.width = 600;
    c.height = 720;
    const ctx = c.getContext('2d');
    ctx.fillStyle = '#fff';
    ctx.fillRect(0, 0, c.width, c.height);
    ctx.fillStyle = '#111827';
    ctx.textAlign = 'center';
    ctx.font = '800 30px "Plus Jakarta Sans", sans-serif';
    ctx.fillText(QRIS.nama.toUpperCase(), 300, 80, 540);
    ctx.font = '500 22px "Plus Jakarta Sans", sans-serif';
    ctx.fillText('NMID : ' + QRIS.nmid, 300, 122, 540);
    ctx.drawImage(qr, 60, 170, 480, 480);
    return c;
  }

  // TODO: nama handler Flutter (downloadQris / shareQris) masih asumsi, sesuaikan dengan aplikasi
  function unduhQris() {
    const c = buildQrisImage();
    if (!c) return toast('QRIS belum tersedia.', true);
    const dataUrl = c.toDataURL('image/png');

    if (window.flutter_inappwebview) {
      window.flutter_inappwebview.callHandler('downloadQris', { filename: QRIS_FILENAME, dataUrl });
      return;
    }
    const a = document.createElement('a');
    a.href = dataUrl;
    a.download = QRIS_FILENAME;
    a.click();
  }

  function bagikanQris() {
    const c = buildQrisImage();
    if (!c) return toast('QRIS belum tersedia.', true);

    if (window.flutter_inappwebview) {
      window.flutter_inappwebview.callHandler('shareQris', { filename: QRIS_FILENAME, dataUrl: c.toDataURL('image/png') });
      return;
    }
    c.toBlob(blob => {
      const file = new File([blob], QRIS_FILENAME, { type: 'image/png' });
      if (navigator.canShare && navigator.canShare({ files: [file] })) {
        navigator.share({ files: [file], title: 'QRIS ' + QRIS.nama }).catch(() => {});
      } else {
        toast('Fitur bagikan tidak didukung di perangkat ini.', true);
      }
    });
  }
</script>
