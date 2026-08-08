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
        $typo3VersionInformationService = new Typo3VersionInformationService();

        self::assertSame(['version' => '14.3.1'], $typo3VersionInformationService->getCurrentVersion($versions, '14.3.1'));
        self::assertSame([], $typo3VersionInformationService->getCurrentVersion($versions, '14.3.0'));
        self::assertSame(['version' => '6.2.31'], $typo3VersionInformationService->getCurrentVersion($versions, '6.2.31'));
        self::assertSame(['version' => '14.3.1'], $typo3VersionInformationService->getCurrentBranch($versions, '14.3.0'));
        self::assertSame(['version' => '6.2.31'], $typo3VersionInformationService->getCurrentBranch($versions, '6.2.0'));
        self::assertSame(['version' => '14.3.1'], $typo3VersionInformationService->getLatestStable($versions));
        self::assertSame(['version' => '6.2.31'], $typo3VersionInformationService->getLatestLts($versions));
    }

    public function testUnavailableAndMalformedInformationReturnsEmptyResults(): void
    {
        $typo3VersionInformationService = new Typo3VersionInformationService();

        self::assertSame([], $typo3VersionInformationService->getCurrentVersion([], '14.3.0'));
        self::assertSame([], $typo3VersionInformationService->getCurrentBranch([], '14.3.0'));
        self::assertSame([], $typo3VersionInformationService->getCurrentBranch(['14' => ['releases' => 'invalid']], '14.3.0'));
        self::assertSame([], $typo3VersionInformationService->getLatestStable([]));
        self::assertSame([], $typo3VersionInformationService->getLatestLts([]));
    }
}
