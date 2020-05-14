<?php

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use TYPO3\CMS\Reports\Controller\ReportController;
use Sng\AdditionalReports\Reports\Status;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class StatusTest extends FunctionalTestCase
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
        if (self::isNotSqlite()) {
            $report = new Status(new ReportController());
            self::assertNotEmpty($report->display());
        }
    }

    public static function isNotSqlite()
    {
        return $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['driver'] !== 'pdo_sqlite';
    }
}
