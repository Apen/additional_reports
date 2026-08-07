<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Reports\Status;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;

class StatusTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testDisplay()
    {
        $originalRequest = $GLOBALS['TYPO3_REQUEST'];
        $request = new ServerRequest(
            $originalRequest->getUri(),
            'GET',
            null,
            $originalRequest->getHeaders(),
            [...$originalRequest->getServerParams(), 'HTTP_USER_AGENT' => '<script>alert(1)</script>'],
        );
        foreach ($originalRequest->getAttributes() as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }
        $GLOBALS['TYPO3_REQUEST'] = $request->withAttribute('normalizedParams', NormalizedParams::createFromRequest($request));
        $output = (new Status(parent::getReportObject()))->display();

        self::assertNotEmpty($output);
        self::assertStringContainsString('class="additional-reports-status-section"', $output);
        self::assertStringContainsString('<summary>MySQL</summary>', $output);
        self::assertStringContainsString('additional-reports-status-subtable', $output);
        self::assertStringNotContainsString('class="table-fit"', $output);
        self::assertStringNotContainsString('<script>alert(1)</script>', $output);
        self::assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $output);
    }
}
