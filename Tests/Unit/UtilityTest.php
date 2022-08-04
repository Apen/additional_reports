<?php

namespace Sng\AdditionalReports\Tests\Unit;

use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\TestingFramework\Core\BaseTestCase;

class UtilityTest extends BaseTestCase
{
    public function testReportsList()
    {
        self::assertNotEmpty(Utility::getReportsList());
    }

    public function testGetInstExtList()
    {
        $extLits = Utility::getInstExtList(Environment::getBackendPath() . '/sysext/');
        self::assertNotEmpty($extLits);
        self::assertEquals('core', $extLits['dev']['core']['extkey']);
    }

    public function testIncludeEMCONF()
    {
        $emConf = Utility::includeEMCONF(__DIR__ . '../../../ext_emconf.php', 'additional_reports');
        self::assertNotEmpty($emConf);
        self::assertEquals('CERDAN Yohann', $emConf['author']);
    }

    public function testCheckExtensionUpdate()
    {
        self::assertEmpty(Utility::checkExtensionUpdate(['extkey' => 'additional_reports']));
    }

    public function testGetExtIcon()
    {
        self::assertNotEmpty(Utility::getExtIcon('core'));
    }

    public function testGetExtensionType()
    {
        self::assertNotEmpty(Utility::getExtensionType('core'));
    }

    public function testGetExtPath()
    {
        self::assertNotEmpty(Utility::getExtPath('core'));
    }

    public function testViewArray()
    {
        self::assertNotEmpty(Utility::viewArray(['foo' => 'bar']));
    }

    public function testGenerateLink()
    {
        self::assertNotEmpty(Utility::generateLink());
    }

    public function testGetExtensionVersion()
    {
        self::assertEquals(\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class)->getVersion(), Utility::getExtensionVersion('core'));
    }

    public function testWriteInformation()
    {
        self::assertNotEmpty(Utility::writeInformation('foo', 'bar'));
    }

    public function testGetJsonVersionInfos()
    {
        self::assertNotEmpty(Utility::getJsonVersionInfos());
    }

    public function testGetCurrentVersionInfos()
    {
        self::assertNotEmpty(Utility::getCurrentVersionInfos(Utility::getJsonVersionInfos(), \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class)->getVersion()));
    }

    public function testGetCurrentBranchInfos()
    {
        self::assertNotEmpty(Utility::getCurrentBranchInfos(Utility::getJsonVersionInfos(), \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class)->getVersion()));
    }

    public function testGetLatestStableInfos()
    {
        self::assertNotEmpty(Utility::getLatestStableInfos(Utility::getJsonVersionInfos()));
    }

    public function testGetLatestLtsInfos()
    {
        self::assertNotEmpty(Utility::getLatestLtsInfos(Utility::getJsonVersionInfos()));
    }

    public function testGetPluginsDisplayMode()
    {
        self::assertEmpty(Utility::getPluginsDisplayMode());
    }

    public function testDownloadT3x()
    {
        self::assertNotEmpty(Utility::downloadT3x('additional_reports', '3.3.2'));
    }

    public function testIsHook()
    {
        $hook = 'TYPO3\\CMS\\Backend\\Security\\EmailLoginNotification->emailAtLogin';
        self::assertTrue(Utility::isHook($hook));
    }

    public function testGetHook()
    {
        $hook = 'TYPO3\\CMS\\Backend\\Security\\EmailLoginNotification->emailAtLogin';
        self::assertNotEmpty(Utility::getHook($hook));
    }

    public function testGetPathSite()
    {
        self::assertNotEmpty(Utility::getPathSite());
    }

    public function testGetPathTypo3Conf()
    {
        self::assertNotEmpty(Utility::getPathTypo3Conf());
    }

    public function testIsComposerMode()
    {
        self::assertTrue(Utility::isComposerMode());
    }
}
