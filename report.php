<?php
require_once __DIR__ . '/includes/dummy.php';

$pageTitle = 'Laporan - InterActive QRIS';
$activeTab = 'laporan';

$toko = $DUMMY['toko_aktif'];
$allToko = $DUMMY['toko'];
$tx = $DUMMY['transaksi_hari_ini'];
$lap = $DUMMY['laporan'];
$klaim = $DUMMY['klaim'];
$chart = $DUMMY['chart'];
$jam = $DUMMY['jam'];
$period = 'kemarin';
$initialReportData = buildReportPayload($dbLocations ?? [], $DUMMY, (int)($toko['id'] ?? 0), $period);

require_once __DIR__ . '/includes/header.php';
?>

<div class="content">

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

  <div class="tx-card fi">
    <div class="tx-info">
      <div class="tx-label">Nilai transaksi hari ini</div>
      <div class="tx-val" id="txValue"><?= fmtRp($tx['total_nilai']) ?></div>
      <div class="tx-saldo" id="txSaldoRow" onclick="openSaldoQris()" style="display:none">
        <span class="tx-saldo-lbl">Saldo QRIS</span>
        <span class="tx-saldo-val" id="txSaldo"></span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round">
          <polyline points="9 6 15 12 9 18" />
        </svg>
      </div>
    </div>
    <img class="tx-qris" src="assets/images/Soft%20Overlay.png" alt="Lihat QRIS" onclick="openQris()">
  </div>

  <div class="laporan-wrap fi">
    <div class="laporan-head">
      <div>
        <div class="laporan-title">Laporan Bulanan</div>
        <div class="laporan-date" id="laporanDate"><?= e($lap['periode_str']) ?></div>
      </div>
      <button class="period-btn" id="periodBtn" onclick="openOv('ovPeriod')">
        Kemarin
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
          stroke-linecap="round" stroke-linejoin="round">
          <polyline points="6 9 12 15 18 9" />
        </svg>
      </button>
    </div>
    <div class="klaim-row">
      <div class="klaim-box" onclick="openOv('ovKlaim')">
        <div class="klaim-label">Klaim user <span class="info-i" onclick="event.stopPropagation();openOv('ovKlaim')">!</span></div>
        <div class="klaim-val" id="klaimUser"><?= $klaim['user'] ?></div>
      </div>
      <div class="klaim-box" onclick="openOv('ovKlaimMerchant')">
        <div class="klaim-label">Klaim merchant <span class="info-i" onclick="event.stopPropagation();openOv('ovKlaimMerchant')">!</span></div>
        <div class="klaim-val" id="klaimMerchant"><?= $klaim['merchant'] ?></div>
      </div>
    </div>
  </div>

  <div class="chart-card fi">
    <div class="chart-title">Grafik transaksi QRIS:</div>
    <div class="chart-outer">
      <div class="chart-y" id="chartY"></div>
      <div class="chart-inner">
        <canvas id="lineChart" height="140"></canvas>
        <div class="chart-x-lbl">Tanggal</div>
      </div>
    </div>
  </div>

  <div class="stat-wrap">
    <div class="stat-item">
      <div class="stat-left">
        <div class="stat-icon">
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M16.1833 9.7915H13.3333C12.9916 9.7915 12.7083 9.50817 12.7083 9.1665V3.3415C12.7083 2.72484 12.95 2.14984 13.3833 1.71651C13.8166 1.28317 14.3916 1.0415 15.0083 1.0415H15.0166C16.0583 1.04984 17.0416 1.45817 17.7916 2.19984C18.5416 2.95817 18.95 3.95817 18.95 4.99984V7.0165C18.9583 8.67484 17.8416 9.7915 16.1833 9.7915ZM13.9583 8.5415H16.1833C17.15 8.5415 17.7083 7.98317 17.7083 7.0165V4.99984C17.7083 4.28317 17.425 3.59984 16.9166 3.08317C16.4083 2.58317 15.725 2.29984 15.0166 2.2915C15.0166 2.2915 15.0166 2.2915 15.0083 2.2915C14.7333 2.2915 14.4666 2.39984 14.2666 2.59984C14.0666 2.79984 13.9583 3.05817 13.9583 3.3415V8.5415V8.5415Z" fill="var(--gray)"/>
            <path d="M7.50002 19.4415C7.10835 19.4415 6.74169 19.2915 6.46669 19.0082L5.08335 17.6165C5.00835 17.5415 4.89169 17.5332 4.80835 17.5998L3.37502 18.6665C2.93335 18.9998 2.35002 19.0582 1.85002 18.8082C1.35002 18.5582 1.04169 18.0582 1.04169 17.4998V4.99984C1.04169 2.48317 2.48335 1.0415 5.00002 1.0415H15C15.3417 1.0415 15.625 1.32484 15.625 1.6665C15.625 2.00817 15.3417 2.2915 15 2.2915C14.425 2.2915 13.9584 2.75817 13.9584 3.33317V17.4998C13.9584 18.0582 13.65 18.5582 13.15 18.8082C12.65 19.0582 12.0667 19.0082 11.625 18.6748L10.2 17.6082C10.1167 17.5415 10 17.5582 9.93335 17.6248L8.53335 19.0248C8.25835 19.2915 7.89169 19.4415 7.50002 19.4415ZM4.92502 16.3082C5.30835 16.3082 5.68335 16.4498 5.96669 16.7415L7.35002 18.1332C7.40002 18.1832 7.46669 18.1915 7.50002 18.1915C7.53335 18.1915 7.60002 18.1832 7.65002 18.1332L9.05002 16.7332C9.56669 16.2165 10.3834 16.1665 10.9584 16.6082L12.375 17.6665C12.4667 17.7332 12.55 17.7082 12.5917 17.6832C12.6334 17.6582 12.7084 17.6082 12.7084 17.4998V3.33317C12.7084 2.95817 12.8 2.59984 12.9584 2.2915H5.00002C3.15002 2.2915 2.29169 3.14984 2.29169 4.99984V17.4998C2.29169 17.6165 2.36669 17.6665 2.40835 17.6915C2.45835 17.7165 2.54169 17.7332 2.62502 17.6665L4.05002 16.5998C4.30835 16.4082 4.61669 16.3082 4.92502 16.3082Z" fill="var(--gray)"/>
            <path d="M10 11.4668H7.5C7.15833 11.4668 6.875 11.1835 6.875 10.8418C6.875 10.5001 7.15833 10.2168 7.5 10.2168H10C10.3417 10.2168 10.625 10.5001 10.625 10.8418C10.625 11.1835 10.3417 11.4668 10 11.4668Z" fill="var(--gray)"/>
            <path d="M10 8.1333H7.5C7.15833 8.1333 6.875 7.84997 6.875 7.5083C6.875 7.16663 7.15833 6.8833 7.5 6.8833H10C10.3417 6.8833 10.625 7.16663 10.625 7.5083C10.625 7.84997 10.3417 8.1333 10 8.1333Z" fill="var(--gray)"/>
            <path d="M4.97518 8.34147C4.51685 8.34147 4.14185 7.96647 4.14185 7.50814C4.14185 7.0498 4.51685 6.6748 4.97518 6.6748C5.43351 6.6748 5.80851 7.0498 5.80851 7.50814C5.80851 7.96647 5.43351 8.34147 4.97518 8.34147Z" fill="var(--gray)"/>
            <path d="M4.97518 11.675C4.51685 11.675 4.14185 11.3 4.14185 10.8416C4.14185 10.3833 4.51685 10.0083 4.97518 10.0083C5.43351 10.0083 5.80851 10.3833 5.80851 10.8416C5.80851 11.3 5.43351 11.675 4.97518 11.675Z" fill="var(--gray)"/>
          </svg>
        </div>
        <div>
          <div class="stat-lbl">Total transaksi</div>
          <div class="stat-v" id="statTotalTx"><?= number_format($lap['total_tx']) ?></div>
        </div>
      </div>
      <div class="<?= $lap['pct_tx'] >= 0 ? 'badge-up' : 'badge-down' ?>" id="statPctTx">
        <span id="statPctTxText"><?= abs($lap['pct_tx']) ?>%</span>
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
          stroke-linecap="round" stroke-linejoin="round" id="statPctTxIcon">
          <?= $lap['pct_tx'] >= 0 ? '<polyline points="18 15 12 9 6 15"/>' : '<polyline points="6 9 12 15 18 9"/>' ?>
        </svg>
      </div>
    </div>

    <div class="stat-item">
      <div class="stat-left">
        <div class="stat-icon">
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M8.78337 18.2832C8.5917 18.2832 8.40836 18.1998 8.28336 18.0332L7.44169 16.9082C7.26669 16.6748 7.03337 16.5415 6.78337 16.5248C6.52504 16.5082 6.28335 16.6248 6.08335 16.8332C4.87502 18.1248 3.95836 18.0165 3.51669 17.8498C3.06669 17.6748 2.30835 17.0998 2.30835 15.2498V5.86651C2.30835 2.16651 3.37502 1.0415 6.86668 1.0415H13.1584C16.65 1.0415 17.7167 2.16651 17.7167 5.86651V9.41649C17.7167 9.75816 17.4334 10.0415 17.0917 10.0415C16.75 10.0415 16.4667 9.75816 16.4667 9.41649V5.86651C16.4667 2.85818 15.9417 2.2915 13.1584 2.2915H6.86668C4.08335 2.2915 3.55835 2.85818 3.55835 5.86651V15.2498C3.55835 16.1248 3.77502 16.6082 3.97502 16.6832C4.12502 16.7415 4.53337 16.6582 5.1667 15.9832C5.62504 15.4998 6.22503 15.2415 6.85003 15.2748C7.46669 15.3082 8.05003 15.6332 8.4417 16.1582L9.29169 17.2832C9.50002 17.5582 9.44169 17.9498 9.16669 18.1582C9.04169 18.2498 8.90837 18.2832 8.78337 18.2832Z" fill="var(--gray)"/>
            <path d="M13.3334 6.4585H6.66669C6.32502 6.4585 6.04169 6.17516 6.04169 5.8335C6.04169 5.49183 6.32502 5.2085 6.66669 5.2085H13.3334C13.675 5.2085 13.9584 5.49183 13.9584 5.8335C13.9584 6.17516 13.675 6.4585 13.3334 6.4585Z" fill="var(--gray)"/>
            <path d="M12.5 9.7915H7.5C7.15833 9.7915 6.875 9.50817 6.875 9.1665C6.875 8.82484 7.15833 8.5415 7.5 8.5415H12.5C12.8417 8.5415 13.125 8.82484 13.125 9.1665C13.125 9.50817 12.8417 9.7915 12.5 9.7915Z" fill="var(--gray)"/>
            <path d="M12.35 18.1498C12.0334 18.1498 11.7334 18.0332 11.5167 17.8165C11.2584 17.5582 11.1417 17.1832 11.2 16.7915L11.3584 15.6665C11.4 15.3748 11.5751 15.0248 11.7834 14.8165L14.7334 11.8665C15.1334 11.4665 15.5251 11.2582 15.9501 11.2165C16.4667 11.1665 16.9834 11.3832 17.4667 11.8665C17.95 12.3498 18.1667 12.8582 18.1167 13.3832C18.075 13.7998 17.8584 14.1998 17.4667 14.5998L14.5167 17.5498C14.3084 17.7582 13.9584 17.9332 13.6667 17.9749L12.5417 18.1332C12.475 18.1415 12.4167 18.1498 12.35 18.1498ZM16.0917 12.4582C16.0834 12.4582 16.075 12.4582 16.0667 12.4582C15.95 12.4665 15.7917 12.5748 15.6167 12.7498L12.6667 15.6998C12.6417 15.7248 12.6 15.8082 12.6 15.8415L12.45 16.8832L13.4917 16.7332C13.525 16.7248 13.6084 16.6832 13.6334 16.6582L16.5834 13.7082C16.7584 13.5249 16.8667 13.3748 16.875 13.2582C16.8917 13.0915 16.725 12.8915 16.5834 12.7498C16.45 12.6165 16.2584 12.4582 16.0917 12.4582Z" fill="var(--gray)"/>
            <path d="M16.6001 15.2083C16.5417 15.2083 16.4834 15.1999 16.4334 15.1833C15.3334 14.8749 14.4584 14 14.1501 12.9C14.0584 12.5666 14.2501 12.2249 14.5834 12.1249C14.9167 12.0333 15.2584 12.2249 15.3501 12.5583C15.5417 13.2416 16.0834 13.7833 16.7667 13.9749C17.1001 14.0666 17.2917 14.4166 17.2001 14.75C17.1251 15.025 16.8751 15.2083 16.6001 15.2083Z" fill="var(--gray)"/>
          </svg>
        </div>
        <div>
          <div class="stat-lbl">Rata rata nilai transaksi harian</div>
          <div class="stat-v" id="statAvgHarian"><?= fmtRp($lap['avg_harian']) ?></div>
        </div>
      </div>
      <div class="<?= $lap['pct_avg'] >= 0 ? 'badge-up' : 'badge-down' ?>" id="statPctAvg">
        <span id="statPctAvgText"><?= abs($lap['pct_avg']) ?>%</span>
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
          stroke-linecap="round" stroke-linejoin="round" id="statPctAvgIcon">
          <?= $lap['pct_avg'] >= 0 ? '<polyline points="18 15 12 9 6 15"/>' : '<polyline points="6 9 12 15 18 9"/>' ?>
        </svg>
      </div>
    </div>

    <div class="stat-item">
      <div class="stat-left">
        <div class="stat-icon">
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12.0834 13.9585H7.91669C7.57502 13.9585 7.29169 13.6752 7.29169 13.3335C7.29169 12.9918 7.57502 12.7085 7.91669 12.7085H12.0834C12.425 12.7085 12.7084 12.9918 12.7084 13.3335C12.7084 13.6752 12.425 13.9585 12.0834 13.9585Z" fill="var(--gray)"/>
            <path d="M4.3333 8.71689C4.17497 8.71689 4.01664 8.65022 3.89164 8.53356C3.71664 8.35856 3.6583 8.08356 3.7583 7.85022L5.3083 4.15022C5.34164 4.07522 5.3583 4.02522 5.3833 3.98356C6.61664 1.14189 8.19164 0.450223 10.975 1.51689C11.1333 1.57522 11.2583 1.70022 11.325 1.85856C11.3916 2.01689 11.3916 2.19189 11.325 2.35022L8.8833 8.01689C8.7833 8.24189 8.5583 8.39189 8.3083 8.39189H5.9333C5.4583 8.39189 5.0083 8.48356 4.57497 8.66689C4.49997 8.70022 4.41664 8.71689 4.3333 8.71689ZM8.84164 2.29189C7.8083 2.29189 7.17497 2.96689 6.51664 4.50022C6.5083 4.52522 6.49164 4.55022 6.4833 4.57522L5.39164 7.16689C5.57497 7.15022 5.74997 7.14189 5.9333 7.14189H7.89164L9.89997 2.47522C9.5083 2.35022 9.1583 2.29189 8.84164 2.29189Z" fill="var(--gray)"/>
            <path d="M15.2417 8.55843C15.1833 8.55843 15.1167 8.5501 15.0583 8.53343C14.75 8.44177 14.4083 8.39177 14.0583 8.39177H8.30832C8.09999 8.39177 7.89999 8.28343 7.78332 8.10843C7.67499 7.93343 7.64999 7.70844 7.73332 7.51677L10.15 1.90843C10.275 1.60843 10.6333 1.40843 10.95 1.50843C11.05 1.54177 11.1417 1.58343 11.2417 1.6251L13.2083 2.4501C14.3583 2.9251 15.125 3.4251 15.625 4.0251C15.725 4.14177 15.8083 4.26677 15.8833 4.39177C15.975 4.53343 16.0583 4.70844 16.1167 4.88344C16.1417 4.94177 16.1833 5.0501 16.2083 5.16677C16.4417 5.9501 16.325 6.9251 15.825 8.1751C15.725 8.40843 15.4917 8.55843 15.2417 8.55843ZM9.25832 7.14177H14.0667C14.3333 7.14177 14.5917 7.16677 14.85 7.20843C15.0833 6.48343 15.1333 5.9251 15 5.4751C14.9833 5.4001 14.9667 5.36677 14.9583 5.33343C14.9083 5.2001 14.875 5.1251 14.8333 5.05844C14.775 4.96677 14.7333 4.89177 14.6667 4.81677C14.3083 4.38344 13.675 3.98344 12.7333 3.6001L11.0833 2.90843L9.25832 7.14177Z" fill="var(--gray)"/>
            <path d="M13.25 18.9583H6.75C6.51667 18.9583 6.3 18.9416 6.08333 18.9166C3.15833 18.7166 1.49167 17.0499 1.29167 14.0916C1.26667 13.9083 1.25 13.6833 1.25 13.4583V11.8333C1.25 9.95827 2.36667 8.2666 4.09167 7.5166C4.675 7.2666 5.3 7.1416 5.94167 7.1416H14.075C14.55 7.1416 15.0083 7.20827 15.4333 7.3416C17.3917 7.93327 18.7667 9.78327 18.7667 11.8333V13.4583C18.7667 13.6416 18.7583 13.8166 18.75 13.9833C18.5667 17.2416 16.6667 18.9583 13.25 18.9583ZM5.93333 8.3916C5.45833 8.3916 5.00833 8.48327 4.575 8.6666C3.30833 9.2166 2.49167 10.4583 2.49167 11.8333V13.4583C2.49167 13.6333 2.50833 13.8083 2.525 13.9749C2.68333 16.3499 3.85 17.5166 6.19167 17.6749C6.4 17.6999 6.56667 17.7166 6.74167 17.7166H13.2417C15.9917 17.7166 17.3417 16.5083 17.475 13.9249C17.4833 13.7749 17.4917 13.6249 17.4917 13.4583V11.8333C17.4917 10.3249 16.4833 8.97493 15.05 8.53327C14.7417 8.4416 14.4 8.3916 14.05 8.3916H5.93333V8.3916Z" fill="var(--gray)"/>
            <path d="M1.86664 12.4582C1.52497 12.4582 1.24164 12.1748 1.24164 11.8332V9.3915C1.24164 6.7665 3.09997 4.49983 5.66664 3.99983C5.89164 3.95817 6.12497 4.0415 6.27497 4.2165C6.41664 4.3915 6.4583 4.6415 6.36664 4.84983L4.9083 8.33317C4.84164 8.48317 4.72497 8.59983 4.58331 8.6665C3.31664 9.2165 2.49997 10.4582 2.49997 11.8332C2.49164 12.1748 2.21664 12.4582 1.86664 12.4582ZM4.66664 5.68317C3.59997 6.28317 2.82497 7.33317 2.5833 8.55817C2.94997 8.18317 3.37497 7.8665 3.85831 7.63317L4.66664 5.68317Z" fill="var(--gray)"/>
            <path d="M18.1333 12.4581C17.7917 12.4581 17.5083 12.1748 17.5083 11.8331C17.5083 10.3248 16.5 8.97481 15.0667 8.53315C14.9 8.48315 14.7583 8.36648 14.6833 8.20815C14.6083 8.04982 14.6 7.86648 14.6667 7.70815C15.0583 6.73315 15.1583 6.02481 15 5.47481C14.9833 5.39981 14.9667 5.36648 14.9583 5.33315C14.85 5.09148 14.9083 4.80815 15.1 4.62482C15.2917 4.44148 15.5833 4.39981 15.8167 4.52481C17.6333 5.47481 18.7583 7.34148 18.7583 9.39148V11.8331C18.7583 12.1748 18.475 12.4581 18.1333 12.4581ZM16.0417 7.57481C16.5667 7.81648 17.0333 8.15815 17.425 8.56648C17.2667 7.74981 16.875 7.00815 16.3 6.42481C16.2583 6.77481 16.175 7.15815 16.0417 7.57481Z" fill="var(--gray)"/>
          </svg>
        </div>
        <div>
          <div class="stat-lbl">Total saldo dicairkan</div>
          <div class="stat-v" id="statSaldoCair"><?= fmtRp($lap['saldo_cair']) ?></div>
        </div>
      </div>
      <div class="<?= $lap['pct_saldo'] >= 0 ? 'badge-up' : 'badge-down' ?>" id="statPctSaldo">
        <span id="statPctSaldoText"><?= abs($lap['pct_saldo']) ?>%</span>
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
          stroke-linecap="round" stroke-linejoin="round" id="statPctSaldoIcon">
          <?= $lap['pct_saldo'] >= 0 ? '<polyline points="18 15 12 9 6 15"/>' : '<polyline points="6 9 12 15 18 9"/>' ?>
        </svg>
      </div>
    </div>
  </div>

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

  <div class="bar-card fi">
    <div class="jam-head">
      <div class="jam-title">Jam favorit: <b id="jamFavorit"><?= e($jam['jam_fav']) ?></b></div>
      <span class="info-i" onclick="openOv('ovJamFavorit')">!</span>
    </div>
    <div class="bar-wrap" id="barWrap"></div>
    <div class="dots" id="jamDots"></div>
  </div>

</div>

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

<div class="overlay" id="ovJamFavorit" onclick="closeOv(event,'ovJamFavorit')">
  <div class="sheet">
    <div class="handle"></div>
    <div class="klaim-sheet-title">Jam Favorit</div>
    <div class="klaim-body">
      Jam Favorit membantu menunjukkan waktu dengan trafik tertinggi saat pelanggan melakukan transaksi.
    </div>
    <div style="padding:16px">
      <button class="btn-red" onclick="closeOvById('ovJamFavorit')">Mengerti</button>
    </div>
  </div>
</div>

<div class="overlay" id="ovKlaimMerchant" onclick="closeOv(event,'ovKlaimMerchant')">
  <div class="sheet">
    <div class="handle"></div>
    <div class="klaim-sheet-title">Klaim Merchant</div>
    <div class="klaim-body" style="padding-bottom:16px">
      Klaim Merchant adalah permintaan merchant kepada tim InterActive QRIS untuk memverifikasi transaksi pelanggan
      yang berhasil, namun tidak tercatat di dashboard web/aplikasi InterActive QRIS. <a href="#">Pergi ke Dashboard</a>
    </div>
    <div style="padding:0 16px 16px">
      <button class="btn-red" onclick="closeOvById('ovKlaimMerchant')">Mengerti</button>
    </div>
  </div>
</div>

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
          <div class="p-opt-date" id="plbl-<?= $key ?>">-</div>
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

<script src="assets/js/main.js"></script>
<script src="assets/js/chart.js"></script>
<script>
  const ALL_TOKO = <?= json_encode($allToko) ?>;
  let selToko = <?= (int)$toko['id'] ?>;
  let curPeriod = <?= json_encode($initialReportData['period']['key'] ?? 'kemarin') ?>;
  let reportState = <?= json_encode($initialReportData, JSON_UNESCAPED_UNICODE) ?>;
  let isLoadingReport = false;
  let activeJamDayIndex = parseInt(reportState.jam?.active_day_index || 0, 10);

  const JAM_VALS = <?= json_encode($jam['values']) ?>;
  const JAM_LBLS = <?= json_encode($jam['labels']) ?>;

  function renderJamDots(days, activeIndex) {
    const dots = document.getElementById('jamDots');
    if (!dots) return;

    dots.innerHTML = days.map((day, index) =>
      `<div class="dot ${index === activeIndex ? 'on' : ''}" onclick="dotClick(${index})" title="${day.label}"></div>`
    ).join('');
  }

  function renderJamSection(jamData, forcedIndex = null) {
    const days = Array.isArray(jamData?.days) && jamData.days.length ? jamData.days : [];
    if (!days.length) {
      drawBarChart(jamData?.values || JAM_VALS, jamData?.labels || JAM_LBLS);
      document.getElementById('jamFavorit').textContent = jamData?.jam_fav || '-';
      return;
    }

    const nextIndex = forcedIndex === null
      ? parseInt(jamData.active_day_index || 0, 10)
      : forcedIndex;
    activeJamDayIndex = Math.max(0, Math.min(nextIndex, days.length - 1));

    document.getElementById('jamFavorit').textContent = days[activeJamDayIndex]?.label || '-';
    drawBarChart(days[activeJamDayIndex]?.values || [], days[activeJamDayIndex]?.labels || []);
    renderJamDots(days, activeJamDayIndex);
  }

  function dotClick(i) {
    renderJamSection(reportState.jam || {}, i);
  }

  function pickLoc(id, nama, alamat) {
    selToko = parseInt(id, 10);
    document.getElementById('storeName').textContent = nama;
    document.getElementById('storeAddr').innerHTML =
      `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
        <circle cx="12" cy="10" r="3"></circle>
      </svg>${alamat}`;
    renderLocs('');
    fetchReportData();
    setTimeout(() => closeOvById('ovLokasi'), 300);
  }

  // TODO: nama handler Flutter masih asumsi, sesuaikan dengan aplikasi
  function openSaldoQris() {
    if (window.flutter_inappwebview) {
      window.flutter_inappwebview.callHandler('openSaldoQris', { toko_id: selToko });
    } else {
      toast('Detail saldo QRIS hanya tersedia di aplikasi.', true);
    }
  }

  function openQris() {
    window.location.href = 'qris.php?toko_id=' + encodeURIComponent(selToko);
  }

  function selPeriod(v) {
    curPeriod = v;
    updateRadios(v);
  }

  function applyPeriod() {
    fetchReportData();
    closeOvById('ovPeriod');
  }

  function setPeriodButton(label) {
    const btn = document.getElementById('periodBtn');
    btn.innerHTML = `${label}
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="6 9 12 15 18 9"></polyline>
      </svg>`;
  }

  function setPctBadge(badgeId, textId, iconId, pct, displayText = null) {
    const badge = document.getElementById(badgeId);
    const text = document.getElementById(textId);
    const icon = document.getElementById(iconId);
    const normalizedPct = parseInt(pct || 0, 10);
    const isUp = normalizedPct >= 0;

    badge.classList.remove('badge-up', 'badge-down');
    badge.classList.add(isUp ? 'badge-up' : 'badge-down');
    text.textContent = displayText || `${Math.abs(normalizedPct)}%`;
    icon.innerHTML = isUp
      ? '<polyline points="18 15 12 9 6 15"></polyline>'
      : '<polyline points="6 9 12 15 18 9"></polyline>';
  }

  function renderReportData(data) {
    reportState = data;
    curPeriod = data.period?.key || curPeriod;
    selToko = parseInt(data.toko?.id || selToko, 10);
    activeJamDayIndex = parseInt(data.jam?.active_day_index || 0, 10);

    document.getElementById('storeName').textContent = data.toko?.nama || '-';
    document.getElementById('storeAddr').innerHTML =
      `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
        <circle cx="12" cy="10" r="3"></circle>
      </svg>${data.toko?.alamat || ''}`;

    document.getElementById('txValue').textContent = fmtRp(data.tx?.total_nilai || 0);
    // Saldo QRIS (belum ter-settle): baris disembunyikan selama datanya belum tersedia
    const saldoQris = data.tx?.saldo_qris;
    const hasSaldo = saldoQris !== null && saldoQris !== undefined;
    document.getElementById('txSaldoRow').style.display = hasSaldo ? '' : 'none';
    if (hasSaldo) document.getElementById('txSaldo').textContent = fmtRp(saldoQris);
    document.getElementById('laporanDate').textContent = data.lap?.periode_str || '-';
    document.getElementById('klaimUser').textContent = parseInt(data.klaim?.user || 0, 10);
    document.getElementById('klaimMerchant').textContent = parseInt(data.klaim?.merchant || 0, 10);
    document.getElementById('statTotalTx').textContent = parseInt(data.lap?.total_tx || 0, 10).toLocaleString('id-ID');
    document.getElementById('statAvgHarian').textContent = fmtRp(data.lap?.avg_harian || 0);
    document.getElementById('statSaldoCair').textContent = fmtRp(data.lap?.saldo_cair || 0);

    setPctBadge('statPctTx', 'statPctTxText', 'statPctTxIcon', data.lap?.pct_tx || 0, data.lap?.pct_tx_display || null);
    setPctBadge('statPctAvg', 'statPctAvgText', 'statPctAvgIcon', data.lap?.pct_avg || 0, data.lap?.pct_avg_display || null);
    setPctBadge('statPctSaldo', 'statPctSaldoText', 'statPctSaldoIcon', data.lap?.pct_saldo || 0, data.lap?.pct_saldo_display || null);
    setPeriodButton(data.period?.label || PERIOD_LBLS[curPeriod] || curPeriod);
    updateRadios(curPeriod);

    if (data.period?.date_from) {
      document.getElementById('dateFrom').value = data.period.date_from;
    }
    if (data.period?.date_to) {
      document.getElementById('dateTo').value = data.period.date_to;
    }

    drawLineChart(data.chart?.values || [], data.chart?.labels || []);
    renderJamSection(data.jam || {}, activeJamDayIndex);
  }

  async function fetchReportData() {
    if (isLoadingReport) return;

    const params = new URLSearchParams({
      period: curPeriod,
      toko_id: String(selToko || 0),
    });

    if (curPeriod === 'atur') {
      const dateFrom = document.getElementById('dateFrom').value;
      const dateTo = document.getElementById('dateTo').value;

      if (!dateFrom || !dateTo) {
        toast('Tanggal awal dan akhir wajib diisi.', true);
        return;
      }

      params.set('date_from', dateFrom);
      params.set('date_to', dateTo);
    }

    isLoadingReport = true;
    try {
      const response = await fetch(`report_data.php?${params.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const payload = await response.json();

      if (!response.ok || !payload.success) {
        throw new Error(payload.message || 'Gagal memuat laporan.');
      }

      renderReportData(payload.data);
      toast('Laporan berhasil diperbarui.');
    } catch (error) {
      toast(error.message || 'Gagal memuat laporan.', true);
    } finally {
      isLoadingReport = false;
    }
  }

  initPeriodLabels(curPeriod);
  renderReportData(reportState);
</script>
