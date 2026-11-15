<?php
declare(strict_types=1);

/**
 * api.php
 * GET /api.php?target=example.com
 * Response JSON + status HTTP akurat.
 */

require_once __DIR__ . '/bootstrap.php';

use App\Controllers\AuditController;

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, max-age=0');
header('Access-Control-Allow-Origin: *');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    echo json_encode([
        'ok' => false,
        'error' => 'method_not_allowed',
        'message' => 'Gunakan HTTP GET.',
    ], JSON_UNESCAPED_SLASHES);
    exit;
}

$target = $_GET['target'] ?? '';
if (!is_string($target) || trim($target) === '') {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'error' => 'missing_target',
        'message' => 'Parameter `target` wajib diisi. Contoh: ?target=example.com',
    ], JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    $controller = new AuditController();
    $outcome = $controller->handle($target);
    http_response_code($outcome['status']);
    echo json_encode($outcome['body'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'internal_error',
        'message' => 'Terjadi kesalahan internal: ' . $e->getMessage(),
    ], JSON_UNESCAPED_SLASHES);
}
