<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\AssignmentInConditionSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Metrics\CyclomaticComplexitySniff;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\CastNotation\CastSpacesFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\Import\NoLeadingImportSlashFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\ConcatSpaceFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Operator\TernaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Phpdoc\AlignMultilineCommentFixer;
use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use PhpCsFixer\Fixer\Phpdoc\NoBlankLinesAfterPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitStrictFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitTestAnnotationFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Whitespace\NoExtraBlankLinesFixer;
use PhpCsFixer\Fixer\Whitespace\NoSpacesAroundOffsetFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayListItemNewlineFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->import(SetList::PSR_12);
    $ecsConfig->import(SetList::CLEAN_CODE);
    $ecsConfig->import(SetList::COMMON);

    $ecsConfig->skip([
        dirname(__DIR__) . '/Build/*',
        dirname(__DIR__) . '/.Build/*',
        TrailingCommaInMultilineFixer::class,
        \PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer::class,
        PhpUnitStrictFixer::class,
        PhpUnitTestAnnotationFixer::class,
        ArrayOpenerAndCloserNewlineFixer::class,
        ArrayListItemNewlineFixer::class,
        CastSpacesFixer::class,
        NotOperatorWithSuccessorSpaceFixer::class,
        NoSuperfluousPhpdocTagsFixer::class,
        ClassAttributesSeparationFixer::class,
        OrderedClassElementsFixer::class,
        NoSpacesAroundOffsetFixer::class,
        AssignmentInConditionSniff::class . '.Found',
        DeclareStrictTypesFixer::class => [
            '**/ext_emconf.php',
            '**/ext_localconf.php',
            '**/ext_tables.php',
            '**/TCA/*',
        ],
    ]);

    $ecsConfig->ruleWithConfiguration(ArraySyntaxFixer::class, ['syntax' => 'short']);
    $ecsConfig->ruleWithConfiguration(ConcatSpaceFixer::class, ['spacing' => 'one']);
    $ecsConfig->ruleWithConfiguration(BinaryOperatorSpacesFixer::class, ['default' => 'single_space']);
    $ecsConfig->rule(NoExtraBlankLinesFixer::class);
    $ecsConfig->rule(TernaryOperatorSpacesFixer::class);
    $ecsConfig->rule(NoBlankLinesAfterPhpdocFixer::class);
    $ecsConfig->ruleWithConfiguration(AlignMultilineCommentFixer::class, ['comment_type' => 'phpdocs_only']);
    $ecsConfig->ruleWithConfiguration(GeneralPhpdocAnnotationRemoveFixer::class, ['annotations' => ['author', 'since']]);
    $ecsConfig->rule(NoLeadingImportSlashFixer::class);
    $ecsConfig->rule(NoUnusedImportsFixer::class);
    $ecsConfig->ruleWithConfiguration(OrderedImportsFixer::class, ['imports_order' => ['class', 'const', 'function']]);
    //    $ecsConfig->ruleWithConfiguration(CyclomaticComplexitySniff::class, ['complexity' => 20, 'absoluteComplexity' => 20]);
};
