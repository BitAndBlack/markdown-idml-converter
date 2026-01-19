<?php

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
    public const PARAGRAPH_STYLE_BODY = 'PARAGRAPHSTYLEBODY';
    public const CHARACTER_STYLE_REGULAR = 'CHARACTERSTYLEREGULAR';
    public const CHARACTER_STYLE_BOLD = 'CHARACTERSTYLEBOLD';
    public const CHARACTER_STYLE_CURSIVE = 'CHARACTERSTYLECURSIVE';

    /**
     * @param array<string, StyleInterface> $formats
     */
    public function __construct(
        private readonly array $formats,
    ) {
    }

    /**
     * @return array<int, ParagraphStyleRange>
     */
    public function convert(string $string): array
    {
        $textParts = $this->parseText($string);

        if (self::PARAGRAPH_STYLE_BODY !== $textParts[0]['format']) {
            array_unshift($textParts, [
                'format' => self::PARAGRAPH_STYLE_BODY,
                'value' => '',
            ]);
        }

        $paragraphStyleRanges = [];
        $characterStyleRanges = [];
        $paragraphCounter = -1;

        foreach ($textParts as $textPart) {
            if (self::PARAGRAPH_STYLE_BODY === $textPart['format']) {
                if ([] !== $characterStyleRanges) {
                    $key = count($characterStyleRanges[$paragraphCounter]) - 1;
                    $characterStyleRanges[$paragraphCounter][$key]->addContent(new LineBreak());
                }

                ++$paragraphCounter;
                continue;
            }

            $style = $this->formats[$textPart['format']] ?? null;

            $characterStyleRanges[$paragraphCounter][] = new CharacterStyleRange(
                $style instanceof CharacterStyle ? $style : null,
                new Text($textPart['value'])
            );
        }

        foreach ($characterStyleRanges as $characterStyleRange) {
            $style = $this->formats[self::PARAGRAPH_STYLE_BODY] ?? null;

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
        $string = (string) preg_replace_callback(
            "/(__.+__)|(\*\*.+\*\*)/U",
            static function (array $match): string {
                $text = substr($match[0], 2, -2);
                return '][{{' . self::CHARACTER_STYLE_BOLD . '}}' . $text . '][';
            },
            $string
        );

        $string = (string) preg_replace_callback(
            "/(_.+_)|(\*.+\*)/U",
            static function (array $match): string {
                $text = substr($match[0], 1, -1);
                return '][{{' . self::CHARACTER_STYLE_CURSIVE . '}}' . $text . '][';
            },
            $string
        );

        $string = (string) preg_replace_callback(
            "/\n\n/U",
            static function (array $match): string {
                $text = substr($match[0], 1, -1);
                return '][{{' . self::PARAGRAPH_STYLE_BODY . '}}' . $text . '][';
            },
            $string
        );

        $string = str_replace('[]', '', $string);
        $parts = explode('][', $string);

        /** @var array<int, string> $partsNamed */
        $partsNamed = [];

        foreach ($parts as $key => $part) {
            if (!str_starts_with($part, '{{')) {
                $part = '{{' . self::CHARACTER_STYLE_REGULAR . '}}' . $part;
            }

            $partsNamed[$key] = $part;
        }

        /**
         * @var array<int, array{
         *     format: string,
         *     value: string,
         * }> $partsNamed2
         * */
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

        return $partsNamed2;
    }
}
