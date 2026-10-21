<?php
// PDF "Laporan Ringkasan QRIS" (A4 portrait) dengan FPDF 1.84 — versi yang sama
// dengan yang dipakai dashboard QRIS lain (m/php-plugins/fpdf).

require_once __DIR__ . '/lib/fpdf/fpdf.php';

class ReportSummaryPdf extends FPDF
{
    // Warna desain (RGB)
    const RED = [232, 25, 44];
    const DARK = [17, 24, 39];
    const GRAY = [140, 140, 140];
    const LIGHT = [229, 231, 235];
    const GREEN = [30, 140, 58];

    public function setRgb(string $what, array $rgb): void
    {
        [$r, $g, $b] = $rgb;
        if ($what === 'text') {
            $this->SetTextColor($r, $g, $b);
        } elseif ($what === 'fill') {
            $this->SetFillColor($r, $g, $b);
        } else {
            $this->SetDrawColor($r, $g, $b);
        }
    }

    /** Teks UTF-8 → cp1252 (font inti FPDF). */
    public static function t(string $s): string
    {
        $out = @iconv('UTF-8', 'windows-1252//TRANSLIT', $s);
        return $out === false ? $s : $out;
    }

    public function txt(float $x, float $y, string $s, string $align = 'L'): void
    {
        $s = self::t($s);
        if ($align === 'R') {
            $x -= $this->GetStringWidth($s);
        } elseif ($align === 'C') {
            $x -= $this->GetStringWidth($s) / 2;
        }
        $this->Text($x, $y, $s);
    }

    public function font(string $style, float $size, array $rgb): void
    {
        $this->SetFont('Helvetica', $style, $size);
        $this->setRgb('text', $rgb);
    }

    public function RoundedRect(float $x, float $y, float $w, float $h, float $r, string $style = 'D'): void
    {
        $k = $this->k;
        $hp = $this->h;
        $op = $style === 'F' ? 'f' : (in_array($style, ['FD', 'DF'], true) ? 'B' : 'S');
        $arc = 4 / 3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m', ($x + $r) * $k, ($hp - $y) * $k));
        $xc = $x + $w - $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - $y) * $k));
        $this->arcTo($xc + $r * $arc, $yc - $r, $xc + $r, $yc - $r * $arc, $xc + $r, $yc);
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - $yc) * $k));
        $this->arcTo($xc + $r, $yc + $r * $arc, $xc + $r * $arc, $yc + $r, $xc, $yc + $r);
        $xc = $x + $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - ($y + $h)) * $k));
        $this->arcTo($xc - $r * $arc, $yc + $r, $xc - $r, $yc + $r * $arc, $xc - $r, $yc);
        $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', $x * $k, ($hp - $yc) * $k));
        $this->arcTo($xc - $r, $yc - $r * $arc, $xc - $r * $arc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    private function arcTo(float $x1, float $y1, float $x2, float $y2, float $x3, float $y3): void
    {
        $h = $this->h;
        $k = $this->k;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c', $x1 * $k, ($h - $y1) * $k, $x2 * $k, ($h - $y2) * $k, $x3 * $k, ($h - $y3) * $k));
    }

    public function Circle(float $x, float $y, float $r, string $style = 'D'): void
    {
        $op = $style === 'F' ? 'f' : (in_array($style, ['FD', 'DF'], true) ? 'B' : 'S');
        $l = 4 / 3 * (M_SQRT2 - 1) * $r;
        $k = $this->k;
        $h = $this->h;
        $p = static fn($px, $py) => sprintf('%.2F %.2F', $px * $k, ($h - $py) * $k);
        $this->_out($p($x + $r, $y) . ' m ' . $p($x + $r, $y - $l) . ' ' . $p($x + $l, $y - $r) . ' ' . $p($x, $y - $r) . ' c');
        $this->_out($p($x - $l, $y - $r) . ' ' . $p($x - $r, $y - $l) . ' ' . $p($x - $r, $y) . ' c');
        $this->_out($p($x - $r, $y + $l) . ' ' . $p($x - $l, $y + $r) . ' ' . $p($x, $y + $r) . ' c');
        $this->_out($p($x + $l, $y + $r) . ' ' . $p($x + $r, $y + $l) . ' ' . $p($x + $r, $y) . ' c ' . $op);
    }

    /** @param float[] $pts [x1, y1, x2, y2, ...] */
    public function Polygon(array $pts, string $style = 'D'): void
    {
        $op = $style === 'F' ? 'f' : (in_array($style, ['FD', 'DF'], true) ? 'b' : 's');
        $out = '';
        for ($i = 0, $n = count($pts); $i < $n; $i += 2) {
            $out .= sprintf('%.2F %.2F %s ', $pts[$i] * $this->k, ($this->h - $pts[$i + 1]) * $this->k, $i === 0 ? 'm' : 'l');
        }
        $this->_out($out . $op);
    }

    public function SetDash(?float $on = null, ?float $off = null): void
    {
        $this->_out($on === null ? '[] 0 d' : sprintf('[%.3F %.3F] 0 d', $on * $this->k, $off * $this->k));
    }

    /** Panah naik/turun kecil (pengganti karakter ↑ yang tidak ada di cp1252). */
    public function arrow(float $x, float $y, bool $up, array $rgb): void
    {
        $this->setRgb('draw', $rgb);
        $this->SetLineWidth(0.3);
        $dir = $up ? -1 : 1;
        $this->Line($x, $y - 1.4 * $dir, $x, $y + 1.4 * $dir);
        $this->Line($x, $y + 1.4 * $dir, $x - 0.9, $y + 0.5 * $dir);
        $this->Line($x, $y + 1.4 * $dir, $x + 0.9, $y + 0.5 * $dir);
    }
}

function reportPdfShort(float $v): string
{
    if ($v >= 1e9) {
        return rtrim(rtrim(number_format($v / 1e9, 1, ',', ''), '0'), ',') . ' M';
    }
    if ($v >= 1e6) {
        return rtrim(rtrim(number_format($v / 1e6, 1, ',', ''), '0'), ',') . ' jt';
    }
    if ($v >= 1e3) {
        return round($v / 1e3) . ' rb';
    }
    return (string)round($v);
}

/** Langkah sumbu-Y "rapi" (1, 2, 2.5, 5 × 10^n) untuk 5 garis. */
function reportPdfNiceStep(float $max): float
{
    if ($max <= 0) {
        return 1;
    }
    $raw = $max / 5;
    $pow = 10 ** floor(log10($raw));
    foreach ([1, 2, 2.5, 5, 10] as $m) {
        if ($m * $pow >= $raw) {
            return $m * $pow;
        }
    }
    return 10 * $pow;
}

/**
 * Data tambahan yang belum punya sumber (TODO): jam favorit per hari saat
 * payload hanya punya satu hari, dan ringkasan kehadiran karyawan.
 */
function reportPdfDummyExtras(array $payload): array
{
    $days = $payload['jam']['days'] ?? [];
    if (empty($days)) {
        $base = $payload['jam']['values'] ?? array_fill(0, 24, 0);
        $names = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        foreach ($names as $d => $name) {
            $values = [];
            foreach ($base as $h => $v) {
                $values[] = (int)round($v * (0.6 + 0.08 * (($d * 7 + $h) % 6)));
            }
            $days[] = ['label' => $name, 'values' => $values];
        }
    }

    return [
        'days' => $days,
        'kehadiran' => [
            'tanggal' => date('Y-m-d'),
            'total_karyawan' => 50,
            'hadir' => 46,
            'izin' => 4,
            'terlambat' => 2,
            'alpha' => 2,
        ],
    ];
}

/**
 * Mode dummy (tanpa lokasi/DB): angka laporan semuanya 0, jadi PDF diisi data
 * contoh dari $DUMMY supaya tampilannya bisa dicek. Merchant asli tidak lewat sini.
 */
function reportPdfApplyDummy(array $payload, array $dummy): array
{
    $payload['lap'] = array_merge($payload['lap'] ?? [], array_intersect_key($dummy['laporan'], array_flip([
        'total_tx', 'pct_tx', 'avg_harian', 'pct_avg', 'saldo_cair', 'pct_saldo',
    ])));
    foreach (['pct_tx_display', 'pct_avg_display', 'pct_saldo_display'] as $key) {
        unset($payload['lap'][$key]);
    }

    $pattern = [2000000, 4000000, 1000000, 4900000, 3000000, 2000000, 3000000, 2700000, 1000000, 4000000, 4000000, 2000000, 2000000, 3900000, 1000000, 2000000];
    $labels = $payload['chart']['labels'] ?? [];
    $payload['chart']['values'] = array_map(
        static fn($i) => $pattern[$i % count($pattern)],
        array_keys($labels)
    );

    unset($payload['jam']['days']);
    $payload['jam']['values'] = $dummy['jam']['values'];

    return $payload;
}

/** @return string isi file PDF */
function buildReportSummaryPdf(array $payload): string
{
    $extras = reportPdfDummyExtras($payload);

    $pdf = new ReportSummaryPdf('P', 'mm', 'A4');
    $pdf->SetTitle('Laporan Ringkasan QRIS');
    $pdf->SetAuthor('InterActive QRIS');
    $pdf->SetAutoPageBreak(false);
    $pdf->SetMargins(12.7, 12.7, 12.7);
    $pdf->AddPage();

    $L = 12.7;
    $R = 210 - 12.7;
    $W = $R - $L;

    // ── Header ──────────────────────────────────────
    $logo = __DIR__ . '/../assets/images/logo.png';
    if (is_file($logo)) {
        $pdf->Image($logo, $L, 11, 36);
    }

    $pdf->font('B', 17, ReportSummaryPdf::DARK);
    $pdf->txt($L, 33, 'Laporan Ringkasan QRIS');

    $toko = $payload['toko'] ?? [];
    $pdf->font('', 9.5, ReportSummaryPdf::GRAY);
    $pdf->txt($L, 40.5, trim(($toko['nama'] ?? '') . ' · ' . ($toko['alamat'] ?? ''), ' ·'));

    $periode = 'Periode: ' . str_replace(' - ', ' – ', (string)($payload['lap']['periode_str'] ?? '-'));
    $pdf->SetFont('Helvetica', '', 9);
    $pw = $pdf->GetStringWidth(ReportSummaryPdf::t($periode)) + 10;
    $pdf->setRgb('fill', [243, 244, 246]);
    $pdf->RoundedRect($R - $pw, 31.5, $pw, 10, 2.5, 'F');
    $pdf->font('', 9, ReportSummaryPdf::DARK);
    $pdf->txt($R - $pw / 2, 37.8, $periode, 'C');

    $pdf->setRgb('draw', ReportSummaryPdf::LIGHT);
    $pdf->SetLineWidth(0.2);
    $pdf->Line($L, 48, $R, 48);

    // ── Kartu statistik ─────────────────────────────
    $lap = $payload['lap'] ?? [];
    $stats = [
        ['Total transaksi', number_format((int)($lap['total_tx'] ?? 0), 0, ',', '.'), (int)($lap['pct_tx'] ?? 0), $lap['pct_tx_display'] ?? null],
        ['Rata-rata nilai transaksi harian', fmtRp((int)($lap['avg_harian'] ?? 0)), (int)($lap['pct_avg'] ?? 0), $lap['pct_avg_display'] ?? null],
        ['Total saldo dicairkan', fmtRp((int)($lap['saldo_cair'] ?? 0)), (int)($lap['pct_saldo'] ?? 0), $lap['pct_saldo_display'] ?? null],
    ];
    $gap = 5;
    $cw = ($W - 2 * $gap) / 3;
    foreach ($stats as $i => [$label, $value, $pct, $pctText]) {
        $x = $L + $i * ($cw + $gap);
        $pdf->setRgb('draw', ReportSummaryPdf::LIGHT);
        $pdf->setRgb('fill', [255, 255, 255]);
        $pdf->RoundedRect($x, 54.5, $cw, 19.5, 2.5, 'D');
        $pdf->font('', 8, ReportSummaryPdf::GRAY);
        $pdf->txt($x + 4.5, 61.5, $label);
        $pdf->font('B', 12, ReportSummaryPdf::DARK);
        $pdf->txt($x + 4.5, 69.5, $value);

        $up = $pct >= 0;
        $color = $up ? ReportSummaryPdf::GREEN : ReportSummaryPdf::RED;
        $label = $pctText ?: abs($pct) . '%';
        $pdf->font('B', 8.5, $color);
        $pdf->txt($x + $cw - 4.5, 69.3, $label, 'R');
        $pdf->arrow($x + $cw - 4.5 - $pdf->GetStringWidth(ReportSummaryPdf::t($label)) - 1.8, 68.2, $up, $color);
    }

    // ── Grafik transaksi ────────────────────────────
    $pdf->font('B', 11, ReportSummaryPdf::DARK);
    $pdf->txt($L, 84, 'Grafik transaksi QRIS');
    $pdf->font('', 8.5, ReportSummaryPdf::GRAY);
    $pdf->txt($L, 89.5, 'Nilai transaksi berdasarkan tanggal');

    $vals = array_map('floatval', $payload['chart']['values'] ?? []);
    $lbls = $payload['chart']['labels'] ?? [];
    $px1 = $L + 8;
    $px2 = $R - 3;
    $py1 = 96;
    $py2 = 139;
    $step = reportPdfNiceStep(max($vals ?: [0]));
    $top = $step * 5;

    $pdf->SetLineWidth(0.15);
    $pdf->setRgb('draw', ReportSummaryPdf::LIGHT);
    $pdf->SetDash(0.6, 0.8);
    $pdf->font('', 7, ReportSummaryPdf::GRAY);
    for ($i = 1; $i <= 5; $i++) {
        $gy = $py2 - ($py2 - $py1) * $i / 5;
        $pdf->Line($px1, $gy, $px2, $gy);
        $pdf->txt($px1 - 2, $gy + 1, reportPdfShort($step * $i), 'R');
    }
    $pdf->SetDash();

    $n = count($vals);
    if ($n > 0) {
        $xAt = static fn($i) => $n === 1 ? ($px1 + $px2) / 2 : $px1 + 3 + ($px2 - $px1 - 6) * $i / ($n - 1);
        $yAt = static fn($v) => $py2 - ($py2 - $py1) * min($v, $top) / $top;

        $area = [$xAt(0), $py2];
        foreach ($vals as $i => $v) {
            array_push($area, $xAt($i), $yAt($v));
        }
        array_push($area, $xAt($n - 1), $py2);
        $pdf->setRgb('fill', [251, 225, 225]);
        $pdf->Polygon($area, 'F');

        $pdf->setRgb('draw', ReportSummaryPdf::RED);
        $pdf->SetLineWidth(0.35);
        for ($i = 1; $i < $n; $i++) {
            $pdf->Line($xAt($i - 1), $yAt($vals[$i - 1]), $xAt($i), $yAt($vals[$i]));
        }

        $pdf->SetLineWidth(0.4);
        $pdf->setRgb('draw', [255, 255, 255]);
        $pdf->setRgb('fill', ReportSummaryPdf::RED);
        foreach ($vals as $i => $v) {
            $pdf->Circle($xAt($i), $yAt($v), 1.2, 'FD');
        }

        // Label sumbu-X: maksimal ±16 label supaya tidak bertumpuk
        $every = max(1, (int)ceil($n / 16));
        $pdf->font('', 7, ReportSummaryPdf::GRAY);
        foreach ($lbls as $i => $lbl) {
            if ($i % $every === 0 || $i === $n - 1) {
                $pdf->txt($xAt($i), $py2 + 5, (string)$lbl, 'C');
            }
        }
    }
    $pdf->font('', 7.5, ReportSummaryPdf::GRAY);
    $pdf->txt(($px1 + $px2) / 2, $py2 + 9.5, 'Tanggal', 'C');

    // ── Jam favorit ─────────────────────────────────
    $pdf->font('B', 11, ReportSummaryPdf::DARK);
    $pdf->txt($L, 160, 'Jam favorit');
    $pdf->font('', 8.5, ReportSummaryPdf::GRAY);
    $pdf->txt($L, 165.5, 'Distribusi per jam · skala batang sama untuk seluruh hari');

    $days = array_slice($extras['days'], 0, 7);
    $maxJam = 1;
    foreach ($days as $d) {
        $maxJam = max($maxJam, max($d['values'] ?: [0]));
    }
    $bx1 = $L + 21;
    $slot = ($R - $bx1) / 24;
    $rowH = 9;
    foreach ($days as $r => $d) {
        $base = 177 + $r * $rowH;
        $pdf->font('', 8, ReportSummaryPdf::DARK);
        $pdf->txt($L, $base - 4.5, (string)$d['label']);
        $pdf->setRgb('fill', ReportSummaryPdf::RED);
        foreach (array_slice($d['values'], 0, 24) as $h => $v) {
            $bh = max(0.25, 7 * $v / $maxJam);
            $pdf->Rect($bx1 + $h * $slot + 0.35, $base - $bh, $slot - 0.7, $bh, 'F');
        }
    }
    $pdf->font('', 7, ReportSummaryPdf::GRAY);
    $hourY = 177 + 7 * $rowH - 3.5;
    $pdf->txt($L, $hourY, 'Jam');
    foreach ([3, 7, 11, 15, 19, 23] as $h) {
        $pdf->txt($bx1 + ($h + 0.5) * $slot, $hourY, (string)$h, 'C');
    }

    // ── Ringkasan harian (kehadiran) ────────────────
    $k = $extras['kehadiran'];
    $pdf->font('B', 11, ReportSummaryPdf::DARK);
    $pdf->txt($L, 248, 'Ringkasan harian');
    $pdf->font('', 8.5, ReportSummaryPdf::GRAY);
    $sub = fmtTglId($k['tanggal'], true, true);
    $pdf->txt($L, 253.5, $sub);
    $dotX = $L + $pdf->GetStringWidth(ReportSummaryPdf::t($sub)) + 2.5;
    $pdf->setRgb('fill', [209, 213, 219]);
    $pdf->Circle($dotX, 252.5, 0.6, 'F');
    $pdf->txt($dotX + 2.5, 253.5, 'Total karyawan: ' . $k['total_karyawan']);

    $hadirPct = $k['total_karyawan'] > 0 ? round($k['hadir'] / $k['total_karyawan'] * 100) : 0;
    $cards = [
        ['Hadir', $k['hadir'] . ' (' . $hadirPct . '%)'],
        ['Izin', (string)$k['izin']],
        ['Terlambat', (string)$k['terlambat']],
        ['Alpha', (string)$k['alpha']],
    ];
    $gap = 4.5;
    $cw = ($W - 3 * $gap) / 4;
    foreach ($cards as $i => [$label, $value]) {
        $x = $L + $i * ($cw + $gap);
        $pdf->setRgb('draw', ReportSummaryPdf::LIGHT);
        $pdf->SetLineWidth(0.2);
        $pdf->RoundedRect($x, 258, $cw, 19, 2.5, 'D');
        $pdf->font('', 8.5, ReportSummaryPdf::GRAY);
        $pdf->txt($x + 4.5, 265, $label);
        $pdf->font('B', 11.5, ReportSummaryPdf::DARK);
        $pdf->txt($x + 4.5, 272.5, $value);
    }

    // ── Footer ──────────────────────────────────────
    $pdf->setRgb('draw', ReportSummaryPdf::LIGHT);
    $pdf->Line($L, 279.5, $R, 279.5);
    $pdf->font('', 7.5, ReportSummaryPdf::GRAY);
    $pdf->txt($L, 284.5, 'InterActive QRIS · Laporan Ringkasan');
    $pdf->txt($R, 284.5, 'Halaman 1 dari 1', 'R');

    return $pdf->Output('S');
}
