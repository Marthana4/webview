<?php
// Tampilan Referal setelah merchant bergabung. Butuh $referal dari getReferalStatus().
$bulanPanjang = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$bulanIni = $bulanPanjang[(int)date('n') - 1] . ' ' . date('Y');
$bonusLabel = 'Bonus ' . date('j') . ', ' . $bulanIni;

$levelKeys = array_keys(REFERAL_LEVELS);
$levelIdx = array_search($referal['level'], $levelKeys, true);
$nextKey = $levelKeys[$levelIdx + 1] ?? null;
$merchant = (int)$referal['merchant_bergabung'];
$progress = $referal['level'] === 'super' ? 100 : referalProgressPct($merchant);
$nextPct = $nextKey ? referalProgressPct(REFERAL_LEVELS[$nextKey]['min']) : null;
$kurang = $nextKey ? REFERAL_LEVELS[$nextKey]['min'] - $merchant : 0;
?>
<div class="content content--referal">

  <div class="rfd-hero fi">
    <div class="rfd-hero-lbl">
      <?= e($bonusLabel) ?>
      <span class="info-i" onclick="openOv('ovRfdBonus')">i</span>
    </div>
    <div class="rfd-hero-val"><?= fmtRp($referal['bonus']) ?></div>
    <?php if ($referal['bonus_cair'] > 0): ?>
      <div class="rfd-pill">+ <?= fmtRp($referal['bonus_cair']) ?>, bonus cair!</div>
    <?php endif; ?>
    <div class="rfd-hero-note">*Minimal pencairan bonus referal Rp 50.000. Bonus akan dicairkan otomatis pada bulan berikutnya (estimasi tanggal 15–20)</div>
    <button type="button" class="btn-red" onclick="openShareSheet()">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round" stroke-linejoin="round">
        <circle cx="18" cy="5" r="3" />
        <circle cx="6" cy="12" r="3" />
        <circle cx="18" cy="19" r="3" />
        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" />
        <line x1="15.41" y1="6.51" x2="8.59" y2="10.49" />
      </svg>
      Bagikan Link Referal
    </button>
  </div>

  <div class="ref-section fi">
    <div class="rfd-head">
      <div class="ref-title">Perolehan Bulan Ini</div>
      <div class="rfd-month"><?= e($bulanIni) ?></div>
    </div>
    <div class="rfd-stats">
      <div class="rfd-stat">
        <div class="rfd-stat-lbl">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 12a9 9 0 1 0-9 9" />
            <path d="M13 13l8 3-3.5 1.5L16 21z" />
          </svg>
          Total Klik
        </div>
        <div class="rfd-stat-val"><?= (int)$referal['total_klik'] ?></div>
      </div>
      <a class="rfd-stat rfd-stat--link" href="merchant_bergabung.php">
        <div class="rfd-stat-lbl">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            stroke-linecap="round" stroke-linejoin="round">
            <circle cx="9" cy="8" r="4" />
            <path d="M2 21a7 7 0 0 1 14 0" />
            <path d="M16 4a4 4 0 0 1 0 8M22 21a7 7 0 0 0-4-6.3" />
          </svg>
          Merchant Bergabung
        </div>
        <div class="rfd-stat-val">
          <?= $merchant ?>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 6 15 12 9 18" />
          </svg>
        </div>
      </a>
    </div>
  </div>

  <div class="ref-section fi">
    <div class="rfd-head">
      <div class="ref-title">Level Referal</div>
      <div class="rfd-month"><?= e($bulanIni) ?></div>
    </div>

    <div class="rfd-medals">
      <?php foreach ($levelKeys as $i => $key):
        $state = $i === $levelIdx ? 'current' : ($i < $levelIdx ? 'passed' : 'future'); ?>
        <div class="rfd-medal-item ref-level--<?= $key ?> rfd-medal--<?= $state ?>">
          <div class="ref-medal rfd-medal">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none">
              <path d="M8 13l-2 9 6-3 6 3-2-9" fill="currentColor" opacity=".55" />
              <circle cx="12" cy="9" r="7" fill="currentColor" />
              <path d="M12 5.2l1.2 2.5 2.7.4-2 1.9.5 2.7-2.4-1.3-2.4 1.3.5-2.7-2-1.9 2.7-.4z" fill="white" />
            </svg>
            <?php if ($state !== 'future'): ?>
              <span class="rfd-check">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3.5"
                  stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12" /></svg>
              </span>
            <?php endif; ?>
          </div>
          <div class="rfd-medal-name"><?= e(REFERAL_LEVELS[$key]['nama']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="rfd-bar ref-level--<?= $referal['level'] ?>">
      <div class="rfd-bar-fill" style="width:<?= max($progress, 4) ?>%"></div>
      <?php if ($nextKey): ?>
        <svg class="rfd-bar-star ref-level--<?= $nextKey ?>" style="left:<?= $nextPct ?>%" width="16" height="16" viewBox="0 0 24 24">
          <path d="M12 2l2.9 6.3 6.9.8-5.1 4.7 1.4 6.8L12 17.2l-6.1 3.4 1.4-6.8L2.2 9.1l6.9-.8z" fill="currentColor" />
        </svg>
      <?php endif; ?>
    </div>

    <?php if ($nextKey): ?>
      <div class="rfd-next"><?= $kurang ?> Merchant lagi menuju <?= e(REFERAL_LEVELS[$nextKey]['nama']) ?> Level</div>
    <?php else: ?>
      <div class="rfd-top">
        <div class="rfd-top-title">Tetap Cuan di Level Terpuncak!</div>
        <div class="rfd-top-text">Terus ajak yang lain daftar QRIS InterActive. <b>Dapatkan nilai bonus tertinggi</b> untuk setiap pendaftar yang Anda ajak.</div>
      </div>
    <?php endif; ?>

    <button type="button" class="btn-outline" onclick="openOv('ovRfdSkema')">Lihat Skema Level</button>
  </div>

  <div class="ref-section ref-section--grow fi">
    <div class="rfd-head">
      <div class="ref-title">Detail Bonus Referal</div>
      <button type="button" class="rfd-help" aria-label="Pencairan Bonus Referal" onclick="openOv('ovRfdBonus')">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
          stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 12a8 8 0 0 1-8 8H6l-3 2V12a8 8 0 0 1 8-8h2a8 8 0 0 1 8 8z" />
          <path d="M10 10a2 2 0 1 1 3 1.7c-.6.4-1 .8-1 1.5M12 16h.01" />
        </svg>
      </button>
    </div>

    <div class="rfd-detail" onclick="this.classList.toggle('open')">
      <div class="rfd-detail-main">
        <div>
          <div class="rfd-detail-lbl">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
              stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="9" />
              <path d="M15 9a3 3 0 0 0-3-1.5c-1.7 0-3 .9-3 2.2 0 3 6 1.6 6 4.6 0 1.3-1.3 2.2-3 2.2A3.2 3.2 0 0 1 9 15M12 6v1.5M12 16.5V18" />
            </svg>
            <?= e($bonusLabel) ?>
          </div>
          <div class="rfd-detail-val">
            <?= fmtRp($referal['bonus']) ?>
            <?php if ($referal['bonus_cair'] > 0): ?>
              <span class="rfd-pill">+ <?= fmtRp($referal['bonus_cair']) ?>, bonus cair!</span>
            <?php endif; ?>
          </div>
        </div>
        <svg class="rfd-detail-chev" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
          stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="6 9 12 15 18 9" />
        </svg>
      </div>
      <div class="rfd-detail-body">
        <div class="stl-row"><span>Merchant Bergabung</span><span><?= $merchant ?> Merchant</span></div>
        <div class="stl-row"><span>Level</span><span><?= e(REFERAL_LEVELS[$referal['level']]['nama']) ?></span></div>
        <div class="stl-row"><span>Nilai Bonus</span><span><?= fmtRp(REFERAL_LEVELS[$referal['level']]['bonus']) ?> / Merchant</span></div>
        <div class="stl-row"><span>Total Bonus</span><b><?= fmtRp($referal['bonus']) ?></b></div>
      </div>
    </div>

    <div class="rfd-foot-note">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
        stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="9" />
        <line x1="12" y1="11" x2="12" y2="16" />
        <line x1="12" y1="8" x2="12.01" y2="8" />
      </svg>
      Perolehan &amp; Level referal berlaku per bulan.
    </div>
  </div>

</div><!-- /content -->

<div class="overlay" id="ovShareRef" onclick="closeOv(event,'ovShareRef')">
  <div class="sheet">
    <div class="handle"></div>
    <div class="sheet-title rfd-skema-title">Bagikan Link Referal</div>
    <div class="rfd-skema-sub">Anda akan mendapatkan bonus dari setiap teman Anda yang daftar QRIS (hingga bayar) dengan link tersebut</div>

    <div class="rfs-link-card">
      <div class="rfs-link-lbl">Link Referal Anda</div>
      <div class="rfs-link" id="rfsLink"><?= e($referal['link']) ?></div>
      <button type="button" class="btn-red" onclick="copyReferalLink()">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round">
          <rect x="9" y="9" width="12" height="12" rx="3" />
          <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
        </svg>
        Salin Link Referal
      </button>
    </div>

    <div class="rfs-to-lbl">Bagikan ke</div>
    <div class="rfs-apps">
      <button type="button" class="rfs-app" onclick="shareReferalTo('whatsapp')">
        <span class="rfs-app-ic" style="background:#25d366">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="white"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.6.8-.8 1-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.3-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6a2.7 2.7 0 0 0 1.8-1.2 2.2 2.2 0 0 0 .1-1.2c0-.1-.2-.2-.4-.3z"/></svg>
        </span>
        Whatsapp
      </button>
      <button type="button" class="rfs-app" onclick="shareReferalTo('telegram')">
        <span class="rfs-app-ic" style="background:#2aabee">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="white"><path d="M21.6 3.4 2.9 10.6c-1.3.5-1.3 1.2-.2 1.6l4.8 1.5 1.8 5.6c.2.6.1.9.8.9.5 0 .7-.2 1-.5l2.4-2.3 4.9 3.6c.9.5 1.6.2 1.8-.8l3.3-15.4c.3-1.3-.5-1.9-1.4-1.5zM8.9 13.4l9.4-5.9c.5-.3.9-.1.5.2l-8 7.3-.3 3.4z"/></svg>
        </span>
        Telegram
      </button>
      <button type="button" class="rfs-app" onclick="shareReferalTo('x')">
        <span class="rfs-app-ic" style="background:#f3f4f6">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="#111827"><path d="M17.8 3h3.1l-6.8 7.7L22 21h-6.2l-4.9-6.4L5.3 21H2.2l7.2-8.3L1.8 3h6.4l4.4 5.8zm-1.1 16.2h1.7L7.4 4.7H5.6z"/></svg>
        </span>
        X
      </button>
      <button type="button" class="rfs-app" onclick="shareReferalTo('lainnya')">
        <span class="rfs-app-ic" style="background:#f3f4f6">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#111827" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <circle cx="18" cy="5" r="3" /><circle cx="6" cy="12" r="3" /><circle cx="18" cy="19" r="3" />
            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" /><line x1="15.41" y1="6.51" x2="8.59" y2="10.49" />
          </svg>
        </span>
        Lainnya
      </button>
    </div>

    <div class="rfs-foot">
      <button type="button" class="btn-outline" id="rfsBack" onclick="closeOvById('ovShareRef')">Kembali</button>
      <div class="rfs-copied" id="rfsCopied">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9" /><polyline points="8 12 11 15 16 9" /></svg>
        Link referal berhasil disalin
      </div>
    </div>
  </div>
</div>

<div class="overlay" id="ovRfdBonus" onclick="closeOv(event,'ovRfdBonus')">
  <div class="sheet">
    <div class="handle"></div>
    <div class="sheet-title rfd-skema-title">Pencairan Bonus Referal</div>
    <div class="rfd-skema-sub">Minimal pencairan bonus referal Rp 50.000. Bonus akan dicairkan otomatis pada bulan berikutnya (estimasi tanggal 15–20)</div>
    <div style="padding:0 16px 16px">
      <button class="btn-red" onclick="closeOvById('ovRfdBonus')">Mengerti</button>
    </div>
  </div>
</div>

<div class="overlay" id="ovRfdSkema" onclick="closeOv(event,'ovRfdSkema')">
  <div class="sheet">
    <div class="handle"></div>
    <div class="sheet-title rfd-skema-title">Tingkatan Bonus Referal</div>
    <div class="rfd-skema-sub">Perolehan &amp; level referal berlaku untuk setiap satu bulan</div>
    <div style="padding:0 16px 16px">
      <?php require __DIR__ . '/referal_levels.php'; ?>
      <button class="btn-red" style="margin-top:16px" onclick="closeOvById('ovRfdSkema')">Mengerti</button>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>

<script src="assets/js/main.js"></script>
<script>
  const REFERAL_LINK = <?= json_encode($referal['link']) ?>;

  const REFERAL_SHARE_TEXT = 'Daftar QRIS InterActive lewat link referal saya: ' + REFERAL_LINK;
  let rfsCopiedTimer = null;

  // Dibuka dari halaman lain lewat referal.php#bagikan
  if (window.location.hash === '#bagikan') {
    history.replaceState(null, '', window.location.pathname + window.location.search);
    openShareSheet();
  }

  function openShareSheet() {
    setCopiedState(false);
    openOv('ovShareRef');
  }

  function setCopiedState(on) {
    document.getElementById('rfsCopied').classList.toggle('on', on);
    document.getElementById('rfsBack').style.display = on ? 'none' : '';
  }

  async function copyReferalLink() {
    try {
      await navigator.clipboard.writeText(REFERAL_LINK);
    } catch (e) {
      // Clipboard API butuh konteks aman (https/localhost); pakai cara lama
      const ta = document.createElement('textarea');
      ta.value = REFERAL_LINK;
      ta.style.position = 'fixed';
      ta.style.opacity = '0';
      document.body.appendChild(ta);
      ta.select();
      const ok = document.execCommand('copy');
      ta.remove();
      if (!ok) return toast('Gagal menyalin link.', true);
    }
    setCopiedState(true);
    clearTimeout(rfsCopiedTimer);
    rfsCopiedTimer = setTimeout(() => setCopiedState(false), 2500);
  }

  // TODO: di webview Flutter, link eksternal (wa.me, t.me, x.com) perlu diizinkan
  // dibuka ke aplikasi/browser; nama handler shareReferalLink masih asumsi.
  function shareReferalTo(app) {
    const text = encodeURIComponent(REFERAL_SHARE_TEXT);
    const urls = {
      whatsapp: `https://wa.me/?text=${text}`,
      telegram: `https://t.me/share/url?url=${encodeURIComponent(REFERAL_LINK)}&text=${encodeURIComponent('Daftar QRIS InterActive lewat link referal saya')}`,
      x: `https://x.com/intent/post?text=${text}`,
    };
    if (urls[app]) {
      window.open(urls[app], '_blank');
      return;
    }

    if (window.flutter_inappwebview) {
      window.flutter_inappwebview.callHandler('shareReferalLink', { link: REFERAL_LINK, text: REFERAL_SHARE_TEXT });
    } else if (navigator.share) {
      navigator.share({ title: 'Referal QRIS InterActive', text: REFERAL_SHARE_TEXT, url: REFERAL_LINK }).catch(() => {});
    } else {
      copyReferalLink();
    }
  }
</script>
