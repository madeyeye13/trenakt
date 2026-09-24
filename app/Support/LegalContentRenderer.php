<?php

namespace App\Support;

/**
 * Splits admin-edited legal page text (Terms of Service / Privacy Policy,
 * stored as plain text in the terms_content/privacy_content settings) into
 * blocks for display. A block is a "heading" when it's a short line
 * starting with "<number>. " - the numbered-section style both
 * DefaultLegalContent drafts use and the admin editor's own hint
 * encourages - otherwise it's a plain paragraph.
 *
 * Deliberately not raw HTML: this content is rendered escaped (see
 * legal/show.blade.php), so a compromised admin account can't turn a free
 * text field into a stored-XSS vector aimed at every visitor of a public
 * page. That trades a bit of formatting power for real safety, which is
 * the right trade for a page every visitor loads unauthenticated.
 */
class LegalContentRenderer
{
    /**
     * @return array<int, array{heading: bool, text: string}>
     */
    public static function blocks(string $content): array
    {
        $paragraphs = preg_split('/\r?\n\s*\r?\n/', trim($content)) ?: [];

        $blocks = [];

        foreach ($paragraphs as $paragraph) {
            $trimmed = trim($paragraph);

            if ($trimmed === '') {
                continue;
            }

            $blocks[] = [
                'heading' => (bool) preg_match('/^\d+\.\s/', $trimmed) && mb_strlen($trimmed) < 80,
                'text' => $trimmed,
            ];
        }

        return $blocks;
    }
}
