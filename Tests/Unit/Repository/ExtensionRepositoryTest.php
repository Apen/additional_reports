<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit\Repository;

use PHPUnit\Framework\TestCase;
use Sng\AdditionalReports\Repository\ExtensionRepository;

final class ExtensionRepositoryTest extends TestCase
{
    public function testEmptyExtensionKeyIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new ExtensionRepository())->findVersion('');
    }

    public function testDevelopmentVersionIsNotCheckedAgainstPackagist(): void
    {
        self::assertNull((new ExtensionRepository())->findLatestVersion([
            'composerName' => 'vendor/private-package',
            'version' => 'dev-main',
        ]));
    }

    public function testComposerPackageWithoutComposerNameHasNoUpdateSource(): void
    {
        self::assertNull((new ExtensionRepository())->findLatestVersion([
            'extkey' => 'private_extension',
            'version' => '1.0.0',
        ]));
    }
}
