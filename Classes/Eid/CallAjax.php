<?php

declare(strict_types=1);

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
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Package\Exception\UnknownPackageException;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\DiffUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CallAjax
{
    public function main(ServerRequestInterface $request): ResponseInterface
    {
        $parameters = array_replace($request->getQueryParams(), (array) $request->getParsedBody());
        $mode = is_string($parameters['mode'] ?? null) ? $parameters['mode'] : 'compareFile';
        $extensionKey = is_string($parameters['extKey'] ?? null) ? $parameters['extKey'] : '';
        $extensionVersion = is_string($parameters['extVersion'] ?? null) ? $parameters['extVersion'] : '';
        if ($extensionKey === '' || $extensionVersion === '' || ! in_array($mode, ['compareFile', 'compareExtension'], true)) {
            return new HtmlResponse('Invalid comparison request.', 400);
        }

        try {
            $package = GeneralUtility::makeInstance(PackageManager::class)->getPackage($extensionKey);
        } catch (UnknownPackageException) {
            return new HtmlResponse('Extension not found.', 404);
        }
        $extensionPath = realpath($package->getPackagePath());
        if ($extensionPath === false) {
            return new HtmlResponse('Extension path not found.', 404);
        }

        $content = '<div style="background:white;">';

        if ($mode === 'compareFile') {
            $extensionFile = is_string($parameters['extFile'] ?? null) ? $parameters['extFile'] : '';
            $localFile = $this->resolveExtensionFile($extensionPath, $extensionFile);
            if ($localFile === null) {
                return new HtmlResponse('Access denied.', 403);
            }
            $terFileContent = Utility::downloadT3x($extensionKey, $extensionVersion, $extensionFile);
            $content .= $this->t3Diff(GeneralUtility::getURL($localFile), $terFileContent);
        } else {
            $t3xfiles = Utility::downloadT3x($extensionKey, $extensionVersion);
            $diff = 0;
            foreach ($t3xfiles['FILES'] as $filePath => $file) {
                $localFile = $this->resolveExtensionFile($extensionPath, $filePath);
                if ($localFile === null) {
                    continue;
                }
                $currentFileContent = GeneralUtility::getURL($localFile);
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
        return new HtmlResponse($content);
    }

    private function resolveExtensionFile(string $extensionPath, string $relativeFile): ?string
    {
        if ($relativeFile === '') {
            return null;
        }
        $file = realpath($extensionPath . DIRECTORY_SEPARATOR . ltrim($relativeFile, '/\\'));
        $extensionPath = rtrim($extensionPath, '/\\');
        return $file !== false && str_starts_with($file, $extensionPath . DIRECTORY_SEPARATOR) && is_file($file)
            ? $file
            : null;
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
