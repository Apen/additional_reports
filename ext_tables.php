<?php

if (!defined('TYPO3_MODE')) die ('Access denied.');

require_once(t3lib_extMgm::extPath('additional_reports') . 'classes/class.tx_additionalreports_main.php');
require_once(t3lib_extMgm::extPath('additional_reports') . 'classes/class.tx_additionalreports_util.php');
require_once(t3lib_extMgm::extPath('additional_reports') . 'classes/class.tx_additionalreports_templating.php');

if (TYPO3_MODE == 'BE') {

	$reports = tx_additionalreports_util::getReportsList();

	// Add a module for older version (<4.3)
	if (tx_additionalreports_util::intFromVer(TYPO3_version) < 4003000) {
		t3lib_extMgm::addModulePath('tools_txadditionalreportsM1', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');
		t3lib_extMgm::addModule('tools', 'txadditionalreportsM1', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');
	} else {
		// 4.3>=
		foreach ($reports as $report) {
			$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports'][$_EXTKEY][$report] = array(
				'title'       => 'LLL:EXT:' . $_EXTKEY . '/locallang.xml:' . $report . '_title',
				'description' => 'LLL:EXT:' . $_EXTKEY . '/locallang.xml:' . $report . '_description',
				'icon'        => 'EXT:' . $_EXTKEY . '/reports/reports_' . $report . '/tx_additionalreports_' . $report . '.png',
				'report'      => 'tx_additionalreports_' . $report
			);
		}
	}

}



?>