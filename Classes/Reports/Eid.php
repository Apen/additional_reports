<?php

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

class Eid extends \Sng\AdditionalReports\Reports\AbstractReport implements \TYPO3\CMS\Reports\ReportInterface
{

    /**
     * This method renders the report
     *
     * @return    string    The status report as HTML
     */
    public function getReport()
    {
        $content = $this->display();
        return $content;
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
                preg_match('/EXT:(.*?)\//', $itemValue, $ext);
                if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($ext[1])) {
                    $eids[] = [
                        'icon'      => \Sng\AdditionalReports\Utility::getExtIcon($ext[1]),
                        'extension' => $ext[1],
                        'name'      => $itemKey,
                        'path'      => $itemValue
                    ];
                }
            }
        }

        $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/eid-fluid.html');
        $view->assign('eids', $eids);
        return $view->render();
    }
}
