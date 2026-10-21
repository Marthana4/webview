<?php
require_once __DIR__ . '/includes/dummy.php';

$pageTitle = 'Syarat & Ketentuan – InterActive QRIS';
$simpleTopbar = ['title' => 'Syarat & Ketentuan', 'back' => 'referal.php'];

require_once __DIR__ . '/includes/header.php';
?>

<div class="content content--tnc">

  <div class="tnc fi">
    <h1 class="tnc-title">Syarat dan Ketentuan Referal InterActive QRIS</h1>

    <h2 class="tnc-h">Affiliate Marketer</h2>
    <ol class="ref-ol tnc-ol">
      <li>Merchant InterActive QRIS yang berhak menjadi Affiliate Marketer (yang membagikan referal) adalah merchant yang sudah berhasil mendapatkan NMID (QRIS sudah aktif).</li>
      <li>Merchant perlu menyetujui syarat dan ketentuan referal InterActive QRIS untuk membagikan referal. Persetujuan dilakukan dengan cara mencentang kolom persetujuan syarat dan ketentuan pada saat pertama kali membuka menu referal (di aplikasi QRIS Online atau web dashboard merchant), lalu klik lanjutkan.</li>
    </ol>

    <h2 class="tnc-h">Perolehan Merchant Bergabung</h2>
    <ol class="ref-ol tnc-ol">
      <li>Merchant Bergabung adalah merchant yang berhasil mendaftar dan melunasi biaya pendaftaran merchant InterActive QRIS melalui link referal milik Affiliate Marketer.</li>
      <li>Jumlah Merchant Bergabung dapat dilihat melalui menu referal aplikasi QRIS Online atau dashboard web merchant QRIS.</li>
      <li>Jumlah Merchant Bergabung yang dihitung dalam bonus referal adalah akumulasi setiap satu bulan (mulai tanggal 1 hingga akhir tanggal bulan).</li>
      <li>Jumlah Merchant Bergabung akan di ulang dari 0 setiap bulannya.</li>
    </ol>

    <h2 class="tnc-h">Perhitungan Bonus Referal</h2>
    <ol class="ref-ol tnc-ol">
      <li>Bonus referal yang diterima adalah hasil kali dari Nilai Bonus referal dengan Jumlah Merchant Bergabung pada setiap satu bulan.</li>
      <li>Nilai bonus referal adalah nilai nominal bonus yang diperoleh untuk setiap Merchant Bergabung, dan ditentukan berdasarkan level Affiliate Marketer.</li>
      <li>Berikut skema level Affiliate Marketer dan nilai bonus yang berlaku:</li>
    </ol>
    <?php require __DIR__ . '/includes/referal_levels.php'; ?>
    <ol class="ref-ol tnc-ol" start="4">
      <li>Level Affiliate Marketer akan diulang dari awal setiap bulannya mengikuti ketentuan Jumlah Merchant Bergabung.</li>
    </ol>

    <h2 class="tnc-h">Ketentuan Pencairan Bonus</h2>
    <ol class="ref-ol tnc-ol">
      <li>Pencairan bonus referal dilakukan pada tanggal 15–20 bulan berikutnya.</li>
      <li>Terdapat minimal pencairan bonus referal yaitu sebesar Rp 50.000.</li>
      <li>Jika perolehan bonus referal dalam satu bulan tidak memenuhi syarat minimal pencairan bonus referal, maka akan dicairkan pada bulan berikutnya hingga akumulasi nominal perolehan bonus referal mencapai minimal pencairan bonus. Berikut Contohnya : Budi memperoleh total bonus referal sebanyak Rp 24.000 pada bulan Januari. Pada bulan Februari, Budi memperoleh Rp 30.000. Maka akumulasi perolehan bonus referal menjadi Rp 54.000. Sehingga perolehan bonus referal akan dicairkan pada bulan Maret di rentang tanggal 15–20.</li>
      <li>Terdapat tambahan biaya SKN (Sistem Kliring Nasional) setiap kali pencairan jika pencairan dana bukan ditransfer ke rekening bank BCA, BRI dan Mandiri. Tambahan Biaya SKN yang dikenakan sebesar Rp 2.900.</li>
      <li>Bonus referal akan ditransfer ke nomor rekening yang telah didaftarkan QRIS.ONLINE.</li>
      <li>Apabila ditemukan kecurangan (hack data, auto referal, permainan admin, dll) maka pihak QRIS.ONLINE secara sepihak berhak menghanguskan bonus tersebut.</li>
    </ol>

    <h2 class="tnc-h">Cara Kerja Referal</h2>
    <ol class="ref-ol tnc-ol">
      <li>Affiliate Marketer membagikan link referalnya ke calon merchant dengan cara menekan tombol yang digunakan untuk membagikan referal (lihat di menu referal aplikasi maupun web dashboard merchant)</li>
      <li>Calon merchant QRIS melakukan pendaftaran dan pembayaran melalui link referal yang dibagikan</li>
      <li>Jika calon merchant berhasil daftar dan membayar, maka Affiliate Marketer telah berhasil memperoleh 1 orang bergabung.</li>
      <li>Setiap bulan akan dilakukan perhitungan bonus referal, yaitu jumlah orang yang berhasil diajak dikali nilai bonus yang berlaku.</li>
    </ol>
    <ol class="ref-ol tnc-ol tnc-ol--tight" start="5">
      <li>Contoh Perhitungan Bonus :</li>
      <li>Anton dalam bulan Juni berhasil mengajak 10 Orang</li>
      <li>Jumlah Merchant Bergabung : 18 Orang</li>
      <li>Level Anton: Bronze (karena kurang dari 20 orang)</li>
      <li>Nilai Bonus : Rp 3000 / pendaftar</li>
      <li>Rekening Budi adalah Bank Permata</li>
      <li>Perhitungan Total Bonus yang dicairkan =</li>
      <li>18 Orang x 3.000 = Rp 54.000</li>
      <li>Dikurangi biaya SKN Rp 2.900 (karena rekening pencairan yang dipakai Anton bukan BCA, Mandiri, dan BRI)</li>
      <li>Sehingga total bonus yang diterima Anton sebesar Rp 51.100</li>
      <li>Budi dalam bulan Juni berhasil mengajak 40 Orang</li>
      <li>Jumlah Merchant Bergabung : 40 Orang</li>
      <li>Level Budi : Gold (karena kurang dari 50 orang)</li>
      <li>Nilai Bonus : Rp 4.000 / pendaftar</li>
      <li>Rekening Budi adalah Bank BRI (sehingga tidak dikenakan biaya SKN)</li>
      <li>Perhitungan Total Bonus yang dicairkan =</li>
      <li>40 Orang x 4.000 = Rp 160.000</li>
      <li>Sehingga total bonus yang diterima sebesar Rp 160.000</li>
      <li>Budi dalam bulan Juli berhasil mengajak 50 Orang</li>
      <li>Jumlah Merchant Bergabung : 50 Orang</li>
      <li>Level Budi : Super (karena lebih dari 50 orang)</li>
      <li>Nilai Bonus : Rp 5000/ pendaftar</li>
      <li>Maka Total Bonus =</li>
      <li>50 Orang x 5000 = Rp 250.000</li>
      <li>Sehingga total bonus yang diterima sebesar Rp 250.000</li>
    </ol>
  </div>

  <div class="tnc-foot">
    Syarat dan Ketentuan di atas tidak boleh diganggu gugat dan dapat berubah sewaktu-waktu sesuai ketentuan yang dibuat oleh InterActive QRIS.
  </div>

</div><!-- /content -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script src="assets/js/main.js"></script>
