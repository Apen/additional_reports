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
use Sng\AdditionalReports\Service\TerArchiveService;
use Sng\AdditionalReports\Service\UnifiedDiffRenderer;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Package\Exception\UnknownPackageException;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CallAjax
{
    public function main(ServerRequestInterface $serverRequest): ResponseInterface
    {
        $parameters = array_replace($serverRequest->getQueryParams(), (array) $serverRequest->getParsedBody());
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

            $terFileContent = $this->downloadT3x($extensionKey, $extensionVersion, $extensionFile);
            $content .= $this->renderDiff($this->readLocalFile($localFile), $terFileContent);
        } else {
            $t3xfiles = $this->downloadT3x($extensionKey, $extensionVersion);
            $diff = 0;
            foreach ($t3xfiles['FILES'] as $filePath => $file) {
                $localFile = $this->resolveExtensionFile($extensionPath, $filePath);
                if ($localFile === null) {
                    continue;
                }

                $currentFileContent = $this->readLocalFile($localFile);
                if ($file['content_md5'] !== md5($currentFileContent)) {
                    $diff++;
                    $content .= '<h2>' . $filePath . '</h2>';
                    $content .= $this->renderDiff($currentFileContent, $file['content']);
                }
            }

            if ($diff === 0) {
                $content .= 'No diff to show';
            }
        }

        $content .= '</div>';
        return new HtmlResponse($content);
    }

    protected function downloadT3x(string $extensionKey, string $extensionVersion, ?string $extensionFile = null): mixed
    {
        return GeneralUtility::makeInstance(TerArchiveService::class)->download($extensionKey, $extensionVersion, $extensionFile);
    }

    protected function readLocalFile(string $file): string
    {
        return (string) GeneralUtility::getURL($file);
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

    protected function renderDiff(string $localContent, string $remoteContent): string
    {
        return GeneralUtility::makeInstance(UnifiedDiffRenderer::class)->render($localContent, $remoteContent);
    }
}
