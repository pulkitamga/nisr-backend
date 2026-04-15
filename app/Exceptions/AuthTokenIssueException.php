<?php

namespace App\Exceptions;

use Illuminate\Contracts\Debug\ShouldntReport;
use RuntimeException;

class AuthTokenIssueException extends RuntimeException implements ShouldntReport
{
    public function __construct(
        string $message,
        private readonly string $reason = 'token issuance failed',
    ) {
        parent::__construct($message);
    }

    public function reason(): string
    {
        return $this->reason;
    }
}
