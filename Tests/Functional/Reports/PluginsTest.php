<?php

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Reports\Plugins;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;

class PluginsTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testDisplay()
    {
        $report = new Plugins(parent::getReportObject());
        self::assertNotEmpty($report->display());
    }
}
