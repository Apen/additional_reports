<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Reports\LogErrors;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;

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
}
