<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Sng\AdditionalReports\Service\Typo3VersionInformationService;

final class Typo3VersionInformationServiceTest extends TestCase
{
    public function testVersionInformationSupportsModernAndLegacyBranches(): void
    {
        $versions = [
            '14' => ['releases' => ['14.3.1' => ['version' => '14.3.1']]],
            '6.2' => ['releases' => ['6.2.31' => ['version' => '6.2.31']]],
            'latest_stable' => '14.3.1',
            'latest_lts' => '6.2.31',
        ];
        $service = new Typo3VersionInformationService();

        self::assertSame(['version' => '14.3.1'], $service->getCurrentVersion($versions, '14.3.1'));
        self::assertSame([], $service->getCurrentVersion($versions, '14.3.0'));
        self::assertSame(['version' => '6.2.31'], $service->getCurrentVersion($versions, '6.2.31'));
        self::assertSame(['version' => '14.3.1'], $service->getCurrentBranch($versions, '14.3.0'));
        self::assertSame(['version' => '6.2.31'], $service->getCurrentBranch($versions, '6.2.0'));
        self::assertSame(['version' => '14.3.1'], $service->getLatestStable($versions));
        self::assertSame(['version' => '6.2.31'], $service->getLatestLts($versions));
    }

    public function testUnavailableAndMalformedInformationReturnsEmptyResults(): void
    {
        $service = new Typo3VersionInformationService();

        self::assertSame([], $service->getCurrentVersion([], '14.3.0'));
        self::assertSame([], $service->getCurrentBranch([], '14.3.0'));
        self::assertSame([], $service->getCurrentBranch(['14' => ['releases' => 'invalid']], '14.3.0'));
        self::assertSame([], $service->getLatestStable([]));
        self::assertSame([], $service->getLatestLts([]));
    }
}
