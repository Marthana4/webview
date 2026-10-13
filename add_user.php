<?php
require_once __DIR__ . '/includes/dummy.php';

$pageTitle = 'Buat Akun Pegawai – InterActive QRIS';
$activeTab = 'pegawai';

$ownerEmail = $WEBVIEW_AUTH_EMAIL;
// $dbLocations datang dari includes/dummy.php: lokasi paid milik akun yg sedang login,
// 'id' di sini adalah mlocid asli (dipakai langsung sbg location_can, bukan index palsu)
$allToko = $dbLocations;

// ─── Handle form submit ───────────────────────────
$errors  = [];
$success = false;
$saved   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokoId  = (int)($_POST['toko_id']     ?? 0);
    $nama    = trim($_POST['nama']          ?? '');
    $email   = strtolower(trim($_POST['email'] ?? ''));
    $telp    = trim($_POST['no_telepon']    ?? '');
    $pass    = (string)($_POST['password']  ?? '');
    $konfirm = (string)($_POST['konfirmasi'] ?? '');
    $jabatan = $_POST['jabatan']            ?? 'kasir';

    // Validasi server-side (selaras dgn validasi add-account di v2/m proses.php)
    $tokoValid = array_values(array_filter($allToko, static fn($t) => (int)($t['id'] ?? 0) === $tokoId));
    if (!$tokoId || empty($tokoValid)) {
        $errors['toko_id'] = 'Pilih akses lokasi yang valid.';
    }
    if (empty($nama) || strlen($nama) < 3 || strlen($nama) > 50) {
        $errors['nama'] = 'Nama lengkap wajib diisi (3-50 karakter).';
    }
    if (empty($email) || strlen($email) < 5 || strlen($email) > 50 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid.';
    }
    if (empty($telp) || strlen($telp) < 5 || strlen($telp) > 50 || !preg_match('/^[0-9]+$/', $telp)) {
        $errors['no_telepon'] = 'Nomor telepon wajib diisi, hanya angka.';
    }
    if (strlen($pass) < 8 || strlen($pass) > 16) {
        $errors['password'] = 'Password harus 8-16 karakter.';
    }
    if (strlen($konfirm) < 8 || strlen($konfirm) > 16 || $pass !== $konfirm) {
        $errors['konfirmasi'] = 'Konfirmasi password tidak cocok.';
    }
    if (!in_array($jabatan, ['kasir', 'manajer', 'staff'], true)) {
        $errors['jabatan'] = 'Jabatan tidak valid.';
    }

    $conn = null;
    if (empty($errors)) {
        $conn = getDbConnection();
        if (!$conn) {
            $errors['general'] = 'Gagal terhubung ke database. Silahkan coba lagi.';
        }
    }

    // Akun yg sedang login harus akun cred aktif (owner/pegawai existing)
    if (empty($errors)) {
        $qOwner = mysqli_prepare($conn, "SELECT credin FROM qris_merchant_cred WHERE LOWER(credusername) = ? AND is_active = 1 LIMIT 1");
        mysqli_stmt_bind_param($qOwner, 's', $ownerEmail);
        mysqli_stmt_execute($qOwner);
        $ownerFound = mysqli_stmt_get_result($qOwner)->num_rows > 0;
        mysqli_stmt_close($qOwner);

        if (!$ownerFound) {
            $errors['general'] = 'Akun anda tidak ditemukan atau belum aktif.';
        }
    }

    // Email tidak boleh sudah terdaftar sbg merchant (paid) sendiri
    if (empty($errors)) {
        $qMerchant = mysqli_prepare($conn, "SELECT mname FROM qris_merchant_loc WHERE LOWER(memailaddr) = ? AND staticqr_paymentstat = 'paid' AND rowStat = 'real' LIMIT 1");
        mysqli_stmt_bind_param($qMerchant, 's', $email);
        mysqli_stmt_execute($qMerchant);
        $rMerchant = mysqli_stmt_get_result($qMerchant)->fetch_assoc();
        mysqli_stmt_close($qMerchant);

        if ($rMerchant) {
            $errors['email'] = 'Email ' . $email . ' sudah terdaftar sebagai merchant di lokasi usaha "' . $rMerchant['mname'] . '". Gunakan email lain.';
        }
    }

    // Email tidak boleh sudah jadi akun pegawai/owner (di lokasi manapun)
    if (empty($errors)) {
        $qCred = mysqli_prepare($conn, "SELECT credin FROM qris_merchant_cred WHERE LOWER(credusername) = ? LIMIT 1");
        mysqli_stmt_bind_param($qCred, 's', $email);
        mysqli_stmt_execute($qCred);
        $credExists = mysqli_stmt_get_result($qCred)->num_rows > 0;
        mysqli_stmt_close($qCred);

        if ($credExists) {
            $errors['email'] = 'Email ' . $email . ' sudah terdaftar sebagai akun pegawai/owner. Gunakan email lain.';
        }
    }

    if (empty($errors)) {
        $encryptMD5    = md5($pass . $email);
        $encryptBcrypt = password_hash($encryptMD5, PASSWORD_BCRYPT, ['cost' => 12]);
        $now           = date('Y-m-d H:i:s');
        $locationCan   = (string)$tokoId;

        $insertSQL = "INSERT INTO qris_merchant_cred
            (credusername, credpass, credlanguage, datereg, dateconfirm, credfullname, country, credemailaddr, credphonenumber, credrule, credsalt, gabuteruno, is_active, position, location_can)
            VALUES (?, ?, 'id', ?, ?, ?, 'Indonesia', ?, ?, NULL, ?, ?, 1, 'employee', ?)";
        $stmt = mysqli_prepare($conn, $insertSQL);

        if (!$stmt) {
            $errors['general'] = 'Gagal membuat akun. Silahkan coba lagi.';
        } else {
            mysqli_stmt_bind_param(
                $stmt,
                'ssssssssss',
                $email, $encryptBcrypt, $now, $now, $nama, $email, $telp, $encryptMD5, $encryptMD5, $locationCan
            );

            if (mysqli_stmt_execute($stmt)) {
                $success = true;
                $saved   = compact('nama', 'email', 'jabatan', 'telp');
            } else {
                $errors['general'] = 'Gagal membuat akun. Silahkan coba lagi.';
            }
            mysqli_stmt_close($stmt);
        }
    }

    if (!$success) {
        // Kembalikan nilai agar tidak hilang saat error
        $saved = compact('tokoId', 'nama', 'email', 'telp', 'jabatan');
    }
}

$simpleTopbar = ['title' => 'Akun Pegawai', 'back' => 'list.php'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="content">

  <!-- SUCCESS MESSAGE -->
  <?php if ($success): ?>
  <div class="alert-success fi" style="margin-top:8px">
    ✓ Akun pegawai <b><?= e($saved['nama']) ?></b> berhasil ditambahkan!
    <a href="list.php" style="color:var(--green);font-weight:700;margin-left:4px">Lihat daftar →</a>
  </div>
  <?php endif; ?>

  <!-- ERROR SUMMARY -->
  <?php if (!empty($errors)): ?>
  <div class="alert-error fi" style="margin-top:8px">
    <?= isset($errors['general']) ? e($errors['general']) : 'Mohon periksa kembali data yang diisi.' ?>
  </div>
  <?php endif; ?>

  <!-- FORM -->
  <form method="POST" action="add_user.php" id="formPegawai" novalidate>
    <div class="form-body">

      <!-- Akses Lokasi -->
      <div class="form-group">
        <label class="form-label" for="tokoTrigger">Akses Lokasi</label>
        <?php
          $selectedToko = null;
          if (!empty($saved['tokoId'])) {
              foreach ($allToko as $t) {
                  if ((int)$t['id'] === (int)$saved['tokoId']) { $selectedToko = $t; break; }
              }
          }
        ?>
        <button type="button" id="tokoTrigger"
                class="form-fakeselect <?= isset($errors['toko_id']) ? 'is-invalid' : '' ?>"
                onclick="openTokoSheet()">
          <span id="tokoLabel" class="<?= $selectedToko ? '' : 'placeholder' ?>">
            <?= $selectedToko ? e($selectedToko['nama'] . ' – ' . $selectedToko['alamat']) : 'Pilih akses lokasi' ?>
          </span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--gray)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 12 15 18 9"/>
          </svg>
        </button>
        <input type="hidden" name="toko_id" id="toko_id" value="<?= e($saved['tokoId'] ?? '') ?>">
        <?php if (isset($errors['toko_id'])): ?>
          <small id="tokoErrorHint" style="color:var(--red);font-size:11px"><?= e($errors['toko_id']) ?></small>
        <?php endif; ?>
      </div>

      <div class="divider"></div>

      <!-- Nama Lengkap -->
      <div class="form-group">
        <label class="form-label" for="nama">Nama Lengkap</label>
        <input class="form-input <?= isset($errors['nama']) ? 'is-invalid' : '' ?>"
               type="text" name="nama" id="nama"
               placeholder="Tuliskan disini..."
               value="<?= e($saved['nama'] ?? '') ?>">
        <?php if (isset($errors['nama'])): ?>
          <small style="color:var(--red);font-size:11px"><?= e($errors['nama']) ?></small>
        <?php endif; ?>
      </div>

      <!-- Email -->
      <div class="form-group">
        <label class="form-label" for="email">Email</label>
        <input class="form-input <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
               type="email" name="email" id="email"
               placeholder="Cth: pegawaiqris@gmail.com"
               value="<?= e($saved['email'] ?? '') ?>">
        <?php if (isset($errors['email'])): ?>
          <small style="color:var(--red);font-size:11px"><?= e($errors['email']) ?></small>
        <?php endif; ?>
      </div>

      <!-- Nomor Telepon -->
      <div class="form-group">
        <label class="form-label" for="no_telepon">Nomor Telepon</label>
        <input class="form-input <?= isset($errors['no_telepon']) ? 'is-invalid' : '' ?>"
               type="tel" name="no_telepon" id="no_telepon"
               placeholder="Cth: 081234567890"
               value="<?= e($saved['telp'] ?? '') ?>">
        <?php if (isset($errors['no_telepon'])): ?>
          <small style="color:var(--red);font-size:11px"><?= e($errors['no_telepon']) ?></small>
        <?php endif; ?>
      </div>

      <!-- Buat Password -->
      <div class="form-group">
        <label class="form-label" for="password">Buat Password</label>
        <input class="form-input <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
               type="password" name="password" id="password"
               placeholder="••••••••">
        <?php if (isset($errors['password'])): ?>
          <small style="color:var(--red);font-size:11px"><?= e($errors['password']) ?></small>
        <?php endif; ?>
      </div>

      <!-- Konfirmasi Password -->
      <div class="form-group">
        <label class="form-label" for="konfirmasi">Konfirmasi Password</label>
        <input class="form-input <?= isset($errors['konfirmasi']) ? 'is-invalid' : '' ?>"
               type="password" name="konfirmasi" id="konfirmasi"
               placeholder="••••••••">
        <?php if (isset($errors['konfirmasi'])): ?>
          <small style="color:var(--red);font-size:11px"><?= e($errors['konfirmasi']) ?></small>
        <?php endif; ?>
      </div>

      <!-- Jabatan -->
      <div class="form-group">
        <label class="form-label" for="jabatan">Jabatan</label>
        <div class="form-select-wrap">
          <select name="jabatan" id="jabatan">
            <?php foreach (['kasir'=>'Kasir','manajer'=>'Manajer','staff'=>'Staff'] as $val => $lbl): ?>
            <option value="<?= $val ?>"
                    <?= (($saved['jabatan'] ?? 'kasir') === $val) ? 'selected' : '' ?>>
              <?= $lbl ?>
            </option>
            <?php endforeach; ?>
          </select>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--gray)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 12 15 18 9"/>
          </svg>
        </div>
      </div>

    </div><!-- /form-body -->

    <!-- SUBMIT -->
    <div class="form-foot">
      <button type="submit" class="btn-red" id="btnSimpan">Simpan</button>
    </div>
  </form>

</div><!-- /content -->

<!-- PILIH LOKASI SHEET (samain desain dgn Ubah Lokasi di halaman Laporan) -->
<div class="overlay" id="ovLokasiPegawai" onclick="closeOv(event,'ovLokasiPegawai')">
  <div class="sheet">
    <div class="handle"></div>
    <div class="sheet-title">Pilih Lokasi</div>
    <div class="search-row">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--gray2)" stroke-width="2"
        stroke-linecap="round" stroke-linejoin="round">
        <circle cx="11" cy="11" r="8" />
        <line x1="21" y1="21" x2="16.65" y2="16.65" />
      </svg>
      <input type="text" placeholder="Cari lokasi..." id="tokoSearch" oninput="filterTokoPegawai(this.value)">
    </div>
    <div class="loc-list" id="tokoLocList" style="padding-bottom:16px"></div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script src="assets/js/main.js"></script>
<script>
// ─── PILIH LOKASI SHEET (Akses Lokasi) ────────────
const ALL_TOKO_PEGAWAI = <?= json_encode($allToko) ?>;
let selTokoPegawai = <?= json_encode((int)($saved['tokoId'] ?? 0)) ?>;

function openTokoSheet() {
  document.getElementById('tokoSearch').value = '';
  renderTokoPegawai('');
  openOv('ovLokasiPegawai');
}

function renderTokoPegawai(filter) {
  const list = document.getElementById('tokoLocList');
  if (!list) return;
  const f = (filter || '').toLowerCase();
  list.innerHTML = ALL_TOKO_PEGAWAI
    .filter(l => l.nama.toLowerCase().includes(f) || (l.alamat || '').toLowerCase().includes(f))
    .map(l => `
      <div class="loc-item" onclick="pickTokoPegawai(${l.id}, '${l.nama.replace(/'/g,"\\'")}', '${(l.alamat||'').replace(/'/g,"\\'")}')">
        <div>
          <div class="loc-name">${l.nama}</div>
          <div class="loc-addr2">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
            </svg>
            ${l.alamat || ''}
          </div>
        </div>
        ${selTokoPegawai === parseInt(l.id, 10)
          ? `<div class="loc-check">
               <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                 <polyline points="20 6 9 17 4 12"/>
               </svg>
             </div>`
          : ''}
      </div>`).join('');
}

function filterTokoPegawai(val) {
  renderTokoPegawai(val);
}

function pickTokoPegawai(id, nama, alamat) {
  selTokoPegawai = parseInt(id, 10);
  document.getElementById('toko_id').value = selTokoPegawai;
  const label = document.getElementById('tokoLabel');
  label.textContent = `${nama} – ${alamat}`;
  label.classList.remove('placeholder');
  clearTokoError();
  closeOvById('ovLokasiPegawai');
}

function clearTokoError() {
  const trigger = document.getElementById('tokoTrigger');
  trigger.classList.remove('is-invalid');
  const hint = document.getElementById('tokoErrorHint');
  if (hint) hint.textContent = '';
}

// ─── Client-side validation sebelum submit ────────
document.getElementById('formPegawai').addEventListener('submit', function(e) {
  const pass  = document.getElementById('password').value;
  const konf  = document.getElementById('konfirmasi').value;
  const toko  = document.getElementById('toko_id').value;
  const nama  = document.getElementById('nama').value.trim();
  const telp  = document.getElementById('no_telepon').value.trim();

  if (!toko) {
    e.preventDefault();
    highlightToko('Pilih akses lokasi terlebih dahulu');
    return;
  }
  if (!nama) {
    e.preventDefault();
    highlight('nama', 'Nama wajib diisi');
    return;
  }
  if (!telp || !/^[0-9]+$/.test(telp)) {
    e.preventDefault();
    highlight('no_telepon', 'Nomor telepon wajib diisi, hanya angka');
    return;
  }
  if (pass.length < 8 || pass.length > 16) {
    e.preventDefault();
    highlight('password', 'Password harus 8-16 karakter');
    return;
  }
  if (pass !== konf) {
    e.preventDefault();
    highlight('konfirmasi', 'Password tidak cocok');
    return;
  }

  // Disable button untuk cegah double submit
  document.getElementById('btnSimpan').disabled = true;
  document.getElementById('btnSimpan').textContent = 'Menyimpan...';
});

function highlightToko(msg) {
  const trigger = document.getElementById('tokoTrigger');
  trigger.classList.add('is-invalid');
  let hint = document.getElementById('tokoErrorHint');
  if (!hint) {
    hint = document.createElement('small');
    hint.id = 'tokoErrorHint';
    hint.style.cssText = 'color:var(--red);font-size:11px';
    trigger.insertAdjacentElement('afterend', hint);
  }
  hint.textContent = msg;
}

function highlight(fieldId, msg) {
  const el = document.getElementById(fieldId);
  if (!el) return;
  el.classList.add('is-invalid');
  el.focus();
  // Tampilkan pesan error inline
  let hint = el.nextElementSibling;
  if (!hint || hint.tagName !== 'SMALL') {
    hint = document.createElement('small');
    hint.style.cssText = 'color:var(--red);font-size:11px';
    el.parentNode.insertBefore(hint, el.nextSibling);
  }
  hint.textContent = msg;
  el.addEventListener('input', () => {
    el.classList.remove('is-invalid');
    hint.textContent = '';
  }, { once: true });
}
</script>
