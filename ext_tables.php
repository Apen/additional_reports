<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (TYPO3_MODE == 'BE') {
    $reports = \Sng\AdditionalReports\Utility::getReportsList();
    foreach ($reports as $report) {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['additional_reports'][$report[1]] = [
            'title'       => 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:' . $report[1] . '_title',
            'description' => 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:' . $report[1] . '_description',
            'icon'        => 'EXT:additional_reports/Resources/Public/Icons/tx_additionalreports_' . $report[1] . '.png',
            'report'      => 'Sng\AdditionalReports\Reports\\' . $report[0]
        ];
    }
}
