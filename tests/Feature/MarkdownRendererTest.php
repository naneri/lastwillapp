<?php

namespace Tests\Feature;

use App\Domain\Services\MarkdownRenderer;
use Tests\TestCase;

class MarkdownRendererTest extends TestCase
{
    public function test_it_renders_basic_formatting(): void
    {
        $html = (new MarkdownRenderer)->toHtml("# Title\n\n**bold** and *italic* and [a link](https://example.com)\n\n- one\n- two\n\n> quoted\n\n---");

        $this->assertStringContainsString('<h1>Title</h1>', $html);
        $this->assertStringContainsString('<strong>bold</strong>', $html);
        $this->assertStringContainsString('<em>italic</em>', $html);
        $this->assertStringContainsString('<a href="https://example.com">a link</a>', $html);
        $this->assertStringContainsString('<li>one</li>', $html);
        $this->assertStringContainsString('<blockquote>', $html);
        $this->assertStringContainsString('<hr', $html);
    }

    public function test_it_escapes_raw_html_instead_of_executing_it(): void
    {
        $html = (new MarkdownRenderer)->toHtml('<script>alert(1)</script>');

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_it_renders_a_single_line_break_as_br(): void
    {
        $html = (new MarkdownRenderer)->toHtml("line one\nline two");

        $this->assertStringContainsString("line one<br", $html);
    }

    public function test_blank_input_renders_to_an_empty_string(): void
    {
        $this->assertSame('', (new MarkdownRenderer)->toHtml(null));
        $this->assertSame('', (new MarkdownRenderer)->toHtml(''));
    }
}
