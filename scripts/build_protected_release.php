<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

require __DIR__ . '/../vendor/autoload.php';

final class ProtectedReleaseBuilder
{
    private array $excludePaths = [
        '.claude',
        '.codex',
        '.git',
        '.github',
        '.idea',
        '.kilocode',
        '.playwright-mcp',
        '.remember',
        '.roo',
        '.specify',
        '.superpowers',
        '.vscode',
        'build',
        'deployment',
        'docs',
        'output',
        'phpBolt',
        'review-screenshots',
        'scripts',
        'storage/app/encryption',
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/logs',
        'tests',
        'tmp',
    ];

    private array $excludeFilePatterns = [
        '*.sql',
        '*.zip',
        '.env',
        '.env.*',
    ];

    private array $encryptTargets = [
        'app/Console',
        'app/Core',
        'app/Exceptions/AccessViolationException.php',
        'app/Http/Controllers/Admin',
        'app/Http/Middleware/VerifyAccess.php',
        'app/Services',
    ];

    public function run(array $options): void
    {
        $root = realpath(__DIR__ . '/..');
        if ($root === false) {
            throw new RuntimeException('Unable to resolve repository root.');
        }

        $customerId = $this->requiredOption($options, 'customer');
        $buildId = $this->requiredOption($options, 'build');
        $stateFile = $this->requiredExistingFile($options, 'state-file');
        $boltBin = $this->requiredExistingFile($options, 'bolt-bin');
        $customerKey = $this->requiredOption($options, 'key');
        $workspace = $this->resolvePath($root, $options['workspace'] ?? 'build/workspace');
        $artifact = $this->resolvePath($root, $options['artifact'] ?? 'build/release.zip');
        $releaseRoot = $workspace . DIRECTORY_SEPARATOR . 'release';
        $encryptedRoot = $workspace . DIRECTORY_SEPARATOR . 'encrypted';

        $this->resetDirectory($workspace);
        $this->copySource($root, $releaseRoot);
        $this->installRuntimeState($stateFile, $releaseRoot);
        $this->writeBuildMeta($releaseRoot, $customerId, $buildId, encrypted: false);
        $this->encryptTargets($root, $releaseRoot, $encryptedRoot, $boltBin, $customerKey);
        $this->writeBuildMeta($releaseRoot, $customerId, $buildId, encrypted: true);
        $this->validateRelease($releaseRoot);
        $this->packageRelease($releaseRoot, $artifact);

        echo "Protected release created:" . PHP_EOL;
        echo "  Artifact: {$artifact}" . PHP_EOL;
        echo "  Customer: {$customerId}" . PHP_EOL;
        echo "  Build: {$buildId}" . PHP_EOL;
    }

    private function copySource(string $root, string $destination): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $sourcePath = $item->getPathname();
            $relativePath = ltrim(str_replace($root, '', $sourcePath), DIRECTORY_SEPARATOR);

            if ($relativePath === '' || $this->shouldExclude($relativePath)) {
                continue;
            }

            $targetPath = $destination . DIRECTORY_SEPARATOR . $relativePath;

            if ($item->isDir()) {
                if (!is_dir($targetPath) && !mkdir($targetPath, 0775, true) && !is_dir($targetPath)) {
                    throw new RuntimeException("Unable to create directory: {$targetPath}");
                }

                continue;
            }

            $parent = dirname($targetPath);
            if (!is_dir($parent) && !mkdir($parent, 0775, true) && !is_dir($parent)) {
                throw new RuntimeException("Unable to create directory: {$parent}");
            }

            if (!copy($sourcePath, $targetPath)) {
                throw new RuntimeException("Unable to copy file: {$relativePath}");
            }
        }
    }

    private function encryptTargets(
        string $root,
        string $releaseRoot,
        string $encryptedRoot,
        string $boltBin,
        string $customerKey,
    ): void {
        foreach ($this->encryptTargets as $target) {
            $source = $releaseRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $target);

            if (!file_exists($source)) {
                continue;
            }

            $destination = $encryptedRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $target);
            $this->deletePath($destination);

            $process = new Process([
                PHP_BINARY,
                $boltBin,
                '-encrypt',
                $source,
                $customerKey,
                $destination,
            ], $root, [
                'PHPBOLT_CUSTOMER_ID' => basename($root) . ':' . md5($target),
            ]);

            $process->setTimeout(600);
            $process->mustRun();

            $this->replacePath($source, $destination);
        }
    }

    private function installRuntimeState(string $stateFile, string $releaseRoot): void
    {
        $destination = $releaseRoot . DIRECTORY_SEPARATOR . 'storage/framework/.runtime_state';
        $parent = dirname($destination);

        if (!is_dir($parent) && !mkdir($parent, 0775, true) && !is_dir($parent)) {
            throw new RuntimeException("Unable to create runtime state directory: {$parent}");
        }

        if (!copy($stateFile, $destination)) {
            throw new RuntimeException('Unable to install runtime state file into release workspace.');
        }
    }

    private function writeBuildMeta(string $releaseRoot, string $customerId, string $buildId, bool $encrypted): void
    {
        $metaPath = $releaseRoot . DIRECTORY_SEPARATOR . 'bootstrap/cache/build-meta.php';
        $parent = dirname($metaPath);

        if (!is_dir($parent) && !mkdir($parent, 0775, true) && !is_dir($parent)) {
            throw new RuntimeException("Unable to create build meta directory: {$parent}");
        }

        $payload = var_export([
            'customer_id' => $customerId,
            'build_id' => $buildId,
            'product' => 'Elnisr',
            'encrypted' => $encrypted,
            'required_loader' => 'bolt',
            'built_at' => gmdate('c'),
            'targets' => $this->encryptTargets,
        ], true);

        $contents = "<?php\n\nreturn {$payload};\n";

        if (file_put_contents($metaPath, $contents) === false) {
            throw new RuntimeException('Unable to write build meta file.');
        }
    }

    private function validateRelease(string $releaseRoot): void
    {
        $requiredFiles = [
            'bootstrap/bolt_guard.php',
            'bootstrap/cache/build-meta.php',
            'storage/framework/.runtime_state',
            'app/Services/AccessGuard.php',
        ];

        foreach ($requiredFiles as $file) {
            $path = $releaseRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);

            if (!is_file($path)) {
                throw new RuntimeException("Missing required release file: {$file}");
            }
        }

        foreach ($this->encryptTargets as $target) {
            $path = $releaseRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $target);

            if (is_file($path)) {
                $this->assertEncryptedFile($path);
                continue;
            }

            if (is_dir($path)) {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
                );

                foreach ($iterator as $file) {
                    if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
                        $this->assertEncryptedFile($file->getPathname());
                    }
                }
            }
        }
    }

    private function assertEncryptedFile(string $path): void
    {
        $contents = (string) file_get_contents($path);

        if (!str_contains($contents, 'bolt_decrypt(')) {
            throw new RuntimeException("Encrypted output validation failed for {$path}");
        }
    }

    private function packageRelease(string $releaseRoot, string $artifact): void
    {
        $parent = dirname($artifact);
        if (!is_dir($parent) && !mkdir($parent, 0775, true) && !is_dir($parent)) {
            throw new RuntimeException("Unable to create artifact directory: {$parent}");
        }

        $zip = new ZipArchive();
        if ($zip->open($artifact, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create artifact archive.');
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($releaseRoot, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $relativePath = ltrim(str_replace($releaseRoot, '', $file->getPathname()), DIRECTORY_SEPARATOR);
            $zip->addFile($file->getPathname(), str_replace(DIRECTORY_SEPARATOR, '/', $relativePath));
        }

        $zip->close();
    }

    private function shouldExclude(string $relativePath): bool
    {
        $relativePath = str_replace('\\', '/', $relativePath);

        foreach ($this->excludePaths as $excluded) {
            if ($relativePath === $excluded || str_starts_with($relativePath, $excluded . '/')) {
                return true;
            }
        }

        foreach ($this->excludeFilePatterns as $pattern) {
            if (fnmatch($pattern, basename($relativePath))) {
                return true;
            }
        }

        return false;
    }

    private function replacePath(string $source, string $replacement): void
    {
        $this->deletePath($source);

        $parent = dirname($source);
        if (!is_dir($parent) && !mkdir($parent, 0775, true) && !is_dir($parent)) {
            throw new RuntimeException("Unable to recreate directory: {$parent}");
        }

        if (!rename($replacement, $source)) {
            throw new RuntimeException("Unable to place encrypted output for {$source}");
        }
    }

    private function deletePath(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        if (is_file($path)) {
            unlink($path);
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            $itemPath = $item->getPathname();

            if ($item->isDir()) {
                rmdir($itemPath);
                continue;
            }

            unlink($itemPath);
        }

        rmdir($path);
    }

    private function resetDirectory(string $path): void
    {
        $this->deletePath($path);

        if (!mkdir($path, 0775, true) && !is_dir($path)) {
            throw new RuntimeException("Unable to create workspace: {$path}");
        }
    }

    private function resolvePath(string $root, string $path): string
    {
        if (str_starts_with($path, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:\\\\/', $path)) {
            return $path;
        }

        return $root . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }

    private function requiredOption(array $options, string $name): string
    {
        $value = trim((string) ($options[$name] ?? ''));

        if ($value === '') {
            throw new RuntimeException("Missing required option --{$name}");
        }

        return $value;
    }

    private function requiredExistingFile(array $options, string $name): string
    {
        $path = $this->resolvePath(realpath(__DIR__ . '/..') ?: __DIR__, $this->requiredOption($options, $name));

        if (!is_file($path)) {
            throw new RuntimeException("Required file not found for --{$name}: {$path}");
        }

        return $path;
    }
}

function parseOptions(array $argv): array
{
    $options = [];

    foreach (array_slice($argv, 1) as $argument) {
        if (!str_starts_with($argument, '--')) {
            continue;
        }

        [$key, $value] = array_pad(explode('=', substr($argument, 2), 2), 2, '');
        $options[$key] = $value;
    }

    return $options;
}

try {
    $builder = new ProtectedReleaseBuilder();
    $builder->run(parseOptions($_SERVER['argv'] ?? []));
} catch (Throwable $throwable) {
    fwrite(STDERR, $throwable->getMessage() . PHP_EOL);
    exit(1);
}
