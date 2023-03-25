<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector;
use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\DeadCode\Rector\Property\RemoveUselessVarTagRector;
use Rector\Php73\Rector\ConstFetch\SensitiveConstantNameRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\PostRector\Rector\NameImportingPostRector;
use Rector\Set\ValueObject\SetList;
use Ssch\TYPO3Rector\Configuration\Typo3Option;
use Ssch\TYPO3Rector\Set\Typo3LevelSetList;
use Ssch\TYPO3Rector\Set\Typo3SetList;

return static function (RectorConfig $rectorConfig): void {
    $parameters = $rectorConfig->parameters();

    // php
    $rectorConfig->import(SetList::CODE_QUALITY);
    $rectorConfig->import(SetList::CODING_STYLE);
    $rectorConfig->import(SetList::DEAD_CODE);
    $rectorConfig->import(SetList::PHP_53);
    $rectorConfig->import(SetList::PHP_54);
    $rectorConfig->import(SetList::PHP_55);
    $rectorConfig->import(SetList::PHP_56);
    $rectorConfig->import(SetList::PHP_70);
    $rectorConfig->import(SetList::PHP_71);
    $rectorConfig->import(SetList::PHP_72);
    $rectorConfig->import(SetList::PHP_73);
    $rectorConfig->import(SetList::PHP_74);
    $rectorConfig->import(SetList::PHP_80);
    $rectorConfig->import(SetList::PHP_81);

    // typo3
    $rectorConfig->sets([
        Typo3LevelSetList::UP_TO_TYPO3_11,
        //        SetList::TYPE_DECLARATION
    ]);
    $rectorConfig->import(Typo3SetList::UNDERSCORE_TO_NAMESPACE);
    $rectorConfig->import(Typo3SetList::EXTBASE_COMMAND_CONTROLLERS_TO_SYMFONY_COMMANDS);
    $rectorConfig->import(Typo3SetList::DATABASE_TO_DBAL);

    // In order to have a better analysis from phpstan we teach it here some more things
    $rectorConfig->phpstanConfig(Typo3Option::PHPSTAN_FOR_RECTOR_PATH);

    // FQN classes are not imported by default. If you don't do it manually after every Rector run, enable it by:
    $rectorConfig->importNames(true, false);

    // Disable parallel otherwise non php file processing is not working i.e. typoscript
    $rectorConfig->disableParallel();

    // Define your target version which you want to support
    $rectorConfig->phpVersion(PhpVersion::PHP_81);

    // If you only want to process one/some TYPO3 extension(s), you can specify its path(s) here.
    // If you use the option --config change __DIR__ to getcwd()
    // $rectorConfig->paths([
    //    __DIR__ . '/packages/acme_demo/',
    // ]);

    // is there a file you need to skip?
    $rectorConfig->skip([
        '*/news/*',
        '*/flux/*',
        '*Build/*',
        '*/Resources/Private/Php/*',
        '*/Resources/Public/*',
        '*/Configuration/TypoScript/*',
        '*/Configuration/RequestMiddlewares.php',
        AddLiteralSeparatorToNumberRector::class,
        SensitiveConstantNameRector::class,
        PostIncDecToPreIncDecRector::class,
        UnSpreadOperatorRector::class,
        RemoveUselessReturnTagRector::class,
        RemoveUselessParamTagRector::class,
        RemoveUselessVarTagRector::class,
        NameImportingPostRector::class => [
            '*/ClassAliasMap.php',
            '*/ext_localconf.php',
            '*/ext_emconf.php',
            '*/ext_tables.php',
            '*/Configuration/TCA/*',
            '*/Configuration/RequestMiddlewares.php',
            '*/Configuration/Commands.php',
            '*/Configuration/AjaxRoutes.php',
            '*/Configuration/Extbase/Persistence/Classes.php',
        ],
    ]);

    // is there single rule you don't like from a set you use?
    //    $parameters->set(Option::EXCLUDE_RECTORS, [
    //        \Rector\Php71\Rector\FuncCall\CountOnNullRector::class,
    //        \Rector\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector::class,
    //        \Rector\DeadCode\Rector\ClassMethod\RemoveUnusedParameterRector::class,
    //        \Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector::class
    //    ]);

    /*// get services (needed for register a single rule)
    $services = $rectorConfig->services();

    // register a single rule
    $services->set(ContentObjectRendererFileResourceRector::class);
    $services->set(TemplateGetFileNameToFilePathSanitizerRector::class);*/
};
