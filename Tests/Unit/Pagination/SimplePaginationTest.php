<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit\Pagination;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sng\AdditionalReports\Pagination\SimplePagination;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;

final class SimplePaginationTest extends TestCase
{
    public function testGeneratesCenteredPageRange(): void
    {
        $subject = $this->createSubject(50, 5, 5);

        self::assertSame([3, 4, 5, 6, 7], array_column($subject->getPages(), 'number'));
        self::assertSame(range(1, 10), $subject->getAllPageNumbers());
        self::assertTrue($subject->getHasLessPages());
        self::assertTrue($subject->getHasMorePages());
        self::assertSame(4, $subject->getPreviousPageNumber());
        self::assertSame(6, $subject->getNextPageNumber());
        self::assertSame(21, $subject->getStartRecordNumber());
        self::assertSame(25, $subject->getEndRecordNumber());
    }

    #[DataProvider('edgePageProvider')]
    public function testHandlesFirstAndLastPage(int $page, ?int $previous, ?int $next, array $numbers): void
    {
        $subject = $this->createSubject(50, $page, 5);

        self::assertSame($previous, $subject->getPreviousPageNumber());
        self::assertSame($next, $subject->getNextPageNumber());
        self::assertSame($numbers, array_column($subject->getPages(), 'number'));
    }

    public static function edgePageProvider(): iterable
    {
        yield 'first page' => [1, null, 2, [1, 2, 3, 4, 5]];
        yield 'last page' => [10, 9, null, [6, 7, 8, 9, 10]];
    }

    public function testMaximumNumberOfLinksCanBeChanged(): void
    {
        $paginator = new ArrayPaginator(range(1, 100), 10, 5);
        $subject = new SimplePagination($paginator);
        $subject->setMaximumNumberOfLinks(3);
        $subject->generate();

        self::assertCount(3, $subject->getPages());
        self::assertSame([9, 10, 11], array_column($subject->getPages(), 'number'));
    }

    public function testOutOfRangePageHasNoRecordNumbers(): void
    {
        $paginator = $this->createMock(\TYPO3\CMS\Core\Pagination\PaginatorInterface::class);
        $paginator->method('getCurrentPageNumber')
            ->willReturn(3);
        $paginator->method('getNumberOfPages')
            ->willReturn(2);
        $subject = new SimplePagination($paginator);

        self::assertSame(0, $subject->getStartRecordNumber());
        self::assertSame(0, $subject->getEndRecordNumber());
    }

    public function testLimitsPageRangeWhenThereAreFewerPagesThanLinks(): void
    {
        $subject = $this->createSubject(6, 2, 5);

        self::assertSame([1, 2], array_column($subject->getPages(), 'number'));
        self::assertFalse($subject->getHasLessPages());
        self::assertFalse($subject->getHasMorePages());
        self::assertSame(1, $subject->getFirstPageNumber());
        self::assertSame(2, $subject->getLastPageNumber());
    }

    private function createSubject(int $numberOfItems, int $currentPage, int $itemsPerPage): SimplePagination
    {
        $subject = new SimplePagination(new ArrayPaginator(range(1, $numberOfItems), $currentPage, $itemsPerPage));
        $subject->generate();

        return $subject;
    }
}
