<?php
/**
 * Copyright notice
 *
 *       (c) 2010 CERDAN Yohann <cerdanyohann@yahoo.fr>
 *       All rights reserved
 *
 *       This script is part of the TYPO3 project. The TYPO3 project is
 *       free software; you can redistribute it and/or modify
 *       it under the terms of the GNU General Public License as published by
 *       the Free Software Foundation; either version 2 of the License, or
 *       (at your option) any later version.
 *
 *       The GNU General Public License can be found at
 *       http://www.gnu.org/copyleft/gpl.html.
 *
 *       This script is distributed in the hope that it will be useful,
 *       but WITHOUT ANY WARRANTY; without even the implied warranty of
 *       MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *       GNU General Public License for more details.
 *
 *       This copyright notice MUST APPEAR in all copies of the script!
 */

/**
 * This class provides a report displaying a list of informations
 * Code inspired by EXT:dam/lib/class.tx_dam_svlist.php by Rene Fritz
 *
 * @author CERDAN Yohann <cerdanyohann@yahoo.fr>
 * @package TYPO3
 */

class tx_additionalreports_plugins implements tx_reports_Report
{
	/**
	 * Back-reference to the calling reports module
	 *
	 * @var tx_reports_Module $reportObject
	 */

	protected $reportObject;
	protected $nbElementsPerPage = 15;
	protected $display = 1;
	protected $filtersCat = 1;

	/**
	 * Constructor for class tx_additionalreports_plugins
	 *
	 * @param tx_reports_Module $ Back-reference to the calling reports module
	 */

	public function __construct(tx_reports_Module $reportObject)
	{
		$this->reportObject = $reportObject;
		$GLOBALS['LANG']->includeLLFile('EXT:additional_reports/locallang.xml');
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
	}

	/**
	 * This method renders the report
	 *
	 * @return string The status report as HTML
	 */

	public function getReport()
	{
		$content = '';
		$this->reportObject->doc->getPageRenderer()->addCssFile(t3lib_extMgm::extRelPath('additional_reports') . 'tx_additionalreports.css');
		//$content .= '<p class="help">' . $GLOBALS['LANG']->getLL('plugins_description') . '</p>';
		$content .= $this->displayPlugins();
		return $content;
	}

	protected function displayPlugins()
	{
		//$content = '<h3 class="uppercase">' . $GLOBALS['LANG']->getLL('pluginschoose') . '</h3>';
		$url = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'mod.php?M=tools_txreportsM1';
		$content = '<table>';
		$content .= '<tr>';
		$content .= '<td><input onClick="jumpToUrl(\'' . $url . '&display=1\');" style="margin-right:4px;" type="radio" name="display" value="1" id="radio1"' . (($this->display == 1) ? ' checked="checked"' : '') . '/><label for="radio1" style="margin-right:10px;">' . $GLOBALS['LANG']->getLL('pluginsmode1') . '</label></td>';
		$content .= '<td><input onClick="jumpToUrl(\'' . $url . '&display=4\');" style="margin-right:4px;" type="radio" name="display" value="4" id="radio4"' . (($this->display == 4) ? ' checked="checked"' : '') . '/><label for="radio4" style="margin-right:10px;">' . $GLOBALS['LANG']->getLL('pluginsmode4') . '</label></td>';
		$content .= '<td><input onClick="jumpToUrl(\'' . $url . '&display=6\');" style="margin-right:4px;" type="radio" name="display" value="6" id="radio6"' . (($this->display == 6) ? ' checked="checked"' : '') . '/><label for="radio6" style="margin-right:10px;">' . $GLOBALS['LANG']->getLL('pluginsmode4hidden') . '</label></td>';
		$content .= '</tr>';
		$content .= '<tr>';
		$content .= '<td><input onClick="jumpToUrl(\'' . $url . '&display=2\');" style="margin-right:4px;" type="radio" name="display" value="2" id="radio2"' . (($this->display == 2) ? ' checked="checked"' : '') . '/><label for="radio2" style="margin-right:10px;">' . $GLOBALS['LANG']->getLL('pluginsmode2') . '</label></td>';
		$content .= '<td><input onClick="jumpToUrl(\'' . $url . '&display=3\');" style="margin-right:4px;" type="radio" name="display" value="3" id="radio3"' . (($this->display == 3) ? ' checked="checked"' : '') . '/><label for="radio3" style="margin-right:10px;">' . $GLOBALS['LANG']->getLL('pluginsmode3') . '</label></td>';
		$content .= '<td><input onClick="jumpToUrl(\'' . $url . '&display=7\');" style="margin-right:4px;" type="radio" name="display" value="7" id="radio7"' . (($this->display == 7) ? ' checked="checked"' : '') . '/><label for="radio7" style="margin-right:10px;">' . $GLOBALS['LANG']->getLL('pluginsmode3hidden') . '</label></td>';
		$content .= '</tr>';
		$content .= '<tr>';
		$content .= '<td><input onClick="jumpToUrl(\'' . $url . '&display=5\');" style="margin-right:4px;" type="radio" name="display" value="5" id="radio5"' . (($this->display == 5) ? ' checked="checked"' : '') . '/><label for="radio5" style="margin-right:10px;">' . $GLOBALS['LANG']->getLL('pluginsmode5') . '</label></td>';
		$content .= '</tr>';
		$content .= '</table><div class="uppercase"></div>';

		$content .= $this->reportObject->doc->spacer(5);

		switch ($this->display) {
			case 1 :
				$content .= $this->getAllPlugins();
				break;
			case 2 :
				$content .= $this->getAllCType();
				break;
			case 3 :
				$content .= $this->getAllUsedCType();
				break;
			case 4 :
				$content .= $this->getAllUsedPlugins();
				break;
			case 5 :
				$content .= $this->getSummary();
				break;
			case 6 :
				$content .= $this->getAllUsedPlugins(true);
				break;
			case 7 :
				$content .= $this->getAllUsedCType(true);
				break;
		}

		return $content;
	}

	function getAllPlugins()
	{
		$content = '';
		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="10">';
		$content .= $GLOBALS['LANG']->getLL('pluginsmode1');
		$content .= '</td></tr>';
		$content .= '<tr class="c-headLine">';
		$content .= '<td class="cell"></td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('extension') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('plugin') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('eminfo') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('used') . '</td>';
		$content .= '</tr>';
		foreach ($GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'] as $itemKey => $itemValue) {
			if (trim($itemValue[1]) != '') {
				preg_match('/EXT:(.*?)\//', $itemValue[0], $ext);
				preg_match('/^LLL:(EXT:.*?):(.*)/', $itemValue[0], $llfile);
				$LOCAL_LANG = t3lib_div::readLLfile($llfile[1], $GLOBALS['LANG']->lang);
				$content .= '<tr class="db_list_normal">';
				$content .= '<td class="col-icon"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . $itemValue[2] . '"/></td>';
				$content .= '<td class="cell">' . $ext[1] . '</td>';
				$content .= '<td class="cell">' . $GLOBALS['LANG']->getLLL($llfile[2], $LOCAL_LANG) . ' (' . $itemValue[1] . ')</td>';
				$content .= '<td class="cell"><a href="#" onclick="top.goToModule(\'tools_em\', 1, \'CMD[showExt]=' . $ext[1] . '&SET[singleDetails]=info\')">' . $GLOBALS['LANG']->getLL('emlink') . '</a></td>';
				$items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('DISTINCT tt_content.list_type,tt_content.pid,pages.title', 'tt_content,pages', 'tt_content.pid=pages.uid AND tt_content.hidden=0 AND tt_content.deleted=0 AND pages.hidden=0 AND pages.deleted=0 AND tt_content.CType=\'list\' AND tt_content.list_type=\'' . $itemValue[1] . '\'', '', 'tt_content.list_type');
				if (count($items) > 0) {
					$content .= '<td class="cell typo3-message message-ok">' . $GLOBALS['LANG']->getLL('yes') . '</td>';
				} else {
					$content .= '<td class="cell typo3-message message-error">' . $GLOBALS['LANG']->getLL('no') . '</td>';
				}
				$content .= '</tr>';
			}
		}
		$content .= '</table>';
		return $content;
	}

	function getAllCType()
	{
		$content = '';
		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="3">';
		$content .= $GLOBALS['LANG']->getLL('pluginsmode2');
		$content .= '</td></tr>';
		$content .= '<tr class="c-headLine">';
		$content .= '<td class="cell"></td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('ctype') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('used') . '</td>';
		$content .= '</tr>';

		foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $itemKey => $itemValue) {
			if ($itemValue[1] != '--div--') {
				preg_match('/^LLL:(EXT:.*?):(.*)/', $itemValue[0], $llfile);
				$LOCAL_LANG = t3lib_div::readLLfile($llfile[1], $GLOBALS['LANG']->lang);
				$content .= '<tr class="db_list_normal">';
				$content .= '<td class="col-icon">';
				if ($itemValue[2] != '' && is_file(PATH_site . '/typo3/sysext/t3skin/icons/gfx/' . $itemValue[2])) {
					$content .= '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/' . $itemValue[2] . '"/>';
				}
				$content .= '</td>';
				$content .= '<td class="cell">' . $GLOBALS['LANG']->getLLL($llfile[2], $LOCAL_LANG) . ' (' . $itemValue[1] . ')</td>';

				$items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('DISTINCT tt_content.CType,tt_content.pid,pages.title', 'tt_content,pages', 'tt_content.pid=pages.uid AND tt_content.hidden=0 AND tt_content.deleted=0 AND pages.hidden=0 AND pages.deleted=0 AND tt_content.CType=\'' . $itemValue[1] . '\'', '', 'tt_content.CType');

				if (count($items) > 0) {
					$content .= '<td class="cell typo3-message message-ok">' . $GLOBALS['LANG']->getLL('yes') . '</td>';
				} else {
					$content .= '<td class="cell typo3-message message-error">' . $GLOBALS['LANG']->getLL('no') . '</td>';
				}

				$content .= '</tr>';
			}
		}
		$content .= '</table>';
		return $content;
	}

	function getAllUsedCType($displayHidden = false)
	{
		$ctypes = array();
		foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $itemKey => $itemValue) {
			if ($itemValue[1] != '--div--') {
				$ctypes[$itemValue[1]] = $itemValue;
			}
		}

		// addWhere
		$addWhere = '';
		$addhidden = ($displayHidden === true) ? '' : ' AND tt_content.hidden=0 AND pages.hidden=0 ';

		// Plugin list for the select box
		$getFiltersCat = t3lib_div::_GP('filtersCat');
		$pluginsList = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'DISTINCT tt_content.CType',
			'tt_content,pages',
			'tt_content.pid=pages.uid AND tt_content.deleted=0 AND pages.deleted=0 ' . $addhidden . 'AND tt_content.CType<>\'list\'',
			'',
			'tt_content.list_type'
		);
		$this->filtersCat .= '<option value="all">' . $GLOBALS['LANG']->getLL('all') . '</option>';
		foreach ($pluginsList as $pluginsElement) {
			if (($getFiltersCat == $pluginsElement['CType']) && ($getFiltersCat !== null)) {
				$this->filtersCat .= '<option value="' . $pluginsElement['CType'] . '" selected="selected">' . $pluginsElement['CType'] . '</option>';
				$addWhere = ' AND tt_content.CType=\'' . $pluginsElement['CType'] . '\'';
			} else {
				$this->filtersCat .= '<option value="' . $pluginsElement['CType'] . '">' . $pluginsElement['CType'] . '</option>';
			}
		}

		// All items
		$items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'DISTINCT tt_content.CType,tt_content.pid,tt_content.uid,pages.title,pages.hidden as "hiddenpages",tt_content.hidden as "hiddentt_content"',
			'tt_content,pages',
			'tt_content.pid=pages.uid AND tt_content.deleted=0 AND pages.deleted=0 ' . $addhidden . 'AND tt_content.CType<>\'list\'' . $addWhere,
			'',
			'tt_content.CType,tt_content.pid'
		);
		// Page browser
		$pointer = t3lib_div::_GP('pointer');
		$limit = ($pointer !== null) ? $pointer . ',' . $this->nbElementsPerPage : '0,' . $this->nbElementsPerPage;
		$current = ($pointer !== null) ? intval($pointer) : 0;
		$pageBrowser = $this->renderListNavigation(count($items), $this->nbElementsPerPage, $current);
		$itemsBrowser = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'DISTINCT tt_content.CType,tt_content.pid,tt_content.uid,pages.title,pages.hidden as "hiddenpages",tt_content.hidden as "hiddentt_content"',
			'tt_content,pages',
			'tt_content.pid=pages.uid AND tt_content.deleted=0 AND pages.deleted=0 ' . $addhidden . 'AND tt_content.CType<>\'list\'' . $addWhere,
			'',
			'tt_content.CType,tt_content.pid',
			$limit
		);

		$content = '';

		$content .= $pageBrowser;

		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="10">';
		$content .= $GLOBALS['LANG']->getLL('pluginsmode3');
		$content .= '</td></tr>';
		$content .= '<tr class="c-headLine">';
		$content .= '<td class="cell"></td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('ctype') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('pid') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('uid') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('pagetitle') . '</td>';
		$content .= '<td class="cell" align="center">DB mode</td>';
		$content .= '<td class="cell" align="center">Page</td>';
		if (t3lib_extMgm::isLoaded('templavoila')) {
			$content .= '<td class="cell" align="center">Page TV</td>';
			$content .= '<td class="cell" align="center">' . $GLOBALS['LANG']->getLL('tvused') . '</td>';
		}
		$content .= '</tr>';
		foreach ($itemsBrowser as $itemKey => $itemValue) {
			preg_match('/^LLL:(EXT:.*?):(.*)/', $ctypes[$itemValue['CType']][0], $llfile);
			$LOCAL_LANG = t3lib_div::readLLfile($llfile[1], $GLOBALS['LANG']->lang);
			$content .= '<tr class="db_list_normal">';
			$content .= '<td class="col-icon">';
			if (is_file(PATH_site . '/typo3/sysext/t3skin/icons/gfx/' . $ctypes[$itemValue['CType']][2])) {
				$content .= '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/' . $ctypes[$itemValue['CType']][2] . '"/>';
			}
			$content .= '</td>';
			$content .= '<td class="cell">' . $GLOBALS['LANG']->getLLL($llfile[2], $LOCAL_LANG) . ' (' . $itemValue['CType'] . ')</td>';
			$iconPage = ($itemValue['hiddenpages'] == 0) ? '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/i/pages.gif"/>' : '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/i/pages__h.gif"/>';
			$iconContent = ($itemValue['hiddentt_content'] == 0) ? '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/i/tt_content.gif"/>' : '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/i/tt_content__h.gif"/>';
			$content .= '<td class="cell">' . $iconPage . ' ' . $itemValue['pid'] . '</td>';
			$content .= '<td class="cell">' . $iconContent . ' ' . $itemValue['uid'] . '</td>';
			$content .= '<td class="cell">' . $itemValue['title'] . '</td>';
			$content .= '<td class="cell" align="center"><a target="_blank" href="/typo3/db_list.php?id=' . $itemValue['pid'] . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/zoom.gif"/></span></a></td>';
			$content .= '<td class="cell" align="center"><a target="_blank" href="/typo3/sysext/cms/layout/db_layout.php?id=' . $itemValue['pid'] . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/zoom.gif"/></span></a></td>';
			if (t3lib_extMgm::isLoaded('templavoila')) {
				$content .= '<td class="cell" align="center"><a target="_blank" href="/typo3conf/ext/templavoila/mod1/index.php?id=' . $itemValue['pid'] . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/zoom.gif"/></span></a></td>';
				if ($this->isUsedInTV($itemValue['uid'], $itemValue['pid'])) {
					$content .= '<td class="cell typo3-message message-ok">' . $GLOBALS['LANG']->getLL('yes') . '</td>';
				} else {
					$content .= '<td class="cell typo3-message message-error">' . $GLOBALS['LANG']->getLL('no') . '</td>';
				}
			}
			$content .= '</tr>';
		}
		$content .= '</table>';
		return $content;
	}

	function getAllUsedPlugins($displayHidden = false)
	{
		$plugins = array();
		foreach ($GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'] as $itemKey => $itemValue) {
			if (trim($itemValue[1]) != '') {
				$plugins[$itemValue[1]] = $itemValue;
			}
		}
		// addWhere
		$addWhere = '';
		$addhidden = ($displayHidden === true) ? '' : ' AND tt_content.hidden=0 AND pages.hidden=0 ';

		// Plugin list for the select box
		$getFiltersCat = t3lib_div::_GP('filtersCat');
		$pluginsList = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'DISTINCT tt_content.list_type',
			'tt_content,pages',
			'tt_content.pid=pages.uid AND tt_content.deleted=0 AND pages.deleted=0 ' . $addhidden . 'AND tt_content.CType=\'list\'',
			'',
			'tt_content.list_type'
		);
		$this->filtersCat .= '<option value="all">' . $GLOBALS['LANG']->getLL('all') . '</option>';
		foreach ($pluginsList as $pluginsElement) {
			if (($getFiltersCat == $pluginsElement['list_type']) && ($getFiltersCat !== null)) {
				$this->filtersCat .= '<option value="' . $pluginsElement['list_type'] . '" selected="selected">' . $pluginsElement['list_type'] . '</option>';
				$addWhere = ' AND tt_content.list_type=\'' . $pluginsElement['list_type'] . '\'';
			} else {
				$this->filtersCat .= '<option value="' . $pluginsElement['list_type'] . '">' . $pluginsElement['list_type'] . '</option>';
			}
		}
		// All items
		$items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'DISTINCT tt_content.list_type,tt_content.pid,tt_content.uid,pages.title,pages.hidden as "hiddenpages",tt_content.hidden as "hiddentt_content"',
			'tt_content,pages',
			'tt_content.pid=pages.uid AND tt_content.deleted=0 AND pages.deleted=0 ' . $addhidden . 'AND tt_content.CType=\'list\'' . $addWhere,
			'',
			'tt_content.list_type,tt_content.pid'
		);
		// Page browser
		$pointer = t3lib_div::_GP('pointer');
		$limit = ($pointer !== null) ? $pointer . ',' . $this->nbElementsPerPage : '0,' . $this->nbElementsPerPage;
		$current = ($pointer !== null) ? intval($pointer) : 0;
		$pageBrowser = $this->renderListNavigation(count($items), $this->nbElementsPerPage, $current);
		$itemsBrowser = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'DISTINCT tt_content.list_type,tt_content.pid,tt_content.uid,pages.title,pages.hidden as "hiddenpages",tt_content.hidden as "hiddentt_content"',
			'tt_content,pages',
			'tt_content.pid=pages.uid AND tt_content.deleted=0 AND pages.deleted=0 ' . $addhidden . 'AND tt_content.CType=\'list\'' . $addWhere,
			'',
			'tt_content.list_type,tt_content.pid',
			$limit
		);

		$content = '';

		$content .= $pageBrowser;

		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="10">';
		$content .= $GLOBALS['LANG']->getLL('pluginsmode4');
		$content .= '</td></tr>';
		$content .= '<tr class="c-headLine">';
		$content .= '<td class="cell"></td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('extension') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('plugin') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('pid') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('uid') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('pagetitle') . '</td>';
		$content .= '<td class="cell" align="center">DB mode</td>';
		$content .= '<td class="cell" align="center">Page</td>';
		if (t3lib_extMgm::isLoaded('templavoila')) {
			$content .= '<td class="cell" align="center">Page TV</td>';
			$content .= '<td class="cell" align="center">' . $GLOBALS['LANG']->getLL('tvused') . '</td>';
		}
		$content .= '</tr>';
		foreach ($itemsBrowser as $itemKey => $itemValue) {
			preg_match('/EXT:(.*?)\//', $plugins[$itemValue['list_type']][0], $ext);
			preg_match('/^LLL:(EXT:.*?):(.*)/', $plugins[$itemValue['list_type']][0], $llfile);
			$LOCAL_LANG = t3lib_div::readLLfile($llfile[1], $GLOBALS['LANG']->lang);
			$content .= '<tr class="db_list_normal">';
			$content .= '<td class="col-icon"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . $plugins[$itemValue['list_type']][2] . '"/></td>';
			$content .= '<td class="cell">' . $ext[1] . '</td>';
			$content .= '<td class="cell">' . $GLOBALS['LANG']->getLLL($llfile[2], $LOCAL_LANG) . ' (' . $itemValue['list_type'] . ')</td>';
			$iconPage = ($itemValue['hiddenpages'] == 0) ? '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/i/pages.gif"/>' : '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/i/pages__h.gif"/>';
			$iconContent = ($itemValue['hiddentt_content'] == 0) ? '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/i/tt_content.gif"/>' : '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/i/tt_content__h.gif"/>';
			$content .= '<td class="cell">' . $iconPage . ' ' . $itemValue['pid'] . '</td>';
			$content .= '<td class="cell">' . $iconContent . ' ' . $itemValue['uid'] . '</td>';
			$content .= '<td class="cell">' . $itemValue['title'] . '</td>';
			$content .= '<td class="cell" align="center"><a target="_blank" href="/typo3/db_list.php?id=' . $itemValue['pid'] . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/zoom.gif"/></span></a></td>';
			$content .= '<td class="cell" align="center"><a target="_blank" href="/typo3/sysext/cms/layout/db_layout.php?id=' . $itemValue['pid'] . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/zoom.gif"/></span></a></td>';
			if (t3lib_extMgm::isLoaded('templavoila')) {
				$content .= '<td class="cell" align="center"><a target="_blank" href="/typo3conf/ext/templavoila/mod1/index.php?id=' . $itemValue['pid'] . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/zoom.gif"/></span></a></td>';
				if ($this->isUsedInTV($itemValue['uid'], $itemValue['pid'])) {
					$content .= '<td class="cell typo3-message message-ok">' . $GLOBALS['LANG']->getLL('yes') . '</td>';
				} else {
					$content .= '<td class="cell typo3-message message-error">' . $GLOBALS['LANG']->getLL('no') . '</td>';
				}
			}
			$content .= '</tr>';
		}
		$content .= '</table>';
		return $content;
	}

	function getSummary()
	{
		$plugins = array();
		foreach ($GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'] as $itemKey => $itemValue) {
			if (trim($itemValue[1]) != '') {
				$plugins[$itemValue[1]] = $itemValue;
			}
		}

		$ctypes = array();
		foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $itemKey => $itemValue) {
			if ($itemValue[1] != '--div--') {
				$ctypes[$itemValue[1]] = $itemValue;
			}
		}

		$itemsCount = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('COUNT( tt_content.uid ) as "nb"', 'tt_content,pages', 'tt_content.pid=pages.uid AND tt_content.hidden=0 AND tt_content.deleted=0 AND pages.hidden=0 AND pages.deleted=0');
		$items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('tt_content.CType,tt_content.list_type,count(*) as "nb"', 'tt_content,pages', 'tt_content.pid=pages.uid AND tt_content.hidden=0 AND tt_content.deleted=0 AND pages.hidden=0 AND pages.deleted=0', 'tt_content.CType,tt_content.list_type', 'nb DESC');

		$content = '';
		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="4">';
		$content .= $GLOBALS['LANG']->getLL('pluginsmode5');
		$content .= '</td></tr>';
		$content .= '<tr class="c-headLine">';
		$content .= '<td class="cell"></td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('content') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('references') . '</td>';
		$content .= '<td class="cell">%</td>';
		$content .= '</tr>';
		foreach ($items as $itemKey => $itemValue) {
			$content .= '<tr class="db_list_normal">';

			if ($itemValue['CType'] == 'list') {
				preg_match('/EXT:(.*?)\//', $plugins[$itemValue['list_type']][0], $ext);
				preg_match('/^LLL:(EXT:.*?):(.*)/', $plugins[$itemValue['list_type']][0], $llfile);
				$LOCAL_LANG = t3lib_div::readLLfile($llfile[1], $GLOBALS['LANG']->lang);
				$content .= '<td class="col-icon"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . $plugins[$itemValue['list_type']][2] . '"/></td>';
				$content .= '<td class="cell">' . $GLOBALS['LANG']->getLLL($llfile[2], $LOCAL_LANG) . ' (' . $itemValue['list_type'] . ')</td>';
			} else {
				preg_match('/^LLL:(EXT:.*?):(.*)/', $ctypes[$itemValue['CType']][0], $llfile);
				$LOCAL_LANG = t3lib_div::readLLfile($llfile[1], $GLOBALS['LANG']->lang);
				$content .= '<td class="col-icon">';
				if (is_file(PATH_site . '/typo3/sysext/t3skin/icons/gfx/' . $ctypes[$itemValue['CType']][2])) {
					$content .= '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/' . $ctypes[$itemValue['CType']][2] . '"/>';
				}
				$content .= '</td>';
				$content .= '<td class="cell">' . $GLOBALS['LANG']->getLLL($llfile[2], $LOCAL_LANG) . ' (' . $itemValue['CType'] . ')</td>';
			}

			$content .= '<td class="cell">' . $itemValue['nb'] . '</td>';
			$content .= '<td class="cell">' . round((($itemValue['nb'] * 100) / $itemsCount[0]['nb']), 2) . ' %</td>';
			$content .= '</tr>';
		}
		$content .= '</table>';
		return $content;
	}

	function isUsedInTV($uid, $pid)
	{
		$apiObj = t3lib_div::makeInstance('tx_templavoila_api', 'pages');
		$rootElementRecord = t3lib_BEfunc::getRecordWSOL('pages', $pid, '*');
		$contentTreeData = $apiObj->getContentTree('pages', $rootElementRecord);
		$usedUids = array_keys($contentTreeData['contentElementUsage']);
		if (t3lib_div::inList(implode(',', $usedUids), $uid)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Creates a page browser for tables with many records
	 */

	function renderListNavigation($totalItems, $iLimit, $firstElementNumber, $renderPart = 'top')
	{
		$totalPages = ceil($totalItems / $iLimit);

		$content = '';
		$returnContent = '';
		// Show page selector if not all records fit into one page
		if ($totalPages >= 1) {
			$first = $previous = $next = $last = $reload = '';
			$listURLOrig = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'mod.php?M=tools_txreportsM1&display=' . $this->display;
			$listURL = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'mod.php?M=tools_txreportsM1&display=' . $this->display;
			$listURL .= '&nbPerPage=' . $this->nbElementsPerPage;
			$getFiltersCat = t3lib_div::_GP('filtersCat');
			if ($getFiltersCat !== null) {
				$listURL .= '&filtersCat=' . $getFiltersCat;
			}
			$currentPage = floor(($firstElementNumber + 1) / $iLimit) + 1;
			// First
			if ($currentPage > 1) {
				$labelFirst = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:first');
				$first = '<a href="' . $listURL . '&pointer=0"><img width="16" height="16" title="' . $labelFirst . '" alt="' . $labelFirst . '" src="sysext/t3skin/icons/gfx/control_first.gif"></a>';
			} else {
				$first = '<img width="16" height="16" title="" alt="" src="sysext/t3skin/icons/gfx/control_first_disabled.gif">';
			}
			// Previous
			if (($currentPage - 1) > 0) {
				$labelPrevious = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:previous');
				$previous = '<a href="' . $listURL . '&pointer=' . (($currentPage - 2) * $iLimit) . '"><img width="16" height="16" title="' . $labelPrevious . '" alt="' . $labelPrevious . '" src="sysext/t3skin/icons/gfx/control_previous.gif"></a>';
			} else {
				$previous = '<img width="16" height="16" title="" alt="" src="sysext/t3skin/icons/gfx/control_previous_disabled.gif">';
			}
			// Next
			if (($currentPage + 1) <= $totalPages) {
				$labelNext = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:next');
				$next = '<a href="' . $listURL . '&pointer=' . (($currentPage) * $iLimit) . '"><img width="16" height="16" title="' . $labelNext . '" alt="' . $labelNext . '" src="sysext/t3skin/icons/gfx/control_next.gif"></a>';
			} else {
				$next = '<img width="16" height="16" title="" alt="" src="sysext/t3skin/icons/gfx/control_next_disabled.gif">';
			}
			// Last
			if ($currentPage != $totalPages) {
				$labelLast = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:last');
				$last = '<a href="' . $listURL . '&pointer=' . (($totalPages - 1) * $iLimit) . '"><img width="16" height="16" title="' . $labelLast . '" alt="' . $labelLast . '" src="sysext/t3skin/icons/gfx/control_last.gif"></a>';
			} else {
				$last = '<img width="16" height="16" title="" alt="" src="sysext/t3skin/icons/gfx/control_last_disabled.gif">';
			}

			$pageNumberInput = '<span>' . $currentPage . '</span>';
			$pageIndicator = '<span class="pageIndicator">'
			                 . sprintf($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:pageIndicator'), $pageNumberInput, $totalPages)
			                 . '</span>';

			if ($totalItems > ($firstElementNumber + $iLimit)) {
				$lastElementNumber = $firstElementNumber + $iLimit;
			} else {
				$lastElementNumber = $totalItems;
			}

			$rangeIndicator = '<span class="pageIndicator">'
			                  . sprintf($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:rangeIndicator'), $firstElementNumber + 1, $lastElementNumber)
			                  . '</span>';
			// nb per page, filter and reload
			$reload = '<input type="text" name="nbPerPage" id="nbPerPage" size="5" value="' . $this->nbElementsPerPage . '"/> / page ';

			if ($getFiltersCat !== null) {
				$reload .= '<a href="#"  onClick="jumpToUrl(\'' . $listURLOrig . '&nbPerPage=\'+document.getElementById(\'nbPerPage\').value+\'&filtersCat=' . $getFiltersCat . '\');">';
			} else {
				$reload .= '<a href="#"  onClick="jumpToUrl(\'' . $listURLOrig . '&nbPerPage=\'+document.getElementById(\'nbPerPage\').value);">';
			}
			$reload .= '<img width="16" height="16" title="" alt="" src="sysext/t3skin/icons/gfx/refresh_n.gif"></a>';

			if ($this->filtersCat != '') {
				$reload .= '<span class="bar">&nbsp;</span>';
				$reload .= $GLOBALS['LANG']->getLL('filterByCat') . '&nbsp;<select name="filtersCat" id="filtersCat">' . $this->filtersCat . '</select>';
				$reload .= '<a href="#"  onClick="jumpToUrl(\'' . $listURLOrig . '&nbPerPage=\'+document.getElementById(\'nbPerPage\').value+\'&filtersCat=\'+document.getElementById(\'filtersCat\').value);">';
				$reload .= '&nbsp;<img width="16" height="16" title="" alt="" src="sysext/t3skin/icons/gfx/refresh_n.gif"></a>';
			}

			$content .= '<div id="typo3-dblist-pagination">'
			            . $first . $previous
			            . '<span class="bar">&nbsp;</span>'
			            . $rangeIndicator . '<span class="bar">&nbsp;</span>'
			            . $pageIndicator . '<span class="bar">&nbsp;</span>'
			            . $next . $last . '<span class="bar">&nbsp;</span>'
			            . $reload
			            . '</div>';

			$returnContent = $content;
		} // end of if pages > 1
		return $returnContent;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/additional_reports/reports_plugins/class.tx_additionalreports_plugins.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/additional_reports/reports_plugins/class.tx_additionalreports_plugins.php']);
}

?>