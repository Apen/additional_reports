<?php

namespace Sng\AdditionalReports\Eid;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Core\Http\NullResponse;
use TYPO3\CMS\Core\Utility\DiffUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CallAjax
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function main(ServerRequestInterface $request)
    {
        $mode = GeneralUtility::_GP('mode');
        $extKey = GeneralUtility::_GP('extKey');
        $extFile = GeneralUtility::_GP('extFile');
        $extVersion = GeneralUtility::_GP('extVersion');
        $file1 = realpath(ExtensionManagementUtility::extPath($extKey, $extFile));
        $realPathExt = realpath(Utility::getPathSite() . '/typo3conf/ext/' . $extKey);

        if ($mode === null) {
            $mode = 'compareFile';
        }

        $content = '<div style="background:white;">';

        if ($mode == 'compareFile') {
            if (!strstr($file1, $realPathExt)) {
                die('Access denied.');
            }
            $terFileContent = Utility::downloadT3x($extKey, $extVersion, $extFile);
            $content .= $this->t3Diff(GeneralUtility::getURL($file1), $terFileContent);
        } elseif ($mode == 'compareExtension') {
            $t3xfiles = Utility::downloadT3x($extKey, $extVersion);
            $diff = 0;
            foreach ($t3xfiles['FILES'] as $filePath => $file) {
                $currentFileContent = GeneralUtility::getURL($realPathExt . '/' . $filePath);
                if ($file['content_md5'] !== md5($currentFileContent)) {
                    $diff++;
                    $content .= '<h2>' . $filePath . '</h2>';
                    $content .= $this->t3Diff($currentFileContent, $file['content']);
                }
            }
            if (empty($diff)) {
                $content .= 'No diff to show';
            }
        }

        $content .= '</div>';
        echo $content;
        return new NullResponse();
    }

    /**
     * @param string $file1
     * @param string $file2
     * @return string
     */
    public function t3Diff($file1, $file2)
    {
        $diff = GeneralUtility::makeInstance(DiffUtility::class);
        $diff->stripTags = false;
        $sourcesDiff = $diff->makeDiffDisplay($file1, $file2);
        return $this->printT3Diff($sourcesDiff);
    }

    /**
     * @param string $sourcesDiff
     * @return string
     */
    public function printT3Diff($sourcesDiff)
    {
        $out = '<pre width="10">';
        $out .= '<table border="0" cellspacing="0" cellpadding="0" style="width:780px;padding:8px;">';
        $out .= '<tr><td style="background-color: #FDD;"><strong>Local file</strong></td></tr>';
        $out .= '<tr><td style="background-color: #DFD;"><strong>TER file</strong></td></tr>';
        $sourcesDiff = str_replace('<del>', '<del style="background-color:#FDD;">', $sourcesDiff);
        $sourcesDiff = str_replace('<ins>', '<ins style="background-color:#DFD;">', $sourcesDiff);
        $out .= $sourcesDiff;
        $out .= '</table>';
        $out .= '</pre>';
        return $out;
    }
}
