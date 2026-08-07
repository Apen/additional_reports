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

    public function testScalarAndObjectValuesAreNormalized(): void
    {
        $stringable = new class implements \Stringable {
            public function __toString(): string
            {
                return 'example';
            }
        };

        $normalizer = new StructuredDataNormalizer();

        self::assertSame([['key' => '', 'value' => 'null', 'children' => []]], $normalizer->normalize(null));
        self::assertSame([['key' => '', 'value' => 'false', 'children' => []]], $normalizer->normalize(false));
        self::assertSame(
            [['key' => '', 'value' => $stringable::class . ': example', 'children' => []]],
            $normalizer->normalize($stringable),
        );
        self::assertSame(
            [['key' => '', 'value' => \stdClass::class, 'children' => []]],
            $normalizer->normalize(new \stdClass()),
        );
    }
}
