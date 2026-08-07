<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Functional;

use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class UtilityTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'reports',
    ];

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/additional_reports',
    ];

    protected array $configurationToUseInTestInstance = [
        'SYS' => [
            'caching' => [
                'cacheConfigurations' => [
                    'assets' => [
                        'frontend' => VariableFrontend::class,
                        'backend' => SimpleFileBackend::class,
                        'options' => [
                            'defaultLifetime' => 0,
                        ],
                        'groups' => ['system'],
                    ],
                    'l10n' => [
                        'frontend' => VariableFrontend::class,
                        'backend' => SimpleFileBackend::class,
                        'options' => [
                            'defaultLifetime' => 0,
                        ],
                        'groups' => ['system'],
                    ],
                ],
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/be_users.csv');
        $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)->createFromUserPreferences($GLOBALS['BE_USER']);
        $this->importCSVDataSet(__DIR__ . '/Fixtures/pages.csv');
        $fixture = GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() >= 14
            ? '/Fixtures/tt_content_v14.csv'
            : '/Fixtures/tt_content.csv';
        $this->importCSVDataSet(__DIR__ . $fixture);
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

    public function testGetInstExtList()
    {
        $extLits = Utility::getInstExtList(Environment::getPublicPath() . '/typo3/sysext/');
        self::assertNotEmpty($extLits);
        self::assertEquals('additional_reports', $extLits['dev']['additional_reports']['extkey']);
    }

    public function testGetExtPath()
    {
        self::assertNotEmpty(Utility::getExtPath('core'));
    }

    public function testGetExtensionVersion()
    {
        self::assertEquals(GeneralUtility::makeInstance(Typo3Version::class)->getVersion(), Utility::getExtensionVersion('core'));
    }

    public function testGetExtIcon()
    {
        self::assertNotEmpty(Utility::getExtIcon('core'));
    }

    public function testGetJsonVersionInfos()
    {
        if (getenv('RUN_NETWORK_TESTS') !== '1') {
            self::markTestSkipped('Set RUN_NETWORK_TESTS=1 to run TYPO3 API integration tests.');
        }
        self::assertNotEmpty(Utility::getJsonVersionInfos());
    }

    public function testDownloadT3x()
    {
        if (getenv('RUN_NETWORK_TESTS') !== '1') {
            self::markTestSkipped('Set RUN_NETWORK_TESTS=1 to run TER integration tests.');
        }
        self::assertNotEmpty(Utility::downloadT3x('additional_reports', '3.3.2'));
    }

    public function testGetTreeList()
    {
        self::assertEquals($this->pagesListProvider(), Utility::getTreeList(1, 99));
    }

    public function testGetCountPagesUids()
    {
        if (self::isNotSqlite()) {
            self::assertEquals(0, Utility::getCountPagesUids($this->pagesListProvider(), 'hidden'));
            self::assertEquals(1, Utility::getCountPagesUids($this->pagesListProvider(), 'no_search'));
        } else {
            self::markTestSkipped('This query is MySQL-specific.');
        }
    }

    public function testGetRootLine()
    {
        self::assertNotEmpty(Utility::getRootLine(1));
    }

    public function testGetDomain()
    {
        $this->writeSiteConfiguration(
            'acme-com',
            [
                'rootPageId' => 1,
                'base' => 'https://acme.com/',
            ]
        );
        self::assertEquals('acme.com', Utility::getDomain(1));
    }

    public function testGetMySqlCacheInformations()
    {
        if (! self::isNotSqlite()) {
            self::markTestSkipped('MySQL-specific report.');
        }
        if (self::isNotSqlite()) {
            self::assertNotEmpty(Utility::getMySqlCacheInformations());
        }
    }

    public function testGetMySqlCharacterSet()
    {
        if (! self::isNotSqlite()) {
            self::markTestSkipped('MySQL-specific report.');
        }
        if (self::isNotSqlite()) {
            self::assertNotEmpty(Utility::getMySqlCharacterSet());
        }
    }

    public function testGetAllDifferentPlugins()
    {
        $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'][] = [
            'label' => 'Indexed search',
            'value' => 'indexedsearch_pi2',
            'group' => 'plugins',
        ];

        self::assertSame(['indexedsearch_pi2'], array_column(Utility::getAllDifferentPlugins(), 'CType'));
    }

    public function testGetAllDifferentCtypes()
    {
        self::assertNotEmpty(Utility::getAllDifferentCtypes());
    }

    public function testGetAllPlugins()
    {
        $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'][] = [
            'label' => 'Indexed search',
            'value' => 'indexedsearch_pi2',
            'group' => 'plugins',
        ];

        self::assertSame(['indexedsearch_pi2'], array_values(array_unique(array_column(Utility::getAllPlugins(), 'CType'))));
    }

    public function testGetAllCtypes()
    {
        self::assertNotEmpty(Utility::getAllCtypes());
    }

    public function testGetAllCtypesFiltersWithAQueryParameter(): void
    {
        $items = Utility::getAllCtypes(false, 'text');

        self::assertNotEmpty($items);
        self::assertSame(['text'], array_values(array_unique(array_column($items, 'CType'))));
    }

    public function testGetAllCtypesDoesNotInterpretFilterAsSql(): void
    {
        self::assertSame([], Utility::getAllCtypes(false, "text' OR 1=1 --"));
    }

    public function testGetCountPagesUidsRejectsUnknownFields(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Utility::getCountPagesUids($this->pagesListProvider(), 'uid');
    }

    public function testGetLl()
    {
        self::assertNotEmpty(Utility::getLl('domain'));
    }

    public function testGetLanguageService()
    {
        self::assertNotEmpty(Utility::getLanguageService());
    }

    public function testSubModules()
    {
        self::assertNotEmpty(Utility::getSubModules());
    }

    public function pagesListProvider()
    {
        return '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54';
    }

    public static function isNotSqlite()
    {
        return $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['driver'] !== 'pdo_sqlite';
    }

    protected function writeSiteConfiguration(
        string $identifier,
        array $site = [],
        array $languages = [],
        array $errorHandling = []
    ) {
        $configuration = $site;
        if (! empty($languages)) {
            $configuration['languages'] = $languages;
        }
        if (! empty($errorHandling)) {
            $configuration['errorHandling'] = $errorHandling;
        }

        if (GeneralUtility::makeInstance(Typo3Version::class)->getBranch() === '12.4') {
            $siteConfiguration = new SiteConfiguration(
                $this->instancePath . '/typo3conf/sites/',
                $this->getContainer()
                    ->get(EventDispatcher::class)
            );
            try {
                // ensure no previous site configuration influences the test
                GeneralUtility::rmdir($this->instancePath . '/typo3conf/sites/' . $identifier, true);
                $siteConfiguration->write($identifier, $configuration);
            } catch (\Exception $exception) {
                self::markTestSkipped($exception->getMessage());
            }
        } else {
            try {
                $siteConfiguration = GeneralUtility::makeInstance(SiteConfiguration::class);
                GeneralUtility::rmdir($this->instancePath . '/typo3conf/sites/' . $identifier, true);
                $siteWriter = GeneralUtility::makeInstance(
                    \TYPO3\CMS\Core\Configuration\SiteWriter::class,
                    $this->instancePath . '/typo3conf/sites/',
                    $this->getContainer()
                        ->get(EventDispatcher::class),
                    GeneralUtility::makeInstance(YamlFileLoader::class)
                );
                $siteWriter->write($identifier, $configuration);
                $siteConfiguration->load($identifier);
            } catch (\Exception $exception) {
                self::markTestSkipped($exception->getMessage());
            }
        }
    }
}
