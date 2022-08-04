<?php

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Reports\WebsiteConf;

class WebsiteConfTest extends \Sng\AdditionalReports\Tests\Functional\FunctionalTestCase
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
