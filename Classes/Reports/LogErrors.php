<?php

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

class LogErrors extends \Sng\AdditionalReports\Reports\AbstractReport implements \TYPO3\CMS\Reports\ReportInterface
{

    /**
     * This method renders the report
     *
     * @return    string    The status report as HTML
     */
    public function getReport()
    {
        $content = $this->display();
        return $content;
    }

    /**
     * Generate the log error report
     *
     * @return string HTML code
     */
    public function display()
    {

        // query
        $query = array();
        $query['SELECT'] = 'COUNT(*) AS "nb",details,MAX(tstamp) as "tstamp"';
        $query['FROM'] = 'sys_log';
        $query['WHERE'] = 'error>0';
        $query['GROUPBY'] = 'details';
        $query['ORDERBY'] = 'nb DESC,tstamp DESC';
        $query['LIMIT'] = '';

        $orderby = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('orderby');
        if ($orderby !== null) {
            $query['ORDERBY'] = $orderby;
        }

        $content = \Sng\AdditionalReports\Utility::writeInformation(
            \Sng\AdditionalReports\Utility::getLl('flushalllog'), 'DELETE FROM sys_log WHERE error>0;'
        );

        $logErrors = array();

        $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/logerrors-fluid.html');
        $view->assign('extconf', unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['additional_reports']));
        $view->assign('baseUrl', \Sng\AdditionalReports\Utility::getBaseUrl());
        $view->assign('requestDir', \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_DIR'));
        $view->assign('query', $query);

        return $content . $view->render();
    }

}
