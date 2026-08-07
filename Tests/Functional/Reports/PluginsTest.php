<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use PHPUnit\Framework\Attributes\DataProvider;
use Sng\AdditionalReports\Reports\Plugins;
use Sng\AdditionalReports\Service\ContentTypeResolver;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PluginsTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/pages.csv');
        $contentFixture = GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() >= 14
            ? '/../Fixtures/tt_content_v14.csv'
            : '/../Fixtures/tt_content.csv';
        $this->importCSVDataSet(__DIR__ . $contentFixture);
    }

    public function testDisplay()
    {
        $report = new Plugins(parent::getReportObject());
        $output = $report->display();

        self::assertNotEmpty($output);
        self::assertStringContainsString('class="notice col-xs-6"', $output);
        self::assertStringContainsString('class="additional-reports-view-options"', $output);
        self::assertStringContainsString('class="form-check-input"', $output);
        self::assertStringContainsString('/typo3/module/system/reports', $output);
        $assetCollector = GeneralUtility::makeInstance(AssetCollector::class);
        self::assertTrue($assetCollector->hasInlineJavaScript('additional-reports-plugins'));
        self::assertTrue($assetCollector->getInlineJavaScripts()['additional-reports-plugins']['options']['csp']);
    }

    public function testContentRowsAreEnrichedBeforeRendering(): void
    {
        $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'][] = [
            'label' => 'Example plugin',
            'value' => 'vendor_plugin',
        ];

        $rows = (new Plugins(parent::getReportObject()))->enrichContentRows([[
            'CType' => 'vendor_plugin',
            'pid' => 42,
            'title' => 'Example page',
        ]], 'plugin');

        self::assertSame('vendor', $rows[0]['extension']);
        self::assertSame('Example page', $rows[0]['pagetitle']);
        self::assertSame('/index.php?id=42', $rows[0]['preview']);
        self::assertArrayHasKey('domain', $rows[0]);
    }

    public function testTcaExtensionIconIsPublished(): void
    {
        $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'][] = [
            'label' => 'Example content type',
            'value' => 'vendor_example',
            'icon' => 'EXT:additional_reports/Resources/Public/Icons/Extension.svg',
        ];

        $result = GeneralUtility::makeInstance(ContentTypeResolver::class)->resolve('ctype', 'vendor_example');

        self::assertStringContainsString(
            'additional_reports/Resources/Public/Icons/Extension.svg',
            $result['iconext'],
        );
    }

    public function testSummaryContainsNormalizedCounters(): void
    {
        $summary = (new Plugins(parent::getReportObject()))->getSummary();

        self::assertNotEmpty($summary);
        self::assertIsInt($summary[0]['references']);
        self::assertIsFloat($summary[0]['pourc']);
    }

    #[DataProvider('displayModeProvider')]
    public function testDisplayModes(int $displayMode): void
    {
        $request = $GLOBALS['TYPO3_REQUEST'];
        $GLOBALS['TYPO3_REQUEST'] = $request->withQueryParams([
            ...$request->getQueryParams(),
            'display' => $displayMode,
        ]);

        $report = new Plugins(parent::getReportObject());

        self::assertNotEmpty($report->display());
    }

    public static function displayModeProvider(): iterable
    {
        yield 'visible content types' => [3];
        yield 'visible plugins' => [4];
        yield 'all plugins' => [6];
        yield 'all content types' => [7];
    }
}
