<?php

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Reports\Extensions;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;

class HooksTest extends FunctionalTestCase
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
