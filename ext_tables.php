<?php

defined('TYPO3') or die();

$reports = \Sng\AdditionalReports\Utility::getReportsList();

// only for typo3 < 12
foreach ($reports as $report) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['additional_reports'][$report[1]] = [
        'title'       => 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:' . $report[1] . '_title',
        'description' => 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:' . $report[1] . '_description',
        'icon'        => 'EXT:additional_reports/Resources/Public/Icons/tx_additionalreports_' . $report[1] . '.png',
        'report'      => 'Sng\AdditionalReports\Reports\\' . $report[0]
    ];
}
