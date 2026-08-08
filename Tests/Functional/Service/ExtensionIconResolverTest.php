<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Functional\Service;

use Sng\AdditionalReports\Service\ExtensionIconResolver;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;

final class ExtensionIconResolverTest extends FunctionalTestCase
{
    public function testEmptyAndUnknownExtensionKeysHaveNoIcon(): void
    {
        $extensionIconResolver = new ExtensionIconResolver();

        self::assertSame('', $extensionIconResolver->resolve(''));
        self::assertSame('', $extensionIconResolver->resolve('extension_that_does_not_exist'));
    }

    public function testExtensionIconIsPublishedAsPublicResource(): void
    {
        $icon = (new ExtensionIconResolver())->resolve('additional_reports', $GLOBALS['TYPO3_REQUEST']);

        self::assertNotSame('', $icon);
        self::assertStringContainsString('Extension.svg', $icon);
    }
}
