<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Functional;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

        $this->importCSVDataSet(__DIR__ . '/Fixtures/be_users.csv');
        $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)->createFromUserPreferences($GLOBALS['BE_USER']);

        $uri = new Uri('https://localhost/typo3/');
        $request = new ServerRequest($uri, 'GET', null, [], [
            'DOCUMENT_ROOT' => Environment::getPublicPath(),
            'HTTP_HOST' => 'localhost',
            'REQUEST_URI' => '/typo3/',
            'SCRIPT_NAME' => '/typo3/index.php',
            'SERVER_PORT' => '443',
            'HTTPS' => 'on',
        ]);
        $request = $request
            ->withQueryParams([
                'report' => 'logerrors',
            ])
            ->withAttribute('normalizedParams', NormalizedParams::createFromRequest($request));
        $request = $request->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE);
        $GLOBALS['TYPO3_REQUEST'] = $request;
    }

    public static function getReportObject(): object
    {
        return new \stdClass();
    }
}
