<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 CERDAN Yohann <cerdanyohann@yahoo.fr>
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

tx_additionalreports_main::init();
require_once(PATH_t3lib . 'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF, 1); // This checks permissions and exits if the users has no permission for entry.

/**
 * Module 'additional_reports' for the 'additional_reports' extension.
 *
 * @author    CERDAN Yohann <cerdanyohann@yahoo.fr>
 * @package    TYPO3
 * @subpackage    tx_additionalreports
 */

class  tx_additionalreports_module1 extends t3lib_SCbase
{
	public $pageinfo;
	public $nbElementsPerPage = 15;
	public $display = 1;
	public $filtersCat = 1;
	public $baseURL = '';

	/**
	 * Initializes the Module
	 * @return    void
	 */
	function init() {
		global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;
		parent::init();
		// Check nb per page
		$nbPerPage = t3lib_div::_GP('nbPerPage');
		if ($nbPerPage !== null) {
			$this->nbElementsPerPage = $nbPerPage;
		}
		// Check the display mode
		$display = t3lib_div::_GP('display');
		if ($display !== null) {
			$GLOBALS['BE_USER']->setAndSaveSessionData('additional_reports_menu', $display);
			$this->display = $display;
		}
		// Check the session
		$sessionDisplay = $GLOBALS['BE_USER']->getSessionData('additional_reports_menu');
		if ($sessionDisplay !== null) {
			$this->display = $sessionDisplay;
		}
		$this->filtersCat = '';
		$this->baseURL = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'mod.php?M=tools_txadditionalreportsM1';
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return    void
	 */
	function menuConfig() {
		global $LANG;
		$this->MOD_MENU = array(
			'function' => tx_additionalreports_util ::getSubModules()
		);
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return    [type]        ...
	 */
	function main() {
		global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;

		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id, $this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id)) {

			// Draw the header.
			$this->doc = t3lib_div::makeInstance('bigDoc');
			$this->doc->JScode .= '';
			$this->doc->JScode .= '<link rel="stylesheet" type="text/css" href="' . tx_additionalreports_main::getCss() .'" media="all"/>';
			$this->doc->JScode .= '<link rel="stylesheet" type="text/css" href="' . t3lib_extMgm::extRelPath('additional_reports') . 'libs/shadowbox.css" media="all"/>';
			$this->doc->JScode .= '<script src="contrib/prototype/prototype.js" type="text/javascript"></script>';
			$this->doc->JScode .= '<script src="' . t3lib_extMgm::extRelPath('additional_reports') . 'libs/shadowbox.js" type="text/javascript"></script>';
			$this->doc->divClass = '';
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form = '<form action="" method="post" enctype="multipart/form-data">';

			// JavaScript
			$this->doc->JScode .= '
                            <script language="javascript" type="text/javascript">
                                script_ended = 0;
                                function jumpToUrl(URL)    {
                                    document.location = URL;
                                }
                            </script>
                        ';
			$this->doc->postCode = '
                            <script language="javascript" type="text/javascript">
                                script_ended = 1;
                                if (top.fsMod) top.fsMod.recentIds["web"] = 0;
                            </script>
                        ';

			$headerSection = $this->doc->getHeader('pages', $this->pageinfo, $this->pageinfo['_thePath']) . '<br />' . $LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path') . ': ' . t3lib_div::fixed_lgd_cs($this->pageinfo['_thePath'], 50);

			$this->content .= $this->doc->startPage($LANG->getLL('title'));
			$this->content .= $this->doc->header($LANG->getLL('title'));
			$this->content .= $this->doc->spacer(5);
			$this->content .= $this->doc->section('', $this->doc->funcMenu($headerSection, t3lib_BEfunc::getFuncMenu($this->id, 'SET[function]', $this->MOD_SETTINGS['function'], $this->MOD_MENU['function'])));
			$this->content .= $this->doc->divider(5);


			// Render content:
			$this->content .= '<div style="height:100%;overflow-y:auto;">';
			$this->moduleContent();
			$this->content .= '</div>';

			// ShortCut
			if ($BE_USER->mayMakeShortcut()) {
				$this->content .= $this->doc->spacer(20) . $this->doc->section('', $this->doc->makeShortcutIcon('id', implode(',', array_keys($this->MOD_MENU)), $this->MCONF['name']));
			}

			$this->content .= $this->doc->spacer(10);
		} else {
			// If no access or if ID == zero

			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;

			$this->content .= $this->doc->startPage($LANG->getLL('title'));
			$this->content .= $this->doc->header($LANG->getLL('title'));
			$this->content .= $this->doc->spacer(5);
			$this->content .= $this->doc->spacer(10);
		}

	}

	/**
	 * Prints out the module HTML
	 *
	 * @return    void
	 */
	function printContent() {

		$this->content .= $this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 *
	 * @return    void
	 */
	function moduleContent() {
		$this->doc->inDocStylesArray[] = t3lib_div::getURL(t3lib_extMgm::extPath('additional_reports') . 'res/tx_additionalreports.css');
		$action = (string)$this->MOD_SETTINGS['function'];
		$this->content .= tx_additionalreports_main::$action();
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/additional_reports/mod1/index.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/additional_reports/mod1/index.php']);
}


// Make instance:
$SOBE = t3lib_div::makeInstance('tx_additionalreports_module1');
$SOBE->init();

// Include files?
foreach ($SOBE->include_once as $INC_FILE) include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>