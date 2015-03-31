<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 CERDAN Yohann <cerdanyohann@yahoo.fr>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * This class provides methods to generate the reports
 *
 * @author         CERDAN Yohann <cerdanyohann@yahoo.fr>
 * @package        TYPO3
 */
class tx_additionalreports_main {
    /**
     * Get the global css path
     *
     * @return string
     */
    public static function getCss() {
        return t3lib_extMgm::extRelPath('additional_reports') . 'Resources/Public/Css/tx_additionalreports.css';
    }

    public static function getLl($key) {
        return $GLOBALS['LANG']->getLL($key);
    }

    /**
     * Generate the xclass report
     *
     * @return string HTML code
     */
    public static function displayXclass() {
        $xclassList = array(
            'BE' => $GLOBALS['TYPO3_CONF_VARS']['BE']['XCLASS'],
            'FE' => $GLOBALS['TYPO3_CONF_VARS']['FE']['XCLASS']
        );

        if (tx_additionalreports_util::intFromVer(TYPO3_version) >= 6000000) {
            $xclassList['autoload'] = tx_additionalreports_util::getAutoloadXlass();
        }

        $view = t3lib_div::makeInstance('Tx_Fluid_View_StandaloneView');
        $view->setTemplatePathAndFilename(t3lib_extMgm::extPath('additional_reports') . 'Resources/Private/Templates/xclass-fluid.html');
        $view->assign('xclass', $xclassList);
        $view->assign('typo3version', tx_additionalreports_util::intFromVer(TYPO3_version));
        return $view->render();
    }

    /**
     * Generate the ajax report
     *
     * @return string HTML code
     */
    public static function displayAjax() {
        $view = t3lib_div::makeInstance('Tx_Fluid_View_StandaloneView');
        $view->setTemplatePathAndFilename(t3lib_extMgm::extPath('additional_reports') . 'Resources/Private/Templates/ajax-fluid.html');
        $view->assign('ajax', $GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']);
        return $view->render();
    }

    /**
     * Generate the cli keys report
     *
     * @return string HTML code
     */
    public static function displayCliKeys() {
        $items = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys'];
        $clikeys = array();

        if (count($items) > 0) {
            foreach ($items as $itemKey => $itemValue) {
                preg_match('/EXT:(.*?)\//', $itemValue[0], $ext);
                $clikeys[] = array(
                    'icon'      => tx_additionalreports_util::getExtIcon($ext[1]),
                    'extension' => $ext[1],
                    'name'      => $itemKey,
                    'path'      => $itemValue[0],
                    'user'      => $itemValue[1]
                );
            }
        }

        $view = t3lib_div::makeInstance('Tx_Fluid_View_StandaloneView');
        $view->setTemplatePathAndFilename(t3lib_extMgm::extPath('additional_reports') . 'Resources/Private/Templates/clikeys-fluid.html');
        $view->assign('clikeys', $clikeys);
        return $view->render();
    }

    /**
     * Generate the eid report
     *
     * @return string HTML code
     */
    public static function displayEid() {
        $items = $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include'];
        $eids = array();

        if (count($items) > 0) {
            foreach ($items as $itemKey => $itemValue) {
                preg_match('/EXT:(.*?)\//', $itemValue, $ext);
                if (t3lib_extMgm::isLoaded($ext[1])) {
                    $eids[] = array(
                        'icon'      => tx_additionalreports_util::getExtIcon($ext[1]),
                        'extension' => $ext[1],
                        'name'      => $itemKey,
                        'path'      => $itemValue
                    );
                }
            }
        }

        $view = t3lib_div::makeInstance('Tx_Fluid_View_StandaloneView');
        $view->setTemplatePathAndFilename(t3lib_extMgm::extPath('additional_reports') . 'Resources/Private/Templates/eid-fluid.html');
        $view->assign('eids', $eids);
        return $view->render();
    }

    /**
     * Generate the ext direct report
     *
     * @return string HTML code
     */
    public static function displayExtDirect() {
        $items = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ExtDirect'];
        $extdirects = array();

        if (count($items) > 0) {
            foreach ($items as $itemKey => $itemValue) {
                $extdirects[] = array(
                    'name' => $itemKey,
                    'path' => tx_additionalreports_util::viewArray($itemValue)
                );
            }
        }

        $view = t3lib_div::makeInstance('Tx_Fluid_View_StandaloneView');
        $view->setTemplatePathAndFilename(t3lib_extMgm::extPath('additional_reports') . 'Resources/Private/Templates/extdirect-fluid.html');
        $view->assign('extdirects', $extdirects);
        return $view->render();
    }

    /**
     * Generate the loaded extension report
     *
     * @return string HTML code
     */
    public static function displayExtensions() {
        $extensionsToUpdate = 0;
        $extensionsModified = 0;

        $dbSchema = tx_additionalreports_util::getDatabaseSchema();
        $allExtension = tx_additionalreports_util::getInstExtList(PATH_typo3conf . 'ext/', $dbSchema);

        $listExtensionsTer = array();
        $listExtensionsDev = array();
        $listExtensionsUnloaded = array();

        if (count($allExtension['ter']) > 0) {
            foreach ($allExtension['ter'] as $extKey => $itemValue) {
                $currentExtension = self::getExtensionInformations($itemValue);
                if (version_compare($itemValue['EM_CONF']['version'], $itemValue['lastversion']['version'], '<')) {
                    $extensionsToUpdate++;
                }
                if (count($itemValue['affectedfiles']) > 0) {
                    $extensionsModified++;
                }
                $listExtensionsTer[] = $currentExtension;
            }
        }

        if (count($allExtension['dev']) > 0) {
            foreach ($allExtension['dev'] as $extKey => $itemValue) {
                $listExtensionsDev[] = self::getExtensionInformations($itemValue);
            }
        }

        if (count($allExtension['unloaded']) > 0) {
            foreach ($allExtension['unloaded'] as $extKey => $itemValue) {
                $listExtensionsUnloaded[] = self::getExtensionInformations($itemValue);
            }
        }

        $addContent = '';
        $addContent .= (count($allExtension['ter']) + count($allExtension['dev'])) . ' ' . self::getLl('extensions_extensions');
        $addContent .= '<br/>';
        $addContent .= count($allExtension['ter']) . ' ' . self::getLl('extensions_ter');
        $addContent .= '  /  ';
        $addContent .= count($allExtension['dev']) . ' ' . self::getLl('extensions_dev');
        $addContent .= '<br/>';
        $addContent .= $extensionsToUpdate . ' ' . self::getLl('extensions_toupdate');
        $addContent .= '  /  ';
        $addContent .= $extensionsModified . ' ' . self::getLl('extensions_extensionsmodified');
        $addContentItem = tx_additionalreports_util::writeInformation(self::getLl('pluginsmode5') . '<br/>' . self::getLl('extensions_updateter') . '', $addContent);

        $view = t3lib_div::makeInstance('Tx_Fluid_View_StandaloneView');
        $view->setTemplatePathAndFilename(t3lib_extMgm::extPath('additional_reports') . 'Resources/Private/Templates/extensions-fluid.html');
        $view->assign('listExtensionsTer', $listExtensionsTer);
        $view->assign('listExtensionsDev', $listExtensionsDev);
        $view->assign('listExtensionsUnloaded', $listExtensionsUnloaded);
        return $addContentItem . $view->render();
    }

    /**
     * Get all necessary informations about an ext
     *
     * @param array $itemValue
     * @return array
     */
    public static function getExtensionInformations($itemValue) {
        $extKey = $itemValue['extkey'];
        $listExtensionsTerItem = array();
        $listExtensionsTerItem['icon'] = $itemValue['icon'];
        $listExtensionsTerItem['extension'] = $extKey;
        $listExtensionsTerItem['extensionlink'] = '<a href="#" onclick="' . tx_additionalreports_util::goToModuleEm($extKey) . '">' . tx_additionalreports_util::getIconZoom() . '</a>';
        $listExtensionsTerItem['version'] = $itemValue['EM_CONF']['version'];
        $listExtensionsTerItem['versioncheck'] = tx_additionalreports_util::versionCompare($itemValue['EM_CONF']['constraints']['depends']['typo3']);

        // version compare
        $compareUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
        $compareUrl .= 'typo3/ajax.php?ajaxID=additional_reports::compareFiles';
        $compareUrl .= '&extKey=' . $extKey . '&mode=compareExtension&extVersion=' . $itemValue['EM_CONF']['version'];
        $compareLabem = $extKey . ' : ' . $itemValue['EM_CONF']['version'] . ' <--> TER ' . $itemValue['EM_CONF']['version'];
        $js = 'Shadowbox.open({content:\'' . $compareUrl . '\',player:\'iframe\',title:\'' . $compareLabem . '\',height:600,width:800});';
        $listExtensionsTerItem['versioncompare'] = '<input type="button" onclick="' . $js . '" value="'.self::getLl('comparesame').'" title="' . $compareLabem . '"/>';

        // need extension update ?
        if (version_compare($itemValue['EM_CONF']['version'], $itemValue['lastversion']['version'], '<')) {
            $listExtensionsTerItem['versionlast'] = '<span style="color:green;font-weight:bold;">' . $itemValue['lastversion']['version'] . '&nbsp;(' . $itemValue['lastversion']['updatedate'] . ')</span>';
            $compareUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
            $compareUrl .= 'typo3/ajax.php?ajaxID=additional_reports::compareFiles';
            $compareUrl .= '&extKey=' . $extKey . '&mode=compareExtension&extVersion=' . $itemValue['lastversion']['version'];
            $compareLabem = $extKey . ' : ' . $itemValue['EM_CONF']['version'] . ' <--> TER ' . $itemValue['lastversion']['version'];
            $js = 'Shadowbox.open({content:\'' . $compareUrl . '\',player:\'iframe\',title:\'' . $compareLabem . '\',height:600,width:800});';
            $listExtensionsTerItem['versioncompare'] .= ' <input type="button" onclick="' . $js . '" value="'.self::getLl('comparelast').'" title="' . $compareLabem . '"/>';
        } else {
            $listExtensionsTerItem['versionlast'] = $itemValue['lastversion']['version'] . '&nbsp;(' . $itemValue['lastversion']['updatedate'] . ')';
        }

        $listExtensionsTerItem['downloads'] = $itemValue['lastversion']['alldownloadcounter'];

        // show db
        $dumpTf1 = '';
        $dumpTf2 = '';
        if (count($itemValue['fdfile']) > 0) {
            $id = 'sql' . $extKey;
            $dumpTf1 = count($itemValue['fdfile']) . ' ' . self::getLl('extensions_tablesmodified');
            $dumpTf2 = tx_additionalreports_util::writePopUp($id, $extKey, tx_additionalreports_util::viewArray($itemValue['fdfile']));
        }
        $listExtensionsTerItem['tables'] = $dumpTf1;
        $listExtensionsTerItem['tableslink'] = $dumpTf2;

        // need db update
        if (count($itemValue['updatestatements']) > 0) {
            $listExtensionsTerItem['tablesintegrity'] = self::getLl('yes');
        } else {
            $listExtensionsTerItem['tablesintegrity'] = self::getLl('no');
        }

        // need extconf update
        $absPath = tx_additionalreports_util::getExtPath($extKey, $itemValue['type']);
        if (is_file($absPath . 'ext_conf_template.txt')) {
            $configTemplate = t3lib_div::getUrl($absPath . 'ext_conf_template.txt');
            /** @var $tsparserObj t3lib_TSparser */
            $tsparserObj = t3lib_div::makeInstance('t3lib_TSparser');
            $tsparserObj->parse($configTemplate);
            $arr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$extKey]);
            $arr = is_array($arr) ? $arr : array();
            $diffConf = array_diff_key($tsparserObj->setup, $arr);
            if (isset($diffConf['updateMessage'])) {
                unset($diffConf['updateMessage']);
            }
            if (count($diffConf) > 0) {
                $id = 'extconf' . $extKey;
                $datas = '<span style="color:white;">Diff : </span>' . tx_additionalreports_util::viewArray($diffConf);
                $datas .= '<span style="color:white;">$GLOBALS[\'TYPO3_CONF_VARS\'][\'EXT\'][\'extConf\'][\'' . $extKey . '\'] : </span>';
                $datas .= tx_additionalreports_util::viewArray($arr);
                $datas .= '<span style="color:white;">ext_conf_template.txt : </span>';
                $datas .= tx_additionalreports_util::viewArray($tsparserObj->setup);
                $dumpExtConf = tx_additionalreports_util::writePopUp($id, $extKey, $datas);
                $listExtensionsTerItem['confintegrity'] = self::getLl('yes') . '&nbsp;&nbsp;' . $dumpExtConf;
            } else {
                $listExtensionsTerItem['confintegrity'] = self::getLl('no');
            }
        } else {
            $listExtensionsTerItem['confintegrity'] = self::getLl('no');
        }

        // modified files
        if (count($itemValue['affectedfiles']) > 0) {
            $id = 'files' . $extKey;
            $contentUl = '<div style="display:none;" id="' . $id . '"><ul>';
            foreach ($itemValue['affectedfiles'] as $affectedFile) {
                $compareUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
                $compareUrl .= 'typo3/ajax.php?ajaxID=additional_reports::compareFiles';
                $compareUrl .= '&extKey=' . $extKey . '&extFile=' . $affectedFile . '&extVersion=' . $itemValue['EM_CONF']['version'];
                $contentUl .= '<li><a rel="shadowbox;height=600;width=800;" href = "' . $compareUrl . '" target = "_blank"';
                $contentUl .= 'title="' . $affectedFile . ' : ' . $extKey . ' ' . $itemValue['EM_CONF']['version'] . '" > ';
                $contentUl .= $affectedFile . '</a></li>';
            }
            $contentUl .= '</ul>';
            $contentUl .= '</div>';
            $listExtensionsTerItem['files'] = count($itemValue['affectedfiles']) . ' ' . self::getLl('extensions_filesmodified') . $contentUl;
            $listExtensionsTerItem['fileslink'] = '<input type="button" onclick="$(\'' . $id . '\').toggle();" value="+"/>';
        } else {
            $listExtensionsTerItem['files'] = '&nbsp;';
            $listExtensionsTerItem['fileslink'] = '&nbsp;';
        }

        return $listExtensionsTerItem;
    }

    /**
     * Generate the hooks report
     *
     * @return string HTML code
     */
    public static function displayHooks() {
        $hooks = array();

        // core hooks
        $items = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'];
        if (count($items) > 0) {
            foreach ($items as $itemKey => $itemValue) {
                if (preg_match('/.*?\/.*?\.php/', $itemKey, $matches)) {
                    foreach ($itemValue as $hookName => $hookList) {
                        $hookList = tx_additionalreports_util::getHook($hookList);
                        if (!empty($hookList)) {
                            $hooks['core'][] = array(
                                'corefile' => $itemKey,
                                'name'     => $hookName,
                                'file'     => tx_additionalreports_util::viewArray($hookList)
                            );
                        }
                    }
                }
            }
        }

        $items = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];
        if (count($items) > 0) {
            foreach ($items as $itemKey => $itemValue) {
                foreach ($itemValue as $hookName => $hookList) {
                    $hookList = tx_additionalreports_util::getHook($hookList);
                    if (!empty($hookList)) {
                        $hooks['extensions'][] = array(
                            'corefile' => $itemKey,
                            'name'     => $hookName,
                            'file'     => tx_additionalreports_util::viewArray($hookList)
                        );
                    }
                }
            }
        }

        $view = t3lib_div::makeInstance('Tx_Fluid_View_StandaloneView');
        $view->setTemplatePathAndFilename(t3lib_extMgm::extPath('additional_reports') . 'Resources/Private/Templates/hooks-fluid.html');
        $view->assign('hooks', $hooks);
        return $view->render();
    }

    /**
     * Generate the global status report
     *
     * @return string HTML code
     */
    public static function displayStatus() {
        $view = t3lib_div::makeInstance('Tx_Fluid_View_StandaloneView');
        $view->setTemplatePathAndFilename(t3lib_extMgm::extPath('additional_reports') . 'Resources/Private/Templates/status-fluid.html');

        // infos about typo3 versions
        $jsonVersions = tx_additionalreports_util::getJsonVersionInfos();
        $currentVersionInfos = tx_additionalreports_util::getCurrentVersionInfos($jsonVersions, TYPO3_version);
        $currentBranch = tx_additionalreports_util::getCurrentBranchInfos($jsonVersions, TYPO3_version);
        $latestStable = tx_additionalreports_util::getLatestStableInfos($jsonVersions);
        $latestLts = tx_additionalreports_util::getLatestLtsInfos($jsonVersions);
        $headerVersions = self::getLl('status_version') . '<br/>';
        $headerVersions .= self::getLl('latestbranch') . '<br/>';
        $headerVersions .= self::getLl('lateststable') . '<br/>';
        $headerVersions .= self::getLl('latestlts');
        $htmlVersions = TYPO3_version . ' [' . $currentVersionInfos['date'] . ']';
        $htmlVersions .= '<br/>' . $currentBranch['version'] . ' [' . $currentBranch['date'] . ']';
        $htmlVersions .= '<br/>' . $latestStable['version'] . ' [' . $latestStable['date'] . ']';
        $htmlVersions .= '<br/>' . $latestLts['version'] . ' [' . $latestLts['date'] . ']';

        // TYPO3
        $content = tx_additionalreports_util::writeInformation(self::getLl('status_sitename'), $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']);
        $content .= tx_additionalreports_util::writeInformation($headerVersions, $htmlVersions);
        $content .= tx_additionalreports_util::writeInformation(self::getLl('status_path'), PATH_site);
        $content .= tx_additionalreports_util::writeInformation('TYPO3_db<br/>TYPO3_db_username<br/>TYPO3_db_host', TYPO3_db . '<br/>' . TYPO3_db_username . '<br/>' . TYPO3_db_host);
        if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path'] != '') {
            $cmd = t3lib_div::imageMagickCommand('convert', '-version');
            exec($cmd, $ret);
            $content .= tx_additionalreports_util::writeInformation(
                self::getLl('status_im'), $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path'] . ' (' . $ret[0] . ')'
            );
        }
        $content .= tx_additionalreports_util::writeInformation('forceCharset', $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset']);
        $content .= tx_additionalreports_util::writeInformation('setDBinit', $GLOBALS['TYPO3_CONF_VARS']['SYS']['setDBinit']);
        $content .= tx_additionalreports_util::writeInformation('no_pconnect', $GLOBALS['TYPO3_CONF_VARS']['SYS']['no_pconnect']);
        $content .= tx_additionalreports_util::writeInformation('displayErrors', $GLOBALS['TYPO3_CONF_VARS']['SYS']['displayErrors']);
        $content .= tx_additionalreports_util::writeInformation('maxFileSize', $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize']);

        $extensions = explode(',', $GLOBALS['TYPO3_CONF_VARS']['EXT']['extList']);

        if (is_file(PATH_site . 'typo3conf/PackageStates.php')) {
            $extensions = array();
            $packages = include(PATH_site . 'typo3conf/PackageStates.php');
            foreach ($packages['packages'] as $extensionKey => $package) {
                if ($package['state'] === 'active') {
                    $extensions[] = $extensionKey;
                }
            }
        }

        sort($extensions);
        foreach ($extensions as $aKey => $extension) {
            $extensions[$aKey] = $extension . ' (' . tx_additionalreports_util::getExtensionVersion($extension) . ')';
        }
        $content .= tx_additionalreports_util::writeInformationList(
            self::getLl('status_loadedextensions'), $extensions
        );

        $view->assign('typo3', $content);

        // Debug
        $content = '';
        $vars = t3lib_div::getIndpEnv('_ARRAY');
        foreach ($vars as $varKey => $varValue) {
            $content .= tx_additionalreports_util::writeInformation($varKey, $varValue);
        }
        $gE_keys = explode(',', 'HTTP_ACCEPT,HTTP_ACCEPT_ENCODING,HTTP_CONNECTION,HTTP_COOKIE,REMOTE_PORT,SERVER_ADDR,SERVER_ADMIN,SERVER_NAME,SERVER_PORT,SERVER_SIGNATURE,SERVER_SOFTWARE,GATEWAY_INTERFACE,SERVER_PROTOCOL,REQUEST_METHOD,PATH_TRANSLATED');
        foreach ($gE_keys as $k) {
            $content .= tx_additionalreports_util::writeInformation($k, getenv($k));
        }
        $view->assign('getIndpEnv', $content);

        // PHP
        $content = tx_additionalreports_util::writeInformation(self::getLl('status_version'), phpversion());
        $content .= tx_additionalreports_util::writeInformation('memory_limit', ini_get('memory_limit'));
        $content .= tx_additionalreports_util::writeInformation('max_execution_time', ini_get('max_execution_time'));
        $content .= tx_additionalreports_util::writeInformation('post_max_size', ini_get('post_max_size'));
        $content .= tx_additionalreports_util::writeInformation('upload_max_filesize', ini_get('upload_max_filesize'));
        $content .= tx_additionalreports_util::writeInformation('display_errors', ini_get('display_errors'));
        $content .= tx_additionalreports_util::writeInformation('error_reporting', ini_get('error_reporting'));
        if (function_exists('posix_getpwuid') && function_exists('posix_getgrgid')) {
            $apacheUser = posix_getpwuid(posix_getuid());
            $apacheGroup = posix_getgrgid(posix_getgid());
            $content .= tx_additionalreports_util::writeInformation(
                'Apache user', $apacheUser['name'] . ' (' . $apacheUser['uid'] . ')'
            );
            $content .= tx_additionalreports_util::writeInformation(
                'Apache group', $apacheGroup['name'] . ' (' . $apacheGroup['gid'] . ')'
            );
        }
        $extensions = array_map('strtolower', get_loaded_extensions());
        natcasesort($extensions);
        $content .= tx_additionalreports_util::writeInformationList(
            self::getLl('status_loadedextensions'), $extensions
        );

        $view->assign('php', $content);

        // Apache
        if (function_exists('apache_get_version') && function_exists('apache_get_modules')) {
            $extensions = apache_get_modules();
            natcasesort($extensions);
            $content = tx_additionalreports_util::writeInformation(
                self::getLl('status_version'), apache_get_version()
            );
            $content .= tx_additionalreports_util::writeInformationList(
                self::getLl('status_loadedextensions'), $extensions
            );
            $view->assign('apache', $content);
        } else {
            $view->assign('apache', self::getLl('noresults'));
        }

        // MySQL
        if (function_exists('mysql_get_server_info')) {
            $content = tx_additionalreports_util::writeInformation('Version', mysql_get_server_info());
        }
        $items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
            'default_character_set_name, default_collation_name',
            'information_schema.schemata',
            'schema_name = \'' . TYPO3_db . '\''
        );
        $content .= tx_additionalreports_util::writeInformation(
            'default_character_set_name', $items[0]['default_character_set_name']
        );
        $content .= tx_additionalreports_util::writeInformation('default_collation_name', $items[0]['default_collation_name']);
        $content .= tx_additionalreports_util::writeInformation('query_cache', tx_additionalreports_util::getMySqlCacheInformations());
        $content .= tx_additionalreports_util::writeInformation('character_set', tx_additionalreports_util::getMySqlCharacterSet());

        // TYPO3 database
        $items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
            'table_name, engine, table_collation, table_rows, ((data_length+index_length)/1024/1024) as "size"',
            'information_schema.tables',
            'table_schema = \'' . TYPO3_db . '\'', '', 'table_name'
        );

        $tables = array();
        $size = 0;

        foreach ($items as $itemValue) {
            $tables[] = array(
                'name'      => $itemValue['table_name'],
                'engine'    => $itemValue['engine'],
                'collation' => $itemValue['table_collation'],
                'rows'      => $itemValue['table_rows'],
                'size'      => round($itemValue['size'], 2),
            );
            $size += round($itemValue['size'], 2);
        }

        $view->assign('mysql', $content);
        $view->assign('tables', $tables);
        $view->assign('tablessize', round($size, 2));
        $view->assign('typo3db', TYPO3_db);

        // Crontab
        exec('crontab -l', $crontab);
        $crontabString = self::getLl('status_nocrontab');
        if (count($crontab) > 0) {
            $crontabString = '';
            foreach ($crontab as $cron) {
                if (trim($cron) != '') {
                    $crontabString .= $cron . '<br />';
                }
            }
        }
        $content = tx_additionalreports_util::writeInformation('Crontab', $crontabString);
        $view->assign('crontab', $content);

        return $view->render();
    }

    /**
     * Generate the plugins and ctypes report
     *
     * @return string HTML code
     */
    public static function displayPlugins() {
        $view = t3lib_div::makeInstance('Tx_Fluid_View_StandaloneView');
        $view->setTemplatePathAndFilename(t3lib_extMgm::extPath('additional_reports') . 'Resources/Private/Templates/plugins-fluid.html');

        $view->assign('extconf', unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['additional_reports']));
        $view->assign('url', tx_additionalreports_util::getBaseUrl());
        $view->assign('caution', tx_additionalreports_util::writeInformation(self::getLl('careful'), self::getLl('carefuldesc')));
        $view->assign('checkedpluginsmode3', (tx_additionalreports_util::getPluginsDisplayMode() == 3) ? ' checked="checked"' : '');
        $view->assign('checkedpluginsmode4', (tx_additionalreports_util::getPluginsDisplayMode() == 4) ? ' checked="checked"' : '');
        $view->assign('checkedpluginsmode5', (tx_additionalreports_util::getPluginsDisplayMode() == 5) ? ' checked="checked"' : '');
        $view->assign('checkedpluginsmode6', (tx_additionalreports_util::getPluginsDisplayMode() == 6) ? ' checked="checked"' : '');
        $view->assign('checkedpluginsmode7', (tx_additionalreports_util::getPluginsDisplayMode() == 7) ? ' checked="checked"' : '');

        switch (tx_additionalreports_util::getPluginsDisplayMode()) {
            case 3 :
                $view->assign('filtersCat', tx_additionalreports_util::getAllDifferentCtypesSelect(FALSE));
                $view->assign('items', self::getAllUsedCtypes());
                break;
            case 4 :
                $view->assign('filtersCat', tx_additionalreports_util::getAllDifferentPluginsSelect(FALSE));
                $view->assign('items', self::getAllUsedPlugins());
                break;
            case 5 :
                $view->assign('items', self::getSummary());
                break;
            case 6 :
                $view->assign('filtersCat', tx_additionalreports_util::getAllDifferentPluginsSelect(TRUE));
                $view->assign('items', self::getAllUsedPlugins(TRUE));
                break;
            case 7 :
                $view->assign('filtersCat', tx_additionalreports_util::getAllDifferentCtypesSelect(TRUE));
                $view->assign('items', self::getAllUsedCtypes(TRUE));
                break;
            default:
                $view->assign('items', self::getSummary());
                break;
        }

        $view->assign('display', tx_additionalreports_util::getPluginsDisplayMode());

        if (t3lib_extMgm::isLoaded('templavoila') && class_exists('tx_templavoila_api')) {
            $view->assign('tvused', TRUE);
        } else {
            $view->assign('tvused', FALSE);
        }

        return $view->render();
    }

    /**
     * Generate the used plugins report
     *
     * @param boolean $displayHidden
     * @return string HTML code
     */
    public static function getAllUsedPlugins($displayHidden = FALSE) {
        $getFiltersCat = t3lib_div::_GP('filtersCat');
        $addhidden = ($displayHidden === TRUE) ? '' : ' AND tt_content.hidden=0 AND pages.hidden=0 ';
        $addWhere = (($getFiltersCat !== NULL) && ($getFiltersCat != 'all')) ? ' AND tt_content.list_type=\'' . $getFiltersCat . '\'' : '';
        return tx_additionalreports_util::getAllPlugins($addhidden . $addWhere, '', TRUE);
    }

    /**
     * Generate the used ctypes    report
     *
     * @param boolean $displayHidden
     * @return string HTML code
     */
    public static function getAllUsedCtypes($displayHidden = FALSE) {
        $getFiltersCat = t3lib_div::_GP('filtersCat');
        $addhidden = ($displayHidden === TRUE) ? '' : ' AND tt_content.hidden=0 AND pages.hidden=0 ';
        $addWhere = (($getFiltersCat !== NULL) && ($getFiltersCat != 'all')) ? ' AND tt_content.CType=\'' . $getFiltersCat . '\'' : '';
        return tx_additionalreports_util::getAllCtypes($addhidden . $addWhere, '', TRUE);
    }

    /**
     * Return informations about a ctype or plugin
     *
     * @param array $itemValue
     * @return array
     */
    public static function getContentInfos($itemValue) {
        $markersExt = array();

        $domain = tx_additionalreports_util::getDomain($itemValue['pid']);
        $markersExt['domain'] = tx_additionalreports_util::getIconDomain() . $domain;

        $iconPage = ($itemValue['hiddenpages'] == 0) ? tx_additionalreports_util::getIconPage() : tx_additionalreports_util::getIconPage(TRUE);
        $iconContent = ($itemValue['hiddentt_content'] == 0) ? tx_additionalreports_util::getIconContent() : tx_additionalreports_util::getIconContent(TRUE);

        $markersExt['pid'] = $iconPage . ' ' . $itemValue['pid'];
        $markersExt['uid'] = $iconContent . ' ' . $itemValue['uid'];
        $markersExt['pagetitle'] = $itemValue['title'];

        if (t3lib_extMgm::isLoaded('templavoila') && class_exists('tx_templavoila_api')) {

            $linkAtt = array('href' => '#', 'title' => self::getLl('switch'), 'onclick' => tx_additionalreports_util::goToModuleList($itemValue['pid']));
            $markersExt['db'] = tx_additionalreports_util::generateLink($linkAtt, tx_additionalreports_util::getIconWebList());

            $linkAtt = array('href' => tx_additionalreports_util::goToModuleList($itemValue['pid'], TRUE), 'target' => '_blank', 'title' => self::getLl('newwindow'));
            $markersExt['db'] .= tx_additionalreports_util::generateLink($linkAtt, tx_additionalreports_util::getIconWebList());

            $linkAtt = array('href' => '#', 'title' => self::getLl('switch'), 'onclick' => tx_additionalreports_util::goToModulePageTv($itemValue['pid']));
            $markersExt['page'] = tx_additionalreports_util::generateLink($linkAtt, tx_additionalreports_util::getIconWebPage());

            $linkAtt = array('href' => tx_additionalreports_util::goToModulePageTv($itemValue['pid'], TRUE), 'target' => '_blank', 'title' => self::getLl('newwindow'));
            $markersExt['page'] .= tx_additionalreports_util::generateLink($linkAtt, tx_additionalreports_util::getIconWebPage());

            if (tx_additionalreports_util::isUsedInTv($itemValue['uid'], $itemValue['pid'])) {
                $markersExt['usedtv'] = self::getLl('yes');
                $markersExt['usedtvclass'] = ' typo3-message message-ok';
            } else {
                $markersExt['usedtv'] = self::getLl('no');
                $markersExt['usedtvclass'] = ' typo3-message message-error';
            }
        } else {
            $markersExt['usedtv'] = '';
            $markersExt['usedtvclass'] = '';

            $linkAtt = array('href' => '#', 'title' => self::getLl('switch'), 'onclick' => tx_additionalreports_util::goToModuleList($itemValue['pid']));
            $markersExt['db'] = tx_additionalreports_util::generateLink($linkAtt, tx_additionalreports_util::getIconWebList());

            $linkAtt = array('href' => tx_additionalreports_util::goToModuleList($itemValue['pid'], TRUE), 'target' => '_blank', 'title' => self::getLl('newwindow'));
            $markersExt['db'] .= tx_additionalreports_util::generateLink($linkAtt, tx_additionalreports_util::getIconWebList());

            $linkAtt = array('href' => '#', 'title' => self::getLl('switch'), 'onclick' => tx_additionalreports_util::goToModulePage($itemValue['pid']));
            $markersExt['page'] = tx_additionalreports_util::generateLink($linkAtt, tx_additionalreports_util::getIconWebPage());

            $linkAtt = array('href' => tx_additionalreports_util::goToModulePage($itemValue['pid'], TRUE), 'target' => '_blank', 'title' => self::getLl('newwindow'));
            $markersExt['page'] .= tx_additionalreports_util::generateLink($linkAtt, tx_additionalreports_util::getIconWebPage());
        }

        $markersExt['preview'] = '<a target="_blank" href="http://' . $domain . '/index.php?id=' . $itemValue['pid'] . '">';
        $markersExt['preview'] .= tx_additionalreports_util::getIconZoom();
        $markersExt['preview'] .= '</a>';

        return $markersExt;
    }

    /**
     * Generate the summary of the plugins and ctypes report
     *
     * @return string HTML code
     */
    public static function getSummary() {

        $plugins = array();
        foreach ($GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'] as $itemKey => $itemValue) {
            if (trim($itemValue[1]) != '') {
                $plugins[$itemValue[1]] = $itemValue;
            }
        }

        $ctypes = array();
        foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $itemKey => $itemValue) {
            if ($itemValue[1] != '--div--') {
                $ctypes[$itemValue[1]] = $itemValue;
            }
        }

        $itemsCount = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
            'COUNT( tt_content.uid ) as "nb"', 'tt_content,pages',
            'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.hidden=0 ' .
            'AND tt_content.deleted=0 AND pages.hidden=0 AND pages.deleted=0'
        );

        $items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
            'tt_content.CType,tt_content.list_type,count(*) as "nb"',
            'tt_content,pages',
            'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.hidden=0 ' .
            'AND tt_content.deleted=0 AND pages.hidden=0 AND pages.deleted=0',
            'tt_content.CType,tt_content.list_type',
            'nb DESC'
        );

        $allItems = array();

        foreach ($items as $itemKey => $itemValue) {
            $itemTemp = array();
            if ($itemValue['CType'] == 'list') {
                preg_match('/EXT:(.*?)\//', $plugins[$itemValue['list_type']][0], $ext);
                preg_match('/^LLL:(EXT:.*?):(.*)/', $plugins[$itemValue['list_type']][0], $llfile);
                $localLang = t3lib_div::readLLfile($llfile[1], $GLOBALS['LANG']->lang);
                if ($plugins[$itemValue['list_type']][2]) {
                    $itemTemp['iconext'] = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . $plugins[$itemValue['list_type']][2];
                } else {
                    $itemTemp['iconext'] = '';
                }
                $itemTemp['content'] = $GLOBALS['LANG']->getLLL($llfile[2], $localLang) . ' (' . $itemValue['list_type'] . ')';
            } else {
                preg_match('/^LLL:(EXT:.*?):(.*)/', $ctypes[$itemValue['CType']][0], $llfile);
                $localLang = t3lib_div::readLLfile($llfile[1], $GLOBALS['LANG']->lang);
                if (is_file(PATH_site . '/typo3/sysext/t3skin/icons/gfx/' . $ctypes[$itemValue['CType']][2])) {
                    $itemTemp['iconext'] = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/' . $ctypes[$itemValue['CType']][2];
                } elseif (preg_match('/^\.\./', $ctypes[$itemValue['CType']][2], $temp)) {
                    $itemTemp['iconext'] = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . $ctypes[$itemValue['CType']][2];
                } elseif (preg_match('/^EXT:(.*)$/', $ctypes[$itemValue['CType']][2], $temp)) {
                    $itemTemp['iconext'] = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . '../typo3conf/ext/' . $temp[1];
                } else {
                    $itemTemp['iconext'] = '';
                }
                $itemTemp['content'] = $GLOBALS['LANG']->getLLL($llfile[2], $localLang) . ' (' . $itemValue['CType'] . ')';
            }
            $itemTemp['references'] = $itemValue['nb'];
            $itemTemp['pourc'] = round((($itemValue['nb'] * 100) / $itemsCount[0]['nb']), 2);
            $allItems[] = $itemTemp;
        }

        return $allItems;
    }

    /**
     * Generate the realurl report
     *
     * @return string HTML code
     */
    public static function displayRealUrlErrors() {
        $cmd = t3lib_div::_GP('cmd');

        if ($cmd === 'deleteAll') {
            $GLOBALS['TYPO3_DB']->exec_DELETEquery(
                'tx_realurl_errorlog',
                ''
            );
        }

        if ($cmd === 'delete') {
            $delete = t3lib_div::_GP('delete');
            $GLOBALS['TYPO3_DB']->exec_DELETEquery(
                'tx_realurl_errorlog',
                'url_hash=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($delete, NULL)
            );
        }

        $query = array(
            'SELECT'  => 'url_hash,url,error,last_referer,counter,cr_date,tstamp',
            'FROM'    => 'tx_realurl_errorlog',
            'ORDERBY' => 'counter DESC'
        );

        $view = t3lib_div::makeInstance('Tx_Fluid_View_StandaloneView');
        $view->setTemplatePathAndFilename(t3lib_extMgm::extPath('additional_reports') . 'Resources/Private/Templates/realurlerrors-fluid.html');
        $view->assign('extconf', unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['additional_reports']));
        $view->assign('baseUrl', tx_additionalreports_util::getBaseUrl());
        $view->assign('requestDir', t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR'));
        $view->assign('query', $query);
        return $view->render();
    }

    /**
     * Generate the log error report
     *
     * @return string HTML code
     */
    public static function displayLogErrors() {

        // query
        $query = array();
        $query['SELECT'] = 'COUNT(*) AS "nb",details,MAX(tstamp) as "tstamp"';
        $query['FROM'] = 'sys_log';
        $query['WHERE'] = 'error>0';
        $query['GROUPBY'] = 'details';
        $query['ORDERBY'] = 'nb DESC,tstamp DESC';
        $query['LIMIT'] = '';

        $orderby = t3lib_div::_GP('orderby');
        if ($orderby !== NULL) {
            $query['ORDERBY'] = $orderby;
        }

        $content = tx_additionalreports_util::writeInformation(
            self::getLl('flushalllog'), 'DELETE FROM sys_log WHERE error>0;'
        );

        $logErrors = array();

        $view = t3lib_div::makeInstance('Tx_Fluid_View_StandaloneView');
        $view->setTemplatePathAndFilename(t3lib_extMgm::extPath('additional_reports') . 'Resources/Private/Templates/logerrors-fluid.html');
        $view->assign('extconf', unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['additional_reports']));
        $view->assign('baseUrl', tx_additionalreports_util::getBaseUrl());
        $view->assign('requestDir', t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR'));
        $view->assign('query', $query);
        return $content . $view->render();
    }

    /**
     * Generate the website conf report
     *
     * @return string HTML code
     */
    public static function displayWebsitesConf() {
        $items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
            'uid, title',
            'pages',
            'is_siteroot = 1 AND deleted = 0 AND hidden = 0 AND pid != -1',
            '', '', '',
            'uid'
        );

        $websiteconf = array();

        if (!empty($items)) {
            foreach ($items as $itemKey => $itemValue) {
                $websiteconfItem = array();

                $domainRecords = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
                    'uid, pid, domainName',
                    'sys_domain',
                    'pid IN(' . $itemValue['uid'] . ') AND hidden=0',
                    '',
                    'sorting'
                );

                $websiteconfItem['pid'] = $itemValue['uid'];
                $websiteconfItem['pagetitle'] = tx_additionalreports_util::getIconPage() . $itemValue['title'];
                $websiteconfItem['domains'] = '';
                $websiteconfItem['template'] = '';

                foreach ($domainRecords as $domain) {
                    $websiteconfItem['domains'] .= tx_additionalreports_util::getIconDomain() . $domain['domainName'] . '<br/>';
                }

                $templates = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
                    'uid,title,root',
                    'sys_template',
                    'pid IN(' . $itemValue['uid'] . ') AND deleted=0 AND hidden=0',
                    '',
                    'sorting'
                );


                foreach ($templates as $templateObj) {
                    $websiteconfItem['template'] .= '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR');
                    $websiteconfItem['template'] .= 'sysext/t3skin/icons/gfx/i/template.gif"/> ' . $templateObj['title'] . ' ';
                    $websiteconfItem['template'] .= '[uid=' . $templateObj['uid'] . ',root=' . $templateObj['root'] . ']<br/>';
                }

                // baseurl
                $tmpl = t3lib_div::makeInstance('t3lib_tsparser_ext');
                $tmpl->tt_track = 0;
                $tmpl->init();
                $tmpl->runThroughTemplates(tx_additionalreports_util::getRootLine($itemValue['uid']), 0);
                $tmpl->generateConfig();
                $websiteconfItem['baseurl'] = $tmpl->setup['config.']['baseURL'];

                // count pages
                $list = tx_additionalreports_util::getTreeList($itemValue['uid'], 99, 0, '1=1');
                $listArray = explode(',', $list);
                $websiteconfItem['pages'] = (count($listArray) - 1);
                $websiteconfItem['pageshidden'] = (tx_additionalreports_util::getCountPagesUids($list, 'hidden=1'));
                $websiteconfItem['pagesnosearch'] = (tx_additionalreports_util::getCountPagesUids($list, 'no_search=1'));

                $websiteconf[] = $websiteconfItem;
            }
        }

        $view = t3lib_div::makeInstance('Tx_Fluid_View_StandaloneView');
        $view->setTemplatePathAndFilename(t3lib_extMgm::extPath('additional_reports') . 'Resources/Private/Templates/websiteconf-fluid.html');
        $view->assign('items', $websiteconf);
        return $view->render();
    }

    /**
     * Generate the dbcheck report
     *
     * @return string HTML code
     */
    public static function displayDbCheck() {
        $sqlStatements = tx_additionalreports_util::getSqlUpdateStatements();
        $dbchecks = array();

        if (!empty($sqlStatements['update']['add'])) {
            $dbchecks[1]['title'] = 'Add fields';
            $dbchecks[1]['items'] = $sqlStatements['update']['add'];
        }

        if (!empty($sqlStatements['update']['change'])) {
            $dbchecks[2]['title'] = 'Changing fields';
            foreach ($sqlStatements['update']['change'] as $itemKey => $itemValue) {
                if (isset($sqlStatements['update']['change_currentValue'][$itemKey])) {
                    $dbchecks[2]['items'][] = $itemValue . ' -- [current: ' . $sqlStatements['update']['change_currentValue'][$itemKey] . ']';
                } else {
                    $dbchecks[2]['items'][] = $itemValue;
                }
            }
        }

        if (!empty($sqlStatements['remove']['change'])) {
            $dbchecks[3]['title'] = 'Remove unused fields (rename with prefix)';
            $dbchecks[3]['items'] = $sqlStatements['remove']['change'];
        }

        if (!empty($sqlStatements['remove']['drop'])) {
            $dbchecks[4]['title'] = 'Drop fields (really!)';
            $dbchecks[4]['items'] = $sqlStatements['remove']['drop'];
        }

        if (!empty($sqlStatements['update']['create_table'])) {
            $dbchecks[5]['title'] = 'Add tables';
            $dbchecks[5]['items'] = $sqlStatements['update']['create_table'];
        }

        if (!empty($sqlStatements['remove']['change_table'])) {
            $dbchecks[6]['title'] = 'Removing tables (rename with prefix)';
            foreach ($sqlStatements['remove']['change_table'] as $itemKey => $itemValue) {
                if (!empty($sqlStatements['remove']['tables_count'][$itemKey])) {
                    $dbchecks[6]['items'][] = $itemValue . ' -- [' . $sqlStatements['remove']['tables_count'][$itemKey] . ']';
                } else {
                    $dbchecks[6]['items'][] = $itemValue . ' -- [empty]';
                }
            }
        }

        if (!empty($sqlStatements['remove']['drop_table'])) {
            $dbchecks[7]['title'] = 'Drop tables (really!)';
            foreach ($sqlStatements['remove']['drop_table'] as $itemKey => $itemValue) {
                if (!empty($sqlStatements['remove']['tables_count'][$itemKey])) {
                    $dbchecks[7]['items'][] = $itemValue . ' -- [' . $sqlStatements['remove']['tables_count'][$itemKey] . ']';
                } else {
                    $dbchecks[7]['items'][] = $itemValue . ' -- [empty]';
                }
            }
        }

        // dump sql structure
        $items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
            'table_name',
            'information_schema.tables',
            'table_schema = \'' . TYPO3_db . '\'', '', 'table_name'
        );

        $sqlStructure = '';

        foreach ($items as $table) {
            $resSqlDump = $GLOBALS['TYPO3_DB']->sql_query('SHOW CREATE TABLE ' . $table['table_name']);
            $sqlDump = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resSqlDump);
            $sqlStructure .= $sqlDump['Create Table'] . "\r\n\r\n";
            $GLOBALS['TYPO3_DB']->sql_free_result($resSqlDump);
        }

        $content = '<h3 class="uppercase">Dump SQL Structure (md5:' . md5($sqlStructure) . ')</h3>';
        $content .= '<textarea style="width:100%;height:200px;">' . $sqlStructure . '</textarea>';

        $view = t3lib_div::makeInstance('Tx_Fluid_View_StandaloneView');
        $view->setTemplatePathAndFilename(t3lib_extMgm::extPath('additional_reports') . 'Resources/Private/Templates/dbcheck-fluid.html');
        $view->assign('dbchecks', $dbchecks);
        return $view->render() . $content;
    }

}

?>