<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Schema\Parser\Lexer;
use TYPO3\CMS\Core\Database\Schema\Parser\Parser;
use TYPO3\CMS\Core\Database\Schema\SqlReader;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Extensions extends AbstractReport
{
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

        $allExtension = Utility::getExtensionList();

        $listExtensionsTer = [];
        $listExtensionsDev = [];
        $listExtensionsUnloaded = [];

        if (! empty($allExtension['ter'])) {
            foreach ($allExtension['ter'] as $itemValue) {
                $currentExtension = $this->getExtensionInformations($itemValue);
                if (version_compare($itemValue['version'], $itemValue['lastversion']['version'], '<')) {
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
     * @param array $itemValue
     * @return array
     */
    public function getExtensionInformations($itemValue)
    {
        $extKey = $itemValue['extkey'];
        $extVersion = $itemValue['version'] ?? '';
        $listExtensionsTerItem = [];
        $listExtensionsTerItem['extension'] = $extKey;
        $listExtensionsTerItem['version'] = $extVersion;

        // version compare
        $compareUrl = GeneralUtility::getIndpEnv('TYPO3_SITE_URL');

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $routeIdentifier = 'additional_reports_compareFiles';
        $uri = (string) $uriBuilder->buildUriFromRoute($routeIdentifier, []);

        // Bugfix for wrong CompareUrl in case of TYPO3 is installed in a subdirectory
        if (strpos($uri, 'typo3/index.php') > 0) {
            $uri = substr($uri, strpos($uri, 'typo3/index.php'));
        }

        $compareUrl .= $uri;
        $compareUrl .= '&extKey=' . $extKey . '&mode=compareExtension&extVersion=' . $extVersion;
        $listExtensionsTerItem['compareUrl'] = $compareUrl;

        // need extension update ?
        if (version_compare($extVersion, $itemValue['lastversion']['version'] ?? '', '<')) {
            $listExtensionsTerItem['versionlast'] = '<span style="color:green;font-weight:bold;">' . $itemValue['lastversion']['version'] . '&nbsp;(' . $itemValue['lastversion']['updatedate'] . ')</span>';
            $compareUrl = GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
            $compareUrl .= $uri;
            $compareUrl .= '&extKey=' . $extKey . '&mode=compareExtension&extVersion=' . $itemValue['lastversion']['version'];
            $listExtensionsTerItem['compareUrlLast'] = $compareUrl;
        } else {
            $listExtensionsTerItem['versionlast'] = ($itemValue['lastversion']['version'] ?? '') . '&nbsp;(' . ($itemValue['lastversion']['updatedate'] ?? '') . ')';
            $listExtensionsTerItem['compareUrlLast'] = '';
        }

        $listExtensionsTerItem['downloads'] = $itemValue['lastversion']['alldownloadcounter'] ?? '';
        $listExtensionsTerItem['tables'] = $this->getExtensionTables((string) ($itemValue['fdfile'] ?? ''));

        // need extconf update
        $listExtensionsTerItem['confintegrity'] = Utility::getLl('no');
        $listExtensionsTerItem['confintegrityContent'] = '';

        return $listExtensionsTerItem;
    }

    /**
     * Uses the same SQL reader and schema parser as TYPO3's database analyzer.
     *
     * @return list<array{name: string, columns: list<string>}>
     */
    public function getExtensionTables(string $sql): array
    {
        if (trim($sql) === '') {
            return [];
        }

        $sqlReader = GeneralUtility::makeInstance(SqlReader::class);
        $parser = new Parser(new Lexer());
        $tables = [];
        foreach ($sqlReader->getCreateTableStatementArray($sql) as $statement) {
            try {
                $parsedTables = $parser->parse($statement);
            } catch (\Throwable) {
                continue;
            }
            foreach ($parsedTables as $table) {
                $tables[] = [
                    'name' => $table->getName(),
                    'columns' => array_map(static fn($column): string => $column->getName(), $table->getColumns()),
                ];
            }
        }
        return $tables;
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
        return 'additionalreports_extensions';
    }
}
