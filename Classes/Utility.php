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
use Sng\AdditionalReports\Service\PackagistVersionService;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Information\Typo3Version;
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
        $depth = (int) $depth;
        $begin = (int) $begin;
        $id = (int) $id;
        $theList = $begin === 0 ? $id : '';
        if ($id && $depth > 0) {
            $queryBuilder = self::getQueryBuilder('pages');
            $queryBuilder
                ->select('uid')
                ->from('pages')
                ->where($queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($id)));
            if ($permsClause !== '1=1') {
                $queryBuilder->andWhere($permsClause);
            }
            $res = $queryBuilder->executeQuery();
            while ($row = $res->fetchAssociative()) {
                if ($begin <= 0) {
                    $theList .= ',' . $row['uid'];
                }
                if ($depth > 1) {
                    $theList .= self::getTreeList($row['uid'], $depth - 1, $begin - 1, $permsClause);
                }
            }
        }
        return $theList;
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
        $queryBuilder = self::getQueryBuilder('pages');
        $queryBuilder
            ->count('uid')
            ->from('pages')
            ->where($queryBuilder->expr()->in('uid', $queryBuilder->createNamedParameter($pageUids, Connection::PARAM_INT_ARRAY)));
        if ($field !== '') {
            if (! in_array($field, ['hidden', 'no_search'], true)) {
                throw new \InvalidArgumentException('Unsupported page field: ' . $field);
            }
            $queryBuilder->andWhere($queryBuilder->expr()->eq($field, 1));
        }
        return (int) $queryBuilder->executeQuery()->fetchOne();
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
        if (! self::hasLegacyListType()) {
            $pluginContentTypes = self::getPluginContentTypes();
            if ($pluginContentTypes === []) {
                return [];
            }
            $queryBuilder = self::createContentQueryBuilder($displayHidden);
            return $queryBuilder
                ->select('tt_content.CType')
                ->distinct()
                ->andWhere($queryBuilder->expr()->in('tt_content.CType', $queryBuilder->createNamedParameter($pluginContentTypes, \Doctrine\DBAL\ArrayParameterType::STRING)))
                ->orderBy('tt_content.CType')
                ->executeQuery()
                ->fetchAllAssociative();
        }
        $queryBuilder = self::createContentQueryBuilder($displayHidden);
        return $queryBuilder
            ->select('tt_content.list_type')
            ->distinct()
            ->andWhere($queryBuilder->expr()->eq('tt_content.CType', $queryBuilder->createNamedParameter('list')))
            ->andWhere($queryBuilder->expr()->neq('tt_content.list_type', $queryBuilder->createNamedParameter('')))
            ->orderBy('tt_content.list_type')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    public static function getAllDifferentCtypes(bool $displayHidden = false): array
    {
        $queryBuilder = self::createContentQueryBuilder($displayHidden);
        $queryBuilder
            ->select('tt_content.CType')
            ->distinct()
            ->andWhere($queryBuilder->expr()->neq('tt_content.CType', $queryBuilder->createNamedParameter('')));
        if (self::hasLegacyListType()) {
            $queryBuilder
                ->addSelect('tt_content.list_type')
                ->andWhere($queryBuilder->expr()->neq('tt_content.CType', $queryBuilder->createNamedParameter('list')))
                ->orderBy('tt_content.list_type');
        } else {
            $pluginContentTypes = self::getPluginContentTypes();
            if ($pluginContentTypes !== []) {
                $queryBuilder->andWhere($queryBuilder->expr()->notIn('tt_content.CType', $queryBuilder->createNamedParameter($pluginContentTypes, \Doctrine\DBAL\ArrayParameterType::STRING)));
            }
            $queryBuilder->orderBy('tt_content.CType');
        }
        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    /**
     * Get all the usage of a all the plugins
     *
     * @return array
     */
    public static function getAllPlugins(bool $displayHidden = false, ?string $filter = null): array
    {
        if (! self::hasLegacyListType()) {
            $pluginContentTypes = self::getPluginContentTypes();
            if ($pluginContentTypes === [] || ($filter !== null && $filter !== 'all' && ! in_array($filter, $pluginContentTypes, true))) {
                return [];
            }
            $queryBuilder = self::createContentQueryBuilder($displayHidden);
            $queryBuilder
                ->select('tt_content.CType', 'tt_content.pid', 'tt_content.uid', 'pages.title')
                ->addSelectLiteral('pages.hidden AS hiddenpages', 'tt_content.hidden AS hiddentt_content')
                ->distinct()
                ->andWhere($queryBuilder->expr()->in('tt_content.CType', $queryBuilder->createNamedParameter($pluginContentTypes, \Doctrine\DBAL\ArrayParameterType::STRING)))
                ->orderBy('tt_content.CType')
                ->addOrderBy('tt_content.pid');
            if ($filter !== null && $filter !== 'all') {
                $queryBuilder->andWhere($queryBuilder->expr()->eq('tt_content.CType', $queryBuilder->createNamedParameter($filter)));
            }
            return $queryBuilder->executeQuery()->fetchAllAssociative();
        }
        $queryBuilder = self::createContentQueryBuilder($displayHidden);
        $queryBuilder
            ->select('tt_content.list_type', 'tt_content.pid', 'tt_content.uid', 'pages.title')
            ->addSelectLiteral('pages.hidden AS hiddenpages', 'tt_content.hidden AS hiddentt_content')
            ->distinct()
            ->andWhere($queryBuilder->expr()->eq('tt_content.CType', $queryBuilder->createNamedParameter('list')))
            ->orderBy('tt_content.list_type')
            ->addOrderBy('tt_content.pid');
        if ($filter !== null && $filter !== 'all') {
            $queryBuilder->andWhere($queryBuilder->expr()->eq('tt_content.list_type', $queryBuilder->createNamedParameter($filter)));
        }
        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    /**
     * Get all the usage of a all the ctypes
     *
     */
    public static function getAllCtypes(bool $displayHidden = false, ?string $filter = null): array
    {
        $queryBuilder = self::createContentQueryBuilder($displayHidden);
        $queryBuilder
            ->select('tt_content.CType', 'tt_content.pid', 'tt_content.uid', 'pages.title')
            ->addSelectLiteral('pages.hidden AS hiddenpages', 'tt_content.hidden AS hiddentt_content')
            ->distinct()
            ->andWhere($queryBuilder->expr()->neq('tt_content.CType', $queryBuilder->createNamedParameter(self::hasLegacyListType() ? 'list' : '')))
            ->orderBy('tt_content.CType')
            ->addOrderBy('tt_content.pid');
        if (! self::hasLegacyListType()) {
            $pluginContentTypes = self::getPluginContentTypes();
            if ($pluginContentTypes !== []) {
                $queryBuilder->andWhere($queryBuilder->expr()->notIn('tt_content.CType', $queryBuilder->createNamedParameter($pluginContentTypes, \Doctrine\DBAL\ArrayParameterType::STRING)));
            }
        }
        if ($filter !== null && $filter !== 'all') {
            $queryBuilder->andWhere($queryBuilder->expr()->eq('tt_content.CType', $queryBuilder->createNamedParameter($filter)));
        }
        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    private static function createContentQueryBuilder(bool $displayHidden): QueryBuilder
    {
        $queryBuilder = self::getQueryBuilder('tt_content');
        $queryBuilder
            ->from('tt_content')
            ->innerJoin('tt_content', 'pages', 'pages', 'tt_content.pid = pages.uid')
            ->where($queryBuilder->expr()->gte('pages.pid', 0));
        if (! $displayHidden) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->eq('tt_content.hidden', 0))
                ->andWhere($queryBuilder->expr()->eq('pages.hidden', 0));
        }
        return $queryBuilder;
    }

    public static function hasLegacyListType(): bool
    {
        return GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() < 14
            && isset($GLOBALS['TCA']['tt_content']['columns']['list_type']);
    }

    /**
     * @return list<string>
     */
    public static function getPluginContentTypes(): array
    {
        $contentTypeGroups = ['default', 'lists', 'menu', 'forms', 'special'];
        $pluginContentTypes = [];
        foreach (($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] ?? []) as $item) {
            $value = $item['value'] ?? $item[1] ?? null;
            $group = $item['group'] ?? $item[3] ?? 'default';
            if (is_string($value) && $value !== '' && ! in_array($group, $contentTypeGroups, true)) {
                $pluginContentTypes[] = $value;
            }
        }
        return array_values(array_unique($pluginContentTypes));
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
