<?php

namespace Tests\Unit;

use App\Utils\helpers;
use PHPUnit\Framework\TestCase;

class HelpersProductJsonFormattingTest extends TestCase
{
    public function test_set_data_format_for_json_data_handles_repeatedly_encoded_attributes(): void
    {
        $encodedNull = 'null';
        for ($i = 0; $i < 3; $i++) {
            $encodedNull = json_encode($encodedNull);
        }

        $formatted = helpers::set_data_format_for_json_data([
            'colors' => '[]',
            'color_image' => '[]',
            'category_ids' => '[{"id":"8","position":1}]',
            'images' => '["a.webp"]',
            'attributes' => $encodedNull,
            'choice_options' => '[]',
            'variation' => '[]',
        ]);

        $this->assertSame([], $formatted['attributes']);
        $this->assertSame([['id' => '8', 'position' => 1]], $formatted['category_ids']);
        $this->assertSame(['a.webp'], $formatted['images']);
        $this->assertSame([], $formatted['choice_options']);
        $this->assertSame([], $formatted['variation']);
    }
}
