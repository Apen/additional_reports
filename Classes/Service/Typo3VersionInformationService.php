<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

final class Typo3VersionInformationService
{
    public function fetch(): array
    {
        $response = GeneralUtility::getUrl('https://get.typo3.org/json');
        if (! is_string($response) || $response === '') {
            return [];
        }

        $versions = json_decode($response, true);
        return is_array($versions) ? $versions : [];
    }

    public function getCurrentVersion(array $versions, string $version): array
    {
        $versionParts = explode('.', $version);
        if ((int) $versionParts[0] >= 7) {
            return $versions[$versionParts[0]]['releases'][$version] ?? [];
        }

        return $versions[$versionParts[0] . '.' . ($versionParts[1] ?? '0')]['releases'][$version] ?? [];
    }

    public function getCurrentBranch(array $versions, string $version): array
    {
        $versionParts = explode('.', $version);
        $branch = (int) $versionParts[0] >= 7
            ? $versionParts[0]
            : $versionParts[0] . '.' . ($versionParts[1] ?? '0');
        $releases = $versions[$branch]['releases'] ?? [];
        if (! is_array($releases)) {
            return [];
        }

        $release = reset($releases);
        return is_array($release) ? $release : [];
    }

    public function getLatestStable(array $versions): array
    {
        return $this->getNamedRelease($versions, 'latest_stable');
    }

    public function getLatestLts(array $versions): array
    {
        return $this->getNamedRelease($versions, 'latest_lts');
    }

    private function getNamedRelease(array $versions, string $key): array
    {
        $version = $versions[$key] ?? null;
        return is_string($version) ? $this->getCurrentVersion($versions, $version) : [];
    }
}
