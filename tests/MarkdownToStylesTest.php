<?php

/**
 * Bit&Black Markdown IDML Converter.
 *
 * @author Tobias Köngeter
 * @copyright Copyright © Bit&Black
 * @link https://www.bitandblack.com
 * @license MIT
 */

declare(strict_types=1);

namespace BitAndBlack\MarkdownIdmlConverter\Tests;

use BitAndBlack\MarkdownIdmlConverter\MarkdownToStyles;
use IDML\Content\Style\CharacterStyle;
use IDML\Content\Style\ParagraphStyle;
use PHPUnit\Framework\TestCase;

final class MarkdownToStylesTest extends TestCase
{
    public function testConvert(): void
    {
        $text = <<<MARKDOWN
            # Hello World
            
            This is a *test*.
            MARKDOWN
        ;

        $formats = [
            MarkdownToStyles::PARAGRAPH_STYLE_BODY => new ParagraphStyle('Body'),
            MarkdownToStyles::CHARACTER_STYLE_REGULAR => new CharacterStyle('Regular'),
            MarkdownToStyles::CHARACTER_STYLE_ITALIC => new CharacterStyle('Italic'),
            MarkdownToStyles::CHARACTER_STYLE_BOLD => new CharacterStyle('Bold'),
        ];

        $markdownToStyles = new MarkdownToStyles($formats);

        $result = $markdownToStyles->convert($text);

        self::assertCount(
            2,
            $result
        );
    }
}
