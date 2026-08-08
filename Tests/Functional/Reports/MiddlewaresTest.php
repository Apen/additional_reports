<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Reports\Middlewares;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;

class MiddlewaresTest extends FunctionalTestCase
{
    public function testDisplay(): void
    {
        $middlewares = new Middlewares(parent::getReportObject());
        self::assertNotEmpty($middlewares->display());
    }
}
