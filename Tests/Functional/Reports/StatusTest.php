<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Reports\Status;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;

class StatusTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testDisplay()
    {
        if (self::isNotSqlite()) {
            $report = new Status(parent::getReportObject());
            self::assertNotEmpty($report->display());
        } else {
            self::markTestSkipped('The status report contains MySQL-specific queries.');
        }
    }

    public static function isNotSqlite()
    {
        return $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['driver'] !== 'pdo_sqlite';
    }
}
