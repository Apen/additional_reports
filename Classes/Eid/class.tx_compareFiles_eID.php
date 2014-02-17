<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>additional_reports : Compare files</title>
</head>
<body style="background:white;">
<?php

require_once(t3lib_extMgm::extPath('additional_reports') . 'Classes/class.tx_additionalreports_util.php');

if (tx_additionalreports_util::intFromVer(TYPO3_version) < 6002000) {
	require_once(PATH_t3lib . 'class.t3lib_befunc.php');
	require_once(PATH_t3lib . 'stddb/tables.php');
}

if (checkBeLogin() !== TRUE) {
	die ('Access denied.');
}

$mode = t3lib_div::_GP('mode');
$extKey = t3lib_div::_GP('extKey');
$extFile = t3lib_div::_GP('extFile');
$extVersion = t3lib_div::_GP('extVersion');
$file1 = realpath(t3lib_extMgm::extPath($extKey, $extFile));
$realPathExt = realpath(PATH_site . 'typo3conf/ext/' . $extKey);

if ($mode === NULL) {
	$mode = 'compareFile';
}

switch ($mode) {
	case 'compareFile':
		if (strstr($file1, $realPathExt) === FALSE) {
			die ('Access denied.');
		}
		$terFileContent = tx_additionalreports_util::downloadT3x($extKey, $extVersion, $extFile);
		t3Diff(t3lib_div::getURL($file1), $terFileContent);
		break;
	case 'compareExtension':
		$emConf = tx_additionalreports_util::includeEMCONF(PATH_typo3conf . 'ext/' . $extKey . '/ext_emconf.php', $extKey);
		$currentExt = array();
		$currentExt['extkey'] = $extKey;
		$currentExt['EM_CONF'] = $emConf;
		//$currentExt['files'] = t3lib_div::getFilesInDir($path . $extKey, '', 0, '', NULL);
		$currentExt['lastversion'] = tx_additionalreports_util::checkExtensionUpdate($currentExt);
		$affectedfileslast = tx_additionalreports_util::getExtAffectedFilesLastVersion($currentExt);
		printFilesList($affectedfileslast, $currentExt);
		break;
}

function t3Diff($file1, $file2) {
	$diff = t3lib_div::makeInstance('t3lib_diff');
	$diff->diffOptions = '-bu';
	$sourcesDiff = $diff->getDiff($file1, $file2);
	printT3Diff($sourcesDiff);
}

function printFilesList($affectedfileslast, $currentExt) {
	$compareUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
	$compareUrl .= 'index.php?eID=additional_reports_compareFiles';
	$compareUrl .= '&extKey=' . $currentExt['extkey'] . '&extVersion=' . $currentExt['lastversion']['version'];
	$out = '<ul>';
	foreach ($affectedfileslast as $file) {
		$out .= '<li><a href="' . $compareUrl . '&extFile=' . $file . '">' . $file . '</a></li>';
	}
	$out .= '<ul>';
	echo $out;
}

function printT3Diff($sourcesDiff) {
	$out = '<pre width="10"><table border="0" cellspacing="0" cellpadding="0" style="width:780px;padding:8px;">';
	$out .= '<tr><td style="background-color: #FDD;"><strong>Local file</strong></td></tr>';
	$out .= '<tr><td style="background-color: #DFD;"><strong>TER file</strong></td></tr>';
	unset($sourcesDiff[0]);
	unset($sourcesDiff[1]);
	foreach ($sourcesDiff as $line => $content) {
		switch (substr($content, 0, 1)) {
			case '+':
				$out .= '<tr><td style="background-color: #DFD;">' . formatcode($content) . '</td></tr>';
				break;
			case '-':
				$out .= '<tr><td style="background-color: #FDD;">' . formatcode($content) . '</td></tr>';
				break;
			case '@' :
				$out .= '<tr><td><br/><br/><br/></td></tr>';
				$out .= '<tr><td><strong>' . formatcode($content) . '</strong></td></tr>';
				break;
			default:
				$out .= '<tr><td>' . formatcode($content) . '</td></tr>';
		}
	}
	$out .= '</table></pre>';
	echo $out;
}

function formatcode($code) {
	$code = htmlentities($code);
	return $code;
}

function checkBeLogin() {
	tx_additionalreports_util::initTSFE(0);
	$BE_USER = NULL;
	if ($_COOKIE['be_typo_user']) {
		$BE_USER = t3lib_div::makeInstance('t3lib_tsfeBeUserAuth');
		$BE_USER->start();
		if ($BE_USER->user['uid']) {
			$BE_USER->fetchGroupData();
		}
		return $BE_USER->isAdmin();
	}
	return $BE_USER;
}


?>
</body>
</html>