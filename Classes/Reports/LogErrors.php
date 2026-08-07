<?php

declare(strict_types=1);

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LogErrors extends AbstractReport
{
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
    public function display()
    {
        $queryBuilder = Utility::getQueryBuilder('sys_log');
        $queryBuilder
            ->select('details')
            ->addSelectLiteral('COUNT(*) AS nb', 'MAX(tstamp) AS tstamp')
            ->from('sys_log')
            ->where($queryBuilder->expr()->gt('error', 0))
            ->groupBy('details');
        $orderBy = $this->getRequestParameter('orderby');
        $allowedOrderings = [
            'nb ASC' => ['nb', 'ASC'],
            'nb DESC' => ['nb', 'DESC'],
            'tstamp ASC' => ['tstamp', 'ASC'],
            'tstamp DESC' => ['tstamp', 'DESC'],
        ];
        $orderKey = is_string($orderBy) ? $orderBy : '';
        [$orderField, $orderDirection] = $allowedOrderings[$orderKey] ?? ['nb', 'DESC'];
        $queryBuilder->orderBy($orderField, $orderDirection)->addOrderBy('tstamp', 'DESC');

        $view = $this->createView();
        $view->assign('reportname', Utility::hasLegacyListType() ? 'additionalreports_logerrors' : 'logerrors');
        $view->assign('paginationRoute', $this->getCurrentRouteIdentifier());
        $view->assign('extconf', unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['additional_reports'] ?? ''));
        $view->assign('requestDir', GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR'));

        Utility::buildPagination($queryBuilder->executeQuery()->fetchAllAssociative(), (int) ($this->getRequestParameter('currentPage') ?? 1), $view);

        return $view->render('logerrors-fluid');
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
        return 'additionalreports_logerrors';
    }
}
