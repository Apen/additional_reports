<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Reports\EventDispatcher;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;

final class EventDispatcherTest extends FunctionalTestCase
{
    public function testDisplayUsesCompiledListenerDefinitions(): void
    {
        $output = (new EventDispatcher(parent::getReportObject()))->display();

        self::assertNotEmpty($output);
        self::assertStringContainsString('AfterBackendPageRenderEvent', $output);
        self::assertStringContainsString('__invoke', $output);
    }
}
