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
        $items = \Sng\AdditionalReports\Utility::exec_SELECTgetRows(
            'table_name',
            'information_schema.tables',
            'table_schema = \'' . $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['dbname'] . '\'', '', 'table_name'
        );

        $sqlStructure = '';

        foreach ($items as $table) {
            $resSqlDump = \Sng\AdditionalReports\Utility::sql_query('SHOW CREATE TABLE ' . $table['table_name']);
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

}

?>