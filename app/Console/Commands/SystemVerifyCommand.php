<?php

namespace App\Console\Commands;

use App\Exceptions\AccessViolationException;
use App\Services\AccessGuard;
use Illuminate\Console\Command;

class SystemVerifyCommand extends Command
{
    protected $signature = 'system:verify';

    protected $description = 'Verify the local runtime state and required loader before deployment or startup.';

    public function handle(AccessGuard $accessGuard): int
    {
        try {
            $accessGuard->ensureValid();

            $this->info('Runtime verification passed.');

            return self::SUCCESS;
        } catch (AccessViolationException $exception) {
            $this->error($exception->reason());

            return self::FAILURE;
        }
    }
}
