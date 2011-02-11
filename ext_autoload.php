<?php

$reports = array('eid', 'clikeys', 'plugins', 'xclass', 'hooks', 'status', 'ajax');
$extensionPath = t3lib_extMgm::extPath('additional_reports');
$autoload = array();

foreach ($reports as $report) {
	$autoload['tx_additionalreports_' . $report] = $extensionPath . 'reports_' . $report . '/class.tx_additionalreports_' . $report . '.php';
}

return $autoload;

?>