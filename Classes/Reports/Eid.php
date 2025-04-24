<?php

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Reports\ReportInterface;

class Eid extends AbstractReport
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
     * Generate the eid report
     *
     * @return string HTML code
     */
    public function display()
    {
        $items = $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include'];
        $eids = [];

        if (count($items) > 0) {
            foreach ($items as $itemKey => $itemValue) {
                preg_match('#EXT:(.*?)\/#', $itemValue, $ext);
                if ($ext[1] ?? false) {
                    continue;
                }
                if (ExtensionManagementUtility::isLoaded($ext[1] ?? '')) {
                    $eids[] = [
                        'icon' => Utility::getExtIcon($ext[1]),
                        'extension' => $ext[1],
                        'name' => $itemKey,
                        'path' => $itemValue,
                    ];
                }
            }
        }

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/eid-fluid.html');
        $view->assign('eids', $eids);
        return $view->render();
    }

    public function getIdentifier(): string
    {
        return 'additionalreports_eid';
    }

    public function getTitle(): string
    {
        return 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:eid_title';
    }

    public function getDescription(): string
    {
        return 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:eid_description';
    }

    public function getIconIdentifier(): string
    {
        return 'additionalreports_eid';
    }
}
