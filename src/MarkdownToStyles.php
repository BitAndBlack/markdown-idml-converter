<?php

/**
 * Bit&Black Markdown IDML Converter.
 *
 * @author Tobias Köngeter
 * @copyright Copyright © Bit&Black
 * @link https://www.bitandblack.com
 * @license MIT
 */

namespace BitAndBlack\MarkdownIdmlConverter;

use IDML\Content\Story\CharacterStyleRange;
use IDML\Content\Story\LineBreak;
use IDML\Content\Story\ParagraphStyleRange;
use IDML\Content\Story\Text;
use IDML\Content\Style\CharacterStyle;
use IDML\Content\Style\ParagraphStyle;
use IDML\Content\Style\StyleInterface;

class MarkdownToStyles
{
    final public const PARAGRAPH_STYLE_H1 = 'PARAGRAPHSTYLEH1';
    final public const PARAGRAPH_STYLE_H2 = 'PARAGRAPHSTYLEH2';
    final public const PARAGRAPH_STYLE_H3 = 'PARAGRAPHSTYLEH3';
    final public const PARAGRAPH_STYLE_H4 = 'PARAGRAPHSTYLEH4';
    final public const PARAGRAPH_STYLE_H5 = 'PARAGRAPHSTYLEH5';
    final public const PARAGRAPH_STYLE_H6 = 'PARAGRAPHSTYLEH6';
    final public const PARAGRAPH_STYLE_BODY = 'PARAGRAPHSTYLEBODY';
    final public const CHARACTER_STYLE_REGULAR = 'CHARACTERSTYLEREGULAR';
    final public const CHARACTER_STYLE_ITALIC = 'CHARACTERSTYLEITALIC';
    final public const CHARACTER_STYLE_BOLD = 'CHARACTERSTYLEBOLD';

    private bool $convertNewLines = false;

    /**
     * @var callable|null
     */
    private $nodeHandler;

    /**
     * @param array<self::PARAGRAPH_* | self::CHARACTER_* , StyleInterface> $formats
     */
    public function __construct(
        private readonly array $formats,
    ) {
    }

    /**
     * This option allows to convert single new line characters (`\n`) into double new line characters (`\n\n`).
     * This can be useful when the input text is not formatted in the style of Markdown, which requires
     * the double new line characters as a sign for a new paragraph.
     *
     * @return $this
     */
    public function enableNewLineConversion(): self
    {
        $this->convertNewLines = true;
        return $this;
    }

    /**
     * @return array<int, ParagraphStyleRange>
     * @throws Exception
     */
    public function convert(string $string): array
    {
        $textParts = $this->parseText($string);

        if (false === str_starts_with($textParts[0]['format'], 'PARAGRAPH')) {
            array_unshift($textParts, [
                'format' => self::PARAGRAPH_STYLE_BODY,
                'value' => '',
            ]);
        }

        $paragraphStyleRanges = [];
        $characterStyleRanges = [];
        $paragraphCounter = -1;
        $paragraphStyleNames = [];

        foreach ($textParts as $textPart) {
            if (null !== $this->nodeHandler) {
                $nodeHandler = $this->nodeHandler;
                $textPart = $nodeHandler($textPart);
            }

            if (null === $textPart) {
                continue;
            }

            /**
             * Validate the structure of the node, that could possibly be broken.
             */
            if (false === is_array($textPart)
                || false === array_key_exists('format', $textPart)
                || false === array_key_exists('value', $textPart)
                || false === is_string($textPart['format'])
                || false === is_string($textPart['value'])
            ) {
                throw new Exception('Malformed node. The node handler probably returned a wrong structure.');
            }

            if (true === str_starts_with($textPart['format'], 'PARAGRAPH')) {
                if ([] !== $characterStyleRanges) {
                    $key = count($characterStyleRanges[$paragraphCounter]) - 1;
                    $characterStyleRanges[$paragraphCounter][$key]->addContent(new LineBreak());
                }

                ++$paragraphCounter;

                $paragraphStyleNames[$paragraphCounter] = $textPart['format'];
                continue;
            }

            $style = $this->formats[$textPart['format']] ?? null;

            $characterStyleRanges[$paragraphCounter][] = new CharacterStyleRange(
                $style instanceof CharacterStyle ? $style : null,
                new Text($textPart['value'])
            );
        }

        foreach ($characterStyleRanges as $paragraphCounter => $characterStyleRange) {
            $paragraphStyleName = $paragraphStyleNames[$paragraphCounter];
            $style = $this->formats[$paragraphStyleName] ?? null;

            $paragraphStyleRanges[] = new ParagraphStyleRange(
                $style instanceof ParagraphStyle ? $style : null,
                ...$characterStyleRange
            );
        }

        return $paragraphStyleRanges;
    }

    /**
     * @return array<int, array{
     *     format: string,
     *     value: string,
     * }>
     */
    private function parseText(string $string): array
    {
        if (true === $this->convertNewLines) {
            $string = str_replace("\n", "\n\n", $string);
        }

        /**
         * Detects bold text.
         */
        $string = preg_replace_callback(
            "/(__.+__)|(\*\*.+\*\*)/U",
            static function (array $match): string {
                $text = substr($match[0], 2, -2);
                return '][{{' . self::CHARACTER_STYLE_BOLD . '}}' . $text . '][';
            },
            $string
        );

        /**
         * Detects italic text.
         */
        $string = preg_replace_callback(
            "/(_.+_)|(\*.+\*)/U",
            static function (array $match): string {
                $text = substr($match[0], 1, -1);
                return '][{{' . self::CHARACTER_STYLE_ITALIC . '}}' . $text . '][';
            },
            (string) $string
        );

        /**
         * Detects headlines.
         */
        $string = preg_replace_callback(
            "/(?<=\n\n|^)(#+\s)(.+)\n\n/U",
            static function (array $match): string {
                $length = strlen($match[1]);
                $headlineCount = $length - 1;
                /** @var string $constant */
                $constant = constant('self::PARAGRAPH_STYLE_H' . $headlineCount);
                return ']' . '[{{' . $constant . '}}]' . '[' . $match[2] . ']' . '[{{' . self::PARAGRAPH_STYLE_BODY . '}}]' . '[';
            },
            (string) $string
        );

        /**
         * Detects paragraph endings.
         */
        $string = preg_replace_callback(
            "/\n\n/U",
            static function (array $match): string {
                $text = substr($match[0], 1, -1);
                return '][{{' . self::PARAGRAPH_STYLE_BODY . '}}' . $text . '][';
            },
            (string) $string
        );

        /**
         * Removes empty parts to clear the list.
         */
        $string = preg_replace("/\[\{\{[A-Z0-9]+}}]\[]/", '', (string) $string);

        $parts = explode('][', (string) $string);

        /**
         * Removes empty parts to clear the list.
         */
        $parts = array_filter($parts);

        /** @var array<int, string> $partsNamed */
        $partsNamed = [];

        foreach ($parts as $key => $part) {
            if (false === str_starts_with($part, '{{')) {
                $part = '{{' . self::CHARACTER_STYLE_REGULAR . '}}' . $part;
            }

            $partsNamed[(int)$key] = $part;
        }

        /**
         * @var array<int, array{
         *     format: string,
         *     value: string,
         * }> $partsNamed2
         */
        $partsNamed2 = [];

        foreach ($partsNamed as $key => $part) {
            $position = strpos($part, '}}') + 2;
            $format = substr($part, 0, $position);
            $format = trim($format, '{}');
            $value = substr($part, $position);

            $partsNamed2[$key] = [
                'format' => $format,
                'value' => $value,
            ];
        }

        return array_values($partsNamed2);
    }

    /**
     * Adds a custom node handler. This needs to be a callable, that takes an array as first argument.
     * The array contains the current style and the value of the node. It is possible to modify that values
     * and append those changes by returning the array. If the callback returns null, the node will get deleted.
     *
     * @return $this
     */
    public function setNodeHandler(callable $nodeHandler): self
    {
        $this->nodeHandler = $nodeHandler;
        return $this;
    }
}
