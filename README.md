[![PHP from Packagist](https://img.shields.io/packagist/php-v/bitandblack/markdown-idml-converter)](http://www.php.net)
[![Total Downloads](https://poser.pugx.org/bitandblack/markdown-idml-converter/downloads)](https://packagist.org/packages/bitandblack/markdown-idml-converter)
[![License](https://poser.pugx.org/bitandblack/markdown-idml-converter/license)](https://packagist.org/packages/bitandblack/markdown-idml-converter)

<p align="center">
    <a href="https://www.bitandblack.com" target="_blank">
        <img src="https://www.bitandblack.com/build/images/BitAndBlack-Logo-Full.png" alt="Bit&Black Logo" width="400">
    </a>
</p>

# Bit&Black Markdown-IDML-Converter

Convert Markdown into (parts of) Adobe InDesign Markup Language Files (IDML).

## Installation

This library is written in [PHP](https://www.php.net) and made for the use with [Composer](https://packagist.org/packages/bitandblack/markdown-idml-converter). Be sure to have both of them installed on your system.

As this library requires the [IDML Creator](https://www.idml.dev/en/idml-creator-php.html) — which requires a licence — be sure to get one at first. If not already part of your project, add the Composer credentials. (This is explained for example [here](https://www.idml.dev/en/idml-creator-php/installation.html).)

Add the library then to your project by running `$ composer require bitandblack/markdown-idml-converter`.

## Usage

### Converting Markdown into IDML Formatted Text 

This library can convert a few Markdown formats into Paragraph and Character Styles. It can handle: 

-   The base "copy" paragraph
-   An *italic* formatting
-   A **bold** formatting

Therefore, the first step is to create an array with the styles, that should be used:

```php
<?php

use BitAndBlack\MarkdownIdmlConverter\MarkdownToStyles;
use IDML\Content\Style\CharacterStyle;
use IDML\Content\Style\ParagraphStyle;

$formats = [
    MarkdownToStyles::PARAGRAPH_STYLE_BODY => new ParagraphStyle('Body'),
    MarkdownToStyles::CHARACTER_STYLE_REGULAR => new CharacterStyle('Regular'),
    MarkdownToStyles::CHARACTER_STYLE_ITALIC => new CharacterStyle('Italic'),
    MarkdownToStyles::CHARACTER_STYLE_BOLD => new CharacterStyle('Bold'),
];
```

Second, the [`MarkdownToStyles`](./src/MarkdownToStyles.php) class can be initialised with those styles and text can be converted:

```php
<?php

use BitAndBlack\MarkdownIdmlConverter\MarkdownToStyles;

$markdownToStyles = new MarkdownToStyles($formats);

$paragraphStyleRange = $markdownToStyles->convert($markdownFormattedText);
```

The result is a IDML ParagraphStyleRange, that can be handled using the [IDML Creator](https://www.idml.dev/en/idml-creator-php.html).

## Other Tools

Bit&Black offers some more tools to handle IDML files:

-   The [IDML-Creator](https://www.idml.dev/en/idml-creator-php.html) library that allows creating IDML content natively in PHP in an object-oriented way. (A demo is available [here](https://bitbucket.org/wirbelwild/idml-creator-demo).)
-   The [IDML-Writer](https://www.idml.dev/en/idml-writer-php.html) library that can write IDML content into a valid IDML file.
-   The [IDML-Validator](https://www.idml.dev/en/idml-validator-php.html) library that allows validating IDML files against the official schema from Adobe.
-   The [IDML-JSON-Validator](https://www.idml.dev/en/json-idml-converter-php.html) library that allows converting Adobe InDesign Markup Language Files (IDML) into JSON and JSON into IDML.

Feel free to visit [www.idml.dev](https://www.idml.dev) for more information!

## Help

If you have any questions feel free to contact us under `hello@bitandblack.com`.

Further information about Bit&Black can be found under [www.bitandblack.com](https://www.bitandblack.com).