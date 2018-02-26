<?php

namespace Sng\AdditionalReports;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
        $reports = [
            ['Eid', 'eid'],
            ['CommandControllers', 'commandcontrollers'],
            ['Plugins', 'plugins'],
            ['Xclass', 'xclass'],
            ['Hooks', 'hooks'],
            ['Status', 'status'],
            ['LogErrors', 'logerrors'],
            ['WebsiteConf', 'websitesconf'],
            ['DbCheck', 'dbcheck'],
            ['Extensions', 'extensions']
        ];
        return $reports;
    }

    /**
     * Get base url of the report (use to generate links)
     *
     * @return string url
     */
    public static function getBaseUrl()
    {
        // since 6.0> extbase is using by reports module
        $baseUrl = BackendUtility::getModuleUrl(\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('M')) . '&';
        $parameters = array();
        $vars = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('tx_reports_system_reportstxreportsm1');
        //$parameters[] = 'M=system_ReportsTxreportsm1';
        $parameters[] = 'tx_reports_system_reportstxreportsm1%5Bextension%5D=additional_reports';
        $parameters[] = 'tx_reports_system_reportstxreportsm1%5Breport%5D=' . $vars['report'];
        $parameters[] = 'tx_reports_system_reportstxreportsm1%5Baction%5D=detail';
        $parameters[] = 'tx_reports_system_reportstxreportsm1%5Bcontroller%5D=Report';
        //$parameters[] = 'moduleToken=' . \TYPO3\CMS\Core\FormProtection\FormProtectionFactory::get()->generateToken('moduleCall', 'system_ReportsTxreportsm1');
        return $baseUrl . implode('&', $parameters);
    }

    /**
     * Define all the sub modules
     *
     * @return array
     */
    public static function getSubModules()
    {
        return array(
            'displayAjax'          => $GLOBALS['LANG']->getLL('ajax_title'),
            'displayEid'           => $GLOBALS['LANG']->getLL('eid_title'),
            'displayCliKeys'       => $GLOBALS['LANG']->getLL('clikeys_title'),
            'displayPlugins'       => $GLOBALS['LANG']->getLL('plugins_title'),
            'displayXclass'        => $GLOBALS['LANG']->getLL('xclass_title'),
            'displayHooks'         => $GLOBALS['LANG']->getLL('hooks_title'),
            'displayStatus'        => $GLOBALS['LANG']->getLL('status_title'),
            'displayExtensions'    => $GLOBALS['LANG']->getLL('extensions_title'),
            'displayRealUrlErrors' => $GLOBALS['LANG']->getLL('realurlerrors_title'),
            'displayLogErrors'     => $GLOBALS['LANG']->getLL('logerrors_title'),
            'displayWebsitesConf'  => $GLOBALS['LANG']->getLL('websitesconf_title'),
            'displayDbCheck'       => $GLOBALS['LANG']->getLL('dbcheck_title'),
        );
    }

    /**
     * Generates a list of Page-uid's from $id
     *
     * @param  int    $id
     * @param  int    $depth
     * @param  int    $begin
     * @param  string $permsClause
     * @return string
     */
    public static function getTreeList($id, $depth, $begin = 0, $permsClause = '1=1')
    {
        $depth = intval($depth);
        $begin = intval($begin);
        $id = intval($id);
        if ($begin == 0) {
            $theList = $id;
        } else {
            $theList = '';
        }
        if ($id && $depth > 0) {
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'pages', 'pid=' . $id . ' ' . \TYPO3\CMS\Backend\Utility\BackendUtility::deleteClause('pages') . ' AND ' . $permsClause);
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                if ($begin <= 0) {
                    $theList .= ',' . $row['uid'];
                }
                if ($depth > 1) {
                    $theList .= self::getTreeList($row['uid'], $depth - 1, $begin - 1, $permsClause);
                }
            }
            $GLOBALS['TYPO3_DB']->sql_free_result($res);
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
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'pages', 'uid IN (' . $listOfUids . ') AND ' . $where);
        $count = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        return $count;
    }

    /**
     * Check if a content is used by TemplaVoila
     *
     * @param int $uid
     * @param int $pid
     * @return bool
     */
    public static function isUsedInTv($uid, $pid)
    {
        $apiObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_templavoila_api', 'pages');
        $rootElementRecord = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecordWSOL('pages', $pid, '*');
        $contentTreeData = $apiObj->getContentTree('pages', $rootElementRecord);
        $usedUids = array_keys($contentTreeData['contentElementUsage']);
        if (\TYPO3\CMS\Core\Utility\GeneralUtility::inList(implode(',', $usedUids), $uid)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns an integer from a three part version number, eg '4.12.3' -> 4012003
     *
     * @param    string $verNumberStr number on format x.x.x
     * @return   integer   Integer version of version number (where each part can count to 999)
     */
    public static function intFromVer($verNumberStr)
    {
        $verParts = explode('.', $verNumberStr);
        return intval((int)$verParts[0] . str_pad((int)$verParts[1], 3, '0', STR_PAD_LEFT) . str_pad((int)$verParts[2], 3, '0', STR_PAD_LEFT));
    }

    /**
     * Splits a version range into an array.
     *
     * If a single version number is given, it is considered a minimum value.
     * If a dash is found, the numbers left and right are considered as minimum and maximum. Empty values are allowed.
     *
     * @param    string $ver A string with a version range.
     * @return   array
     */
    public static function splitVersionRange($ver)
    {
        $versionRange = array();
        if (strstr($ver, '-')) {
            $versionRange = explode('-', $ver, 2);
        } else {
            $versionRange[0] = $ver;
            $versionRange[1] = '';
        }
        if (!$versionRange[0]) {
            $versionRange[0] = '0.0.0';
        }
        if (!$versionRange[1]) {
            $versionRange[1] = '0.0.0';
        }
        return $versionRange;
    }

    /**
     * Gathers all extensions in $path
     *
     * @param    string $path     Absolute path to local, global or system extensions
     * @param    array  $dbSchema array with all the tables
     * @return    array        "Returns" content by reference
     */
    public static function getInstExtList($path, $dbSchema)
    {
        $list = array();
        if (@is_dir($path)) {
            $extList = \TYPO3\CMS\Core\Utility\GeneralUtility::get_dirs($path);
            if (is_array($extList)) {
                foreach ($extList as $extKey) {
                    if (@is_file($path . $extKey . '/ext_emconf.php')) {
                        $emConf = self::includeEMCONF($path . $extKey . '/ext_emconf.php', $extKey);
                        if (is_array($emConf)) {
                            $currentExt = array();
                            $currentExt['extkey'] = $extKey;
                            $currentExt['installed'] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($extKey);
                            $currentExt['EM_CONF'] = $emConf;
                            $currentExt['files'] = \TYPO3\CMS\Core\Utility\GeneralUtility::getFilesInDir($path . $extKey, '', 0, '', null);
                            $currentExt['lastversion'] = \Sng\AdditionalReports\Utility::checkExtensionUpdate($currentExt);
                            $currentExt['affectedfiles'] = \Sng\AdditionalReports\Utility::getExtAffectedFiles($currentExt);
                            $currentExt['icon'] = \Sng\AdditionalReports\Utility::getExtIcon($extKey);

                            // db infos
                            $fdFile = array();
                            $updateStatements = array();
                            \Sng\AdditionalReports\Utility::getExtSqlUpdateStatements($currentExt, $dbSchema, $fdFile, $updateStatements);
                            $currentExt['fdfile'] = $fdFile;
                            $currentExt['updatestatements'] = $updateStatements;

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
     * @param    string $path    Absolute path to EMCONF file.
     * @param    string $_EXTKEY Extension key.
     * @return    array        EMconf array values.
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
     * @return array        EMconf array values.
     */
    public static function checkExtensionUpdate($extInfo)
    {
        $lastVersion = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tx_extensionmanager_domain_model_extension', 'extension_key="' . $extInfo['extkey'] . '" AND current_version=1');
        if ($lastVersion) {
            $lastVersion[0]['updatedate'] = date('d/m/Y', $lastVersion[0]['last_updated']);
            return $lastVersion[0];
        }
        return null;
    }

    /**
     * Compares two arrays with MD5-hash values for analysis of which files has changed.
     *
     * @param    array $current Current values
     * @param    array $past    Past values
     * @return    array        Affected files
     */
    public static function findMD5ArrayDiff($current, $past)
    {
        if (!is_array($current)) {
            $current = array();
        }
        if (!is_array($past)) {
            $past = array();
        }
        $filesInCommon = array_intersect($current, $past);
        $diff1 = array_keys(array_diff($past, $filesInCommon));
        $diff2 = array_keys(array_diff($current, $filesInCommon));
        $affectedFiles = array_unique(array_merge($diff1, $diff2));
        return $affectedFiles;
    }

    /**
     * Get all all files and md5 to check modified files
     *
     * @param array $extInfo
     * @return array
     */
    public static function getFilesMDArray($extInfo)
    {
        $filesMD5Array = array();
        $fileArr = array();
        $extPath = self::typePath($extInfo['type']) . $extInfo['extkey'] . '/';
        $fileArr = \TYPO3\CMS\Core\Utility\GeneralUtility::getAllFilesAndFoldersInPath($fileArr, $extPath, '', 0, 99, $GLOBALS['TYPO3_CONF_VARS']['EXT']['excludeForPackaging']);
        foreach ($fileArr as $file) {
            $relFileName = substr($file, strlen($extPath));
            if ($relFileName != 'ext_emconf.php') {
                $content = \TYPO3\CMS\Core\Utility\GeneralUtility::getUrl($file);
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
        $content = \TYPO3\CMS\Core\Utility\GeneralUtility::getURL($from);
        $t3xfiles = self::extractExtensionDataFromT3x($content);
        $filesMD5Array = array();
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
        } elseif ($type === 'G') {
            return PATH_typo3 . 'ext/';
        } elseif ($type === 'L') {
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
        return \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . $path;
    }

    /**
     * Get the HTTP icon path of an extension
     *
     * @param string $extKey
     * @return string
     */
    public static function getContentTypeIcon($path)
    {
        $icon = null;
        if (is_file(PATH_site . 'typo3/sysext/t3skin/icons/gfx/' . $path)) {
            $icon = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/' . $path;
        } elseif (preg_match('/^\.\./', $path, $temp)) {
            $icon = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR') . $path;
        } elseif (preg_match('/^EXT:(.*)$/', $path, $temp)) {
            $icon = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR') . '../typo3conf/ext/' . $temp[1];
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
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconFactory::class)->getIcon(
            'actions-version-workspace-preview',
            \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL
        )->render();
    }

    /**
     * Get the icon path of refresh icon
     *
     * @return string
     */
    public static function getIconRefresh()
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconFactory::class)->getIcon(
            'actions-system-refresh',
            \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL
        )->render();
    }

    /**
     * Get the icon path of zoom icon
     *
     * @return string
     */
    public static function getIconDomain()
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconFactory::class)->getIcon(
            'apps-pagetree-page-domain',
            \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL
        )->render();
    }

    /**
     * Get the icon path of web page icon
     *
     * @return string
     */
    public static function getIconWebPage()
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconFactory::class)->getIcon(
            'actions-version-page-open',
            \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL
        )->render();
    }

    /**
     * Get the icon path of template
     *
     * @return string
     */
    public static function getIconTemplate()
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconFactory::class)->getIcon(
            'mimetypes-x-content-template',
            \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL
        )->render();
    }

    /**
     * Get the icon path of web list icon
     *
     * @return string
     */
    public static function getIconWebList()
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconFactory::class)->getIcon(
            'actions-system-list-open',
            \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL
        )->render();
    }

    /**
     * Get the icon path of page icon
     *
     * @param boolean $hidden
     * @return string
     */
    public static function getIconPage($hidden = false)
    {
        if ($hidden === true) {
            return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconFactory::class)->getIcon(
                'apps-pagetree-page-default',
                \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL,
                'overlay-hidden'
            )->render();
        } else {
            return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconFactory::class)->getIcon(
                'apps-pagetree-page-default',
                \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL
            )->render();
        }
    }

    /**
     * Get the icon path of content icon
     *
     * @param boolean $hidden
     * @return string
     */
    public static function getIconContent($hidden = false)
    {
        if ($hidden === true) {
            return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconFactory::class)->getIcon(
                'content-text',
                \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL,
                'overlay-hidden'
            )->render();
        } else {
            return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconFactory::class)->getIcon(
                'content-text',
                \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL
            )->render();
        }
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
            return array(
                'type'         => 'L',
                'siteRelPath'  => 'typo3conf/ext/' . $extKey . '/',
                'typo3RelPath' => '../typo3conf/ext/' . $extKey . '/'
            );
        } elseif (@is_dir(PATH_typo3 . 'ext/' . $extKey . '/')) {
            return array(
                'type'         => 'G',
                'siteRelPath'  => TYPO3_mainDir . 'ext/' . $extKey . '/',
                'typo3RelPath' => 'ext/' . $extKey . '/'
            );
        } elseif (@is_dir(PATH_typo3 . 'sysext/' . $extKey . '/')) {
            return array(
                'type'         => 'S',
                'siteRelPath'  => TYPO3_mainDir . 'sysext/' . $extKey . '/',
                'typo3RelPath' => 'sysext/' . $extKey . '/'
            );
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
        $sysPage = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
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
        $domain = \TYPO3\CMS\Backend\Utility\BackendUtility::firstDomainRecord(self::getRootLine($pageUid));
        if (empty($domain)) {
            $domain = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY');
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
        if ($typePath) {
            $path = $typePath . ($returnWithoutExtKey ? '' : $extKey . '/');
            return $path;
        } else {
            return '';
        }
    }

    /**
     * Get the update statement of the database
     *
     * @return array
     */
    public static function getSqlUpdateStatements()
    {
        $tblFileContent = \TYPO3\CMS\Core\Utility\GeneralUtility::getUrl(PATH_typo3 . 'sysext/core/ext_tables.sql');

        foreach ($GLOBALS['TYPO3_LOADED_EXT'] as $loadedExtConf) {
            if (is_array($loadedExtConf) && $loadedExtConf['ext_tables.sql']) {
                $tblFileContent .= chr(10) . chr(10) . chr(10) . chr(10) . \TYPO3\CMS\Core\Utility\GeneralUtility::getUrl($loadedExtConf['ext_tables.sql']);
            }
        }

        if (class_exists('\TYPO3\CMS\Core\Category\CategoryRegistry') && version_compare(TYPO3_version, '7.6.0', '>=')) {
            $tblFileContent .= \TYPO3\CMS\Core\Category\CategoryRegistry::getInstance()->getDatabaseTableDefinitions();
            $tableDefinitions = \TYPO3\CMS\Core\Category\CategoryRegistry::getInstance()->addCategoryDatabaseSchemaToTablesDefinition(array());
            $tblFileContent .= $tableDefinitions['sqlString '];
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            $cachingFrameworkDatabaseSchemaService = $objectManager->get('TYPO3\CMS\Core\Cache\DatabaseSchemaService');
            $tableDefinitionsCache = $cachingFrameworkDatabaseSchemaService->addCachingFrameworkRequiredDatabaseSchemaForSqlExpectedSchemaService(array());
            $tblFileContent .= implode(LF, $tableDefinitionsCache[0]);
        } else {
            $tblFileContent .= \TYPO3\CMS\Core\Cache\Cache::getDatabaseTableDefinitions();
        }

        $installClass = self::getInstallSqlClass();
        $instObj = new $installClass();
        $fdDb = self::getDatabaseSchema();

        if ($tblFileContent) {
            $fileContent = implode(chr(10), $instObj->getStatementArray($tblFileContent, 1, '^CREATE TABLE '));

            // just support for old version
            if (method_exists($installClass, 'getFieldDefinitions_fileContent') === true) {
                $fdFile = $instObj->getFieldDefinitions_fileContent($fileContent);
            } else {
                $fdFile = $instObj->getFieldDefinitions_sqlContent($fileContent);
            }

            $diff = $instObj->getDatabaseExtra($fdFile, $fdDb);
            $updateStatements = $instObj->getUpdateSuggestions($diff);
            $diff = $instObj->getDatabaseExtra($fdDb, $fdFile);
            $removeStatements = $instObj->getUpdateSuggestions($diff, 'remove');

            return array(
                'update' => $updateStatements,
                'remove' => $removeStatements
            );
        } else {
            return array(
                'update' => null,
                'remove' => null
            );
        }
    }

    /**
     * Get the sql statements of an extension define in ext_tables.sql
     *
     * @param string $extInfo
     * @param array  $dbSchema
     * @param array  &$fdFile
     * @param array  &$updateStatements
     */
    public static function getExtSqlUpdateStatements($extInfo, $dbSchema, &$fdFile, &$updateStatements)
    {
        $installClass = self::getInstallSqlClass();

        if (is_array($extInfo['files']) && in_array('ext_tables.sql', $extInfo['files'])) {
            $fileContent = \TYPO3\CMS\Core\Utility\GeneralUtility::getUrl(self::getExtPath($extInfo['extkey'], $extInfo['type']) . 'ext_tables.sql');
        }

        $instObj = new $installClass();

        // just support for old version < 4.5
        if (method_exists($installClass, 'getFieldDefinitions_fileContent') === true) {
            $fdFile = $instObj->getFieldDefinitions_fileContent($fileContent);
        } else {
            $fdFile = $instObj->getFieldDefinitions_sqlContent($fileContent);
        }

        $diff = $instObj->getDatabaseExtra($fdFile, $dbSchema);
        $updateStatements = $instObj->getUpdateSuggestions($diff);
    }

    /**
     * Get the install class name (for compatibility)
     *
     * @return string
     */
    public static function getInstallSqlClass()
    {
        $installClass = 'TYPO3\\CMS\\Install\\Service\\SqlSchemaMigrationService';
        return $installClass;
    }

    /**
     * Get the entire database schema
     *
     * @return array
     */
    public static function getDatabaseSchema()
    {
        $installClass = self::getInstallSqlClass();
        $instObj = new $installClass();
        return $instObj->getFieldDefinitions_database(TYPO3_db);
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
            if (count($arrayIn) == 0) {
                $result .= '<tr><td><strong>EMPTY!</strong></td></tr>';
            } else {
                foreach ($arrayIn as $key => $val) {
                    $result .= '<tr><td>' . htmlspecialchars((string)$key) . '</td><td class="debugvar">';
                    if (is_array($val)) {
                        $result .= self::viewArray($val);
                    } elseif (is_object($val)) {
                        $string = get_class($val);
                        if (method_exists($val, '__toString')) {
                            $string .= ': ' . (string)$val;
                        }
                        $result .= nl2br(htmlspecialchars($string)) . '<br />';
                    } else {
                        if (gettype($val) == 'object') {
                            $string = 'Unknown object';
                        } else {
                            $string = (string)$val;
                        }
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
        if ($urlOnly === true) {
            return $url;
        } else {
            return 'top.nextLoadModuleUrl=\'' . $url . '\';top.goToModule(\'web_list\');';
        }
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
        if ($urlOnly === true) {
            return $url;
        } else {
            return 'top.nextLoadModuleUrl=\'' . $url . '\';top.goToModule(\'web_layout\');';
        }
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
        $url = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR') . '../typo3conf/ext/templavoila/mod1/index.php?id=' . $uid;
        if ($urlOnly === true) {
            return $url;
        } else {
            return 'top.nextLoadModuleUrl=\'' . $url . '\';top.goToModule(\'web_txtemplavoilaM1\');';
        }
    }

    /**
     * Return a <a...>...</a> code
     *
     * @param array  $att
     * @param string $content
     * @return string
     */
    public static function generateLink($att = array(), $content = '')
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
     * @throws InvalidArgumentException
     */
    public static function getExtensionVersion($key)
    {
        $EM_CONF = array();
        if (!is_string($key) || empty($key)) {
            throw new InvalidArgumentException('Extension key must be a non-empty string.');
        }
        if (!\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($key)) {
            return null;
        }

        // need for the next include
        $_EXTKEY = $key;
        include(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($key) . 'ext_emconf.php');

        return $EM_CONF[$key]['version'];
    }

    /**
     * Get the name of the temp_CACHED files
     *
     * @return string
     */
    public static function getCacheFilePrefix()
    {
        $extensionCacheBehaviour = intval($GLOBALS['TYPO3_CONF_VARS']['EXT']['extCache']);

        // Caching of extensions is disabled when install tool is used:
        if (defined('TYPO3_enterInstallScript') && TYPO3_enterInstallScript) {
            $extensionCacheBehaviour = 0;
        }

        $cacheFileSuffix = (TYPO3_MODE == 'FE' ? '_FE' : '');
        $cacheFilePrefix = 'temp_CACHED' . $cacheFileSuffix;

        if ($extensionCacheBehaviour == 1) {
            $cacheFilePrefix .= '_ps' . substr(\TYPO3\CMS\Core\Utility\GeneralUtility::shortMD5(PATH_site . '|' . $GLOBALS['TYPO_VERSION']), 0, 4);
        }

        return $cacheFilePrefix;
    }

    /**
     * Get informations about the mysql cache
     *
     * @return string HTML code
     */
    public static function getMySqlCacheInformations()
    {
        $queryCache = '';

        $res = $GLOBALS['TYPO3_DB']->sql_query('SHOW VARIABLES LIKE "%query_cache%";');
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $queryCache .= $row['Variable_name'] . ' : ' . $row['Value'] . '<br />';
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);

        $res = $GLOBALS['TYPO3_DB']->sql_query('SHOW STATUS LIKE "%Qcache%";');
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $queryCache .= $row['Variable_name'] . ' : ' . $row['Value'] . '<br />';
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);

        return $queryCache;
    }

    /**
     * Get informations about the mysql character_set
     *
     * @return string HTML code
     */
    public static function getMySqlCharacterSet()
    {
        $sqlEncoding = '';

        $res = $GLOBALS['TYPO3_DB']->sql_query('SHOW VARIABLES LIKE "%character%";');
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $sqlEncoding .= $row['Variable_name'] . ' : ' . $row['Value'] . '<br />';
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);

        return $sqlEncoding;
    }

    /**
     * Generate a special formated div (with icon)
     *
     * @param   string $label
     * @param   string $value
     * @return  string HTML code
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
     * @return string HTML code
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
     * @return string HTML code
     */
    public static function writePopUp($divId, $title, $hideContent)
    {
        if (version_compare(TYPO3_version, '7.6.0', '>=')) {
            $js = 'Shadowbox.open({content:\'<div>\'+TYPO3.jQuery(this).next().html()';
        } else {
            $js = 'Shadowbox.open({content:\'<div>\'+$(\'#' . $divId . '\').innerHTML';
        }
        $js .= '+\'</div>\',player:\'html\',title:\'' . $title . '\',height:600,width:800});';
        $content = '<input type="button" onclick="' . $js . '" value="+"/>';
        $content .= '<div style="display:none;" id="' . $divId . '">' . $hideContent . '</div>';
        return $content;
    }

    /**
     * Get all the different plugins
     *
     * @param string $where
     * @return array
     */
    public static function getAllDifferentPlugins($where)
    {
        return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
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
     * @param boolean $displayHidden
     * @return array
     */
    public static function getAllDifferentPluginsSelect($displayHidden)
    {
        $where = ($displayHidden === true) ? '' : ' AND tt_content.hidden=0 AND pages.hidden=0 ';
        $getFiltersCat = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('filtersCat');
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
        return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
            'DISTINCT tt_content.CType',
            'tt_content,pages',
            'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.deleted=0 AND pages.deleted=0 ' . $where . 'AND tt_content.CType<>\'list\'',
            '',
            'tt_content.list_type'
        );
    }

    /**
     * Get all the different ctypes (html select)
     *
     * @param boolean $displayHidden
     * @return array
     */
    public static function getAllDifferentCtypesSelect($displayHidden)
    {
        $where = ($displayHidden === true) ? '' : ' AND tt_content.hidden=0 AND pages.hidden=0 ';
        $getFiltersCat = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('filtersCat');
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
        $query = array(
            'SELECT'  => 'DISTINCT tt_content.list_type,tt_content.pid,tt_content.uid,pages.title,pages.hidden as "hiddenpages",tt_content.hidden as "hiddentt_content"',
            'FROM'    => 'tt_content,pages',
            'WHERE'   => 'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.deleted=0 AND pages.deleted=0 ' . $where . 'AND tt_content.CType=\'list\'',
            'ORDERBY' => 'tt_content.list_type,tt_content.pid',
            'LIMIT'   => $limit
        );
        if ($returnQuery === true) {
            return $query;
        } else {
            return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
                $query['SELECT'],
                $query['FROM'],
                $query['WHERE'],
                '',
                $query['ORDERBY'],
                $query['LIMIT']
            );
        }
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
        $query = array(
            'SELECT'  => 'DISTINCT tt_content.CType,tt_content.pid,tt_content.uid,pages.title,pages.hidden as "hiddenpages",tt_content.hidden as "hiddentt_content"',
            'FROM'    => 'tt_content,pages',
            'WHERE'   => 'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.deleted=0 AND pages.deleted=0 ' . $where . 'AND tt_content.CType<>\'list\'',
            'ORDERBY' => 'tt_content.CType,tt_content.pid',
            'LIMIT'   => $limit
        );
        if ($returnQuery === true) {
            return $query;
        } else {
            return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
                $query['SELECT'],
                $query['FROM'],
                $query['WHERE'],
                '',
                $query['ORDERBY'],
                $query['LIMIT']
            );
        }
    }

    /**
     * Return a php array of autoload classes
     *
     * @param string $identifier
     * @return mixed|null
     */
    public static function getAutoloadXlassFile($identifier)
    {
        $file = PATH_site . 'typo3temp/Cache/Code/cache_phpcode/' . $identifier . '.php';
        if (is_file($file)) {
            return require($file);
        } else {
            return null;
        }
    }

    /**
     * Return all the XCLASS from autoload class
     *
     * @return array|null
     */
    public static function getAutoloadXlass()
    {
        $identifier = 'autoload_' . sha1(TYPO3_version . PATH_site . 'autoload');
        $classes = self::getAutoloadXlassFile($identifier);
        if ($classes === null) {
            return null;
        }
        $xclass = array();
        foreach ($classes as $class => $file) {
            if ((substr($class, 0, 3) === 'ux_') && ($file !== null)) {
                $xclass[$class] = $file;
            }
        }
        return $xclass;
    }

    /**
     * Return an array with all versions infos
     *
     * @return array
     */
    public static function getJsonVersionInfos()
    {
        return json_decode(\TYPO3\CMS\Core\Utility\GeneralUtility::getUrl('http://get.typo3.org/json'), true);
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
        if (intval($currentVersion[0]) >= 7) {
            return $jsonVersions[$currentVersion[0]]['releases'][$version];
        } else {
            return $jsonVersions[$currentVersion[0] . '.' . $currentVersion[1]]['releases'][$version];
        }
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
        if (intval($currentVersion[0]) >= 7) {
            return @reset($jsonVersions[$currentVersion[0]]['releases']);
        } else {
            return @reset($jsonVersions[$currentVersion[0] . '.' . $currentVersion[1]]['releases']);
        }
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
        if (intval($currentVersion[0]) >= 7) {
            return $jsonVersions[$currentVersion[0]]['releases'][$jsonVersions['latest_stable']];
        } else {
            return $jsonVersions[$currentVersion[0] . '.' . $currentVersion[1]]['releases'][$jsonVersions['latest_stable']];
        }
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
        if (intval($currentVersion[0]) >= 7) {
            return $jsonVersions[$currentVersion[0]]['releases'][$jsonVersions['latest_lts']];
        } else {
            return $jsonVersions[$currentVersion[0] . '.' . $currentVersion[1]]['releases'][$jsonVersions['latest_lts']];
        }
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
        $display = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('display');
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
        $content = \TYPO3\CMS\Core\Utility\GeneralUtility::getURL($from);
        $t3xfiles = self::extractExtensionDataFromT3x($content);
        if (empty($extFile)) {
            return $t3xfiles;
        } else {
            return $t3xfiles['FILES'][$extFile]['content'];
        }
    }

    /**
     * Extract a t3x file
     *
     * @param $content
     * @return array
     * @throws Exception
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
            } else {
                throw new \Exception('Error: Content could not be unserialized to an array. Strange (since MD5 hashes match!)');
            }
        } else {
            throw new \Exception('Error: MD5 mismatch. Maybe the extension file was downloaded and saved as a text file by the browser and thereby corrupted!? (Always select "All" filetype when saving extensions)');
        }
    }

    /**
     * Init a fake TSFE
     *
     * @param $id
     */
    public static function initTSFE($id)
    {

        if (!is_object($GLOBALS['TT'])) {
            $GLOBALS['TT'] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('t3lib_TimeTrackNull');
        }

        $GLOBALS['TSFE'] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tslib_fe', $GLOBALS['TYPO3_CONF_VARS'], $id, '');
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
     * @return boolean
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
            if ($hook[0] == '&') {
                $hook = substr($hook, 1);
            }
            //Check class exists
            if (class_exists($hook)) {
                $isHook = true;
            } //Check if namespace and class exists
            else {
                if (strpos($hook, "\\") !== false && class_exists($hook)) {
                    $isHook = true;
                } //Check if file.php is used
                else {
                    if (strpos($hook, ".php") !== false) {
                        $hookArray = explode(".php", $hook);
                        if (!empty($hookArray) && is_array($hookArray)) {
                            $file = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($hookArray[0] . ".php");
                            if (file_exists($file)) {
                                $isHook = true;
                            }
                        }
                    }
                }
            }
            //Check if function is used
            if ($isHook === false && strpos($hook, "->") !== false) {
                $hookArray = explode("->", $hook);
                if (!empty($hookArray) && is_array($hookArray)) {
                    if (class_exists($hookArray[0])) {
                        $isHook = true;
                    }
                }
            }
        }
        return $isHook;
    }

    /**
     * Get the string from potential array and test it
     *
     * @param string|array $hookPotential
     * @return null|array
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
                        } else {
                            if (self::isHook($valueSecond) === false) {
                                unset($value[$keySecond]);
                            }
                        }
                    }
                } else {
                    if (self::isHook($value) === false) {
                        $value = null;
                    }
                }

                if (empty($value)) {
                    unset($hookPotential[$key]);
                } else {
                    $hookPotential[$key] = $value;
                }
            }
        } else {
            if (self::isHook($hookPotential) === false) {
                $hookPotential = null;
            }
        }

        return $hookPotential;
    }

}

?>
