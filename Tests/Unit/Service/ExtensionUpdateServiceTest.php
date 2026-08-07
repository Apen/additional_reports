<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Sng\AdditionalReports\Service\ExtensionUpdateService;
use Sng\AdditionalReports\Service\PackageVersionProviderInterface;

final class ExtensionUpdateServiceTest extends TestCase
{
    public function testDevelopmentVersionIsNotCheckedAgainstPackagist(): void
    {
        $packagist = $this->createMock(PackageVersionProviderInterface::class);
        $packagist->expects(self::never())->method('findLatestVersion');

        self::assertNull((new ExtensionUpdateService($packagist))->findLatestVersion([
            'composerName' => 'vendor/private-package',
            'version' => 'dev-main',
        ]));
    }

    public function testStableComposerPackageUsesPackagist(): void
    {
        $latestVersion = ['version' => '2.0.0'];
        $packagist = $this->createMock(PackageVersionProviderInterface::class);
        $packagist->expects(self::once())->method('findLatestVersion')
            ->with('vendor/package')->willReturn($latestVersion);

        self::assertSame($latestVersion, (new ExtensionUpdateService($packagist))->findLatestVersion([
            'composerName' => 'vendor/package',
            'version' => '1.0.0',
        ]));
    }

    public function testComposerPackageWithoutComposerNameHasNoUpdateSource(): void
    {
        self::assertNull((new ExtensionUpdateService())->findLatestVersion([
            'extkey' => 'private_extension',
            'version' => '1.0.0',
        ]));
    }
}
