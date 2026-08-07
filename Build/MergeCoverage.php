<?php

declare(strict_types=1);

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\Html\Facade as HtmlReport;
use SebastianBergmann\CodeCoverage\Report\Text;
use SebastianBergmann\CodeCoverage\Report\Thresholds;

require dirname(__DIR__) . '/.Build/vendor/autoload.php';

$root = dirname(__DIR__);
$files = glob($root . '/.Build/coverage/additional_reports/*.cov');
if ($files === false || $files === []) {
    throw new RuntimeException('No coverage files found.');
}

sort($files);
/** @var CodeCoverage $coverage */
$coverage = require array_shift($files);
foreach ($files as $file) {
    /** @var CodeCoverage $additionalCoverage */
    $additionalCoverage = require $file;
    $coverage->merge($additionalCoverage);
}

$target = $root . '/.Build/public/coverage';
(new HtmlReport())->process($coverage, $target . '/html');
$text = (new Text(Thresholds::default(), true))->process($coverage);
file_put_contents($target . '/report.txt', $text);
echo $text;
