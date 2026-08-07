<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit\Repository;

use Sng\AdditionalReports\Repository\ContentUsageRepository;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\TestingFramework\Core\BaseTestCase;

final class ContentUsageRepositoryTest extends BaseTestCase
{
    private mixed $originalContentTypeItems;
    private mixed $originalListType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalContentTypeItems = $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] ?? null;
        $this->originalListType = $GLOBALS['TCA']['tt_content']['columns']['list_type'] ?? null;
    }

    protected function tearDown(): void
    {
        if ($this->originalContentTypeItems === null) {
            unset($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items']);
        } else {
            $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] = $this->originalContentTypeItems;
        }
        if ($this->originalListType === null) {
            unset($GLOBALS['TCA']['tt_content']['columns']['list_type']);
        } else {
            $GLOBALS['TCA']['tt_content']['columns']['list_type'] = $this->originalListType;
        }
        parent::tearDown();
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
            (new ContentUsageRepository())->getPluginContentTypes(),
        );
    }

    public function testLegacyListTypeRequiresTypo3BeforeVersionFourteenAndTcaColumn(): void
    {
        $GLOBALS['TCA']['tt_content']['columns']['list_type'] = ['config' => []];

        self::assertTrue((new ContentUsageRepository(typo3Version: $this->createTypo3Version(13)))->hasLegacyListType());
        self::assertFalse((new ContentUsageRepository(typo3Version: $this->createTypo3Version(14)))->hasLegacyListType());

        unset($GLOBALS['TCA']['tt_content']['columns']['list_type']);
        self::assertFalse((new ContentUsageRepository(typo3Version: $this->createTypo3Version(13)))->hasLegacyListType());
    }

    private function createTypo3Version(int $majorVersion): Typo3Version
    {
        $version = $this->createMock(Typo3Version::class);
        $version->method('getMajorVersion')->willReturn($majorVersion);
        return $version;
    }
}
