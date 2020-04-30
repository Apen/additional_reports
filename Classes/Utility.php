<?php

namespace Sng\AdditionalReports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Page\PageRepository;

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
            ['Extensions', 'extensions']
        ];
    }

    /**
     * Get base url of the report (use to generate links)
     *
     * @return string url
     */
    public static function getBaseUrl()
    {
        $parameters = [];
        if (version_compare(TYPO3_version, '9.0.0') >= 0) {
            $parameters['extension'] = 'additional_reports';
            $parameters['action'] = 'detail';
            $parameters['report'] = GeneralUtility::_GET('report');
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            $url = $uriBuilder->buildUriFromRoute('system_reports', $parameters);
            return (string)$url;
        }
        $baseUrl = BackendUtility::getModuleUrl(GeneralUtility::_GET('M')) . '&';
        $vars = GeneralUtility::_GET('tx_reports_system_reportstxreportsm1');
        $parameters[] = 'tx_reports_system_reportstxreportsm1%5Bextension%5D=additional_reports';
        $parameters[] = 'tx_reports_system_reportstxreportsm1%5Breport%5D=' . $vars['report'];
        $parameters[] = 'tx_reports_system_reportstxreportsm1%5Baction%5D=detail';
        $parameters[] = 'tx_reports_system_reportstxreportsm1%5Bcontroller%5D=Report';
        return $baseUrl . implode('&', $parameters);
    }

    /**
     * Define all the sub modules
     *
     * @return array
     */
    public static function getSubModules()
    {
        return [
            'displayAjax'         => $GLOBALS['LANG']->getLL('ajax_title'),
            'displayEid'          => $GLOBALS['LANG']->getLL('eid_title'),
            'displayCliKeys'      => $GLOBALS['LANG']->getLL('clikeys_title'),
            'displayPlugins'      => $GLOBALS['LANG']->getLL('plugins_title'),
            'displayXclass'       => $GLOBALS['LANG']->getLL('xclass_title'),
            'displayHooks'        => $GLOBALS['LANG']->getLL('hooks_title'),
            'displayStatus'       => $GLOBALS['LANG']->getLL('status_title'),
            'displayExtensions'   => $GLOBALS['LANG']->getLL('extensions_title'),
            'displayLogErrors'    => $GLOBALS['LANG']->getLL('logerrors_title'),
            'displayWebsitesConf' => $GLOBALS['LANG']->getLL('websitesconf_title')
        ];
    }

    /**
     * Return informations about a ctype or plugin
     *
     * @param array $itemValue
     * @return array
     */
    public static function getContentInfos($itemValue)
    {
        $markersExt = [];

        $domain = \Sng\AdditionalReports\Utility::getDomain($itemValue['pid']);
        $markersExt['domain'] = \Sng\AdditionalReports\Utility::getIconDomain() . $domain;

        $iconPage = ($itemValue['hiddenpages'] == 0) ? \Sng\AdditionalReports\Utility::getIconPage() : \Sng\AdditionalReports\Utility::getIconPage(true);
        $iconContent = ($itemValue['hiddentt_content'] == 0) ? \Sng\AdditionalReports\Utility::getIconContent() : \Sng\AdditionalReports\Utility::getIconContent(true);

        $markersExt['pid'] = $iconPage . ' ' . $itemValue['pid'];
        $markersExt['uid'] = $iconContent . ' ' . $itemValue['uid'];
        $markersExt['pagetitle'] = $itemValue['title'];

        $markersExt['usedtv'] = '';
        $markersExt['usedtvclass'] = '';

        $linkAtt = ['href' => '#', 'title' => \Sng\AdditionalReports\Utility::getLl('switch'), 'onclick' => \Sng\AdditionalReports\Utility::goToModuleList($itemValue['pid']), 'class' => 'btn btn-default'];
        $markersExt['db'] = \Sng\AdditionalReports\Utility::generateLink($linkAtt, \Sng\AdditionalReports\Utility::getIconWebList());

        $linkAtt = ['href' => \Sng\AdditionalReports\Utility::goToModuleList($itemValue['pid'], true), 'target' => '_blank', 'title' => \Sng\AdditionalReports\Utility::getLl('newwindow'), 'class' => 'btn btn-default'];
        $markersExt['db'] .= \Sng\AdditionalReports\Utility::generateLink($linkAtt, \Sng\AdditionalReports\Utility::getIconWebList());

        $linkAtt = ['href' => '#', 'title' => \Sng\AdditionalReports\Utility::getLl('switch'), 'onclick' => \Sng\AdditionalReports\Utility::goToModulePage($itemValue['pid']), 'class' => 'btn btn-default'];
        $markersExt['page'] = \Sng\AdditionalReports\Utility::generateLink($linkAtt, \Sng\AdditionalReports\Utility::getIconWebPage());

        $linkAtt = ['href' => \Sng\AdditionalReports\Utility::goToModulePage($itemValue['pid'], true), 'target' => '_blank', 'title' => \Sng\AdditionalReports\Utility::getLl('newwindow'), 'class' => 'btn btn-default'];
        $markersExt['page'] .= \Sng\AdditionalReports\Utility::generateLink($linkAtt, \Sng\AdditionalReports\Utility::getIconWebPage());

        $markersExt['preview'] = '<a target="_blank" class="btn btn-default" href="http://' . $domain . '/index.php?id=' . $itemValue['pid'] . '">';
        $markersExt['preview'] .= \Sng\AdditionalReports\Utility::getIconZoom();
        $markersExt['preview'] .= '</a>';

        return $markersExt;
    }

    /**
     * Generates a list of Page-uid's from $id
     *
     * @param int    $id
     * @param int    $depth
     * @param int    $begin
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
            $res = self::exec_SELECTquery('uid', 'pages', 'pid=' . $id . ' ' . BackendUtility::deleteClause('pages') . ' AND ' . $permsClause);
            while ($row = $res->fetch()) {
                if ($begin <= 0) {
                    $theList .= ',' . $row['uid'];
                }
                if ($depth > 1) {
                    $theList .= self::getTreeList($row['uid'], $depth - 1, $begin - 1, $permsClause);
                }
            }
            $res->closeCursor();
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
        $res->closeCursor();
        return $count;
    }

    /**
     * Returns an integer from a three part version number, eg '4.12.3' -> 4012003
     *
     * @param string $verNumberStr number on format x.x.x
     * @return int
     */
    public static function intFromVer($verNumberStr)
    {
        $verParts = explode('.', $verNumberStr);
        return (int)((int)$verParts[0] . str_pad((int)$verParts[1], 3, '0', STR_PAD_LEFT) . str_pad((int)$verParts[2], 3, '0', STR_PAD_LEFT));
    }

    /**
     * Splits a version range into an array.
     *
     * @param string $ver A string with a version range.
     * @return array
     */
    public static function splitVersionRange($ver)
    {
        $versionRange = [];
        if (strstr($ver, '-')) {
            $versionRange = explode('-', $ver, 2);
        } else {
            $versionRange[0] = $ver;
            $versionRange[1] = '';
        }
        if ($versionRange[0] === '') {
            $versionRange[0] = '0.0.0';
        }
        if ($versionRange[1] === '') {
            $versionRange[1] = '0.0.0';
        }
        return $versionRange;
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
                            $currentExt['lastversion'] = \Sng\AdditionalReports\Utility::checkExtensionUpdate($currentExt);
                            $currentExt['affectedfiles'] = \Sng\AdditionalReports\Utility::getExtAffectedFiles($currentExt);
                            $currentExt['icon'] = \Sng\AdditionalReports\Utility::getExtIcon($extKey);

                            // db infos
                            $fileContent = '';
                            if (is_array($currentExt['files']) && in_array('ext_tables.sql', $currentExt['files'])) {
                                $fileContent = GeneralUtility::getUrl(self::getExtPath($currentExt['extkey'], $currentExt['type']) . 'ext_tables.sql');
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
     * @param string $path    Absolute path to EMCONF file.
     * @param string $_EXTKEY Extension key.
     * @return array
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
        $lastVersion = \Sng\AdditionalReports\Utility::exec_SELECTgetRows('*', 'tx_extensionmanager_domain_model_extension', 'extension_key="' . $extInfo['extkey'] . '" AND current_version=1');
        if ($lastVersion !== []) {
            $lastVersion[0]['updatedate'] = date('d/m/Y', $lastVersion[0]['last_updated']);
            return $lastVersion[0];
        }
        return null;
    }

    /**
     * Compares two arrays with MD5-hash values for analysis of which files has changed.
     *
     * @param array $current Current values
     * @param array $past    Past values
     * @return array
     */
    public static function findMD5ArrayDiff($current, $past)
    {
        if (!is_array($current)) {
            $current = [];
        }
        if (!is_array($past)) {
            $past = [];
        }
        $filesInCommon = array_intersect($current, $past);
        $diff1 = array_keys(array_diff($past, $filesInCommon));
        $diff2 = array_keys(array_diff($current, $filesInCommon));
        return array_unique(array_merge($diff1, $diff2));
    }

    /**
     * Get all all files and md5 to check modified files
     *
     * @param array $extInfo
     * @return array
     */
    public static function getFilesMDArray($extInfo)
    {
        $filesMD5Array = [];
        $fileArr = [];
        $extPath = self::typePath($extInfo['type']) . $extInfo['extkey'] . '/';
        $fileArr = GeneralUtility::getAllFilesAndFoldersInPath($fileArr, $extPath, '', 0, 99, $GLOBALS['TYPO3_CONF_VARS']['EXT']['excludeForPackaging']);
        foreach ($fileArr as $file) {
            $relFileName = substr($file, strlen($extPath));
            if ($relFileName !== 'ext_emconf.php') {
                $content = GeneralUtility::getUrl($file);
                $filesMD5Array[$relFileName] = substr(md5($content), 0, 4);
            }
        }
        return $filesMD5Array;
    }

    /**
     * Get all all files and md5 to check modified files of the last version
     *
     * @param string $extension
     * @param string $version
     * @return array
     */
    public static function getFilesMDArrayFromT3x($extension, $version)
    {
        $firstLetter = strtolower(substr($extension, 0, 1));
        $secondLetter = strtolower(substr($extension, 1, 1));
        $from = 'http://typo3.org/fileadmin/ter/' . $firstLetter . '/' . $secondLetter . '/' . $extension . '_' . $version . '.t3x';
        $content = GeneralUtility::getURL($from);
        $t3xfiles = self::extractExtensionDataFromT3x($content);
        $filesMD5Array = [];
        foreach ($t3xfiles['FILES'] as $file => $infos) {
            $filesMD5Array[$file] = substr($infos['content_md5'], 0, 4);
        }
        return $filesMD5Array;
    }

    /**
     * Get all modified files
     *
     * @param array $extInfo
     * @return array
     */
    public static function getExtAffectedFiles($extInfo)
    {
        $currentMd5Array = self::getFilesMDArray($extInfo);
        return self::findMD5ArrayDiff($currentMd5Array, unserialize($extInfo['EM_CONF']['_md5_values_when_last_written']));
    }

    /**
     * Get all modified files
     *
     * @param array $extInfo
     * @return array
     */
    public static function getExtAffectedFilesLastVersion($extInfo)
    {
        $currentMd5Array = self::getFilesMDArrayFromT3x($extInfo['extkey'], $extInfo['lastversion']['version']);
        return self::findMD5ArrayDiff($currentMd5Array, unserialize($extInfo['EM_CONF']['_md5_values_when_last_written']));
    }

    /**
     * Get the extension path for a given type
     *
     * @param string $type
     * @return string
     */
    public static function typePath($type)
    {
        if ($type === 'S') {
            return PATH_typo3 . 'sysext/';
        }
        if ($type === 'G') {
            return PATH_typo3 . 'ext/';
        }
        if ($type === 'L') {
            return PATH_typo3conf . 'ext/';
        }
        return PATH_typo3conf . 'ext/';
    }

    /**
     * Get the HTTP icon path of an extension
     *
     * @param string $extKey
     * @return string
     */
    public static function getExtIcon($extKey)
    {
        $extType = self::getExtensionType($extKey);
        $path = $extType['siteRelPath'] . ExtensionManagementUtility::getExtensionIcon(PATH_site . $extType['siteRelPath']);
        return GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $path;
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
        if (is_file(PATH_site . 'typo3/sysext/t3skin/icons/gfx/' . $path)) {
            $icon = GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/' . $path;
        } elseif (preg_match('#^\.\.#', $path, $temp)) {
            $icon = GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR') . $path;
        } elseif (preg_match('#^EXT:(.*)$#', $path, $temp)) {
            $icon = GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR') . '../typo3conf/ext/' . $temp[1];
        }
        return $icon;
    }

    /**
     * Get the icon path of zoom icon
     *
     * @return string
     */
    public static function getIconZoom()
    {
        return GeneralUtility::makeInstance(IconFactory::class)->getIcon(
            'actions-version-workspace-preview',
            Icon::SIZE_SMALL
        )->render();
    }

    /**
     * Get the icon path of refresh icon
     *
     * @return string
     */
    public static function getIconRefresh()
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
    public static function getIconDomain()
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
    public static function getIconWebPage()
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
    public static function getIconTemplate()
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
    public static function getIconWebList()
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
    public static function getIconPage($hidden = false)
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
    public static function getIconContent($hidden = false)
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
    public static function getExtensionType($extKey)
    {
        if (@is_dir(PATH_typo3conf . 'ext/' . $extKey . '/')) {
            return [
                'type'         => 'L',
                'siteRelPath'  => 'typo3conf/ext/' . $extKey . '/',
                'typo3RelPath' => '../typo3conf/ext/' . $extKey . '/'
            ];
        }
        if (@is_dir(PATH_typo3 . 'ext/' . $extKey . '/')) {
            return [
                'type'         => 'G',
                'siteRelPath'  => TYPO3_mainDir . 'ext/' . $extKey . '/',
                'typo3RelPath' => 'ext/' . $extKey . '/'
            ];
        }
        if (@is_dir(PATH_typo3 . 'sysext/' . $extKey . '/')) {
            return [
                'type'         => 'S',
                'siteRelPath'  => TYPO3_mainDir . 'sysext/' . $extKey . '/',
                'typo3RelPath' => 'sysext/' . $extKey . '/'
            ];
        }
        return null;
    }

    /**
     * Get rootline by page uid
     *
     * @param int $pageUid
     * @return mixed
     */
    public static function getRootLine($pageUid)
    {
        $sysPage = GeneralUtility::makeInstance(PageRepository::class);
        return $sysPage->getRootLine($pageUid);
    }

    /**
     * Get principal domain by page uid
     *
     * @param int $pageUid
     * @return mixed
     */
    public static function getDomain($pageUid)
    {
        $domain = BackendUtility::firstDomainRecord(self::getRootLine($pageUid));
        if (empty($domain)) {
            $domain = GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY');
        }
        if (empty($domain)) {
            $domain = 'localhost';
        }
        return $domain;
    }

    /**
     * Get the absolute path of an extension
     *
     * @param string $extKey
     * @param string $type
     * @param bool   $returnWithoutExtKey
     * @return string
     */
    public static function getExtPath($extKey, $type = 'L', $returnWithoutExtKey = false)
    {
        $typePath = self::typePath($type);
        if ($typePath !== '') {
            return $typePath . ($returnWithoutExtKey ? '' : $extKey . '/');
        }
        return '';
    }

    /**
     * Compare 2 versions of an extension
     *
     * @param string $depV
     * @return string
     */
    public static function versionCompare($depV)
    {
        $t3version = TYPO3_version;
        if (stripos($t3version, '-dev') || stripos($t3version, '-alpha') || stripos($t3version, '-beta') || stripos($t3version, '-RC')) {
            // find the last occurence of "-" and replace that part with a ".0"
            $t3version = substr($t3version, 0, strrpos($t3version, '-')) . '.0';
        }

        $status = 0;

        if (isset($depV)) {
            $versionRange = self::splitVersionRange($depV);
            if ($versionRange[0] != '0.0.0' && version_compare($t3version, $versionRange[0], '<')) {
                $msg = sprintf($GLOBALS['LANG']->getLL('checkDependencies_typo3_too_low'), $t3version, $versionRange[0]);
            } elseif ($versionRange[1] != '0.0.0' && version_compare($t3version, $versionRange[1], '>')) {
                $msg = sprintf($GLOBALS['LANG']->getLL('checkDependencies_typo3_too_high'), $t3version, $versionRange[1]);
            } elseif ($versionRange[1] == '0.0.0') {
                $status = 2;
                $msg = $GLOBALS['LANG']->getLL('nottested') . ' (' . $depV . ')';
            } else {
                $status = 1;
                $msg = 'OK';
            }
        } else {
            $status = 3;
            $msg = $GLOBALS['LANG']->getLL('unknown');
        }

        switch ($status) {
            case 0:
                $msg = '<span style="color:red;font-weight:bold;" title="' . $msg . '">KO</span>';
                break;
            case 1:
                $msg = '<span style="color:green;font-weight:bold;" title="' . $msg . '">OK</span>';
                break;
            case 2:
                $msg = '<span style="color:orange;font-weight:bold;" title="' . $msg . '">' . $GLOBALS['LANG']->getLL('nottested') . '</span>';
                break;
            case 3:
                $msg = '<span style="color:orange;font-weight:bold;" title="' . $msg . '">' . $GLOBALS['LANG']->getLL('unknown') . '</span>';
                break;
            default:
                $msg = '<span style="color:red;font-weight:bold;" title="' . $msg . '">KO</span>';
                break;
        }

        return $msg;
    }

    /**
     * Print a debug of an array
     *
     * @param array $arrayIn
     * @return string
     */
    public static function viewArray($arrayIn)
    {
        if (is_array($arrayIn)) {
            $result = '<table class="debug" border="1" cellpadding="0" cellspacing="0" bgcolor="white" width="100%" style="background-color:white;">';
            if (count($arrayIn) === 0) {
                $result .= '<tr><td><strong>EMPTY!</strong></td></tr>';
            } else {
                foreach ($arrayIn as $key => $val) {
                    $result .= '<tr><td>' . htmlspecialchars((string)$key) . '</td><td class="debugvar">';
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
            $result = '<table class="debug" border="0" cellpadding="0" cellspacing="0" bgcolor="white">';
            $result .= '<tr><td class="debugvar">' . nl2br(htmlspecialchars((string)$arrayIn)) . '</td></tr></table>';
        }
        return $result;
    }

    /**
     * Return a link to the module list
     *
     * @param int  $uid
     * @param bool $urlOnly
     * @return string
     */
    public static function goToModuleList($uid, $urlOnly = false)
    {
        $url = BackendUtility::getModuleUrl('web_list') . '&id=' . $uid;
        if ($urlOnly) {
            return $url;
        }
        return "top.nextLoadModuleUrl='" . $url . "';top.goToModule('web_list');";
    }

    /**
     * Return a link to the module page
     *
     * @param int  $uid
     * @param bool $urlOnly
     * @return string
     */
    public static function goToModulePage($uid, $urlOnly = false)
    {
        $url = BackendUtility::getModuleUrl('web_layout') . '&id=' . $uid;
        if ($urlOnly) {
            return $url;
        }
        return "top.nextLoadModuleUrl='" . $url . "';top.goToModule('web_layout');";
    }

    /**
     * Return a link to the module page (with TV)
     *
     * @param int  $uid
     * @param bool $urlOnly
     * @return string
     */
    public static function goToModulePageTv($uid, $urlOnly = false)
    {
        $url = GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR') . '../typo3conf/ext/templavoila/mod1/index.php?id=' . $uid;
        if ($urlOnly) {
            return $url;
        }
        return "top.nextLoadModuleUrl='" . $url . "';top.goToModule('web_txtemplavoilaM1');";
    }

    /**
     * Return a <a...>...</a> code
     *
     * @param array  $att
     * @param string $content
     * @return string
     */
    public static function generateLink($att = [], $content = '')
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
    public static function getExtensionVersion($key)
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

        return $EM_CONF[$key]['version'];
    }

    /**
     * Get informations about the mysql cache
     *
     * @return string
     */
    public static function getMySqlCacheInformations()
    {
        $queryCache = '';

        $res = \Sng\AdditionalReports\Utility::sql_query('SHOW VARIABLES LIKE "%query_cache%";');
        while ($row = $res->fetch()) {
            $queryCache .= $row['Variable_name'] . ' : ' . $row['Value'] . '<br />';
        }
        $res->closeCursor();

        $res = \Sng\AdditionalReports\Utility::sql_query('SHOW STATUS LIKE "%Qcache%";');
        while ($row = $res->fetch()) {
            $queryCache .= $row['Variable_name'] . ' : ' . $row['Value'] . '<br />';
        }
        $res->closeCursor();

        return $queryCache;
    }

    /**
     * Get informations about the mysql character_set
     *
     * @return string
     */
    public static function getMySqlCharacterSet()
    {
        $sqlEncoding = '';

        $res = \Sng\AdditionalReports\Utility::sql_query('SHOW VARIABLES LIKE "%character%";');
        while ($row = $res->fetch()) {
            $sqlEncoding .= $row['Variable_name'] . ' : ' . $row['Value'] . '<br />';
        }
        $res->closeCursor();

        return $sqlEncoding;
    }

    /**
     * Generate a special formated div (with icon)
     *
     * @param string $label
     * @param string $value
     * @return string
     */
    public static function writeInformation($label, $value)
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
     * Generate a formated list
     *
     * @param string $label
     * @param array  $array
     * @return string
     */
    public static function writeInformationList($label, $array)
    {
        $content = '';
        foreach ($array as $value) {
            $content .= '' . $value . '<br/>';
        }
        $content .= '';
        return self::writeInformation($label, $content);
    }

    /**
     * Open a popup with div content
     *
     * @param string $divId
     * @param string $title
     * @param string $hideContent
     * @return string
     */
    public static function writePopUp($divId, $title, $hideContent)
    {
        $js = 'Shadowbox.open({content:\'<div>\'+$(this).next().html()';
        $js .= "+'</div>',player:'html',title:'" . $title . "',height:600,width:800});";
        $content = '<input type="button" onclick="' . $js . '" value="+"/>';
        return $content . ('<pre style="display:none;" id="' . $divId . '"><div  style="color:white;padding:10px;">' . $hideContent . '</div></pre>');
    }

    /**
     * Get all the different plugins
     *
     * @param string $where
     * @return array
     */
    public static function getAllDifferentPlugins($where)
    {
        return \Sng\AdditionalReports\Utility::exec_SELECTgetRows(
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
    public static function getAllDifferentPluginsSelect($displayHidden)
    {
        $where = ($displayHidden) ? '' : ' AND tt_content.hidden=0 AND pages.hidden=0 ';
        $getFiltersCat = GeneralUtility::_GP('filtersCat');
        $pluginsList = self::getAllDifferentPlugins($where);
        $filterCat = '';

        if ($getFiltersCat == 'all') {
            $filterCat .= '<option value="all" selected="selected">' . $GLOBALS['LANG']->getLL('all') . '</option>';
        } else {
            $filterCat .= '<option value="all">' . $GLOBALS['LANG']->getLL('all') . '</option>';
        }

        foreach ($pluginsList as $pluginsElement) {
            if (($getFiltersCat == $pluginsElement['list_type']) && ($getFiltersCat !== null)) {
                $filterCat .= '<option value="' . $pluginsElement['list_type'] . '" selected="selected">';
                $filterCat .= $pluginsElement['list_type'] . '</option>';
            } else {
                $filterCat .= '<option value="' . $pluginsElement['list_type'] . '">' . $pluginsElement['list_type'] . '</option>';
            }
        }

        $listUrlOrig = \Sng\AdditionalReports\Utility::getBaseUrl() . '&display=' . \Sng\AdditionalReports\Utility::getPluginsDisplayMode();

        $content = '<select name="filtersCat" id="filtersCat">' . $filterCat . '</select>';
        $content .= '<a class="btn btn-default" href="#"  onClick="jumpToUrl(\'' . $listUrlOrig;
        $content .= '&filtersCat=\'+document.getElementById(\'filtersCat\').value);">';
        $content .= self::getIconRefresh() . '</a>';

        return $content;
    }

    /**
     * Get all the different ctypes
     *
     * @param string $where
     * @return array
     */
    public static function getAllDifferentCtypes($where)
    {
        return \Sng\AdditionalReports\Utility::exec_SELECTgetRows(
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
    public static function getAllDifferentCtypesSelect($displayHidden)
    {
        $where = ($displayHidden) ? '' : ' AND tt_content.hidden=0 AND pages.hidden=0 ';
        $getFiltersCat = GeneralUtility::_GP('filtersCat');
        $pluginsList = self::getAllDifferentCtypes($where);
        $filterCat = '';

        if ($getFiltersCat == 'all') {
            $filterCat .= '<option value="all" selected="selected">' . $GLOBALS['LANG']->getLL('all') . '</option>';
        } else {
            $filterCat .= '<option value="all">' . $GLOBALS['LANG']->getLL('all') . '</option>';
        }

        foreach ($pluginsList as $pluginsElement) {
            if (($getFiltersCat == $pluginsElement['CType']) && ($getFiltersCat !== null)) {
                $filterCat .= '<option value="' . $pluginsElement['CType'] . '" selected="selected">';
                $filterCat .= $pluginsElement['CType'] . '</option>';
            } else {
                $filterCat .= '<option value="' . $pluginsElement['CType'] . '">' . $pluginsElement['CType'] . '</option>';
            }
        }

        $listUrlOrig = \Sng\AdditionalReports\Utility::getBaseUrl() . '&display=' . \Sng\AdditionalReports\Utility::getPluginsDisplayMode();

        $content = '<select name="filtersCat" id="filtersCat">' . $filterCat . '</select>';
        $content .= '<a class="btn btn-default" href="#"  onClick="jumpToUrl(\'' . $listUrlOrig;
        $content .= '&filtersCat=\'+document.getElementById(\'filtersCat\').value);">';
        $content .= self::getIconRefresh() . '</a>';

        return $content;
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
            'SELECT'  => 'DISTINCT tt_content.list_type,tt_content.pid,tt_content.uid,pages.title,pages.hidden as "hiddenpages",tt_content.hidden as "hiddentt_content"',
            'FROM'    => 'tt_content,pages',
            'WHERE'   => 'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.deleted=0 AND pages.deleted=0 ' . $where . "AND tt_content.CType='list'",
            'ORDERBY' => 'tt_content.list_type,tt_content.pid',
            'LIMIT'   => $limit
        ];
        if ($returnQuery === true) {
            return $query;
        }
        return \Sng\AdditionalReports\Utility::exec_SELECTgetRows(
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
    public static function getAllCtypes($where, $limit = '', $returnQuery = false)
    {
        $query = [
            'SELECT'  => 'DISTINCT tt_content.CType,tt_content.pid,tt_content.uid,pages.title,pages.hidden as "hiddenpages",tt_content.hidden as "hiddentt_content"',
            'FROM'    => 'tt_content,pages',
            'WHERE'   => 'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.deleted=0 AND pages.deleted=0 ' . $where . "AND tt_content.CType<>'list'",
            'ORDERBY' => 'tt_content.CType,tt_content.pid',
            'LIMIT'   => $limit
        ];
        if ($returnQuery === true) {
            return $query;
        }
        return \Sng\AdditionalReports\Utility::exec_SELECTgetRows(
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
        return json_decode(GeneralUtility::getUrl('http://get.typo3.org/json'), true);
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
            return $jsonVersions[$currentVersion[0]]['releases'][$version];
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
     * @return string
     */
    public static function getPluginsDisplayMode()
    {
        $displayMode = null;

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

        return $displayMode;
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
        $from = 'http://typo3.org/fileadmin/ter/' . $firstLetter . '/' . $secondLetter . '/' . $extension . '_' . $version . '.t3x';
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
        if ($parts[1] === 'gzcompress') {
            if (function_exists('gzuncompress')) {
                $parts[2] = gzuncompress($parts[2]);
            } else {
                throw new \Exception('Decoding Error: No decompressor available for compressed content. gzcompress()/gzuncompress() functions are not available!');
            }
        }
        if (md5($parts[2]) == $parts[0]) {
            $output = unserialize($parts[2]);
            if (is_array($output)) {
                return $output;
            }
            throw new \Exception('Error: Content could not be unserialized to an array. Strange (since MD5 hashes match!)');
        }
        throw new \Exception('Error: MD5 mismatch. Maybe the extension file was downloaded and saved as a text file by the browser and thereby corrupted!? (Always select "All" filetype when saving extensions)');
    }

    /**
     * Init a fake TSFE
     *
     * @param $id
     */
    public static function initTSFE($id)
    {
        if (!is_object($GLOBALS['TT'])) {
            $GLOBALS['TT'] = GeneralUtility::makeInstance('t3lib_TimeTrackNull');
        }

        $GLOBALS['TSFE'] = GeneralUtility::makeInstance('tslib_fe', $GLOBALS['TYPO3_CONF_VARS'], $id, '');
        $GLOBALS['TSFE']->connectToDB();
        $GLOBALS['TSFE']->initFEuser();
        //$GLOBALS['TSFE']->checkAlternativeIdMethods();
        $GLOBALS['TSFE']->determineId();
        $GLOBALS['TSFE']->getCompressedTCarray();
        $GLOBALS['TSFE']->initTemplate();
        $GLOBALS['TSFE']->getConfigArray();
    }

    /**
     * Check if string given is hook
     *
     * @param string $hook
     * @return bool
     */
    public static function isHook($hook)
    {
        $isHook = false;
        if (!empty($hook)) {
            // if it's a key-path hook
            if (is_array($hook)) {
                $isHook = self::isHook($hook[1]);
            }
            // classname begin with &
            if ($hook[0] === '&') {
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
        //If is array
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
     *
     * @param string $key
     * @return string
     */
    public static function getLl($key)
    {
        return $GLOBALS['LANG']->sL('LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:' . $key);
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
     * Creates and executes a SELECT SQL-statement AND traverse result set and returns array with records in.
     *
     * @param string $select_fields List of fields to select from the table. This is what comes right after "SELECT ...". Required value.
     * @param string $from_table    Table(s) from which to select. This is what comes right after "FROM ...". Required value.
     * @param string $where_clause  Additional WHERE clauses put in the end of the query. NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself! DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
     * @param string $groupBy       Optional GROUP BY field(s), if none, supply blank string.
     * @param string $orderBy       Optional ORDER BY field(s), if none, supply blank string.
     * @param string $limit         Optional LIMIT value ([begin,]max), if none, supply blank string.
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
     * @param string $from_table    Table(s) from which to select. This is what comes right after "FROM ...". Required value.
     * @param string $where_clause  Additional WHERE clauses put in the end of the query. NOTICE: You must escape values in this argument with $this->fullQuoteStr() yourself! DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
     * @param string $groupBy       Optional GROUP BY field(s), if none, supply blank string.
     * @param string $orderBy       Optional ORDER BY field(s), if none, supply blank string.
     * @param string $limit         Optional LIMIT value ([begin,]max), if none, supply blank string.
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
     * @param string $from_table    See exec_SELECTquery()
     * @param string $where_clause  See exec_SELECTquery()
     * @param string $groupBy       See exec_SELECTquery()
     * @param string $orderBy       See exec_SELECTquery()
     * @param string $limit         See exec_SELECTquery()
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

    /**
     * @return \TYPO3\CMS\Core\Database\Connection
     */
    public static function getDatabaseConnection()
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
    }

    /**
     * @return \TYPO3\CMS\Core\Database\Query\QueryBuilder
     */
    public static function getQueryBuilder()
    {
        return self::getDatabaseConnection()->createQueryBuilder();
    }
}
