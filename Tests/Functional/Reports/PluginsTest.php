<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use PHPUnit\Framework\Attributes\DataProvider;
use Sng\AdditionalReports\Reports\Plugins;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PluginsTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testDisplay()
    {
        $report = new Plugins(parent::getReportObject());
        $output = $report->display();

        self::assertNotEmpty($output);
        self::assertStringContainsString('class="notice col-xs-6"', $output);
        self::assertStringContainsString('/typo3/module/system/reports', $output);
        self::assertTrue(GeneralUtility::makeInstance(AssetCollector::class)->hasInlineJavaScript('additional-reports-plugins'));
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
