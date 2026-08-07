<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Sng\AdditionalReports\Service\UnifiedDiffRenderer;

final class UnifiedDiffRendererTest extends TestCase
{
    public function testDiffOnlyDisplaysChangesWithThreeContextLines(): void
    {
        $local = implode("\n", ['one', 'two', 'three', 'four', 'local', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen']);
        $remote = implode("\n", ['one', 'two', 'three', 'four', 'remote', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen']);

        $output = (new UnifiedDiffRenderer())->render($local, $remote);

        self::assertStringNotContainsString('one', $output);
        self::assertStringContainsString('two', $output);
        self::assertStringContainsString('-local', $output);
        self::assertStringContainsString('+remote', $output);
        self::assertStringContainsString('eight', $output);
        self::assertStringNotContainsString('nine', $output);
        self::assertStringContainsString('@@ -2,7 +2,7 @@', $output);
    }

    public function testDiffEscapesFileContents(): void
    {
        $output = (new UnifiedDiffRenderer())->render('<script>alert(1)</script>', '<strong>safe</strong>');

        self::assertStringNotContainsString('<script>', $output);
        self::assertStringContainsString('&lt;script&gt;', $output);
        self::assertStringContainsString('&lt;strong&gt;', $output);
    }
}
