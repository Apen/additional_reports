<?php

namespace Sng\AdditionalReports\Tests\Unit;

use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Backend\Security\EmailLoginNotification;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\BaseTestCase;

class UtilityTest extends BaseTestCase
{
    public function testReportsList()
    {
        self::assertNotEmpty(Utility::getReportsList());
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

    public function testViewArray()
    {
        self::assertNotEmpty(Utility::viewArray(['foo' => 'bar']));
    }

    public function testGenerateLink()
    {
        self::assertNotEmpty(Utility::generateLink());
    }

    public function testWriteInformation()
    {
        self::assertNotEmpty(Utility::writeInformation('foo', 'bar'));
    }

    public function testGetPluginsDisplayMode()
    {
        self::assertEmpty(Utility::getPluginsDisplayMode());
    }

    public function testIsHook()
    {
        $hook = EmailLoginNotification::class . '->emailAtLogin';
        self::assertTrue(Utility::isHook($hook));
    }

    public function testGetHook()
    {
        $hook = EmailLoginNotification::class . '->emailAtLogin';
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
