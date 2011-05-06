<?php

$reports = array('eid', 'clikeys', 'plugins', 'xclass', 'hooks', 'status', 'ajax', 'extensions', 'extdirect');
if (t3lib_div::int_from_ver(TYPO3_version) >= 4005000) {
	$reports [] = 'extdirect';
}
$extensionPath = t3lib_extMgm::extPath('additional_reports');
$autoload = array();

foreach ($reports as $report) {
	$autoload['tx_additionalreports_' . $report] = $extensionPath . 'reports_' . $report . '/class.tx_additionalreports_' . $report . '.php';
}

return $autoload;

?>