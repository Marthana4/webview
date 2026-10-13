<?php
// Taruh file ini di dalam folder webview/ (sejajar sama report.php), lalu akses:
// http://localhost/Holic/webview/cek_jwt.php

header('Content-Type: text/plain; charset=UTF-8');

echo "=== Info Lokasi ===\n";
echo "__DIR__ (folder file ini): " . __DIR__ . "\n\n";

$paths = [
    __DIR__ . '/vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../m/php-plugins/jwt/vendor/autoload.php',
    __DIR__ . '/../../m/m/php-plugins/jwt/vendor/autoload.php',
    __DIR__ . '/../../restapi/backend/additional/jwt/vendor/autoload.php',
    'C:/xampp/htdocs/m/php-plugins/jwt/vendor/autoload.php',
];

echo "=== Cek keberadaan file autoload ===\n";
$found = null;
foreach ($paths as $p) {
    $exists = is_file($p) ? 'ADA' : 'tidak ada';
    echo "[$exists] $p\n";
    if ($exists === 'ADA' && $found === null) {
        $found = $p;
    }
}

echo "\n=== Cek isi folder saat ini ===\n";
print_r(scandir(__DIR__));

if ($found) {
    echo "\n=== Coba require: $found ===\n";
    require_once $found;
    echo "Class Firebase\\JWT\\JWT ada? " . (class_exists('Firebase\JWT\JWT') ? 'YA' : 'TIDAK') . "\n";
    echo "Class Firebase\\JWT\\Key ada? " . (class_exists('Firebase\JWT\Key') ? 'YA' : 'TIDAK') . "\n";
} else {
    echo "\nTidak ada autoload.php yang ketemu sama sekali. Composer belum jalan / salah folder.\n";
}