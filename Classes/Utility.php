<?php

declare(strict_types=1);

namespace Sng\AdditionalReports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Composer\Semver\VersionParser;
use Sng\AdditionalReports\Repository\ContentUsageRepository;
use Sng\AdditionalReports\Repository\PageStatisticsRepository;
use Sng\AdditionalReports\Service\PackagistVersionService;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Package\Exception\UnknownPackageException;
use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SlidingWindowPagination;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Utility class
 */
class Utility
{
    /**
     * Define all the reports
     *
     * @return array
     */
    public static function getReportsList()
    {
        return [
            ['Eid', 'eid'],
            ['CommandControllers', 'commandcontrollers'],
            ['Plugins', 'plugins'],
            ['Xclass', 'xclass'],
            ['Hooks', 'hooks'],
            ['Status', 'status'],
            ['LogErrors', 'logerrors'],
            ['WebsiteConf', 'websitesconf'],
            ['Extensions', 'extensions'],
            ['EventDispatcher', 'eventdispatcher'],
            ['Middlewares', 'middlewares'],
        ];
    }

    /**
     * Generates a list of Page-uid's from $id
     *
     * @param int $id
     * @param int $depth
     * @param int $begin
     * @param string $permsClause
     * @return string
     */
    public static function getTreeList($id, $depth, $begin = 0, $permsClause = '1=1')
    {
        if ((int) $begin !== 0 || $permsClause !== '1=1') {
            throw new \InvalidArgumentException('Custom tree offsets and SQL permission clauses are no longer supported.');
        }
        return implode(',', GeneralUtility::makeInstance(PageStatisticsRepository::class)->findPageIdsRecursive((int) $id, (int) $depth));
    }

    /**
     * Count page uids in a list given (validating the condition)
     *
     * @param string $listOfUids
     * @param string $field
     * @return int
     */
    public static function getCountPagesUids(string $listOfUids, string $field = ''): int
    {
        $pageUids = array_values(array_filter(array_map('intval', explode(',', $listOfUids))));
        if ($pageUids === []) {
            return 0;
        }
        if ($field === '') {
            return count($pageUids);
        }
        return GeneralUtility::makeInstance(PageStatisticsRepository::class)->countByFlag($pageUids, $field);
    }

    /**
     * @return array{
     *     ter: array<string, array<string, mixed>>,
     *     dev: array<string, array<string, mixed>>,
     *     unloaded: array<string, array<string, mixed>>
     * }
     */
    public static function getExtensionList(): array
    {
        $list = ['ter' => [], 'dev' => [], 'unloaded' => []];
        $packageManager = GeneralUtility::makeInstance(PackageManager::class);

        foreach ($packageManager->getAvailablePackages() as $package) {
            if (! self::isThirdPartyExtension($package)) {
                continue;
            }

            $extensionKey = $package->getPackageKey();
            $packagePath = rtrim($package->getPackagePath(), '/\\') . DIRECTORY_SEPARATOR;
            $sqlFile = $packagePath . 'ext_tables.sql';
            $extension = [
                'extkey' => $extensionKey,
                'installed' => $packageManager->isPackageActive($extensionKey),
                'composerName' => $package->getValueFromComposerManifest('name'),
                'version' => $package->getPackageMetaData()->getVersion(),
                'lastversion' => null,
                'fdfile' => is_file($sqlFile) ? (string) GeneralUtility::getUrl($sqlFile) : '',
            ];
            $extension['lastversion'] = self::checkExtensionUpdate($extension);

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

    private static function isThirdPartyExtension(PackageInterface $package): bool
    {
        $packageType = $package->getPackageMetaData()->getPackageType();
        return is_string($packageType)
            && str_starts_with($packageType, 'typo3-cms-')
            && $packageType !== 'typo3-cms-framework';
    }

    /**
     * Get last version information for an extkey
     *
     * @param array $extInfo
     * @return array
     */
    public static function checkExtensionUpdate($extInfo)
    {
        $packageName = $extInfo['composerName'] ?? null;
        if (is_string($packageName) && $packageName !== '') {
            $installedVersion = $extInfo['version'] ?? null;
            return is_string($installedVersion)
                && VersionParser::parseStability($installedVersion) === 'stable'
                ? GeneralUtility::makeInstance(PackagistVersionService::class)->findLatestVersion($packageName)
                : null;
        }
        if (Environment::isComposerMode()) {
            return null;
        }
        $queryBuilder = self::getQueryBuilder('tx_extensionmanager_domain_model_extension');
        $lastVersion = $queryBuilder
            ->select('*')
            ->from('tx_extensionmanager_domain_model_extension')
            ->where($queryBuilder->expr()->eq('extension_key', $queryBuilder->createNamedParameter($extInfo['extkey'])))
            ->andWhere($queryBuilder->expr()->eq('current_version', 1))
            ->executeQuery()
            ->fetchAllAssociative();
        if ($lastVersion !== []) {
            $lastVersion[0]['updatedate'] = date('d/m/Y', $lastVersion[0]['last_updated']);
            return $lastVersion[0];
        }
        return null;
    }

    /**
     * Get the HTTP icon path of an extension
     *
     * @param string $extKey
     * @return string
     */
    public static function getExtIcon($extKey)
    {
        if (! empty($extKey)) {
            try {
                $package = GeneralUtility::makeInstance(PackageManager::class)->getPackage($extKey);
            } catch (UnknownPackageException) {
                return '';
            }

            $icon = $package->getPackageIcon();
            return $icon === null ? '' : PathUtility::getPublicResourceWebPath('EXT:' . $extKey . '/' . $icon);
        }
        return '';
    }

    /**
     * Get principal domain by page uid
     */
    public static function getDomain(int $pageUid): string
    {
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        try {
            $siteConf = $siteFinder->getSiteByPageId($pageUid);
            if (! empty($siteConf)) {
                return $siteConf->getBase()
                    ->getHost();
            }
        } catch (SiteNotFoundException $siteNotFoundException) {
            return '';
        }
        return '';
    }

    /**
     * Get the version of a given extension
     *
     * @param string $key
     */
    public static function getExtensionVersion($key): ?string
    {
        if (! is_string($key) || empty($key)) {
            throw new \InvalidArgumentException('Extension key must be a non-empty string.');
        }

        try {
            $package = GeneralUtility::makeInstance(PackageManager::class)->getPackage($key);
        } catch (UnknownPackageException) {
            return null;
        }
        return $package->getPackageMetaData()->getVersion();
    }

    /**
     * Get informations about the mysql cache
     */
    public static function getMySqlCacheInformations(): string
    {
        $queryCache = '';

        $res = self::getDatabaseConnection()->executeQuery('SHOW VARIABLES LIKE "%query_cache%";');
        while ($row = $res->fetchAssociative()) {
            $queryCache .= $row['Variable_name'] . ' : ' . $row['Value'] . '<br />';
        }

        $res = self::getDatabaseConnection()->executeQuery('SHOW STATUS LIKE "%Qcache%";');
        while ($row = $res->fetchAssociative()) {
            $queryCache .= $row['Variable_name'] . ' : ' . $row['Value'] . '<br />';
        }

        return $queryCache;
    }

    /**
     * Get informations about the mysql character_set
     */
    public static function getMySqlCharacterSet(): string
    {
        $sqlEncoding = '';

        $res = self::getDatabaseConnection()->executeQuery('SHOW VARIABLES LIKE "%character%";');
        while ($row = $res->fetchAssociative()) {
            $sqlEncoding .= $row['Variable_name'] . ' : ' . $row['Value'] . '<br />';
        }

        return $sqlEncoding;
    }

    public static function getAllDifferentPlugins(bool $displayHidden = false): array
    {
        return GeneralUtility::makeInstance(ContentUsageRepository::class)->findDistinctPlugins($displayHidden);
    }

    public static function getAllDifferentCtypes(bool $displayHidden = false): array
    {
        return GeneralUtility::makeInstance(ContentUsageRepository::class)->findDistinctContentTypes($displayHidden);
    }

    /**
     * Get all the usage of a all the plugins
     *
     * @return array
     */
    public static function getAllPlugins(bool $displayHidden = false, ?string $filter = null): array
    {
        return GeneralUtility::makeInstance(ContentUsageRepository::class)->findPlugins($displayHidden, $filter);
    }

    /**
     * Get all the usage of a all the ctypes
     *
     */
    public static function getAllCtypes(bool $displayHidden = false, ?string $filter = null): array
    {
        return GeneralUtility::makeInstance(ContentUsageRepository::class)->findContentTypes($displayHidden, $filter);
    }

    public static function hasLegacyListType(): bool
    {
        return GeneralUtility::makeInstance(ContentUsageRepository::class)->hasLegacyListType();
    }

    /**
     * @return list<string>
     */
    public static function getPluginContentTypes(): array
    {
        return GeneralUtility::makeInstance(ContentUsageRepository::class)->getPluginContentTypes();
    }

    /**
     * Return an array with all versions infos
     *
     * @return array
     */
    public static function getJsonVersionInfos()
    {
        return json_decode(GeneralUtility::getUrl('https://get.typo3.org/json'), true);
    }

    /**
     * Return an array with current version infos
     *
     * @return array
     */
    public static function getCurrentVersionInfos($jsonVersions, $version)
    {
        $currentVersion = explode('.', $version);
        if ((int) ($currentVersion[0]) >= 7) {
            return $jsonVersions[$currentVersion[0]]['releases'][$version] ?? [];
        }
        return $jsonVersions[$currentVersion[0] . '.' . $currentVersion[1]]['releases'][$version];
    }

    /**
     * Return an array with current branch infos
     *
     * @return array
     */
    public static function getCurrentBranchInfos($jsonVersions, $version)
    {
        $currentVersion = explode('.', $version);
        if ((int) ($currentVersion[0]) >= 7) {
            return @reset($jsonVersions[$currentVersion[0]]['releases']);
        }
        return @reset($jsonVersions[$currentVersion[0] . '.' . $currentVersion[1]]['releases']);
    }

    /**
     * Return an array with latest stable infos
     *
     * @return array
     */
    public static function getLatestStableInfos($jsonVersions)
    {
        $currentVersion = explode('.', $jsonVersions['latest_stable']);
        if ((int) ($currentVersion[0]) >= 7) {
            return $jsonVersions[$currentVersion[0]]['releases'][$jsonVersions['latest_stable']];
        }
        return $jsonVersions[$currentVersion[0] . '.' . $currentVersion[1]]['releases'][$jsonVersions['latest_stable']];
    }

    /**
     * Return an array with latest LTS infos
     *
     * @return array
     */
    public static function getLatestLtsInfos($jsonVersions)
    {
        $currentVersion = explode('.', $jsonVersions['latest_lts']);
        if ((int) ($currentVersion[0]) >= 7) {
            return $jsonVersions[$currentVersion[0]]['releases'][$jsonVersions['latest_lts']];
        }
        return $jsonVersions[$currentVersion[0] . '.' . $currentVersion[1]]['releases'][$jsonVersions['latest_lts']];
    }

    /**
     * Return the display mode
     *
     * @return int
     */
    public static function getPluginsDisplayMode(mixed $requestedDisplay = null): int
    {
        $displayMode = 0;

        if (! empty($GLOBALS['BE_USER'])) {
            // Check the display mode
            if ($requestedDisplay !== null) {
                $GLOBALS['BE_USER']->setAndSaveSessionData('additional_reports_menu', $requestedDisplay);
                $displayMode = $requestedDisplay;
            }

            // Check the session
            $sessionDisplay = $GLOBALS['BE_USER']->getSessionData('additional_reports_menu');
            if ($sessionDisplay !== null) {
                $displayMode = $sessionDisplay;
            }

            // force default reports to history value
            if ($displayMode == 1) {
                $displayMode = 5;
            }
        }

        return (int) $displayMode;
    }

    /**
     * Download an extension content
     *
     * @return array
     */
    public static function downloadT3x($extension, $version, $extFile = null)
    {
        $firstLetter = strtolower(substr($extension, 0, 1));
        $secondLetter = strtolower(substr($extension, 1, 1));
        $from = 'https://typo3.org/fileadmin/ter/' . $firstLetter . '/' . $secondLetter . '/' . $extension . '_' . trim($version) . '.t3x';
        $content = GeneralUtility::getURL($from);
        if (! is_string($content)) {
            throw new \RuntimeException('The extension archive could not be downloaded.');
        }
        $t3xfiles = self::extractExtensionDataFromT3x($content);
        if (empty($extFile)) {
            return $t3xfiles;
        }
        return $t3xfiles['FILES'][$extFile]['content'];
    }

    /**
     * Extract a t3x file
     *
     * @return array
     */
    public static function extractExtensionDataFromT3x(string $content): array
    {
        $parts = explode(':', $content, 3);
        if (($parts[1] ?? '') === 'gzcompress') {
            if (function_exists('gzuncompress')) {
                $uncompressedContent = gzuncompress($parts[2] ?? '');
                if (! is_string($uncompressedContent)) {
                    throw new \RuntimeException('Decoding Error: The compressed extension payload is invalid.');
                }
                $parts[2] = $uncompressedContent;
            } else {
                throw new \RuntimeException('Decoding Error: No decompressor available for compressed content. gzcompress()/gzuncompress() functions are not available!');
            }
        }
        $serializedContent = $parts[2] ?? '';
        if (isset($parts[0]) && hash_equals($parts[0], md5($serializedContent))) {
            $output = unserialize($serializedContent, ['allowed_classes' => false]);
            if (is_array($output) && ! self::containsObject($output)) {
                return $output;
            }
            throw new \UnexpectedValueException('Error: Content could not be safely unserialized to an array.');
        }
        throw new \UnexpectedValueException('Error: MD5 mismatch. Maybe the extension file was downloaded and saved as a text file by the browser and thereby corrupted!? (Always select "All" filetype when saving extensions)');
    }

    /** @param array<mixed> $values */
    private static function containsObject(array $values): bool
    {
        foreach ($values as $value) {
            if (is_object($value) || (is_array($value) && self::containsObject($value))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if string given is hook
     *
     * @param mixed $hook
     */
    public static function isHook(mixed $hook): bool
    {
        $isHook = false;
        if (! empty($hook)) {
            // if it's a key-path hook
            if (is_array($hook)) {
                $hook = $hook[1] ?? '';
            }
            if (! is_string($hook)) {
                return false;
            }
            // classname begin with &
            if (substr($hook, 0, 1) === '&') {
                $hook = substr($hook, 1);
            }
            // Check class exists
            if (class_exists($hook)) {
                $isHook = true;
            } elseif (strpos($hook, '\\') !== false && class_exists($hook)) {
                $isHook = true;
            } elseif (strpos($hook, '.php') !== false) {
                $hookArray = explode('.php', $hook);
                if (! empty($hookArray) && is_array($hookArray)) {
                    $file = GeneralUtility::getFileAbsFileName($hookArray[0] . '.php');
                    if (file_exists($file)) {
                        $isHook = true;
                    }
                }
            }
            // Check if function is used
            if (! $isHook && strpos($hook, '->') !== false) {
                $hookArray = explode('->', $hook);
                if (! empty($hookArray) && is_array($hookArray) && class_exists($hookArray[0])) {
                    $isHook = true;
                }
            }
        }
        return $isHook;
    }

    /**
     * Get the string from potential array and test it
     *
     * @param string|array $hookPotential
     * @return array|null
     * @see self::isHook
     */
    public static function getHook($hookPotential)
    {
        if (is_array($hookPotential)) {
            foreach ($hookPotential as $key => $value) {
                // if array nested
                if (is_array($value)) {
                    foreach ($value as $keySecond => $valueSecond) {
                        // stop allowing array nested
                        if (is_array($valueSecond)) {
                            unset($value[$keySecond]);
                        } elseif (! self::isHook($valueSecond)) {
                            unset($value[$keySecond]);
                        }
                    }
                } elseif (! self::isHook($value)) {
                    $value = null;
                }

                if (empty($value)) {
                    unset($hookPotential[$key]);
                } else {
                    $hookPotential[$key] = $value;
                }
            }
        } elseif (! self::isHook($hookPotential)) {
            $hookPotential = null;
        }

        return $hookPotential;
    }

    /**
     * Get a label
     */
    public static function getLl(string $key): string
    {
        return self::getLanguageService()->sL('LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:' . $key);
    }

    public static function getDatabaseConnection(): Connection
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
    }

    public static function getQueryBuilder(string $table = ''): QueryBuilder
    {
        if ($table !== '') {
            return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        }
        return self::getDatabaseConnection()->createQueryBuilder();
    }

    public static function getLanguageService(): LanguageService
    {
        // be
        if (! empty($GLOBALS['LANG'])) {
            return $GLOBALS['LANG'];
        }
        // fe
        if (! empty($GLOBALS['TSFE'])) {
            return $GLOBALS['TSFE'];
        }
        $languageService = GeneralUtility::makeInstance(LanguageServiceFactory::class)->create($GLOBALS['BE_USER']->uc['lang'] ?? 'default');
        $GLOBALS['LANG'] = $languageService;
        return $languageService;
    }

    public static function buildPagination(array $items, int $currentPage, &$view): void
    {
        if (count($items) > 0) {
            try {
                $itemsPerPage = (int) GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('additional_reports', 'itemsPerPage');
                if ($itemsPerPage < 1) {
                    $itemsPerPage = 10;
                }
            } catch (\Exception $e) {
                $itemsPerPage = 10;
            }
            $paginator = new ArrayPaginator($items, $currentPage, $itemsPerPage);
            $pagination = new SlidingWindowPagination($paginator, 5);
            $view->assign('paginator', $paginator);
            $view->assign('pagination', $pagination);
        }
    }

}
