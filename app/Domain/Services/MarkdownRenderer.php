<?php

namespace App\Domain\Services;

use League\CommonMark\CommonMarkConverter;

/**
 * Renders a Record's body (written via the WYSIWYG editor, stored as
 * Markdown) to HTML — used both to show the record to a trusted contact and
 * to pre-fill the editor on load. Raw HTML the author typed is escaped
 * rather than executed, since this output ends up in a browser neither the
 * author nor we control.
 */
class MarkdownRenderer
{
    public function toHtml(?string $markdown): string
    {
        if (blank($markdown)) {
            return '';
        }

        $converter = new CommonMarkConverter([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'renderer' => [
                'soft_break' => "<br />\n",
            ],
        ]);

        return (string) $converter->convert($markdown);
    }
}
