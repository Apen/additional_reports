<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Sng\AdditionalReports\Service\ContentTypeResolver;

final class ContentTypeResolverTest extends TestCase
{
    public function testPluginInformationContainsRawNameAndExtensionPrefix(): void
    {
        $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] = [];

        self::assertSame([
            'plugin' => 'vendor_plugin',
            'extension' => 'vendor',
        ], (new ContentTypeResolver())->resolve('plugin', 'vendor_plugin'));
    }

    public function testUnsupportedOrEmptyContentTypeIsIgnored(): void
    {
        $resolver = new ContentTypeResolver();

        self::assertSame([], $resolver->resolve('plugin', ''));
        self::assertSame([], $resolver->resolve('unknown', 'vendor_plugin'));
    }
}
