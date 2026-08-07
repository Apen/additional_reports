<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit;

use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Backend\Security\EmailLoginNotification;
use TYPO3\TestingFramework\Core\BaseTestCase;

class UtilityTest extends BaseTestCase
{
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

    public function testIncludeEMCONF()
    {
        $emConf = Utility::includeEMCONF(__DIR__ . '../../../ext_emconf.php', 'additional_reports');
        self::assertNotEmpty($emConf);
        self::assertEquals('CERDAN Yohann', $emConf['author']);
    }

    public function testCheckExtensionUpdate()
    {
        self::assertEmpty(Utility::checkExtensionUpdate([
            'extkey' => 'additional_reports',
        ]));
    }

    public function testViewArray(): void
    {
        $result = Utility::viewArray([
            '<key>' => ['nested' => '<value>'],
            'object' => new class {
                public function __toString(): string
                {
                    return '<object>';
                }
            },
        ]);

        self::assertStringContainsString('&lt;key&gt;', $result);
        self::assertStringContainsString('&lt;value&gt;', $result);
        self::assertStringContainsString('&lt;object&gt;', $result);
        self::assertStringNotContainsString('<value>', $result);
    }

    public function testViewArrayHandlesEmptyAndScalarValues(): void
    {
        self::assertStringContainsString('<strong>EMPTY!</strong>', Utility::viewArray([]));
        self::assertStringContainsString('&lt;unsafe&gt;', Utility::viewArray('<unsafe>'));
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
        self::assertTrue(Utility::isHook('&' . EmailLoginNotification::class));
        self::assertTrue(Utility::isHook(['unused', $hook]));
        self::assertFalse(Utility::isHook(''));
        self::assertFalse(Utility::isHook('Unknown\\MissingClass->method'));
    }

    public function testGetHook()
    {
        $hook = EmailLoginNotification::class . '->emailAtLogin';
        self::assertNotEmpty(Utility::getHook($hook));
        self::assertNull(Utility::getHook('Unknown\\MissingClass'));
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

    public function testVersionInformationHelpersSupportModernAndLegacyBranches(): void
    {
        $versions = [
            '14' => ['releases' => ['14.3.1' => ['version' => '14.3.1']]],
            '6.2' => ['releases' => ['6.2.31' => ['version' => '6.2.31']]],
            'latest_stable' => '14.3.1',
            'latest_lts' => '6.2.31',
        ];

        self::assertSame(['version' => '14.3.1'], Utility::getCurrentVersionInfos($versions, '14.3.1'));
        self::assertSame([], Utility::getCurrentVersionInfos($versions, '14.3.0'));
        self::assertSame(['version' => '6.2.31'], Utility::getCurrentVersionInfos($versions, '6.2.31'));
        self::assertSame(['version' => '14.3.1'], Utility::getCurrentBranchInfos($versions, '14.3.0'));
        self::assertSame(['version' => '6.2.31'], Utility::getCurrentBranchInfos($versions, '6.2.0'));
        self::assertSame(['version' => '14.3.1'], Utility::getLatestStableInfos($versions));
        self::assertSame(['version' => '6.2.31'], Utility::getLatestLtsInfos($versions));
    }

    public function testExtractExtensionDataFromT3x(): void
    {
        $data = ['FILES' => ['ext_localconf.php' => ['content' => '<?php']]];
        $serializedData = serialize($data);

        self::assertSame($data, Utility::extractExtensionDataFromT3x(md5($serializedData) . ':raw:' . $serializedData));

        $compressedData = gzcompress($serializedData);
        self::assertSame($data, Utility::extractExtensionDataFromT3x(md5($serializedData) . ':gzcompress:' . $compressedData));
    }

    public function testExtractExtensionDataRejectsInvalidContent(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('MD5 mismatch');

        Utility::extractExtensionDataFromT3x('invalid:raw:content');
    }

    public function testExtractExtensionDataRejectsNonArrayPayload(): void
    {
        $serializedData = serialize('not an array');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('could not be unserialized to an array');

        Utility::extractExtensionDataFromT3x(md5($serializedData) . ':raw:' . $serializedData);
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
