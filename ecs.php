<?php

use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withParallel()
    ->withPaths([
        __DIR__,
    ])
    ->withSkip([
        __DIR__ . DIRECTORY_SEPARATOR . 'vendor',
    ])
    ->withPreparedSets(
        psr12: true,
        arrays: true,
        comments: true,
        docblocks: true,
        namespaces: true,
        phpunit: true,
        cleanCode: true,
    )
    ->withConfiguredRule(YodaStyleFixer::class, [
        'always_move_variable' => true,
    ])
;
