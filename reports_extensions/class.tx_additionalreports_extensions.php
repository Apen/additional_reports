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

class tx_additionalreports_extensions implements tx_reports_Report
{

	/**
	 * Back-reference to the calling reports module
	 *
	 * @var    tx_reports_Module    $reportObject
	 */

	protected $reportObject;

	/**
	 * Constructor for class tx_additionalreports_extensions
	 *
	 * @param    tx_reports_Module    Back-reference to the calling reports module
	 */

	public function __construct(tx_reports_Module $reportObject)
	{
		$this->reportObject = $reportObject;
		$GLOBALS['LANG']->includeLLFile('EXT:additional_reports/locallang.xml');
	}

	/**
	 * This method renders the report
	 *
	 * @return    string    The status report as HTML
	 */

	public function getReport()
	{
		$content = '';
		$this->reportObject->doc->getPageRenderer()->addCssFile(t3lib_extMgm::extRelPath('additional_reports') . 'tx_additionalreports.css');
		// $content .= '<p class="help">' . $GLOBALS['LANG']->getLL('eid_description') . '</p>';
		$content .= $this->displayEID();
		return $content;
	}

	protected function displayEID()
	{
		global $BACK_PATH;

		$content = '';

		//t3lib_div::debug(t3lib_div::int_from_ver(TYPO3_version));
		if (t3lib_div::int_from_ver(TYPO3_version) <= 4005000) {
			require_once($BACK_PATH . 'mod/tools/em/class.em_index.php');
			$em = t3lib_div::makeInstance('SC_mod_tools_em_index');
			$em->init();
			$path = PATH_typo3conf . 'ext/';
			$items = array();
			$cat = $em->defaultCategories;
			$em->getInstExtList($path, $items, $cat, 'L');
		} else {
			require_once($BACK_PATH . 'sysext/em/classes/extensions/class.tx_em_extensions_list.php');
			require_once($BACK_PATH . 'sysext/em/classes/extensions/class.tx_em_extensions_details.php');
			require_once($BACK_PATH . 'sysext/em/classes/tools/class.tx_em_tools_xmlhandler.php');
			$em = t3lib_div::makeInstance('tx_em_Extensions_List');
			$emDetails = t3lib_div::makeInstance('tx_em_Extensions_Details');
			$emTools = t3lib_div::makeInstance('tx_em_Tools_XmlHandler');
			$path = PATH_typo3conf . 'ext/';
			$items = array();
			$cat = tx_em_Tools::getDefaultCategory();
			$em->getInstExtList($path, $items, $cat, 'L');
		}

		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="7">';
		$content .= $GLOBALS['LANG']->getLL('extensions_description');
		$content .= '</td></tr>';
		$content .= '<tr class="c-headLine">';
		$content .= '<td class="cell"></td>';
		$content .= '<td class="cell" width="250">' . $GLOBALS['LANG']->getLL('extension') . '</td>';
		$content .= '<td class="cell" width="40" style="text-align:center;">' . $GLOBALS['LANG']->getLL('status_version') . '</td>';
		$content .= '<td class="cell" width="40" style="text-align:center;">' . $GLOBALS['LANG']->getLL('status_lastversion') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('extensions_tables') . '</td>';
		$content .= '<td class="cell" width="80" style="text-align:center;">' . $GLOBALS['LANG']->getLL('extensions_tablesintegrity') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('extensions_files') . '</td>';
		$content .= '</tr>';

		$extensionsToUpdate = 0;
		$extensionsDEV = 0;
		$extensionsModified = 0;

		foreach ($items as $itemKey => $itemValue) {
			if (t3lib_extMgm::isLoaded($itemKey)) {
				$extKey = $itemKey;
				$extInfo = $itemValue;

				if (t3lib_div::int_from_ver(TYPO3_version) <= 4005000) {
					$currentMd5Array = $em->serverExtensionMD5Array($extKey, $extInfo);
					$affectedFiles = $em->findMD5ArrayDiff($currentMd5Array, unserialize($extInfo['EM_CONF']['_md5_values_when_last_written']));
					$lastVersion = $this->checkMAJ($em, $extKey);
					// sql
					$instObj = new t3lib_install;
					$FDfile = array();
					if (is_array($extInfo['files']) && in_array('ext_tables.sql', $extInfo['files'])) {
						$fileContent = t3lib_div::getUrl($em->getExtPath($extKey, $extInfo['type']) . 'ext_tables.sql');
						$FDfile = $instObj->getFieldDefinitions_fileContent($fileContent);
						$FDdb = $instObj->getFieldDefinitions_database(TYPO3_db);
						$diff = $instObj->getDatabaseExtra($FDfile, $FDdb);
						$update_statements = $instObj->getUpdateSuggestions($diff);
					}
				} else {
					$currentMd5Array = $emDetails->serverExtensionMD5Array($extKey, $extInfo);
					$affectedFiles = tx_em_Tools::findMD5ArrayDiff($currentMd5Array, unserialize($extInfo['EM_CONF']['_md5_values_when_last_written']));
					$lastVersion = $this->checkMAJ($emTools, $extKey);
					// sql
					$instObj = new t3lib_install;
					$FDfile = array();
					if (is_array($extInfo['files']) && in_array('ext_tables.sql', $extInfo['files'])) {
						$fileContent = t3lib_div::getUrl(tx_em_Tools::getExtPath($extKey, $extInfo['type']) . 'ext_tables.sql');
						$FDfile = $instObj->getFieldDefinitions_fileContent($fileContent);
						$FDdb = $instObj->getFieldDefinitions_database(TYPO3_db);
						$diff = $instObj->getDatabaseExtra($FDfile, $FDdb);
						$update_statements = $instObj->getUpdateSuggestions($diff);
					}
				}

				$class = "cell";

				if (!$lastVersion) {
					$extensionsDEV++;
					$lastVersion = '/';
					$class = "infos";
				}

				$content .= '<tr class="db_list_normal">';
				$content .= '<td class="col-icon ' . $class . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . t3lib_extMgm::extRelPath($extKey) . 'ext_icon.gif"/></td>';
				$content .= '<td class="' . $class . '">' . $extKey . '</td>';
				$content .= '<td class="' . $class . '" align="center">' . $itemValue['EM_CONF']['version'] . '</td>';

				if (version_compare($itemValue['EM_CONF']['version'], $lastVersion, '<')) {
					$extensionsToUpdate++;
					$content .= '<td class="' . $class . '" align="center"><span style="color:green;font-weight:bold;">' . $lastVersion . '</span></td>';
				} else {
					$content .= '<td class="' . $class . '" align="center">' . $lastVersion . '</td>';
				}

				$dump_tf = '';
				if (count($FDfile) > 0) {
					$id = 'sql' . $extKey;
					$dump_tf = count($FDfile) . ' ' . $GLOBALS['LANG']->getLL('extensions_tablesmodified') . '</span>  <input type="button" onclick="$(\'' . $id . '\').toggle();" value="+"/><div style="display:none;" id="' . $id . '"><br/>' . t3lib_div::view_array($FDfile) . '</div>';
				}
				$content .= '<td class="' . $class . '">' . $dump_tf . '</td>';

				if (count($update_statements) > 0) {
					$content .= '<td class="' . $class . '" align="center"><span style="color:red;font-weight:bold;">' . $GLOBALS['LANG']->getLL('yes') . '</span></td>';
				} else {
					$content .= '<td class="' . $class . '" align="center">' . $GLOBALS['LANG']->getLL('no') . '</td>';
				}

				if (count($affectedFiles) > 0) {
					$extensionsModified++;
					$id = 'files' . $extKey;
					$content .= '<td class="' . $class . '"><span style="color:red;font-weight:bold;">' . count($affectedFiles) . ' ' . $GLOBALS['LANG']->getLL('extensions_filesmodified') . '</span>  <input type="button" onclick="$(\'' . $id . '\').toggle();" value="+"/><div style="display:none;" id="' . $id . '"><br/>' . t3lib_div::view_array($affectedFiles) . '</div></td>';
				} else {
					$content .= '<td class="' . $class . '"></td>';
				}

				$content .= '</tr>';
			}

		}
		$content .= '</table>';

		$addContent = '';
		$addContent .= count($items) . ' ' . $GLOBALS['LANG']->getLL('extensions_extensions');
		$addContent .= '<br/>';
		$addContent .= count($items) - $extensionsDEV . ' ' . $GLOBALS['LANG']->getLL('extensions_ter');
		$addContent .= '  /  ';
		$addContent .= $extensionsDEV . ' ' . $GLOBALS['LANG']->getLL('extensions_dev');
		$addContent .= '<br/>';
		$addContent .= $extensionsToUpdate . ' ' . $GLOBALS['LANG']->getLL('extensions_toupdate');
		$addContent .= '  /  ';
		$addContent .= $extensionsModified . ' ' . $GLOBALS['LANG']->getLL('extensions_extensionsmodified');
		$addContentItem = $this->writeInformation($GLOBALS['LANG']->getLL('pluginsmode5'), $addContent);

		$content = $addContentItem . $content;
		return $content;
	}

	function checkMAJ($em, $name)
	{
		if (t3lib_div::int_from_ver(TYPO3_version) <= 4005000) {
			$em->xmlhandler->searchExtensionsXMLExact($name, '', '', TRUE, TRUE);
			$v = $em->xmlhandler->extensionsXML[$name]['versions'];
		} else {
			$em->searchExtensionsXMLExact($name, '', '', TRUE, TRUE);
			$v = $em->extensionsXML[$name]['versions'];
		}
		$versions = array_keys($v);
		natsort($versions);
		$lastversion = end($versions);
		return $lastversion;
	}

	protected function writeInformation($label, $value)
	{
		return '
			<div class="typo3-message message-information">
				<div class="header-container">
					<div class="message-header message-left">' . $label . '</div>
					<div class="message-header message-right">' . $value . '</div>
				</div>
				<div class="message-body"></div>
			</div>
		';
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/additional_reports/reports_eid/class.tx_additionalreports_extensions.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/additional_reports/reports_eid/class.tx_additionalreports_extensions.php']);
}

?>