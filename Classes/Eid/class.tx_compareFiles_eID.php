<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <title>additional_reports : Compare files</title>
    <style>
        del {
            background-color: #FDD;
        }

        ins {
            background-color: #DFD;
        }
    </style>
</head>
<body style="background:white;">
<?php

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('additional_reports') . 'Classes/Utility.php');

$mode = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('mode');
$extKey = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('extKey');
$extFile = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('extFile');
$extVersion = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('extVersion');
$file1 = realpath(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extKey, $extFile));
$realPathExt = realpath(PATH_site . 'typo3conf/ext/' . $extKey);

if ($mode === null) {
    $mode = 'compareFile';
}

switch ($mode) {
    case 'compareFile':
        if (strstr($file1, $realPathExt) === false) {
            die ('Access denied.');
        }
        $terFileContent = \Sng\AdditionalReports\Utility::downloadT3x($extKey, $extVersion, $extFile);
        t3Diff(\TYPO3\CMS\Core\Utility\GeneralUtility::getURL($file1), $terFileContent);
        break;
    case 'compareExtension':
        $t3xfiles = \Sng\AdditionalReports\Utility::downloadT3x($extKey, $extVersion);

        $diff = 0;

        foreach ($t3xfiles['FILES'] as $filePath => $file) {
            $currentFileContent = \TYPO3\CMS\Core\Utility\GeneralUtility::getURL($realPathExt . '/' . $filePath);
            if ($file['content_md5'] !== md5($currentFileContent)) {
                $diff++;
                echo '<h2>' . $filePath . '</h2>';
                t3Diff($currentFileContent, $file['content']);
            }
        }

        if (empty($diff)) {
            echo 'No diff to show';
        }

        break;
}

function t3Diff($file1, $file2)
{
    $diff = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Utility\\DiffUtility');
    $diff->stripTags = false;
    $sourcesDiff = $diff->makeDiffDisplay($file1, $file2);
    printT3Diff($sourcesDiff);
}

function printT3Diff($sourcesDiff)
{
    $out = '<pre width="10"><table border="0" cellspacing="0" cellpadding="0" style="width:780px;padding:8px;">';
    $out .= '<tr><td style="background-color: #FDD;"><strong>Local file</strong></td></tr>';
    $out .= '<tr><td style="background-color: #DFD;"><strong>TER file</strong></td></tr>';
    $out = $out . $sourcesDiff;
    $out .= '</table></pre>';
    echo $out;
}

function formatcode($code)
{
    $code = htmlentities($code);
    return $code;
}

?>
</body>
</html>