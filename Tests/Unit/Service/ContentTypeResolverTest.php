<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Sng\AdditionalReports\Service\ContentTypeResolver;
use TYPO3\CMS\Core\Information\Typo3Version;

final class ContentTypeResolverTest extends TestCase
{
    public function testPluginInformationContainsRawNameAndExtensionPrefix(): void
    {
        $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] = [];

        self::assertSame([
            'plugin' => 'vendor_plugin',
            'extension' => 'vendor',
        ], (new ContentTypeResolver())->resolve('plugin', 'vendor_plugin'));
    }

    public function testUnsupportedOrEmptyContentTypeIsIgnored(): void
    {
        $resolver = new ContentTypeResolver();

        self::assertSame([], $resolver->resolve('plugin', ''));
        self::assertSame([], $resolver->resolve('unknown', 'vendor_plugin'));
    }

    public function testContentTypeInformationUsesExtensionPrefix(): void
    {
        $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] = [[
            'label' => 'Text',
            'value' => 'vendor_text',
        ]];

        $result = (new ContentTypeResolver())->resolve('ctype', 'vendor_text');

        self::assertSame('vendor_text', $result['ctype']);
        self::assertSame('vendor', $result['extension']);
    }

    public function testContentTypeWithoutVendorPrefixHasNoExtension(): void
    {
        $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] = [];

        self::assertSame([
            'ctype' => 'text',
            'extension' => '',
        ], (new ContentTypeResolver())->resolve('ctype', 'text'));
    }

    public function testPluginContentTypesSupportAssociativeAndLegacyTcaItems(): void
    {
        $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] = [
            ['label' => 'Text', 'value' => 'text', 'group' => 'default'],
            ['label' => 'Plugin', 'value' => 'vendor_plugin', 'group' => 'plugins'],
            ['Legacy plugin', 'vendor_legacy', null, 'vendor'],
            ['Duplicate', 'vendor_plugin', null, 'custom'],
            ['Invalid', 42, null, 'custom'],
            ['Empty', '', null, 'custom'],
        ];

        self::assertSame(
            ['vendor_plugin', 'vendor_legacy'],
            (new ContentTypeResolver())->getPluginContentTypes(),
        );
    }

    public function testLegacyListTypeRequiresTypo3BeforeVersionFourteenAndTcaColumn(): void
    {
        $GLOBALS['TCA']['tt_content']['columns']['list_type'] = ['config' => []];

        self::assertTrue((new ContentTypeResolver($this->createTypo3Version(13)))->hasLegacyListType());
        self::assertFalse((new ContentTypeResolver($this->createTypo3Version(14)))->hasLegacyListType());

        unset($GLOBALS['TCA']['tt_content']['columns']['list_type']);
        self::assertFalse((new ContentTypeResolver($this->createTypo3Version(13)))->hasLegacyListType());
    }

    private function createTypo3Version(int $majorVersion): Typo3Version
    {
        $version = $this->createMock(Typo3Version::class);
        $version->method('getMajorVersion')->willReturn($majorVersion);
        return $version;
    }
}
