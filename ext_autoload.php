<?php

$reports = array('eid', 'clikeys', 'plugins', 'xclass', 'hooks', 'status', 'ajax', 'extensions', 'extdirect', 'realurlerrors', 'logerrors', 'websitesconf', 'dbcheck');

if (version_compare(TYPO3_version, '4.5', '>=')) {
	$reports [] = 'extdirect';
}

$extensionPath = t3lib_extMgm::extPath('additional_reports');
$autoload = array();
$autoload['tx_additionalreports_main'] = $extensionPath . 'classes/class.tx_additionalreports_main.php'; //main class

foreach ($reports as $report) {
	$autoload['tx_additionalreports_' . $report] = $extensionPath . 'reports_' . $report . '/class.tx_additionalreports_' . $report . '.php';
}

return $autoload;

?>