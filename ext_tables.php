<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

if (TYPO3_MODE == 'BE') {
	$reports = array('eid', 'clikeys', 'plugins', 'xclass', 'hooks', 'status', 'ajax', 'extensions', 'logerrors', 'websitesconf');

	if (t3lib_extMgm::isLoaded('realurl')) {
		$reports [] = 'realurlerrors';
	}

	// Add a module for older version (<4.3)
	if (t3lib_div::int_from_ver(TYPO3_version) < 4003000) {
		t3lib_extMgm::addModulePath('tools_txadditionalreportsM1', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');
		t3lib_extMgm::addModule('tools', 'txadditionalreportsM1', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');
		$extensionPath = t3lib_extMgm::extPath('additional_reports');
		require_once($extensionPath . 'classes/class.tx_additionalreports_main.php'); //main class
	} else { // 4.3>=
		if (t3lib_div::int_from_ver(TYPO3_version) >= 4005000) {
			$reports [] = 'extdirect';
		}
		foreach ($reports as $report) {
			$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports'][$_EXTKEY][$report] = array(
				'title' => 'LLL:EXT:' . $_EXTKEY . '/locallang.xml:' . $report . '_title',
				'description' => 'LLL:EXT:' . $_EXTKEY . '/locallang.xml:' . $report . '_description',
				'icon' => 'EXT:' . $_EXTKEY . '/reports_' . $report . '/tx_additionalreports_' . $report . '.png',
				'report' => 'tx_additionalreports_' . $report
			);
		}
	}
}


?>