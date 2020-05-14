<?php

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use TYPO3\CMS\Reports\Controller\ReportController;
use Sng\AdditionalReports\Reports\WebsiteConf;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class WebsiteConfTest extends FunctionalTestCase
{
    protected $coreExtensionsToLoad = [
        'reports',
    ];

    protected $testExtensionsToLoad = [
        'typo3conf/ext/additional_reports',
    ];

    public function testDisplay()
    {
        $report = new WebsiteConf(new ReportController());
        self::assertNotEmpty($report->display());
    }
}
