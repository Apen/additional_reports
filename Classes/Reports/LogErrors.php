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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Sng\AdditionalReports\Utility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class LogErrors extends AbstractReport implements ReportInterface
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
     * Generate the log error report
     *
     * @return string HTML code
     */
    public function display()
    {

        // query
        $query = [];
        $query['SELECT'] = 'COUNT(*) AS "nb",details,MAX(tstamp) as "tstamp"';
        $query['FROM'] = 'sys_log';
        $query['WHERE'] = 'error>0';
        $query['GROUPBY'] = 'details';
        $query['ORDERBY'] = 'nb DESC,tstamp DESC';
        $query['LIMIT'] = '';

        $orderby = GeneralUtility::_GP('orderby');
        if ($orderby !== null) {
            $query['ORDERBY'] = $orderby;
        }

        $content = Utility::writeInformation(
            Utility::getLl('flushalllog'),
            'DELETE FROM sys_log WHERE error>0;'
        );

        $logErrors = [];

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/logerrors-fluid.html');
        $view->assign('extconf', unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['additional_reports']));
        $view->assign('baseUrl', Utility::getBaseUrl());
        $view->assign('requestDir', GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR'));
        $view->assign('query', $query);

        return $content . $view->render();
    }
}
