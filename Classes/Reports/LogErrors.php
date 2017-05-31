<?php

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

class LogErrors extends \Sng\AdditionalReports\Reports\AbstractReport implements \TYPO3\CMS\Reports\ReportInterface
{

    /**
     * Constructor for class tx_additionalreports_xclass
     *
     * @param    object    Back-reference to the calling reports module
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
