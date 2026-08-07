<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit;

use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Backend\Security\EmailLoginNotification;
use TYPO3\TestingFramework\Core\BaseTestCase;

class UtilityTest extends BaseTestCase
{
    public function testPluginContentTypesAreReadFromTcaGroups(): void
    {
        $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] = [
            ['label' => 'Text', 'value' => 'text', 'group' => 'default'],
            ['label' => 'Menu', 'value' => 'menu', 'group' => 'menu'],
            ['label' => 'Plugin', 'value' => 'vendor_plugin', 'group' => 'plugins'],
            ['label' => 'Grouped plugin', 'value' => 'vendor_special', 'group' => 'vendor'],
        ];

        self::assertSame(['vendor_plugin', 'vendor_special'], Utility::getPluginContentTypes());
    }

    public function testReportsList(): void
    {
        self::assertSame([
            ['Eid', 'eid'],
            ['CommandControllers', 'commandcontrollers'],
            ['Plugins', 'plugins'],
            ['Xclass', 'xclass'],
            ['Hooks', 'hooks'],
            ['Status', 'status'],
            ['LogErrors', 'logerrors'],
            ['WebsiteConf', 'websitesconf'],
            ['Extensions', 'extensions'],
            ['EventDispatcher', 'eventdispatcher'],
            ['Middlewares', 'middlewares'],
        ], Utility::getReportsList());
    }

    public function testCheckExtensionUpdate()
    {
        self::assertEmpty(Utility::checkExtensionUpdate([
            'extkey' => 'additional_reports',
        ]));
    }

    public function testDevelopmentVersionIsNotComparedWithPackagist(): void
    {
        self::assertNull(Utility::checkExtensionUpdate([
            'extkey' => 'powermail',
            'composerName' => 'in2code/powermail',
            'version' => 'dev-master',
        ]));
    }

    public function testGetPluginsDisplayMode()
    {
        self::assertEmpty(Utility::getPluginsDisplayMode());
    }

    public function testIsHook()
    {
        $hook = EmailLoginNotification::class . '->emailAtLogin';
        self::assertTrue(Utility::isHook($hook));
        self::assertTrue(Utility::isHook('&' . EmailLoginNotification::class));
        self::assertTrue(Utility::isHook(['unused', $hook]));
        self::assertFalse(Utility::isHook(''));
        self::assertFalse(Utility::isHook(123));
        self::assertFalse(Utility::isHook(true));
        self::assertFalse(Utility::isHook(['unused', 123]));
        self::assertFalse(Utility::isHook('Unknown\\MissingClass->method'));
    }

    public function testGetHook()
    {
        $hook = EmailLoginNotification::class . '->emailAtLogin';
        self::assertNotEmpty(Utility::getHook($hook));
        self::assertNull(Utility::getHook('Unknown\\MissingClass'));
        self::assertNull(Utility::getHook(123));
        self::assertSame(
            ['valid' => $hook, 'nested' => ['valid' => $hook]],
            Utility::getHook([
                'valid' => $hook,
                'invalid' => 'Unknown\\MissingClass',
                'nested' => [
                    'valid' => $hook,
                    'invalid' => 'Unknown\\MissingClass',
                    'tooDeep' => [$hook],
                ],
            ])
        );
    }

    public function testTreeListRejectsLegacyOffsetAndPermissionArguments(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Utility::getTreeList(1, 2, 1);
    }

    public function testPageUidCountHandlesEmptyAndUnfilteredLists(): void
    {
        self::assertSame(0, Utility::getCountPagesUids(''));
        self::assertSame(3, Utility::getCountPagesUids('1,2,3'));
    }

    public function testExtensionVersionRejectsInvalidKeys(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Utility::getExtensionVersion(null);
    }

}
