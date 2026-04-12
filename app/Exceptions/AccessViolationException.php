<?php

namespace App\Exceptions;

use Illuminate\Contracts\Debug\ShouldntReport;
use RuntimeException;

class AccessViolationException extends RuntimeException implements ShouldntReport
{
    public function __construct(
        string $message = 'Access denied.',
        private readonly string $reason = 'runtime validation failed',
        private readonly array $context = [],
    ) {
        parent::__construct($message);
    }

    public function reason(): string
    {
        return $this->reason;
    }

    public function context(): array
    {
        return $this->context;
    }
}
