<?php

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Reports\Middlewares;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;

class MiddlewaresTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testDisplay()
    {
        $report = new Middlewares(parent::getReportObject());
        self::assertNotEmpty($report->display());
    }
}
