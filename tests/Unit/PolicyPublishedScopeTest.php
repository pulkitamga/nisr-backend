<?php

namespace Tests\Unit;

use App\Models\Policy;
use Tests\TestCase;

class PolicyPublishedScopeTest extends TestCase
{
    public function test_published_scope_uses_published_at_only(): void
    {
        $query = Policy::query()->published();
        $sql = str_replace(['`', '"'], '', $query->toSql());

        $this->assertStringNotContainsString('status', $sql);
        $this->assertStringContainsString('published_at is not null', $sql);
    }
}
