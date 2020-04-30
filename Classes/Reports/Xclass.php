<?php

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Reports\ReportInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use Sng\AdditionalReports\Utility;

class Xclass extends AbstractReport implements ReportInterface
{

    /**
     * This method renders the report
     *
     * @return string the status report as HTML
     */
    public function getReport()
    {
        $content = '<p class="help">' . $GLOBALS['LANG']->getLL('xclass_description') . '</p>';
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
        $view->assign('typo3version', Utility::intFromVer(TYPO3_version));
        return $view->render();
    }
}
