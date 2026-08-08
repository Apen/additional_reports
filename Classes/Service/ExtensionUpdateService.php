<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Service;

use Composer\Semver\VersionParser;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final readonly class ExtensionUpdateService
{
    public function __construct(
        private ?PackageVersionProviderInterface $packageVersionProvider = null,
        private ?ConnectionPool $connectionPool = null,
    ) {}

    /** @param array<string, mixed> $extension */
    public function findLatestVersion(array $extension): ?array
    {
        $packageName = $extension['composerName'] ?? null;
        if (is_string($packageName) && $packageName !== '') {
            $installedVersion = $extension['version'] ?? null;
            if (! is_string($installedVersion) || VersionParser::parseStability($installedVersion) !== 'stable') {
                return null;
            }

            $service = $this->packageVersionProvider ?? GeneralUtility::makeInstance(PackagistVersionService::class);
            return $service->findLatestVersion($packageName);
        }

        if (Environment::isComposerMode()) {
            return null;
        }

        $connectionPool = $this->connectionPool ?? GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tx_extensionmanager_domain_model_extension');
        $lastVersion = $queryBuilder->select('*')->from('tx_extensionmanager_domain_model_extension')
            ->where($queryBuilder->expr()->eq('extension_key', $queryBuilder->createNamedParameter($extension['extkey'] ?? '')))
            ->andWhere($queryBuilder->expr()->eq('current_version', 1))
            ->executeQuery()->fetchAssociative();
        if (! is_array($lastVersion)) {
            return null;
        }

        $lastVersion['updatedate'] = date('d/m/Y', (int) $lastVersion['last_updated']);
        return $lastVersion;
    }
}
