<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\AdditionalReports\Repository\ContentUsageRepository;
use Sng\AdditionalReports\Service\ContentTypeResolver;
use Sng\AdditionalReports\Service\PaginationService;
use Sng\AdditionalReports\Service\SiteDomainResolver;
use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Plugins extends AbstractReport
{
    private ContentUsageRepository $contentUsageRepository;

    private ContentTypeResolver $contentTypeResolver;

    private PaginationService $paginationService;

    private SiteDomainResolver $siteDomainResolver;

    public function __construct(
        ?object $reportObject = null,
        ?ContentUsageRepository $contentUsageRepository = null,
        ?ContentTypeResolver $contentTypeResolver = null,
        ?PaginationService $paginationService = null,
        ?SiteDomainResolver $siteDomainResolver = null,
    ) {
        parent::__construct($reportObject);
        $this->contentUsageRepository = $contentUsageRepository ?? GeneralUtility::makeInstance(ContentUsageRepository::class);
        $this->contentTypeResolver = $contentTypeResolver ?? GeneralUtility::makeInstance(ContentTypeResolver::class);
        $this->paginationService = $paginationService ?? GeneralUtility::makeInstance(PaginationService::class);
        $this->siteDomainResolver = $siteDomainResolver ?? GeneralUtility::makeInstance(SiteDomainResolver::class);
    }

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

        $view->assign('reportname', $this->contentTypeResolver->hasLegacyListType() ? 'additionalreports_plugins' : 'plugins');
        $view->assign('paginationRoute', $this->getCurrentRouteIdentifier());
        $view->assign('checkedpluginsmode3', $displayMode === 3);
        $view->assign('checkedpluginsmode4', $displayMode === 4);
        $view->assign('checkedpluginsmode5', $displayMode === 5);
        $view->assign('checkedpluginsmode6', $displayMode === 6);
        $view->assign('checkedpluginsmode7', $displayMode === 7);
        $view->assign('filtersCatParam', $filter);

        $currentPage = (int) ($this->getRequestParameter('currentPage') ?? 1);

        switch ($displayMode) {
            case 3:
                $view->assign('filterOptions', array_column($this->contentUsageRepository->findDistinctContentTypes(), 'CType'));
                $this->paginationService->assign($this->enrichContentRows($this->getAllUsedCtypes(false, $filter), 'ctype'), $currentPage, $view);
                break;
            case 4:
                $filterField = $this->contentTypeResolver->hasLegacyListType() ? 'list_type' : 'CType';
                $view->assign('filterOptions', array_column($this->contentUsageRepository->findDistinctPlugins(), $filterField));
                $this->paginationService->assign($this->enrichContentRows($this->getAllUsedPlugins(false, $filter), 'plugin'), $currentPage, $view);
                break;
            case 6:
                $filterField = $this->contentTypeResolver->hasLegacyListType() ? 'list_type' : 'CType';
                $view->assign('filterOptions', array_column($this->contentUsageRepository->findDistinctPlugins(true), $filterField));
                $this->paginationService->assign($this->enrichContentRows($this->getAllUsedPlugins(true, $filter), 'plugin'), $currentPage, $view);
                break;
            case 7:
                $view->assign('filterOptions', array_column($this->contentUsageRepository->findDistinctContentTypes(true), 'CType'));
                $this->paginationService->assign($this->enrichContentRows($this->getAllUsedCtypes(true, $filter), 'ctype'), $currentPage, $view);
                break;
            default:
                $view->assign('items', $this->getSummary());
                break;
        }

        $view->assign('display', $displayMode);
        $view->assign('showCtypes', in_array($displayMode, [3, 7], true));
        $view->assign('showPlugins', in_array($displayMode, [4, 6], true));

        return $view->render('plugins-fluid');
    }

    /**
     * Generate the summary of the plugins and ctypes report
     *
     * @return array
     */
    public function getSummary()
    {
        $summary = $this->contentUsageRepository->summarizeVisibleContent();
        $hasLegacyListType = $this->contentTypeResolver->hasLegacyListType();

        $allItems = [];
        foreach ($summary['items'] as $itemValue) {
            $itemTemp = [];
            if ($hasLegacyListType && $itemValue['CType'] === 'list') {
                $itemTemp = array_merge($itemTemp, $this->contentTypeResolver->resolve('plugin', $itemValue['list_type'] ?? ''));
                $itemTemp['content'] = $itemTemp['plugin'] ?? '';
            } else {
                $itemTemp = array_merge($itemTemp, $this->contentTypeResolver->resolve('ctype', $itemValue['CType']));
                $itemTemp['content'] = $itemTemp['ctype'] ?? '';
            }
            $itemTemp['references'] = $itemValue['count'];
            $itemTemp['pourc'] = $summary['total'] > 0 ? round((($itemValue['count'] * 100) / $summary['total']), 2) : 0.0;
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
        $hasLegacyListType = $this->contentTypeResolver->hasLegacyListType();
        foreach ($items as &$item) {
            $value = $type === 'plugin' && $hasLegacyListType
                ? (string) ($item['list_type'] ?? '')
                : (string) ($item['CType'] ?? '');
            $item = array_merge($item, $this->contentTypeResolver->resolve($type, $value));
            $pageId = (int) ($item['pid'] ?? 0);
            $item['domain'] = $this->siteDomainResolver->resolve($pageId);
            $item['pagetitle'] = (string) ($item['title'] ?? '');
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
        return $this->contentUsageRepository->findPlugins($displayHidden, $filter);
    }

    /**
     * Generate the used ctypes report
     */
    public function getAllUsedCtypes(bool $displayHidden = false, ?string $filter = null): array
    {
        return $this->contentUsageRepository->findContentTypes($displayHidden, $filter);
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
        return 'module-reports';
    }
}
