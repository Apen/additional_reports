<?php

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use TYPO3\CMS\Reports\Controller\ReportController;
use Sng\AdditionalReports\Reports\LogErrors;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class LogErrorsTest extends FunctionalTestCase
{
    protected $coreExtensionsToLoad = [
        'reports',
    ];

    protected $testExtensionsToLoad = [
        'typo3conf/ext/additional_reports',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpBackendUserFromFixture(1);
        Bootstrap::initializeLanguageObject();
    }

    public function testDisplay()
    {
        $report = new LogErrors(new ReportController());
        self::assertNotEmpty($report->display());
    }
}
