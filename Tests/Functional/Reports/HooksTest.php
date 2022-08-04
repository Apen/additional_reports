<?php

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Reports\Extensions;

class HooksTest extends \Sng\AdditionalReports\Tests\Functional\FunctionalTestCase
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
}
