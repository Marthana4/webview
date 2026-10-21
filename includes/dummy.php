<?php

require_once __DIR__ . '/auth.php';

// Preview lokal: auth dilewati hanya jika file penanda includes/.skip_auth ada.
// File itu di-.gitignore, jadi tidak pernah ikut ter-deploy.
$WEBVIEW_SKIP_AUTH = is_file(__DIR__ . '/.skip_auth');
$WEBVIEW_EXPECTS_JSON = strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';

if ($WEBVIEW_SKIP_AUTH) {
    $WEBVIEW_AUTH = ['status' => true, 'email' => '', 'token' => '', 'token_encoded' => '', 'source' => 'skip_auth'];
    $WEBVIEW_AUTH_EMAIL = '';
} else {
    $WEBVIEW_AUTH = webviewRequireAuth($WEBVIEW_EXPECTS_JSON);
    $WEBVIEW_AUTH_EMAIL = (string)($WEBVIEW_AUTH['email'] ?? '');

    $WEBVIEW_ACCOUNT_STATUS = webviewGetMerchantAccountStatus($WEBVIEW_AUTH_EMAIL);
    if (!$WEBVIEW_ACCOUNT_STATUS['active']) {
        webviewDenyInactiveAccount($WEBVIEW_EXPECTS_JSON, $WEBVIEW_ACCOUNT_STATUS['exists']);
    }
}

$defaultToko = [
    ['id' => 1, 'nama' => 'InterActive Cafe', 'alamat' => 'Dukuh kupang timur 123'],
    ['id' => 2, 'nama' => 'Bakso Panggang', 'alamat' => 'Jl. Keouth Gg. 5 Sehat'],
    ['id' => 3, 'nama' => 'Cozy Corner Cafe', 'alamat' => '123 Cozy Lane'],
    ['id' => 4, 'nama' => 'Sunrise Coffee', 'alamat' => '789 Sunrise Blvd'],
    ['id' => 5, 'nama' => 'Green Bean Bistro', 'alamat' => '456 Bean Street'],
    ['id' => 6, 'nama' => 'Oceanview Java', 'alamat' => '321 Ocean Drive'],
];

function getDbConnection(): ?mysqli
{
    static $conn = false;

    if ($conn !== false) {
        return $conn instanceof mysqli ? $conn : null;
    }

    $configPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '_configdb.php';
    if (!file_exists($configPath)) {
        $conn = null;
        return null;
    }

    try {
        require_once $configPath;
    } catch (Throwable $th) {
        $conn = null;
        return null;
    }

    if (!isset($conn_model) || !($conn_model instanceof mysqli)) {
        $conn = null;
        return null;
    }

    $conn = $conn_model;
    return $conn;
}

function webviewGetMerchantAccountStatus(string $email): array
{
    $conn = getDbConnection();
    if (!$conn || $email === '') {
        return ['exists' => false, 'active' => false];
    }

    $sql = "SELECT staticqr_paymentstat, rowStat
            FROM qris_merchant_loc
            WHERE memailaddr = ?
            ORDER BY mlocid DESC";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return ['exists' => false, 'active' => false];
    }

    mysqli_stmt_bind_param($stmt, 's', $email);
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return ['exists' => false, 'active' => false];
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        mysqli_stmt_close($stmt);
        return ['exists' => false, 'active' => false];
    }

    $exists = false;
    $active = false;
    while ($row = mysqli_fetch_assoc($result)) {
        $exists = true;
        $paymentStat = trim((string)($row['staticqr_paymentstat'] ?? ''));
        $rowStat = trim((string)($row['rowStat'] ?? ''));
        if ($paymentStat === 'paid' && ($rowStat === '' || $rowStat === 'real')) {
            $active = true;
            break;
        }
    }

    mysqli_free_result($result);
    mysqli_stmt_close($stmt);

    return ['exists' => $exists, 'active' => $active];
}

function getPegawaiFromDb(string $ownerEmail, array $locations): array
{
    $conn = getDbConnection();
    if (!$conn || $ownerEmail === '') {
        return [];
    }

    $pegawai = [];
    $locationById = [];
    foreach ($locations as $loc) {
        $locationById[(int)($loc['id'] ?? 0)] = $loc;
    }

    // Akun utama (owner) — sama seperti card pertama di merchantaccount.php
    $stmt = mysqli_prepare($conn, "SELECT credfullname, credusername, credphonenumber, position, is_active, location_can
        FROM qris_merchant_cred WHERE LOWER(credusername) = ? ORDER BY credin ASC LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $ownerEmail);
        mysqli_stmt_execute($stmt);
        $rOwner = mysqli_stmt_get_result($stmt)->fetch_assoc();
        mysqli_stmt_close($stmt);

        if ($rOwner) {
            $ownerToko = count($locations) > 1
                ? (count($locations) . ' lokasi merchant')
                : ($locations[0]['nama'] ?? '-');
            $pegawai[] = [
                'nama' => $rOwner['credfullname'] ?: $ownerEmail,
                'jabatan' => 'owner',
                'toko_nama' => $ownerToko,
                'toko_alamat' => $locations[0]['alamat'] ?? '-',
                'email' => $rOwner['credusername'],
                'no_telepon' => $rOwner['credphonenumber'] ?: '-',
                'status' => ((int)$rOwner['is_active'] === 1) ? 'aktif' : 'nonaktif',
            ];
        }
    }

    // Pegawai yg location_can-nya mencakup salah satu lokasi (mlocid) milik owner ini
    $mlocids = array_values(array_filter(array_map(static fn($l) => (int)($l['id'] ?? 0), $locations)));
    if (!empty($mlocids)) {
        $placeholders = implode(' OR ', array_fill(0, count($mlocids), 'FIND_IN_SET(?, location_can) > 0'));
        $sql = "SELECT credin, credfullname, credusername, credphonenumber, position, is_active, location_can
                FROM qris_merchant_cred
                WHERE ($placeholders) AND LOWER(credusername) != ?
                GROUP BY credin ORDER BY credin ASC";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            $types = str_repeat('i', count($mlocids)) . 's';
            $params = array_merge($mlocids, [$ownerEmail]);
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) {
                $locNames = [];
                foreach (explode(',', (string)$row['location_can']) as $lid) {
                    if (isset($locationById[(int)trim($lid)])) {
                        $locNames[] = $locationById[(int)trim($lid)]['nama'];
                    }
                }
                $pegawai[] = [
                    'nama' => $row['credfullname'] ?: $row['credusername'],
                    'jabatan' => $row['position'] === 'owner' ? 'owner' : 'employee',
                    'toko_nama' => !empty($locNames) ? implode(', ', $locNames) : '-',
                    'toko_alamat' => $locationById[(int)trim(explode(',', (string)$row['location_can'])[0] ?? '')]['alamat'] ?? '-',
                    'email' => $row['credusername'],
                    'no_telepon' => $row['credphonenumber'] ?: '-',
                    'status' => ((int)$row['is_active'] === 1) ? 'aktif' : 'nonaktif',
                ];
            }
            mysqli_stmt_close($stmt);
        }
    }

    return $pegawai;
}

function getMerchantLocationsFromDb(string $email): array
{
    $conn = getDbConnection();
    if (!$conn || $email === '') {
        return [];
    }

    $sql = "SELECT mlocid, mID, mname, mlocaddress
            FROM qris_merchant_loc
            WHERE memailaddr = ?
              AND staticqr_paymentstat = ?
            ORDER BY mname ASC";
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return [];
    }

    $status = 'paid';
    mysqli_stmt_bind_param($stmt, 'ss', $email, $status);

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return [];
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        mysqli_stmt_close($stmt);
        return [];
    }

    $locations = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $locations[] = [
            'id' => (int)($row['mlocid'] ?? 0),
            'm_id' => trim((string)($row['mID'] ?? '')),
            'nama' => trim((string)($row['mname'] ?? '')),
            'alamat' => trim((string)($row['mlocaddress'] ?? '')),
        ];
    }

    mysqli_free_result($result);
    mysqli_stmt_close($stmt);

    return array_values(array_filter($locations, static function (array $item): bool {
        return $item['m_id'] !== '' || $item['nama'] !== '' || $item['alamat'] !== '';
    }));
}

function getTodayTransactionSummary(array $merchantLocations): array
{
    return getTransactionSummaryByMerchantIds(
        getMerchantIdsFromLocations($merchantLocations),
        date('Y-m-d 00:00:00'),
        date('Y-m-d 00:00:00', strtotime('+1 day'))
    );
}

function getMerchantIdsFromLocations(array $merchantLocations, ?int $tokoId = null): array
{
    $filtered = $merchantLocations;

    if ($tokoId !== null) {
        $filtered = array_values(array_filter($merchantLocations, static function (array $item) use ($tokoId): bool {
            return (int)($item['id'] ?? 0) === $tokoId;
        }));
    }

    return array_values(array_unique(array_filter(array_map(
        static fn(array $item): string => trim((string)($item['m_id'] ?? '')),
        $filtered
    ))));
}

function getMlocIdsFromLocations(array $merchantLocations, ?int $tokoId = null): array
{
    $filtered = $merchantLocations;

    if ($tokoId !== null) {
        $filtered = array_values(array_filter($merchantLocations, static function (array $item) use ($tokoId): bool {
            return (int)($item['id'] ?? 0) === $tokoId;
        }));
    }

    return array_values(array_unique(array_filter(array_map(
        static fn(array $item): string => trim((string)($item['id'] ?? '')),
        $filtered
    ))));
}

/**
 * Hitung klaim yang perlu tindakan merchant, dari tabel klaim_transaksi.
 * Klaim user: klaimstat='moved-merchant' (merchant belum merespons sebelum deadline).
 * Klaim merchant: klaimstat masih dalam grup berjalan (belum masuk status success atau decline).
 * Referensi status sama persis dengan buildStatusFilter() di v2/m/proses.php.
 */
function getKlaimSummaryByMlocIds(array $mlocIds): array
{
    $default = ['user' => 0, 'merchant' => 0];

    if (empty($mlocIds)) {
        return $default;
    }

    $rows = runAggregateQuery(
        $mlocIds,
        "SELECT klaim_type, COUNT(*) AS total
         FROM klaim_transaksi
         WHERE mlocid IN (__MERCHANT_IDS__)
           AND (
                (klaim_type = 'u' AND klaimstat = 'moved-merchant')
             OR (klaim_type = 'm' AND klaimstat IN ('pending','moved-telkom','moved-merchant','moved-verifikasi','moved-issuer'))
           )
         GROUP BY klaim_type"
    );

    $summary = $default;
    foreach ($rows as $row) {
        $total = (int)($row['total'] ?? 0);
        if (($row['klaim_type'] ?? '') === 'u') {
            $summary['user'] = $total;
        } elseif (($row['klaim_type'] ?? '') === 'm') {
            $summary['merchant'] = $total;
        }
    }

    return $summary;
}

function runAggregateQuery(array $merchantIds, string $sql, string $typesSuffix = '', array $extraParams = []): array
{
    $conn = getDbConnection();
    if (!$conn || empty($merchantIds)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($merchantIds), '?'));
    $stmt = mysqli_prepare($conn, str_replace('__MERCHANT_IDS__', $placeholders, $sql));
    if (!$stmt) {
        return [];
    }

    $types = str_repeat('s', count($merchantIds)) . $typesSuffix;
    $params = array_merge($merchantIds, $extraParams);
    mysqli_stmt_bind_param($stmt, $types, ...$params);

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return [];
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        mysqli_stmt_close($stmt);
        return [];
    }

    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    mysqli_free_result($result);
    mysqli_stmt_close($stmt);

    return $rows;
}

function getTransactionSummaryByMerchantIds(array $merchantIds, string $startAt, string $endAt): array
{
    $rows = runAggregateQuery(
        $merchantIds,
        "SELECT COALESCE(SUM(amount), 0) AS total_nilai,
                COUNT(id) AS jumlah_transaksi
         FROM qren
         WHERE merchant_apikey IN (__MERCHANT_IDS__)
           AND date_created >= ?
           AND date_created < ?",
        'ss',
        [$startAt, $endAt]
    );

    $summary = $rows[0] ?? [];

    return [
        'total_nilai' => (int)($summary['total_nilai'] ?? 0),
        'jumlah_transaksi' => (int)($summary['jumlah_transaksi'] ?? 0),
    ];
}

function getChartDataByMerchantIds(array $merchantIds, string $startAt, string $endAt): array
{
    $rows = runAggregateQuery(
        $merchantIds,
        "SELECT DATE(date_created) AS tx_date,
                COALESCE(SUM(amount), 0) AS total_nilai
         FROM qren
         WHERE merchant_apikey IN (__MERCHANT_IDS__)
           AND date_created >= ?
           AND date_created < ?
         GROUP BY DATE(date_created)
         ORDER BY tx_date ASC",
        'ss',
        [$startAt, $endAt]
    );

    $totalsByDate = [];
    foreach ($rows as $row) {
        $totalsByDate[(string)($row['tx_date'] ?? '')] = (int)($row['total_nilai'] ?? 0);
    }

    $labels = [];
    $values = [];
    $cursor = strtotime(substr($startAt, 0, 10));
    $endDate = strtotime(substr($endAt, 0, 10));

    while ($cursor < $endDate) {
        $dateKey = date('Y-m-d', $cursor);
        $labels[] = date('d', $cursor);
        $values[] = $totalsByDate[$dateKey] ?? 0;
        $cursor = strtotime('+1 day', $cursor);
    }

    return [
        'labels' => $labels,
        'values' => $values,
    ];
}

function getHourlyDayDataByMerchantIds(array $merchantIds, string $startAt, string $endAt): array
{
    $rows = runAggregateQuery(
        $merchantIds,
        "SELECT WEEKDAY(date_created) AS day_no,
                HOUR(date_created) AS hour_no,
                COUNT(id) AS total_transaksi
         FROM qren
         WHERE merchant_apikey IN (__MERCHANT_IDS__)
           AND date_created >= ?
           AND date_created < ?
         GROUP BY WEEKDAY(date_created), HOUR(date_created)
         ORDER BY day_no ASC, hour_no ASC",
        'ss',
        [$startAt, $endAt]
    );

    $dayMap = [
        0 => 'Senin',
        1 => 'Selasa',
        2 => 'Rabu',
        3 => 'Kamis',
        4 => 'Jumat',
        5 => 'Sabtu',
        6 => 'Minggu',
    ];
    $hourLabels = [];
    for ($hour = 0; $hour < 24; $hour++) {
        $hourLabels[] = in_array($hour, [3, 7, 11, 15, 19, 23], true) ? (string)$hour : '';
    }

    $days = [];
    foreach ($dayMap as $dayNo => $dayName) {
        $days[] = [
            'key' => $dayNo,
            'label' => $dayName,
            'values' => array_fill(0, 24, 0),
            'labels' => $hourLabels,
        ];
    }

    foreach ($rows as $row) {
        $dayNo = (int)($row['day_no'] ?? 0);
        $hourNo = (int)($row['hour_no'] ?? -1);
        if ($dayNo >= 0 && $dayNo <= 6 && $hourNo >= 0 && $hourNo <= 23) {
            $days[$dayNo]['values'][$hourNo] = (int)($row['total_transaksi'] ?? 0);
        }
    }

    $activeDayIndex = 0;
    $bestDayTotal = -1;
    foreach ($days as $index => $day) {
        $dayTotal = array_sum($day['values']);
        if ($dayTotal > $bestDayTotal) {
            $activeDayIndex = $index;
            $bestDayTotal = $dayTotal;
        }
    }

    return [
        'days' => $days,
        'active_day_index' => $activeDayIndex,
        'jam_fav' => $days[$activeDayIndex]['label'] ?? '-',
        'values' => $days[$activeDayIndex]['values'] ?? array_fill(0, 24, 0),
        'labels' => $days[$activeDayIndex]['labels'] ?? $hourLabels,
    ];
}

function calcPercentChange(int|float $current, int|float $previous): array
{
    $current = (float)$current;
    $previous = (float)$previous;

    if ($previous <= 0.0 && $current <= 0.0) {
        return [
            'value' => 0,
            'display' => '0%',
        ];
    }

    if ($previous <= 0.0 && $current > 0.0) {
        return [
            'value' => 999,
            'display' => '999%+',
        ];
    }

    $pct = (int)round((($current - $previous) / $previous) * 100);
    $maxDisplay = 999;

    if ($pct > $maxDisplay) {
        return [
            'value' => $maxDisplay,
            'display' => $maxDisplay . '%+',
        ];
    }

    if ($pct < -$maxDisplay) {
        return [
            'value' => -$maxDisplay,
            'display' => $maxDisplay . '%+',
        ];
    }

    return [
        'value' => $pct,
        'display' => abs($pct) . '%',
    ];
}

function formatPeriodLabel(string $startAt, string $endAt): string
{
    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
    $startTs = strtotime($startAt);
    $endTs = strtotime($endAt . ' -1 second');

    return date('d', $startTs) . ' ' . $months[(int)date('n', $startTs) - 1] . ' ' . date('Y', $startTs)
        . ' - '
        . date('d', $endTs) . ' ' . $months[(int)date('n', $endTs) - 1] . ' ' . date('Y', $endTs);
}

function resolvePeriodRange(string $period, ?string $dateFrom = null, ?string $dateTo = null): array
{
    $todayStart = strtotime(date('Y-m-d 00:00:00'));
    $tomorrowStart = strtotime('+1 day', $todayStart);

    switch ($period) {
        case 'hari_ini':
            $start = $todayStart;
            $end = $tomorrowStart;
            $previousStart = strtotime('-1 day', $todayStart);
            $previousEnd = $todayStart;
            $label = 'Hari Ini';
            break;
        case 'bulan':
            $start = strtotime(date('Y-m-01 00:00:00'));
            $end = $tomorrowStart;
            $previousStart = strtotime(date('Y-m-01 00:00:00', strtotime('-1 month', $start)));
            $previousEnd = $start;
            $label = 'Bulan Ini';
            break;
        case '7':
            $start = strtotime('-6 days', $todayStart);
            $end = $tomorrowStart;
            $previousStart = strtotime('-7 days', $start);
            $previousEnd = $start;
            $label = '7 Hari Terakhir';
            break;
        case '30':
            $start = strtotime('-29 days', $todayStart);
            $end = $tomorrowStart;
            $previousStart = strtotime('-30 days', $start);
            $previousEnd = $start;
            $label = '30 Hari Terakhir';
            break;
        case 'atur':
            $start = $dateFrom ? strtotime($dateFrom . ' 00:00:00') : $todayStart;
            $end = $dateTo ? strtotime($dateTo . ' 00:00:00') : $todayStart;
            if ($end < $start) {
                [$start, $end] = [$end, $start];
            }
            $end = strtotime('+1 day', $end);
            $customDayCount = max(1, (int)(($end - $start) / 86400));
            $previousStart = strtotime("-{$customDayCount} days", $start);
            $previousEnd = $start;
            $label = 'Atur Tanggal';
            break;
        case 'kemarin':
        default:
            $start = strtotime('-1 day', $todayStart);
            $end = $todayStart;
            $previousStart = strtotime('-2 days', $todayStart);
            $previousEnd = strtotime('-1 day', $todayStart);
            $label = 'Kemarin';
            $period = 'kemarin';
            break;
    }

    $diffDays = max(1, (int)(($end - $start) / 86400));

    return [
        'period' => $period,
        'button_label' => $label,
        'start_at' => date('Y-m-d H:i:s', $start),
        'end_at' => date('Y-m-d H:i:s', $end),
        'previous_start_at' => date('Y-m-d H:i:s', $previousStart ?? strtotime("-{$diffDays} days", $start)),
        'previous_end_at' => date('Y-m-d H:i:s', $previousEnd ?? $start),
        'day_count' => $diffDays,
        'periode_str' => formatPeriodLabel(date('Y-m-d H:i:s', $start), date('Y-m-d H:i:s', $end)),
    ];
}

function buildReportPayload(array $merchantLocations, array $fallback, ?int $tokoId = null, string $period = 'kemarin', ?string $dateFrom = null, ?string $dateTo = null): array
{
    $selectedToko = $fallback['toko_aktif'] ?? [];
    foreach ($merchantLocations as $location) {
        if ($tokoId !== null && (int)($location['id'] ?? 0) === $tokoId) {
            $selectedToko = [
                'id' => $location['id'],
                'nama' => $location['nama'],
                'alamat' => $location['alamat'],
            ];
            break;
        }
    }

    $merchantIds = getMerchantIdsFromLocations($merchantLocations, $tokoId);
    if (empty($merchantIds)) {
        $merchantIds = getMerchantIdsFromLocations($merchantLocations);
    }

    $mlocIds = getMlocIdsFromLocations($merchantLocations, $tokoId);
    if (empty($mlocIds)) {
        $mlocIds = getMlocIdsFromLocations($merchantLocations);
    }

    $range = resolvePeriodRange($period, $dateFrom, $dateTo);
    $txToday = getTransactionSummaryByMerchantIds(
        $merchantIds,
        date('Y-m-d 00:00:00'),
        date('Y-m-d 00:00:00', strtotime('+1 day'))
    );

    $currentSummary = getTransactionSummaryByMerchantIds($merchantIds, $range['start_at'], $range['end_at']);
    $previousSummary = getTransactionSummaryByMerchantIds($merchantIds, $range['previous_start_at'], $range['previous_end_at']);
    $chart = getChartDataByMerchantIds($merchantIds, $range['start_at'], $range['end_at']);
    $favoriteDay = getHourlyDayDataByMerchantIds(
        $merchantIds,
        date('Y-m-d 00:00:00', strtotime('-29 days')),
        date('Y-m-d 00:00:00', strtotime('+1 day'))
    );

    $avgCurrent = $range['day_count'] > 0 ? (int)round($currentSummary['total_nilai'] / $range['day_count']) : 0;
    $avgPrevious = $range['day_count'] > 0 ? (int)round($previousSummary['total_nilai'] / $range['day_count']) : 0;
    $pctTx = calcPercentChange($currentSummary['jumlah_transaksi'] ?? 0, $previousSummary['jumlah_transaksi'] ?? 0);
    $pctAvg = calcPercentChange($avgCurrent, $avgPrevious);
    $pctSaldo = calcPercentChange($currentSummary['total_nilai'] ?? 0, $previousSummary['total_nilai'] ?? 0);

    $tx = !empty($txToday) ? $txToday : ($fallback['transaksi_hari_ini'] ?? ['total_nilai' => 0, 'jumlah_transaksi' => 0]);
    // TODO: saldo QRIS belum ter-settle belum punya sumber data. Untuk merchant
    // asli dikirim null (baris disembunyikan); angka dummy hanya saat tanpa lokasi.
    $tx['saldo_qris'] = empty($merchantLocations) ? ($fallback['saldo_qris'] ?? null) : null;

    return [
        'toko' => $selectedToko,
        'tx' => $tx,
        'lap' => [
            'periode_str' => $range['periode_str'],
            'total_tx' => $currentSummary['jumlah_transaksi'] ?? ($fallback['laporan']['total_tx'] ?? 0),
            'pct_tx' => $pctTx['value'],
            'pct_tx_display' => $pctTx['display'],
            'avg_harian' => $avgCurrent,
            'pct_avg' => $pctAvg['value'],
            'pct_avg_display' => $pctAvg['display'],
            'saldo_cair' => $currentSummary['total_nilai'] ?? 0,
            'pct_saldo' => $pctSaldo['value'],
            'pct_saldo_display' => $pctSaldo['display'],
        ],
        'chart' => !empty($chart['labels']) ? $chart : ($fallback['chart'] ?? ['labels' => [], 'values' => []]),
        'klaim' => !empty($mlocIds) ? getKlaimSummaryByMlocIds($mlocIds) : ($fallback['klaim'] ?? ['user' => 0, 'merchant' => 0]),
        'jam' => !empty($favoriteDay['labels'])
            ? $favoriteDay
            : ($fallback['jam'] ?? ['values' => [], 'labels' => [], 'jam_fav' => '-']),
        'period' => [
            'key' => $range['period'],
            'label' => $range['button_label'],
            'date_from' => substr($range['start_at'], 0, 10),
            'date_to' => date('Y-m-d', strtotime($range['end_at'] . ' -1 day')),
        ],
    ];
}

$dbLocations = getMerchantLocationsFromDb($WEBVIEW_AUTH_EMAIL);
$tokoData = !empty($dbLocations)
    ? array_map(static function (array $item): array {
        return [
            'id' => $item['id'],
            'nama' => $item['nama'],
            'alamat' => $item['alamat'],
        ];
    }, $dbLocations)
    : $defaultToko;
$todayTransaction = getTodayTransactionSummary($dbLocations);

$DUMMY = [
    'toko' => $tokoData,
    'toko_aktif' => $tokoData[0] ?? $defaultToko[0],
    'transaksi_hari_ini' => !empty($todayTransaction) ? $todayTransaction : [
        'total_nilai' => 250000,
        'jumlah_transaksi' => 12,
    ],
    'saldo_qris' => 520000,
    'laporan' => [
        'periode_str' => '15 Jan 2024 - 15 Jan 2024',
        'total_tx' => 500,
        'pct_tx' => 20,
        'avg_harian' => 2000000,
        'pct_avg' => 20,
        'saldo_cair' => 250000000,
        'pct_saldo' => 20,
    ],
    'klaim' => [
        'user' => 10,
        'merchant' => 2,
    ],
    'chart' => [
        'values' => [4000000, 2000000, 2000000, 2000000, 4000000, 1200000, 2000000],
        'labels' => ['10', '11', '12', '13', '14', '15', '16'],
    ],
    'jam' => [
        'values' => [2, 2, 2, 2, 3, 4, 5, 8, 12, 18, 28, 42, 65, 80, 78, 82, 88, 92, 90, 95, 87, 72, 50, 30],
        'labels' => ['', '', '', '3', '', '', '', '7', '', '', '', '11', '', '', '', '15', '', '', '', '19', '', '', '', '23'],
        'jam_fav' => 'Senin',
    ],
    'pegawai' => [
        ['id' => 1, 'nama' => 'Irfan Ibrahim', 'jabatan' => 'owner', 'toko_nama' => 'InterActive Cafe', 'toko_alamat' => 'Dukuh kupang timur 123', 'email' => 'irfan@qris.id', 'no_telepon' => '081234567890', 'status' => 'aktif'],
        ['id' => 2, 'nama' => 'Floyd Miles', 'jabatan' => 'kasir', 'toko_nama' => 'InterActive Cafe', 'toko_alamat' => 'Dukuh kupang timur 123', 'email' => 'floyd@qris.id', 'no_telepon' => '081234567891', 'status' => 'aktif'],
        ['id' => 3, 'nama' => 'Esther Howard', 'jabatan' => 'kasir', 'toko_nama' => 'InterActive Cafe', 'toko_alamat' => 'Dukuh kupang timur 123', 'email' => 'esther@qris.id', 'no_telepon' => '081234567892', 'status' => 'aktif'],
        ['id' => 4, 'nama' => 'Marvin McKinney', 'jabatan' => 'staff', 'toko_nama' => 'InterActive Cafe', 'toko_alamat' => 'Dukuh kupang timur 123', 'email' => 'marvin@qris.id', 'no_telepon' => '081234567893', 'status' => 'aktif'],
        ['id' => 5, 'nama' => 'Courtney Henry', 'jabatan' => 'kasir', 'toko_nama' => 'InterActive Cafe', 'toko_alamat' => 'Dukuh kupang timur 123', 'email' => 'courtney@qris.id', 'no_telepon' => '081234567894', 'status' => 'aktif'],
        ['id' => 6, 'nama' => 'Budi Santoso', 'jabatan' => 'kasir', 'toko_nama' => 'Bakso Panggang', 'toko_alamat' => 'Jl. Keouth Gg. 5 Sehat', 'email' => 'budi@qris.id', 'no_telepon' => '081234567895', 'status' => 'aktif'],
    ],
];

// ─── REFERAL ───────────────────────────────────────
// min = jumlah Merchant Bergabung minimal untuk level tersebut (per bulan)
const REFERAL_LEVELS = [
    'bronze' => ['nama' => 'Bronze', 'merchant' => '< 20 Merchant', 'bonus' => 3000, 'min' => 0],
    'gold' => ['nama' => 'Gold', 'merchant' => '20–50 Merchant', 'bonus' => 4000, 'min' => 20],
    'super' => ['nama' => 'Super', 'merchant' => '>50 Merchant', 'bonus' => 5000, 'min' => 51],
];

const REFERAL_SESSION_KEY = 'webview_referal_joined_dummy';

function referalLevelFor(int $merchantCount): string
{
    $current = 'bronze';
    foreach (REFERAL_LEVELS as $key => $lv) {
        if ($merchantCount >= $lv['min']) {
            $current = $key;
        }
    }
    return $current;
}

/**
 * TODO: status & perolehan referal belum punya sumber data. Mode dummy
 * (tanpa lokasi) memakai session + angka contoh; merchant asli selalu
 * dianggap belum bergabung sampai endpoint-nya tersedia.
 */
function getReferalStatus(array $merchantLocations): array
{
    webviewStartSession();
    $joined = empty($merchantLocations) && !empty($_SESSION[REFERAL_SESSION_KEY]);
    if (!$joined) {
        return ['joined' => false];
    }

    // ?demo_merchant=N (mode dummy saja) untuk melihat tampilan tiap level
    $merchant = isset($_GET['demo_merchant']) ? max(0, (int)$_GET['demo_merchant']) : 15;
    $level = referalLevelFor($merchant);

    return [
        'joined' => true,
        'link' => 'https://registrasi.qris.online/m/s/?ID1023276676146',
        'total_klik' => 20,
        'merchant_bergabung' => $merchant,
        'level' => $level,
        'bonus' => $merchant * REFERAL_LEVELS[$level]['bonus'],
        'bonus_cair' => 5000,
    ];
}

/**
 * Merchant yang bergabung lewat link referal pada bulan $ym (YYYY-MM), nama &
 * nomor HP sudah disamarkan. TODO: belum punya sumber data; contoh hanya di
 * mode dummy dan hanya untuk bulan berjalan.
 */
function getReferalMerchants(array $merchantLocations, string $ym): array
{
    $rows = [];
    if (empty($merchantLocations) && $ym === date('Y-m')) {
        $rows = [
            ['nama' => 'John Smith', 'hp' => '081234567872', 'tanggal' => date('Y-m-12')],
            ['nama' => 'Andrew Lee', 'hp' => '082198765472', 'tanggal' => date('Y-m-08')],
            ['nama' => 'Bob Hartono', 'hp' => '085611223372', 'tanggal' => date('Y-m-05')],
            ['nama' => 'Garry Wijaya', 'hp' => '087755443372', 'tanggal' => date('Y-m-02')],
        ];
    }

    return array_map(static fn(array $r) => [
        'nama' => maskReferalName($r['nama']),
        'hp' => maskReferalPhone($r['hp']),
        'tanggal' => $r['tanggal'],
    ], $rows);
}

function maskReferalName(string $nama): string
{
    $first = strtok(trim($nama), ' ');
    return ($first === false ? '' : $first) . '****';
}

function maskReferalPhone(string $hp): string
{
    $digits = preg_replace('/\D/', '', $hp);
    if (strlen($digits) <= 4) {
        return str_repeat('*', strlen($digits));
    }
    return substr($digits, 0, 2) . str_repeat('*', strlen($digits) - 4) . substr($digits, -2);
}

/** Posisi (0–100%) di progress bar level: batas Gold di 50%, batas Super di 90%. */
function referalProgressPct(int $merchantCount): float
{
    $gold = REFERAL_LEVELS['gold']['min'];
    $super = REFERAL_LEVELS['super']['min'];
    if ($merchantCount > $super) {
        return 100;
    }
    if ($merchantCount >= $gold) {
        return 50 + 40 * ($merchantCount - $gold) / ($super - $gold);
    }
    return 50 * $merchantCount / $gold;
}

// ─── SETTLEMENT ────────────────────────────────────
const SETTLEMENT_STATUS = [
    'proses' => ['label' => 'Proses Pencairan', 'class' => 'stl-st--proses'],
    'berhasil' => ['label' => 'Berhasil', 'class' => 'stl-st--berhasil'],
    'gagal' => ['label' => 'Gagal', 'class' => 'stl-st--gagal'],
];

function dummySettlements(): array
{
    // Tanggal relatif ke bulan berjalan supaya muncul di filter default
    $tgl = date('Y-m-05');
    $base = [
        'tanggal' => $tgl,
        'periode_dari' => date('Y-m-01'),
        'periode_sampai' => date('Y-m-03'),
        'bank' => 'BCA',
        'pemilik_rekening' => 'John Doe',
        'total_transaksi' => 33000,
        'biaya_settlement' => 3000,
        'mdr' => 231,
        'biaya_skn' => 0,
        'penyesuaian' => 0,
    ];

    return [
        ['id' => '308389', 'status' => 'proses'] + $base,
        ['id' => '308390', 'status' => 'berhasil'] + $base,
    ];
}

/**
 * TODO: riwayat settlement belum punya sumber data. Data contoh hanya dipakai
 * saat tanpa lokasi (mode dummy); merchant asli mendapat daftar kosong.
 */
function getSettlements(array $merchantLocations, ?int $tokoId = null): array
{
    $rows = empty($merchantLocations) ? dummySettlements() : [];

    foreach ($rows as &$row) {
        $row['total'] = $row['total_transaksi'] - $row['biaya_settlement'] - $row['mdr']
            - $row['biaya_skn'] - $row['penyesuaian'];
    }
    unset($row);

    return $rows;
}

function findSettlement(array $merchantLocations, string $id, ?int $tokoId = null): ?array
{
    foreach (getSettlements($merchantLocations, $tokoId) as $row) {
        if ((string)$row['id'] === $id) {
            return $row;
        }
    }
    return null;
}

function fmtTglId(string $date, bool $withDay = false, bool $longMonth = false): string
{
    $ts = strtotime($date);
    $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $short = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $long = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $m = (int)date('n', $ts) - 1;

    $out = date('d', $ts) . ' ' . ($longMonth ? $long[$m] : $short[$m]) . ' ' . date('Y', $ts);
    return $withDay ? $days[(int)date('w', $ts)] . ', ' . $out : $out;
}

function fmtRangeId(string $from, string $to): string
{
    if (date('Y-m', strtotime($from)) === date('Y-m', strtotime($to))) {
        return date('d', strtotime($from)) . ' – ' . fmtTglId($to);
    }
    return fmtTglId($from) . ' – ' . fmtTglId($to);
}

function e(mixed $val): string
{
    return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
}

function fmtRp(int $val): string
{
    return 'Rp ' . number_format($val, 0, ',', '.');
}
