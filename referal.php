<?php
require_once __DIR__ . '/includes/dummy.php';

$pageTitle = 'Referal – InterActive QRIS';
$activeTab = 'referal';

// TODO: isi dengan URL video yang sebenarnya
$referalVideoUrl = '';
$referalTermsUrl = 'syarat_ketentuan.php';

$caraMudah = [
  'Bagikan Link Referal Anda',
  'Ajak teman Anda daftar QRIS (hingga bayar) dengan link Tersebut',
  'Dapatkan Bonus Anda!',
];

$bonusCair = [
  'Minimal Pencairan Bonus Rp 50.000',
  'Dicairkan pada bulan berikutnya tanggal 15–20',
  'Total Bonus per bulan akan diakumulasikan di bulan berikutnya jika tidak memenuhi minimum pencairan bonus',
  'Dicairkan ke rekening sesuai yang terdaftar di QRIS Anda',
];

$referal = getReferalStatus($dbLocations);

require_once __DIR__ . '/includes/header.php';

if ($referal['joined']) {
  require __DIR__ . '/includes/referal_dashboard.php';
  return;
}
?>

<div class="content content--referal">

  <!-- HERO / VIDEO -->
  <div class="ref-section fi">
    <div class="ref-hero">
      <div class="ref-hero-title">Ajak Teman Pakai QRIS,<br>Bonus Jutaan Rupiah</div>
      <div class="ref-video" onclick="openReferalVideo()">
        <div class="ref-video-text">Referal<br>QRIS<br>InterActive</div>
        <div class="ref-play">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="white">
            <polygon points="7 4 21 12 7 20" />
          </svg>
        </div>
      </div>
    </div>
  </div>

  <!-- CARANYA MUDAH -->
  <div class="ref-section fi">
    <div class="ref-title">Caranya Mudah!</div>
    <?php foreach ($caraMudah as $i => $step): ?>
      <div class="ref-step">
        <div class="ref-step-no"><?= $i + 1 ?></div>
        <div class="ref-step-text"><?= e($step) ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- PERHITUNGAN BONUS -->
  <div class="ref-section fi">
    <div class="ref-title">Perhitungan Bonus</div>
    <ol class="ref-ol">
      <li>Bonus diperoleh dari setiap orang yang mendaftar dan membayar registrasi QRIS dari link referal Anda.</li>
      <li>Terdapat tingkatan bonus yang berlaku tiap satu bulan</li>
    </ol>

    <?php require __DIR__ . '/includes/referal_levels.php'; ?>

    <ol class="ref-ol" start="3">
      <li>Contoh perhitungan : Anton dalam bulan Juni berhasil mengajak 10 Orang</li>
      <li>Jumlah Merchant Bergabung : 18 Orang</li>
      <li>Level Anton: Bronze (karena kurang dari 20 orang)</li>
      <li>Nilai Bonus : Rp 3000 / pendaftar</li>
      <li>Perhitungan Total Bonus yang dicairkan = 18 Orang x 3.000 = Rp 54.000</li>
    </ol>
  </div>

  <!-- BONUS CAIR OTOMATIS -->
  <div class="ref-section ref-section--grow fi">
    <div class="ref-title ref-acc-head" onclick="toggleBonusCair()">
      Bonus Cair Otomatis
      <svg id="bonusCairIcon" class="ref-acc-icon open" width="18" height="18" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="6 9 12 15 18 9" />
      </svg>
    </div>
    <div id="bonusCairBody">
      <?php foreach ($bonusCair as $i => $step): ?>
        <div class="ref-step">
          <div class="ref-step-no"><?= $i + 1 ?></div>
          <div class="ref-step-text"><?= e($step) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- FOOTER: PERSETUJUAN + CTA -->
  <div class="referal-foot">
    <label class="ref-agree">
      <input type="checkbox" id="refAgree" onchange="toggleJoinBtn()">
      <span>Saya setuju dengan <a href="<?= e($referalTermsUrl ?: '#') ?>" onclick="return openReferalTerms(event)">syarat &amp; ketentuan</a> referal QRIS InterActive</span>
    </label>
    <button type="button" class="btn-red" id="btnJoinReferal" onclick="joinReferal()" disabled>Gabung Referal</button>
  </div>

</div><!-- /content -->

<!-- Animasi sukses setelah Gabung Referal -->
<div class="ref-success" id="refSuccess" aria-hidden="true">
  <img class="ref-success-anim" id="refSuccessAnim" alt="Berhasil bergabung">
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script src="assets/js/main.js"></script>
<script>
  const REFERAL_VIDEO_URL = <?= json_encode($referalVideoUrl) ?>;
  const REFERAL_TERMS_URL = <?= json_encode($referalTermsUrl) ?>;

  function toggleBonusCair() {
    const body = document.getElementById('bonusCairBody');
    const isOpen = body.style.display !== 'none';
    body.style.display = isOpen ? 'none' : '';
    document.getElementById('bonusCairIcon').classList.toggle('open', !isOpen);
  }

  function toggleJoinBtn() {
    document.getElementById('btnJoinReferal').disabled = !document.getElementById('refAgree').checked;
  }

  function openReferalVideo() {
    if (!REFERAL_VIDEO_URL) return toast('Video belum tersedia.', true);
    window.open(REFERAL_VIDEO_URL, '_blank');
  }

  function openReferalTerms(e) {
    if (REFERAL_TERMS_URL) return true;
    e.preventDefault();
    toast('Syarat & ketentuan belum tersedia.', true);
    return false;
  }

  async function joinReferal() {
    if (!document.getElementById('refAgree').checked) return;
    const btn = document.getElementById('btnJoinReferal');
    btn.disabled = true;
    try {
      const res = await fetch('referal_join.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });
      const data = await res.json();
      if (!res.ok || !data.success) throw new Error(data.message || 'Gagal bergabung.');
      showJoinSuccess();
    } catch (err) {
      toast(err.message || 'Gagal bergabung.', true);
      btn.disabled = false;
    }
  }

  // Durasi satu putaran referal-sukses.gif (GIF-nya berulang, jadi overlay
  // ditutup tepat sebelum putaran kedua dimulai)
  const JOIN_ANIM_MS = 1500;

  function showJoinSuccess() {
    const img = document.getElementById('refSuccessAnim');
    // Query string baru memaksa GIF mulai lagi dari frame pertama
    img.src = 'assets/images/referal-sukses.gif?t=' + Date.now();
    document.getElementById('refSuccess').classList.add('on');
    setTimeout(onJoinSuccessDone, JOIN_ANIM_MS);
  }

  // Muat ulang supaya server menampilkan dashboard referal aktif
  function onJoinSuccessDone() {
    window.location.reload();
  }
</script>
