<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Sng\AdditionalReports\Service\HookResolver;
use TYPO3\CMS\Backend\Security\EmailLoginNotification;

final class HookResolverTest extends TestCase
{
    public function testClassAndMethodHooksAreDetected(): void
    {
        $hookResolver = new HookResolver();
        $hook = EmailLoginNotification::class . '->emailAtLogin';

        self::assertTrue($hookResolver->isHook($hook));
        self::assertTrue($hookResolver->isHook('&' . EmailLoginNotification::class));
        self::assertTrue($hookResolver->isHook(['unused', $hook]));
        self::assertFalse($hookResolver->isHook(''));
        self::assertFalse($hookResolver->isHook(123));
        self::assertFalse($hookResolver->isHook(['unused', 123]));
        self::assertFalse($hookResolver->isHook('Unknown\\MissingClass->method'));
    }

    public function testNestedCandidatesAreFiltered(): void
    {
        $hookResolver = new HookResolver();
        $hook = EmailLoginNotification::class . '->emailAtLogin';

        self::assertSame($hook, $hookResolver->resolve($hook));
        self::assertNull($hookResolver->resolve('Unknown\\MissingClass'));
        self::assertSame(
            ['valid' => $hook, 'nested' => ['valid' => $hook]],
            $hookResolver->resolve([
                'valid' => $hook,
                'invalid' => 'Unknown\\MissingClass',
                'nested' => [
                    'valid' => $hook,
                    'invalid' => 'Unknown\\MissingClass',
                    'tooDeep' => [$hook],
                ],
            ]),
        );
    }
}
