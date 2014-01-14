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
 * @author         CERDAN Yohann <cerdanyohann@yahoo.fr>
 * @package        TYPO3
 */
class tx_additionalreports_websitesconf extends tx_additionalreports_report implements tx_reports_Report {

	/**
	 * This method renders the report
	 *
	 * @return    string    The status report as HTML
	 */
	public function getReport() {
		$content = '';
		$content .= tx_additionalreports_main::displayWebsitesConf();
		return $content;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['websitesconf']['ext/additional_reports/Classes/Reports/class.tx_additionalreports_websitesconf.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['websitesconf']['ext/additional_reports/Classes/Reports/class.tx_additionalreports_websitesconf.php']);
}

?>