<?php
declare(strict_types=1);

/**
 * router.php
 * Router untuk PHP built-in server (`php -S ... router.php`).
 */

require_once __DIR__ . '/bootstrap.php';

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$uri = '/' . ltrim($uri, '/');

// Route: API
if ($uri === '/api' || $uri === '/api.php') {
    require __DIR__ . '/api.php';
    return true;
}

// Route: root / index
if ($uri === '/' || $uri === '/index.php') {
    require __DIR__ . '/index.php';
    return true;
}

// Static files (secure: no traversal)
if (strpos($uri, '..') === false) {
    $file = __DIR__ . $uri;
    if (is_file($file)) {
        return false;
    }
}

// 404 fallback
http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
echo "404 Not Found\n";
return true;
