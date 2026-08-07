<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Service;

use TYPO3\CMS\Core\Database\Schema\Parser\Lexer;
use TYPO3\CMS\Core\Database\Schema\Parser\Parser;
use TYPO3\CMS\Core\Database\Schema\SqlReader;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final readonly class ExtensionSchemaParser
{
    public function __construct(private ?SqlReader $sqlReader = null) {}

    /** @return list<array{name: string, columns: list<string>}> */
    public function parse(string $sql): array
    {
        if (trim($sql) === '') {
            return [];
        }

        $parser = new Parser(new Lexer());
        $sqlReader = $this->sqlReader ?? GeneralUtility::makeInstance(SqlReader::class);
        $tables = [];
        foreach ($sqlReader->getCreateTableStatementArray($sql) as $statement) {
            try {
                $parsedTables = $parser->parse($statement);
            } catch (\Throwable) {
                continue;
            }
            foreach ($parsedTables as $table) {
                $tables[] = [
                    'name' => $table->getName(),
                    'columns' => array_map(static fn($column): string => $column->getName(), $table->getColumns()),
                ];
            }
        }
        return $tables;
    }
}
