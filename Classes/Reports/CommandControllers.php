<?php

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

class CommandControllers extends \Sng\AdditionalReports\Reports\AbstractReport implements \TYPO3\CMS\Reports\ReportInterface
{

    /**
     * This method renders the report
     *
     * @return string The status report as HTML
     */
    public function getReport()
    {
        $content = $this->display();
        return $content;
    }

    /**
     * Generate the CommandControllers report
     *
     * @return string HTML code
     */
    public function display()
    {
        $items = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'];
        $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/commandcontrollers-fluid.html');
        $view->assign('items', $items);
        return $view->render();
    }

}

