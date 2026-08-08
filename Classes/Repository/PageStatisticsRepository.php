<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Repository;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final readonly class PageStatisticsRepository
{
    public function __construct(
        private ?PageRepository $pageRepository = null,
        private ?ConnectionPool $connectionPool = null,
    ) {}

    /** @return list<int> */
    public function findPageIdsRecursive(int $pageId, int $depth): array
    {
        $pageRepository = $this->pageRepository ?? GeneralUtility::makeInstance(PageRepository::class);
        return array_values(array_map(intval(...), $pageRepository->getPageIdsRecursive([$pageId], $depth)));
    }

    /** @param list<int> $pageIds */
    public function countByFlag(array $pageIds, string $field): int
    {
        if (! in_array($field, ['hidden', 'no_search'], true)) {
            throw new \InvalidArgumentException('Unsupported page field: ' . $field, 9251298027);
        }

        if ($pageIds === []) {
            return 0;
        }

        $connectionPool = $this->connectionPool ?? GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('pages');
        return (int) $queryBuilder->count('uid')->from('pages')
            ->where($queryBuilder->expr()->in('uid', $queryBuilder->createNamedParameter($pageIds, Connection::PARAM_INT_ARRAY)))
            ->andWhere($queryBuilder->expr()->eq($field, 1))
            ->executeQuery()
            ->fetchOne();
    }
}
