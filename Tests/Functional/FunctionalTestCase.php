<?php

namespace Sng\AdditionalReports\Tests\Functional;

use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Reports\Controller\ReportController;

class FunctionalTestCase extends \TYPO3\TestingFramework\Core\Functional\FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'core',
        'extbase',
        'frontend',
        'fluid',
        'reports',
    ];

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/additional_reports',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpBackendUserFromFixture(1);
        Bootstrap::initializeLanguageObject();
    }

    public static function getReportObject()
    {
        return GeneralUtility::makeInstance(ReportController::class);
    }
}