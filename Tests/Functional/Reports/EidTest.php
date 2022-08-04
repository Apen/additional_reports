<?php

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Reports\Eid;

class EidTest extends \Sng\AdditionalReports\Tests\Functional\FunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testDisplay()
    {
        $report = new Eid(parent::getReportObject());
        self::assertNotEmpty($report->display());
    }
}
