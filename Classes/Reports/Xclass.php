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

class Xclass extends AbstractReport
{
    /**
     * This method renders the report
     *
     * @return string the status report as HTML
     */
    public function getReport(): string
    {
        $content = '<p class="help">' . Utility::getLL('xclass_description') . '</p>';
        return $content . $this->display();
    }

    /**
     * Generate the xclass report
     *
     * @return string HTML code
     */
    public function display()
    {
        $xclassList = [];
        $xclassList['objects'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'];
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/xclass-fluid.html');
        $view->assign('xclass', $xclassList);
        return $view->render();
    }

    public function getIdentifier(): string
    {
        return 'additionalreports_xclass';
    }

    public function getTitle(): string
    {
        return 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:xclass_title';
    }

    public function getDescription(): string
    {
        return 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:xclass_description';
    }

    public function getIconIdentifier(): string
    {
        return 'additionalreports_xclass';
    }
}
