<?php

namespace Tests\Feature;

use Tests\TestCase;

class RichTextToPlainTextHelperTest extends TestCase
{
    public function test_rich_text_to_plain_text_decodes_entities_and_removes_tags(): void
    {
        $value = '<p>Appreciation&nbsp;&nbsp;<strong>Team</strong></p>';

        $this->assertSame('Appreciation Team', richTextToPlainText($value));
    }
}
