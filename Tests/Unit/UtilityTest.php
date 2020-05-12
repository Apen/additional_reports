<?php

namespace Sng\AdditionalReports\Tests\Unit;

use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\TestingFramework\Core\BaseTestCase;
use TYPO3\CMS\Core\Core\Bootstrap;

class UtilityTest extends BaseTestCase
{

    public function testReportsList()
    {
        $this->assertNotEmpty(Utility::getReportsList());
    }

    public function testSubModules()
    {
        $this->assertNotEmpty(Utility::getSubModules());
    }

    public function testGetInstExtList()
    {
        $extLits = Utility::getInstExtList(Environment::getBackendPath() . '/sysext/');
        $this->assertNotEmpty($extLits);
        $this->assertEquals('core', $extLits['dev']['core']['extkey']);
    }

    public function testIncludeEMCONF()
    {
        $emConf = Utility::includeEMCONF(__DIR__ . '../../../ext_emconf.php', 'additional_reports');
        $this->assertNotEmpty($emConf);
        $this->assertEquals('CERDAN Yohann', $emConf['author']);
    }

    public function testCheckExtensionUpdate()
    {
        $this->assertEmpty(Utility::checkExtensionUpdate(['extkey' => 'additional_reports']));
    }

    public function testGetExtIcon()
    {
        $this->assertNotEmpty(Utility::getExtIcon('core'));
    }

    public function testGetExtensionType()
    {
        $this->assertNotEmpty(Utility::getExtensionType('core'));
    }

    public function testGetExtPath()
    {
        $this->assertNotEmpty(Utility::getExtPath('core'));
    }

    public function testViewArray()
    {
        $this->assertNotEmpty(Utility::viewArray(['foo' => 'bar']));
    }

    public function testGenerateLink()
    {
        $this->assertNotEmpty(Utility::generateLink());
    }

    public function testGetExtensionVersion()
    {
        $this->assertEquals(TYPO3_version, Utility::getExtensionVersion('core'));
    }

    public function testWriteInformation()
    {
        $this->assertNotEmpty(Utility::writeInformation('foo', 'bar'));
    }

    public function testGetJsonVersionInfos()
    {
        $this->assertNotEmpty(Utility::getJsonVersionInfos());
    }

    public function testGetCurrentVersionInfos()
    {
        $this->assertNotEmpty(Utility::getCurrentVersionInfos(Utility::getJsonVersionInfos(), TYPO3_version));
    }

    public function testGetCurrentBranchInfos()
    {
        $this->assertNotEmpty(Utility::getCurrentBranchInfos(Utility::getJsonVersionInfos(), TYPO3_version));
    }

    public function testGetLatestStableInfos()
    {
        $this->assertNotEmpty(Utility::getLatestStableInfos(Utility::getJsonVersionInfos()));
    }

    public function testGetLatestLtsInfos()
    {
        $this->assertNotEmpty(Utility::getLatestLtsInfos(Utility::getJsonVersionInfos()));
    }

    public function testGetPluginsDisplayMode()
    {
        $this->assertEmpty(Utility::getPluginsDisplayMode());
    }

    public function testDownloadT3x()
    {
        $this->assertNotEmpty(Utility::downloadT3x('additional_reports', '3.3.2'));
    }

    public function testIsHook()
    {
        $hook = 'TYPO3\\CMS\\Frontend\\Hooks\\FrontendHooks->displayPreviewInfoMessage';
        $this->assertTrue(Utility::isHook($hook));
    }

    public function testGetHook()
    {
        $hook = 'TYPO3\\CMS\\Frontend\\Hooks\\FrontendHooks->displayPreviewInfoMessage';
        $this->assertNotEmpty(Utility::getHook($hook));
    }

    public function testGetPathSite()
    {
        $this->assertNotEmpty(Utility::getPathSite());
    }

    public function testGetPathTypo3Conf()
    {
        $this->assertNotEmpty(Utility::getPathTypo3Conf());
    }

    public function testIsComposerMode()
    {
        $this->assertTrue(Utility::isComposerMode());
    }

}