<?php

namespace Sng\AdditionalReports\Tests\Functional;

use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend;
use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Information\Typo3Version;
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
                        'groups' => ['system']
                    ],
                    'l10n' => [
                        'frontend' => VariableFrontend::class,
                        'backend' => SimpleFileBackend::class,
                        'options' => [
                            'defaultLifetime' => 0,
                        ],
                        'groups' => ['system']
                    ],
                ]
            ]
        ]
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/be_users.csv');
        $this->setUpBackendUser(1);
        Bootstrap::initializeLanguageObject();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/tt_content.csv');
        $uri = new Uri('https://localhost/typo3/');
        $request = new ServerRequest($uri);
        $request = $request->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE);
        $GLOBALS['TYPO3_REQUEST'] = $request;
    }

    public function testGetInstExtList()
    {
        $extLits = Utility::getInstExtList(Environment::getPublicPath() . '/typo3/sysext/');
        self::assertNotEmpty($extLits);
        self::assertEquals('core', $extLits['dev']['core']['extkey']);
    }

    public function testGetExtensionType()
    {
        self::assertNotEmpty(Utility::getExtensionType('core'));
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
        self::assertNotEmpty(Utility::getJsonVersionInfos());
    }

    public function testGetCurrentVersionInfos()
    {
        self::assertNotEmpty(Utility::getCurrentVersionInfos(Utility::getJsonVersionInfos(), GeneralUtility::makeInstance(Typo3Version::class)->getVersion()));
    }

    public function testGetCurrentBranchInfos()
    {
        self::assertNotEmpty(Utility::getCurrentBranchInfos(Utility::getJsonVersionInfos(), GeneralUtility::makeInstance(Typo3Version::class)->getVersion()));
    }

    public function testGetLatestStableInfos()
    {
        self::assertNotEmpty(Utility::getLatestStableInfos(Utility::getJsonVersionInfos()));
    }

    public function testGetLatestLtsInfos()
    {
        self::assertNotEmpty(Utility::getLatestLtsInfos(Utility::getJsonVersionInfos()));
    }

    public function testDownloadT3x()
    {
        self::assertNotEmpty(Utility::downloadT3x('additional_reports', '3.3.2'));
    }

    public function testBaseUrl()
    {
        self::assertNotEmpty(Utility::getBaseUrl());
    }

    public function testGetTreeList()
    {
        self::assertEquals($this->pagesListProvider(), Utility::getTreeList(1, 99));
    }

    public function testGetCountPagesUids()
    {
        if (self::isNotSqlite()) {
            self::assertEquals(0, Utility::getCountPagesUids($this->pagesListProvider(), 'hidden=1'));
            self::assertEquals(1, Utility::getCountPagesUids($this->pagesListProvider(), 'no_search=1'));
        }
    }

    public function testGetIconRefresh()
    {
        self::assertNotEmpty(Utility::getIconRefresh());
    }

    public function testGetIconDomain()
    {
        self::assertNotEmpty(Utility::getIconDomain());
    }

    public function testGetIconWebPage()
    {
        self::assertNotEmpty(Utility::getIconWebPage());
    }

    public function testGetIconTemplate()
    {
        self::assertNotEmpty(Utility::getIconTemplate());
    }

    public function testGetIconWebList()
    {
        self::assertNotEmpty(Utility::getIconWebList());
    }

    public function testGetIconPage()
    {
        self::assertNotEmpty(Utility::getIconPage());
    }

    public function testGetIconContent()
    {
        self::assertNotEmpty(Utility::getIconContent());
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

    public function testGoToModuleList()
    {
        self::assertNotEmpty(Utility::goToModuleList(1));
    }

    public function testGoToModulePage()
    {
        self::assertNotEmpty(Utility::goToModulePage(1));
    }

    public function testGetMySqlCacheInformations()
    {
        if (self::isNotSqlite()) {
            self::assertNotEmpty(Utility::getMySqlCacheInformations());
        }
    }

    public function testGetMySqlCharacterSet()
    {
        if (self::isNotSqlite()) {
            self::assertNotEmpty(Utility::getMySqlCharacterSet());
        }
    }

    public function testGetAllDifferentPlugins()
    {
        self::assertNotEmpty(Utility::getAllDifferentPlugins(''));
    }

    public function testGetAllDifferentPluginsSelect()
    {
        self::assertNotEmpty(Utility::getAllDifferentPluginsSelect(true));
    }

    public function testGetAllDifferentCtypes()
    {
        self::assertNotEmpty(Utility::getAllDifferentCtypes(''));
    }

    public function testGetAllDifferentCtypesSelect()
    {
        self::assertNotEmpty(Utility::getAllDifferentCtypesSelect(true));
    }

    public function testGetAllPlugins()
    {
        self::assertNotEmpty(Utility::getAllPlugins(''));
    }

    public function testGetAllCtypes()
    {
        self::assertNotEmpty(Utility::getAllCtypes(''));
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

    public function testExec_SELECT_queryArray()
    {
        self::assertNotEmpty(Utility::exec_SELECT_queryArray(['SELECT' => '*', 'FROM' => 'pages', 'WHERE' => '']));
    }

    public function testExec_SELECTgetRows()
    {
        self::assertNotEmpty(Utility::exec_SELECTgetRows(' * ', 'pages', ''));
    }

    public function pagesListProvider()
    {
        return '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54';
    }

    public static function isNotSqlite()
    {
        return $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['driver'] !== 'pdo_sqlite';
    }

    /**
     * @param string $identifier
     * @param array $site
     * @param array $languages
     * @param array $errorHandling
     */
    protected function writeSiteConfiguration(
        string $identifier,
        array  $site = [],
        array  $languages = [],
        array  $errorHandling = []
    )
    {
        $configuration = $site;
        if (!empty($languages)) {
            $configuration['languages'] = $languages;
        }
        if (!empty($errorHandling)) {
            $configuration['errorHandling'] = $errorHandling;
        }
        $siteConfiguration = new SiteConfiguration(
            $this->instancePath . '/typo3conf/sites/',
            $this->getContainer()->get(EventDispatcher::class)
        );

        try {
            // ensure no previous site configuration influences the test
            GeneralUtility::rmdir($this->instancePath . '/typo3conf/sites/' . $identifier, true);
            $siteConfiguration->write($identifier, $configuration);
        } catch (\Exception $exception) {
            self::markTestSkipped($exception->getMessage());
        }
    }
}
