<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Reports\Eid;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;

class EidTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testDisplay()
    {
        $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include'] = [
            'legacy' => 'EXT:additional_reports/Classes/Eid/CallAjax.php',
            'modern' => self::class . '::handleRequest',
            'callable' => [self::class, 'handleRequest'],
        ];

        $report = new Eid(parent::getReportObject());
        $output = $report->display();

        self::assertStringContainsString('additional_reports', $output);
        self::assertStringContainsString('EXT:additional_reports/Classes/Eid/CallAjax.php', $output);
        self::assertStringContainsString(self::class . '::handleRequest', $output);
        self::assertStringContainsString('array', $output);
    }
}
