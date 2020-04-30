<?php

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Core\Localization\LocalizationFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Reports\ReportInterface;

class Plugins extends AbstractReport implements ReportInterface
{

    /**
     * This method renders the report
     *
     * @return string The status report as HTML
     */
    public function getReport()
    {
        return $this->display();
    }

    /**
     * Generate the plugins and ctypes report
     *
     * @return string HTML code
     */
    public function display()
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/plugins-fluid.html');

        $view->assign('extconf', unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['additional_reports']));
        $view->assign('url', Utility::getBaseUrl());
        $view->assign('caution', Utility::writeInformation(Utility::getLl('careful'), Utility::getLl('carefuldesc')));
        $view->assign('checkedpluginsmode3', (Utility::getPluginsDisplayMode() == 3) ? ' checked="checked"' : '');
        $view->assign('checkedpluginsmode4', (Utility::getPluginsDisplayMode() == 4) ? ' checked="checked"' : '');
        $view->assign('checkedpluginsmode5', (Utility::getPluginsDisplayMode() == 5) ? ' checked="checked"' : '');
        $view->assign('checkedpluginsmode6', (Utility::getPluginsDisplayMode() == 6) ? ' checked="checked"' : '');
        $view->assign('checkedpluginsmode7', (Utility::getPluginsDisplayMode() == 7) ? ' checked="checked"' : '');

        switch (Utility::getPluginsDisplayMode()) {
            case 3:
                $view->assign('filtersCat', Utility::getAllDifferentCtypesSelect(false));
                $view->assign('items', self::getAllUsedCtypes());
                break;
            case 4:
                $view->assign('filtersCat', Utility::getAllDifferentPluginsSelect(false));
                $view->assign('items', self::getAllUsedPlugins());
                break;
            case 6:
                $view->assign('filtersCat', Utility::getAllDifferentPluginsSelect(true));
                $view->assign('items', self::getAllUsedPlugins(true));
                break;
            case 7:
                $view->assign('filtersCat', Utility::getAllDifferentCtypesSelect(true));
                $view->assign('items', self::getAllUsedCtypes(true));
                break;
            default:
                $view->assign('items', self::getSummary());
                break;
        }

        $view->assign('display', Utility::getPluginsDisplayMode());

        if (ExtensionManagementUtility::isLoaded('templavoila') && class_exists('tx_templavoila_api')) {
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
        $plugins = [];
        foreach ($GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'] as $itemValue) {
            if (trim($itemValue[1]) !== '') {
                $plugins[$itemValue[1]] = $itemValue;
            }
        }

        $ctypes = [];
        foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $itemValue) {
            if ($itemValue[1] != '--div--') {
                $ctypes[$itemValue[1]] = $itemValue;
            }
        }

        $itemsCount = Utility::exec_SELECTgetRows(
            'COUNT( tt_content.uid ) as "nb"',
            'tt_content,pages',
            'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.hidden=0 ' .
            'AND tt_content.deleted=0 AND pages.hidden=0 AND pages.deleted=0'
        );

        $items = Utility::exec_SELECTgetRows(
            'tt_content.CType,tt_content.list_type,count(*) as "nb"',
            'tt_content,pages',
            'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.hidden=0 ' .
            'AND tt_content.deleted=0 AND pages.hidden=0 AND pages.deleted=0',
            'tt_content.CType,tt_content.list_type',
            'nb DESC'
        );

        $allItems = [];
        $languageFactory = GeneralUtility::makeInstance(LocalizationFactory::class);

        foreach ($items as $itemValue) {
            $itemTemp = [];
            if ($itemValue['CType'] == 'list') {
                preg_match('#EXT:(.*?)\/#', $plugins[$itemValue['list_type']][0], $ext);
                preg_match('#^LLL:(EXT:.*?):(.*)#', $plugins[$itemValue['list_type']][0], $llfile);
                $localLang = $languageFactory->getParsedData($llfile[1], $GLOBALS['LANG']->lang);
                if ($plugins[$itemValue['list_type']][2]) {
                    $itemTemp['iconext'] = GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR') . $plugins[$itemValue['list_type']][2];
                } else {
                    $itemTemp['iconext'] = '';
                }
                $itemTemp['content'] = $GLOBALS['LANG']->getLLL($llfile[2], $localLang) . ' (' . $itemValue['list_type'] . ')';
            } else {
                preg_match('#^LLL:(EXT:.*?):(.*)#', $ctypes[$itemValue['CType']][0], $llfile);
                $localLang = $languageFactory->getParsedData($llfile[1], $GLOBALS['LANG']->lang);
                if (is_file(PATH_site . '/typo3/sysext/t3skin/icons/gfx/' . $ctypes[$itemValue['CType']][2])) {
                    $itemTemp['iconext'] = GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/' . $ctypes[$itemValue['CType']][2];
                } elseif (preg_match('#^\.\.#', $ctypes[$itemValue['CType']][2], $temp)) {
                    $itemTemp['iconext'] = GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR') . $ctypes[$itemValue['CType']][2];
                } elseif (preg_match('#^EXT:(.*)$#', $ctypes[$itemValue['CType']][2], $temp)) {
                    $itemTemp['iconext'] = GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR') . '../typo3conf/ext/' . $temp[1];
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
     * @param bool $displayHidden
     * @return string HTML code
     */
    public static function getAllUsedPlugins($displayHidden = false)
    {
        $getFiltersCat = GeneralUtility::_GP('filtersCat');
        $addhidden = ($displayHidden) ? '' : ' AND tt_content.hidden=0 AND pages.hidden=0 ';
        $addWhere = (($getFiltersCat !== null) && ($getFiltersCat != 'all')) ? " AND tt_content.list_type='" . $getFiltersCat . "'" : '';
        return Utility::getAllPlugins($addhidden . $addWhere, '', true);
    }

    /**
     * Generate the used ctypes    report
     *
     * @param bool $displayHidden
     * @return string HTML code
     */
    public static function getAllUsedCtypes($displayHidden = false)
    {
        $getFiltersCat = GeneralUtility::_GP('filtersCat');
        $addhidden = ($displayHidden) ? '' : ' AND tt_content.hidden=0 AND pages.hidden=0 ';
        $addWhere = (($getFiltersCat !== null) && ($getFiltersCat != 'all')) ? " AND tt_content.CType='" . $getFiltersCat . "'" : '';
        return Utility::getAllCtypes($addhidden . $addWhere, '', true);
    }
}
