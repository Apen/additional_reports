<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Service;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SlidingWindowPagination;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewInterface;

final readonly class PaginationService
{
    public function __construct(private ?ExtensionConfiguration $extensionConfiguration = null) {}

    /** @param array<int, mixed> $items */
    public function assign(array $items, int $currentPage, ViewInterface $view): void
    {
        if ($items === []) {
            return;
        }

        $arrayPaginator = new ArrayPaginator($items, max(1, $currentPage), $this->getItemsPerPage());
        $view->assign('paginator', $arrayPaginator);
        $view->assign('pagination', new SlidingWindowPagination($arrayPaginator, 5));
    }

    private function getItemsPerPage(): int
    {
        try {
            $configuration = $this->extensionConfiguration ?? GeneralUtility::makeInstance(ExtensionConfiguration::class);
            $itemsPerPage = (int) $configuration->get('additional_reports', 'itemsPerPage');
            return $itemsPerPage > 0 ? $itemsPerPage : 10;
        } catch (\Exception) {
            return 10;
        }
    }
}
