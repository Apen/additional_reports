<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Sng\AdditionalReports\Service\PaginationService;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SlidingWindowPagination;
use TYPO3\CMS\Core\View\ViewInterface;

final class PaginationServiceTest extends TestCase
{
    public function testEmptyItemsDoNotAssignPagination(): void
    {
        $view = $this->createMock(ViewInterface::class);
        $view->expects(self::never())->method('assign');

        (new PaginationService())->assign([], 1, $view);
    }

    public function testConfiguredPaginationIsAssigned(): void
    {
        $configuration = $this->createMock(ExtensionConfiguration::class);
        $configuration->method('get')->with('additional_reports', 'itemsPerPage')->willReturn(2);
        $view = $this->createMock(ViewInterface::class);
        $assignedValues = [];
        $view->expects(self::exactly(2))->method('assign')->willReturnCallback(
            static function (string $key, mixed $value) use (&$assignedValues, $view): ViewInterface {
                $assignedValues[$key] = $value;
                return $view;
            },
        );

        (new PaginationService($configuration))->assign([1, 2, 3], 2, $view);

        self::assertInstanceOf(ArrayPaginator::class, $assignedValues['paginator']);
        self::assertSame([3], $assignedValues['paginator']->getPaginatedItems());
        self::assertInstanceOf(SlidingWindowPagination::class, $assignedValues['pagination']);
    }
}
