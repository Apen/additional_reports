<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit\Repository;

use PHPUnit\Framework\TestCase;
use Sng\AdditionalReports\Repository\DatabaseStatusRepository;

final class DatabaseStatusRepositoryTest extends TestCase
{
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
                'table_name' => 'tt_content',
                'engine' => 'InnoDB',
                'table_collation' => 'utf8mb4_unicode_ci',
                'table_rows' => 8,
                'data_length' => 262144,
                'index_length' => null,
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
}
