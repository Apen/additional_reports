<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Reports\Extensions;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;

class ExtensionsTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testDisplay()
    {
        $report = new Extensions(parent::getReportObject());
        self::assertNotEmpty($report->display());
    }

    public function testExtensionTablesAreParsedLikeTheDatabaseAnalyzer(): void
    {
        $report = new Extensions(parent::getReportObject());

        self::assertSame([
            [
                'name' => 'tx_example_domain_model_item',
                'columns' => ['uid', 'pid', 'title'],
            ],
        ], $report->getExtensionTables(<<<'SQL'
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
        $report = new Extensions(parent::getReportObject());

        self::assertSame([], $report->getExtensionTables('CREATE TABLE invalid syntax;'));
    }
}
