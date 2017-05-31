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
        $content .= \Sng\AdditionalReports\Main::displayDbCheck();
        return $content;
    }

}
