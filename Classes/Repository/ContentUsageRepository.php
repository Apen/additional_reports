<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Repository;

use Doctrine\DBAL\ArrayParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final readonly class ContentUsageRepository
{
    public function __construct(
        private ?ConnectionPool $connectionPool = null,
        private ?Typo3Version $typo3Version = null,
    ) {}

    public function findDistinctPlugins(bool $includeHidden = false): array
    {
        if (! $this->hasLegacyListType()) {
            $pluginContentTypes = $this->getPluginContentTypes();
            if ($pluginContentTypes === []) {
                return [];
            }
            $queryBuilder = $this->createQueryBuilder($includeHidden);
            return $queryBuilder
                ->select('tt_content.CType')
                ->distinct()
                ->andWhere($queryBuilder->expr()->in('tt_content.CType', $queryBuilder->createNamedParameter($pluginContentTypes, ArrayParameterType::STRING)))
                ->orderBy('tt_content.CType')
                ->executeQuery()
                ->fetchAllAssociative();
        }
        $queryBuilder = $this->createQueryBuilder($includeHidden);
        return $queryBuilder
            ->select('tt_content.list_type')
            ->distinct()
            ->andWhere($queryBuilder->expr()->eq('tt_content.CType', $queryBuilder->createNamedParameter('list')))
            ->andWhere($queryBuilder->expr()->neq('tt_content.list_type', $queryBuilder->createNamedParameter('')))
            ->orderBy('tt_content.list_type')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    public function findDistinctContentTypes(bool $includeHidden = false): array
    {
        $queryBuilder = $this->createQueryBuilder($includeHidden);
        $queryBuilder->select('tt_content.CType')->distinct()
            ->andWhere($queryBuilder->expr()->neq('tt_content.CType', $queryBuilder->createNamedParameter('')));
        if ($this->hasLegacyListType()) {
            $queryBuilder->addSelect('tt_content.list_type')
                ->andWhere($queryBuilder->expr()->neq('tt_content.CType', $queryBuilder->createNamedParameter('list')))
                ->orderBy('tt_content.list_type');
        } else {
            $pluginContentTypes = $this->getPluginContentTypes();
            if ($pluginContentTypes !== []) {
                $queryBuilder->andWhere($queryBuilder->expr()->notIn('tt_content.CType', $queryBuilder->createNamedParameter($pluginContentTypes, ArrayParameterType::STRING)));
            }
            $queryBuilder->orderBy('tt_content.CType');
        }
        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    public function findPlugins(bool $includeHidden = false, ?string $filter = null): array
    {
        if (! $this->hasLegacyListType()) {
            $pluginContentTypes = $this->getPluginContentTypes();
            if ($pluginContentTypes === [] || ($filter !== null && $filter !== 'all' && ! in_array($filter, $pluginContentTypes, true))) {
                return [];
            }
            $queryBuilder = $this->createQueryBuilder($includeHidden);
            $queryBuilder->select('tt_content.CType', 'tt_content.pid', 'tt_content.uid', 'pages.title')
                ->addSelectLiteral('pages.hidden AS hiddenpages', 'tt_content.hidden AS hiddentt_content')
                ->distinct()
                ->andWhere($queryBuilder->expr()->in('tt_content.CType', $queryBuilder->createNamedParameter($pluginContentTypes, ArrayParameterType::STRING)))
                ->orderBy('tt_content.CType')->addOrderBy('tt_content.pid');
            if ($filter !== null && $filter !== 'all') {
                $queryBuilder->andWhere($queryBuilder->expr()->eq('tt_content.CType', $queryBuilder->createNamedParameter($filter)));
            }
            return $queryBuilder->executeQuery()->fetchAllAssociative();
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
        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    public function findContentTypes(bool $includeHidden = false, ?string $filter = null): array
    {
        $queryBuilder = $this->createQueryBuilder($includeHidden);
        $queryBuilder->select('tt_content.CType', 'tt_content.pid', 'tt_content.uid', 'pages.title')
            ->addSelectLiteral('pages.hidden AS hiddenpages', 'tt_content.hidden AS hiddentt_content')
            ->distinct()
            ->andWhere($queryBuilder->expr()->neq('tt_content.CType', $queryBuilder->createNamedParameter($this->hasLegacyListType() ? 'list' : '')))
            ->orderBy('tt_content.CType')->addOrderBy('tt_content.pid');
        if (! $this->hasLegacyListType()) {
            $pluginContentTypes = $this->getPluginContentTypes();
            if ($pluginContentTypes !== []) {
                $queryBuilder->andWhere($queryBuilder->expr()->notIn('tt_content.CType', $queryBuilder->createNamedParameter($pluginContentTypes, ArrayParameterType::STRING)));
            }
        }
        if ($filter !== null && $filter !== 'all') {
            $queryBuilder->andWhere($queryBuilder->expr()->eq('tt_content.CType', $queryBuilder->createNamedParameter($filter)));
        }
        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    public function hasLegacyListType(): bool
    {
        $typo3Version = $this->typo3Version ?? new Typo3Version();
        return $typo3Version->getMajorVersion() < 14
            && isset($GLOBALS['TCA']['tt_content']['columns']['list_type']);
    }

    /** @return list<string> */
    public function getPluginContentTypes(): array
    {
        $contentTypeGroups = ['default', 'lists', 'menu', 'forms', 'special'];
        $pluginContentTypes = [];
        foreach (($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] ?? []) as $item) {
            $value = $item['value'] ?? $item[1] ?? null;
            $group = $item['group'] ?? $item[3] ?? 'default';
            if (is_string($value) && $value !== '' && ! in_array($group, $contentTypeGroups, true)) {
                $pluginContentTypes[] = $value;
            }
        }
        return array_values(array_unique($pluginContentTypes));
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
