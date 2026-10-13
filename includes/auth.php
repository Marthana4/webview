<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

const SANDBOX_DIVA_WEBVIEW_SESSION_KEY = 'sandbox_diva_webview_auth';
const SANDBOX_DIVA_WEBVIEW_JWT_SECRET = 'ArwyX!4WW^wwmvmmY#p4y##k*sQzN9M9LoUsHRn4YArM2&Y&WT4SvQ9VaY$L@vUa';
const SANDBOX_DIVA_WEBVIEW_LOG_FILE = __DIR__ . DIRECTORY_SEPARATOR . 'auth_debug.log';

webviewLoadJwtAutoload();

if (class_exists(JWT::class)) {
    JWT::$leeway = 60;
}

function webviewLoadJwtAutoload(): void
{
    static $loaded = false;

    if ($loaded) {
        return;
    }

    $paths = [
        __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'm' . DIRECTORY_SEPARATOR . 'php-plugins' . DIRECTORY_SEPARATOR . 'jwt' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php',
        dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'm' . DIRECTORY_SEPARATOR . 'm' . DIRECTORY_SEPARATOR . 'php-plugins' . DIRECTORY_SEPARATOR . 'jwt' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php',
        dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'm' . DIRECTORY_SEPARATOR . 'php-plugins' . DIRECTORY_SEPARATOR . 'jwt' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php',
        dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'restapi' . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . 'additional' . DIRECTORY_SEPARATOR . 'jwt' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php',
        'C:' . DIRECTORY_SEPARATOR . 'xampp' . DIRECTORY_SEPARATOR . 'htdocs' . DIRECTORY_SEPARATOR . 'm' . DIRECTORY_SEPARATOR . 'php-plugins' . DIRECTORY_SEPARATOR . 'jwt' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php',
        'C:' . DIRECTORY_SEPARATOR . 'xampp' . DIRECTORY_SEPARATOR . 'htdocs' . DIRECTORY_SEPARATOR . 'm' . DIRECTORY_SEPARATOR . 'php-plugins' . DIRECTORY_SEPARATOR . 'jwt' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autooad.php',
    ];

    foreach ($paths as $path) {
        if (is_file($path)) {
            require_once $path;
            $loaded = true;
            return;
        }
    }
}

function webviewRequireAuth(bool $expectsJson = false): array
{
    $auth = webviewResolveAuthContext();
    if (!($auth['status'] ?? false)) {
        webviewLogAuth('deny_access', [
            'expects_json' => $expectsJson,
            'query_keys' => array_keys($_GET ?? []),
            'post_keys' => array_keys($_POST ?? []),
            'has_session' => isset($_SESSION[SANDBOX_DIVA_WEBVIEW_SESSION_KEY]),
        ]);
        webviewDenyAccess($expectsJson);
    }

    return $auth;
}

function webviewResolveAuthContext(): array
{
    webviewStartSession();

    $incomingToken = webviewExtractIncomingToken();
    if ($incomingToken !== '') {
        webviewLogAuth('incoming_token_detected', [
            'token_preview' => webviewTokenPreview($incomingToken),
            'token_length' => strlen($incomingToken),
        ]);
        $auth = webviewValidateToken($incomingToken);
        if ($auth['status']) {
            webviewLogAuth('incoming_token_valid', [
                'email' => $auth['email'] ?? '',
                'source' => $auth['source'] ?? '',
            ]);
            webviewPersistAuth($auth);
            return $auth;
        }
        webviewLogAuth('incoming_token_invalid', [
            'token_preview' => webviewTokenPreview($incomingToken),
        ]);
    }

    $incomingEmail = webviewExtractIncomingEmail();
    if ($incomingEmail !== '') {
        webviewLogAuth('incoming_email_detected', [
            'email' => $incomingEmail,
        ]);
        $auth = webviewGenerateAuthFromEmail($incomingEmail);
        if ($auth['status']) {
            webviewLogAuth('incoming_email_generated_token', [
                'email' => $auth['email'] ?? '',
                'token_preview' => webviewTokenPreview($auth['token'] ?? ''),
            ]);
            webviewPersistAuth($auth);
            return $auth;
        }
        webviewLogAuth('incoming_email_generate_failed', [
            'email' => $incomingEmail,
        ]);
    }

    $sessionAuth = webviewReadSessionAuth();
    if ($sessionAuth['status']) {
        webviewLogAuth('session_auth_valid', [
            'email' => $sessionAuth['email'] ?? '',
            'source' => $sessionAuth['source'] ?? '',
        ]);
        return $sessionAuth;
    }

    return [
        'status' => false,
        'email' => '',
        'token' => '',
        'token_encoded' => '',
        'source' => '',
    ];
}

function webviewStartSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function webviewPersistAuth(array $auth): void
{
    $_SESSION[SANDBOX_DIVA_WEBVIEW_SESSION_KEY] = [
        'email' => (string)($auth['email'] ?? ''),
        'token' => (string)($auth['token'] ?? ''),
        'token_encoded' => (string)($auth['token_encoded'] ?? ''),
        'source' => (string)($auth['source'] ?? ''),
    ];
}

function webviewReadSessionAuth(): array
{
    $stored = $_SESSION[SANDBOX_DIVA_WEBVIEW_SESSION_KEY] ?? null;
    if (!is_array($stored)) {
        webviewLogAuth('session_auth_missing', []);
        return [
            'status' => false,
            'email' => '',
            'token' => '',
            'token_encoded' => '',
            'source' => '',
        ];
    }

    $token = trim((string)($stored['token'] ?? ''));
    $email = trim((string)($stored['email'] ?? ''));
    if ($token === '' || $email === '') {
        webviewLogAuth('session_auth_incomplete', [
            'has_token' => $token !== '',
            'has_email' => $email !== '',
        ]);
        return [
            'status' => false,
            'email' => '',
            'token' => '',
            'token_encoded' => '',
            'source' => '',
        ];
    }

    $validated = webviewValidateToken($token);
    if (!$validated['status']) {
        webviewLogAuth('session_auth_invalid', [
            'email' => $email,
            'token_preview' => webviewTokenPreview($token),
        ]);
        unset($_SESSION[SANDBOX_DIVA_WEBVIEW_SESSION_KEY]);
        return [
            'status' => false,
            'email' => '',
            'token' => '',
            'token_encoded' => '',
            'source' => '',
        ];
    }

    $validated['source'] = trim((string)($stored['source'] ?? 'session')) ?: 'session';
    return $validated;
}

function webviewExtractIncomingToken(): string
{
    $candidates = [
        'get_token' => $_GET['token'] ?? null,
        'get_auth' => $_GET['auth'] ?? null,
        'get_access_token' => $_GET['access_token'] ?? null,
        'post_token' => $_POST['token'] ?? null,
        'post_auth' => $_POST['auth'] ?? null,
        'post_access_token' => $_POST['access_token'] ?? null,
        'cookie_token' => $_COOKIE['token'] ?? null,
        'cookie_auth' => $_COOKIE['auth'] ?? null,
        'authorization_header' => webviewGetAuthorizationHeader(),
    ];

    foreach ($candidates as $source => $candidate) {
        $token = webviewNormalizeIncomingToken((string)$candidate);
        if ($token !== '') {
            webviewLogAuth('token_candidate_matched', [
                'source' => $source,
                'raw_preview' => webviewTokenPreview((string)$candidate),
                'normalized_preview' => webviewTokenPreview($token),
            ]);
            return $token;
        }
    }

    webviewLogAuth('token_candidate_empty', []);
    return '';
}

function webviewExtractIncomingEmail(): string
{
    $candidates = [
        $_GET['email'] ?? null,
        $_GET['usr'] ?? null,
        $_POST['email'] ?? null,
        $_POST['usr'] ?? null,
    ];

    foreach ($candidates as $candidate) {
        $email = trim((string)$candidate);
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return strtolower($email);
        }
    }

    return '';
}

function webviewNormalizeIncomingToken(string $candidate): string
{
    $candidate = trim($candidate);
    if ($candidate === '') {
        return '';
    }

    // Base64 token di query string sering kehilangan '+' dan berubah jadi spasi.
    $candidate = str_replace(' ', '+', $candidate);
    $candidate = trim(rawurldecode($candidate));

    if (stripos($candidate, 'Bearer ') === 0) {
        $candidate = trim(substr($candidate, 7));
    }

    if (substr_count($candidate, '.') === 2) {
        return $candidate;
    }

    $decoded = base64_decode($candidate, true);
    if (is_string($decoded) && substr_count($decoded, '.') === 2) {
        return $decoded;
    }

    return '';
}

function webviewGetAuthorizationHeader(): string
{
    $headers = [
        $_SERVER['HTTP_AUTHORIZATION'] ?? '',
        $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '',
    ];

    if (function_exists('getallheaders')) {
        foreach ((array)getallheaders() as $name => $value) {
            if (strcasecmp((string)$name, 'Authorization') === 0) {
                $headers[] = (string)$value;
            }
        }
    }

    foreach ($headers as $header) {
        $header = trim((string)$header);
        if ($header !== '') {
            return $header;
        }
    }

    return '';
}

function webviewValidateToken(string $rawToken): array
{
    if ($rawToken === '' || !class_exists(JWT::class) || !class_exists(Key::class)) {
        webviewLogAuth('validate_token_prereq_failed', [
            'has_token' => $rawToken !== '',
            'jwt_class' => class_exists(JWT::class),
            'key_class' => class_exists(Key::class),
        ]);
        return [
            'status' => false,
            'email' => '',
            'token' => '',
            'token_encoded' => '',
            'source' => '',
        ];
    }

    try {
        $decoded = JWT::decode($rawToken, new Key(SANDBOX_DIVA_WEBVIEW_JWT_SECRET, 'HS256'));
        $emailClaims = ['email', 'usr', 'emailUser'];
        $email = '';

        foreach ($emailClaims as $claim) {
            $value = trim((string)($decoded->{$claim} ?? ''));
            if ($value !== '') {
                $email = strtolower($value);
                break;
            }
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            webviewLogAuth('validate_token_email_missing', [
                'claims_checked' => $emailClaims,
            ]);
            return [
                'status' => false,
                'email' => '',
                'token' => '',
                'token_encoded' => '',
                'source' => '',
            ];
        }

        return [
            'status' => true,
            'email' => $email,
            'token' => $rawToken,
            'token_encoded' => base64_encode($rawToken),
            'source' => 'token',
        ];
    } catch (Throwable $th) {
        webviewLogAuth('validate_token_exception', [
            'message' => $th->getMessage(),
            'token_preview' => webviewTokenPreview($rawToken),
        ]);
        return [
            'status' => false,
            'email' => '',
            'token' => '',
            'token_encoded' => '',
            'source' => '',
        ];
    }
}

function webviewGenerateAuthFromEmail(string $email): array
{
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !class_exists(JWT::class)) {
        webviewLogAuth('generate_auth_email_prereq_failed', [
            'email' => $email,
            'jwt_class' => class_exists(JWT::class),
        ]);
        return [
            'status' => false,
            'email' => '',
            'token' => '',
            'token_encoded' => '',
            'source' => '',
        ];
    }

    $now = time();
    $payload = [
        'email' => strtolower($email),
        'iat' => $now,
    ];

    $token = JWT::encode($payload, SANDBOX_DIVA_WEBVIEW_JWT_SECRET, 'HS256');

    return [
        'status' => true,
        'email' => strtolower($email),
        'token' => $token,
        'token_encoded' => base64_encode($token),
        'source' => 'email',
    ];
}

function webviewDenyAccess(bool $expectsJson = false): void
{
    http_response_code(401);

    if ($expectsJson) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized. Token auth tidak valid atau belum dikirim.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Unauthorized</title></head><body style="font-family:Arial,sans-serif;padding:24px"><h3>Akses ditolak</h3><p>Token auth tidak valid atau belum dikirim ke webview.</p></body></html>';
    exit;
}

function webviewDenyInactiveAccount(bool $expectsJson, bool $accountExists): void
{
    http_response_code(403);

    $message = $accountExists
        ? 'Akun Anda belum aktif atau pembayaran registrasi belum selesai.'
        : 'Akun tidak ditemukan.';

    webviewLogAuth('deny_inactive_account', [
        'expects_json' => $expectsJson,
        'account_exists' => $accountExists,
    ]);

    if ($expectsJson) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => false,
            'message' => $message,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Akun Belum Aktif</title></head><body style="font-family:Arial,sans-serif;padding:24px"><h3>Akun belum aktif</h3><p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p></body></html>';
    exit;
}

function webviewLogAuth(string $event, array $context): void
{
    $line = json_encode([
        'time' => date('Y-m-d H:i:s'),
        'event' => $event,
        'context' => $context,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if (!is_string($line) || $line === '') {
        return;
    }

    @file_put_contents(SANDBOX_DIVA_WEBVIEW_LOG_FILE, $line . PHP_EOL, FILE_APPEND);
}

function webviewTokenPreview(string $token): string
{
    $token = trim($token);
    if ($token === '') {
        return '';
    }

    if (strlen($token) <= 16) {
        return $token;
    }

    return substr($token, 0, 8) . '...' . substr($token, -8);
}
