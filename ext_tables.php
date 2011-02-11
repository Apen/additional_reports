<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

if (TYPO3_MODE == 'BE') {
	$reports = array('eid', 'clikeys', 'plugins', 'xclass', 'hooks', 'status', 'ajax');
	foreach ($reports as $report) {
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports'][$_EXTKEY][$report] = array(
			'title' => 'LLL:EXT:' . $_EXTKEY . '/locallang.xml:' . $report . '_title',
			'description' => 'LLL:EXT:' . $_EXTKEY . '/locallang.xml:' . $report . '_description',
			'icon' => 'EXT:' . $_EXTKEY . '/reports_' . $report . '/tx_additionalreports_' . $report . '.png',
			'report' => 'tx_additionalreports_' . $report
		);
	}
}
?>