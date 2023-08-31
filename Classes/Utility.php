<?php

namespace Sng\AdditionalReports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\AdditionalReports\Pagination\SimplePagination;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Core\Information\Typo3Version;

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
     * Get base url of the report (use to generate links)
     *
     * @return string url
     */
    public static function getBaseUrl(): string
    {
        $parameters = [];
        $parameters['extension'] = 'additional_reports';
        $parameters['action'] = 'detail';
        $parameters['report'] = GeneralUtility::_GET('report');
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $url = $uriBuilder->buildUriFromRoute('system_reports', $parameters);
        return (string)$url;
    }

    /**
     * Define all the sub modules
     *
     * @return array
     */
    public static function getSubModules()
    {
        return [
            'displayAjax' => Utility::getLanguageService()->getLL('ajax_title'),
            'displayEid' => Utility::getLanguageService()->getLL('eid_title'),
            'displayCliKeys' => Utility::getLanguageService()->getLL('clikeys_title'),
            'displayPlugins' => Utility::getLanguageService()->getLL('plugins_title'),
            'displayXclass' => Utility::getLanguageService()->getLL('xclass_title'),
            'displayHooks' => Utility::getLanguageService()->getLL('hooks_title'),
            'displayStatus' => Utility::getLanguageService()->getLL('status_title'),
            'displayExtensions' => Utility::getLanguageService()->getLL('extensions_title'),
            'displayLogErrors' => Utility::getLanguageService()->getLL('logerrors_title'),
            'displayWebsitesConf' => Utility::getLanguageService()->getLL('websitesconf_title'),
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
        $depth = (int)$depth;
        $begin = (int)$begin;
        $id = (int)$id;
        $theList = $begin === 0 ? $id : '';
        if ($id && $depth > 0) {
            $res = self::exec_SELECTquery('uid', 'pages', 'pid=' . $id . ' AND ' . $permsClause);
            while ($row = $res->fetch()) {
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
     * @param string $where
     * @return int
     */
    public static function getCountPagesUids($listOfUids, $where = '1=1')
    {
        $res = self::exec_SELECTquery('uid', 'pages', 'uid IN (' . $listOfUids . ') AND ' . $where);
        $count = $res->rowCount();
        return $count;
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
                            $currentExt['files'] = GeneralUtility::getFilesInDir($path . $extKey, '', 0, '', null);
                            $currentExt['lastversion'] = Utility::checkExtensionUpdate($currentExt);
                            $currentExt['icon'] = Utility::getExtIcon($extKey);

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
        include($path);
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
        $lastVersion = Utility::exec_SELECTgetRows('*', 'tx_extensionmanager_domain_model_extension', 'extension_key="' . $extInfo['extkey'] . '" AND current_version=1');
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
        if (!empty($extKey)) {
            $extType = self::getExtensionType($extKey);
            $path = $extType['siteRelPath'] . ExtensionManagementUtility::getExtensionIcon(
                    Utility::getPathSite() . '/' . $extType['siteRelPath']
                );
            return GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $path;
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
            foreach ($GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'] as $itemValue) {
                // v12
                if (trim($itemValue['value'] ?? '') === $value) {
                    $infos['iconext'] = PathUtility::getPublicResourceWebPath($itemValue['icon']);
                    $infos[$type] = Utility::getLanguageService()->sL($itemValue['label']) . ' (' . $value . ')';
                }
                // v11
                if (trim($itemValue[1] ?? '') === $value) {
                    $infos['iconext'] = PathUtility::getPublicResourceWebPath($itemValue[2]);
                    $infos[$type] = Utility::getLanguageService()->sL($itemValue[0]) . ' (' . $value . ')';
                }
            }
        }

        if ($type === 'ctype') {
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
                } else {
                    $icon = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class)->getIconConfigurationByIdentifier(
                        $iconPath
                    );
                    if (str_contains($icon['options']['source'], 'EXT:')) {
                        $infos['iconext'] = PathUtility::getPublicResourceWebPath($icon['options']['source']);
                    } else {
                        $infos['iconext'] = PathUtility::getAbsoluteWebPath($icon['options']['source']);
                    }
                }
            }
        }

        return $infos;
    }

    /**
     * Get the HTTP icon path of an extension
     *
     * @param string $path
     * @return string
     */
    public static function getContentTypeIcon($path)
    {
        $icon = null;
        if (is_file(Utility::getPathSite() . '/typo3/sysext/core/Resources/Public/Icons/T3Icons/content/' . $path . '.svg')) {
            $icon = GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/core/Resources/Public/Icons/T3Icons/content/' . $path . '.svg';
        } elseif (preg_match('#^\.\.#', $path, $temp)) {
            $icon = GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR') . $path;
        } elseif (preg_match('#^EXT:(.*)$#', $path, $temp)) {
            $icon = GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR') . '../typo3conf/ext/' . $temp[1];
        }
        return $icon;
    }

    /**
     * Get the icon path of refresh icon
     *
     * @return string
     */
    public static function getIconRefresh(): string
    {
        return GeneralUtility::makeInstance(IconFactory::class)->getIcon(
            'actions-system-refresh',
            Icon::SIZE_SMALL
        )->render();
    }

    /**
     * Get the icon path of zoom icon
     *
     * @return string
     */
    public static function getIconDomain(): string
    {
        return GeneralUtility::makeInstance(IconFactory::class)->getIcon(
            'apps-pagetree-page-domain',
            Icon::SIZE_SMALL
        )->render();
    }

    /**
     * Get the icon path of web page icon
     *
     * @return string
     */
    public static function getIconWebPage(): string
    {
        return GeneralUtility::makeInstance(IconFactory::class)->getIcon(
            'actions-version-page-open',
            Icon::SIZE_SMALL
        )->render();
    }

    /**
     * Get the icon path of template
     *
     * @return string
     */
    public static function getIconTemplate(): string
    {
        return GeneralUtility::makeInstance(IconFactory::class)->getIcon(
            'mimetypes-x-content-template',
            Icon::SIZE_SMALL
        )->render();
    }

    /**
     * Get the icon path of web list icon
     *
     * @return string
     */
    public static function getIconWebList(): string
    {
        return GeneralUtility::makeInstance(IconFactory::class)->getIcon(
            'actions-system-list-open',
            Icon::SIZE_SMALL
        )->render();
    }

    /**
     * Get the icon path of page icon
     *
     * @param bool $hidden
     * @return string
     */
    public static function getIconPage(bool $hidden = false): string
    {
        if ($hidden) {
            return GeneralUtility::makeInstance(IconFactory::class)->getIcon(
                'apps-pagetree-page-default',
                Icon::SIZE_SMALL,
                'overlay-hidden'
            )->render();
        }
        return GeneralUtility::makeInstance(IconFactory::class)->getIcon(
            'apps-pagetree-page-default',
            Icon::SIZE_SMALL
        )->render();
    }

    /**
     * Get the icon path of content icon
     *
     * @param bool $hidden
     * @return string
     */
    public static function getIconContent(bool $hidden = false): string
    {
        if ($hidden) {
            return GeneralUtility::makeInstance(IconFactory::class)->getIcon(
                'content-text',
                Icon::SIZE_SMALL,
                'overlay-hidden'
            )->render();
        }
        return GeneralUtility::makeInstance(IconFactory::class)->getIcon(
            'content-text',
            Icon::SIZE_SMALL
        )->render();
    }

    /**
     * Get the type and the path of an extension
     *
     * @param string $extKey
     * @return array
     */
    public static function getExtensionType(string $extKey): array
    {
        if (is_dir(Utility::getPathTypo3Conf() . 'ext/' . $extKey . '/')) {
            return [
                'type' => 'L',
                'siteRelPath' => 'typo3conf/ext/' . $extKey . '/',
                'typo3RelPath' => '../typo3conf/ext/' . $extKey . '/',
            ];
        }
        if (is_dir(Environment::getPublicPath() . '/typo3/ext/' . $extKey . '/')) {
            return [
                'type' => 'G',
                'siteRelPath' => TYPO3_mainDir . 'ext/' . $extKey . '/',
                'typo3RelPath' => 'ext/' . $extKey . '/',
            ];
        }
        if (is_dir(Environment::getPublicPath() . '/typo3/sysext/' . $extKey . '/')) {
            return [
                'type' => 'S',
                'siteRelPath' => TYPO3_mainDir . 'sysext/' . $extKey . '/',
                'typo3RelPath' => 'sysext/' . $extKey . '/',
            ];
        }
        return [];
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
            if (!empty($siteConf)) {
                return $siteConf->getBase()->getHost();
            }
        } catch (SiteNotFoundException $siteNotFoundException) {
            return '';
        }
        return '';
    }

    /**
     * Get the absolute path of an extension
     *
     * @param string $extKey
     * @return string
     */
    public static function getExtPath(string $extKey): string
    {
        return self::getPathTypo3Conf() . 'ext/' . $extKey . '/';
    }

    /**
     * Print a debug of an array
     *
     * @param array $arrayIn
     * @return string
     */
    public static function viewArray($arrayIn): string
    {
        if (is_array($arrayIn)) {
            $result = '<table class="table table-striped table-condensed">';
            if (count($arrayIn) === 0) {
                $result .= '<tr><td><strong>EMPTY!</strong></td></tr>';
            } else {
                foreach ($arrayIn as $key => $val) {
                    $result .= '<tr><td>' . htmlspecialchars((string)$key) . '</td><td>';
                    if (is_array($val)) {
                        $result .= self::viewArray($val);
                    } elseif (is_object($val)) {
                        $string = get_class($val);
                        if (method_exists($val, '__toString')) {
                            $string .= ': ' . $val;
                        }
                        $result .= nl2br(htmlspecialchars($string)) . '<br />';
                    } else {
                        $string = gettype($val) === 'object' ? 'Unknown object' : (string)$val;
                        $result .= nl2br(htmlspecialchars($string)) . '<br />';
                    }
                    $result .= '</td></tr>';
                }
            }
            $result .= '</table>';
        } else {
            $result = '<table class="table table-striped table-condensed">';
            $result .= '<tr><td>' . nl2br(htmlspecialchars((string)$arrayIn)) . '</td></tr></table>';
        }
        return $result;
    }

    /**
     * Return a link to the module list
     *
     * @param int $uid
     * @param bool $urlOnly
     * @return string
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
     * @return string
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
     * Return a <a...>...</a> code
     *
     * @param array $att
     * @param string $content
     * @return string
     */
    public static function generateLink($att = [], $content = ''): string
    {
        $attList = '';
        foreach ($att as $attKey => $attValue) {
            $attList .= ' ' . $attKey . '="' . $attValue . '"';
        }
        return '<a' . $attList . '>' . $content . '</a>';
    }

    /**
     * Get the version of a given extension
     *
     * @param string $key
     * @return string
     */
    public static function getExtensionVersion($key): ?string
    {
        $EM_CONF = [];
        if (!is_string($key) || empty($key)) {
            throw new \InvalidArgumentException('Extension key must be a non-empty string.');
        }
        if (!ExtensionManagementUtility::isLoaded($key)) {
            return null;
        }

        // need for the next include
        $_EXTKEY = $key;
        include(ExtensionManagementUtility::extPath($key) . 'ext_emconf.php');

        return $EM_CONF[$key]['version'] ?? '?';
    }

    /**
     * Get informations about the mysql cache
     *
     * @return string
     */
    public static function getMySqlCacheInformations(): string
    {
        $queryCache = '';

        $res = Utility::sql_query('SHOW VARIABLES LIKE "%query_cache%";');
        while ($row = $res->fetch()) {
            $queryCache .= $row['Variable_name'] . ' : ' . $row['Value'] . '<br />';
        }

        $res = Utility::sql_query('SHOW STATUS LIKE "%Qcache%";');
        while ($row = $res->fetch()) {
            $queryCache .= $row['Variable_name'] . ' : ' . $row['Value'] . '<br />';
        }

        return $queryCache;
    }

    /**
     * Get informations about the mysql character_set
     *
     * @return string
     */
    public static function getMySqlCharacterSet(): string
    {
        $sqlEncoding = '';

        $res = Utility::sql_query('SHOW VARIABLES LIKE "%character%";');
        while ($row = $res->fetch()) {
            $sqlEncoding .= $row['Variable_name'] . ' : ' . $row['Value'] . '<br />';
        }

        return $sqlEncoding;
    }

    /**
     * Generate a special formated div (with icon)
     *
     * @param string $label
     * @param string $value
     * @return string
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

    /**
     * Get all the different plugins
     *
     * @param string $where
     * @return array
     */
    public static function getAllDifferentPlugins($where): array
    {
        return Utility::exec_SELECTgetRows(
            'DISTINCT tt_content.list_type',
            'tt_content,pages',
            'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.deleted=0 AND pages.deleted=0 ' . $where . 'AND tt_content.CType=\'list\' AND tt_content.list_type<>""',
            '',
            'tt_content.list_type'
        );
    }

    /**
     * Get all the different plugins (html select)
     *
     * @param bool $displayHidden
     * @return string
     */
    public static function getAllDifferentPluginsSelect($displayHidden): string
    {
        $where = ($displayHidden) ? '' : ' AND tt_content.hidden=0 AND pages.hidden=0 ';
        $getFiltersCat = GeneralUtility::_GP('filtersCat');
        $pluginsList = self::getAllDifferentPlugins($where);
        $filterCat = '';

        if ($getFiltersCat == 'all') {
            $filterCat .= '<option value="all" selected="selected">' . Utility::getLanguageService()->getLL('all') . '</option>';
        } else {
            $filterCat .= '<option value="all">' . Utility::getLanguageService()->getLL('all') . '</option>';
        }

        foreach ($pluginsList as $pluginsElement) {
            if (($getFiltersCat == $pluginsElement['list_type']) && ($getFiltersCat !== null)) {
                $filterCat .= '<option value="' . $pluginsElement['list_type'] . '" selected="selected">';
                $filterCat .= $pluginsElement['list_type'] . '</option>';
            } else {
                $filterCat .= '<option value="' . $pluginsElement['list_type'] . '">' . $pluginsElement['list_type'] . '</option>';
            }
        }

        $listUrlOrig = Utility::getBaseUrl() . '&display=' . Utility::getPluginsDisplayMode();

        return '<select name="filtersCat" id="filtersCat" data-url="' . $listUrlOrig . '">' . $filterCat . '</select>';
    }

    /**
     * Get all the different ctypes
     *
     * @param string $where
     * @return array
     */
    public static function getAllDifferentCtypes($where): array
    {
        return Utility::exec_SELECTgetRows(
            'DISTINCT tt_content.CType,tt_content.list_type',
            'tt_content,pages',
            'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.deleted=0 AND pages.deleted=0 ' . $where . "AND tt_content.CType<>'list'",
            '',
            'tt_content.list_type'
        );
    }

    /**
     * Get all the different ctypes (html select)
     *
     * @param bool $displayHidden
     * @return string
     */
    public static function getAllDifferentCtypesSelect($displayHidden): string
    {
        $where = ($displayHidden) ? '' : ' AND tt_content.hidden=0 AND pages.hidden=0 ';
        $getFiltersCat = GeneralUtility::_GP('filtersCat');
        $pluginsList = self::getAllDifferentCtypes($where);
        $filterCat = '';

        if ($getFiltersCat == 'all') {
            $filterCat .= '<option value="all" selected="selected">' . Utility::getLanguageService()->getLL('all') . '</option>';
        } else {
            $filterCat .= '<option value="all">' . Utility::getLanguageService()->getLL('all') . '</option>';
        }

        foreach ($pluginsList as $pluginsElement) {
            if (($getFiltersCat == $pluginsElement['CType']) && ($getFiltersCat !== null)) {
                $filterCat .= '<option value="' . $pluginsElement['CType'] . '" selected="selected">';
                $filterCat .= $pluginsElement['CType'] . '</option>';
            } else {
                $filterCat .= '<option value="' . $pluginsElement['CType'] . '">' . $pluginsElement['CType'] . '</option>';
            }
        }

        $listUrlOrig = Utility::getBaseUrl() . '&display=' . Utility::getPluginsDisplayMode();

        return '<select name="filtersCat" id="filtersCat" data-url="' . $listUrlOrig . '">' . $filterCat . '</select>';
    }

    /**
     * Get all the usage of a all the plugins
     *
     * @param string $where
     * @param string $limit
     * @return array
     */
    public static function getAllPlugins($where, $limit = '', $returnQuery = false)
    {
        $query = [
            'SELECT' => 'DISTINCT tt_content.list_type,tt_content.pid,tt_content.uid,pages.title,pages.hidden as "hiddenpages",tt_content.hidden as "hiddentt_content"',
            'FROM' => 'tt_content,pages',
            'WHERE' => 'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.deleted=0 AND pages.deleted=0 ' . $where . "AND tt_content.CType='list'",
            'ORDERBY' => 'tt_content.list_type,tt_content.pid',
            'LIMIT' => $limit,
        ];
        if ($returnQuery === true) {
            return $query;
        }
        return Utility::exec_SELECTgetRows(
            $query['SELECT'],
            $query['FROM'],
            $query['WHERE'],
            '',
            $query['ORDERBY'],
            $query['LIMIT']
        );
    }

    /**
     * Get all the usage of a all the ctypes
     *
     * @param string $where
     * @param string $limit
     * @return array
     */
    public static function getAllCtypes($where, $limit = '', $returnQuery = false): array
    {
        $query = [
            'SELECT' => 'DISTINCT tt_content.CType,tt_content.pid,tt_content.uid,pages.title,pages.hidden as "hiddenpages",tt_content.hidden as "hiddentt_content"',
            'FROM' => 'tt_content,pages',
            'WHERE' => 'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.deleted=0 AND pages.deleted=0 ' . $where . "AND tt_content.CType<>'list'",
            'ORDERBY' => 'tt_content.CType,tt_content.pid',
            'LIMIT' => $limit,
        ];
        if ($returnQuery === true) {
            return $query;
        }
        return Utility::exec_SELECTgetRows(
            $query['SELECT'],
            $query['FROM'],
            $query['WHERE'],
            '',
            $query['ORDERBY'],
            $query['LIMIT']
        );
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
     * @param $jsonVersions
     * @param $version
     *
     * @return array
     */
    public static function getCurrentVersionInfos($jsonVersions, $version)
    {
        $currentVersion = explode('.', $version);
        if ((int)($currentVersion[0]) >= 7) {
            return $jsonVersions[$currentVersion[0]]['releases'][$version] ?? [];
        }
        return $jsonVersions[$currentVersion[0] . '.' . $currentVersion[1]]['releases'][$version];
    }

    /**
     * Return an array with current branch infos
     *
     * @param $jsonVersions
     * @param $version
     *
     * @return array
     */
    public static function getCurrentBranchInfos($jsonVersions, $version)
    {
        $currentVersion = explode('.', $version);
        if ((int)($currentVersion[0]) >= 7) {
            return @reset($jsonVersions[$currentVersion[0]]['releases']);
        }
        return @reset($jsonVersions[$currentVersion[0] . '.' . $currentVersion[1]]['releases']);
    }

    /**
     * Return an array with latest stable infos
     *
     * @param $jsonVersions
     *
     * @return array
     */
    public static function getLatestStableInfos($jsonVersions)
    {
        $currentVersion = explode('.', $jsonVersions['latest_stable']);
        if ((int)($currentVersion[0]) >= 7) {
            return $jsonVersions[$currentVersion[0]]['releases'][$jsonVersions['latest_stable']];
        }
        return $jsonVersions[$currentVersion[0] . '.' . $currentVersion[1]]['releases'][$jsonVersions['latest_stable']];
    }

    /**
     * Return an array with latest LTS infos
     *
     * @param $jsonVersions
     *
     * @return array
     */
    public static function getLatestLtsInfos($jsonVersions)
    {
        $currentVersion = explode('.', $jsonVersions['latest_lts']);
        if ((int)($currentVersion[0]) >= 7) {
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

        if (!empty($GLOBALS['BE_USER'])) {
            // Check the display mode
            $display = GeneralUtility::_GP('display');
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

        return (int)$displayMode;
    }

    /**
     * Download an extension content
     *
     * @param $extension
     * @param $version
     * @param $extFile
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
     * @param $content
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
     * @param string $hook
     * @return bool
     */
    public static function isHook($hook): bool
    {
        $isHook = false;
        if (!empty($hook)) {
            // if it's a key-path hook
            if (is_array($hook)) {
                $isHook = self::isHook($hook[1]);
            }
            // classname begin with &
            if (substr($hook, 0, 1) === '&') {
                $hook = substr($hook, 1);
            }
            //Check class exists
            if (class_exists($hook)) {
                $isHook = true;
            } elseif (strpos($hook, '\\') !== false && class_exists($hook)) {
                $isHook = true;
            } elseif (strpos($hook, '.php') !== false) {
                $hookArray = explode('.php', $hook);
                if (!empty($hookArray) && is_array($hookArray)) {
                    $file = GeneralUtility::getFileAbsFileName($hookArray[0] . '.php');
                    if (file_exists($file)) {
                        $isHook = true;
                    }
                }
            }
            //Check if function is used
            if (!$isHook && strpos($hook, '->') !== false) {
                $hookArray = explode('->', $hook);
                if (!empty($hookArray) && is_array($hookArray) && class_exists($hookArray[0])) {
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
                //if array nested
                if (is_array($value)) {
                    foreach ($value as $keySecond => $valueSecond) {
                        //stop allowing array nested
                        if (is_array($valueSecond)) {
                            unset($value[$keySecond]);
                        } elseif (!self::isHook($valueSecond)) {
                            unset($value[$keySecond]);
                        }
                    }
                } elseif (!self::isHook($value)) {
                    $value = null;
                }

                if (empty($value)) {
                    unset($hookPotential[$key]);
                } else {
                    $hookPotential[$key] = $value;
                }
            }
        } elseif (!self::isHook($hookPotential)) {
            $hookPotential = null;
        }

        return $hookPotential;
    }

    /**
     * Get a label
     */
    public static function getLl(string $key): string
    {
        return Utility::getLanguageService()->sL('LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:' . $key);
    }

    /**
     * Executes a select based on input query parts array
     *
     * @param array $queryParts Query parts array
     * @return bool|\mysqli_result|object MySQLi result object / DBAL object
     */
    public static function exec_SELECT_queryArray($queryParts)
    {
        return self::exec_SELECTquery($queryParts['SELECT'], $queryParts['FROM'], $queryParts['WHERE'], $queryParts['GROUPBY'], $queryParts['ORDERBY'], $queryParts['LIMIT']);
    }

    /**
     * Executes a select based on input query parts array
     *
     * @param array $queryParts Query parts array
     * @return array
     */
    public static function exec_SELECT_queryArrayRows($queryParts)
    {
        return self::exec_SELECTgetRows($queryParts['SELECT'], $queryParts['FROM'], $queryParts['WHERE'], $queryParts['GROUPBY'], $queryParts['ORDERBY'], $queryParts['LIMIT']);
    }

    /**
     * Creates and executes a SELECT SQL-statement AND traverse result set and returns array with records in.
     *
     * @param string $select_fields List of fields to select from the table. This is what comes right after "SELECT ...". Required value.
     * @param string $from_table Table(s) from which to select. This is what comes right after "FROM ...". Required value.
     * @param string $where_clause Additional WHERE clauses put in the end of the query. NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself! DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
     * @param string $groupBy Optional GROUP BY field(s), if none, supply blank string.
     * @param string $orderBy Optional ORDER BY field(s), if none, supply blank string.
     * @param string $limit Optional LIMIT value ([begin,]max), if none, supply blank string.
     * @param string $uidIndexField If set, the result array will carry this field names value as index. Requires that field to be selected of course!
     * @return array
     */
    public static function exec_SELECTgetRows($select_fields, $from_table, $where_clause, $groupBy = '', $orderBy = '', $limit = '', $uidIndexField = '')
    {
        $res = self::exec_SELECTquery($select_fields, $from_table, $where_clause, $groupBy, $orderBy, $limit);
        return $res->fetchAll();
    }

    /**
     * Creates and executes a SELECT SQL-statement
     *
     * @param string $select_fields List of fields to select from the table. This is what comes right after "SELECT ...". Required value.
     * @param string $from_table Table(s) from which to select. This is what comes right after "FROM ...". Required value.
     * @param string $where_clause Additional WHERE clauses put in the end of the query. NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself! DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
     * @param string $groupBy Optional GROUP BY field(s), if none, supply blank string.
     * @param string $orderBy Optional ORDER BY field(s), if none, supply blank string.
     * @param string $limit Optional LIMIT value ([begin,]max), if none, supply blank string.
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public static function exec_SELECTquery($select_fields, $from_table, $where_clause, $groupBy = '', $orderBy = '', $limit = '')
    {
        $query = self::SELECTquery($select_fields, $from_table, $where_clause, $groupBy, $orderBy, $limit);
        return self::sql_query($query);
    }

    /**
     * Executes query
     *
     * @param string $query Query to execute
     * @return \Doctrine\DBAL\Driver\ResultStatement
     */
    public static function sql_query($query)
    {
        $queryBuilder = self::getQueryBuilder();
        return $queryBuilder->getConnection()->executeQuery($query);
    }

    /**
     * Creates a SELECT SQL-statement
     *
     * @param string $select_fields See exec_SELECTquery()
     * @param string $from_table See exec_SELECTquery()
     * @param string $where_clause See exec_SELECTquery()
     * @param string $groupBy See exec_SELECTquery()
     * @param string $orderBy See exec_SELECTquery()
     * @param string $limit See exec_SELECTquery()
     * @return string Full SQL query for SELECT
     */
    public static function SELECTquery($select_fields, $from_table, $where_clause, $groupBy = '', $orderBy = '', $limit = '')
    {
        // Table and fieldnames should be "SQL-injection-safe" when supplied to this function
        // Build basic query
        $query = 'SELECT ' . $select_fields . ' FROM ' . $from_table . ((string)$where_clause !== '' ? ' WHERE ' . $where_clause : '');
        // Group by
        $query .= (string)$groupBy !== '' ? ' GROUP BY ' . $groupBy : '';
        // Order by
        $query .= (string)$orderBy !== '' ? ' ORDER BY ' . $orderBy : '';
        // Group by
        $query .= (string)$limit !== '' ? ' LIMIT ' . $limit : '';
        return $query;
    }

    public static function getDatabaseConnection(): Connection
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
    }

    public static function getQueryBuilder(): QueryBuilder
    {
        return self::getDatabaseConnection()->createQueryBuilder();
    }

    public static function getLanguageService(): LanguageService
    {
        // fe
        if (!empty($GLOBALS['TSFE'])) {
            return $GLOBALS['TSFE'];
        }
        // be
        if (!empty($GLOBALS['LANG'])) {
            return $GLOBALS['LANG'];
        }
        $LANG = GeneralUtility::makeInstance(LanguageService::class);
        $LANG->init($GLOBALS['BE_USER']->uc['lang']);
        return $LANG;
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
            $itemsPerPage = 10;
            $paginator = new ArrayPaginator($items, $currentPage, $itemsPerPage);
            $pagination = new SimplePagination($paginator);
            $pagination->generate();
            $view->assign('paginator', $paginator);
            $view->assign('pagination', $pagination);
        }
    }

}
