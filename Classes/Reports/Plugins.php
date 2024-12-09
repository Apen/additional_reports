<?php

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Reports\ReportInterface;

class Plugins extends AbstractReport
{
    /**
     * This method renders the report
     *
     * @return string The status report as HTML
     */
    public function getReport(): string
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
        $view->setPartialRootPaths([ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Partials/']);

        $view->assign('reportname', $_GET['report'] ?? 'additionalreports_plugins');
        $view->assign('extconf', unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['additional_reports'] ?? ''));
        $view->assign('url', Utility::getBaseUrl());
        $view->assign('caution', Utility::writeInformation(Utility::getLl('careful'), Utility::getLl('carefuldesc')));
        $view->assign('checkedpluginsmode3', (Utility::getPluginsDisplayMode() === 3) ? ' checked="checked"' : '');
        $view->assign('checkedpluginsmode4', (Utility::getPluginsDisplayMode() === 4) ? ' checked="checked"' : '');
        $view->assign('checkedpluginsmode5', (Utility::getPluginsDisplayMode() === 5) ? ' checked="checked"' : '');
        $view->assign('checkedpluginsmode6', (Utility::getPluginsDisplayMode() === 6) ? ' checked="checked"' : '');
        $view->assign('checkedpluginsmode7', (Utility::getPluginsDisplayMode() === 7) ? ' checked="checked"' : '');
        $view->assign('filtersCatParam', Utility::_GP('filtersCat'));

        $currentPage = !empty($_GET['currentPage']) ? (int)$_GET['currentPage'] : 1;

        switch (Utility::getPluginsDisplayMode()) {
            case 3:
                $view->assign('filtersCat', Utility::getAllDifferentCtypesSelect(false));
                Utility::buildPagination(self::getAllUsedCtypes(), $currentPage, $view);
                break;
            case 4:
                $view->assign('filtersCat', Utility::getAllDifferentPluginsSelect(false));
                Utility::buildPagination(self::getAllUsedPlugins(), $currentPage, $view);
                break;
            case 6:
                $view->assign('filtersCat', Utility::getAllDifferentPluginsSelect(true));
                Utility::buildPagination(self::getAllUsedPlugins(true), $currentPage, $view);
                break;
            case 7:
                $view->assign('filtersCat', Utility::getAllDifferentCtypesSelect(true));
                Utility::buildPagination(self::getAllUsedCtypes(true), $currentPage, $view);
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
            if (trim($itemValue[1] ?? '') !== '') {
                $plugins[$itemValue[1]] = $itemValue;
            }
        }

        $ctypes = [];
        foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $itemValue) {
            if (($itemValue[1] ?? '') != '--div--') {
                $ctypes[$itemValue[1] ?? ''] = $itemValue;
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

        foreach ($items as $itemValue) {
            $itemTemp = [];
            if ($itemValue['CType'] == 'list') {
                $itemTemp = array_merge($itemTemp, Utility::getContentInfosFromTca('plugin', $itemValue['list_type']));
                $itemTemp['content'] = $itemTemp['plugin'] ?? '';
            } else {
                $itemTemp = array_merge($itemTemp, Utility::getContentInfosFromTca('ctype', $itemValue['CType']));
                $itemTemp['content'] = $itemTemp['ctype'] ?? '';
            }
            $itemTemp['references'] = $itemValue['nb'];
            $itemTemp['pourc'] = round((($itemValue['nb'] * 100) / $itemsCount[0]['nb']), 2);
            $allItems[] = $itemTemp;
        }

        return $allItems;
    }

    /**
     * Generate the used plugins report
     */
    public static function getAllUsedPlugins(bool $displayHidden = false): array
    {
        $getFiltersCat = Utility::_GP('filtersCat');
        $addHidden = ($displayHidden) ? '' : ' AND tt_content.hidden=0 AND pages.hidden=0 ';
        $addWhere = ($getFiltersCat !== null && $getFiltersCat != 'all') ? " AND tt_content.list_type='" . $getFiltersCat . "'" : '';
        return Utility::getAllPlugins($addHidden . $addWhere, '');
    }

    /**
     * Generate the used ctypes report
     */
    public static function getAllUsedCtypes(bool $displayHidden = false): array
    {
        $getFiltersCat = Utility::_GP('filtersCat');
        $addHidden = ($displayHidden) ? '' : ' AND tt_content.hidden=0 AND pages.hidden=0 ';
        $addWhere = ($getFiltersCat !== null && $getFiltersCat != 'all') ? " AND tt_content.CType='" . $getFiltersCat . "'" : '';
        return Utility::getAllCtypes($addHidden . $addWhere, '');
    }

    public function getIdentifier(): string
    {
        return 'additionalreports_plugins';
    }

    public function getTitle(): string
    {
        return 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:plugins_title';
    }

    public function getDescription(): string
    {
        return 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:plugins_description';
    }

    public function getIconIdentifier(): string
    {
        return 'additionalreports_plugins';
    }
}
