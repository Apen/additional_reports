<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
		"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>additional_reports : Compare files</title>
</head>
<body style="background:white;">
<?php

require_once(PATH_t3lib . 'class.t3lib_befunc.php');
require_once(PATH_t3lib . 'stddb/tables.php');

$extKey = t3lib_div::_GP('extKey');
$extFile = t3lib_div::_GP('extFile');
$extVersion = t3lib_div::_GP('extVersion');
$firstLetter = strtolower(substr($extKey, 0, 1));
$secondLetter = strtolower(substr($extKey, 1, 1));
$file1 = t3lib_extMgm::extPath($extKey, $extFile);
$file2 = 'http://typo3.org/typo3temp/tx_terfe/t3xcontentcache/' . $firstLetter . '/' . $secondLetter . '/' . $extKey . '/' . $extKey . '-' . $extVersion . '-' . preg_replace('/[^\w]/', '__', $extFile);
t3Diff($file1, $file2);

function t3Diff($file1, $file2) {
	$diff = t3lib_div::makeInstance('t3lib_diff');
	$diff->diffOptions = '-bu';
	$sourcesDiff = $diff->getDiff(t3lib_div::getURL($file1), t3lib_div::getURL($file2));
	$sourcesDiff[0] = $file1;
	$sourcesDiff[1] = $file2;
	printT3Diff($sourcesDiff);
}

function printT3Diff($sourcesDiff) {
	$out = '<pre width="10"><table border="0" cellspacing="0" cellpadding="0" style="width:780px;padding:8px;">';
	$out .= '<tr><td style="background-color: #FDD;"><strong>Local file : ' . $GLOBALS['extKey'] . '/' . $GLOBALS['extFile'] . '</strong></td></tr>';
	$out .= '<tr><td style="background-color: #DFD;"><strong>TER file (version ' . $GLOBALS['extVersion'] . ')</strong></td></tr>';

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

?>
</body>
</html>