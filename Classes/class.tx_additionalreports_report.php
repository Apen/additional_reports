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
class tx_additionalreports_report {

    /**
     * Back-reference to the calling reports module
     *
     * @var    object $reportObject
     */
    protected $reportObject;

    /**
     * Constructor for class tx_additionalreports_report
     *
     * @param    object    Back-reference to the calling reports module
     */
    public function __construct($reportObject) {
        $this->reportObject = $reportObject;
        // include Css files
        $this->setCss(tx_additionalreports_main::getCss());
        // include LL
        $GLOBALS['LANG']->includeLLFile('EXT:additional_reports/locallang.xml');
    }

    /**
     * Set a Css
     *
     * @param $path
     * @return void
     */
    public function setCss($path) {
        if (isset($this->reportObject->doc)) {
            $this->reportObject->doc->getPageRenderer()->addCssFile($path);
        }
        $doc = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate');
        $pageRenderer = $doc->getPageRenderer()->addCssFile($path);
    }

    /**
     * Set a Js
     *
     * @param $path
     * @return void
     */
    public function setJs($path) {
        if (isset($this->reportObject->doc)) {
            $this->reportObject->doc->getPageRenderer()->addJsFile($path);
        }
        $doc = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate');
        $pageRenderer = $doc->getPageRenderer()->addJsFile($path);
    }

}


?>