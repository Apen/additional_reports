<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\AdditionalReports\Service\EventListenerRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EventDispatcher extends AbstractReport
{
    private EventListenerRegistry $eventListenerRegistry;

    public function __construct(?object $reportObject = null, ?EventListenerRegistry $eventListenerRegistry = null)
    {
        parent::__construct($reportObject);
        $this->eventListenerRegistry = $eventListenerRegistry ?? GeneralUtility::makeInstance(EventListenerRegistry::class);
    }

    /**
     * This method renders the report
     *
     * @return string the status report as HTML
     */
    public function getReport(): string
    {
        return $this->display();
    }

    /**
     * Generate the eventdispatcher report
     *
     * @return string HTML code
     */
    public function display()
    {
        $view = $this->createView();
        $view->assign('events', $this->eventListenerRegistry->findAll());
        return $view->render('events-fluid');
    }

    public function getIdentifier(): string
    {
        return 'additionalreports_eventdispatcher';
    }

    public function getTitle(): string
    {
        return 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:eventdispatcher_title';
    }

    public function getDescription(): string
    {
        return 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:eventdispatcher_description';
    }

    public function getIconIdentifier(): string
    {
        return 'module-reports';
    }
}
