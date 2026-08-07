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
        $resolver = new HookResolver();
        $hook = EmailLoginNotification::class . '->emailAtLogin';

        self::assertTrue($resolver->isHook($hook));
        self::assertTrue($resolver->isHook('&' . EmailLoginNotification::class));
        self::assertTrue($resolver->isHook(['unused', $hook]));
        self::assertFalse($resolver->isHook(''));
        self::assertFalse($resolver->isHook(123));
        self::assertFalse($resolver->isHook(['unused', 123]));
        self::assertFalse($resolver->isHook('Unknown\\MissingClass->method'));
    }

    public function testNestedCandidatesAreFiltered(): void
    {
        $resolver = new HookResolver();
        $hook = EmailLoginNotification::class . '->emailAtLogin';

        self::assertSame($hook, $resolver->resolve($hook));
        self::assertNull($resolver->resolve('Unknown\\MissingClass'));
        self::assertSame(
            ['valid' => $hook, 'nested' => ['valid' => $hook]],
            $resolver->resolve([
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
