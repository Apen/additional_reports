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

$extKey = t3lib_div::_GP('extKey');
$extFile = t3lib_div::_GP('extFile');
$extVersion = t3lib_div::_GP('extVersion');
$firstLetter = strtolower(substr($extKey, 0, 1));
$secondLetter = strtolower(substr($extKey, 1, 1));
$file1 = realpath(t3lib_extMgm::extPath($extKey, $extFile));
$realPathExt = realpath(PATH_site . 'typo3conf/ext/' . $extKey);

if (strstr($file1, $realPathExt) === FALSE) {
	die ('Access denied.');
}

$terFileContent = downloadT3x($extKey, $extVersion);
t3Diff(t3lib_div::getURL($file1), $terFileContent);

function downloadT3x($extension, $version) {
	$from = 'http://typo3.org/fileadmin/ter/' . $GLOBALS['firstLetter'] . '/' . $GLOBALS['secondLetter'] . '/' . $extension . '_' . $version . '.t3x';
	$content = t3lib_div::getURL($from);
	$t3xfiles = extractExtensionDataFromT3x($content);
	return $t3xfiles['FILES'][$GLOBALS['extFile']]['content'];
}

function extractExtensionDataFromT3x($content) {
	$parts = explode(':', $content, 3);
	if ($parts[1] === 'gzcompress') {
		if (function_exists('gzuncompress')) {
			$parts[2] = gzuncompress($parts[2]);
		} else {
			throw new \Exception('Decoding Error: No decompressor available for compressed content. gzcompress()/gzuncompress() functions are not available!');
		}
	}
	if (md5($parts[2]) == $parts[0]) {
		$output = unserialize($parts[2]);
		if (is_array($output)) {
			return $output;
		} else {
			throw new \Exception('Error: Content could not be unserialized to an array. Strange (since MD5 hashes match!)');
		}
	} else {
		throw new \Exception('Error: MD5 mismatch. Maybe the extension file was downloaded and saved as a text file by the browser and thereby corrupted!? (Always select "All" filetype when saving extensions)');
	}
}

function t3Diff($file1, $file2) {
	$diff = t3lib_div::makeInstance('t3lib_diff');
	$diff->diffOptions = '-bu';
	$sourcesDiff = $diff->getDiff($file1, $file2);
	printT3Diff($sourcesDiff);
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
	initTSFE(0);
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

function initTSFE($id) {

	if (tx_additionalreports_util::intFromVer(TYPO3_version) < 6002000) {
		require_once(PATH_tslib . 'class.tslib_pagegen.php');
		require_once(PATH_tslib . 'class.tslib_fe.php');
		require_once(PATH_t3lib . 'class.t3lib_page.php');
		require_once(PATH_tslib . 'class.tslib_content.php');
		require_once(PATH_t3lib . 'class.t3lib_userauth.php');
		require_once(PATH_tslib . 'class.tslib_feuserauth.php');
		require_once(PATH_t3lib . 'class.t3lib_tstemplate.php');
		require_once(PATH_t3lib . 'class.t3lib_cs.php');
	}

	if (version_compare(TYPO3_version, '4.3.0', '<')) {
		$tsfeClassName = t3lib_div::makeInstanceClassName('tslib_fe');
		$GLOBALS['TSFE'] = new $tsfeClassName($GLOBALS['TYPO3_CONF_VARS'], $id, '');
	} else {
		$GLOBALS['TSFE'] = t3lib_div::makeInstance('tslib_fe', $GLOBALS['TYPO3_CONF_VARS'], $id, '');
	}
	$GLOBALS['TSFE']->connectToDB();
	$GLOBALS['TSFE']->initFEuser();
	// $GLOBALS['TSFE']->checkAlternativeIdMethods();
	$GLOBALS['TSFE']->determineId();
	$GLOBALS['TSFE']->getCompressedTCarray();
	$GLOBALS['TSFE']->initTemplate();
	$GLOBALS['TSFE']->getConfigArray();
}

?>
</body>
</html>