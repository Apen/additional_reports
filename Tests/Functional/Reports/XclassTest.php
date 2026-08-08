<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Reports\Xclass;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;

class XclassTest extends FunctionalTestCase
{
    public function testDisplay(): void
    {
        $xclass = new Xclass(parent::getReportObject());
        self::assertNotEmpty($xclass->display());
    }
}
