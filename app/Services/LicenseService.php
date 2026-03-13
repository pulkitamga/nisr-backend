<?php


namespace App\Services;

use Illuminate\Support\Facades\Log;

class LicenseService
{
    protected const PUBLIC_KEY = 'gGynReLeavHiElVdKa2knwh43GmVgIKm5rAaEMuJkBE=';

    public function validate(): bool
    {
        if ($this->shouldBypassValidation()) {
            return true;
        }

        if (!function_exists('sodium_crypto_sign_verify_detached')) {
            $this->violation('Sodium extension is required for license verification.');
        }

        $license = $this->readLicenseKey();

        if (!$license) {
            $this->violation('License key missing.');
        }

        [$payloadJson, $signature, $payload] = $this->decodeLicense($license);

        if (!$this->verifySignature($payloadJson, $signature)) {
            $this->violation('License signature is invalid.');
        }

        $this->validatePayload($payload);

        return true;
    }

    protected function shouldBypassValidation(): bool
    {
        if (app()->runningInConsole()) {
            return true;
        }

        return config('license.mode', 'development') === 'development';
    }

    protected function readLicenseKey(): ?string
    {
        $path = (string) config('license.file', storage_path('framework/.license'));

        if (is_file($path) && is_readable($path)) {
            $license = trim((string) file_get_contents($path));

            if ($license !== '') {
                return $license;
            }
        }

        $fallback = trim((string) config('license.key', ''));

        if ($fallback === '' || $fallback === 'DEV-UNLICENSED') {
            return null;
        }

        return $fallback;
    }

    protected function decodeLicense(string $license): array
    {
        $license = trim($license);

        if (!str_starts_with($license, 'LIC-')) {
            $this->violation('License format is invalid.');
        }

        $encoded = substr($license, 4);
        $parts = explode('.', $encoded, 2);

        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            $this->violation('License payload/signature is missing.');
        }

        $payloadJson = $this->base64UrlDecode($parts[0]);
        $signature = $this->base64UrlDecode($parts[1]);

        if ($payloadJson === null || $signature === null) {
            $this->violation('License encoding is invalid.');
        }

        $payload = json_decode($payloadJson, true);

        if (!is_array($payload)) {
            $this->violation('License payload JSON is invalid.');
        }

        return [$payloadJson, $signature, $payload];
    }

    protected function verifySignature(string $payloadJson, string $signature): bool
    {
        $publicKeyBase64 = trim(static::PUBLIC_KEY);

        if ($publicKeyBase64 === '' || $publicKeyBase64 === 'PASTE_YOUR_PUBLIC_KEY_HERE') {
            $this->violation('Public key is not configured.');
        }

        $publicKey = base64_decode($publicKeyBase64, true);

        if ($publicKey === false || strlen($publicKey) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
            $this->violation('Public key format is invalid.');
        }

        return sodium_crypto_sign_verify_detached($signature, $payloadJson, $publicKey);
    }

    protected function validatePayload(array $payload): void
    {
        $version = $payload['v'] ?? null;

        if ((int) $version !== 1) {
            $this->violation('Unsupported license version.');
        }

        $expectedProduct = (string) config('license.product', 'alnisr2');
        $licensedProduct = (string) ($payload['p'] ?? '');

        if ($licensedProduct === '' || !hash_equals($expectedProduct, $licensedProduct)) {
            $this->violation('License product mismatch.');
        }

        $now = time();

        $nbf = $payload['nbf'] ?? null;
        if ($nbf !== null && (!is_numeric($nbf) || (int) $nbf > $now)) {
            $this->violation('License is not active yet.');
        }

        $exp = $payload['exp'] ?? null;
        if ($exp !== null && (!is_numeric($exp) || (int) $exp < $now)) {
            $this->violation('License has expired.');
        }

        $currentHost = $this->normalizeHost((string) request()->getHost());
        if ($currentHost === '') {
            $this->violation('Unable to resolve request host.');
        }

        $domains = $this->normalizeDomains($payload['domains'] ?? []);
        if ($domains !== [] && !$this->domainAllowed($currentHost, $domains)) {
            $this->violation('Current domain is not allowed by license.');
        }

        $ips = $this->normalizeStringArray($payload['ips'] ?? []);
        $cidrs = $this->normalizeStringArray($payload['cidrs'] ?? []);

        if (($ips !== [] || $cidrs !== []) && !$this->ipAllowed(request()->ip(), $ips, $cidrs)) {
            $this->violation('Current IP is not allowed by license.');
        }

        $instance = $payload['instance'] ?? null;
        if (is_string($instance) && trim($instance) !== '') {
            $instanceHost = $this->normalizeHost($instance);

            if ($instanceHost === '' || !$this->hostMatches($currentHost, $instanceHost)) {
                $this->violation('License instance restriction mismatch.');
            }
        }
    }

    protected function normalizeDomains($domains): array
    {
        $normalized = [];

        foreach ($this->normalizeStringArray($domains) as $domain) {
            $isWildcard = str_starts_with($domain, '*.');
            $target = $isWildcard ? substr($domain, 2) : $domain;
            $host = $this->normalizeHost($target);

            if ($host === '') {
                continue;
            }

            $normalized[] = $isWildcard ? '*.' . $host : $host;
        }

        return array_values(array_unique($normalized));
    }

    protected function normalizeStringArray($value): array
    {
        if (is_string($value)) {
            $value = explode(',', $value);
        }

        if (!is_array($value)) {
            return [];
        }

        $result = [];

        foreach ($value as $item) {
            if (!is_string($item)) {
                continue;
            }

            $item = trim($item);

            if ($item !== '') {
                $result[] = $item;
            }
        }

        return array_values($result);
    }

    protected function normalizeHost(string $host): string
    {
        $host = trim(strtolower($host));

        if ($host === '') {
            return '';
        }

        if (str_contains($host, '://')) {
            $parsed = parse_url($host, PHP_URL_HOST);

            if (is_string($parsed) && $parsed !== '') {
                $host = strtolower($parsed);
            }
        }

        $host = preg_replace('/:\d+$/', '', $host);

        return preg_replace('/^www\./', '', $host);
    }

    protected function domainAllowed(string $currentHost, array $allowedDomains): bool
    {
        foreach ($allowedDomains as $allowed) {
            if ($this->hostMatches($currentHost, $allowed)) {
                return true;
            }
        }

        return false;
    }

    protected function hostMatches(string $currentHost, string $allowedHost): bool
    {
        if ($currentHost === $allowedHost) {
            return true;
        }

        if (str_starts_with($allowedHost, '*.')) {
            $suffix = substr($allowedHost, 2);

            if ($suffix !== '' && ($currentHost === $suffix || str_ends_with($currentHost, '.' . $suffix))) {
                return true;
            }
        }

        return false;
    }

    protected function ipAllowed(?string $currentIp, array $ips, array $cidrs): bool
    {
        if ($ips === [] && $cidrs === []) {
            return true;
        }

        if (!is_string($currentIp) || $currentIp === '') {
            return false;
        }

        if (in_array($currentIp, $ips, true)) {
            return true;
        }

        foreach ($cidrs as $cidr) {
            if ($this->ipInCidr($currentIp, $cidr)) {
                return true;
            }
        }

        return false;
    }

    protected function ipInCidr(string $ip, string $cidr): bool
    {
        if (!str_contains($cidr, '/')) {
            return false;
        }

        [$network, $bits] = explode('/', $cidr, 2);
        $network = trim($network);
        $bits = trim($bits);

        if ($network === '' || $bits === '' || !ctype_digit($bits)) {
            return false;
        }

        $bits = (int) $bits;
        $ipBin = inet_pton($ip);
        $networkBin = inet_pton($network);

        if ($ipBin === false || $networkBin === false || strlen($ipBin) !== strlen($networkBin)) {
            return false;
        }

        $maxBits = strlen($ipBin) * 8;

        if ($bits < 0 || $bits > $maxBits) {
            return false;
        }

        $fullBytes = intdiv($bits, 8);
        $remainingBits = $bits % 8;

        if ($fullBytes > 0 && substr($ipBin, 0, $fullBytes) !== substr($networkBin, 0, $fullBytes)) {
            return false;
        }

        if ($remainingBits === 0) {
            return true;
        }

        $mask = (0xFF00 >> $remainingBits) & 0xFF;

        return (ord($ipBin[$fullBytes]) & $mask) === (ord($networkBin[$fullBytes]) & $mask);
    }

    protected function base64UrlDecode(string $value): ?string
    {
        $padding = (4 - (strlen($value) % 4)) % 4;
        $decoded = base64_decode(strtr($value, '-_', '+/') . str_repeat('=', $padding), true);

        return $decoded === false ? null : $decoded;
    }

    protected function violation(string $reason)
    {
        Log::warning('License validation failed', [
            'reason' => $reason,
            'host' => request()->getHost(),
            'ip' => request()->ip(),
        ]);

        abort(403, 'Application license is invalid.');
    }
}
