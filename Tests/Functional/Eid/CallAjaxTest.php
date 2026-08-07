<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Functional\Eid;

use GuzzleHttp\Psr7\ServerRequest;
use Sng\AdditionalReports\Eid\CallAjax;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;

final class CallAjaxTest extends FunctionalTestCase
{
    public function testUnknownExtensionReturnsNotFoundResponse(): void
    {
        $request = (new ServerRequest('GET', '/'))
            ->withQueryParams([
                'mode' => 'compareExtension',
                'extKey' => 'extension_that_does_not_exist',
                'extVersion' => '1.0.0',
            ]);

        $response = (new CallAjax())->main($request);

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('Extension not found.', (string) $response->getBody());
    }
}
