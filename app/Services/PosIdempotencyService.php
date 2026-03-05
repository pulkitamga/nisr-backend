<?php

namespace App\Services;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class PosIdempotencyService
{
    public function execute(
        string $action,
        string $idempotencyKey,
        string $actorType,
        int $actorId,
        \Closure $callback,
        int $ttlSeconds = 180
    ): mixed {
        $idempotencyKey = trim($idempotencyKey);
        if ($idempotencyKey === '') {
            throw ValidationException::withMessages([
                'idempotency_key' => [translate('invalid_request')],
            ]);
        }

        $cacheKey = $this->buildCacheKey($action, $idempotencyKey, $actorType, $actorId);
        $existing = Cache::get($cacheKey);
        if (!is_null($existing)) {
            return $existing;
        }

        $lock = Cache::lock($cacheKey . ':lock', 10);
        try {
            return $lock->block(2, function () use ($cacheKey, $callback, $ttlSeconds) {
                $existing = Cache::get($cacheKey);
                if (!is_null($existing)) {
                    return $existing;
                }

                $result = $callback();
                Cache::put($cacheKey, $result, now()->addSeconds(max(30, $ttlSeconds)));

                return $result;
            });
        } catch (LockTimeoutException) {
            $existing = Cache::get($cacheKey);
            if (!is_null($existing)) {
                return $existing;
            }
            throw ValidationException::withMessages([
                'idempotency_key' => [translate('please_try_again')],
            ]);
        }
    }

    private function buildCacheKey(string $action, string $idempotencyKey, string $actorType, int $actorId): string
    {
        return 'pos_idem:'
            . sha1($actorType . '|' . max(0, $actorId) . '|' . trim($action) . '|' . trim($idempotencyKey));
    }
}
