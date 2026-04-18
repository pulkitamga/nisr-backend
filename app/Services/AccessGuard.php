<?php

namespace App\Services;

use App\Exceptions\AccessViolationException;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AccessGuard
{
    public function __construct(
        private readonly Application $app,
        private readonly ConfigRepository $config,
        private readonly Request $request,
    ) {
    }

    public function ensureValid(): void
    {
        $this->validate();
    }

    public function validate(): bool
    {
        $context = $this->resolveRuntimeContext();

        $this->ensureStateFilePresent();
        $this->ensureRuntimeSupport();

        $state = $this->readState();
        [$payloadJson, $signature, $payload] = $this->decodeState($state);

        if (!$this->verifySignature($payloadJson, $signature)) {
            $this->violation('Runtime signature is invalid.', $context);
        }

        $this->validatePayload($payload, $context);
        $this->validateBuildMeta($payload, $context);
        $this->validateBoltLoader($context);

        return true;
    }

    public function shouldEnforce(): bool
    {
        if ($this->app->runningInConsole()) {
            return $this->shouldEnforceConsoleCommand($this->currentConsoleCommand());
        }

        if ($this->shouldBypassHttpRequest()) {
            return false;
        }

        return $this->matchesPathPattern($this->request->path(), $this->protectedPaths());
    }

    public function shouldBypassHttpRequest(): bool
    {
        return $this->request->is('api', 'api/*');
    }

    public function shouldEnforceConsoleCommand(?string $commandName): bool
    {
        return $commandName !== null && $this->matchesCommandPattern($commandName, $this->protectedConsoleCommands());
    }

    public function currentConsoleCommand(): ?string
    {
        $argv = $_SERVER['argv'] ?? [];

        return isset($argv[1]) && is_string($argv[1]) && $argv[1] !== '' ? $argv[1] : null;
    }

    public function hasReadableStateFile(): bool
    {
        $path = $this->stateFilePath();

        return is_file($path) && is_readable($path);
    }

    public function stateFilePath(): string
    {
        $configured = (string) $this->config->get('access.file', storage_path('framework/.runtime_state'));

        if ($configured === '') {
            return storage_path('framework/.runtime_state');
        }

        return $this->isAbsolutePath($configured) ? $configured : base_path($configured);
    }

    public function buildMetaPath(): string
    {
        $configured = (string) $this->config->get('access.build_meta', base_path('bootstrap/cache/build-meta.php'));

        if ($configured === '') {
            return base_path('bootstrap/cache/build-meta.php');
        }

        return $this->isAbsolutePath($configured) ? $configured : base_path($configured);
    }

    public function loadBuildMeta(): array
    {
        $path = $this->buildMetaPath();

        if (!is_file($path)) {
            return [];
        }

        $meta = require $path;

        return is_array($meta) ? $meta : [];
    }

    private function protectedPaths(): array
    {
        return (array) $this->config->get('access.protected_paths', []);
    }

    private function protectedConsoleCommands(): array
    {
        return (array) $this->config->get('access.protected_console_commands', []);
    }

    private function ensureStateFilePresent(): void
    {
        $path = $this->stateFilePath();

        if (!is_file($path)) {
            $this->violation('Runtime state file is missing.');
        }

        if (!is_readable($path)) {
            $this->violation('Runtime state file is not readable.');
        }
    }

    private function ensureRuntimeSupport(): void
    {
        if (!function_exists('sodium_crypto_sign_verify_detached')) {
            $this->violation('Sodium extension is required for runtime verification.');
        }
    }

    private function readState(): string
    {
        $state = trim((string) file_get_contents($this->stateFilePath()));

        if ($state === '') {
            $this->violation('Runtime state is empty.');
        }

        return $state;
    }

    private function decodeState(string $state): array
    {
        $prefix = $this->resolveAcceptedPrefix($state);

        if ($prefix === null) {
            $this->violation('Runtime state format is invalid.');
        }

        $encoded = substr($state, strlen($prefix));
        $parts = explode('.', $encoded, 2);

        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            $this->violation('Runtime state payload or signature is missing.');
        }

        $payloadJson = $this->base64UrlDecode($parts[0]);
        $signature = $this->base64UrlDecode($parts[1]);

        if ($payloadJson === null || $signature === null) {
            $this->violation('Runtime state encoding is invalid.');
        }

        $payload = json_decode($payloadJson, true);

        if (!is_array($payload)) {
            $this->violation('Runtime state JSON is invalid.');
        }

        return [$payloadJson, $signature, $payload];
    }

    private function verifySignature(string $payloadJson, string $signature): bool
    {
        $publicKeyBase64 = trim((string) $this->config->get('access.public_key', ''));

        if ($publicKeyBase64 === '') {
            $this->violation('Runtime public key is not configured.');
        }

        $publicKey = base64_decode($publicKeyBase64, true);

        if ($publicKey === false || strlen($publicKey) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
            $this->violation('Runtime public key format is invalid.');
        }

        return sodium_crypto_sign_verify_detached($signature, $payloadJson, $publicKey);
    }

    private function validatePayload(array $payload, array $context): void
    {
        if ((int) ($payload['v'] ?? 0) !== 1) {
            $this->violation('Unsupported runtime state version.', $context);
        }

        $expectedProduct = trim((string) $this->config->get('access.product', ''));
        $product = trim((string) ($payload['p'] ?? ''));

        if ($expectedProduct === '' || $product === '' || !hash_equals($expectedProduct, $product)) {
            $this->violation('Runtime product mismatch.', $context);
        }

        $now = time();
        $notBefore = $payload['nbf'] ?? null;
        $expiresAt = $payload['exp'] ?? null;

        if ($notBefore !== null && (!is_numeric($notBefore) || (int) $notBefore > $now)) {
            $this->violation('Runtime state is not active yet.', $context);
        }

        if ($expiresAt !== null && (!is_numeric($expiresAt) || (int) $expiresAt < $now)) {
            $this->violation('Runtime state has expired.', $context);
        }

        $currentHost = $context['host'] ?? '';
        $allowedHosts = $this->normalizeHosts($payload['hosts'] ?? $payload['domains'] ?? []);

        if ($currentHost === '') {
            $this->violation('Unable to resolve runtime host.', $context);
        }

        if ($allowedHosts !== [] && !$this->hostAllowed($currentHost, $allowedHosts)) {
            $this->violation('Runtime host is not allowed.', $context);
        }

        $currentIp = $context['ip'] ?? '';
        $ips = $this->normalizeStringArray($payload['ips'] ?? []);
        $cidrs = $this->normalizeStringArray($payload['cidrs'] ?? []);

        if (($ips !== [] || $cidrs !== []) && !$this->ipAllowed($currentIp, $ips, $cidrs)) {
            $this->violation('Runtime IP is not allowed.', $context);
        }

        $instance = $this->normalizeHost((string) ($payload['instance'] ?? ''));
        if ($instance !== '' && !$this->hostMatches($currentHost, $instance)) {
            $this->violation('Runtime instance mismatch.', $context);
        }
    }

    private function validateBuildMeta(array $payload, array $context): void
    {
        $meta = $this->loadBuildMeta();

        if ($meta === []) {
            return;
        }

        $buildId = trim((string) ($meta['build_id'] ?? ''));
        $customerId = trim((string) ($meta['customer_id'] ?? ''));
        $payloadBuildId = trim((string) ($payload['build'] ?? ''));
        $payloadCustomerId = trim((string) ($payload['customer'] ?? ''));

        if ($buildId !== '' && ($payloadBuildId === '' || !hash_equals($buildId, $payloadBuildId))) {
            $this->violation('Build marker mismatch.', $context);
        }

        if ($customerId !== '' && ($payloadCustomerId === '' || !hash_equals($customerId, $payloadCustomerId))) {
            $this->violation('Customer marker mismatch.', $context);
        }
    }

    private function validateBoltLoader(array $context): void
    {
        $meta = $this->loadBuildMeta();

        if (!(bool) ($meta['encrypted'] ?? false)) {
            return;
        }

        $requiredLoader = trim((string) ($meta['required_loader'] ?? 'bolt'));
        $loaderReady = extension_loaded($requiredLoader) || function_exists('bolt_decrypt');

        if (!$loaderReady) {
            $this->violation('Required runtime loader is unavailable.', $context);
        }
    }

    private function resolveRuntimeContext(): array
    {
        $command = $this->currentConsoleCommand();
        $mode = $this->app->runningInConsole() ? 'console' : 'http';
        $host = $mode === 'http' ? $this->normalizeHost((string) $this->request->getHost()) : $this->resolveConsoleHost();
        $requestIp = $mode === 'http' ? $this->normalizeIp((string) $this->request->ip()) : null;
        $ip = $mode === 'http' ? $this->resolveHttpIp() : $this->resolveConsoleIp($host);

        return [
            'mode' => $mode,
            'command' => $command,
            'host' => $host,
            'ip' => $ip,
            'request_ip' => $requestIp,
            'path' => $mode === 'http' ? $this->request->path() : null,
        ];
    }

    private function resolveHttpIp(): string
    {
        $candidates = [
            (string) $this->config->get('access.runtime_ip', ''),
            (string) env('APP_RUNTIME_IP', ''),
            (string) $this->request->server('SERVER_ADDR', ''),
            (string) $this->request->server('LOCAL_ADDR', ''),
        ];

        foreach ($candidates as $candidate) {
            $ip = $this->normalizeIp($candidate);

            if ($ip !== '') {
                return $ip;
            }
        }

        return $this->normalizeIp((string) $this->request->ip());
    }

    private function resolveConsoleHost(): string
    {
        $candidates = [
            (string) $this->config->get('access.runtime_host', ''),
            (string) env('APP_RUNTIME_HOST', ''),
            (string) parse_url((string) $this->config->get('app.url', ''), PHP_URL_HOST),
            (string) gethostname(),
            (string) php_uname('n'),
        ];

        foreach ($candidates as $candidate) {
            $host = $this->normalizeHost($candidate);

            if ($host !== '') {
                return $host;
            }
        }

        return '';
    }

    private function resolveConsoleIp(string $host): string
    {
        $candidates = [
            (string) $this->config->get('access.runtime_ip', ''),
            (string) env('APP_RUNTIME_IP', ''),
            (string) ($_SERVER['SERVER_ADDR'] ?? ''),
            (string) ($_SERVER['LOCAL_ADDR'] ?? ''),
        ];

        foreach ($candidates as $candidate) {
            $ip = $this->normalizeIp($candidate);

            if ($ip !== '') {
                return $ip;
            }
        }

        if ($host !== '') {
            $resolved = gethostbyname($host);

            if (is_string($resolved) && $resolved !== $host) {
                return $this->normalizeIp($resolved);
            }
        }

        return '';
    }

    private function normalizeHosts(array|string $hosts): array
    {
        $normalized = [];

        foreach ($this->normalizeStringArray($hosts) as $host) {
            $wildcard = str_starts_with($host, '*.');
            $value = $wildcard ? substr($host, 2) : $host;
            $normalizedHost = $this->normalizeHost($value);

            if ($normalizedHost === '') {
                continue;
            }

            $normalized[] = $wildcard ? '*.' . $normalizedHost : $normalizedHost;
        }

        return array_values(array_unique($normalized));
    }

    private function normalizeStringArray(array|string $value): array
    {
        if (is_string($value)) {
            $value = explode(',', $value);
        }

        if (!is_array($value)) {
            return [];
        }

        $normalized = [];

        foreach ($value as $item) {
            if (!is_string($item)) {
                continue;
            }

            $item = trim($item);

            if ($item !== '') {
                $normalized[] = $item;
            }
        }

        return array_values($normalized);
    }

    private function normalizeHost(string $host): string
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

    private function normalizeIp(string $ip): string
    {
        $ip = trim($ip);

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
    }

    private function hostAllowed(string $currentHost, array $allowedHosts): bool
    {
        foreach ($allowedHosts as $allowedHost) {
            if ($this->hostMatches($currentHost, $allowedHost)) {
                return true;
            }
        }

        return false;
    }

    private function hostMatches(string $currentHost, string $allowedHost): bool
    {
        if ($currentHost === $allowedHost) {
            return true;
        }

        if (!str_starts_with($allowedHost, '*.')) {
            return false;
        }

        $suffix = substr($allowedHost, 2);

        return $suffix !== '' && ($currentHost === $suffix || str_ends_with($currentHost, '.' . $suffix));
    }

    private function ipAllowed(string $currentIp, array $ips, array $cidrs): bool
    {
        if ($ips === [] && $cidrs === []) {
            return true;
        }

        if ($currentIp === '') {
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

    private function ipInCidr(string $ip, string $cidr): bool
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

    private function matchesPathPattern(string $path, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if ($pattern !== '' && $this->request->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    private function matchesCommandPattern(string $commandName, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            $pattern = trim((string) $pattern);

            if ($pattern === '') {
                continue;
            }

            if (fnmatch($pattern, $commandName)) {
                return true;
            }
        }

        return false;
    }

    private function base64UrlDecode(string $value): ?string
    {
        $padding = (4 - (strlen($value) % 4)) % 4;
        $decoded = base64_decode(strtr($value, '-_', '+/') . str_repeat('=', $padding), true);

        return $decoded === false ? null : $decoded;
    }

    private function resolveAcceptedPrefix(string $state): ?string
    {
        $prefixes = (array) $this->config->get('access.accepted_prefixes', [
            (string) $this->config->get('access.state_prefix', 'RTS-'),
        ]);

        foreach ($prefixes as $prefix) {
            $prefix = (string) $prefix;

            if ($prefix !== '' && str_starts_with($state, $prefix)) {
                return $prefix;
            }
        }

        return null;
    }

    private function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, DIRECTORY_SEPARATOR) || (bool) preg_match('/^[A-Za-z]:\\\\/', $path);
    }

    private function violation(string $reason, array $context = []): never
    {
        Log::warning('Runtime access validation failed', array_filter([
            'reason' => $reason,
            'mode' => $context['mode'] ?? null,
            'command' => $context['command'] ?? null,
            'host' => $context['host'] ?? null,
            'ip' => $context['ip'] ?? null,
            'request_ip' => $context['request_ip'] ?? null,
            'path' => $context['path'] ?? null,
        ], static fn ($value) => $value !== null && $value !== ''));

        throw new AccessViolationException(
            message: translate('access_Denied'),
            reason: $reason,
            context: $context,
        );
    }
}
