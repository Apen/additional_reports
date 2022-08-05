<?php

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Reports\WebsiteConf;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;

class WebsiteConfTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testDisplay()
    {
        $report = new WebsiteConf(parent::getReportObject());
        self::assertNotEmpty($report->display());
    }
}
