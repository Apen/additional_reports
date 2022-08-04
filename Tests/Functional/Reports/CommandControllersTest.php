<?php

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Reports\CommandControllers;

class CommandControllersTest extends \Sng\AdditionalReports\Tests\Functional\FunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testDisplay()
    {
        $report = new CommandControllers(parent::getReportObject());
        self::assertNotEmpty($report->display());
    }
}
