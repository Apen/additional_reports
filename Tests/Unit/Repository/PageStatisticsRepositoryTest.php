<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit\Repository;

use PHPUnit\Framework\TestCase;
use Sng\AdditionalReports\Repository\PageStatisticsRepository;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;

final class PageStatisticsRepositoryTest extends TestCase
{
    public function testRecursivePageIdentifiersAreNormalizedToIntegers(): void
    {
        $pageRepository = $this->createMock(PageRepository::class);
        $pageRepository->expects(self::once())
            ->method('getPageIdsRecursive')
            ->with([12], 3)
            ->willReturn(['12', 24, '36']);

        self::assertSame(
            [12, 24, 36],
            (new PageStatisticsRepository($pageRepository))->findPageIdsRecursive(12, 3),
        );
    }

    public function testEmptyPageListDoesNotQueryDatabase(): void
    {
        self::assertSame(0, (new PageStatisticsRepository())->countByFlag([], 'hidden'));
    }

    public function testUnsupportedPageFlagIsRejectedBeforeDatabaseAccess(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported page field: deleted');

        (new PageStatisticsRepository())->countByFlag([1], 'deleted');
    }
}
