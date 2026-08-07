<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Unit\Eid;

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Sng\AdditionalReports\Eid\CallAjax;

final class CallAjaxTest extends TestCase
{
    public function testInvalidComparisonRequestReturnsBadRequest(): void
    {
        $response = (new CallAjax())->main(new ServerRequest('GET', '/'));

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('Invalid comparison request.', (string) $response->getBody());
    }
}
