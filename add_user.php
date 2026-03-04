<?php
require_once __DIR__ . '/includes/dummy.php';

$pageTitle = 'Buat Akun Pegawai – InterActive QRIS';
$activeTab = 'pegawai';

$allToko = $DUMMY['toko'];

// ─── Handle form submit ───────────────────────────
$errors  = [];
$success = false;
$saved   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokoId  = (int)($_POST['toko_id']     ?? 0);
    $nama    = trim($_POST['nama']          ?? '');
    $email   = trim($_POST['email']         ?? '');
    $telp    = trim($_POST['no_telepon']    ?? '');
    $pass    = $_POST['password']           ?? '';
    $konfirm = $_POST['konfirmasi']         ?? '';
    $jabatan = $_POST['jabatan']            ?? 'kasir';

    // Validasi server-side
    if (!$tokoId)
        $errors['toko_id']   = 'Pilih akses lokasi.';
    if (empty($nama))
        $errors['nama']      = 'Nama lengkap wajib diisi.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors['email']     = 'Format email tidak valid.';
    if (strlen($pass) < 6)
        $errors['password']  = 'Password minimal 6 karakter.';
    if ($pass !== $konfirm)
        $errors['konfirmasi']= 'Konfirmasi password tidak cocok.';
    if (!in_array($jabatan, ['owner','kasir','manajer','staff']))
        $errors['jabatan']   = 'Jabatan tidak valid.';

    if (empty($errors)) {
        // Tanpa DB: simpan sementara ke session sebagai simulasi
        session_start();
        $_SESSION['pegawai_baru'][] = [
            'nama'    => $nama,
            'email'   => $email,
            'jabatan' => $jabatan,
            'toko_id' => $tokoId,
            'status'  => 'aktif',
        ];
        $success = true;
        $saved   = compact('nama','email','jabatan','telp');
    } else {
        // Kembalikan nilai agar tidak hilang saat error
        $saved = compact('tokoId','nama','email','telp','jabatan');
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="content">

  <!-- FORM HEADER -->
  <div class="form-header fi">
    <a href="list.php" class="back-btn" style="position:static">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="15 18 9 12 15 6"/>
      </svg>
    </a>
    <span class="form-header-title">Akun Pegawai</span>
  </div>

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
    Mohon periksa kembali data yang diisi.
  </div>
  <?php endif; ?>

  <!-- FORM -->
  <form method="POST" action="add_user.php" id="formPegawai" novalidate>
    <div class="form-body">

      <!-- Akses Lokasi -->
      <div class="form-group">
        <label class="form-label" for="toko_id">Akses Lokasi</label>
        <div class="form-select-wrap">
          <select name="toko_id" id="toko_id"
                  class="<?= isset($errors['toko_id']) ? 'is-invalid' : '' ?>">
            <option value="" disabled <?= empty($saved['tokoId']) ? 'selected' : '' ?> hidden>
              Pilih akses lokasi
            </option>
            <?php foreach ($allToko as $t): ?>
            <option value="<?= (int)$t['id'] ?>"
                    <?= (isset($saved['tokoId']) && $saved['tokoId'] == $t['id']) ? 'selected' : '' ?>>
              <?= e($t['nama']) ?> – <?= e($t['alamat']) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--gray)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 12 15 18 9"/>
          </svg>
        </div>
        <?php if (isset($errors['toko_id'])): ?>
          <small style="color:var(--red);font-size:11px"><?= e($errors['toko_id']) ?></small>
        <?php endif; ?>
      </div>

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
        <input class="form-input"
               type="tel" name="no_telepon" id="no_telepon"
               placeholder="Cth: 081234567890"
               value="<?= e($saved['telp'] ?? '') ?>">
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
            <?php foreach (['kasir'=>'Kasir','owner'=>'Owner','manajer'=>'Manajer','staff'=>'Staff'] as $val => $lbl): ?>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script src="assets/js/main.js"></script>
<script>
// Client-side validation sebelum submit
document.getElementById('formPegawai').addEventListener('submit', function(e) {
  const pass  = document.getElementById('password').value;
  const konf  = document.getElementById('konfirmasi').value;
  const toko  = document.getElementById('toko_id').value;
  const nama  = document.getElementById('nama').value.trim();

  if (!toko) {
    e.preventDefault();
    highlight('toko_id', 'Pilih akses lokasi terlebih dahulu');
    return;
  }
  if (!nama) {
    e.preventDefault();
    highlight('nama', 'Nama wajib diisi');
    return;
  }
  if (pass.length < 6) {
    e.preventDefault();
    highlight('password', 'Password minimal 6 karakter');
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
