<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Eid\CallAjax;
use Sng\AdditionalReports\Reports\Eid;
use Sng\AdditionalReports\Service\EidTargetResolver;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EidTest extends FunctionalTestCase
{
    public function testDisplay(): void
    {
        self::assertSame('additional_reports', GeneralUtility::makeInstance(EidTargetResolver::class)->resolve(CallAjax::class . '::main')['extension']);
        $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include'] = [
            'legacy' => 'EXT:additional_reports/Classes/Eid/CallAjax.php',
            'modern' => CallAjax::class . '::main',
            'callable' => [CallAjax::class, 'main'],
        ];

        $eid = new Eid(parent::getReportObject());
        $output = $eid->display();

        self::assertStringContainsString('additional_reports', $output);
        self::assertStringContainsString('EXT:additional_reports/Classes/Eid/CallAjax.php', $output);
        self::assertStringContainsString(CallAjax::class . '::main', $output);
        self::assertStringContainsString('array', $output);
        self::assertGreaterThanOrEqual(3, substr_count($output, '>additional_reports</td>'));
    }
}
