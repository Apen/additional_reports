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
        $addContent .= (count($allExtension['ter']) + count($allExtension['dev'])) . ' ' . \Sng\AdditionalReports\Utility::getLl('extensions_extensions');
        $addContent .= '<br/>';
        $addContent .= count($allExtension['ter']) . ' ' . \Sng\AdditionalReports\Utility::getLl('extensions_ter');
        $addContent .= '  /  ';
        $addContent .= count($allExtension['dev']) . ' ' . \Sng\AdditionalReports\Utility::getLl('extensions_dev');
        $addContent .= '<br/>';
        $addContent .= $extensionsToUpdate . ' ' . \Sng\AdditionalReports\Utility::getLl('extensions_toupdate');
        $addContent .= '  /  ';
        $addContent .= $extensionsModified . ' ' . \Sng\AdditionalReports\Utility::getLl('extensions_extensionsmodified');
        $addContentItem = \Sng\AdditionalReports\Utility::writeInformation(\Sng\AdditionalReports\Utility::getLl('pluginsmode5') . '<br/>' . \Sng\AdditionalReports\Utility::getLl('extensions_updateter') . '', $addContent);

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
        $listExtensionsTerItem['versioncompare'] = '<input type="button" onclick="' . $js . '" value="' . \Sng\AdditionalReports\Utility::getLl('comparesame') . '" title="' . $compareLabem . '"/>';

        // need extension update ?
        if (version_compare($itemValue['EM_CONF']['version'], $itemValue['lastversion']['version'], '<')) {
            $listExtensionsTerItem['versionlast'] = '<span style="color:green;font-weight:bold;">' . $itemValue['lastversion']['version'] . '&nbsp;(' . $itemValue['lastversion']['updatedate'] . ')</span>';
            $compareUrl = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
            $compareUrl .= 'typo3/ajax.php?ajaxID=additional_reports::compareFiles';
            $compareUrl .= '&extKey=' . $extKey . '&mode=compareExtension&extVersion=' . $itemValue['lastversion']['version'];
            $compareLabem = $extKey . ' : ' . $itemValue['EM_CONF']['version'] . ' <--> TER ' . $itemValue['lastversion']['version'];
            $js = 'Shadowbox.open({content:\'' . $compareUrl . '\',player:\'iframe\',title:\'' . $compareLabem . '\',height:600,width:800});';
            $listExtensionsTerItem['versioncompare'] .= ' <input type="button" onclick="' . $js . '" value="' . \Sng\AdditionalReports\Utility::getLl('comparelast') . '" title="' . $compareLabem . '"/>';
        } else {
            $listExtensionsTerItem['versionlast'] = $itemValue['lastversion']['version'] . '&nbsp;(' . $itemValue['lastversion']['updatedate'] . ')';
        }

        $listExtensionsTerItem['downloads'] = $itemValue['lastversion']['alldownloadcounter'];

        // show db
        $dumpTf1 = '';
        $dumpTf2 = '';
        if (count($itemValue['fdfile']) > 0) {
            $id = 'sql' . $extKey;
            $dumpTf1 = count($itemValue['fdfile']) . ' ' . \Sng\AdditionalReports\Utility::getLl('extensions_tablesmodified');
            $dumpTf2 = \Sng\AdditionalReports\Utility::writePopUp($id, $extKey, \Sng\AdditionalReports\Utility::viewArray($itemValue['fdfile']));
        }
        $listExtensionsTerItem['tables'] = $dumpTf1;
        $listExtensionsTerItem['tableslink'] = $dumpTf2;

        // need db update
        if (count($itemValue['updatestatements']) > 0) {
            $listExtensionsTerItem['tablesintegrity'] = \Sng\AdditionalReports\Utility::getLl('yes');
        } else {
            $listExtensionsTerItem['tablesintegrity'] = \Sng\AdditionalReports\Utility::getLl('no');
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
                $listExtensionsTerItem['confintegrity'] = \Sng\AdditionalReports\Utility::getLl('yes') . '&nbsp;&nbsp;' . $dumpExtConf;
            } else {
                $listExtensionsTerItem['confintegrity'] = \Sng\AdditionalReports\Utility::getLl('no');
            }
        } else {
            $listExtensionsTerItem['confintegrity'] = \Sng\AdditionalReports\Utility::getLl('no');
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
            $listExtensionsTerItem['files'] = count($itemValue['affectedfiles']) . ' ' . \Sng\AdditionalReports\Utility::getLl('extensions_filesmodified') . $contentUl;
            $listExtensionsTerItem['fileslink'] = '<input type="button" onclick="$(\'' . $id . '\').toggle();" value="+"/>';
        } else {
            $listExtensionsTerItem['files'] = '&nbsp;';
            $listExtensionsTerItem['fileslink'] = '&nbsp;';
        }

        return $listExtensionsTerItem;
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
        $view->assign('caution', \Sng\AdditionalReports\Utility::writeInformation(\Sng\AdditionalReports\Utility::getLl('careful'), \Sng\AdditionalReports\Utility::getLl('carefuldesc')));
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

            $linkAtt = array('href' => '#', 'title' => \Sng\AdditionalReports\Utility::getLl('switch'), 'onclick' => \Sng\AdditionalReports\Utility::goToModuleList($itemValue['pid']));
            $markersExt['db'] = \Sng\AdditionalReports\Utility::generateLink($linkAtt, \Sng\AdditionalReports\Utility::getIconWebList());

            $linkAtt = array('href' => \Sng\AdditionalReports\Utility::goToModuleList($itemValue['pid'], true), 'target' => '_blank', 'title' => \Sng\AdditionalReports\Utility::getLl('newwindow'));
            $markersExt['db'] .= \Sng\AdditionalReports\Utility::generateLink($linkAtt, \Sng\AdditionalReports\Utility::getIconWebList());

            $linkAtt = array('href' => '#', 'title' => \Sng\AdditionalReports\Utility::getLl('switch'), 'onclick' => \Sng\AdditionalReports\Utility::goToModulePageTv($itemValue['pid']));
            $markersExt['page'] = \Sng\AdditionalReports\Utility::generateLink($linkAtt, \Sng\AdditionalReports\Utility::getIconWebPage());

            $linkAtt = array('href' => \Sng\AdditionalReports\Utility::goToModulePageTv($itemValue['pid'], true), 'target' => '_blank', 'title' => \Sng\AdditionalReports\Utility::getLl('newwindow'));
            $markersExt['page'] .= \Sng\AdditionalReports\Utility::generateLink($linkAtt, \Sng\AdditionalReports\Utility::getIconWebPage());

            if (\Sng\AdditionalReports\Utility::isUsedInTv($itemValue['uid'], $itemValue['pid'])) {
                $markersExt['usedtv'] = \Sng\AdditionalReports\Utility::getLl('yes');
                $markersExt['usedtvclass'] = ' typo3-message message-ok';
            } else {
                $markersExt['usedtv'] = \Sng\AdditionalReports\Utility::getLl('no');
                $markersExt['usedtvclass'] = ' typo3-message message-error';
            }
        } else {
            $markersExt['usedtv'] = '';
            $markersExt['usedtvclass'] = '';

            $linkAtt = array('href' => '#', 'title' => \Sng\AdditionalReports\Utility::getLl('switch'), 'onclick' => \Sng\AdditionalReports\Utility::goToModuleList($itemValue['pid']), 'class' => 'btn btn-default');
            $markersExt['db'] = \Sng\AdditionalReports\Utility::generateLink($linkAtt, \Sng\AdditionalReports\Utility::getIconWebList());

            $linkAtt = array('href' => \Sng\AdditionalReports\Utility::goToModuleList($itemValue['pid'], true), 'target' => '_blank', 'title' => \Sng\AdditionalReports\Utility::getLl('newwindow'), 'class' => 'btn btn-default');
            $markersExt['db'] .= \Sng\AdditionalReports\Utility::generateLink($linkAtt, \Sng\AdditionalReports\Utility::getIconWebList());

            $linkAtt = array('href' => '#', 'title' => \Sng\AdditionalReports\Utility::getLl('switch'), 'onclick' => \Sng\AdditionalReports\Utility::goToModulePage($itemValue['pid']), 'class' => 'btn btn-default');
            $markersExt['page'] = \Sng\AdditionalReports\Utility::generateLink($linkAtt, \Sng\AdditionalReports\Utility::getIconWebPage());

            $linkAtt = array('href' => \Sng\AdditionalReports\Utility::goToModulePage($itemValue['pid'], true), 'target' => '_blank', 'title' => \Sng\AdditionalReports\Utility::getLl('newwindow'), 'class' => 'btn btn-default');
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