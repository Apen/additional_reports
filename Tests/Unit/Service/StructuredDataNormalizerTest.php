<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Sng\AdditionalReports\Service\StructuredDataNormalizer;

final class StructuredDataNormalizerTest extends TestCase
{
    public function testNestedValuesAreNormalizedWithoutGeneratingHtml(): void
    {
        $result = (new StructuredDataNormalizer())->normalize([
            'listener' => ['method' => 'handle', 'enabled' => true],
            'priority' => 10,
        ]);

        self::assertSame([
            [
                'key' => 'listener',
                'value' => '',
                'children' => [
                    ['key' => 'method', 'value' => 'handle', 'children' => []],
                    ['key' => 'enabled', 'value' => 'true', 'children' => []],
                ],
            ],
            ['key' => 'priority', 'value' => '10', 'children' => []],
        ], $result);
    }
}
