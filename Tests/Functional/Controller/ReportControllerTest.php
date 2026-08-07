<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Functional\Controller;

use Sng\AdditionalReports\Controller\ReportController;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;
use TYPO3\CMS\Backend\Routing\Route;

final class ReportControllerTest extends FunctionalTestCase
{
    public function testEventDispatcherActionRendersModuleResponse(): void
    {
        $controller = $this->getContainer()->get(ReportController::class);
        $request = $GLOBALS['TYPO3_REQUEST']->withAttribute('route', new Route(
            '/typo3/module/system/reports/additional-reports/eventdispatcher',
            [
                '_identifier' => 'system_reports_additionalreports_eventdispatcher',
                'packageName' => 'apen/additional_reports',
            ],
        ));
        $response = $controller->eventDispatcher($request);
        $body = (string) $response->getBody();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('AfterBackendPageRenderEvent', $body);
        self::assertStringContainsString('module-body', $body);
    }
}
