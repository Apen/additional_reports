<?php

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

class Xclass extends \Sng\AdditionalReports\Reports\AbstractReport implements \TYPO3\CMS\Reports\ReportInterface
{

    /**
     * This method renders the report
     *
     * @return    string    The status report as HTML
     */
    public function getReport()
    {
        $content = '<p class="help">' . $GLOBALS['LANG']->getLL('xclass_description') . '</p>';
        $content .= $this->display();
        return $content;
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
        $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/xclass-fluid.html');
        $view->assign('xclass', $xclassList);
        $view->assign('typo3version', \Sng\AdditionalReports\Utility::intFromVer(TYPO3_version));
        return $view->render();
    }
}
