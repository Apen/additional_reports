<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    die('CLI only.');
}

$root = dirname(__DIR__);
$finder = (new PhpCsFixer\Finder())
    ->ignoreVCSIgnored(true)
    ->in([$root . '/Classes', $root . '/Configuration', $root . '/Tests'])
    ->append([$root . '/ext_emconf.php']);

return (new PhpCsFixer\Config())
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setUsingCache(false)
    ->setRules([
        '@PER-CS2x0' => true,
        '@PHP8x2Migration' => true,
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'declare_strict_types' => true,
        'no_unused_imports' => true,
        'ordered_imports' => true,
        'single_quote' => true,
        'yoda_style' => false,
    ]);
