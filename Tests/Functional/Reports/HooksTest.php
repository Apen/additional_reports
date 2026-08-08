<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Reports\Hooks;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;

class HooksTest extends FunctionalTestCase
{
    public function testDisplay(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['backend/test.php']['testHook'] = [Hooks::class];

        $output = (new Hooks(parent::getReportObject()))->display();

        self::assertStringContainsString('backend/test.php', $output);
        self::assertStringContainsString(Hooks::class, $output);
        self::assertStringContainsString('additional-reports-structured-data', $output);
        self::assertStringNotContainsString('table table-striped table-condensed mb-0', $output);
    }
}
