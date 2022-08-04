<?php

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Reports\LogErrors;
use TYPO3\CMS\Core\Core\Bootstrap;

class LogErrorsTest extends \Sng\AdditionalReports\Tests\Functional\FunctionalTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testDisplay()
    {
        $report = new LogErrors(parent::getReportObject());
        self::assertNotEmpty($report->display());
    }
}
