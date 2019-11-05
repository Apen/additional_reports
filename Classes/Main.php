<?php

namespace Sng\AdditionalReports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use \TYPO3\CMS\Backend\Routing\Router;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class provides methods to generate the reports
 */
class Main
{
    /**
     * Get the global css path
     *
     * @return string
     */
    public static function getCss()
    {
        return \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Public/Css/tx_additionalreports.css';
    }

    public static function getLl($key)
    {
        return $GLOBALS['LANG']->sL('LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:' . $key);
    }

    /**
     * Generate the xclass report
     *
     * @return string HTML code
     */
    public static function displayXclass()
    {
        $xclassList = array();

        $xclassList['objects'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'];

        $xclassList['autoload'] = \Sng\AdditionalReports\Utility::getAutoloadXlass();

        $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/xclass-fluid.html');
        $view->assign('xclass', $xclassList);
        $view->assign('typo3version', \Sng\AdditionalReports\Utility::intFromVer(TYPO3_version));
        return $view->render();
    }

    /**
     * Generate the CommandControllers report
     *
     * @return string HTML code
     */
    public static function displayCommandControllers()
    {
        $items = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'];
        $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/commandcontrollers-fluid.html');
        $view->assign('items', $items);
        return $view->render();
    }

    /**
     * Generate the eid report
     *
     * @return string HTML code
     */
    public static function displayEid()
    {
        $items = $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include'];
        $eids = array();

        if (count($items) > 0) {
            foreach ($items as $itemKey => $itemValue) {
                preg_match('/EXT:(.*?)\//', $itemValue, $ext);
                if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($ext[1])) {
                    $eids[] = array(
                        'icon'      => \Sng\AdditionalReports\Utility::getExtIcon($ext[1]),
                        'extension' => $ext[1],
                        'name'      => $itemKey,
                        'path'      => $itemValue
                    );
                }
            }
        }

        $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/eid-fluid.html');
        $view->assign('eids', $eids);
        return $view->render();
    }

    /**
     * Generate the ext direct report
     *
     * @return string HTML code
     */
    public static function displayExtDirect()
    {
        $items = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ExtDirect'];
        $extdirects = array();

        if (count($items) > 0) {
            foreach ($items as $itemKey => $itemValue) {
                $extdirects[] = array(
                    'name' => $itemKey,
                    'path' => \Sng\AdditionalReports\Utility::viewArray($itemValue)
                );
            }
        }

        $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/extdirect-fluid.html');
        $view->assign('extdirects', $extdirects);
        return $view->render();
    }

    /**
     * Generate the loaded extension report
     *
     * @return string HTML code
     */
    public static function displayExtensions()
    {
        $extensionsToUpdate = 0;
        $extensionsModified = 0;

        $dbSchema = \Sng\AdditionalReports\Utility::getDatabaseSchema();
        $allExtension = \Sng\AdditionalReports\Utility::getInstExtList(PATH_typo3conf . 'ext/', $dbSchema);

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
        $addContentItem = \Sng\AdditionalReports\Utility::writeInformation(self::getLl('pluginsmode5') . '<br/>' . self::getLl('extensions_updateter') . '', $addContent);

        $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/extensions-fluid.html');
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
    public static function getExtensionInformations($itemValue)
    {
        $extKey = $itemValue['extkey'];
        $listExtensionsTerItem = array();
        $listExtensionsTerItem['icon'] = $itemValue['icon'];
        $listExtensionsTerItem['extension'] = $extKey;
        $listExtensionsTerItem['version'] = $itemValue['EM_CONF']['version'];
        $listExtensionsTerItem['versioncheck'] = \Sng\AdditionalReports\Utility::versionCompare($itemValue['EM_CONF']['constraints']['depends']['typo3']);

        // version compare
        $compareUrl = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL');

        $uriBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Routing\UriBuilder::class);
        $routeIdentifier = 'additional_reports_compareFiles';
        $uri = (string)$uriBuilder->buildUriFromRoute($routeIdentifier, array());

        // Bugfix for wrong CompareUrl in case of TYPO3 is installed in a subdirectory
        if (strpos($uri, 'typo3/index.php') > 0) {
            $uri = substr($uri, strpos($uri, 'typo3/index.php'));
        }

        $compareUrl .= $uri;
        $compareUrl .= '&extKey=' . $extKey . '&mode=compareExtension&extVersion=' . $itemValue['EM_CONF']['version'];
        $compareLabem = $extKey . ' : ' . $itemValue['EM_CONF']['version'] . ' <--> TER ' . $itemValue['EM_CONF']['version'];
        $js = 'Shadowbox.open({content:\'' . $compareUrl . '\',player:\'iframe\',title:\'' . $compareLabem . '\',height:600,width:800});';
        $listExtensionsTerItem['versioncompare'] = '<input type="button" onclick="' . $js . '" value="' . self::getLl('comparesame') . '" title="' . $compareLabem . '"/>';

        // need extension update ?
        if (version_compare($itemValue['EM_CONF']['version'], $itemValue['lastversion']['version'], '<')) {
            $listExtensionsTerItem['versionlast'] = '<span style="color:green;font-weight:bold;">' . $itemValue['lastversion']['version'] . '&nbsp;(' . $itemValue['lastversion']['updatedate'] . ')</span>';
            $compareUrl = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
            $compareUrl .= 'typo3/ajax.php?ajaxID=additional_reports::compareFiles';
            $compareUrl .= '&extKey=' . $extKey . '&mode=compareExtension&extVersion=' . $itemValue['lastversion']['version'];
            $compareLabem = $extKey . ' : ' . $itemValue['EM_CONF']['version'] . ' <--> TER ' . $itemValue['lastversion']['version'];
            $js = 'Shadowbox.open({content:\'' . $compareUrl . '\',player:\'iframe\',title:\'' . $compareLabem . '\',height:600,width:800});';
            $listExtensionsTerItem['versioncompare'] .= ' <input type="button" onclick="' . $js . '" value="' . self::getLl('comparelast') . '" title="' . $compareLabem . '"/>';
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
            $dumpTf2 = \Sng\AdditionalReports\Utility::writePopUp($id, $extKey, \Sng\AdditionalReports\Utility::viewArray($itemValue['fdfile']));
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
        $absPath = \Sng\AdditionalReports\Utility::getExtPath($extKey, $itemValue['type']);
        if (is_file($absPath . 'ext_conf_template.txt')) {
            $configTemplate = \TYPO3\CMS\Core\Utility\GeneralUtility::getUrl($absPath . 'ext_conf_template.txt');
            /** @var $tsparserObj \TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser */
            $tsparserObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\TypoScript\\Parser\\TypoScriptParser');
            $tsparserObj->parse($configTemplate);
            $arr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$extKey]);
            $arr = is_array($arr) ? $arr : array();
            $diffConf = array_diff_key($tsparserObj->setup, $arr);
            if (isset($diffConf['updateMessage'])) {
                unset($diffConf['updateMessage']);
            }
            if (count($diffConf) > 0) {
                $id = 'extconf' . $extKey;
                $datas = '<span style="color:white;">Diff : </span>' . \Sng\AdditionalReports\Utility::viewArray($diffConf);
                $datas .= '<span style="color:white;">$GLOBALS[\'TYPO3_CONF_VARS\'][\'EXT\'][\'extConf\'][\'' . $extKey . '\'] : </span>';
                $datas .= \Sng\AdditionalReports\Utility::viewArray($arr);
                $datas .= '<span style="color:white;">ext_conf_template.txt : </span>';
                $datas .= \Sng\AdditionalReports\Utility::viewArray($tsparserObj->setup);
                $dumpExtConf = \Sng\AdditionalReports\Utility::writePopUp($id, $extKey, $datas);
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
                $compareUrl = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
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
    public static function displayHooks()
    {
        $hooks = array();

        // core hooks
        $items = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'];
        if (count($items) > 0) {
            foreach ($items as $itemKey => $itemValue) {
                if (preg_match('/.*?\/.*?\.php/', $itemKey, $matches)) {
                    foreach ($itemValue as $hookName => $hookList) {
                        $hookList = \Sng\AdditionalReports\Utility::getHook($hookList);
                        if (!empty($hookList)) {
                            $hooks['core'][] = array(
                                'corefile' => $itemKey,
                                'name'     => $hookName,
                                'file'     => \Sng\AdditionalReports\Utility::viewArray($hookList)
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
                    $hookList = \Sng\AdditionalReports\Utility::getHook($hookList);
                    if (!empty($hookList)) {
                        $hooks['extensions'][] = array(
                            'corefile' => $itemKey,
                            'name'     => $hookName,
                            'file'     => \Sng\AdditionalReports\Utility::viewArray($hookList)
                        );
                    }
                }
            }
        }

        $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/hooks-fluid.html');
        $view->assign('hooks', $hooks);
        return $view->render();
    }

    /**
     * Generate the global status report
     *
     * @return string HTML code
     */
    public static function displayStatus()
    {
        $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/status-fluid.html');

        // infos about typo3 versions
        $jsonVersions = \Sng\AdditionalReports\Utility::getJsonVersionInfos();
        $currentVersionInfos = \Sng\AdditionalReports\Utility::getCurrentVersionInfos($jsonVersions, TYPO3_version);
        $currentBranch = \Sng\AdditionalReports\Utility::getCurrentBranchInfos($jsonVersions, TYPO3_version);
        $latestStable = \Sng\AdditionalReports\Utility::getLatestStableInfos($jsonVersions);
        $latestLts = \Sng\AdditionalReports\Utility::getLatestLtsInfos($jsonVersions);
        $headerVersions = self::getLl('status_version') . '<br/>';
        $headerVersions .= self::getLl('latestbranch') . '<br/>';
        $headerVersions .= self::getLl('lateststable') . '<br/>';
        $headerVersions .= self::getLl('latestlts');
        $htmlVersions = TYPO3_version . ' [' . $currentVersionInfos['date'] . ']';
        $htmlVersions .= '<br/>' . $currentBranch['version'] . ' [' . $currentBranch['date'] . ']';
        $htmlVersions .= '<br/>' . $latestStable['version'] . ' [' . $latestStable['date'] . ']';
        $htmlVersions .= '<br/>' . $latestLts['version'] . ' [' . $latestLts['date'] . ']';

        // TYPO3
        $content = \Sng\AdditionalReports\Utility::writeInformation(self::getLl('status_sitename'), $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']);
        $content .= \Sng\AdditionalReports\Utility::writeInformation($headerVersions, $htmlVersions);
        $content .= \Sng\AdditionalReports\Utility::writeInformation(self::getLl('status_path'), PATH_site);
        $content .= \Sng\AdditionalReports\Utility::writeInformation(
            'TYPO3_db<br/>TYPO3_db_username<br/>TYPO3_db_host',
            $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['dbname'] . '<br/>'
            . $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['user'] . '<br/>'
            . $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['host']
        );
        if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path'] != '') {
            $cmd = \TYPO3\CMS\Core\Utility\GeneralUtility::imageMagickCommand('convert', '-version');
            exec($cmd, $ret);
            $content .= \Sng\AdditionalReports\Utility::writeInformation(
                self::getLl('status_im'), $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path'] . ' (' . $ret[0] . ')'
            );
        }
        $content .= \Sng\AdditionalReports\Utility::writeInformation('forceCharset', $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset']);
        $content .= \Sng\AdditionalReports\Utility::writeInformation('setDBinit', $GLOBALS['TYPO3_CONF_VARS']['SYS']['setDBinit']);
        $content .= \Sng\AdditionalReports\Utility::writeInformation('no_pconnect', $GLOBALS['TYPO3_CONF_VARS']['SYS']['no_pconnect']);
        $content .= \Sng\AdditionalReports\Utility::writeInformation('displayErrors', $GLOBALS['TYPO3_CONF_VARS']['SYS']['displayErrors']);
        $content .= \Sng\AdditionalReports\Utility::writeInformation('maxFileSize', $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize']);

        $extensions = explode(',', $GLOBALS['TYPO3_CONF_VARS']['EXT']['extList']);

        if (is_file(PATH_site . 'typo3conf/PackageStates.php')) {
            $extensions = array();
            $packages = include(PATH_site . 'typo3conf/PackageStates.php');
            foreach ($packages['packages'] as $extensionKey => $package) {
                $extensions[] = $extensionKey;
            }
        }

        sort($extensions);
        foreach ($extensions as $aKey => $extension) {
            $extensions[$aKey] = $extension . ' (' . \Sng\AdditionalReports\Utility::getExtensionVersion($extension) . ')';
        }
        $content .= \Sng\AdditionalReports\Utility::writeInformationList(
            self::getLl('status_loadedextensions'), $extensions
        );

        $view->assign('typo3', $content);

        // Debug
        $content = '';
        $vars = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('_ARRAY');
        foreach ($vars as $varKey => $varValue) {
            $content .= \Sng\AdditionalReports\Utility::writeInformation($varKey, $varValue);
        }
        $gE_keys = explode(',', 'HTTP_ACCEPT,HTTP_ACCEPT_ENCODING,HTTP_CONNECTION,HTTP_COOKIE,REMOTE_PORT,SERVER_ADDR,SERVER_ADMIN,SERVER_NAME,SERVER_PORT,SERVER_SIGNATURE,SERVER_SOFTWARE,GATEWAY_INTERFACE,SERVER_PROTOCOL,REQUEST_METHOD,PATH_TRANSLATED');
        foreach ($gE_keys as $k) {
            $content .= \Sng\AdditionalReports\Utility::writeInformation($k, getenv($k));
        }
        $view->assign('getIndpEnv', $content);

        // PHP
        $content = \Sng\AdditionalReports\Utility::writeInformation(self::getLl('status_version'), phpversion());
        $content .= \Sng\AdditionalReports\Utility::writeInformation('memory_limit', ini_get('memory_limit'));
        $content .= \Sng\AdditionalReports\Utility::writeInformation('max_execution_time', ini_get('max_execution_time'));
        $content .= \Sng\AdditionalReports\Utility::writeInformation('post_max_size', ini_get('post_max_size'));
        $content .= \Sng\AdditionalReports\Utility::writeInformation('upload_max_filesize', ini_get('upload_max_filesize'));
        $content .= \Sng\AdditionalReports\Utility::writeInformation('display_errors', ini_get('display_errors'));
        $content .= \Sng\AdditionalReports\Utility::writeInformation('error_reporting', ini_get('error_reporting'));
        if (function_exists('posix_getpwuid') && function_exists('posix_getgrgid')) {
            $apacheUser = posix_getpwuid(posix_getuid());
            $apacheGroup = posix_getgrgid(posix_getgid());
            $content .= \Sng\AdditionalReports\Utility::writeInformation(
                'Apache user', $apacheUser['name'] . ' (' . $apacheUser['uid'] . ')'
            );
            $content .= \Sng\AdditionalReports\Utility::writeInformation(
                'Apache group', $apacheGroup['name'] . ' (' . $apacheGroup['gid'] . ')'
            );
        }
        $extensions = array_map('strtolower', get_loaded_extensions());
        natcasesort($extensions);
        $content .= \Sng\AdditionalReports\Utility::writeInformationList(
            self::getLl('status_loadedextensions'), $extensions
        );

        $view->assign('php', $content);

        // Apache
        if (function_exists('apache_get_version') && function_exists('apache_get_modules')) {
            $extensions = apache_get_modules();
            natcasesort($extensions);
            $content = \Sng\AdditionalReports\Utility::writeInformation(
                self::getLl('status_version'), apache_get_version()
            );
            $content .= \Sng\AdditionalReports\Utility::writeInformationList(
                self::getLl('status_loadedextensions'), $extensions
            );
            $view->assign('apache', $content);
        } else {
            $view->assign('apache', self::getLl('noresults'));
        }

        $connection = self::getDatabaseConnection();
        $connectionParams = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections'][\TYPO3\CMS\Core\Database\ConnectionPool::DEFAULT_CONNECTION_NAME];

        // MySQL
        $content = \Sng\AdditionalReports\Utility::writeInformation('Version', $connection->getServerVersion());

        $items = self::getQueryBuilder()
            ->select('default_character_set_name', 'default_collation_name')
            ->from('information_schema.schemata')
            ->where('schema_name = \'' . $connectionParams['dbname'] . '\'')
            ->execute()
            ->fetchAll();

        $content .= \Sng\AdditionalReports\Utility::writeInformation(
            'default_character_set_name', $items[0]['default_character_set_name']
        );
        $content .= \Sng\AdditionalReports\Utility::writeInformation('default_collation_name', $items[0]['default_collation_name']);
        $content .= \Sng\AdditionalReports\Utility::writeInformation('query_cache', \Sng\AdditionalReports\Utility::getMySqlCacheInformations());
        $content .= \Sng\AdditionalReports\Utility::writeInformation('character_set', \Sng\AdditionalReports\Utility::getMySqlCharacterSet());

        // TYPO3 database
        $items = self::getQueryBuilder()
            ->select('table_name', 'engine', 'table_collation', 'table_rows')
            ->add('select', '((data_length+index_length)/1024/1024) as "size"', true)
            ->from('information_schema.tables')
            ->where('table_schema = \'' . $connectionParams['dbname'] . '\'')
            ->orderBy('table_name')
            ->execute()
            ->fetchAll();

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
        $view->assign('typo3db', $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['dbname']);

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
        $content = \Sng\AdditionalReports\Utility::writeInformation('Crontab', $crontabString);
        $view->assign('crontab', $content);

        return $view->render();
    }

    /**
     * Generate the plugins and ctypes report
     *
     * @return string HTML code
     */
    public static function displayPlugins()
    {
        $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/plugins-fluid.html');

        $view->assign('extconf', unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['additional_reports']));
        $view->assign('url', \Sng\AdditionalReports\Utility::getBaseUrl());
        $view->assign('caution', \Sng\AdditionalReports\Utility::writeInformation(self::getLl('careful'), self::getLl('carefuldesc')));
        $view->assign('checkedpluginsmode3', (\Sng\AdditionalReports\Utility::getPluginsDisplayMode() == 3) ? ' checked="checked"' : '');
        $view->assign('checkedpluginsmode4', (\Sng\AdditionalReports\Utility::getPluginsDisplayMode() == 4) ? ' checked="checked"' : '');
        $view->assign('checkedpluginsmode5', (\Sng\AdditionalReports\Utility::getPluginsDisplayMode() == 5) ? ' checked="checked"' : '');
        $view->assign('checkedpluginsmode6', (\Sng\AdditionalReports\Utility::getPluginsDisplayMode() == 6) ? ' checked="checked"' : '');
        $view->assign('checkedpluginsmode7', (\Sng\AdditionalReports\Utility::getPluginsDisplayMode() == 7) ? ' checked="checked"' : '');

        switch (\Sng\AdditionalReports\Utility::getPluginsDisplayMode()) {
            case 3 :
                $view->assign('filtersCat', \Sng\AdditionalReports\Utility::getAllDifferentCtypesSelect(false));
                $view->assign('items', self::getAllUsedCtypes());
                break;
            case 4 :
                $view->assign('filtersCat', \Sng\AdditionalReports\Utility::getAllDifferentPluginsSelect(false));
                $view->assign('items', self::getAllUsedPlugins());
                break;
            case 5 :
                $view->assign('items', self::getSummary());
                break;
            case 6 :
                $view->assign('filtersCat', \Sng\AdditionalReports\Utility::getAllDifferentPluginsSelect(true));
                $view->assign('items', self::getAllUsedPlugins(true));
                break;
            case 7 :
                $view->assign('filtersCat', \Sng\AdditionalReports\Utility::getAllDifferentCtypesSelect(true));
                $view->assign('items', self::getAllUsedCtypes(true));
                break;
            default:
                $view->assign('items', self::getSummary());
                break;
        }

        $view->assign('display', \Sng\AdditionalReports\Utility::getPluginsDisplayMode());

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('templavoila') && class_exists('tx_templavoila_api')) {
            $view->assign('tvused', true);
        } else {
            $view->assign('tvused', false);
        }

        return $view->render();
    }

    /**
     * Generate the used plugins report
     *
     * @param boolean $displayHidden
     * @return string HTML code
     */
    public static function getAllUsedPlugins($displayHidden = false)
    {
        $getFiltersCat = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('filtersCat');
        $addhidden = ($displayHidden === true) ? '' : ' AND tt_content.hidden=0 AND pages.hidden=0 ';
        $addWhere = (($getFiltersCat !== null) && ($getFiltersCat != 'all')) ? ' AND tt_content.list_type=\'' . $getFiltersCat . '\'' : '';
        return \Sng\AdditionalReports\Utility::getAllPlugins($addhidden . $addWhere, '', true);
    }

    /**
     * Generate the used ctypes    report
     *
     * @param boolean $displayHidden
     * @return string HTML code
     */
    public static function getAllUsedCtypes($displayHidden = false)
    {
        $getFiltersCat = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('filtersCat');
        $addhidden = ($displayHidden === true) ? '' : ' AND tt_content.hidden=0 AND pages.hidden=0 ';
        $addWhere = (($getFiltersCat !== null) && ($getFiltersCat != 'all')) ? ' AND tt_content.CType=\'' . $getFiltersCat . '\'' : '';
        return \Sng\AdditionalReports\Utility::getAllCtypes($addhidden . $addWhere, '', true);
    }

    /**
     * Return informations about a ctype or plugin
     *
     * @param array $itemValue
     * @return array
     */
    public static function getContentInfos($itemValue)
    {
        $markersExt = array();

        $domain = \Sng\AdditionalReports\Utility::getDomain($itemValue['pid']);
        $markersExt['domain'] = \Sng\AdditionalReports\Utility::getIconDomain() . $domain;

        $iconPage = ($itemValue['hiddenpages'] == 0) ? \Sng\AdditionalReports\Utility::getIconPage() : \Sng\AdditionalReports\Utility::getIconPage(true);
        $iconContent = ($itemValue['hiddentt_content'] == 0) ? \Sng\AdditionalReports\Utility::getIconContent() : \Sng\AdditionalReports\Utility::getIconContent(true);

        $markersExt['pid'] = $iconPage . ' ' . $itemValue['pid'];
        $markersExt['uid'] = $iconContent . ' ' . $itemValue['uid'];
        $markersExt['pagetitle'] = $itemValue['title'];

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('templavoila') && class_exists('tx_templavoila_api')) {

            $linkAtt = array('href' => '#', 'title' => self::getLl('switch'), 'onclick' => \Sng\AdditionalReports\Utility::goToModuleList($itemValue['pid']));
            $markersExt['db'] = \Sng\AdditionalReports\Utility::generateLink($linkAtt, \Sng\AdditionalReports\Utility::getIconWebList());

            $linkAtt = array('href' => \Sng\AdditionalReports\Utility::goToModuleList($itemValue['pid'], true), 'target' => '_blank', 'title' => self::getLl('newwindow'));
            $markersExt['db'] .= \Sng\AdditionalReports\Utility::generateLink($linkAtt, \Sng\AdditionalReports\Utility::getIconWebList());

            $linkAtt = array('href' => '#', 'title' => self::getLl('switch'), 'onclick' => \Sng\AdditionalReports\Utility::goToModulePageTv($itemValue['pid']));
            $markersExt['page'] = \Sng\AdditionalReports\Utility::generateLink($linkAtt, \Sng\AdditionalReports\Utility::getIconWebPage());

            $linkAtt = array('href' => \Sng\AdditionalReports\Utility::goToModulePageTv($itemValue['pid'], true), 'target' => '_blank', 'title' => self::getLl('newwindow'));
            $markersExt['page'] .= \Sng\AdditionalReports\Utility::generateLink($linkAtt, \Sng\AdditionalReports\Utility::getIconWebPage());

            if (\Sng\AdditionalReports\Utility::isUsedInTv($itemValue['uid'], $itemValue['pid'])) {
                $markersExt['usedtv'] = self::getLl('yes');
                $markersExt['usedtvclass'] = ' typo3-message message-ok';
            } else {
                $markersExt['usedtv'] = self::getLl('no');
                $markersExt['usedtvclass'] = ' typo3-message message-error';
            }
        } else {
            $markersExt['usedtv'] = '';
            $markersExt['usedtvclass'] = '';

            $linkAtt = array('href' => '#', 'title' => self::getLl('switch'), 'onclick' => \Sng\AdditionalReports\Utility::goToModuleList($itemValue['pid']), 'class' => 'btn btn-default');
            $markersExt['db'] = \Sng\AdditionalReports\Utility::generateLink($linkAtt, \Sng\AdditionalReports\Utility::getIconWebList());

            $linkAtt = array('href' => \Sng\AdditionalReports\Utility::goToModuleList($itemValue['pid'], true), 'target' => '_blank', 'title' => self::getLl('newwindow'), 'class' => 'btn btn-default');
            $markersExt['db'] .= \Sng\AdditionalReports\Utility::generateLink($linkAtt, \Sng\AdditionalReports\Utility::getIconWebList());

            $linkAtt = array('href' => '#', 'title' => self::getLl('switch'), 'onclick' => \Sng\AdditionalReports\Utility::goToModulePage($itemValue['pid']), 'class' => 'btn btn-default');
            $markersExt['page'] = \Sng\AdditionalReports\Utility::generateLink($linkAtt, \Sng\AdditionalReports\Utility::getIconWebPage());

            $linkAtt = array('href' => \Sng\AdditionalReports\Utility::goToModulePage($itemValue['pid'], true), 'target' => '_blank', 'title' => self::getLl('newwindow'), 'class' => 'btn btn-default');
            $markersExt['page'] .= \Sng\AdditionalReports\Utility::generateLink($linkAtt, \Sng\AdditionalReports\Utility::getIconWebPage());
        }

        $markersExt['preview'] = '<a target="_blank" class="btn btn-default" href="http://' . $domain . '/index.php?id=' . $itemValue['pid'] . '">';
        $markersExt['preview'] .= \Sng\AdditionalReports\Utility::getIconZoom();
        $markersExt['preview'] .= '</a>';

        return $markersExt;
    }

    /**
     * Generate the summary of the plugins and ctypes report
     *
     * @return string HTML code
     */
    public static function getSummary()
    {

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
        $languageFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Localization\LocalizationFactory::class);

        foreach ($items as $itemKey => $itemValue) {
            $itemTemp = array();
            if ($itemValue['CType'] == 'list') {
                preg_match('/EXT:(.*?)\//', $plugins[$itemValue['list_type']][0], $ext);
                preg_match('/^LLL:(EXT:.*?):(.*)/', $plugins[$itemValue['list_type']][0], $llfile);
                $localLang = $languageFactory->getParsedData($llfile[1], $GLOBALS['LANG']->lang);
                if ($plugins[$itemValue['list_type']][2]) {
                    $itemTemp['iconext'] = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR') . $plugins[$itemValue['list_type']][2];
                } else {
                    $itemTemp['iconext'] = '';
                }
                $itemTemp['content'] = $GLOBALS['LANG']->getLLL($llfile[2], $localLang) . ' (' . $itemValue['list_type'] . ')';
            } else {
                preg_match('/^LLL:(EXT:.*?):(.*)/', $ctypes[$itemValue['CType']][0], $llfile);
                $localLang = $languageFactory->getParsedData($llfile[1], $GLOBALS['LANG']->lang);
                if (is_file(PATH_site . '/typo3/sysext/t3skin/icons/gfx/' . $ctypes[$itemValue['CType']][2])) {
                    $itemTemp['iconext'] = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/' . $ctypes[$itemValue['CType']][2];
                } elseif (preg_match('/^\.\./', $ctypes[$itemValue['CType']][2], $temp)) {
                    $itemTemp['iconext'] = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR') . $ctypes[$itemValue['CType']][2];
                } elseif (preg_match('/^EXT:(.*)$/', $ctypes[$itemValue['CType']][2], $temp)) {
                    $itemTemp['iconext'] = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR') . '../typo3conf/ext/' . $temp[1];
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
    public static function displayRealUrlErrors()
    {
        $cmd = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('cmd');

        if ($cmd === 'deleteAll') {
            $GLOBALS['TYPO3_DB']->exec_DELETEquery(
                'tx_realurl_errorlog',
                ''
            );
        }

        if ($cmd === 'delete') {
            $delete = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('delete');
            $GLOBALS['TYPO3_DB']->exec_DELETEquery(
                'tx_realurl_errorlog',
                'url_hash=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($delete, null)
            );
        }

        $query = array(
            'SELECT'  => 'url_hash,url,error,last_referer,counter,cr_date,tstamp',
            'FROM'    => 'tx_realurl_errorlog',
            'ORDERBY' => 'counter DESC'
        );

        $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/realurlerrors-fluid.html');
        $view->assign('extconf', unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['additional_reports']));
        $view->assign('baseUrl', \Sng\AdditionalReports\Utility::getBaseUrl());
        $view->assign('requestDir', \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR'));
        $view->assign('query', $query);
        return $view->render();
    }

    /**
     * Generate the log error report
     *
     * @return string HTML code
     */
    public static function displayLogErrors()
    {

        // query
        $query = array();
        $query['SELECT'] = 'COUNT(*) AS "nb",details,MAX(tstamp) as "tstamp"';
        $query['FROM'] = 'sys_log';
        $query['WHERE'] = 'error>0';
        $query['GROUPBY'] = 'details';
        $query['ORDERBY'] = 'nb DESC,tstamp DESC';
        $query['LIMIT'] = '';

        $orderby = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('orderby');
        if ($orderby !== null) {
            $query['ORDERBY'] = $orderby;
        }

        $content = \Sng\AdditionalReports\Utility::writeInformation(
            self::getLl('flushalllog'), 'DELETE FROM sys_log WHERE error>0;'
        );

        $logErrors = array();

        $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/logerrors-fluid.html');
        $view->assign('extconf', unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['additional_reports']));
        $view->assign('baseUrl', \Sng\AdditionalReports\Utility::getBaseUrl());
        $view->assign('requestDir', \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR'));
        $view->assign('query', $query);
        return $content . $view->render();
    }

    /**
     * Generate the website conf report
     *
     * @return string HTML code
     */
    public static function displayWebsitesConf()
    {
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
                $websiteconfItem['pagetitle'] = \Sng\AdditionalReports\Utility::getIconPage() . $itemValue['title'];
                $websiteconfItem['domains'] = '';
                $websiteconfItem['template'] = '';

                foreach ($domainRecords as $domain) {
                    $websiteconfItem['domains'] .= \Sng\AdditionalReports\Utility::getIconDomain() . $domain['domainName'] . '<br/>';
                }

                $templates = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
                    'uid,title,root',
                    'sys_template',
                    'pid IN(' . $itemValue['uid'] . ') AND deleted=0 AND hidden=0',
                    '',
                    'sorting'
                );

                foreach ($templates as $templateObj) {
                    $websiteconfItem['template'] .= \Sng\AdditionalReports\Utility::getIconTemplate() . ' ' . $templateObj['title'] . ' ';
                    $websiteconfItem['template'] .= '[uid=' . $templateObj['uid'] . ',root=' . $templateObj['root'] . ']<br/>';
                }

                // baseurl
                $tmpl = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\TypoScript\\ExtendedTemplateService');
                $tmpl->tt_track = 0;
                $tmpl->init();
                $tmpl->runThroughTemplates(\Sng\AdditionalReports\Utility::getRootLine($itemValue['uid']), 0);
                $tmpl->generateConfig();
                $websiteconfItem['baseurl'] = $tmpl->setup['config.']['baseURL'];

                // count pages
                $list = \Sng\AdditionalReports\Utility::getTreeList($itemValue['uid'], 99, 0, '1=1');
                $listArray = explode(',', $list);
                $websiteconfItem['pages'] = (count($listArray) - 1);
                $websiteconfItem['pageshidden'] = (\Sng\AdditionalReports\Utility::getCountPagesUids($list, 'hidden=1'));
                $websiteconfItem['pagesnosearch'] = (\Sng\AdditionalReports\Utility::getCountPagesUids($list, 'no_search=1'));

                $websiteconf[] = $websiteconfItem;
            }
        }

        $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/websiteconf-fluid.html');
        $view->assign('items', $websiteconf);
        return $view->render();
    }

    /**
     * Generate the dbcheck report
     *
     * @return string HTML code
     */
    public static function displayDbCheck()
    {
        $sqlStatements = \Sng\AdditionalReports\Utility::getSqlUpdateStatements();
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
            'table_schema = \'' . $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['dbname'] . '\'', '', 'table_name'
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

        $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/dbcheck-fluid.html');
        $view->assign('dbchecks', $dbchecks);
        return $view->render() . $content;
    }

    /**
     * @return \TYPO3\CMS\Core\Database\Connection
     */
    protected static function getDatabaseConnection()
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getConnectionByName(\TYPO3\CMS\Core\Database\ConnectionPool::DEFAULT_CONNECTION_NAME);
    }

    /**
     * @return \TYPO3\CMS\Core\Database\Query\QueryBuilder
     */
    protected static function getQueryBuilder()
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
            ->getConnectionByName(\TYPO3\CMS\Core\Database\ConnectionPool::DEFAULT_CONNECTION_NAME)
            ->createQueryBuilder();
    }

}

?>