<?php

namespace Tests\Feature;

use App\Domain\Rules\MaxVisibleTextLength;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class MaxVisibleTextLengthTest extends TestCase
{
    public function test_markdown_syntax_does_not_count_toward_the_limit(): void
    {
        $body = '**'.str_repeat('a', 34).'**'; // 4 chars of ** markers, 34 visible chars

        $validator = Validator::make(['body' => $body], ['body' => [new MaxVisibleTextLength(34)]]);

        $this->assertTrue($validator->passes());
    }

    public function test_visible_text_over_the_limit_fails(): void
    {
        $body = str_repeat('a', 21);

        $validator = Validator::make(['body' => $body], ['body' => [new MaxVisibleTextLength(20)]]);

        $this->assertTrue($validator->fails());
    }

    public function test_link_urls_do_not_count_toward_the_limit(): void
    {
        $body = '[short](https://a-very-long-url.example.com/path/that/would/otherwise/blow/the/limit)';

        $validator = Validator::make(['body' => $body], ['body' => [new MaxVisibleTextLength(5)]]);

        $this->assertTrue($validator->passes());
    }
}
