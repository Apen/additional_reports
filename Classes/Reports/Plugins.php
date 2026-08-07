<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

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
        $view = $this->createView();

        $view->assign('reportname', Utility::hasLegacyListType() ? 'additionalreports_plugins' : 'plugins');
        $view->assign('paginationRoute', $this->getCurrentRouteIdentifier());
        $view->assign('extconf', unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['additional_reports'] ?? ''));
        $view->assign('checkedpluginsmode3', (Utility::getPluginsDisplayMode() === 3) ? ' checked="checked"' : '');
        $view->assign('checkedpluginsmode4', (Utility::getPluginsDisplayMode() === 4) ? ' checked="checked"' : '');
        $view->assign('checkedpluginsmode5', (Utility::getPluginsDisplayMode() === 5) ? ' checked="checked"' : '');
        $view->assign('checkedpluginsmode6', (Utility::getPluginsDisplayMode() === 6) ? ' checked="checked"' : '');
        $view->assign('checkedpluginsmode7', (Utility::getPluginsDisplayMode() === 7) ? ' checked="checked"' : '');
        $view->assign('filtersCatParam', Utility::_GP('filtersCat'));

        $currentPage = (int) (Utility::_GP('currentPage') ?? 1);

        switch (Utility::getPluginsDisplayMode()) {
            case 3:
                $view->assign('filterOptions', array_column(Utility::getAllDifferentCtypes(false), 'CType'));
                Utility::buildPagination(self::getAllUsedCtypes(), $currentPage, $view);
                break;
            case 4:
                $filterField = Utility::hasLegacyListType() ? 'list_type' : 'CType';
                $view->assign('filterOptions', array_column(Utility::getAllDifferentPlugins(false), $filterField));
                Utility::buildPagination(self::getAllUsedPlugins(), $currentPage, $view);
                break;
            case 6:
                $filterField = Utility::hasLegacyListType() ? 'list_type' : 'CType';
                $view->assign('filterOptions', array_column(Utility::getAllDifferentPlugins(true), $filterField));
                Utility::buildPagination(self::getAllUsedPlugins(true), $currentPage, $view);
                break;
            case 7:
                $view->assign('filterOptions', array_column(Utility::getAllDifferentCtypes(true), 'CType'));
                Utility::buildPagination(self::getAllUsedCtypes(true), $currentPage, $view);
                break;
            default:
                $view->assign('items', self::getSummary());
                break;
        }

        $displayMode = Utility::getPluginsDisplayMode();
        $view->assign('display', $displayMode);
        $view->assign('showCtypes', in_array($displayMode, [3, 7], true));
        $view->assign('showPlugins', in_array($displayMode, [4, 6], true));

        if (ExtensionManagementUtility::isLoaded('templavoila') && class_exists('tx_templavoila_api')) {
            $view->assign('tvused', true);
        } else {
            $view->assign('tvused', false);
        }

        return $view->render('plugins-fluid');
    }

    /**
     * Generate the summary of the plugins and ctypes report
     *
     * @return array
     */
    public static function getSummary()
    {
        $plugins = [];
        foreach (($GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'] ?? []) as $itemValue) {
            if (trim($itemValue[1] ?? '') !== '') {
                $plugins[$itemValue[1]] = $itemValue;
            }
        }

        $ctypes = [];
        foreach (($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] ?? []) as $itemValue) {
            if (($itemValue[1] ?? '') != '--div--') {
                $ctypes[$itemValue[1] ?? ''] = $itemValue;
            }
        }

        $queryBuilder = Utility::getQueryBuilder('tt_content');
        $itemsCount = (int) $queryBuilder
            ->count('tt_content.uid')
            ->from('tt_content')
            ->innerJoin('tt_content', 'pages', 'pages', 'tt_content.pid = pages.uid')
            ->where($queryBuilder->expr()->gte('pages.pid', 0))
            ->andWhere($queryBuilder->expr()->eq('tt_content.hidden', 0))
            ->andWhere($queryBuilder->expr()->eq('pages.hidden', 0))
            ->executeQuery()
            ->fetchOne();

        $hasLegacyListType = Utility::hasLegacyListType();
        $groupFields = $hasLegacyListType
            ? 'tt_content.CType,tt_content.list_type'
            : 'tt_content.CType';
        $queryBuilder = Utility::getQueryBuilder('tt_content');
        $queryBuilder
            ->select('tt_content.CType')
            ->addSelectLiteral('COUNT(*) AS nb')
            ->from('tt_content')
            ->innerJoin('tt_content', 'pages', 'pages', 'tt_content.pid = pages.uid')
            ->where($queryBuilder->expr()->gte('pages.pid', 0))
            ->andWhere($queryBuilder->expr()->eq('tt_content.hidden', 0))
            ->andWhere($queryBuilder->expr()->eq('pages.hidden', 0))
            ->groupBy('tt_content.CType')
            ->orderBy('nb', 'DESC');
        if ($hasLegacyListType) {
            $queryBuilder->addSelect('tt_content.list_type')->addGroupBy('tt_content.list_type');
        }
        $items = $queryBuilder->executeQuery()->fetchAllAssociative();

        $allItems = [];

        foreach ($items as $itemValue) {
            $itemTemp = [];
            if ($hasLegacyListType && $itemValue['CType'] === 'list') {
                $itemTemp = array_merge($itemTemp, Utility::getContentInfosFromTca('plugin', $itemValue['list_type']));
                $itemTemp['content'] = $itemTemp['plugin'] ?? '';
            } else {
                $itemTemp = array_merge($itemTemp, Utility::getContentInfosFromTca('ctype', $itemValue['CType']));
                $itemTemp['content'] = $itemTemp['ctype'] ?? '';
            }
            $itemTemp['references'] = $itemValue['nb'];
            $itemTemp['pourc'] = $itemsCount > 0 ? round((($itemValue['nb'] * 100) / $itemsCount), 2) : 0.0;
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
        return Utility::getAllPlugins($displayHidden, is_string($getFiltersCat) ? $getFiltersCat : null);
    }

    /**
     * Generate the used ctypes report
     */
    public static function getAllUsedCtypes(bool $displayHidden = false): array
    {
        $getFiltersCat = Utility::_GP('filtersCat');
        return Utility::getAllCtypes($displayHidden, is_string($getFiltersCat) ? $getFiltersCat : null);
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
