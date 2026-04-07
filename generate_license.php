<?php

if (!function_exists('sodium_crypto_sign_detached')) {
    fwrite(STDERR, "Sodium extension is required.\n");
    exit(1);
}

function b64url(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// 🔐 PASTE YOUR PRIVATE KEY HERE
$privateKeyBase64 = 'xOERGqvBdJNh70jI4sr2yKaqVlXxN3uLg7tigjfhVdeAbKdF4t5q8eISVV0praSfCHjcaZWAgqbmsBoQy4mQEQ==';
$privateKeyBase64 = trim($privateKeyBase64);

if ($privateKeyBase64 === '' || str_contains($privateKeyBase64, 'PASTE YOUR PRIVATE KEY HERE')) {
    fwrite(STDERR, "Set \$privateKeyBase64 before running this script.\n");
    exit(1);
}

$privateKey = base64_decode($privateKeyBase64, true);

if ($privateKey === false || strlen($privateKey) !== SODIUM_CRYPTO_SIGN_SECRETKEYBYTES) {
    fwrite(STDERR, "Private key is invalid. Expected base64-encoded sodium secret key.\n");
    exit(1);
}

$payload = [
    'v' => 1,
    'p' => 'alnisr2', // must match LICENSE_PRODUCT
    'domains' => ['alnisr2.test'],  // owner will add allowed domains
    'ips' => [],      // owner will add allowed IPs (optional)
    'cidrs' => [],    // optional
    'instance' => null,
    'iat' => time(),
    'nbf' => time(),
    'exp' => null,    // null = lifetime license
];


$jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);

if ($jsonPayload === false) {
    fwrite(STDERR, "Failed to encode payload as JSON.\n");
    exit(1);
}

$signature = sodium_crypto_sign_detached($jsonPayload, $privateKey);

$license = 'LIC-' . b64url($jsonPayload) . '.' . b64url($signature);

echo "============================\n";
echo "LICENSE KEY:\n\n";
echo $license . "\n";
echo "============================\n";
