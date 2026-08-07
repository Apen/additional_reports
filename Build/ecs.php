<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\Basic\BracesPositionFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\Operator\ConcatSpaceFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitStrictFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitTestAnnotationFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Whitespace\LineEndingFixer;
use Symplify\CodingStandard\Fixer\Spacing\StandaloneLinePromotedPropertyFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

$root = dirname(__DIR__);

$config = ECSConfig::configure()
    ->withPaths([
        $root . '/Classes',
        $root . '/Configuration',
        $root . '/Tests',
        $root . '/ext_emconf.php',
        $root . '/ext_tables.php',
    ])
    ->withConfiguredRule(ConcatSpaceFixer::class, ['spacing' => 'one'])
    ->withConfiguredRule(OrderedImportsFixer::class, ['imports_order' => ['class', 'const', 'function']])
    ->withRules([NoUnusedImportsFixer::class])
    ->withSkip([
        YodaStyleFixer::class,
        PhpUnitStrictFixer::class,
        PhpUnitTestAnnotationFixer::class,
        OrderedClassElementsFixer::class,
        BracesPositionFixer::class,
        LineEndingFixer::class,
        StandaloneLinePromotedPropertyFixer::class,
        DeclareStrictTypesFixer::class => [
            $root . '/ext_emconf.php',
            $root . '/ext_localconf.php',
            $root . '/ext_tables.php',
            $root . '/Configuration/TCA/*',
        ],
    ])
    ->withFileExtensions(['php'])
    ->withCache($root . '/.Build/cache/ecs');

return DIRECTORY_SEPARATOR === '\\' ? $config->withoutParallel() : $config->withParallel();
