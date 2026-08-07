<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit\Repository;

use Doctrine\DBAL\Driver\Result as DriverResult;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\TestCase;
use Sng\AdditionalReports\Repository\DatabaseStatusRepository;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

final class DatabaseStatusRepositoryTest extends TestCase
{
    public function testSchemaColumnNamesAreCaseInsensitive(): void
    {
        self::assertSame([
            'defaultCharacterSet' => 'utf8mb4',
            'defaultCollation' => 'utf8mb4_unicode_ci',
        ], (new DatabaseStatusRepository())->summarizeSchema([
            'DEFAULT_CHARACTER_SET_NAME' => 'utf8mb4',
            'DEFAULT_COLLATION_NAME' => 'utf8mb4_unicode_ci',
        ]));
    }

    public function testTableSizesAreCalculatedFromDatabaseLengths(): void
    {
        $result = (new DatabaseStatusRepository())->summarizeTables([
            [
                'table_name' => 'pages',
                'engine' => 'InnoDB',
                'table_collation' => 'utf8mb4_unicode_ci',
                'table_rows' => '12',
                'data_length' => '1048576',
                'index_length' => '524288',
            ],
            [
                'TABLE_NAME' => 'tt_content',
                'ENGINE' => 'InnoDB',
                'TABLE_COLLATION' => 'utf8mb4_unicode_ci',
                'TABLE_ROWS' => 8,
                'DATA_LENGTH' => 262144,
                'INDEX_LENGTH' => null,
            ],
        ]);

        self::assertSame([
            [
                'name' => 'pages',
                'engine' => 'InnoDB',
                'collation' => 'utf8mb4_unicode_ci',
                'rows' => 12,
                'size' => 1.5,
            ],
            [
                'name' => 'tt_content',
                'engine' => 'InnoDB',
                'collation' => 'utf8mb4_unicode_ci',
                'rows' => 8,
                'size' => 0.25,
            ],
        ], $result['tables']);
        self::assertSame(1.75, $result['totalSize']);
    }

    public function testMissingLengthValuesProduceAnEmptySize(): void
    {
        $result = (new DatabaseStatusRepository())->summarizeTables([[]]);

        self::assertSame(0.0, $result['tables'][0]['size']);
        self::assertSame(0.0, $result['totalSize']);
    }

    public function testMySqlStatusCombinesSchemaAndTableInformation(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('getParams')->willReturn(['dbname' => 'example']);
        $connection->method('getServerVersion')->willReturn('8.0.42');
        $connection->method('getDatabasePlatform')->willReturn(new MySQLPlatform());

        $schemaResult = $this->createMock(DriverResult::class);
        $schemaResult->method('fetchAssociative')->willReturn([
            'default_character_set_name' => 'utf8mb4',
            'default_collation_name' => 'utf8mb4_unicode_ci',
        ]);
        $tableResult = $this->createMock(DriverResult::class);
        $tableResult->method('fetchAllAssociative')->willReturn([[
            'table_name' => 'pages',
            'engine' => 'InnoDB',
            'table_collation' => 'utf8mb4_unicode_ci',
            'table_rows' => 12,
            'data_length' => 1048576,
            'index_length' => 0,
        ]]);

        $schemaQuery = $this->createQueryBuilder(new Result($schemaResult, $connection));
        $tableQuery = $this->createQueryBuilder(new Result($tableResult, $connection));
        $connection->expects(self::exactly(2))
            ->method('createQueryBuilder')
            ->willReturnOnConsecutiveCalls($schemaQuery, $tableQuery);

        $connectionPool = $this->createMock(ConnectionPool::class);
        $connectionPool->expects(self::once())
            ->method('getConnectionByName')
            ->with(ConnectionPool::DEFAULT_CONNECTION_NAME)
            ->willReturn($connection);

        self::assertSame([
            'version' => '8.0.42',
            'database' => 'example',
            'defaultCharacterSet' => 'utf8mb4',
            'defaultCollation' => 'utf8mb4_unicode_ci',
            'tables' => [[
                'name' => 'pages',
                'engine' => 'InnoDB',
                'collation' => 'utf8mb4_unicode_ci',
                'rows' => 12,
                'size' => 1.0,
            ]],
            'totalSize' => 1.0,
        ], (new DatabaseStatusRepository($connectionPool))->getStatus());
    }

    private function createQueryBuilder(Result $result): QueryBuilder
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        foreach (['select', 'from', 'where', 'setParameter', 'orderBy'] as $method) {
            $queryBuilder->method($method)->willReturnSelf();
        }
        $queryBuilder->method('executeQuery')->willReturn($result);
        return $queryBuilder;
    }
}
