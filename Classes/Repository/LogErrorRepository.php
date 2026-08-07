<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final readonly class LogErrorRepository
{
    public function __construct(private ?ConnectionPool $connectionPool = null) {}

    /** @return list<array{details: string, nb: int, tstamp: int}> */
    public function findGrouped(?string $orderBy = null): array
    {
        $queryBuilder = ($this->connectionPool ?? GeneralUtility::makeInstance(ConnectionPool::class))
            ->getQueryBuilderForTable('sys_log');
        $queryBuilder
            ->select('details')
            ->addSelectLiteral('COUNT(*) AS nb', 'MAX(tstamp) AS tstamp')
            ->from('sys_log')
            ->where($queryBuilder->expr()->gt('error', 0))
            ->groupBy('details');

        $allowedOrderings = [
            'nb ASC' => ['nb', 'ASC'],
            'nb DESC' => ['nb', 'DESC'],
            'tstamp ASC' => ['tstamp', 'ASC'],
            'tstamp DESC' => ['tstamp', 'DESC'],
        ];
        [$orderField, $orderDirection] = $allowedOrderings[$orderBy ?? ''] ?? ['nb', 'DESC'];

        $rows = $queryBuilder
            ->orderBy($orderField, $orderDirection)
            ->addOrderBy('tstamp', 'DESC')
            ->executeQuery()
            ->fetchAllAssociative();

        return array_map(static fn(array $row): array => [
            'details' => (string) ($row['details'] ?? ''),
            'nb' => (int) ($row['nb'] ?? 0),
            'tstamp' => (int) ($row['tstamp'] ?? 0),
        ], $rows);
    }
}
