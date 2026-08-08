<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Reports\CommandControllers;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;

class CommandControllersTest extends FunctionalTestCase
{
    public function testDisplay(): void
    {
        $commandControllers = new CommandControllers(parent::getReportObject());
        self::assertNotEmpty($commandControllers->display());
    }
}
