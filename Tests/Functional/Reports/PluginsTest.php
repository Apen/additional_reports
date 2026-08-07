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
        self::assertNotEmpty($report->display());
        self::assertTrue(GeneralUtility::makeInstance(AssetCollector::class)->hasJavaScript('additional-reports-plugins'));
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
