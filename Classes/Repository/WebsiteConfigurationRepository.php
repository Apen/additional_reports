<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final readonly class WebsiteConfigurationRepository
{
    public function __construct(private ?ConnectionPool $connectionPool = null) {}

    /** @return list<array{uid: int, title: string}> */
    public function findVisibleRootPages(): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('pages');
        $rows = $queryBuilder
            ->select('uid', 'title')
            ->from('pages')
            ->where($queryBuilder->expr()->eq('is_siteroot', 1))
            ->andWhere($queryBuilder->expr()->eq('hidden', 0))
            ->andWhere($queryBuilder->expr()->neq('pid', -1))
            ->orderBy('uid')
            ->executeQuery()
            ->fetchAllAssociative();

        return array_map(static fn(array $row): array => [
            'uid' => (int) ($row['uid'] ?? 0),
            'title' => (string) ($row['title'] ?? ''),
        ], $rows);
    }

    /** @return list<array{uid: int, title: string, root: bool}> */
    public function findVisibleTemplates(int $pageId): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('sys_template');
        $rows = $queryBuilder
            ->select('uid', 'title', 'root')
            ->from('sys_template')
            ->where($queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageId)))
            ->andWhere($queryBuilder->expr()->eq('hidden', 0))
            ->orderBy('sorting')
            ->executeQuery()
            ->fetchAllAssociative();

        return array_map(static fn(array $row): array => [
            'uid' => (int) ($row['uid'] ?? 0),
            'title' => (string) ($row['title'] ?? ''),
            'root' => (bool) ($row['root'] ?? false),
        ], $rows);
    }

    private function getConnectionPool(): ConnectionPool
    {
        return $this->connectionPool ?? GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
