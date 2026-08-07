<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit\ViewHelpers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sng\AdditionalReports\ViewHelpers\InListViewHelper;

final class InListViewHelperTest extends TestCase
{
    #[DataProvider('listProvider')]
    public function testRendersExpectedBranch(string $list, string $item, string $expected): void
    {
        $subject = new InListViewHelper();
        $subject->setArguments(['list' => $list, 'item' => $item]);
        $subject->handleAdditionalArguments([
            '__then' => static fn(): string => 'then',
            '__else' => static fn(): string => 'else',
        ]);

        self::assertSame($expected, $subject->render());
    }

    public static function listProvider(): iterable
    {
        yield 'first item' => ['alpha,beta', 'alpha', 'then'];
        yield 'last item' => ['alpha,beta', 'beta', 'then'];
        yield 'partial item does not match' => ['alphabet,beta', 'alpha', 'else'];
        yield 'missing item' => ['alpha,beta', 'gamma', 'else'];
        yield 'empty list' => ['', 'alpha', 'else'];
    }

    public function testDeclaresListAndItemArguments(): void
    {
        $definitions = (new InListViewHelper())->prepareArguments();

        self::assertArrayHasKey('list', $definitions);
        self::assertArrayHasKey('item', $definitions);
        self::assertSame('string', $definitions['list']->getType());
        self::assertSame('string', $definitions['item']->getType());
    }
}
