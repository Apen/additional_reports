<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Functional\Service;

use Sng\AdditionalReports\Service\ContentTypeResolver;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;

final class ContentTypeResolverTest extends FunctionalTestCase
{
    public function testExtensionIconFromTcaIsPublished(): void
    {
        $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'][] = [
            'label' => 'Example',
            'value' => 'vendor_example',
            'icon' => 'EXT:additional_reports/Resources/Public/Icons/Extension.svg',
        ];

        $information = (new ContentTypeResolver())->resolve('ctype', 'vendor_example');

        self::assertSame('vendor', $information['extension']);
        self::assertStringContainsString('Extension.svg', $information['iconext']);
    }
}
