<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Service;

use Composer\Semver\Semver;
use Composer\Semver\VersionParser;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class PackagistVersionService implements PackageVersionProviderInterface
{
    /**
     * @return array{version: string, updatedate: string, alldownloadcounter: string}|null
     */
    public function findLatestVersion(string $packageName): ?array
    {
        if (preg_match('#^[a-z0-9_.-]+/[a-z0-9_.-]+$#i', $packageName) !== 1) {
            return null;
        }

        $frontend = GeneralUtility::makeInstance(CacheManager::class)->getCache('hash');
        $cacheIdentifier = 'additional_reports_packagist_' . hash('xxh128', $packageName);
        if ($frontend->has($cacheIdentifier)) {
            $cachedResult = $frontend->get($cacheIdentifier);
            if (is_array($cachedResult)
                && is_string($cachedResult['version'] ?? null)
                && is_string($cachedResult['updatedate'] ?? null)
                && is_string($cachedResult['alldownloadcounter'] ?? null)
            ) {
                return [
                    'version' => $cachedResult['version'],
                    'updatedate' => $cachedResult['updatedate'],
                    'alldownloadcounter' => $cachedResult['alldownloadcounter'],
                ];
            }

            return null;
        }

        $result = null;
        try {
            $response = GeneralUtility::makeInstance(RequestFactory::class)->request(
                'https://packagist.org/packages/' . $packageName . '.json',
                'GET',
                ['timeout' => 3.0],
            );
            if ($response->getStatusCode() === 200) {
                $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
                $versions = $data['package']['versions'] ?? [];
                $result = $this->findLatestCompatibleStableVersion(is_array($versions) ? $versions : []);
            }
        } catch (\Throwable) {
            // Private packages, unavailable repositories and network errors have no public update information.
        }

        $frontend->set($cacheIdentifier, $result ?? false, [], 43200);
        return $result;
    }

    /**
     * @param array<string, array<string, mixed>> $versions
     * @return array{version: string, updatedate: string, alldownloadcounter: string}|null
     */
    public function findLatestCompatibleStableVersion(array $versions, ?string $typo3Version = null): ?array
    {
        $typo3Version ??= (new Typo3Version())->getVersion();
        $latest = null;
        foreach ($versions as $versionData) {
            $version = $versionData['version'] ?? null;
            if (! is_string($version)) {
                continue;
            }
            if (VersionParser::parseStability($version) !== 'stable') {
                continue;
            }
            if (! $this->isCompatible($versionData, $typo3Version)) {
                continue;
            }

            if ($latest === null || version_compare(ltrim($version, 'v'), ltrim($latest['version'], 'v'), '>')) {
                $time = is_string($versionData['time'] ?? null) ? strtotime($versionData['time']) : false;
                $latest = [
                    'version' => ltrim($version, 'v'),
                    'updatedate' => $time === false ? '' : date('d/m/Y', $time),
                    'alldownloadcounter' => '',
                ];
            }
        }

        return $latest;
    }

    /** @param array<string, mixed> $versionData */
    private function isCompatible(array $versionData, string $typo3Version): bool
    {
        $requirements = is_array($versionData['require'] ?? null) ? $versionData['require'] : [];
        return $this->matchesConstraint(PHP_VERSION, $requirements['php'] ?? null)
            && $this->matchesConstraint($typo3Version, $requirements['typo3/cms-core'] ?? null);
    }

    private function matchesConstraint(string $version, mixed $constraint): bool
    {
        return ! is_string($constraint) || Semver::satisfies($version, $constraint);
    }
}
