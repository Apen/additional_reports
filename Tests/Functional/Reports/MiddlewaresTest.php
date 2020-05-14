<?php

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use TYPO3\CMS\Reports\Controller\ReportController;
use Sng\AdditionalReports\Reports\Middlewares;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class MiddlewaresTest extends FunctionalTestCase
{
    protected $coreExtensionsToLoad = [
        'reports',
    ];

    protected $testExtensionsToLoad = [
        'typo3conf/ext/additional_reports',
    ];

    public function testDisplay()
    {
        $report = new Middlewares(new ReportController());
        self::assertNotEmpty($report->display());
    }
}
