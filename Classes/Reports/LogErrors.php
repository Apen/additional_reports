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
     * Constructor for class tx_additionalreports_xclass
     *
     * @param object    Back-reference to the calling reports module
     */
    public function __construct($reportObject)
    {
        parent::__construct($reportObject);
    }

    /**
     * This method renders the report
     *
     * @return    string    The status report as HTML
     */

    public function getReport()
    {
        $content = \Sng\AdditionalReports\Main::displayLogErrors();
        return $content;
    }

}
