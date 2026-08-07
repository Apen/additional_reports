<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Console\CommandRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CommandControllers extends AbstractReport
{
    /**
     * This method renders the report
     *
     * @return string The status report as HTML
     */
    public function getReport(): string
    {
        return $this->display();
    }

    /**
     * Generate the CommandControllers report
     *
     * @return string HTML code
     */
    public function display(): string
    {
        $view = $this->createView();
        $commands = GeneralUtility::makeInstance(CommandRegistry::class);
        $items = [];
        foreach ($commands->getSchedulableCommands() as $cmd => $el) {
            $items[$cmd] = get_class($el);
        }
        $view->assign('itemsNew', $items);
        return $view->render('commandcontrollers-fluid');
    }

    public function getIdentifier(): string
    {
        return 'additionalreports_commandcontrollers';
    }

    public function getTitle(): string
    {
        return 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:commandcontrollers_title';
    }

    public function getDescription(): string
    {
        return 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:commandcontrollers_description';
    }

    public function getIconIdentifier(): string
    {
        return 'module-reports';
    }
}
