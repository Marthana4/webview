<?php

require_once __DIR__ . '/includes/dummy.php';

header('Content-Type: application/json; charset=UTF-8');

$period = trim((string)($_GET['period'] ?? 'kemarin'));
$tokoId = isset($_GET['toko_id']) ? (int)$_GET['toko_id'] : null;
$dateFrom = isset($_GET['date_from']) ? trim((string)$_GET['date_from']) : null;
$dateTo = isset($_GET['date_to']) ? trim((string)($_GET['date_to'] ?? '')) : null;

$allowedPeriods = ['kemarin', 'bulan', '7', '30', 'atur'];
if (!in_array($period, $allowedPeriods, true)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Filter periode tidak valid.',
    ]);
    exit;
}

if ($period === 'atur' && ($dateFrom === null || $dateTo === null || $dateFrom === '' || $dateTo === '')) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Tanggal awal dan akhir wajib diisi.',
    ]);
    exit;
}

$payload = buildReportPayload($dbLocations ?? [], $DUMMY, $tokoId, $period, $dateFrom, $dateTo);

echo json_encode([
    'success' => true,
    'data' => $payload,
], JSON_UNESCAPED_UNICODE);
