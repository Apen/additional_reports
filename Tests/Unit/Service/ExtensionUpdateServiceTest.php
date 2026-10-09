<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit\Service;

use Composer\Semver\VersionParser;
use Doctrine\DBAL\Driver\Result as DriverResult;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Sng\AdditionalReports\Service\ExtensionUpdateService;
use Sng\AdditionalReports\Service\PackageVersionProviderInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

final class ExtensionUpdateServiceTest extends TestCase
{
    #[DataProvider('classicExtensionProvider')]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testClassicExtensionsUseTerWithoutComposerSemver(?string $composerName, bool $hasTerVersion, bool $hasExtensionManager): void
    {
        self::assertFalse(class_exists(VersionParser::class, false));
        $rejectSemver = static function (string $class): void {
            if (str_starts_with($class, 'Composer\\Semver\\')) {
                throw new \RuntimeException('Composer Semver is unavailable in this classic installation.', 1791540001);
            }
        };
        spl_autoload_register($rejectSemver, true, true);

        $packageManager = $this->createMock(PackageManager::class);
        $packageManager->method('isPackageActive')->with('extensionmanager')->willReturn($hasExtensionManager);
        ExtensionManagementUtility::setPackageManager($packageManager);

        $packagist = $this->createMock(PackageVersionProviderInterface::class);
        $packagist->expects(self::never())->method('findLatestVersion');
        $terVersion = ['version' => '2.0.0', 'last_updated' => 0];
        $driverResult = $this->createMock(DriverResult::class);
        $driverResult->method('fetchAssociative')->willReturn($hasTerVersion ? $terVersion : false);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        foreach (['select', 'from', 'where', 'andWhere'] as $method) {
            $queryBuilder->method($method)->willReturnSelf();
        }
        $expressionBuilder = $this->createMock(ExpressionBuilder::class);
        $expressionBuilder->method('eq')->willReturn('extension_key = :extension');
        $queryBuilder->method('expr')->willReturn($expressionBuilder);
        $queryBuilder->method('createNamedParameter')->willReturn(':extension');
        $queryBuilder->method('executeQuery')->willReturn(new Result($driverResult, $this->createMock(Connection::class)));
        $connectionPool = $this->createMock(ConnectionPool::class);
        $connectionPool->expects($hasExtensionManager ? self::once() : self::never())->method('getQueryBuilderForTable')
            ->with('tx_extensionmanager_domain_model_extension')->willReturn($queryBuilder);

        $reflectionProperty = new \ReflectionProperty(Environment::class, 'composerMode');
        $originalMode = $reflectionProperty->getValue();
        $reflectionProperty->setValue(null, false);
        try {
            $result = (new ExtensionUpdateService($packagist, $connectionPool))->findLatestVersion([
                'extkey' => 'example',
                'composerName' => $composerName,
                'version' => '1.0.0',
            ]);
            self::assertSame($hasTerVersion ? $terVersion + ['updatedate' => date('d/m/Y', 0)] : null, $result);
        } finally {
            $reflectionProperty->setValue(null, $originalMode);
            spl_autoload_unregister($rejectSemver);
        }
    }

    /** @return iterable<string, array{0: string|null, 1: bool, 2: bool}> */
    public static function classicExtensionProvider(): iterable
    {
        yield 'Composer metadata in classic mode' => ['vendor/package', true, true];
        yield 'No Composer metadata in classic mode' => [null, true, true];
        yield 'Extension absent from TER' => ['vendor/private-package', false, true];
        yield 'Extension Manager unavailable' => ['vendor/package', false, false];
    }

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
