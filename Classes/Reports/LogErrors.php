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
        $query = [];
        $query['SELECT'] = 'COUNT(*) AS "nb",details,MAX(tstamp) as "tstamp"';
        $query['FROM'] = 'sys_log';
        $query['WHERE'] = 'error>0';
        $query['GROUPBY'] = 'details';
        $query['ORDERBY'] = 'nb DESC,tstamp DESC';
        $query['LIMIT'] = '';

        $orderby = Utility::_GP('orderby');
        if ($orderby !== null) {
            $query['ORDERBY'] = $orderby;
        }

        $content = Utility::writeInformation(
            Utility::getLl('flushalllog'),
            'DELETE FROM sys_log WHERE error > 0;'
        );

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/logerrors-fluid.html');
        $view->setPartialRootPaths([ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Partials/']);
        $view->assign('reportname', $_GET['report'] ?? 'additionalreports_logerrors');
        $view->assign('extconf', unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['additional_reports'] ?? ''));
        $view->assign('baseUrl', Utility::getBaseUrl());
        $view->assign('requestDir', GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR'));

        Utility::buildPagination(Utility::exec_SELECT_queryArrayRows($query), !empty($_GET['currentPage']) ? (int)$_GET['currentPage'] : 1, $view);

        return $content . $view->render();
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
