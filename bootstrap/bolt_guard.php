<?php

$metaPath = __DIR__ . '/cache/build-meta.php';

if (!is_file($metaPath)) {
    return;
}

$meta = require $metaPath;

if (!is_array($meta) || !($meta['encrypted'] ?? false)) {
    return;
}

$loaderName = (string) ($meta['required_loader'] ?? 'bolt');
$loaderReady = extension_loaded($loaderName) || function_exists('bolt_decrypt');

if ($loaderReady) {
    return;
}

if (PHP_SAPI === 'cli') {
    fwrite(STDERR, "Required runtime loader is unavailable.\n");
    exit(1);
}

http_response_code(503);
header('Content-Type: text/plain; charset=UTF-8');
echo 'Service unavailable.';
exit(1);
