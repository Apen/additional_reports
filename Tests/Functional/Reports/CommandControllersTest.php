<?php

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use TYPO3\CMS\Reports\Controller\ReportController;
use Sng\AdditionalReports\Reports\CommandControllers;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class CommandControllersTest extends FunctionalTestCase
{
    protected $coreExtensionsToLoad = [
        'reports',
    ];

    protected $testExtensionsToLoad = [
        'typo3conf/ext/additional_reports',
    ];

    public function testDisplay()
    {
        $report = new CommandControllers(new ReportController());
        self::assertNotEmpty($report->display());
    }
}
