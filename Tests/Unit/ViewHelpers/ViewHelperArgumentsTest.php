<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit\ViewHelpers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sng\AdditionalReports\ViewHelpers\ContentInfosViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class ViewHelperArgumentsTest extends TestCase
{
    /**
     * @param class-string<AbstractViewHelper> $viewHelperClass
     * @param array<string, array{type: string, required: bool}> $expectedArguments
     */
    #[DataProvider('argumentProvider')]
    public function testArgumentDefinitions(string $viewHelperClass, array $expectedArguments): void
    {
        $definitions = (new $viewHelperClass())->prepareArguments();

        foreach ($expectedArguments as $name => $expected) {
            self::assertArrayHasKey($name, $definitions);
            self::assertSame($expected['type'], $definitions[$name]->getType());
            self::assertSame($expected['required'], $definitions[$name]->isRequired());
        }
    }

    public static function argumentProvider(): iterable
    {
        yield 'content information' => [
            ContentInfosViewHelper::class,
            [
                'item' => ['type' => 'array', 'required' => false],
                'as' => ['type' => 'string', 'required' => false],
                'plugin' => ['type' => 'boolean', 'required' => false],
                'ctype' => ['type' => 'boolean', 'required' => false],
            ],
        ];
    }
}
