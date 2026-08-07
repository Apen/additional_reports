<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\AdditionalReports\Service\ContentTypeResolver;
use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
        $displayMode = Utility::getPluginsDisplayMode($this->getRequestParameter('display'));
        $filter = $this->getRequestParameter('filtersCat');
        $filter = is_string($filter) ? $filter : null;

        $view->assign('reportname', Utility::hasLegacyListType() ? 'additionalreports_plugins' : 'plugins');
        $view->assign('paginationRoute', $this->getCurrentRouteIdentifier());
        $view->assign('extconf', unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['additional_reports'] ?? ''));
        $view->assign('checkedpluginsmode3', ($displayMode === 3) ? ' checked="checked"' : '');
        $view->assign('checkedpluginsmode4', ($displayMode === 4) ? ' checked="checked"' : '');
        $view->assign('checkedpluginsmode5', ($displayMode === 5) ? ' checked="checked"' : '');
        $view->assign('checkedpluginsmode6', ($displayMode === 6) ? ' checked="checked"' : '');
        $view->assign('checkedpluginsmode7', ($displayMode === 7) ? ' checked="checked"' : '');
        $view->assign('filtersCatParam', $filter);

        $currentPage = (int) ($this->getRequestParameter('currentPage') ?? 1);

        switch ($displayMode) {
            case 3:
                $view->assign('filterOptions', array_column(Utility::getAllDifferentCtypes(false), 'CType'));
                Utility::buildPagination($this->enrichContentRows($this->getAllUsedCtypes(false, $filter), 'ctype'), $currentPage, $view);
                break;
            case 4:
                $filterField = Utility::hasLegacyListType() ? 'list_type' : 'CType';
                $view->assign('filterOptions', array_column(Utility::getAllDifferentPlugins(false), $filterField));
                Utility::buildPagination($this->enrichContentRows($this->getAllUsedPlugins(false, $filter), 'plugin'), $currentPage, $view);
                break;
            case 6:
                $filterField = Utility::hasLegacyListType() ? 'list_type' : 'CType';
                $view->assign('filterOptions', array_column(Utility::getAllDifferentPlugins(true), $filterField));
                Utility::buildPagination($this->enrichContentRows($this->getAllUsedPlugins(true, $filter), 'plugin'), $currentPage, $view);
                break;
            case 7:
                $view->assign('filterOptions', array_column(Utility::getAllDifferentCtypes(true), 'CType'));
                Utility::buildPagination($this->enrichContentRows($this->getAllUsedCtypes(true, $filter), 'ctype'), $currentPage, $view);
                break;
            default:
                $view->assign('items', $this->getSummary());
                break;
        }

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
    public function getSummary()
    {
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
        $resolver = GeneralUtility::makeInstance(ContentTypeResolver::class);

        foreach ($items as $itemValue) {
            $itemTemp = [];
            if ($hasLegacyListType && $itemValue['CType'] === 'list') {
                $itemTemp = array_merge($itemTemp, $resolver->resolve('plugin', $itemValue['list_type']));
                $itemTemp['content'] = $itemTemp['plugin'] ?? '';
            } else {
                $itemTemp = array_merge($itemTemp, $resolver->resolve('ctype', $itemValue['CType']));
                $itemTemp['content'] = $itemTemp['ctype'] ?? '';
            }
            $itemTemp['references'] = $itemValue['nb'];
            $itemTemp['pourc'] = $itemsCount > 0 ? round((($itemValue['nb'] * 100) / $itemsCount), 2) : 0.0;
            $allItems[] = $itemTemp;
        }

        return $allItems;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    public function enrichContentRows(array $items, string $type): array
    {
        $resolver = GeneralUtility::makeInstance(ContentTypeResolver::class);
        $hasLegacyListType = Utility::hasLegacyListType();
        foreach ($items as &$item) {
            $value = $type === 'plugin' && $hasLegacyListType
                ? (string) ($item['list_type'] ?? '')
                : (string) ($item['CType'] ?? '');
            $item = array_merge($item, $resolver->resolve($type, $value));
            $pageId = (int) ($item['pid'] ?? 0);
            $item['domain'] = Utility::getDomain($pageId);
            $item['pagetitle'] = (string) ($item['title'] ?? '');
            $item['usedtv'] = '';
            $item['usedtvclass'] = '';
            $item['preview'] = '/index.php?id=' . $pageId;
        }
        unset($item);
        return $items;
    }

    /**
     * Generate the used plugins report
     */
    public function getAllUsedPlugins(bool $displayHidden = false, ?string $filter = null): array
    {
        return Utility::getAllPlugins($displayHidden, $filter);
    }

    /**
     * Generate the used ctypes report
     */
    public function getAllUsedCtypes(bool $displayHidden = false, ?string $filter = null): array
    {
        return Utility::getAllCtypes($displayHidden, $filter);
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
