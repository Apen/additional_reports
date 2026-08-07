<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Sng\AdditionalReports\Service\PackagistVersionService;

final class PackagistVersionServiceTest extends TestCase
{
    public function testInvalidComposerPackageNameIsIgnored(): void
    {
        self::assertNull((new PackagistVersionService())->findLatestVersion('private-package'));
    }

    public function testLatestCompatibleStableVersionIsSelected(): void
    {
        $subject = new PackagistVersionService();

        $result = $subject->findLatestCompatibleStableVersion([
            '15.0.0' => ['version' => '15.0.0', 'require' => ['typo3/cms-core' => '^15']],
            '14.2.0-beta1' => ['version' => '14.2.0-beta1', 'require' => ['typo3/cms-core' => '^14']],
            '14.1.0' => ['version' => 'v14.1.0', 'time' => '2026-06-12T10:00:00+00:00', 'require' => ['typo3/cms-core' => '^14']],
            '13.4.0' => ['version' => '13.4.0', 'require' => ['typo3/cms-core' => '^13 || ^14']],
        ], '14.3.5');

        self::assertSame([
            'version' => '14.1.0',
            'updatedate' => '12/06/2026',
            'alldownloadcounter' => '',
        ], $result);
    }

    public function testVersionsWithoutConstraintsAreAcceptedAndInvalidDatesStayEmpty(): void
    {
        $result = (new PackagistVersionService())->findLatestCompatibleStableVersion([
            'invalid' => ['version' => 14],
            '14.0.0-rc1' => ['version' => '14.0.0-rc1'],
            '13.4.0' => ['version' => '13.4.0', 'time' => 'not-a-date'],
        ], '14.3.5');

        self::assertSame([
            'version' => '13.4.0',
            'updatedate' => '',
            'alldownloadcounter' => '',
        ], $result);
    }

    public function testIncompatiblePhpAndTypo3VersionsAreRejected(): void
    {
        $result = (new PackagistVersionService())->findLatestCompatibleStableVersion([
            '14.2.0' => ['version' => '14.2.0', 'require' => ['php' => '<8.0']],
            '14.1.0' => ['version' => '14.1.0', 'require' => ['typo3/cms-core' => '^13']],
        ], '14.3.5');

        self::assertNull($result);
    }
}
