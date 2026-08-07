<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit\Reports;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sng\AdditionalReports\Reports\CommandControllers;
use Sng\AdditionalReports\Reports\Eid;
use Sng\AdditionalReports\Reports\EventDispatcher;
use Sng\AdditionalReports\Reports\Hooks;
use Sng\AdditionalReports\Reports\LogErrors;
use Sng\AdditionalReports\Reports\Middlewares;
use Sng\AdditionalReports\Reports\Plugins;
use Sng\AdditionalReports\Reports\Status;
use Sng\AdditionalReports\Reports\WebsiteConf;
use Sng\AdditionalReports\Reports\Xclass;

final class ReportMetadataTest extends TestCase
{
    /**
     * @param class-string $reportClass
     */
    #[DataProvider('reportProvider')]
    public function testReportMetadata(string $reportClass, string $identifier): void
    {
        $report = (new \ReflectionClass($reportClass))->newInstanceWithoutConstructor();

        self::assertSame('additionalreports_' . $identifier, $report->getIdentifier());
        self::assertSame(
            'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:' . $identifier . '_title',
            $report->getTitle()
        );
        self::assertSame(
            'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:' . $identifier . '_description',
            $report->getDescription()
        );
        self::assertSame('module-reports', $report->getIconIdentifier());
    }

    public static function reportProvider(): iterable
    {
        yield [Eid::class, 'eid'];
        yield [CommandControllers::class, 'commandcontrollers'];
        yield [Plugins::class, 'plugins'];
        yield [Xclass::class, 'xclass'];
        yield [Hooks::class, 'hooks'];
        yield [Status::class, 'status'];
        yield [LogErrors::class, 'logerrors'];
        yield [WebsiteConf::class, 'websitesconf'];
        yield [EventDispatcher::class, 'eventdispatcher'];
        yield [Middlewares::class, 'middlewares'];
    }
}
