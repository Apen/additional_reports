<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Reports\CommandControllers;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;

class CommandControllersTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testDisplay(): void
    {
        $report = new CommandControllers(parent::getReportObject());
        self::assertNotEmpty($report->display());
    }
}
