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
use DOMException;
use IDML\Content\Exception\InvalidDomStructureException;
use IDML\Content\Exception\InvalidPropertyException;
use IDML\Content\Exception\WrongInDesignVersionException;
use IDML\Content\Story\Story;
use IDML\Content\Style\CharacterStyle;
use IDML\Content\Style\ParagraphStyle;
use PHPUnit\Framework\TestCase;
use ReflectionException;

final class MarkdownToStylesTest extends TestCase
{
    public function testConvert1(): void
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

    /**
     * @throws DOMException
     * @throws InvalidDomStructureException
     * @throws InvalidPropertyException
     * @throws ReflectionException
     * @throws WrongInDesignVersionException
     */
    public function testCanConvert2(): void
    {
        $formats = [
            MarkdownToStyles::PARAGRAPH_STYLE_BODY => new ParagraphStyle('Body'),
            MarkdownToStyles::CHARACTER_STYLE_REGULAR => new CharacterStyle('Regular'),
            MarkdownToStyles::CHARACTER_STYLE_BOLD => new CharacterStyle('Bold'),
            MarkdownToStyles::CHARACTER_STYLE_ITALIC => new CharacterStyle('Italic'),
        ];

        $input = 'Maybe __bold__ maybe *not* ' . PHP_EOL . PHP_EOL . ' maybe **both or _not_**?';

        $markdownToStyles = new MarkdownToStyles($formats);

        $output = $markdownToStyles->convert($input);

        self::assertCount(
            2,
            $output
        );

        if (null === $output[0]->getParagraphStyle()) {
            self::fail();
        }

        self::assertSame(
            'Body',
            $output[0]->getParagraphStyle()->getName()
        );

        $story = new Story('Story');
        $story->addContent(...$output);

        $domDocument = $story->render();

        self::assertSame(
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Story Self="Story" AppliedTOCStyle="n" TrackChanges="false" StoryTitle="$ID/" AppliedNamedGrid="n">
  <StoryPreference FrameType="TextFrameType"/>
  <ParagraphStyleRange AppliedParagraphStyle="ParagraphStyle/Body">
    <CharacterStyleRange AppliedCharacterStyle="CharacterStyle/Regular">
      <Content>Maybe </Content>
    </CharacterStyleRange>
    <CharacterStyleRange AppliedCharacterStyle="CharacterStyle/Bold">
      <Content>bold</Content>
    </CharacterStyleRange>
    <CharacterStyleRange AppliedCharacterStyle="CharacterStyle/Regular">
      <Content> maybe </Content>
    </CharacterStyleRange>
    <CharacterStyleRange AppliedCharacterStyle="CharacterStyle/Italic">
      <Content>not</Content>
    </CharacterStyleRange>
    <CharacterStyleRange AppliedCharacterStyle="CharacterStyle/Regular">
      <Content> </Content>
      <Br/>
    </CharacterStyleRange>
  </ParagraphStyleRange>
  <ParagraphStyleRange AppliedParagraphStyle="ParagraphStyle/Body">
    <CharacterStyleRange AppliedCharacterStyle="CharacterStyle/Regular">
      <Content> maybe </Content>
    </CharacterStyleRange>
    <CharacterStyleRange AppliedCharacterStyle="CharacterStyle/Bold">
      <Content>both or </Content>
    </CharacterStyleRange>
    <CharacterStyleRange AppliedCharacterStyle="CharacterStyle/Italic">
      <Content>not</Content>
    </CharacterStyleRange>
    <CharacterStyleRange AppliedCharacterStyle="CharacterStyle/Regular">
      <Content>?</Content>
    </CharacterStyleRange>
  </ParagraphStyleRange>
</Story>
',
            $domDocument->saveXML()
        );
    }
}
