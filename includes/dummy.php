<?php
// ─── DUMMY DATA ───────────────────────────────────────────────────────────────
// Semua data statis, tanpa koneksi database

$DUMMY = [

    // ── TOKO ──────────────────────────────────────
    'toko' => [
        ['id' => 1, 'nama' => 'InterActive Cafe',  'alamat' => 'Dukuh kupang timur 123'],
        ['id' => 2, 'nama' => 'Bakso Panggang',    'alamat' => 'Jl. Keouth Gg. 5 Sehat'],
        ['id' => 3, 'nama' => 'Cozy Corner Cafe',  'alamat' => '123 Cozy Lane'],
        ['id' => 4, 'nama' => 'Sunrise Coffee',    'alamat' => '789 Sunrise Blvd'],
        ['id' => 5, 'nama' => 'Green Bean Bistro', 'alamat' => '456 Bean Street'],
        ['id' => 6, 'nama' => 'Oceanview Java',    'alamat' => '321 Ocean Drive'],
    ],

    // ── TOKO AKTIF ────────────────────────────────
    'toko_aktif' => [
        'id'     => 1,
        'nama'   => 'InterActive Cafe',
        'alamat' => 'Dukuh kupang timur 123',
    ],

    // ── TRANSAKSI HARI INI ─────────────────────────
    'transaksi_hari_ini' => [
        'total_nilai'      => 250000,
        'jumlah_transaksi' => 12,
    ],

    // ── LAPORAN PERIODE ───────────────────────────
    'laporan' => [
        'periode_str'  => '15 Jan 2024 – 15 Jan 2024',
        'total_tx'     => 500,
        'pct_tx'       => 20,
        'avg_harian'   => 2000000,
        'pct_avg'      => 20,
        'saldo_cair'   => 250000000,
        'pct_saldo'    => 20,
    ],

    // ── KLAIM ─────────────────────────────────────
    'klaim' => [
        'user'     => 10,
        'merchant' => 2,
    ],

    // ── CHART LINE (transaksi per tanggal) ────────
    'chart' => [
        'values' => [4000000, 2000000, 2000000, 2000000, 4000000, 1200000, 2000000],
        'labels' => ['10',    '11',    '12',    '13',    '14',    '15',    '16'],
    ],

    // ── BAR CHART (jam 0-23) ──────────────────────
    'jam' => [
        'values'  => [2,2,2,2,3,4,5,8,12,18,28,42,65,80,78,82,88,92,90,95,87,72,50,30],
        'labels'  => ['','','','3','','','','7','','','','11','','','','15','','','','19','','','','23'],
        'jam_fav' => 'Senin',
    ],

    // ── PEGAWAI ───────────────────────────────────
    'pegawai' => [
        ['id'=>1,'nama'=>'Irfan Ibrahim',   'jabatan'=>'owner',   'toko_nama'=>'InterActive Cafe', 'toko_alamat'=>'Dukuh kupang timur 123', 'email'=>'irfan@qris.id',    'no_telepon'=>'081234567890','status'=>'aktif'],
        ['id'=>2,'nama'=>'Floyd Miles',     'jabatan'=>'kasir',   'toko_nama'=>'InterActive Cafe', 'toko_alamat'=>'Dukuh kupang timur 123', 'email'=>'floyd@qris.id',    'no_telepon'=>'081234567891','status'=>'aktif'],
        ['id'=>3,'nama'=>'Esther Howard',   'jabatan'=>'kasir',   'toko_nama'=>'InterActive Cafe', 'toko_alamat'=>'Dukuh kupang timur 123', 'email'=>'esther@qris.id',   'no_telepon'=>'081234567892','status'=>'aktif'],
        ['id'=>4,'nama'=>'Marvin McKinney', 'jabatan'=>'staff',   'toko_nama'=>'InterActive Cafe', 'toko_alamat'=>'Dukuh kupang timur 123', 'email'=>'marvin@qris.id',   'no_telepon'=>'081234567893','status'=>'aktif'],
        ['id'=>5,'nama'=>'Courtney Henry',  'jabatan'=>'kasir',   'toko_nama'=>'InterActive Cafe', 'toko_alamat'=>'Dukuh kupang timur 123', 'email'=>'courtney@qris.id', 'no_telepon'=>'081234567894','status'=>'aktif'],
        ['id'=>6,'nama'=>'Budi Santoso',    'jabatan'=>'kasir',   'toko_nama'=>'Bakso Panggang',   'toko_alamat'=>'Jl. Keouth Gg. 5 Sehat','email'=>'budi@qris.id',     'no_telepon'=>'081234567895','status'=>'aktif'],
    ],

];

// ─── HELPER FUNCTIONS ─────────────────────────────────────────────────────────

function e(mixed $val): string {
    return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
}

function fmtRp(int $val): string {
    return 'Rp ' . number_format($val, 0, ',', '.');
}
