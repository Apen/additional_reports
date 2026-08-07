<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Repository;

use Sng\AdditionalReports\Service\ExtensionUpdateService;
use TYPO3\CMS\Core\Package\Exception\UnknownPackageException;
use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final readonly class ExtensionRepository
{
    public function __construct(
        private ?PackageManager $packageManager = null,
        private ?ExtensionUpdateService $extensionUpdateService = null,
    ) {}

    /**
     * @return array{
     *     ter: array<string, array<string, mixed>>,
     *     dev: array<string, array<string, mixed>>,
     *     unloaded: array<string, array<string, mixed>>
     * }
     */
    public function findGrouped(): array
    {
        $list = ['ter' => [], 'dev' => [], 'unloaded' => []];
        $packageManager = $this->getPackageManager();
        foreach ($packageManager->getAvailablePackages() as $package) {
            if (! $this->isThirdPartyExtension($package)) {
                continue;
            }
            $extensionKey = $package->getPackageKey();
            $sqlFile = rtrim($package->getPackagePath(), '/\\') . DIRECTORY_SEPARATOR . 'ext_tables.sql';
            $extension = [
                'extkey' => $extensionKey,
                'installed' => $packageManager->isPackageActive($extensionKey),
                'composerName' => $package->getValueFromComposerManifest('name'),
                'version' => $package->getPackageMetaData()->getVersion(),
                'lastversion' => null,
                'fdfile' => is_file($sqlFile) ? (string) file_get_contents($sqlFile) : '',
            ];
            $updateService = $this->extensionUpdateService ?? GeneralUtility::makeInstance(ExtensionUpdateService::class);
            $extension['lastversion'] = $updateService->findLatestVersion($extension);
            if (! $extension['installed']) {
                $list['unloaded'][$extensionKey] = $extension;
            } elseif ($extension['lastversion'] !== null) {
                $list['ter'][$extensionKey] = $extension;
            } else {
                $list['dev'][$extensionKey] = $extension;
            }
        }
        return $list;
    }

    public function findVersion(string $extensionKey): ?string
    {
        if ($extensionKey === '') {
            throw new \InvalidArgumentException('Extension key must be a non-empty string.');
        }
        try {
            return $this->getPackageManager()->getPackage($extensionKey)->getPackageMetaData()->getVersion();
        } catch (UnknownPackageException) {
            return null;
        }
    }

    private function getPackageManager(): PackageManager
    {
        return $this->packageManager ?? GeneralUtility::makeInstance(PackageManager::class);
    }

    private function isThirdPartyExtension(PackageInterface $package): bool
    {
        $packageType = $package->getPackageMetaData()->getPackageType();
        return is_string($packageType)
            && str_starts_with($packageType, 'typo3-cms-')
            && $packageType !== 'typo3-cms-framework';
    }
}
