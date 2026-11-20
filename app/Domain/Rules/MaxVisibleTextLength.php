<?php

namespace App\Domain\Rules;

use App\Domain\Services\MarkdownRenderer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Caps the Record body by the text a reader would actually see, not the
 * raw Markdown source — formatting marks like ** or # shouldn't eat into
 * someone's writing room just because the editor expresses them as a few
 * extra characters.
 */
class MaxVisibleTextLength implements ValidationRule
{
    public function __construct(protected int $max) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        $html = (new MarkdownRenderer)->toHtml($value);
        $visibleLength = mb_strlen(trim(strip_tags($html)));

        if ($visibleLength > $this->max) {
            $fail("The :attribute must not have more than {$this->max} characters of visible text.");
        }
    }
}
