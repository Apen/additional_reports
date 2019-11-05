<?php

namespace Sng\AdditionalReports\Reports;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

class Status extends \Sng\AdditionalReports\Reports\AbstractReport implements \TYPO3\CMS\Reports\ReportInterface
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

        /*$this->reportObject->doc->getPageRenderer()->loadExtJS();
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
        );*/

        $content .= \Sng\AdditionalReports\Main::displayStatus();
        return $content;
    }

}
