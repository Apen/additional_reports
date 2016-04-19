<?php

if (!defined('TYPO3_MODE')) die ('Access denied.');

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('additional_reports') . 'Classes/class.tx_additionalreports_main.php');
require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('additional_reports') . 'Classes/class.tx_additionalreports_util.php');

if (TYPO3_MODE == 'BE') {
	$reports = tx_additionalreports_util::getReportsList();
	foreach ($reports as $report) {
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports'][$_EXTKEY][$report] = array(
			'title'       => 'LLL:EXT:' . $_EXTKEY . '/locallang.xml:' . $report . '_title',
			'description' => 'LLL:EXT:' . $_EXTKEY . '/locallang.xml:' . $report . '_description',
			'icon'        => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/tx_additionalreports_' . $report . '.png',
			'report'      => 'tx_additionalreports_' . $report
		);
	}
}

?>