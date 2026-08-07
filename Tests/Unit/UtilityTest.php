<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit;

use Sng\AdditionalReports\Utility;
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

    public function testGetPluginsDisplayMode()
    {
        self::assertEmpty(Utility::getPluginsDisplayMode());
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
