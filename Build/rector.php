<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector;
use Rector\DeadCode\Rector\Property\RemoveDefaultValueFromAssignedPropertyRector;
use Rector\Php84\Rector\FuncCall\AddEscapeArgumentRector;
use Rector\Php84\Rector\MethodCall\NewMethodCallWithoutParenthesesRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use Ssch\TYPO3Rector\CodeQuality\General\GeneralUtilityMakeInstanceToConstructorPropertyRector;
use Ssch\TYPO3Rector\CodeQuality\General\InjectMethodToConstructorInjectionRector;
use Ssch\TYPO3Rector\Configuration\Typo3Option;
use Ssch\TYPO3Rector\Set\Typo3LevelSetList;
use Ssch\TYPO3Rector\Set\Typo3SetList;
use Ssch\TYPO3Rector\TYPO310\v4\UseFileGetContentsForGetUrlRector;
use Ssch\TYPO3Rector\TYPO311\v0\ReplaceInjectAnnotationWithMethodRector;

$root = dirname(__DIR__);

return RectorConfig::configure()
    ->withPaths([
        $root . '/Classes',
        $root . '/Configuration',
        $root . '/Tests',
        $root . '/ext_emconf.php',
    ])
    ->withSets([
        LevelSetList::UP_TO_PHP_82,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
        SetList::INSTANCEOF,
        SetList::NAMING,
        SetList::PRIVATIZATION,
        SetList::TYPE_DECLARATION,
        Typo3SetList::CODE_QUALITY,
        Typo3SetList::GENERAL,
        Typo3LevelSetList::UP_TO_TYPO3_13,
    ])
    ->withImportNames(importShortClasses: false, removeUnusedImports: true)
    ->withPHPStanConfigs([
        Typo3Option::PHPSTAN_FOR_RECTOR_PATH,
    ])
    ->withSkip([
        $root . '/Resources/Public/*',
        $root . '/Configuration/TypoScript/*',
        ReplaceInjectAnnotationWithMethodRector::class,
        UseFileGetContentsForGetUrlRector::class,
        TypedPropertyFromStrictConstructorRector::class,
        InjectMethodToConstructorInjectionRector::class,
        GeneralUtilityMakeInstanceToConstructorPropertyRector::class,
        RemoveUnusedVariableAssignRector::class,
        RemoveDefaultValueFromAssignedPropertyRector::class,
        NewlineAfterStatementRector::class,
    ]);
