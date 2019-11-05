<?php

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

class Hooks extends \Sng\AdditionalReports\Reports\AbstractReport implements \TYPO3\CMS\Reports\ReportInterface
{

    /**
     * This method renders the report
     *
     * @return    string    The status report as HTML
     */
    public function getReport()
    {
        $content = '<p class="help">' . $GLOBALS['LANG']->getLL('hooks_description') . '</p>';
        $content .= $this->display();
        return $content;
    }

    /**
     * Generate the hooks report
     *
     * @return string HTML code
     */
    public function display()
    {
        $hooks = array();

        // core hooks
        $items = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'];
        if (count($items) > 0) {
            foreach ($items as $itemKey => $itemValue) {
                if (preg_match('/.*?\/.*?\.php/', $itemKey, $matches)) {
                    foreach ($itemValue as $hookName => $hookList) {
                        $hookList = \Sng\AdditionalReports\Utility::getHook($hookList);
                        if (!empty($hookList)) {
                            $hooks['core'][] = array(
                                'corefile' => $itemKey,
                                'name'     => $hookName,
                                'file'     => \Sng\AdditionalReports\Utility::viewArray($hookList)
                            );
                        }
                    }
                }
            }
        }

        $items = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];
        if (count($items) > 0) {
            foreach ($items as $itemKey => $itemValue) {
                foreach ($itemValue as $hookName => $hookList) {
                    $hookList = \Sng\AdditionalReports\Utility::getHook($hookList);
                    if (!empty($hookList)) {
                        $hooks['extensions'][] = array(
                            'corefile' => $itemKey,
                            'name'     => $hookName,
                            'file'     => \Sng\AdditionalReports\Utility::viewArray($hookList)
                        );
                    }
                }
            }
        }

        $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/hooks-fluid.html');
        $view->assign('hooks', $hooks);
        return $view->render();
    }

}

