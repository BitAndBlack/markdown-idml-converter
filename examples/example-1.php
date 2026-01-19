<?php

use BitAndBlack\MarkdownIdmlConverter\MarkdownToStyles;
use IDML\Content\Style\CharacterStyle;
use IDML\Content\Style\ParagraphStyle;

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

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

dump($result);