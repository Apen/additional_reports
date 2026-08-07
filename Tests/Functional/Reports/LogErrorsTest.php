<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Reports\LogErrors;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LogErrorsTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testDisplay()
    {
        $report = new LogErrors(parent::getReportObject());
        $output = $report->display();

        self::assertStringContainsString('<code>DELETE FROM sys_log WHERE error &gt; 0;</code>', $output);
    }

    public function testErrorsAreEscapedAndOrderingIsWhitelisted(): void
    {
        GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_log')->insert('sys_log', [
            'error' => 2,
            'details' => '<script>alert(1)</script>',
            'tstamp' => 1_700_000_000,
        ]);
        $request = $GLOBALS['TYPO3_REQUEST'];
        $GLOBALS['TYPO3_REQUEST'] = $request->withQueryParams([
            ...$request->getQueryParams(),
            'orderby' => 'nb DESC; DROP TABLE sys_log',
        ]);

        $output = (new LogErrors(parent::getReportObject()))->display();

        self::assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $output);
        self::assertStringNotContainsString('<script>alert(1)</script>', $output);
        self::assertStringContainsString('14/11/2023', $output);
    }
}
