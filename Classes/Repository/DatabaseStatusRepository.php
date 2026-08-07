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
            $schemaSummary = $this->summarizeSchema($schema);
            $status['defaultCharacterSet'] = $schemaSummary['defaultCharacterSet'];
            $status['defaultCollation'] = $schemaSummary['defaultCollation'];
        }

        $rows = $connection->createQueryBuilder()
            ->select('table_name', 'engine', 'table_collation', 'table_rows', 'data_length', 'index_length')
            ->from('information_schema.tables')
            ->where('table_schema = :databaseName')
            ->setParameter('databaseName', $databaseName)
            ->orderBy('table_name')
            ->executeQuery()
            ->fetchAllAssociative();

        $tableSummary = $this->summarizeTables($rows);
        $status['tables'] = $tableSummary['tables'];
        $status['totalSize'] = $tableSummary['totalSize'];

        return $status;
    }

    /**
     * @param array<string, mixed> $row
     * @return array{defaultCharacterSet: string, defaultCollation: string}
     */
    public function summarizeSchema(array $row): array
    {
        $row = array_change_key_case($row, CASE_LOWER);
        return [
            'defaultCharacterSet' => (string) ($row['default_character_set_name'] ?? ''),
            'defaultCollation' => (string) ($row['default_collation_name'] ?? ''),
        ];
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return array{
     *     tables: list<array{name: string, engine: string, collation: string, rows: int, size: float}>,
     *     totalSize: float
     * }
     */
    public function summarizeTables(array $rows): array
    {
        $tables = [];
        $totalSize = 0.0;
        foreach ($rows as $row) {
            $row = array_change_key_case($row, CASE_LOWER);
            $tableSize = round(((float) ($row['data_length'] ?? 0) + (float) ($row['index_length'] ?? 0)) / 1024 / 1024, 2);
            $tables[] = [
                'name' => (string) ($row['table_name'] ?? ''),
                'engine' => (string) ($row['engine'] ?? ''),
                'collation' => (string) ($row['table_collation'] ?? ''),
                'rows' => (int) ($row['table_rows'] ?? 0),
                'size' => $tableSize,
            ];
            $totalSize += $tableSize;
        }
        return [
            'tables' => $tables,
            'totalSize' => round($totalSize, 2),
        ];
    }
}
