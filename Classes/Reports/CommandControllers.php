<?php

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Console\CommandRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Reports\ReportInterface;

class CommandControllers extends AbstractReport implements ReportInterface
{

    /**
     * This method renders the report
     *
     * @return string The status report as HTML
     */
    public function getReport()
    {
        return $this->display();
    }

    /**
     * Generate the CommandControllers report
     *
     * @return string HTML code
     */
    public function display()
    {
        $items = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'];
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/commandcontrollers-fluid.html');
        $view->assign('itemsOld', $items);

        $commands = GeneralUtility::makeInstance(CommandRegistry::class);
        $items = [];
        foreach ($commands->getIterator() as $cmd => $el) {
            $items[$cmd] = get_class($el);
        }
        $view->assign('itemsNew', $items);

        return $view->render();
    }
}
