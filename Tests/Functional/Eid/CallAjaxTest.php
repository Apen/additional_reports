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
        $serverRequest = (new ServerRequest('GET', '/'))
            ->withQueryParams([
                'mode' => 'compareExtension',
                'extKey' => 'extension_that_does_not_exist',
                'extVersion' => '1.0.0',
            ]);

        $response = (new CallAjax())->main($serverRequest);

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('Extension not found.', (string) $response->getBody());
    }

    public function testPathOutsideExtensionIsRejected(): void
    {
        $response = $this->createCallAjax('unused')->main($this->createRequest('compareFile', [
            'extFile' => '../composer.json',
        ]));

        self::assertSame(403, $response->getStatusCode());
        self::assertSame('Access denied.', (string) $response->getBody());
    }

    public function testSingleFileComparisonUsesDownloadedContent(): void
    {
        $response = $this->createCallAjax("<?php\nremote\n")->main($this->createRequest('compareFile', [
            'extFile' => 'ext_emconf.php',
        ]));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('+remote', (string) $response->getBody());
        self::assertStringContainsString('-$EM_CONF', (string) $response->getBody());
    }

    public function testExtensionComparisonShowsChangedFilesAndSkipsMissingFiles(): void
    {
        $archive = [
            'FILES' => [
                'ext_emconf.php' => [
                    'content_md5' => md5('remote'),
                    'content' => 'remote',
                ],
                'missing.php' => [
                    'content_md5' => md5('missing'),
                    'content' => 'missing',
                ],
            ],
        ];

        $response = $this->createCallAjax($archive)->main($this->createRequest('compareExtension'));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('<h2>ext_emconf.php</h2>', (string) $response->getBody());
        self::assertStringNotContainsString('<h2>missing.php</h2>', (string) $response->getBody());
    }

    public function testExtensionComparisonReportsWhenFilesAreIdentical(): void
    {
        $localContent = (string) file_get_contents(__DIR__ . '/../../../ext_emconf.php');
        $archive = [
            'FILES' => [
                'ext_emconf.php' => [
                    'content_md5' => md5($localContent),
                    'content' => $localContent,
                ],
            ],
        ];

        $response = $this->createCallAjax($archive)->main($this->createRequest('compareExtension'));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('No diff to show', (string) $response->getBody());
    }

    /** @param array<string, string> $parameters */
    private function createRequest(string $mode, array $parameters = []): ServerRequest
    {
        return (new ServerRequest('GET', '/'))->withQueryParams(array_replace([
            'mode' => $mode,
            'extKey' => 'additional_reports',
            'extVersion' => '3.4.9',
        ], $parameters));
    }

    private function createCallAjax(mixed $archive): CallAjax
    {
        return new class ($archive) extends CallAjax {
            public function __construct(private readonly mixed $archive) {}

            protected function downloadT3x(string $extensionKey, string $extensionVersion, ?string $extensionFile = null): mixed
            {
                return $this->archive;
            }
        };
    }
}
