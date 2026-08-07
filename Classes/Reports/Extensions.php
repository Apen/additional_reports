<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\AdditionalReports\Repository\ExtensionRepository;
use Sng\AdditionalReports\Service\ExtensionSchemaParser;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Extensions extends AbstractReport
{
    private ExtensionRepository $extensionRepository;

    private UriBuilder $uriBuilder;

    private ExtensionSchemaParser $extensionSchemaParser;

    public function __construct(
        ?object $reportObject = null,
        ?ExtensionRepository $extensionRepository = null,
        ?UriBuilder $uriBuilder = null,
        ?ExtensionSchemaParser $extensionSchemaParser = null,
    ) {
        parent::__construct($reportObject);
        $this->extensionRepository = $extensionRepository ?? GeneralUtility::makeInstance(ExtensionRepository::class);
        $this->uriBuilder = $uriBuilder ?? GeneralUtility::makeInstance(UriBuilder::class);
        $this->extensionSchemaParser = $extensionSchemaParser ?? GeneralUtility::makeInstance(ExtensionSchemaParser::class);
    }

    /**
     * This method renders the report
     *
     * @return string the status report as HTML
     */
    public function getReport(): string
    {
        return $this->display();
    }

    /**
     * Generate the loaded extension report
     *
     * @return string HTML code
     */
    public function display()
    {
        $extensionsToUpdate = 0;

        $allExtension = $this->extensionRepository->findGrouped();

        $listExtensionsTer = [];
        $listExtensionsDev = [];
        $listExtensionsUnloaded = [];

        if (! empty($allExtension['ter'])) {
            foreach ($allExtension['ter'] as $itemValue) {
                $currentExtension = $this->getExtensionInformations($itemValue);
                if ($currentExtension['updateAvailable']) {
                    $extensionsToUpdate++;
                }
                $listExtensionsTer[] = $currentExtension;
            }
        }

        if (! empty($allExtension['dev'])) {
            foreach ($allExtension['dev'] as $itemValue) {
                $listExtensionsDev[] = $this->getExtensionInformations($itemValue);
            }
        }

        if (! empty($allExtension['unloaded'])) {
            foreach ($allExtension['unloaded'] as $itemValue) {
                $listExtensionsUnloaded[] = $this->getExtensionInformations($itemValue);
            }
        }

        $view = $this->createView();
        $view->assign('extensionsSummary', [
            'loaded' => count($listExtensionsTer) + count($listExtensionsDev),
            'ter' => count($listExtensionsTer),
            'development' => count($listExtensionsDev),
            'updates' => $extensionsToUpdate,
        ]);
        $view->assign('listExtensionsTer', $listExtensionsTer);
        $view->assign('listExtensionsDev', $listExtensionsDev);
        $view->assign('listExtensionsUnloaded', $listExtensionsUnloaded);
        $view->assign('composer', Environment::isComposerMode());
        return $view->render('extensions-fluid');
    }

    /**
     * Get all necessary informations about an ext
     *
     * @param array<string, mixed> $itemValue
     * @return array{
     *     extension: string,
     *     version: string,
     *     latestVersion: string,
     *     latestVersionDate: string,
     *     updateAvailable: bool,
     *     compareUrl: string,
     *     compareUrlLast: string,
     *     tables: list<array{name: string, columns: list<string>}>
     * }
     */
    public function getExtensionInformations(array $itemValue): array
    {
        $extKey = (string) ($itemValue['extkey'] ?? '');
        $extVersion = (string) ($itemValue['version'] ?? '');
        $latestVersionData = is_array($itemValue['lastversion'] ?? null) ? $itemValue['lastversion'] : [];
        $latestVersion = (string) ($latestVersionData['version'] ?? '');
        $updateAvailable = $extVersion !== '' && $latestVersion !== '' && version_compare($extVersion, $latestVersion, '<');

        $compareUrl = (string) $this->uriBuilder->buildUriFromRoute('additional_reports_compareFiles', [
            'extKey' => $extKey,
            'mode' => 'compareExtension',
            'extVersion' => $extVersion,
        ]);

        $compareUrlLast = '';
        if ($updateAvailable) {
            $compareUrlLast = (string) $this->uriBuilder->buildUriFromRoute('additional_reports_compareFiles', [
                'extKey' => $extKey,
                'mode' => 'compareExtension',
                'extVersion' => $latestVersion,
            ]);
        }

        return [
            'extension' => $extKey,
            'version' => $extVersion,
            'latestVersion' => $latestVersion,
            'latestVersionDate' => (string) ($latestVersionData['updatedate'] ?? ''),
            'updateAvailable' => $updateAvailable,
            'compareUrl' => $compareUrl,
            'compareUrlLast' => $compareUrlLast,
            'tables' => $this->extensionSchemaParser->parse((string) ($itemValue['fdfile'] ?? '')),
        ];
    }

    public function getIdentifier(): string
    {
        return 'additionalreports_extensions';
    }

    public function getTitle(): string
    {
        return 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:extensions_title';
    }

    public function getDescription(): string
    {
        return 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:extensions_description';
    }

    public function getIconIdentifier(): string
    {
        return 'module-reports';
    }
}
