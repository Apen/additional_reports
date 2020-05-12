<?php

namespace Sng\AdditionalReports\Tests\Unit;

use Sng\AdditionalReports\Utility;
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
        $extLits = Utility::getInstExtList(Utility::getPathTypo3Conf() . 'ext/');
        $this->assertNotEmpty($extLits);
        $this->assertEquals('additional_reports', $extLits['dev']['additional_reports']['extkey']);
    }

    public function testIncludeEMCONF()
    {
        $emConf = Utility::includeEMCONF(Utility::getPathTypo3Conf() . 'ext/additional_reports/ext_emconf.php', 'additional_reports');
        $this->assertNotEmpty($emConf);
        $this->assertEquals('CERDAN Yohann', $emConf['author']);
    }

    public function testCheckExtensionUpdate()
    {
        $this->assertEmpty(Utility::checkExtensionUpdate(['extkey' => 'additional_reports']));
    }

    public function testGetExtIcon()
    {
        $this->assertNotEmpty(Utility::getExtIcon('additional_reports'));
    }

    public function testGetExtensionType()
    {
        $this->assertNotEmpty(Utility::getExtensionType('additional_reports'));
        $this->assertNotEmpty(Utility::getExtensionType('core'));
    }

    public function testGetExtPath()
    {
        $this->assertNotEmpty(Utility::getExtPath('additional_reports'));
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
        $this->assertNotEmpty(Utility::getExtensionVersion('additional_reports'));
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
        $hook['t3lib/class.t3lib_tcemain.php']['clearCachePostProc']['news_clearcache'] = 'GeorgRinger\\News\\Hooks\\DataHandler->clearCachePostProc';
        $this->assertTrue(Utility::isHook($hook['t3lib/class.t3lib_tcemain.php']['clearCachePostProc']['news_clearcache']));
    }

    public function testGetHook()
    {
        $hook['t3lib/class.t3lib_tcemain.php']['clearCachePostProc']['news_clearcache'] = 'GeorgRinger\\News\\Hooks\\DataHandler->clearCachePostProc';
        $this->assertNotEmpty(Utility::getHook($hook['t3lib/class.t3lib_tcemain.php']['clearCachePostProc']['news_clearcache']));
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