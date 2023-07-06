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

class Hooks extends AbstractReport
{
    /**
     * This method renders the report
     *
     * @return string the status report as HTML
     */
    public function getReport(): string
    {
        $content = '<p class="help">' . Utility::getLanguageService()->getLL('hooks_description') . '</p>';
        return $content . $this->display();
    }

    /**
     * Generate the hooks report
     *
     * @return string HTML code
     */
    public function display()
    {
        $hooks = [];

        // core hooks
        $items = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'];
        if (count($items) > 0) {
            foreach ($items as $itemKey => $itemValue) {
                if (preg_match('#.*?\/.*?\.php#', $itemKey, $matches)) {
                    foreach ($itemValue as $hookName => $hookList) {
                        $hookList = Utility::getHook($hookList);
                        if (!empty($hookList)) {
                            $hooks['core'][] = [
                                'corefile' => $itemKey,
                                'name'     => $hookName,
                                'file'     => Utility::viewArray($hookList)
                            ];
                        }
                    }
                }
            }
        }

        $items = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];
        if (count($items) > 0) {
            foreach ($items as $itemKey => $itemValue) {
                foreach ($itemValue as $hookName => $hookList) {
                    $hookList = Utility::getHook($hookList);
                    if (!empty($hookList)) {
                        $hooks['extensions'][] = [
                            'corefile' => $itemKey,
                            'name'     => $hookName,
                            'file'     => Utility::viewArray($hookList)
                        ];
                    }
                }
            }
        }

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/hooks-fluid.html');
        $view->assign('hooks', $hooks);
        return $view->render();
    }

    public function getIdentifier(): string
    {
        return 'additionalreports_hooks';
    }

    public function getTitle(): string
    {
        return 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:hooks_title';
    }

    public function getDescription(): string
    {
        return 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:hooks_description';
    }

    public function getIconIdentifier(): string
    {
        return 'additionalreports_hooks';
    }
}
