<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 CERDAN Yohann <cerdanyohann@yahoo.fr>
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
class tx_additionalreports_status extends tx_additionalreports_report implements \TYPO3\CMS\Reports\ReportInterface
{

    /**
     * This method renders the report
     *
     * @return    string    The status report as HTML
     */
    public function getReport()
    {
        $content = '<p class="help">' . $GLOBALS['LANG']->getLL('status_description') . '</p>';

        if (!isset($this->reportObject->doc)) {
            $this->reportObject->doc = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate');
        }

        $this->reportObject->doc->getPageRenderer()->loadExtJS();
        $this->reportObject->doc->getPageRenderer()->addExtOnReadyCode('
			Ext.select("h2.section-header").each(function(element){
				element.on("click", function(event, tag) {
					var state = 0,
						el = Ext.fly(tag),
						div = el.next("div"),
						saveKey = el.getAttribute("rel");
					if (el.hasClass("collapsed")) {
						el.removeClass("collapsed").addClass("expanded");
						div.slideIn("t", {
							easing: "easeIn",
							duration: .5
						});
					} else {
						el.removeClass("expanded").addClass("collapsed");
						div.slideOut("t", {
							easing: "easeOut",
							duration: .5,
							remove: false,
							useDisplay: true
						});
						state = 1;
					}
					if (saveKey) {
						try {
							top.TYPO3.BackendUserSettings.ExtDirect.set(saveKey + "." + tag.id, state, function(response) {});
						} catch(e) {}
					}
				});
			});
		'
        );

        $content .= tx_additionalreports_main::displayStatus();
        return $content;
    }

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/additional_reports/Classes/Reports/class.tx_additionalreports_status.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/additional_reports/Classes/Reports/class.tx_additionalreports_status.php']);
}

?>