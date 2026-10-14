<?php
require_once __DIR__ . '/includes/dummy.php';

$pageTitle = 'Referal – InterActive QRIS';
$activeTab = 'referal';

// TODO: isi dengan URL video & halaman syarat ketentuan yang sebenarnya
$referalVideoUrl = '';
$referalTermsUrl = '';

$caraMudah = [
  'Bagikan Link Referal Anda',
  'Ajak teman Anda daftar QRIS (hingga bayar) dengan link Tersebut',
  'Dapatkan Bonus Anda!',
];

$levels = [
  ['key' => 'bronze', 'nama' => 'Bronze Level', 'merchant' => '< 20 Merchant', 'bonus' => 3000],
  ['key' => 'gold', 'nama' => 'Gold Level', 'merchant' => '20–50 Merchant', 'bonus' => 4000],
  ['key' => 'super', 'nama' => 'Super Level', 'merchant' => '>50 Merchant', 'bonus' => 5000],
];

$bonusCair = [
  'Minimal Pencairan Bonus Rp 50.000',
  'Dicairkan pada bulan berikutnya tanggal 15–20',
  'Total Bonus per bulan akan diakumulasikan di bulan berikutnya jika tidak memenuhi minimum pencairan bonus',
  'Dicairkan ke rekening sesuai yang terdaftar di QRIS Anda',
];

require_once __DIR__ . '/includes/header.php';
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

    <div class="ref-levels">
      <?php foreach ($levels as $lv): ?>
        <div class="ref-level ref-level--<?= $lv['key'] ?>">
          <div class="ref-level-rail">
            <div class="ref-medal">
              <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
                <path d="M8 13l-2 9 6-3 6 3-2-9" fill="currentColor" opacity=".55" />
                <circle cx="12" cy="9" r="7" fill="currentColor" />
                <path d="M12 5.2l1.2 2.5 2.7.4-2 1.9.5 2.7-2.4-1.3-2.4 1.3.5-2.7-2-1.9 2.7-.4z" fill="white" />
              </svg>
            </div>
            <div class="ref-level-line"></div>
          </div>
          <div class="ref-level-card">
            <div class="ref-level-name"><?= e($lv['nama']) ?></div>
            <div class="ref-level-lbl">Merchant Bergabung</div>
            <div class="ref-level-val"><?= e($lv['merchant']) ?></div>
            <div class="ref-level-lbl">Nilai Bonus</div>
            <div class="ref-level-val"><span class="ref-level-bonus"><?= fmtRp($lv['bonus']) ?></span> / Merchant Bergabung</div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

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

  // TODO: sambungkan ke endpoint / handler pendaftaran referal
  function joinReferal() {
    if (!document.getElementById('refAgree').checked) return;
    toast('Pendaftaran referal belum tersedia.', true);
  }
</script>
