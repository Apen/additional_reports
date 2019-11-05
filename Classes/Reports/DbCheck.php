<?php

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

class DbCheck extends \Sng\AdditionalReports\Reports\AbstractReport implements \TYPO3\CMS\Reports\ReportInterface
{

    /**
     * This method renders the report
     *
     * @return    string    The status report as HTML
     */
    public function getReport()
    {
        $content = '<p class="help">' . $GLOBALS['LANG']->getLL('dbcheck_description') . '</p>';
        $content .= $this->display();
        return $content;
    }

    /**
     * Generate the dbcheck report
     *
     * @return string HTML code
     */
    public function display()
    {
        $sqlStatements = \Sng\AdditionalReports\Utility::getSqlUpdateStatements();
        $dbchecks = array();

        if (!empty($sqlStatements['update']['add'])) {
            $dbchecks[1]['title'] = 'Add fields';
            $dbchecks[1]['items'] = $sqlStatements['update']['add'];
        }

        if (!empty($sqlStatements['update']['change'])) {
            $dbchecks[2]['title'] = 'Changing fields';
            foreach ($sqlStatements['update']['change'] as $itemKey => $itemValue) {
                if (isset($sqlStatements['update']['change_currentValue'][$itemKey])) {
                    $dbchecks[2]['items'][] = $itemValue . ' -- [current: ' . $sqlStatements['update']['change_currentValue'][$itemKey] . ']';
                } else {
                    $dbchecks[2]['items'][] = $itemValue;
                }
            }
        }

        if (!empty($sqlStatements['remove']['change'])) {
            $dbchecks[3]['title'] = 'Remove unused fields (rename with prefix)';
            $dbchecks[3]['items'] = $sqlStatements['remove']['change'];
        }

        if (!empty($sqlStatements['remove']['drop'])) {
            $dbchecks[4]['title'] = 'Drop fields (really!)';
            $dbchecks[4]['items'] = $sqlStatements['remove']['drop'];
        }

        if (!empty($sqlStatements['update']['create_table'])) {
            $dbchecks[5]['title'] = 'Add tables';
            $dbchecks[5]['items'] = $sqlStatements['update']['create_table'];
        }

        if (!empty($sqlStatements['remove']['change_table'])) {
            $dbchecks[6]['title'] = 'Removing tables (rename with prefix)';
            foreach ($sqlStatements['remove']['change_table'] as $itemKey => $itemValue) {
                if (!empty($sqlStatements['remove']['tables_count'][$itemKey])) {
                    $dbchecks[6]['items'][] = $itemValue . ' -- [' . $sqlStatements['remove']['tables_count'][$itemKey] . ']';
                } else {
                    $dbchecks[6]['items'][] = $itemValue . ' -- [empty]';
                }
            }
        }

        if (!empty($sqlStatements['remove']['drop_table'])) {
            $dbchecks[7]['title'] = 'Drop tables (really!)';
            foreach ($sqlStatements['remove']['drop_table'] as $itemKey => $itemValue) {
                if (!empty($sqlStatements['remove']['tables_count'][$itemKey])) {
                    $dbchecks[7]['items'][] = $itemValue . ' -- [' . $sqlStatements['remove']['tables_count'][$itemKey] . ']';
                } else {
                    $dbchecks[7]['items'][] = $itemValue . ' -- [empty]';
                }
            }
        }

        // dump sql structure
        $items = \Sng\AdditionalReports\Utility::exec_SELECTgetRows(
            'table_name',
            'information_schema.tables',
            'table_schema = \'' . $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['dbname'] . '\'', '', 'table_name'
        );

        $sqlStructure = '';

        foreach ($items as $table) {
            $resSqlDump = \Sng\AdditionalReports\Utility::sql_query('SHOW CREATE TABLE ' . $table['table_name']);
            $sqlDump = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resSqlDump);
            $sqlStructure .= $sqlDump['Create Table'] . "\r\n\r\n";
            $GLOBALS['TYPO3_DB']->sql_free_result($resSqlDump);
        }

        $content = '<h3 class="uppercase">Dump SQL Structure (md5:' . md5($sqlStructure) . ')</h3>';
        $content .= '<textarea style="width:100%;height:200px;">' . $sqlStructure . '</textarea>';

        $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplatePathAndFilename(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('additional_reports') . 'Resources/Private/Templates/dbcheck-fluid.html');
        $view->assign('dbchecks', $dbchecks);
        return $view->render() . $content;
    }

}
