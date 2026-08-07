<?php

declare(strict_types=1);

namespace Sng\AdditionalReports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Package\Exception\UnknownPackageException;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SlidingWindowPagination;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;

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

    public static function getReportRouteIdentifier(string $report): string
    {
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() < 14) {
            return 'system_reports';
        }

        $report = preg_replace('/^additionalreports_/', '', $report);
        return 'system_reports_additionalreports_' . $report;
    }

    /**
     * Define all the sub modules
     *
     * @return array
     */
    public static function getSubModules()
    {
        return [
            'displayAjax' => self::getLL('ajax_title'),
            'displayEid' => self::getLL('eid_title'),
            'displayCliKeys' => self::getLL('clikeys_title'),
            'displayPlugins' => self::getLL('plugins_title'),
            'displayXclass' => self::getLL('xclass_title'),
            'displayHooks' => self::getLL('hooks_title'),
            'displayStatus' => self::getLL('status_title'),
            'displayExtensions' => self::getLL('extensions_title'),
            'displayLogErrors' => self::getLL('logerrors_title'),
            'displayWebsitesConf' => self::getLL('websitesconf_title'),
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
     * Gathers all extensions in $path
     *
     * @param string $path Absolute path to local, global or system extensions
     * @return array
     */
    public static function getInstExtList($path)
    {
        $list = [];
        $list['ter'] = $list['dev'] = $list['unloaded'] = [];

        if (self::isComposerMode()) {
            $packageManager = GeneralUtility::makeInstance(PackageManager::class);
            /** @var \TYPO3\CMS\Core\Package\PackageInterface $package */
            $activePackages = $packageManager->getActivePackages();
            foreach ($activePackages as $package) {
                $packagePath = $package->getPackagePath();
                $extKey = $package->getPackageKey();
                if (str_contains($packagePath, 'vendor/typo3')) {
                    continue;
                }
                if (@is_file($packagePath . '/ext_emconf.php')) {
                    $emConf = self::includeEMCONF($packagePath . '/ext_emconf.php', $package->getPackageKey());
                    if (is_array($emConf)) {
                        $currentExt = [];
                        $currentExt['extkey'] = $extKey;
                        $currentExt['installed'] = ExtensionManagementUtility::isLoaded($extKey);
                        $currentExt['EM_CONF'] = $emConf;
                        $currentExt['files'] = GeneralUtility::getFilesInDir($packagePath);
                        $currentExt['lastversion'] = self::checkExtensionUpdate($currentExt);

                        // db infos
                        $fileContent = '';
                        if (is_array($currentExt['files']) && in_array('ext_tables.sql', $currentExt['files'])) {
                            $fileContent = GeneralUtility::getUrl($packagePath . 'ext_tables.sql');
                        }
                        $currentExt['fdfile'] = $fileContent;

                        if ($currentExt['installed']) {
                            if ($currentExt['lastversion']) {
                                $list['ter'][$extKey] = $currentExt;
                            } else {
                                $list['dev'][$extKey] = $currentExt;
                            }
                        } else {
                            $list['unloaded'][$extKey] = $currentExt;
                        }
                    }
                }
            }
            return $list;
        }

        if (@is_dir($path)) {
            $extList = GeneralUtility::get_dirs($path);
            if (is_array($extList)) {
                foreach ($extList as $extKey) {
                    if (@is_file($path . $extKey . '/ext_emconf.php')) {
                        $emConf = self::includeEMCONF($path . $extKey . '/ext_emconf.php', $extKey);
                        if (is_array($emConf)) {
                            $currentExt = [];
                            $currentExt['extkey'] = $extKey;
                            $currentExt['installed'] = ExtensionManagementUtility::isLoaded($extKey);
                            $currentExt['EM_CONF'] = $emConf;
                            $currentExt['files'] = GeneralUtility::getFilesInDir($path . $extKey);
                            $currentExt['lastversion'] = self::checkExtensionUpdate($currentExt);

                            // db infos
                            $fileContent = '';
                            if (is_array($currentExt['files']) && in_array('ext_tables.sql', $currentExt['files'])) {
                                $fileContent = GeneralUtility::getUrl(self::getExtPath($currentExt['extkey']) . 'ext_tables.sql');
                            }
                            $currentExt['fdfile'] = $fileContent;

                            if ($currentExt['installed']) {
                                if ($currentExt['lastversion']) {
                                    $list['ter'][$extKey] = $currentExt;
                                } else {
                                    $list['dev'][$extKey] = $currentExt;
                                }
                            } else {
                                $list['unloaded'][$extKey] = $currentExt;
                            }
                        }
                    }
                }
            }
        }
        return $list;
    }

    /**
     * Returns the $EM_CONF array from an extensions ext_emconf.php file
     *
     * @param string $path Absolute path to EMCONF file.
     * @param string $_EXTKEY Extension key.
     * @return array
     * @noRector
     */
    public static function includeEMCONF($path, $_EXTKEY)
    {
        $EM_CONF = null;
        include $path;
        return $EM_CONF[$_EXTKEY];
    }

    /**
     * Get last version information for an extkey
     *
     * @param array $extInfo
     * @return array
     */
    public static function checkExtensionUpdate($extInfo)
    {
        if (self::isComposerMode()) {
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

    public static function getContentInfosFromTca($type, $value)
    {
        $infos = [];

        if (trim($value) === '') {
            return $infos;
        }

        $infos[$type] = $value;

        preg_match('#(^.*?)_#', $value, $matches);
        $infos['extension'] = $matches[1] ?? '';

        if ($type === 'plugin') {
            $pluginField = self::hasLegacyListType() ? 'list_type' : 'CType';
            foreach (($GLOBALS['TCA']['tt_content']['columns'][$pluginField]['config']['items'] ?? []) as $itemValue) {
                // v12
                if (trim($itemValue['value'] ?? '') === $value) {
                    $infos['iconext'] = '';
                    if (isset($itemValue['icon']) && PathUtility::isExtensionPath($itemValue['icon'])) {
                        $infos['iconext'] = PathUtility::getPublicResourceWebPath($itemValue['icon']);
                    }
                    $infos[$type] = self::getLanguageService()->sL($itemValue['label']) . ' (' . $value . ')';
                }
                // v11
                if (trim($itemValue[1] ?? '') === $value) {
                    $infos['iconext'] = PathUtility::getPublicResourceWebPath($itemValue[2]);
                    $infos[$type] = self::getLanguageService()->sL($itemValue[0]) . ' (' . $value . ')';
                }
            }
        }

        if ($type === 'ctype') {
            $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
            foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $itemValue) {
                if (($itemValue['value'] ?? $itemValue[1] ?? '') === '--div--') {
                    continue;
                }
                if (trim($itemValue['value'] ?? $itemValue[1] ?? '') !== $value) {
                    continue;
                }
                $iconPath = $itemValue['icon'] ?? $itemValue[2] ?? '';
                if (str_contains($iconPath, 'EXT:')) {
                    $infos['iconext'] = PathUtility::getPublicResourceWebPath($iconPath);
                } elseif ($iconRegistry->isRegistered($iconPath)) {
                    $icon = $iconRegistry->getIconConfigurationByIdentifier($iconPath);
                    $iconSource = $icon['options']['source'] ?? null;
                    if (is_string($iconSource) && str_contains($iconSource, 'EXT:')) {
                        $infos['iconext'] = PathUtility::getPublicResourceWebPath($iconSource);
                    } elseif (is_string($iconSource) && $iconSource !== '') {
                        $infos['iconext'] = PathUtility::getAbsoluteWebPath($iconSource);
                    }
                }
            }
        }

        return $infos;
    }

    /**
     * Get rootline by page uid
     */
    public static function getRootLine(int $pageUid): array
    {
        $rootline = GeneralUtility::makeInstance(RootlineUtility::class, $pageUid);
        return $rootline->get();
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
     * Get the absolute path of an extension
     */
    public static function getExtPath(string $extKey): string
    {
        return self::getPathTypo3Conf() . 'ext/' . $extKey . '/';
    }

    /**
     * Print a debug of an array
     *
     * @param array $arrayIn
     */
    public static function viewArray($arrayIn): string
    {
        if (is_array($arrayIn)) {
            $result = '<table class="table table-striped table-condensed"><tbody>';
            if (count($arrayIn) === 0) {
                $result .= '<tr><td><strong>EMPTY!</strong></td></tr>';
            } else {
                foreach ($arrayIn as $key => $val) {
                    $result .= '<tr><td>' . htmlspecialchars((string) $key) . '</td><td>';
                    if (is_array($val)) {
                        $result .= self::viewArray($val);
                    } elseif (is_object($val)) {
                        $string = get_class($val);
                        if (method_exists($val, '__toString')) {
                            $string .= ': ' . $val;
                        }
                        $result .= nl2br(htmlspecialchars($string)) . '<br />';
                    } else {
                        $string = gettype($val) === 'object' ? 'Unknown object' : (string) $val;
                        $result .= nl2br(htmlspecialchars($string)) . '<br />';
                    }
                    $result .= '</td></tr>';
                }
            }
            $result .= '</tbody></table>';
        } else {
            $result = '<table class="table table-striped table-condensed">';
            $result .= '<tr><td>' . nl2br(htmlspecialchars((string) $arrayIn)) . '</td></tr></table>';
        }
        return $result;
    }

    /**
     * Return a link to the module list
     *
     * @param int $uid
     * @param bool $urlOnly
     */
    public static function goToModuleList($uid, $urlOnly = false): string
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $url = $uriBuilder->buildUriFromRoute('web_list') . '&id=' . $uid;
        if ($urlOnly) {
            return $url;
        }
        return "top.nextLoadModuleUrl='" . $url . "';top.goToModule('web_list');";
    }

    /**
     * Return a link to the module page
     *
     * @param int $uid
     * @param bool $urlOnly
     */
    public static function goToModulePage($uid, $urlOnly = false): string
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $url = $uriBuilder->buildUriFromRoute('web_layout') . '&id=' . $uid;
        if ($urlOnly) {
            return $url;
        }
        return "top.nextLoadModuleUrl='" . $url . "';top.goToModule('web_layout');";
    }

    /**
     * Get the version of a given extension
     *
     * @param string $key
     */
    public static function getExtensionVersion($key): ?string
    {
        $EM_CONF = [];
        if (! is_string($key) || empty($key)) {
            throw new \InvalidArgumentException('Extension key must be a non-empty string.');
        }

        if (self::isComposerMode()) {
            $packageManager = GeneralUtility::makeInstance(PackageManager::class);
            /** @var \TYPO3\CMS\Core\Package\PackageInterface $package */
            try {
                $package = $packageManager->getPackage($key);
            } catch (UnknownPackageException $e) {
                return null;
            }
            if ($package === null) {
                return null;
            }
            return $package->getPackageMetaData()
                ->getVersion();
        }

        if (! ExtensionManagementUtility::isLoaded($key)) {
            return null;
        }

        // need for the next include
        $_EXTKEY = $key;

        if (! is_file(ExtensionManagementUtility::extPath($key) . 'ext_emconf.php')) {
            return null;
        }

        include ExtensionManagementUtility::extPath($key) . 'ext_emconf.php';

        return $EM_CONF[$key]['version'] ?? '?';
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

    /**
     * Generate a special formated div (with icon)
     *
     * @param string $label
     * @param string $value
     */
    public static function writeInformation($label, $value): string
    {
        return '
        <table class="table table-striped table-hover">
            <tbody>
			<tr>
				<td class="notice col-xs-6">' . $label . '</td>
				<td class="notice col-xs-6">' . $value . '</td>
			</tr>
		    </tbody>
		</table>
		';
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
    public static function getPluginsDisplayMode()
    {
        $displayMode = 0;

        if (! empty($GLOBALS['BE_USER'])) {
            // Check the display mode
            $display = self::_GP('display');
            if ($display !== null) {
                $GLOBALS['BE_USER']->setAndSaveSessionData('additional_reports_menu', $display);
                $displayMode = $display;
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
    public static function extractExtensionDataFromT3x($content)
    {
        $parts = explode(':', $content, 3);
        if (($parts[1] ?? '') === 'gzcompress') {
            if (function_exists('gzuncompress')) {
                $parts[2] = gzuncompress($parts[2]);
            } else {
                throw new \Exception('Decoding Error: No decompressor available for compressed content. gzcompress()/gzuncompress() functions are not available!');
            }
        }
        if (md5($parts[2] ?? '') == $parts[0]) {
            $output = unserialize($parts[2]);
            if (is_array($output)) {
                return $output;
            }
            throw new \Exception('Error: Content could not be unserialized to an array. Strange (since MD5 hashes match!)');
        }
        throw new \Exception('Error: MD5 mismatch. Maybe the extension file was downloaded and saved as a text file by the browser and thereby corrupted!? (Always select "All" filetype when saving extensions)');
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

    public static function getPathSite(): string
    {
        return Environment::getPublicPath();
    }

    public static function getPathTypo3Conf(): string
    {
        return Environment::getPublicPath() . '/typo3conf/';
    }

    public static function isComposerMode(): bool
    {
        return defined('TYPO3_COMPOSER_MODE') && TYPO3_COMPOSER_MODE;
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

    public static function _GP(string $key)
    {
        return
            $GLOBALS['TYPO3_REQUEST']->getParsedBody()[$key] ??
            $GLOBALS['TYPO3_REQUEST']->getQueryParams()[$key] ??
            null;
    }

    public static function _GET(string $key)
    {
        return $GLOBALS['TYPO3_REQUEST']->getQueryParams()[$key] ?? null;
    }
}
