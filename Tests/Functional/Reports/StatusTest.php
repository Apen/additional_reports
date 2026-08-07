<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Reports\Status;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;

class StatusTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testDisplay()
    {
        $output = (new Status(parent::getReportObject()))->display();

        self::assertNotEmpty($output);
        self::assertStringContainsString('reportsMySQL', $output);
    }
}
