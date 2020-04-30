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
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Eid extends AbstractReport implements ReportInterface
{

    /**
     * This method renders the report
     *
     * @return string the status report as HTML
     */
    public function getReport()
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
                if (ExtensionManagementUtility::isLoaded($ext[1])) {
                    $eids[] = [
                        'icon'      => Utility::getExtIcon($ext[1]),
                        'extension' => $ext[1],
                        'name'      => $itemKey,
                        'path'      => $itemValue
                    ];
                }
            }
        }

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/eid-fluid.html');
        $view->assign('eids', $eids);
        return $view->render();
    }
}
