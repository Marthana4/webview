<?php
require_once __DIR__ . '/includes/dummy.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
  exit;
}

// TODO: daftarkan merchant ke program referal di server. Sampai endpoint-nya
// ada, hanya mode dummy (tanpa lokasi) yang bisa "bergabung" lewat session.
if (!empty($dbLocations)) {
  http_response_code(501);
  echo json_encode(['success' => false, 'message' => 'Pendaftaran referal belum tersedia.']);
  exit;
}

webviewStartSession();
if (($_POST['action'] ?? '') === 'reset') {
  unset($_SESSION[REFERAL_SESSION_KEY]);
} else {
  $_SESSION[REFERAL_SESSION_KEY] = true;
}

echo json_encode(['success' => true]);
