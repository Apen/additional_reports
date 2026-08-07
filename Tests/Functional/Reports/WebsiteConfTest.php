<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Tests\Functional\Reports;

use Sng\AdditionalReports\Reports\WebsiteConf;
use Sng\AdditionalReports\Tests\Functional\FunctionalTestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class WebsiteConfTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/pages.csv');
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $connectionPool->getConnectionForTable('pages')->update('pages', [
            'is_siteroot' => 1,
            'title' => '<script>Root page</script>',
        ], ['uid' => 1]);
        $connectionPool->getConnectionForTable('sys_template')->insert('sys_template', [
            'pid' => 1,
            'title' => '<script>Site template</script>',
            'root' => 1,
            'hidden' => 0,
            'sorting' => 1,
        ]);
    }

    public function testDisplay()
    {
        $output = (new WebsiteConf(parent::getReportObject()))->display();

        self::assertStringContainsString('&lt;script&gt;Root page&lt;/script&gt;', $output);
        self::assertStringContainsString('&lt;script&gt;Site template&lt;/script&gt;', $output);
        self::assertStringNotContainsString('<script>', $output);
        self::assertStringContainsString('[uid=', $output);
    }
}
