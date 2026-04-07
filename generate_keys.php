<?php

if (!function_exists('sodium_crypto_sign_keypair')) {
    fwrite(STDERR, "Sodium extension is required.\n");
    exit(1);
}

$keypair = sodium_crypto_sign_keypair();

$publicKey  = sodium_crypto_sign_publickey($keypair);
$privateKey = sodium_crypto_sign_secretkey($keypair);

echo "============================\n";
echo "PUBLIC KEY (put in LicenseService::PUBLIC_KEY):\n";
echo base64_encode($publicKey) . "\n\n";

echo "PRIVATE KEY (KEEP SECRET!):\n";
echo base64_encode($privateKey) . "\n";
echo "============================\n";
