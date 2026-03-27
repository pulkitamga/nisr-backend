<?php

namespace Tests\Unit;

use Illuminate\Support\HtmlString;
use PHPUnit\Framework\TestCase;

use function App\Utils\support_ticket_message_html;

class SupportTicketMessageHtmlHelperTest extends TestCase
{
    public function test_it_escapes_text_and_linkifies_urls(): void
    {
        $html = support_ticket_message_html(
            "Pay here: https://example.com/pay?ticket=57&status=done.\n<script>alert('x')</script>"
        );

        $this->assertInstanceOf(HtmlString::class, $html);
        $this->assertStringContainsString(
            '<a href="https://example.com/pay?ticket=57&amp;status=done" target="_blank" rel="noopener noreferrer">https://example.com/pay?ticket=57&amp;status=done</a>.',
            $html->toHtml()
        );
        $this->assertStringContainsString("Pay here: ", $html->toHtml());
        $this->assertStringContainsString("<br>\n", $html->toHtml());
        $this->assertStringContainsString('&lt;script&gt;alert(&#039;x&#039;)&lt;/script&gt;', $html->toHtml());
    }

    public function test_it_returns_empty_html_string_for_empty_messages(): void
    {
        $html = support_ticket_message_html('');

        $this->assertSame('', $html->toHtml());
    }
}
