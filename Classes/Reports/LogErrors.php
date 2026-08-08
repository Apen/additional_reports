<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use Sng\AdditionalReports\Repository\LogErrorRepository;
use Sng\AdditionalReports\Service\PaginationService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Reports\ReportInterface;

class LogErrors extends AbstractReport
{
    private readonly LogErrorRepository $logErrorRepository;

    private readonly PaginationService $paginationService;

    public function __construct(
        ?object $reportObject = null,
        ?LogErrorRepository $logErrorRepository = null,
        ?PaginationService $paginationService = null,
    ) {
        parent::__construct($reportObject);
        $this->logErrorRepository = $logErrorRepository ?? GeneralUtility::makeInstance(LogErrorRepository::class);
        $this->paginationService = $paginationService ?? GeneralUtility::makeInstance(PaginationService::class);
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
     * Generate the log error report
     *
     * @return string HTML code
     */
    public function display(): string
    {
        $orderBy = $this->getRequestParameter('orderby');

        $view = $this->createView('logerrors-fluid');
        $view->assign('reportname', interface_exists(ReportInterface::class) ? 'additionalreports_logerrors' : 'logerrors');
        $view->assign('paginationRoute', $this->getCurrentRouteIdentifier());

        $this->paginationService->assign(
            $this->logErrorRepository->findGrouped(is_string($orderBy) ? $orderBy : null),
            (int) ($this->getRequestParameter('currentPage') ?? 1),
            $view,
        );

        return $view->render();
    }

    public function getIdentifier(): string
    {
        return 'additionalreports_logerrors';
    }

    public function getTitle(): string
    {
        return 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:logerrors_title';
    }

    public function getDescription(): string
    {
        return 'LLL:EXT:additional_reports/Resources/Private/Language/locallang.xlf:logerrors_description';
    }

    public function getIconIdentifier(): string
    {
        return 'module-reports';
    }
}
