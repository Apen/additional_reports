<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Repository;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final readonly class DatabaseStatusRepository
{
    public function __construct(private ?ConnectionPool $connectionPool = null) {}

    /**
     * @return array{
     *     version: string,
     *     database: string,
     *     defaultCharacterSet: string,
     *     defaultCollation: string,
     *     tables: list<array{name: string, engine: string, collation: string, rows: int, size: float}>,
     *     totalSize: float
     * }
     */
    public function getStatus(): array
    {
        $connectionPool = $this->connectionPool ?? GeneralUtility::makeInstance(ConnectionPool::class);
        $connection = $connectionPool->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
        $params = $connection->getParams();
        $databaseName = is_string($params['dbname'] ?? null) ? $params['dbname'] : '';
        $status = [
            'version' => $connection->getServerVersion(),
            'database' => $databaseName,
            'defaultCharacterSet' => '',
            'defaultCollation' => '',
            'tables' => [],
            'totalSize' => 0.0,
        ];

        if (! $connection->getDatabasePlatform() instanceof AbstractMySQLPlatform || $databaseName === '') {
            return $status;
        }

        $schema = $connection->createQueryBuilder()
            ->select('default_character_set_name', 'default_collation_name')
            ->from('information_schema.schemata')
            ->where('schema_name = :databaseName')
            ->setParameter('databaseName', $databaseName)
            ->executeQuery()
            ->fetchAssociative();
        if (is_array($schema)) {
            $status['defaultCharacterSet'] = (string) ($schema['default_character_set_name'] ?? '');
            $status['defaultCollation'] = (string) ($schema['default_collation_name'] ?? '');
        }

        $rows = $connection->createQueryBuilder()
            ->select('table_name', 'engine', 'table_collation', 'table_rows')
            ->addSelect('((data_length + index_length) / 1024 / 1024) AS table_size')
            ->from('information_schema.tables')
            ->where('table_schema = :databaseName')
            ->setParameter('databaseName', $databaseName)
            ->orderBy('table_name')
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($rows as $row) {
            $tableSize = round((float) ($row['table_size'] ?? 0), 2);
            $status['tables'][] = [
                'name' => (string) ($row['table_name'] ?? ''),
                'engine' => (string) ($row['engine'] ?? ''),
                'collation' => (string) ($row['table_collation'] ?? ''),
                'rows' => (int) ($row['table_rows'] ?? 0),
                'size' => $tableSize,
            ];
            $status['totalSize'] += $tableSize;
        }
        $status['totalSize'] = round($status['totalSize'], 2);

        return $status;
    }
}
