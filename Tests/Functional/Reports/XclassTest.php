<?php

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Reports\Xclass;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;

class XclassTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testDisplay()
    {
        $report = new Xclass(parent::getReportObject());
        self::assertNotEmpty($report->display());
    }
}
