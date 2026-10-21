<?php
// Export PDF "Laporan Ringkasan QRIS". Filter sama dengan report_data.php.
// Respons: { success, url_document } — URL file PDF yang bisa diunduh aplikasi.

require_once __DIR__ . '/includes/dummy.php';
require_once __DIR__ . '/includes/report_pdf.php';

header('Content-Type: application/json; charset=UTF-8');

const EXPORT_DIR = __DIR__ . '/exports';
const EXPORT_TTL_SECONDS = 86400; // file lama dihapus setelah 1 hari

$period = trim((string)($_GET['period'] ?? 'kemarin'));
$tokoId = isset($_GET['toko_id']) ? (int)$_GET['toko_id'] : null;
$dateFrom = isset($_GET['date_from']) ? trim((string)$_GET['date_from']) : null;
$dateTo = isset($_GET['date_to']) ? trim((string)$_GET['date_to']) : null;

if (!in_array($period, ['kemarin', 'bulan', '7', '30', 'atur'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Filter periode tidak valid.']);
    exit;
}

if ($period === 'atur' && ($dateFrom === null || $dateTo === null || $dateFrom === '' || $dateTo === '')) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tanggal awal dan akhir wajib diisi.']);
    exit;
}

try {
    $payload = buildReportPayload($dbLocations ?? [], $DUMMY, $tokoId, $period, $dateFrom, $dateTo);
    if (empty($dbLocations)) {
        $payload = reportPdfApplyDummy($payload, $DUMMY); // TODO: hapus saat data asli tersedia
    }
    $pdf = buildReportSummaryPdf($payload);

    if (!is_dir(EXPORT_DIR) && !mkdir(EXPORT_DIR, 0755, true)) {
        throw new RuntimeException('Folder export tidak bisa dibuat.');
    }

    foreach (glob(EXPORT_DIR . '/*.pdf') ?: [] as $old) {
        if (filemtime($old) < time() - EXPORT_TTL_SECONDS) {
            @unlink($old);
        }
    }

    // Nama acak: file berisi data keuangan merchant, jadi tidak boleh bisa ditebak
    $fileName = 'laporan-ringkasan-' . date('Ymd-His') . '-' . bin2hex(random_bytes(12)) . '.pdf';
    if (file_put_contents(EXPORT_DIR . '/' . $fileName, $pdf) === false) {
        throw new RuntimeException('File PDF tidak bisa disimpan.');
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    $basePath = rtrim(str_replace('\\', '/', dirname((string)($_SERVER['SCRIPT_NAME'] ?? ''))), '/');
    $url = ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
        . $basePath . '/exports/' . rawurlencode($fileName);

    echo json_encode(['success' => true, 'url_document' => $url], JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal membuat PDF.']);
}
