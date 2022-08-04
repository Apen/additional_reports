<?php

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Reports\Xclass;

class XclassTest extends \Sng\AdditionalReports\Tests\Functional\FunctionalTestCase
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
