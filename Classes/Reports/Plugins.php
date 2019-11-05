<?php

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

class Plugins extends \Sng\AdditionalReports\Reports\AbstractReport implements \TYPO3\CMS\Reports\ReportInterface
{

    /**
     * This method renders the report
     *
     * @return string The status report as HTML
     */
    public function getReport()
    {
        $content = $this->display();
        return $content;
    }

    /**
     * Generate the plugins and ctypes report
     *
     * @return string HTML code
     */
    public function display()
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
     * Generate the summary of the plugins and ctypes report
     *
     * @return array
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

        $itemsCount = \Sng\AdditionalReports\Utility::exec_SELECTgetRows(
            'COUNT( tt_content.uid ) as "nb"', 'tt_content,pages',
            'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.hidden=0 ' .
            'AND tt_content.deleted=0 AND pages.hidden=0 AND pages.deleted=0'
        );

        $items = \Sng\AdditionalReports\Utility::exec_SELECTgetRows(
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

}
