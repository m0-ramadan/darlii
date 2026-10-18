<?php

/**
 * Router used by `php artisan serve`.
 *
 * Laravel's default router serves static files through PHP's built-in server,
 * which does not compress them. This router adds gzip for text assets (CSS,
 * JS, HTML, JSON, SVG, XML) while delegating binary files to the built-in
 * server so range requests keep working.
 */

$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

$acceptEncoding = strtolower((string) ($_SERVER['HTTP_ACCEPT_ENCODING'] ?? ''));
$acceptsGzip = str_contains($acceptEncoding, 'gzip');

$extension = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
$compressible = [
    'css' => 'text/css; charset=UTF-8',
    'js' => 'application/javascript; charset=UTF-8',
    'mjs' => 'application/javascript; charset=UTF-8',
    'json' => 'application/json; charset=UTF-8',
    'svg' => 'image/svg+xml',
    'xml' => 'application/xml; charset=UTF-8',
    'txt' => 'text/plain; charset=UTF-8',
    'html' => 'text/html; charset=UTF-8',
    'map' => 'application/json; charset=UTF-8',
];

$requestedFile = $publicPath . $uri;

// Serve compressible static assets ourselves so they are gzipped. Everything
// else (images, fonts, media) is left to the built-in server.
if ($uri !== '/' && isset($compressible[$extension]) && is_file($requestedFile)) {
    if (! $acceptsGzip || filesize($requestedFile) < 1024) {
        return false;
    }

    $contents = file_get_contents($requestedFile);
    if ($contents === false) {
        return false;
    }

    $payload = gzencode($contents, 6);
    if ($payload === false) {
        return false;
    }

    header('Content-Type: ' . $compressible[$extension]);
    header('Content-Encoding: gzip');
    header('Vary: Accept-Encoding');
    header('Content-Length: ' . strlen($payload));
    header('Cache-Control: public, max-age=604800');
    header('X-Content-Type-Options: nosniff');

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
        echo $payload;
    }

    return true;
}

// Any other existing static file (images, fonts, media) is served directly
// by PHP's built-in server so binary content and range requests stay intact.
if ($uri !== '/' && is_file($requestedFile)) {
    return false;
}

// Everything else goes through Laravel, with gzip applied to text responses.
ob_start();

require_once $publicPath . '/index.php';

$contents = ob_get_clean();

if (! is_string($contents) || $contents === '') {
    return true;
}

$contentType = '';
foreach (headers_list() as $headerLine) {
    if (stripos($headerLine, 'Content-Type:') === 0) {
        $contentType = strtolower(trim(substr($headerLine, strlen('Content-Type:'))));
        break;
    }
}

$isText = $contentType === ''
    || str_contains($contentType, 'text/')
    || str_contains($contentType, 'application/json')
    || str_contains($contentType, 'application/javascript')
    || str_contains($contentType, 'application/xml')
    || str_contains($contentType, 'image/svg+xml');

$alreadyEncoded = false;
foreach (headers_list() as $headerLine) {
    if (stripos($headerLine, 'Content-Encoding:') === 0) {
        $alreadyEncoded = true;
        break;
    }
}

if ($acceptsGzip && $isText && ! $alreadyEncoded && strlen($contents) >= 1024) {
    $payload = gzencode($contents, 6);

    if ($payload !== false) {
        header_remove('Content-Length');
        header('Content-Encoding: gzip');
        header('Vary: Accept-Encoding');
        $contents = $payload;
    }
}

echo $contents;

return true;
