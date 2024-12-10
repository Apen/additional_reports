<?php

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

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

        $allExtension = Utility::getInstExtList(Utility::getPathTypo3Conf() . 'ext/');

        $listExtensionsTer = [];
        $listExtensionsDev = [];
        $listExtensionsUnloaded = [];

        if (!empty($allExtension['ter'])) {
            foreach ($allExtension['ter'] as $itemValue) {
                $currentExtension = $this->getExtensionInformations($itemValue);
                if (version_compare($itemValue['EM_CONF']['version'], $itemValue['lastversion']['version'], '<')) {
                    $extensionsToUpdate++;
                }
                $listExtensionsTer[] = $currentExtension;
            }
        }

        if (!empty($allExtension['dev'])) {
            foreach ($allExtension['dev'] as $itemValue) {
                $listExtensionsDev[] = $this->getExtensionInformations($itemValue);
            }
        }

        if (!empty($allExtension['unloaded'])) {
            foreach ($allExtension['unloaded'] as $itemValue) {
                $listExtensionsUnloaded[] = $this->getExtensionInformations($itemValue);
            }
        }

        $addContent = '';
        $addContent .= (count($listExtensionsTer) + count($listExtensionsDev)) . ' ' . Utility::getLl('extensions_extensions');
        $addContent .= '<br/>';
        $addContent .= count($listExtensionsTer) . ' ' . Utility::getLl('extensions_ter');
        $addContent .= '  /  ';
        $addContent .= count($listExtensionsDev) . ' ' . Utility::getLl('extensions_dev');
        $addContent .= '<br/>';
        $addContent .= $extensionsToUpdate . ' ' . Utility::getLl('extensions_toupdate');
        $addContentItem = Utility::writeInformation(Utility::getLl('pluginsmode5') . '<br/>' . Utility::getLl('extensions_updateter') . '', $addContent);

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/extensions-fluid.html');
        $view->assign('listExtensionsTer', $listExtensionsTer);
        $view->assign('listExtensionsDev', $listExtensionsDev);
        $view->assign('listExtensionsUnloaded', $listExtensionsUnloaded);
        $view->assign('composer', Utility::isComposerMode());
        return $addContentItem . $view->render();
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
        $extVersion = $itemValue['EM_CONF']['version'] ?? '';
        $listExtensionsTerItem = [];
        $listExtensionsTerItem['extension'] = $extKey;
        $listExtensionsTerItem['version'] = $extVersion;

        // version compare
        $compareUrl = GeneralUtility::getIndpEnv('TYPO3_SITE_URL');

        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $routeIdentifier = 'additional_reports_compareFiles';
        $uri = (string)$uriBuilder->buildUriFromRoute($routeIdentifier, []);

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
        $listExtensionsTerItem['tablesmodal'] = !empty($itemValue['fdfile']) ? '<pre class="pre-scrollable">' . (htmlspecialchars($itemValue['fdfile'])) . '</pre>' : '';

        // need extconf update
        $listExtensionsTerItem['confintegrity'] = Utility::getLl('no');
        $listExtensionsTerItem['confintegrityContent'] = '';

        return $listExtensionsTerItem;
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
