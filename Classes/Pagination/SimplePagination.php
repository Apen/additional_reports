<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Pagination;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Pagination\PaginationInterface;
use TYPO3\CMS\Core\Pagination\PaginatorInterface;

class SimplePagination implements PaginationInterface
{
    /**
     * @var PaginatorInterface
     */
    protected $paginator;

    /**
     * @var int
     */
    protected $maximumNumberOfLinks = 5;

    /**
     * @var int|float
     */
    protected $displayRangeStart;

    /**
     * @var int|float
     */
    protected $displayRangeEnd;

    /**
     * @var array
     */
    protected $pages = [];

    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }

    public function getPreviousPageNumber(): ?int
    {
        $previousPage = $this->paginator->getCurrentPageNumber() - 1;

        if ($previousPage > $this->paginator->getNumberOfPages()) {
            return null;
        }

        return $previousPage >= $this->getFirstPageNumber()
            ? $previousPage
            : null;
    }

    public function getNextPageNumber(): ?int
    {
        $nextPage = $this->paginator->getCurrentPageNumber() + 1;

        return $nextPage <= $this->paginator->getNumberOfPages()
            ? $nextPage
            : null;
    }

    public function generate(): void
    {
        $this->calculateDisplayRange();
        $pages = [];
        for ($i = $this->displayRangeStart; $i <= $this->displayRangeEnd; $i++) {
            $pages[] = ['number' => $i, 'isCurrent' => $i === $this->paginator->getCurrentPageNumber()];
        }
        $this->pages = $pages;
    }

    public function getFirstPageNumber(): int
    {
        return 1;
    }

    public function getLastPageNumber(): int
    {
        return $this->paginator->getNumberOfPages();
    }

    public function getStartRecordNumber(): int
    {
        if ($this->paginator->getCurrentPageNumber() > $this->paginator->getNumberOfPages()) {
            return 0;
        }

        return $this->paginator->getKeyOfFirstPaginatedItem() + 1;
    }

    public function getEndRecordNumber(): int
    {
        if ($this->paginator->getCurrentPageNumber() > $this->paginator->getNumberOfPages()) {
            return 0;
        }

        return $this->paginator->getKeyOfLastPaginatedItem() + 1;
    }

    /**
     * If a certain number of links should be displayed, adjust before and after
     * amounts accordingly.
     */
    protected function calculateDisplayRange(): void
    {
        $maximumNumberOfLinks = $this->maximumNumberOfLinks;
        if ($maximumNumberOfLinks > $this->paginator->getNumberOfPages()) {
            $maximumNumberOfLinks = $this->paginator->getNumberOfPages();
        }
        $delta = floor($maximumNumberOfLinks / 2);
        $this->displayRangeStart = $this->paginator->getCurrentPageNumber() - $delta;
        $this->displayRangeEnd = $this->paginator->getCurrentPageNumber() + $delta - ($maximumNumberOfLinks % 2 === 0 ? 1 : 0);
        if ($this->displayRangeStart < 1) {
            $this->displayRangeEnd -= $this->displayRangeStart - 1;
        }
        if ($this->displayRangeEnd > $this->paginator->getNumberOfPages()) {
            $this->displayRangeStart -= $this->displayRangeEnd - $this->paginator->getNumberOfPages();
        }
        $this->displayRangeStart = (int)max($this->displayRangeStart, 1);
        $this->displayRangeEnd = (int)min($this->displayRangeEnd, $this->paginator->getNumberOfPages());
    }

    public function setMaximumNumberOfLinks(int $maximumNumberOfLinks): void
    {
        $this->maximumNumberOfLinks = $maximumNumberOfLinks;
    }

    public function getPages(): array
    {
        return $this->pages;
    }

    public function getHasLessPages(): bool
    {
        return $this->displayRangeStart > 2;
    }

    public function getHasMorePages(): bool
    {
        return $this->displayRangeEnd + 1 < $this->paginator->getNumberOfPages();
    }

    public function getAllPageNumbers(): array
    {
        $pageNumbers = [];
        for ($i = 1; $i <= $this->paginator->getNumberOfPages(); $i++) {
            $pageNumbers[] = $i;
        }
        return $pageNumbers;
    }
}