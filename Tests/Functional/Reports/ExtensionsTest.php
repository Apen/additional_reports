<?php

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use TYPO3\CMS\Reports\Controller\ReportController;
use Sng\AdditionalReports\Reports\Extensions;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class ExtensionsTest extends FunctionalTestCase
{
    protected $coreExtensionsToLoad = [
        'reports',
    ];

    protected $testExtensionsToLoad = [
        'typo3conf/ext/additional_reports',
    ];

    public function testDisplay()
    {
        $report = new Extensions(new ReportController());
        self::assertNotEmpty($report->display());
    }
}
