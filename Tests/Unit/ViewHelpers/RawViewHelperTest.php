<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit\ViewHelpers;

use PHPUnit\Framework\TestCase;
use Sng\AdditionalReports\ViewHelpers\RawViewHelper;

final class RawViewHelperTest extends TestCase
{
    public function testReturnsGivenValueWithoutModification(): void
    {
        $subject = new RawViewHelper();

        self::assertSame('<strong>raw</strong>', $subject->render('<strong>raw</strong>'));
        self::assertSame(0, $subject->render(0));
        self::assertSame(false, $subject->render(false));
    }

    public function testRendersChildrenWhenValueIsNull(): void
    {
        $subject = new RawViewHelper();
        $subject->setRenderChildrenClosure(static fn(): string => '<em>child</em>');

        self::assertSame('<em>child</em>', $subject->render());
    }
}
