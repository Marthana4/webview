<?php // Kartu skema level Affiliate Marketer (halaman Referal, S&K, dan sheet Skema Level) ?>
    <div class="ref-levels">
      <?php foreach (REFERAL_LEVELS as $lvKey => $lv): ?>
        <div class="ref-level ref-level--<?= $lvKey ?>">
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
            <div class="ref-level-name"><?= e($lv['nama']) ?> Level</div>
            <div class="ref-level-lbl">Merchant Bergabung</div>
            <div class="ref-level-val"><?= e($lv['merchant']) ?></div>
            <div class="ref-level-lbl">Nilai Bonus</div>
            <div class="ref-level-val"><span class="ref-level-bonus"><?= fmtRp($lv['bonus']) ?></span> / Merchant Bergabung</div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
