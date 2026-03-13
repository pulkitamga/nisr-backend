<?php

namespace App\Domain\Stock\DTO;

class StockValidationResult
{
    private array $failures = [];

    public function __construct(
        private bool $passed = true
    ) {}

    public function addFailure(array $failure): void
    {
        $this->passed = false;
        $this->failures[] = $failure;
    }

    public function passed(): bool
    {
        return $this->passed;
    }

    public function failed(): bool
    {
        return !$this->passed;
    }

    public function failures(): array
    {
        return $this->failures;
    }

    public static function success(): self
    {
        return new self(true);
    }
}

