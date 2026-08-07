<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Repository;

use Doctrine\DBAL\ArrayParameterType;
use Sng\AdditionalReports\Service\ContentTypeResolver;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final readonly class ContentUsageRepository
{
    public function __construct(
        private ?ConnectionPool $connectionPool = null,
        private ?ContentTypeResolver $contentTypeResolver = null,
    ) {}

    public function findDistinctPlugins(bool $includeHidden = false): array
    {
        $plugins = [];
        $pluginContentTypes = $this->getContentTypeResolver()->getPluginContentTypes();
        if ($pluginContentTypes !== []) {
            $queryBuilder = $this->createQueryBuilder($includeHidden);
            $plugins = array_map(static function (array $row): array {
                $row['pluginIdentifier'] = (string) ($row['CType'] ?? '');
                return $row;
            }, $queryBuilder
                ->select('tt_content.CType')
                ->distinct()
                ->andWhere($queryBuilder->expr()->in('tt_content.CType', $queryBuilder->createNamedParameter($pluginContentTypes, ArrayParameterType::STRING)))
                ->orderBy('tt_content.CType')
                ->executeQuery()
                ->fetchAllAssociative());
        }
        if (! $this->getContentTypeResolver()->hasLegacyListType()) {
            return $plugins;
        }
        $queryBuilder = $this->createQueryBuilder($includeHidden);
        $legacyPlugins = array_map(static function (array $row): array {
            $row['pluginIdentifier'] = (string) ($row['list_type'] ?? '');
            return $row;
        }, $queryBuilder
            ->select('tt_content.list_type')
            ->distinct()
            ->andWhere($queryBuilder->expr()->eq('tt_content.CType', $queryBuilder->createNamedParameter('list')))
            ->andWhere($queryBuilder->expr()->neq('tt_content.list_type', $queryBuilder->createNamedParameter('')))
            ->orderBy('tt_content.list_type')
            ->executeQuery()
            ->fetchAllAssociative());
        return [...$plugins, ...$legacyPlugins];
    }

    public function findDistinctContentTypes(bool $includeHidden = false): array
    {
        $queryBuilder = $this->createQueryBuilder($includeHidden);
        $queryBuilder->select('tt_content.CType')->distinct()
            ->andWhere($queryBuilder->expr()->neq('tt_content.CType', $queryBuilder->createNamedParameter('')));
        if ($this->getContentTypeResolver()->hasLegacyListType()) {
            $queryBuilder->andWhere($queryBuilder->expr()->neq('tt_content.CType', $queryBuilder->createNamedParameter('list')));
        }
        $pluginContentTypes = $this->getContentTypeResolver()->getPluginContentTypes();
        if ($pluginContentTypes !== []) {
            $queryBuilder->andWhere($queryBuilder->expr()->notIn('tt_content.CType', $queryBuilder->createNamedParameter($pluginContentTypes, ArrayParameterType::STRING)));
        }
        $queryBuilder->orderBy('tt_content.CType');
        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    public function findPlugins(bool $includeHidden = false, ?string $filter = null): array
    {
        $plugins = [];
        $pluginContentTypes = $this->getContentTypeResolver()->getPluginContentTypes();
        if ($pluginContentTypes !== [] && ($filter === null || $filter === 'all' || in_array($filter, $pluginContentTypes, true))) {
            $queryBuilder = $this->createQueryBuilder($includeHidden);
            $queryBuilder->select('tt_content.CType', 'tt_content.pid', 'tt_content.uid', 'pages.title')
                ->addSelectLiteral('pages.hidden AS hiddenpages', 'tt_content.hidden AS hiddentt_content')
                ->distinct()
                ->andWhere($queryBuilder->expr()->in('tt_content.CType', $queryBuilder->createNamedParameter($pluginContentTypes, ArrayParameterType::STRING)))
                ->orderBy('tt_content.CType')->addOrderBy('tt_content.pid');
            if ($filter !== null && $filter !== 'all') {
                $queryBuilder->andWhere($queryBuilder->expr()->eq('tt_content.CType', $queryBuilder->createNamedParameter($filter)));
            }
            $plugins = array_map(static function (array $row): array {
                $row['pluginIdentifier'] = (string) ($row['CType'] ?? '');
                return $row;
            }, $queryBuilder->executeQuery()->fetchAllAssociative());
        }
        if (! $this->getContentTypeResolver()->hasLegacyListType()) {
            return $plugins;
        }
        $queryBuilder = $this->createQueryBuilder($includeHidden);
        $queryBuilder->select('tt_content.list_type', 'tt_content.pid', 'tt_content.uid', 'pages.title')
            ->addSelectLiteral('pages.hidden AS hiddenpages', 'tt_content.hidden AS hiddentt_content')
            ->distinct()
            ->andWhere($queryBuilder->expr()->eq('tt_content.CType', $queryBuilder->createNamedParameter('list')))
            ->orderBy('tt_content.list_type')->addOrderBy('tt_content.pid');
        if ($filter !== null && $filter !== 'all') {
            $queryBuilder->andWhere($queryBuilder->expr()->eq('tt_content.list_type', $queryBuilder->createNamedParameter($filter)));
        }
        $legacyPlugins = array_map(static function (array $row): array {
            $row['pluginIdentifier'] = (string) ($row['list_type'] ?? '');
            return $row;
        }, $queryBuilder->executeQuery()->fetchAllAssociative());
        return [...$plugins, ...$legacyPlugins];
    }

    public function findContentTypes(bool $includeHidden = false, ?string $filter = null): array
    {
        $queryBuilder = $this->createQueryBuilder($includeHidden);
        $queryBuilder->select('tt_content.CType', 'tt_content.pid', 'tt_content.uid', 'pages.title')
            ->addSelectLiteral('pages.hidden AS hiddenpages', 'tt_content.hidden AS hiddentt_content')
            ->distinct()
            ->andWhere($queryBuilder->expr()->neq('tt_content.CType', $queryBuilder->createNamedParameter($this->getContentTypeResolver()->hasLegacyListType() ? 'list' : '')))
            ->orderBy('tt_content.CType')->addOrderBy('tt_content.pid');
        $pluginContentTypes = $this->getContentTypeResolver()->getPluginContentTypes();
        if ($pluginContentTypes !== []) {
            $queryBuilder->andWhere($queryBuilder->expr()->notIn('tt_content.CType', $queryBuilder->createNamedParameter($pluginContentTypes, ArrayParameterType::STRING)));
        }
        if ($filter !== null && $filter !== 'all') {
            $queryBuilder->andWhere($queryBuilder->expr()->eq('tt_content.CType', $queryBuilder->createNamedParameter($filter)));
        }
        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    /**
     * @return array{total: int, items: list<array{CType: string, list_type?: string, count: int}>}
     */
    public function summarizeVisibleContent(): array
    {
        $queryBuilder = $this->createQueryBuilder(false);
        $total = (int) $queryBuilder
            ->count('tt_content.uid')
            ->executeQuery()
            ->fetchOne();

        $queryBuilder = $this->createQueryBuilder(false);
        $queryBuilder
            ->select('tt_content.CType')
            ->addSelectLiteral('COUNT(*) AS item_count')
            ->groupBy('tt_content.CType')
            ->orderBy('item_count', 'DESC');
        if ($this->getContentTypeResolver()->hasLegacyListType()) {
            $queryBuilder->addSelect('tt_content.list_type')->addGroupBy('tt_content.list_type');
        }

        $items = array_map(
            static function (array $item): array {
                $item['CType'] = (string) ($item['CType'] ?? '');
                if (array_key_exists('list_type', $item)) {
                    $item['list_type'] = (string) $item['list_type'];
                }
                $item['count'] = (int) ($item['item_count'] ?? 0);
                unset($item['item_count']);
                return $item;
            },
            $queryBuilder->executeQuery()->fetchAllAssociative(),
        );

        return ['total' => $total, 'items' => $items];
    }

    private function getContentTypeResolver(): ContentTypeResolver
    {
        return $this->contentTypeResolver ?? GeneralUtility::makeInstance(ContentTypeResolver::class);
    }

    private function createQueryBuilder(bool $includeHidden): QueryBuilder
    {
        $connectionPool = $this->connectionPool ?? GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->from('tt_content')
            ->innerJoin('tt_content', 'pages', 'pages', 'tt_content.pid = pages.uid')
            ->where($queryBuilder->expr()->gte('pages.pid', 0));
        if (! $includeHidden) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq('tt_content.hidden', 0))
                ->andWhere($queryBuilder->expr()->eq('pages.hidden', 0));
        }
        return $queryBuilder;
    }
}
