<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Reports\Extensions;
use Sng\AdditionalReports\Service\ExtensionSchemaParser;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExtensionsTest extends FunctionalTestCase
{
    public function testDisplay(): void
    {
        $extensions = new Extensions(parent::getReportObject());
        $output = $extensions->display();

        self::assertNotEmpty($output);
        self::assertStringContainsString('class="notice col-xs-6"', $output);
        self::assertStringContainsString('class="table-fit"', $output);
        self::assertStringContainsString('additional-reports-extensions-table', $output);
        self::assertStringContainsString('<colgroup>', $output);
    }

    public function testExtensionTablesAreParsedLikeTheDatabaseAnalyzer(): void
    {
        $extensionSchemaParser = GeneralUtility::makeInstance(ExtensionSchemaParser::class);

        self::assertSame([
            [
                'name' => 'tx_example_domain_model_item',
                'columns' => ['uid', 'pid', 'title'],
            ],
        ], $extensionSchemaParser->parse(<<<'SQL'
            CREATE TABLE tx_example_domain_model_item (
                uid int(11) NOT NULL auto_increment,
                pid int(11) DEFAULT '0' NOT NULL,
                title varchar(255) DEFAULT '' NOT NULL,
                PRIMARY KEY (uid)
            );
            SQL));
    }

    public function testInvalidExtensionTableDefinitionDoesNotBreakTheReport(): void
    {
        $extensionSchemaParser = GeneralUtility::makeInstance(ExtensionSchemaParser::class);

        self::assertSame([], $extensionSchemaParser->parse('CREATE TABLE invalid syntax;'));
    }

    public function testExtensionInformationExposesStructuredUpdateData(): void
    {
        $information = (new Extensions(parent::getReportObject()))->getExtensionInformations([
            'extkey' => 'example',
            'version' => '1.0.0',
            'lastversion' => [
                'version' => '2.0.0',
                'updatedate' => '01/08/2026',
            ],
            'fdfile' => 'CREATE TABLE tx_example (uid int(11) NOT NULL);',
        ]);

        self::assertTrue($information['updateAvailable']);
        self::assertSame('2.0.0', $information['latestVersion']);
        self::assertSame('01/08/2026', $information['latestVersionDate']);
        self::assertNotSame('', $information['compareUrlLast']);
        self::assertSame('tx_example', $information['tables'][0]['name']);
    }

    public function testDevelopmentExtensionHasNoUpdateLink(): void
    {
        $information = (new Extensions(parent::getReportObject()))->getExtensionInformations([
            'extkey' => 'example',
            'version' => 'dev-main',
            'lastversion' => null,
        ]);

        self::assertFalse($information['updateAvailable']);
        self::assertSame('', $information['latestVersion']);
        self::assertSame('', $information['compareUrlLast']);
    }

}
