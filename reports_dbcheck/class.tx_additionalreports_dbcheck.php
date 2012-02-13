<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 CERDAN Yohann <cerdanyohann@yahoo.fr>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * This class provides a report displaying a list of informations
 * Code inspired by EXT:dam/lib/class.tx_dam_svlist.php by Rene Fritz
 *
 * @author        CERDAN Yohann <cerdanyohann@yahoo.fr>
 * @package        TYPO3
 */

class tx_additionalreports_dbcheck implements tx_reports_Report
{

	/**
	 * Back-reference to the calling reports module
	 *
	 * @var    tx_reports_Module    $reportObject
	 */

	protected $reportObject;

	/**
	 * Constructor for class tx_additionalreports_dbcheck
	 *
	 * @param    tx_reports_Module    Back-reference to the calling reports module
	 */

	public function __construct(tx_reports_Module $reportObject) {
		$this->reportObject = $reportObject;
		tx_additionalreports_main::init();
	}

	/**
	 * This method renders the report
	 *
	 * @return    string    The status report as HTML
	 */

	public function getReport() {
		$this->reportObject->doc->getPageRenderer()->addCssFile(t3lib_extMgm::extRelPath('additional_reports') . 'tx_additionalreports.css');
		$content = '<p class="help">' . $GLOBALS['LANG']->getLL('dbcheck_description') . '</p>';
		$content .= tx_additionalreports_main::displayDbCheck();
		return $content;
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/additional_reports/reports_dbcheck/class.tx_additionalreports_dbcheck.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/additional_reports/reports_dbcheck/class.tx_additionalreports_dbcheck.php']);
}

?>