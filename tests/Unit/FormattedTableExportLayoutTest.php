<?php

namespace Tests\Unit;

use App\Exports\FormattedTableExport;
use PHPUnit\Framework\TestCase;

class FormattedTableExportLayoutTest extends TestCase
{
    public function test_it_distributes_meta_segments_evenly_for_standard_widths(): void
    {
        $this->assertSame([3, 2], FormattedTableExport::resolveMetaSegments(5, 2));
        $this->assertSame([2, 2], FormattedTableExport::resolveMetaSegments(4, 2));
    }

    public function test_it_distributes_meta_segments_evenly_for_compact_widths(): void
    {
        $this->assertSame([2, 1, 1], FormattedTableExport::resolveMetaSegments(4, 3));
        $this->assertSame([2, 2, 1], FormattedTableExport::resolveMetaSegments(5, 3));
    }
}
