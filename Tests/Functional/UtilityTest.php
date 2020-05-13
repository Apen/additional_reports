<?php

namespace Sng\AdditionalReports\Tests\Functional;

use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class UtilityTest extends FunctionalTestCase
{

    protected $coreExtensionsToLoad = [
        'reports',
    ];

    protected $testExtensionsToLoad = [
        'typo3conf/ext/additional_reports',
    ];

    protected $configurationToUseInTestInstance = [
        'SYS' => [
            'caching' => [
                'cacheConfigurations' => [
                    'assets' => [
                        'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
                        'backend' => \TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend::class,
                        'options' => [
                            'defaultLifetime' => 0,
                        ],
                        'groups' => ['system']
                    ],
                    'l10n' => [
                        'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
                        'backend' => \TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend::class,
                        'options' => [
                            'defaultLifetime' => 0,
                        ],
                        'groups' => ['system']
                    ],
                ]
            ]
        ]
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->backendUser = $this->setUpBackendUserFromFixture(1);
        Bootstrap::initializeLanguageObject();
        $this->importDataSet(__DIR__ . '/Fixtures/pages.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/tt_content.xml');
    }

    /*
    public function testUseless()
    {
        $this->assertNotEmpty('test');
    }
    */

    public function testBaseUrl()
    {
        $this->assertNotEmpty(Utility::getBaseUrl());
    }

    public function testGetTreeList()
    {
        $this->assertEquals($this->pagesListProvider(), Utility::getTreeList(1, 99));
    }

    public function testGetCountPagesUids()
    {
        $this->assertEquals(0, Utility::getCountPagesUids($this->pagesListProvider(), 'hidden=1'));
        $this->assertEquals(1, Utility::getCountPagesUids($this->pagesListProvider(), 'no_search=1'));
    }

    public function testGetIconRefresh()
    {
        $this->assertNotEmpty(Utility::getIconRefresh());
    }

    public function testGetIconDomain()
    {
        $this->assertNotEmpty(Utility::getIconDomain());
    }

    public function testGetIconWebPage()
    {
        $this->assertNotEmpty(Utility::getIconWebPage());
    }

    public function testGetIconTemplate()
    {
        $this->assertNotEmpty(Utility::getIconTemplate());
    }

    public function testGetIconWebList()
    {
        $this->assertNotEmpty(Utility::getIconWebList());
    }

    public function testGetIconPage()
    {
        $this->assertNotEmpty(Utility::getIconPage());
    }

    public function testGetIconContent()
    {
        $this->assertNotEmpty(Utility::getIconContent());
    }

    public function testGetRootLine()
    {
        $this->assertNotEmpty(Utility::getRootLine(1));
    }

    public function testGetDomain()
    {
        $this->markTestSkipped();
    }

    public function testGoToModuleList()
    {
        $this->assertNotEmpty(Utility::goToModuleList(1));
    }

    public function testGoToModulePage()
    {
        $this->assertNotEmpty(Utility::goToModulePage(1));
    }

    public function testGetMySqlCacheInformations()
    {
        $this->assertNotEmpty(Utility::getMySqlCacheInformations());
    }

    public function testGetMySqlCharacterSet()
    {
        $this->assertNotEmpty(Utility::getMySqlCharacterSet());
    }

    public function testGetAllDifferentPlugins()
    {
        $this->assertNotEmpty(Utility::getAllDifferentPlugins(''));
    }

    public function testGetAllDifferentPluginsSelect()
    {
        $this->assertNotEmpty(Utility::getAllDifferentPluginsSelect(true));
    }

    public function testGetAllDifferentCtypes()
    {
        $this->assertNotEmpty(Utility::getAllDifferentCtypes(''));
    }

    public function testGetAllDifferentCtypesSelect()
    {
        $this->assertNotEmpty(Utility::getAllDifferentCtypesSelect(true));
    }

    public function testGetAllPlugins()
    {
        $this->assertNotEmpty(Utility::getAllPlugins(''));
    }

    public function testGetAllCtypes()
    {
        $this->assertNotEmpty(Utility::getAllCtypes(''));
    }

    public function testGetLl()
    {
        $this->assertNotEmpty(Utility::getLl('domain'));
    }

    public function testGetLanguageService()
    {
        $this->assertNotEmpty(Utility::getLanguageService());
    }

    public function testExec_SELECT_queryArray()
    {
        $this->assertNotEmpty(Utility::exec_SELECT_queryArray(['SELECT' => '*', 'FROM' => 'pages', 'WHERE' => '']));
    }

    public function testExec_SELECTgetRows()
    {
        $this->assertNotEmpty(Utility::exec_SELECTgetRows(' * ', 'pages', ''));
    }

    public function pagesListProvider()
    {
        return '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54';
    }

    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

}