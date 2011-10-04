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

/**
 * This class provides methods to generate the reports
 *
 * @author		CERDAN Yohann <cerdanyohann@yahoo.fr>
 * @package		TYPO3
 */

class tx_additionalreports_main
{
	public function init() {
		$GLOBALS['LANG']->includeLLFile('EXT:additional_reports/locallang.xml');
	}

	public function displayAjax() {
		$content = '';
		$items = $GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX'];
		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="7">';
		$content .= $GLOBALS['LANG']->getLL('ajax_description');
		$content .= '</td></tr>';
		$content .= '<tr class="c-headLine">';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('name') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('path') . '</td>';
		$content .= '</tr>';
		if (!empty($items)) {
			foreach ($items as $itemKey => $itemValue) {
				$content .= '<tr class="db_list_normal">';
				$content .= '<td class="cell">typo3/ajax.php?ajaxID=<strong>' . $itemKey . '</strong></td>';
				$content .= '<td class="cell">' . $itemValue . '</td>';
				$content .= '</tr>';
			}
		}
		$content .= '</table>';
		return $content;
	}

	public function displayCliKeys() {
		$content = '';
		$items = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys'];
		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="7">';
		$content .= $GLOBALS['LANG']->getLL('clikeys_description');
		$content .= '</td></tr>';
		$content .= '<tr class="c-headLine">';
		$content .= '<td class="cell">&nbsp;</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('extension') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('name') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('path') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('user') . '</td>';
		$content .= '</tr>';

		foreach ($items as $itemKey => $itemValue) {
			preg_match('/EXT:(.*?)\//', $itemValue[0], $ext);
			$content .= '<tr class="db_list_normal">';
			$content .= '<td class="col-icon"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . t3lib_extMgm::extRelPath($ext[1]) . 'ext_icon.gif"/></td>';
			$content .= '<td class="cell">' . $ext[1] . '</td>';
			$content .= '<td class="cell">' . $itemKey . '</td>';
			$content .= '<td class="cell">' . $itemValue[0] . '</td>';
			$content .= '<td class="cell">' . $itemValue[1] . '</td>';
			$content .= '</tr>';
		}
		$content .= '</table>';
		return $content;
	}

	public function displayEID() {
		$content = '';
		$items = $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include'];
		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="7">';
		$content .= $GLOBALS['LANG']->getLL('eid_description');
		$content .= '</td></tr>';
		$content .= '<tr class="c-headLine">';
		$content .= '<td class="cell">&nbsp;</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('extension') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('name') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('path') . '</td>';
		$content .= '</tr>';
		foreach ($items as $itemKey => $itemValue) {
			preg_match('/EXT:(.*?)\//', $itemValue, $ext);
			$content .= '<tr class="db_list_normal">';
			$content .= '<td class="col-icon"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . t3lib_extMgm::extRelPath($ext[1]) . 'ext_icon.gif"/></td>';
			$content .= '<td class="cell">' . $ext[1] . '</td>';
			$content .= '<td class="cell">' . $itemKey . '</td>';
			$content .= '<td class="cell">' . $itemValue . '</td>';
			$content .= '</tr>';
		}
		$content .= '</table>';
		return $content;
	}

	public function displayExtDirect() {
		$content = '';
		$items = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ExtDirect'];
		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="7">';
		$content .= $GLOBALS['LANG']->getLL('extdirect_description');
		$content .= '</td></tr>';
		$content .= '<tr class="c-headLine">';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('name') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('path') . '</td>';
		$content .= '</tr>';
		foreach ($items as $itemKey => $itemValue) {
			$content .= '<tr class="db_list_normal">';
			$content .= '<td class="cell">' . $itemKey . '</td>';
			$content .= '<td class="cell">' . $itemValue . '</td>';
			$content .= '</tr>';
		}
		$content .= '</table>';
		return $content;
	}

	public function splitVersionRange($ver) {
		$versionRange = array();
		if (strstr($ver, '-')) {
			$versionRange = explode('-', $ver, 2);
		} else {
			$versionRange[0] = $ver;
			$versionRange[1] = '';
		}
		if (!$versionRange[0]) {
			$versionRange[0] = '0.0.0';
		}
		if (!$versionRange[1]) {
			$versionRange[1] = '0.0.0';
		}
		return $versionRange;
	}

	public function getExtList($path, &$items) {
		global $BACK_PATH;
		if (t3lib_div::int_from_ver(TYPO3_version) <= 4005000) {
			require_once($BACK_PATH . 'mod/tools/em/class.em_index.php');
			$em = t3lib_div::makeInstance('SC_mod_tools_em_index');
			$em->init();
			$cat = $em->defaultCategories;
			$em->getInstExtList($path, $items, $cat, 'L');
		} else {
			require_once($BACK_PATH . 'sysext/em/classes/extensions/class.tx_em_extensions_list.php');
			require_once($BACK_PATH . 'sysext/em/classes/extensions/class.tx_em_extensions_details.php');
			require_once($BACK_PATH . 'sysext/em/classes/tools/class.tx_em_tools_xmlhandler.php');
			$em = t3lib_div::makeInstance('tx_em_Extensions_List');
			$cat = tx_em_Tools::getDefaultCategory();
			$em->getInstExtList($path, $items, $cat, 'L');
		}
		return $em;
	}

	public function getExtAffectedFiles($em, $extKey, $extInfo, &$affectedFiles, &$lastVersion) {
		if (t3lib_div::int_from_ver(TYPO3_version) <= 4005000) {
			$currentMd5Array = $em->serverExtensionMD5Array($extKey, $extInfo);
			$affectedFiles = $em->findMD5ArrayDiff($currentMd5Array, unserialize($extInfo['EM_CONF']['_md5_values_when_last_written']));
			$lastVersion = self::checkMAJ($em, $extKey);
		} else {
			$emDetails = t3lib_div::makeInstance('tx_em_Extensions_Details');
			$emTools = t3lib_div::makeInstance('tx_em_Tools_XmlHandler');
			$currentMd5Array = $emDetails->serverExtensionMD5Array($extKey, $extInfo);
			$affectedFiles = tx_em_Tools::findMD5ArrayDiff($currentMd5Array, unserialize($extInfo['EM_CONF']['_md5_values_when_last_written']));
			$lastVersion = self::checkMAJ($emTools, $extKey);
		}
	}

	public function getExtSQLUpdateStatements($em, $extKey, $extInfo, &$FDfile, &$update_statements) {
		if (t3lib_div::int_from_ver(TYPO3_version) <= 4005000) {
			$instObj = new t3lib_install;
			if (is_array($extInfo['files']) && in_array('ext_tables.sql', $extInfo['files'])) {
				$fileContent = t3lib_div::getUrl($em->getExtPath($extKey, $extInfo['type']) . 'ext_tables.sql');
				if (method_exists('t3lib_install', 'getFieldDefinitions_fileContent') === TRUE) { // for compatibility
					$FDfile = $instObj->getFieldDefinitions_fileContent($fileContent);
				} else {
					$FDfile = $instObj->getFieldDefinitions_sqlContent($fileContent);
				}
				$FDdb = $instObj->getFieldDefinitions_database(TYPO3_db);
				$diff = $instObj->getDatabaseExtra($FDfile, $FDdb);
				$update_statements = $instObj->getUpdateSuggestions($diff);
			}
		} else {
			$instObj = new t3lib_install;
			if (is_array($extInfo['files']) && in_array('ext_tables.sql', $extInfo['files'])) {
				$fileContent = t3lib_div::getUrl(tx_em_Tools::getExtPath($extKey, $extInfo['type']) . 'ext_tables.sql');
				$FDfile = $instObj->getFieldDefinitions_fileContent($fileContent);
				$FDdb = $instObj->getFieldDefinitions_database(TYPO3_db);
				$diff = $instObj->getDatabaseExtra($FDfile, $FDdb);
				$update_statements = $instObj->getUpdateSuggestions($diff);
			}
		}
	}

	public function versionCompare($depV) {
		$t3version = TYPO3_version;
		$depK = 'typo3';
		if (stripos($t3version, '-dev') || stripos($t3version, '-alpha') || stripos($t3version, '-beta') || stripos($t3version, '-RC')) {
			// find the last occurence of "-" and replace that part with a ".0"
			$t3version = substr($t3version, 0, strrpos($t3version, '-')) . '.0';
		}

		$status = 0;

		if (isset($depV)) {
			$versionRange = self::splitVersionRange($depV);
			if ($versionRange[0] != '0.0.0' && version_compare($t3version, $versionRange[0], '<')) {
				$msg = sprintf($GLOBALS['LANG']->getLL('checkDependencies_typo3_too_low'), $t3version, $versionRange[0]);
			} elseif ($versionRange[1] != '0.0.0' && version_compare($t3version, $versionRange[1], '>')) {
				$msg = sprintf($GLOBALS['LANG']->getLL('checkDependencies_typo3_too_high'), $t3version, $versionRange[1]);
			} elseif ($versionRange[1] == '0.0.0') {
				$status = 2;
				$msg = $GLOBALS['LANG']->getLL('nottested') . ' (' . $depV . ')';
			} else {
				$status = 1;
				$msg = 'OK';
			}
		} else {
			$status = 3;
			$msg = $GLOBALS['LANG']->getLL('unknown');
		}

		switch ($status) {
			case 0:
				$msg = '<span style="color:red;font-weight:bold;" title="' . $msg . '">KO</span>';
				break;
			case 1:
				$msg = '<span style="color:green;font-weight:bold;" title="' . $msg . '">OK</span>';
				break;
			case 2:
				$msg = '<span style="color:orange;font-weight:bold;" title="' . $msg . '">' . $GLOBALS['LANG']->getLL('nottested') . '</span>';
				break;
			case 3:
				$msg = '<span style="color:orange;font-weight:bold;" title="' . $msg . '">' . $GLOBALS['LANG']->getLL('unknown') . '</span>';
				break;
		}

		return $msg;
	}

	public function displayExtensions() {

		$content = '';
		$path = PATH_typo3conf . 'ext/';
		$items = array();
		$itemsDev = array();

		$em = self::getExtList($path, $items);

		$content .= '<script type="text/javascript">Shadowbox.init({displayNav:true,displayCounter:false,overlayOpacity:0.8});</script>';

		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="15">';
		$content .= $GLOBALS['LANG']->getLL('extensions_ter') . ' (TYPO3 ' . TYPO3_version . ')';
		$content .= '</td></tr>';
		$content .= '<tr class="c-headLine">';
		$content .= '<td class="cell">&nbsp;</td>';
		$content .= '<td class="cell" width="150" colspan="2">' . $GLOBALS['LANG']->getLL('extension') . '</td>';
		$content .= '<td class="cell" width="40" style="text-align:center;">' . $GLOBALS['LANG']->getLL('status_version') . '</td>';
		$content .= '<td class="cell" width="40" style="text-align:center;">' . $GLOBALS['LANG']->getLL('versioncheck') . '</td>';
		$content .= '<td class="cell" width="40" style="text-align:center;">' . $GLOBALS['LANG']->getLL('status_lastversion') . '</td>';
		$content .= '<td class="cell" width="40" style="text-align:center;">' . $GLOBALS['LANG']->getLL('downloads') . '</td>';
		$content .= '<td class="cell" colspan="2">' . $GLOBALS['LANG']->getLL('extensions_tables') . '</td>';
		$content .= '<td class="cell" width="80" style="text-align:center;">' . $GLOBALS['LANG']->getLL('extensions_tablesintegrity') . '</td>';
		$content .= '<td class="cell" colspan="2">' . $GLOBALS['LANG']->getLL('extensions_files') . '</td>';
		$content .= '</tr>';

		$extensionsToUpdate = 0;
		$extensionsDEV = 0;
		$extensionsModified = 0;

		foreach ($items as $itemKey => $itemValue) {
			if (t3lib_extMgm::isLoaded($itemKey)) {
				$extKey = $itemKey;
				$extInfo = $itemValue;
				$FDfile = array();
				$update_statements = array();
				$affectedFiles = array();
				$lastVersion = '';

				self::getExtAffectedFiles($em, $extKey, $extInfo, $affectedFiles, $lastVersion);
				self::getExtSQLUpdateStatements($em, $extKey, $extInfo, $FDfile, $update_statements);

				$class = "cell";

				if (!$lastVersion) {
					$itemsDev[$itemKey] = $itemValue;
					$extensionsDEV++;
					$lastVersion = '/';
					$class = "specs";
				} else {

					$content .= '<tr class="db_list_normal">';
					$content .= '<td class="col-icon ' . $class . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . t3lib_extMgm::extRelPath($extKey) . 'ext_icon.gif"/></td>';
					$content .= '<td class="' . $class . '">' . $extKey . '</td>';
					$content .= '<td width="30" class="' . $class . '" align="center"><a href="#" onclick="top.goToModule(\'tools_em\', 1, \'CMD[showExt]=' . $extKey . '&SET[singleDetails]=info\')"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/zoom.gif"/></a></td>';
					$content .= '<td class="' . $class . '" align="center">' . $itemValue['EM_CONF']['version'] . '</td>';
					$content .= '<td class="' . $class . '" align="center">' . self::versionCompare($itemValue['EM_CONF']['constraints']['depends']['typo3']) . '</td>';

					// need extension update ?
					$updateDate = date('d/m/Y', $lastVersion['lastuploaddate']);
					if (version_compare($itemValue['EM_CONF']['version'], $lastVersion['version'], '<')) {
						$extensionsToUpdate++;
						$content .= '<td class="' . $class . '" align="center"><span style="color:green;font-weight:bold;">' . $lastVersion['version'] . '&nbsp;(' . $updateDate . ')</span></td>';
					} else {
						$content .= '<td class="' . $class . '" align="center">' . $lastVersion['version'] . '&nbsp;(' . $updateDate . ')</td>';
					}
					$content .= '<td class="' . $class . '" align="center">' . $lastVersion['alldownloadcounter'] . '</td>';

					// show db
					$dump_tf1 = '';
					$dump_tf2 = '';
					if (count($FDfile) > 0) {
						$id = 'sql' . $extKey;
						$dump_tf1 = count($FDfile) . ' ' . $GLOBALS['LANG']->getLL('extensions_tablesmodified');
						$dump_tf2 = '<input type="button" onclick="Shadowbox.open({content:\'<div>\'+$(\'' . $id . '\').innerHTML+\'</div>\',player:\'html\',title:\'' . $extKey . '\',height:600,width:800});"'
						            . ' value="+"/><div style="display:none;" id="' . $id . '">'
						            . self::view_array($FDfile) . '</div>';
					}
					$content .= '<td class="' . $class . '">' . $dump_tf1 . '</td>';
					$content .= '<td width="30" class="' . $class . '">' . $dump_tf2 . '</td>';

					// need db update
					if (count($update_statements) > 0) {
						$content .= '<td class="' . $class . '" align="center"><span style="color:red;font-weight:bold;">' . $GLOBALS['LANG']->getLL('yes') . '</span></td>';
					} else {
						$content .= '<td class="' . $class . '" align="center">' . $GLOBALS['LANG']->getLL('no') . '</td>';
					}

					// modified files
					if (count($affectedFiles) > 0) {
						$extensionsModified++;
						$id = 'files' . $extKey;
						$content .= '<td class="' . $class . '"><span style="color:red;font-weight:bold;">' . count($affectedFiles) . ' ' . $GLOBALS['LANG']->getLL('extensions_filesmodified') . '</span>';
						$content .= '<div style="display:none;" id="' . $id . '"><ul>';
						foreach ($affectedFiles as $affectedFile) {
							$compareURL = t3lib_div::getIndpEnv('TYPO3_SITE_URL') . ('index.php?eID=additional_reports_compareFiles&extKey=' . $extKey . '&extFile=' . $affectedFile . '&extVersion=' . $itemValue['EM_CONF']['version']);
							$content .= '<li><a rel="shadowbox;height=600;width=800;" href = "' . $compareURL . '" target = "_blank" title="' . $affectedFile . ' : ' . $extKey . ' ' . $itemValue['EM_CONF']['version'] . '" > ' . $affectedFile . '</a></li>';
						}
						$content .= '</ul>';
						$content .= '</div></td>';
						$content .= '<td width="30" class="' . $class . '" align="center"><input type="button" onclick="$(\'' . $id . '\').toggle();" value="+"/></td>';
					} else {
						$content .= '<td class="' . $class . '">&nbsp;</td><td class="' . $class . '">&nbsp;</td>';
					}
					$content .= '</tr>';
				}
			}
		}

		$content .= '</table>';

		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="15">';
		$content .= $GLOBALS['LANG']->getLL('extensions_dev');
		$content .= '</td></tr>';
		$content .= '<tr class="c-headLine">';
		$content .= '<td class="cell">&nbsp;</td>';
		$content .= '<td class="cell" width="150" colspan="2">' . $GLOBALS['LANG']->getLL('extension') . '</td>';
		$content .= '<td class="cell" width="40" style="text-align:center;">' . $GLOBALS['LANG']->getLL('status_version') . '</td>';
		$content .= '<td class="cell" colspan="2">' . $GLOBALS['LANG']->getLL('extensions_tables') . '</td>';
		$content .= '<td class="cell" width="80" style="text-align:center;">' . $GLOBALS['LANG']->getLL('extensions_tablesintegrity') . '</td>';
		$content .= '<td class="cell" colspan="2">' . $GLOBALS['LANG']->getLL('extensions_files') . '</td>';
		$content .= '</tr>';

		foreach ($itemsDev as $itemKey => $itemValue) {
			if (t3lib_extMgm::isLoaded($itemKey)) {
				$extKey = $itemKey;
				$extInfo = $itemValue;
				$FDfile = array();
				$update_statements = array();
				$affectedFiles = array();
				$lastVersion = '';

				self::getExtAffectedFiles($em, $extKey, $extInfo, $affectedFiles, $lastVersion);
				self::getExtSQLUpdateStatements($em, $extKey, $extInfo, $FDfile, $update_statements);

				$class = "cell";

				$content .= '<tr class="db_list_normal">';
				$content .= '<td class="col-icon ' . $class . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . t3lib_extMgm::extRelPath($extKey) . 'ext_icon.gif"/></td>';
				$content .= '<td class="' . $class . '">' . $extKey . '</td>';
				$content .= '<td width="30" class="' . $class . '" align="center"><a href="#" onclick="top.goToModule(\'tools_em\', 1, \'CMD[showExt]=' . $extKey . '&SET[singleDetails]=info\')"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/zoom.gif"/></a></td>';
				$content .= '<td class="' . $class . '" align="center">' . $itemValue['EM_CONF']['version'] . '</td>';

				// show db
				$dump_tf1 = '';
				$dump_tf2 = '';
				if (count($FDfile) > 0) {
					$id = 'sql' . $extKey;
					$dump_tf1 = count($FDfile) . ' ' . $GLOBALS['LANG']->getLL('extensions_tablesmodified');
					$dump_tf2 = '<input type="button" onclick="Shadowbox.open({content:\'<div>\'+$(\'' . $id . '\').innerHTML+\'</div>\',player:\'html\',title:\'' . $extKey . '\',height:600,width:800});"'
					            . ' value="+"/><div style="display:none;" id="' . $id . '">'
					            . self::view_array($FDfile) . '</div>';
				}
				$content .= '<td class="' . $class . '">' . $dump_tf1 . '</td>';
				$content .= '<td width="30" class="' . $class . '">' . $dump_tf2 . '</td>';

				// need db update
				if (count($update_statements) > 0) {
					$content .= '<td class="' . $class . '" align="center"><span style="color:red;font-weight:bold;">' . $GLOBALS['LANG']->getLL('yes') . '</span></td>';
				} else {
					$content .= '<td class="' . $class . '" align="center">' . $GLOBALS['LANG']->getLL('no') . '</td>';
				}

				// modified files
				if (count($affectedFiles) > 0) {
					$id = 'files' . $extKey;
					$content .= '<td class="' . $class . '"><span style="color:red;font-weight:bold;">' . count($affectedFiles) . ' ' . $GLOBALS['LANG']->getLL('extensions_filesmodified') . '</span>';
					$content .= '<div style="display:none;" id="' . $id . '"><ul>';
					foreach ($affectedFiles as $affectedFile) {
						$content .= '<li>' . $affectedFile . '</li>';
					}
					$content .= '</ul>';
					$content .= '</div></td>';
					$content .= '<td width="30" class="' . $class . '" align="center"><input type="button" onclick="$(\'' . $id . '\').toggle();" value="+"/></td>';
				} else {
					$content .= '<td class="' . $class . '">&nbsp;</td><td class="' . $class . '">&nbsp;</td>';
				}
				$content .= '</tr>';
			}
		}

		$content .= '</table>';

		$addContent = '';
		$addContent .= count($items) . ' ' . $GLOBALS['LANG']->getLL('extensions_extensions');
		$addContent .= '<br/>';
		$addContent .= count($items) - count($itemsDev) . ' ' . $GLOBALS['LANG']->getLL('extensions_ter');
		$addContent .= '  /  ';
		$addContent .= $extensionsDEV . ' ' . $GLOBALS['LANG']->getLL('extensions_dev');
		$addContent .= '<br/>';
		$addContent .= $extensionsToUpdate . ' ' . $GLOBALS['LANG']->getLL('extensions_toupdate');
		$addContent .= '  /  ';
		$addContent .= $extensionsModified . ' ' . $GLOBALS['LANG']->getLL('extensions_extensionsmodified');
		$addContentItem = self::writeInformation($GLOBALS['LANG']->getLL('pluginsmode5') . '<br/>' . $GLOBALS['LANG']->getLL('extensions_updateter') . '', $addContent);

		$content = $addContentItem . $content;
		return $content;
	}

	public function checkMAJ($em, $name) {
		if (t3lib_div::int_from_ver(TYPO3_version) <= 4005000) {
			$em->xmlhandler->searchExtensionsXML($name, '', '', TRUE, TRUE, 0, 500, TRUE);
			$v = $em->xmlhandler->extensionsXML[$name]['versions'];
		} else {
			$em->searchExtensionsXML($name, '', '', TRUE, TRUE, 0, 500, TRUE);
			$v = $em->extensionsXML[$name]['versions'];
		}
		if (is_array($v)) {
			$versions = array_keys($v);
			natsort($versions);
			$lastversion = end($versions);
			return $v[$lastversion];
			return $lastversion;
		} else {
			return null;
		}
	}

	public function writeInformation($label, $value) {
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

	public function view_array($array_in) {
		if (is_array($array_in)) {
			$result = '
			<table border="1" cellpadding="1" cellspacing="0" bgcolor="white" width="100%">';
			if (count($array_in) == 0) {
				$result .= '<tr><td><font face="Verdana,Arial" size="1"><strong>EMPTY!</strong></font></td></tr>';
			} else {
				foreach ($array_in as $key => $val) {
					$result .= '<tr>
						<td valign="top"><font face="Verdana,Arial" size="1">' . htmlspecialchars((string)$key) . '</font></td>
						<td>';
					if (is_array($val)) {
						$result .= self::view_array($val);
					} elseif (is_object($val)) {
						$string = get_class($val);
						if (method_exists($val, '__toString')) {
							$string .= ': ' . (string)$val;
						}
						$result .= '<font face="Verdana,Arial" size="1" color="red">' . nl2br(htmlspecialchars($string)) . '<br /></font>';
					} else {
						if (gettype($val) == 'object') {
							$string = 'Unknown object';
						} else {
							$string = (string)$val;
						}
						$result .= '<font face="Verdana,Arial" size="1" color="red">' . nl2br(htmlspecialchars($string)) . '<br /></font>';
					}
					$result .= '</td>
					</tr>';
				}
			}
			$result .= '</table>';
		} else {
			$result = '<table border="0" cellpadding="1" cellspacing="0" bgcolor="white">
				<tr>
					<td><font face="Verdana,Arial" size="1" color="red">' . nl2br(htmlspecialchars((string)$array_in)) . '<br /></font></td>
				</tr>
			</table>'; // Output it as a string.
		}
		return $result;
	}

	public function displayHooks() {
		$content = '';

		// core hooks
		$items = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'];
		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="7">';
		$content .= $GLOBALS['LANG']->getLL('hooks_core');
		$content .= '</td></tr>';
		if (count($items) > 0) {
			$content .= '<tr class="c-headLine">';
			$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('hooks_corefile') . '</td>';
			$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('hooks_name') . '</td>';
			$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('hooks_file') . '</td>';
			$content .= '</tr>';
			foreach ($items as $itemKey => $itemValue) {
				if (preg_match('/.*?\/.*?\.php/', $itemKey, $matches)) {
					foreach ($itemValue as $hookName => $hookList) {
						$content .= '<tr class="db_list_normal">';
						$content .= '<td class="cell">' . $itemKey . '</td>';
						$content .= '<td class="cell">' . $hookName . '</td>';
						$content .= '<td class="cell"><ul>';
						foreach ($hookList as $hookPath) {
							if (is_array($hookPath)) {
								foreach ($hookPath as $hookPathValue) {
									$content .= '<li>' . $hookPathValue . '</li>';
								}
							} else {
								$content .= '<li>' . $hookPath . '</li>';
							}
						}
						$content .= '</ul></td>';
						$content .= '</tr>';
					}
				}
			}
		} else {
			$content .= '<tr class="db_list_normal" colspan="5"><td class="cell">' . $GLOBALS['LANG']->getLL('noresults') . '</td></tr>';
		}
		$content .= '</table>';

		// extension hooks (we read the temp_CACHED and look for $EXTCONF modification)
		$tempCached = self::getCacheFilePrefix() . '_ext_localconf.php';
		$items = array();
		if (is_file(PATH_site . 'typo3conf/' . $tempCached)) {
			$handle = fopen(PATH_site . 'typo3conf/' . $tempCached, 'r');
			$extension = '';
			if ($handle) {
				while (!feof($handle)) {
					$buffer = fgets($handle);
					if ($extension != '') {
						if (preg_match("/\['EXTCONF'\]\['(.*?)'\].*?=/", $buffer, $matches)) {
							if ($matches[1] != $extension) {
								$items [] = array($extension, $buffer);
							}
						}
					}
					if (preg_match('/## EXTENSION: (.*?)$/', $buffer, $matches)) {
						$extension = $matches[1];
					}
				}
				fclose($handle);
			}
		}

		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="7">';
		$content .= $GLOBALS['LANG']->getLL('hooks_extension');
		$content .= '</td></tr>';
		if (count($items) > 0) {
			$content .= '<tr class="c-headLine">';
			$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('extension') . '</td>';
			$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('hooks_line') . '</td>';
			$content .= '</tr>';
			foreach ($items as $itemKey => $itemValue) {
				$content .= '<tr class="db_list_normal">';
				$content .= '<td class="cell">' . $itemValue[0] . '</td>';
				$content .= '<td class="cell">' . $itemValue[1] . '</td>';
				$content .= '</tr>';

			}
		} else {
			$content .= '<tr class="db_list_normal" colspan="5"><td class="cell">' . $GLOBALS['LANG']->getLL('noresults') . '</td></tr>';
		}
		$content .= '</table>';
		return $content;
	}

	public function getCacheFilePrefix() {
		$extensionCacheBehaviour = intval($GLOBALS['TYPO3_CONF_VARS']['EXT']['extCache']);

		// Caching of extensions is disabled when install tool is used:
		if (defined('TYPO3_enterInstallScript') && TYPO3_enterInstallScript) {
			$extensionCacheBehaviour = 0;
		}

		$cacheFileSuffix = (TYPO3_MODE == 'FE' ? '_FE' : '');
		$cacheFilePrefix = 'temp_CACHED' . $cacheFileSuffix;

		if ($extensionCacheBehaviour == 1) {
			$cacheFilePrefix .= '_ps' . substr(t3lib_div::shortMD5(PATH_site . '|' . $GLOBALS['TYPO_VERSION']), 0, 4);
		} elseif ($extensionCacheBehaviour == 2) {
			$cacheFilePrefix .= '_' . t3lib_div::shortMD5(self::getEnabledExtensionList());
		}

		return $cacheFilePrefix;
	}

	public function displayStatus() {
		$content = '';
		// Typo3
		$content .= '<h2 id="reportsTypo3" class="section-header expanded">TYPO3 :</h2>';
		$content .= '<div>';
		$content .= self::writeInformation($GLOBALS['LANG']->getLL('status_sitename'), $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']);
		$content .= self::writeInformation($GLOBALS['LANG']->getLL('status_version'), TYPO3_version);
		$content .= self::writeInformation($GLOBALS['LANG']->getLL('status_path'), PATH_site);
		$content .= self::writeInformation('TYPO3_db', TYPO3_db);
		$content .= self::writeInformation('TYPO3_db_username', TYPO3_db_username);
		$content .= self::writeInformation('TYPO3_db_host', TYPO3_db_host);
		if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path'] != '') {
			$cmd = t3lib_div::imageMagickCommand('convert', '-version');
			exec($cmd, $ret);
			$content .= self::writeInformation($GLOBALS['LANG']->getLL('status_im'), $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path'] . ' (' . $ret[0] . ')');
		}
		$content .= self::writeInformation('forceCharset', $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] . '&nbsp;');
		$content .= self::writeInformation('setDBinit', $GLOBALS['TYPO3_CONF_VARS']['SYS']['setDBinit'] . '&nbsp;');
		$content .= '<div class="typo3-message message-information">';
		$content .= '<div class="header-container">';
		$extensions = explode(',', $GLOBALS['TYPO3_CONF_VARS']['EXT']['extList']);
		sort($extensions);
		$content .= '<div class="message-header message-left">' . $GLOBALS['LANG']->getLL('status_loadedextensions') . ' - ' . count($extensions) . ' extensions</div>';
		$content .= '<div class="message-header message-right">';
		$content .= '<ul>';
		foreach ($extensions as $extension) {
			$content .= '<li>' . $extension . ' (' . self::getExtensionVersion($extension) . ')</li>';
		}
		$content .= '</ul>';
		$content .= '</div>';
		$content .= '</div>';
		$content .= '<div class="message-body"></div>';
		$content .= '</div>';
		$content .= '</div>';
		// PHP
		$content .= '<h2 id="reportsPHP" class="section-header expanded">PHP :</h2>';
		$content .= '<div>';
		$content .= self::writeInformation($GLOBALS['LANG']->getLL('status_version'), phpversion());
		$content .= self::writeInformation('memory_limit', ini_get('memory_limit'));
		$content .= self::writeInformation('max_execution_time', ini_get('max_execution_time'));
		$content .= self::writeInformation('post_max_size', ini_get('post_max_size'));
		$content .= self::writeInformation('upload_max_filesize', ini_get('upload_max_filesize'));
		$content .= '<div class="typo3-message message-information">';
		$content .= '<div class="header-container">';
		$content .= '<div class="message-header message-left">' . $GLOBALS['LANG']->getLL('status_loadedextensions') . '</div>';
		$content .= '<div class="message-header message-right">';
		$content .= '<ul>';
		$extensions = get_loaded_extensions();
		natcasesort($extensions);
		foreach ($extensions as $extension) {
			if (phpversion($extension)) {
				$content .= '<li>' . $extension . ' (' . phpversion($extension) . ')</li>';
			} else {
				$content .= '<li>' . $extension . '</li>';
			}
		}
		$content .= '</ul>';
		$content .= '</div>';
		$content .= '</div>';
		$content .= '<div class="message-body"></div>';
		$content .= '</div>';
		$content .= '</div>';
		// Apache
		if (function_exists('apache_get_version') && function_exists('apache_get_modules')) {
			$content .= '<h2 id="reportsApache" class="section-header expanded">Apache :</h2>';
			$content .= '<div>';
			$extensions = apache_get_modules();
			natcasesort($extensions);
			$content .= self::writeInformation($GLOBALS['LANG']->getLL('status_version'), apache_get_version());
			$content .= self::writeInformationList($GLOBALS['LANG']->getLL('status_loadedextensions'), $extensions);
			$content .= '</div>';
		}
		// MySQL
		$content .= '<h2 id="reportsMySQL" class="section-header expanded">MySQL :</h2>';
		$content .= '<div>';
		$content .= self::writeInformation('Version', mysql_get_server_info());
		$items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('default_character_set_name, default_collation_name', 'information_schema.schemata', 'schema_name = \'' . TYPO3_db . '\'');
		$content .= self::writeInformation('default_character_set_name', $items[0]['default_character_set_name']);
		$content .= self::writeInformation('default_collation_name', $items[0]['default_collation_name']);
		$items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('table_name, engine, table_collation, table_rows ', 'information_schema.tables', 'table_schema = \'' . TYPO3_db . '\'', '', 'table_name');
		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist" width="100%">';
		$content .= '<tr class="t3-row-header"><td colspan="4">TYPO3_db - ' . count($items) . ' tables</td></tr>';
		$content .= '<tr class="c-headLine">';
		$content .= '<td class="cell">Name</td>';
		$content .= '<td class="cell">Engine</td>';
		$content .= '<td class="cell">Collation</td>';
		$content .= '<td class="cell">Rows</td>';
		$content .= '</tr>';
		foreach ($items as $itemKey => $itemValue) {
			$content .= '<tr class="db_list_normal">';
			$content .= '<td class="cell">' . $itemValue['table_name'] . '</td>';
			$content .= '<td class="cell">' . $itemValue['engine'] . '</td>';
			$content .= '<td class="cell">' . $itemValue['table_collation'] . '</td>';
			$content .= '<td class="cell">' . $itemValue['table_rows'] . '</td>';
			$content .= '</tr>';

		}
		$content .= '</table>';
		$content .= '</div>';
		// Crontab
		$content .= '<h2 id="reportsTypo3" class="section-header expanded">Crontab :</h2>';
		$content .= '<div>';
		exec('crontab -l', $crontab);
		$crontabString = $GLOBALS['LANG']->getLL('status_nocrontab');
		if (count($crontab) > 0) {
			$crontabString = '';
			foreach ($crontab as $cron) {
				if (trim($cron) != '') {
					$crontabString .= $cron . '<br />';
				}
			}
		}
		$content .= self::writeInformation('Crontab', $crontabString);
		$content .= '</div>';
		return $content;
	}

	public function writeInformationList($label, $array) {
		$content = '
			<div class="typo3-message message-information">
				<div class="header-container">
					<div class="message-header message-left">' . $label . '</div>
					<div class="message-header message-right"><ul>';

		foreach ($array as $value) {
			$content .= '<li>' . $value . '</li>';
		}

		$content .= '</ul></div>
				</div>
				<div class="message-body"></div>
			</div>
		';

		return $content;
	}

	public static function getExtensionVersion($key) {
		if (!is_string($key) || empty($key)) {
			throw new InvalidArgumentException('Extension key must be a non-empty string.');
		}
		if (!t3lib_extMgm::isLoaded($key)) {
			return '';
		}

		$_EXTKEY = $key;
		include(t3lib_extMgm::extPath($key) . 'ext_emconf.php');

		return $EM_CONF[$key]['version'];
	}

	public function displayXclass() {
		$content = '';
		$items = $GLOBALS['TYPO3_CONF_VARS']['BE']['XCLASS'];
		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="7">';
		$content .= 'Backend :';
		$content .= '</td></tr>';
		if (count($items) > 0) {
			$content .= '<tr class="c-headLine">';
			$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('name') . '</td>';
			$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('path') . '</td>';
			$content .= '</tr>';
			foreach ($items as $itemKey => $itemValue) {
				$content .= '<tr class="db_list_normal">';
				$content .= '<td class="cell">' . $itemKey . '</td>';
				$content .= '<td class="cell">' . $itemValue . '</td>';
				$content .= '</tr>';

			}
		} else {
			$content .= '<tr class="db_list_normal" colspan="5"><td class="cell">' . $GLOBALS['LANG']->getLL('noresults') . '</td></tr>';
		}
		$content .= '</table>';
		$items = $GLOBALS['TYPO3_CONF_VARS']['FE']['XCLASS'];
		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="7">';
		$content .= 'Frontend :';
		$content .= '</td></tr>';
		if (count($items) > 0) {
			$content .= '<tr class="c-headLine">';
			$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('name') . '</td>';
			$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('path') . '</td>';
			$content .= '</tr>';
			foreach ($items as $itemKey => $itemValue) {
				$content .= '<tr class="db_list_normal">';
				$content .= '<td class="cell">' . $itemKey . '</td>';
				$content .= '<td class="cell">' . $itemValue . '</td>';
				$content .= '</tr>';

			}
		} else {
			$content .= '<tr class="db_list_normal" colspan="5"><td class="cell">' . $GLOBALS['LANG']->getLL('noresults') . '</td></tr>';
		}
		$content .= '</table>';
		return $content;
	}

	public function displayPlugins() {
		$url = $this->baseURL;
		$content = '<table>';
		$content .= '<tr>';
		$content .= '<td><input onClick="jumpToUrl(\'' . $url . '&display=1\');" style="margin-right:4px;" type="radio" name="display" value="1" id="radio1"' . (($this->display == 1)
				? ' checked="checked"'
				: '') . '/><label for="radio1" style="margin-right:10px;">' . $GLOBALS['LANG']->getLL('pluginsmode1') . '</label></td>';
		$content .= '<td><input onClick="jumpToUrl(\'' . $url . '&display=4\');" style="margin-right:4px;" type="radio" name="display" value="4" id="radio4"' . (($this->display == 4)
				? ' checked="checked"'
				: '') . '/><label for="radio4" style="margin-right:10px;">' . $GLOBALS['LANG']->getLL('pluginsmode4') . '</label></td>';
		$content .= '<td><input onClick="jumpToUrl(\'' . $url . '&display=6\');" style="margin-right:4px;" type="radio" name="display" value="6" id="radio6"' . (($this->display == 6)
				? ' checked="checked"'
				: '') . '/><label for="radio6" style="margin-right:10px;">' . $GLOBALS['LANG']->getLL('pluginsmode4hidden') . '</label></td>';
		$content .= '</tr>';
		$content .= '<tr>';
		$content .= '<td><input onClick="jumpToUrl(\'' . $url . '&display=2\');" style="margin-right:4px;" type="radio" name="display" value="2" id="radio2"' . (($this->display == 2)
				? ' checked="checked"'
				: '') . '/><label for="radio2" style="margin-right:10px;">' . $GLOBALS['LANG']->getLL('pluginsmode2') . '</label></td>';
		$content .= '<td><input onClick="jumpToUrl(\'' . $url . '&display=3\');" style="margin-right:4px;" type="radio" name="display" value="3" id="radio3"' . (($this->display == 3)
				? ' checked="checked"'
				: '') . '/><label for="radio3" style="margin-right:10px;">' . $GLOBALS['LANG']->getLL('pluginsmode3') . '</label></td>';
		$content .= '<td><input onClick="jumpToUrl(\'' . $url . '&display=7\');" style="margin-right:4px;" type="radio" name="display" value="7" id="radio7"' . (($this->display == 7)
				? ' checked="checked"'
				: '') . '/><label for="radio7" style="margin-right:10px;">' . $GLOBALS['LANG']->getLL('pluginsmode3hidden') . '</label></td>';
		$content .= '</tr>';
		$content .= '<tr>';
		$content .= '<td><input onClick="jumpToUrl(\'' . $url . '&display=5\');" style="margin-right:4px;" type="radio" name="display" value="5" id="radio5"' . (($this->display == 5)
				? ' checked="checked"'
				: '') . '/><label for="radio5" style="margin-right:10px;">' . $GLOBALS['LANG']->getLL('pluginsmode5') . '</label></td>';
		$content .= '</tr>';
		$content .= '</table><div class="uppercase" style="margin-bottom:5px;"></div>';

		switch ($this->display) {
			case 1 :
				$content .= self::getAllPlugins();
				break;
			case 2 :
				$content .= self::getAllCType();
				break;
			case 3 :
				$content .= self::getAllUsedCType();
				break;
			case 4 :
				$content .= self::getAllUsedPlugins();
				break;
			case 5 :
				$content .= self::getSummary();
				break;
			case 6 :
				$content .= self::getAllUsedPlugins(true);
				break;
			case 7 :
				$content .= self::getAllUsedCType(true);
				break;
		}

		return $content;
	}

	public function getAllPlugins() {
		$content = '';
		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="10">';
		$content .= $GLOBALS['LANG']->getLL('pluginsmode1');
		$content .= '</td></tr>';
		$content .= '<tr class="c-headLine">';
		$content .= '<td class="cell">&nbsp;</td>';
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

	public function getAllUsedPlugins($displayHidden = false) {
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
			'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.deleted=0 AND pages.deleted=0 ' . $addhidden . 'AND tt_content.CType=\'list\'',
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
			'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.deleted=0 AND pages.deleted=0 ' . $addhidden . 'AND tt_content.CType=\'list\'' . $addWhere,
			'',
			'tt_content.list_type,tt_content.pid'
		);
		// Page browser
		$pointer = t3lib_div::_GP('pointer');
		$limit = ($pointer !== null) ? $pointer . ',' . $this->nbElementsPerPage : '0,' . $this->nbElementsPerPage;
		$current = ($pointer !== null) ? intval($pointer) : 0;
		$pageBrowser = self::pluginsRenderListNavigation(count($items), $this->nbElementsPerPage, $current);
		$itemsBrowser = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'DISTINCT tt_content.list_type,tt_content.pid,tt_content.uid,pages.title,pages.hidden as "hiddenpages",tt_content.hidden as "hiddentt_content"',
			'tt_content,pages',
			'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.deleted=0 AND pages.deleted=0 ' . $addhidden . 'AND tt_content.CType=\'list\'' . $addWhere,
			'',
			'tt_content.list_type,tt_content.pid',
			$limit
		);

		$content = '';

		$content .= $pageBrowser;

		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="15">';
		$content .= $GLOBALS['LANG']->getLL('pluginsmode4');
		$content .= '</td></tr>';
		$content .= '<tr class="c-headLine">';
		$content .= '<td class="cell">&nbsp;</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('extension') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('plugin') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('domain') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('pid') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('uid') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('pagetitle') . '</td>';
		if (t3lib_extMgm::isLoaded('templavoila') && class_exists('tx_templavoila_api')) {
			$content .= '<td class="cell" align="center">DB mode</td>';
			$content .= '<td class="cell" align="center">Page TV</td>';
			$content .= '<td class="cell" align="center">' . $GLOBALS['LANG']->getLL('tvused') . '</td>';
		} else {
			$content .= '<td class="cell" align="center">Page</td>';
			$content .= '<td class="cell" align="center">DB mode</td>';
		}
		$content .= '<td class="cell" align="center">' . $GLOBALS['LANG']->getLL('preview') . '</td>';
		$content .= '</tr>';
		foreach ($itemsBrowser as $itemKey => $itemValue) {
			preg_match('/EXT:(.*?)\//', $plugins[$itemValue['list_type']][0], $ext);
			preg_match('/^LLL:(EXT:.*?):(.*)/', $plugins[$itemValue['list_type']][0], $llfile);
			$LOCAL_LANG = t3lib_div::readLLfile($llfile[1], $GLOBALS['LANG']->lang);

			$pageSelect = t3lib_div::makeInstance('t3lib_pageSelect');
			$rootLine = $pageSelect->getRootLine($itemValue['pid']);
			$domain = t3lib_BEfunc::firstDomainRecord($rootLine);
			if ($domain === NULL) {
				$domain = t3lib_div::getIndpEnv('TYPO3_HOST_ONLY');
			}

			$content .= '<tr class="db_list_normal">';
			if ($plugins[trim($ext[1])]) {
				$content .= '<td class="col-icon"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . $plugins[trim($ext[1])][2] . '"/></td>';
			} else {
				if ($ext) {
					$content .= '<td class="col-icon"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . '../typo3conf/ext/' . $ext[1] . '/ext_icon.gif"/></td>';
				} else {
					$content .= '<td class="col-icon">&nbsp;</td>';
				}
			}
			$content .= '<td class="cell">' . $ext[1] . '</td>';
			$content .= '<td class="cell">' . $GLOBALS['LANG']->getLLL($llfile[2], $LOCAL_LANG) . ' (' . $itemValue['list_type'] . ')</td>';
			$content .= '<td class="cell"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/i/domain.gif"/>' . $domain . '</td>';
			$iconPage = ($itemValue['hiddenpages'] == 0)
					? '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/i/pages.gif"/>'
					: '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/i/pages__h.gif"/>';
			$iconContent = ($itemValue['hiddentt_content'] == 0)
					? '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/i/tt_content.gif"/>'
					: '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/i/tt_content__h.gif"/>';
			$content .= '<td class="cell">' . $iconPage . ' ' . $itemValue['pid'] . '</td>';
			$content .= '<td class="cell">' . $iconContent . ' ' . $itemValue['uid'] . '</td>';
			$content .= '<td class="cell">' . $itemValue['title'] . '</td>';

			if (t3lib_extMgm::isLoaded('templavoila') && class_exists('tx_templavoila_api')) {
				$content .= '<td class="cell" align="center">';
				$content .= '<a href="#" onclick="' . self::goToModuleList($itemValue['pid']) . '" title="' . $GLOBALS['LANG']->getLL('switch') . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/zoom.gif"/></span></a>';
				$content .= '<a target="_blank" href="' . self::goToModuleList($itemValue['pid'], TRUE) . '" title="' . $GLOBALS['LANG']->getLL('newwindow') . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/zoom.gif"/></span></a>';
				$content .= '</td>';
				$content .= '<td class="cell" align="center">';
				$content .= '<a href="#" onclick="' . self::goToModulePageTV($itemValue['pid']) . '" title="' . $GLOBALS['LANG']->getLL('switch') . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/zoom.gif"/></span></a>';
				$content .= '<a target="_blank" href="' . self::goToModulePageTV($itemValue['pid'], TRUE) . '" title="' . $GLOBALS['LANG']->getLL('newwindow') . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/zoom.gif"/></span></a>';
				$content .= '</td>';
				if (self::isUsedInTV($itemValue['uid'], $itemValue['pid'])) {
					$content .= '<td class="cell typo3-message message-ok">' . $GLOBALS['LANG']->getLL('yes') . '</td>';
				} else {
					$content .= '<td class="cell typo3-message message-error">' . $GLOBALS['LANG']->getLL('no') . '</td>';
				}
			} else {
				$content .= '<td class="cell" align="center">';
				$content .= '<a href="#" onclick="' . self::goToModuleList($itemValue['pid']) . '" title="' . $GLOBALS['LANG']->getLL('switch') . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/zoom.gif"/></span></a>';
				$content .= '<a target="_blank" href="' . self::goToModuleList($itemValue['pid'], TRUE) . '" title="' . $GLOBALS['LANG']->getLL('newwindow') . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/zoom.gif"/></span></a>';
				$content .= '</td>';
				$content .= '<td class="cell" align="center">';
				$content .= '<a href="#" onclick="' . self::goToModulePage($itemValue['pid']) . '" title="' . $GLOBALS['LANG']->getLL('switch') . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/zoom.gif"/></span></a>';
				$content .= '<a target="_blank" href="' . self::goToModulePage($itemValue['pid'], TRUE) . '" title="' . $GLOBALS['LANG']->getLL('newwindow') . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/zoom.gif"/></span></a>';
				$content .= '</td>';
			}
			$content .= '<td class="cell" align="center"><a target="_blank" href="http://' . $domain . '/index.php?id=' . $itemValue['pid'] . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/zoom.gif"/></a></td>';
			$content .= '</tr>';
		}
		$content .= '</table>';
		return $content;
	}

	public function goToModuleList($uid, $urlOnly = FALSE) {
		$url = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'db_list.php?id=' . $uid;
		if ($urlOnly === TRUE) {
			return $url;
		} else {
			return 'top.nextLoadModuleUrl=\'' . $url . '\';top.goToModule(\'web_list\');';
		}
	}

	public function goToModulePage($uid, $urlOnly = FALSE) {
		$url = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/cms/layout/db_layout.php?id=' . $uid;
		if ($urlOnly === TRUE) {
			return $url;
		} else {
			return 'top.nextLoadModuleUrl=\'' . $url . '\';top.goToModule(\'web_layout\');';
		}
	}

	public function goToModulePageTV($uid, $urlOnly = FALSE) {
		$url = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . '../typo3conf/ext/templavoila/mod1/index.php?id=' . $uid;
		if ($urlOnly === TRUE) {
			return $url;
		} else {
			return 'top.nextLoadModuleUrl=\'' . $url . '\';top.goToModule(\'web_txtemplavoilaM1\');';
		}
	}

	public function getAllCType() {
		$content = '';
		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="3">';
		$content .= $GLOBALS['LANG']->getLL('pluginsmode2');
		$content .= '</td></tr>';
		$content .= '<tr class="c-headLine">';
		$content .= '<td class="cell">&nbsp;</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('ctype') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('used') . '</td>';
		$content .= '</tr>';

		foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $itemKey => $itemValue) {
			if ($itemValue[1] != '--div--') {
				$temp = null;
				preg_match('/^LLL:(EXT:.*?):(.*)/', $itemValue[0], $llfile);
				$LOCAL_LANG = t3lib_div::readLLfile($llfile[1], $GLOBALS['LANG']->lang);
				$content .= '<tr class="db_list_normal">';
				$content .= '<td class="col-icon">';
				if ($itemValue[2] != '') {
					if (is_file(PATH_site . 'typo3/sysext/t3skin/icons/gfx/' . $itemValue[2])) {
						$content .= '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/' . $itemValue[2] . '"/>';
					} elseif (preg_match('/^\.\./', $itemValue[2], $temp)) {
						$content .= '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . $itemValue[2] . '"/>';
					} elseif (preg_match('/^EXT:(.*)$/', $itemValue[2], $temp)) {
						$content .= '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . '../typo3conf/ext/' . $temp[1] . '"/>';
					}
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

	public function getAllUsedCType($displayHidden = false) {
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
			'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.deleted=0 AND pages.deleted=0 ' . $addhidden . 'AND tt_content.CType<>\'list\'',
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
			'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.deleted=0 AND pages.deleted=0 ' . $addhidden . 'AND tt_content.CType<>\'list\'' . $addWhere,
			'',
			'tt_content.CType,tt_content.pid'
		);
		// Page browser
		$pointer = t3lib_div::_GP('pointer');
		$limit = ($pointer !== null) ? $pointer . ',' . $this->nbElementsPerPage : '0,' . $this->nbElementsPerPage;
		$current = ($pointer !== null) ? intval($pointer) : 0;
		$pageBrowser = self::pluginsRenderListNavigation(count($items), $this->nbElementsPerPage, $current);
		$itemsBrowser = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'DISTINCT tt_content.CType,tt_content.pid,tt_content.uid,pages.title,pages.hidden as "hiddenpages",tt_content.hidden as "hiddentt_content"',
			'tt_content,pages',
			'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.deleted=0 AND pages.deleted=0 ' . $addhidden . 'AND tt_content.CType<>\'list\'' . $addWhere,
			'',
			'tt_content.CType,tt_content.pid',
			$limit
		);

		$content = '';

		$content .= $pageBrowser;

		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="15">';
		$content .= $GLOBALS['LANG']->getLL('pluginsmode3');
		$content .= '</td></tr>';
		$content .= '<tr class="c-headLine">';
		$content .= '<td class="cell">&nbsp;</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('ctype') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('domain') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('pid') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('uid') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('pagetitle') . '</td>';
		if (t3lib_extMgm::isLoaded('templavoila') && class_exists('tx_templavoila_api')) {
			$content .= '<td class="cell" align="center">DB mode</td>';
			$content .= '<td class="cell" align="center">Page TV</td>';
			$content .= '<td class="cell" align="center">' . $GLOBALS['LANG']->getLL('tvused') . '</td>';
		} else {
			$content .= '<td class="cell" align="center">Page</td>';
			$content .= '<td class="cell" align="center">DB mode</td>';
		}
		$content .= '<td class="cell" align="center">' . $GLOBALS['LANG']->getLL('preview') . '</td>';
		$content .= '</tr>';
		foreach ($itemsBrowser as $itemKey => $itemValue) {
			$temp = NULL;
			preg_match('/^LLL:(EXT:.*?):(.*)/', $ctypes[$itemValue['CType']][0], $llfile);
			$LOCAL_LANG = t3lib_div::readLLfile($llfile[1], $GLOBALS['LANG']->lang);

			$pageSelect = t3lib_div::makeInstance('t3lib_pageSelect');
			$rootLine = $pageSelect->getRootLine($itemValue['pid']);
			$domain = t3lib_BEfunc::firstDomainRecord($rootLine);
			if ($domain === NULL) {
				$domain = t3lib_div::getIndpEnv('TYPO3_HOST_ONLY');
			}

			$content .= '<tr class="db_list_normal">';
			$content .= '<td class="col-icon">';
			if ($ctypes[$itemValue['CType']][2] != '') {
				if (is_file(PATH_site . 'typo3/sysext/t3skin/icons/gfx/' . $ctypes[$itemValue['CType']][2])) {
					$content .= '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/' . $ctypes[$itemValue['CType']][2] . '"/>';
				} elseif (preg_match('/^\.\./', $ctypes[$itemValue['CType']][2], $temp)) {
					$content .= '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . $ctypes[$itemValue['CType']][2] . '"/>';
				} elseif (preg_match('/^EXT:(.*)$/', $ctypes[$itemValue['CType']][2], $temp)) {
					$content .= '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . '../typo3conf/ext/' . $temp[1] . '"/>';
				}
			}
			$content .= '</td>';
			$content .= '<td class="cell">' . $GLOBALS['LANG']->getLLL($llfile[2], $LOCAL_LANG) . ' (' . $itemValue['CType'] . ')</td>';
			$content .= '<td class="cell"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/i/domain.gif"/>' . $domain . '</td>';
			$iconPage = ($itemValue['hiddenpages'] == 0)
					? '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/i/pages.gif"/>'
					: '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/i/pages__h.gif"/>';
			$iconContent = ($itemValue['hiddentt_content'] == 0)
					? '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/i/tt_content.gif"/>'
					: '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/i/tt_content__h.gif"/>';
			$content .= '<td class="cell">' . $iconPage . ' ' . $itemValue['pid'] . '</td>';
			$content .= '<td class="cell">' . $iconContent . ' ' . $itemValue['uid'] . '</td>';
			$content .= '<td class="cell">' . $itemValue['title'] . '</td>';
			if (t3lib_extMgm::isLoaded('templavoila') && class_exists('tx_templavoila_api')) {
				$content .= '<td class="cell" align="center">';
				$content .= '<a href="#" onclick="' . self::goToModuleList($itemValue['pid']) . '" title="' . $GLOBALS['LANG']->getLL('switch') . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/zoom.gif"/></span></a>';
				$content .= '<a target="_blank" href="' . self::goToModuleList($itemValue['pid'], TRUE) . '" title="' . $GLOBALS['LANG']->getLL('newwindow') . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/zoom.gif"/></span></a>';
				$content .= '</td>';
				$content .= '<td class="cell" align="center">';
				$content .= '<a href="#" onclick="' . self::goToModulePageTV($itemValue['pid']) . '" title="' . $GLOBALS['LANG']->getLL('switch') . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/zoom.gif"/></span></a>';
				$content .= '<a target="_blank" href="' . self::goToModulePageTV($itemValue['pid'], TRUE) . '" title="' . $GLOBALS['LANG']->getLL('newwindow') . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/zoom.gif"/></span></a>';
				$content .= '</td>';
				if (self::isUsedInTV($itemValue['uid'], $itemValue['pid'])) {
					$content .= '<td class="cell typo3-message message-ok">' . $GLOBALS['LANG']->getLL('yes') . '</td>';
				} else {
					$content .= '<td class="cell typo3-message message-error">' . $GLOBALS['LANG']->getLL('no') . '</td>';
				}
			} else {
				$content .= '<td class="cell" align="center">';
				$content .= '<a href="#" onclick="' . self::goToModuleList($itemValue['pid']) . '" title="' . $GLOBALS['LANG']->getLL('switch') . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/zoom.gif"/></span></a>';
				$content .= '<a target="_blank" href="' . self::goToModuleList($itemValue['pid'], TRUE) . '" title="' . $GLOBALS['LANG']->getLL('newwindow') . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/zoom.gif"/></span></a>';
				$content .= '</td>';
				$content .= '<td class="cell" align="center">';
				$content .= '<a href="#" onclick="' . self::goToModulePage($itemValue['pid']) . '" title="' . $GLOBALS['LANG']->getLL('switch') . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/zoom.gif"/></span></a>';
				$content .= '<a target="_blank" href="' . self::goToModulePage($itemValue['pid'], TRUE) . '" title="' . $GLOBALS['LANG']->getLL('newwindow') . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/zoom.gif"/></span></a>';
				$content .= '</td>';
			}
			$content .= '<td class="cell" align="center"><a target="_blank" href="http://' . $domain . '/index.php?id=' . $itemValue['pid'] . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/zoom.gif"/></a></td>';
			$content .= '</tr>';
		}
		$content .= '</table>';
		return $content;
	}

	function getSummary() {
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

		$itemsCount = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('COUNT( tt_content.uid ) as "nb"', 'tt_content,pages', 'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.hidden=0 AND tt_content.deleted=0 AND pages.hidden=0 AND pages.deleted=0');
		$items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('tt_content.CType,tt_content.list_type,count(*) as "nb"', 'tt_content,pages', 'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.hidden=0 AND tt_content.deleted=0 AND pages.hidden=0 AND pages.deleted=0', 'tt_content.CType,tt_content.list_type', 'nb DESC');

		$content = '';
		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="4">';
		$content .= $GLOBALS['LANG']->getLL('pluginsmode5');
		$content .= '</td></tr>';
		$content .= '<tr class="c-headLine">';
		$content .= '<td class="cell">&nbsp;</td>';
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
				if ($plugins[$itemValue['list_type']][2]) {
					$content .= '<td class="col-icon"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . $plugins[$itemValue['list_type']][2] . '"/></td>';
				} else {
					$content .= '<td class="col-icon">&nbsp;</td>';
				}
				$content .= '<td class="cell">' . $GLOBALS['LANG']->getLLL($llfile[2], $LOCAL_LANG) . ' (' . $itemValue['list_type'] . ')</td>';
			} else {
				preg_match('/^LLL:(EXT:.*?):(.*)/', $ctypes[$itemValue['CType']][0], $llfile);
				$LOCAL_LANG = t3lib_div::readLLfile($llfile[1], $GLOBALS['LANG']->lang);
				$content .= '<td class="col-icon">';
				if (is_file(PATH_site . '/typo3/sysext/t3skin/icons/gfx/' . $ctypes[$itemValue['CType']][2])) {
					$content .= '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/' . $ctypes[$itemValue['CType']][2] . '"/>';
				} elseif (preg_match('/^\.\./', $ctypes[$itemValue['CType']][2], $temp)) {
					$content .= '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . $ctypes[$itemValue['CType']][2] . '"/>';
				} elseif (preg_match('/^EXT:(.*)$/', $ctypes[$itemValue['CType']][2], $temp)) {
					$content .= '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . '../typo3conf/ext/' . $temp[1] . '"/>';
				} else {
					$content .= '';
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

	function isUsedInTV($uid, $pid) {
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

	public function pluginsRenderListNavigation($totalItems, $iLimit, $firstElementNumber, $renderPart = 'top') {
		$totalPages = ceil($totalItems / $iLimit);

		$content = '';
		$returnContent = '';
		// Show page selector if not all records fit into one page
		if ($totalPages >= 1) {
			$first = $previous = $next = $last = $reload = '';
			$listURLOrig = $this->baseURL . '&display=' . $this->display;
			$listURL = $this->baseURL . '&display=' . $this->display;
			$listURL .= '&nbPerPage=' . $this->nbElementsPerPage;
			$getFiltersCat = t3lib_div::_GP('filtersCat');
			if ($getFiltersCat !== null) {
				$listURL .= '&filtersCat=' . $getFiltersCat;
			}
			$orderby = t3lib_div::_GP('orderby');
			if ($orderby !== null) {
				$listURL .= '&orderby=' . $orderby;
			}
			$currentPage = floor(($firstElementNumber + 1) / $iLimit) + 1;
			// First
			if ($currentPage > 1) {
				$labelFirst = $GLOBALS['LANG']->getLL('first');
				$first = '<a href="' . $listURL . '&pointer=0"><img width="16" height="16" title="' . $labelFirst . '" alt="' . $labelFirst . '" src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . '../typo3conf/ext/additional_reports/res/control_first.gif"></a>';
			} else {
				$first = '<img width="16" height="16" title="" alt="" src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . '../typo3conf/ext/additional_reports/res/control_first_disabled.gif">';
			}
			// Previous
			if (($currentPage - 1) > 0) {
				$labelPrevious = $GLOBALS['LANG']->getLL('previous');
				$previous = '<a href="' . $listURL . '&pointer=' . (($currentPage - 2) * $iLimit) . '"><img width="16" height="16" title="' . $labelPrevious . '" alt="' . $labelPrevious . '" src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . '../typo3conf/ext/additional_reports/res/control_previous.gif"></a>';
			} else {
				$previous = '<img width="16" height="16" title="" alt="" src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . '../typo3conf/ext/additional_reports/res/control_previous_disabled.gif">';
			}
			// Next
			if (($currentPage + 1) <= $totalPages) {
				$labelNext = $GLOBALS['LANG']->getLL('next');
				$next = '<a href="' . $listURL . '&pointer=' . (($currentPage) * $iLimit) . '"><img width="16" height="16" title="' . $labelNext . '" alt="' . $labelNext . '" src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . '../typo3conf/ext/additional_reports/res/control_next.gif"></a>';
			} else {
				$next = '<img width="16" height="16" title="" alt="" src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . '../typo3conf/ext/additional_reports/res/control_next_disabled.gif">';
			}
			// Last
			if ($currentPage != $totalPages) {
				$labelLast = $GLOBALS['LANG']->getLL('last');
				$last = '<a href="' . $listURL . '&pointer=' . (($totalPages - 1) * $iLimit) . '"><img width="16" height="16" title="' . $labelLast . '" alt="' . $labelLast . '" src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . '../typo3conf/ext/additional_reports/res/control_last.gif"></a>';
			} else {
				$last = '<img width="16" height="16" title="" alt="" src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . '../typo3conf/ext/additional_reports/res/control_last_disabled.gif">';
			}

			$pageNumberInput = '<span>' . $currentPage . '</span>';
			$pageIndicator = '<span class="pageIndicator">'
			                 . sprintf($GLOBALS['LANG']->getLL('pageIndicator'), $pageNumberInput, $totalPages)
			                 . '</span>';

			if ($totalItems > ($firstElementNumber + $iLimit)) {
				$lastElementNumber = $firstElementNumber + $iLimit;
			} else {
				$lastElementNumber = $totalItems;
			}

			$rangeIndicator = '<span class="pageIndicator">'
			                  . sprintf($GLOBALS['LANG']->getLL('rangeIndicator'), $firstElementNumber + 1, $lastElementNumber) . ' / ' . $totalItems
			                  . '</span>';
			// nb per page, filter and reload
			$reload = '<input type="text" name="nbPerPage" id="nbPerPage" size="5" value="' . $this->nbElementsPerPage . '"/> / page ';

			if ($getFiltersCat !== null) {
				$reload .= '<a href="#"  onClick="jumpToUrl(\'' . $listURLOrig . '&nbPerPage=\'+document.getElementById(\'nbPerPage\').value+\'&filtersCat=' . $getFiltersCat . '\');">';
			} else {
				$reload .= '<a href="#"  onClick="jumpToUrl(\'' . $listURLOrig . '&nbPerPage=\'+document.getElementById(\'nbPerPage\').value);">';
			}
			$reload .= '<img width="16" height="16" title="" alt="" src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . '../typo3conf/ext/additional_reports/res/refresh_n.gif"></a>';

			if ($this->filtersCat != '') {
				$reload .= '<span class="bar">&nbsp;</span>';
				$reload .= $GLOBALS['LANG']->getLL('filterByCat') . '&nbsp;<select name="filtersCat" id="filtersCat">' . $this->filtersCat . '</select>';
				$reload .= '<a href="#"  onClick="jumpToUrl(\'' . $listURLOrig . '&nbPerPage=\'+document.getElementById(\'nbPerPage\').value+\'&filtersCat=\'+document.getElementById(\'filtersCat\').value);">';
				$reload .= '&nbsp;<img width="16" height="16" title="" alt="" src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . '../typo3conf/ext/additional_reports/res/refresh_n.gif"></a>';
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

	public function getRealUrlErrors() {
		$cmd = t3lib_div::_GP('cmd');
		if ($cmd === 'deleteAll') {
			$GLOBALS['TYPO3_DB']->exec_DELETEquery(
				'tx_realurl_errorlog',
				''
			);
		}
		if ($cmd === 'delete') {
			$delete = t3lib_div::_GP('delete');
			$GLOBALS['TYPO3_DB']->exec_DELETEquery(
				'tx_realurl_errorlog',
				'url_hash=' . mysql_real_escape_string($delete)
			);
		}

		// query
		$query = array();
		$query['SELECT'] = 'url_hash,url,error,last_referer,counter,cr_date,tstamp';
		$query['FROM'] = 'tx_realurl_errorlog';
		$query['WHERE'] = '';
		$query['GROUPBY'] = '';
		$query['ORDERBY'] = 'counter DESC';
		$query['LIMIT'] = '';

		// items
		$items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			$query['SELECT'],
			$query['FROM'],
			$query['WHERE'],
			$query['GROUPBY'],
			$query['ORDERBY'],
			$query['LIMIT']
		);

		// Page browser
		$pointer = t3lib_div::_GP('pointer');
		$limit = ($pointer !== null) ? $pointer . ',' . $this->nbElementsPerPage : '0,' . $this->nbElementsPerPage;
		$current = ($pointer !== null) ? intval($pointer) : 0;
		$pageBrowser = self::pluginsRenderListNavigation(count($items), $this->nbElementsPerPage, $current);
		$itemsBrowser = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			$query['SELECT'],
			$query['FROM'],
			$query['WHERE'],
			$query['GROUPBY'],
			$query['ORDERBY'],
			$limit
		);


		$content = '';

		if (count($itemsBrowser) > 0) {

			$content .= $pageBrowser;

			$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
			$content .= '<tr class="t3-row-header"><td colspan="10">';
			$content .= $GLOBALS['LANG']->getLL('realurlerrors_description');
			$content .= '</td></tr>';
			$content .= '<tr class="c-headLine">';
			$content .= '<td class="cell">&nbsp;</td>';
			$content .= '<td class="cell">URL</td>';
			$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('error') . '</td>';
			$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('counter') . '</td>';
			$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('crdate') . '</td>';
			$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('tstamp') . '</td>';
			$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('last_referer') . '</td>';
			$content .= '</tr>';

			foreach ($itemsBrowser as $itemKey => $itemValue) {
				$content .= '<tr class="db_list_normal">';
				$actionURL = $this->baseURL . '&cmd=delete&delete=' . $itemValue['url_hash'];
				$action = '<a href="' . $actionURL . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/garbage.gif"/></a>';
				$content .= '<td class="cell">' . $action . '</td>';
				$content .= '<td class="cell">' . $itemValue['url'] . '</td>';
				$content .= '<td class="cell">' . $itemValue['error'] . '</td>';
				$content .= '<td class="cell">' . $itemValue['counter'] . '</td>';
				$content .= '<td class="cell">' . date('d/m/Y H:i:s', $itemValue['cr_date']) . '</td>';
				$content .= '<td class="cell">' . date('d/m/Y H:i:s', $itemValue['tstamp']) . '</td>';
				$content .= '<td class="cell">' . $itemValue['last_referer'] . '</td>';
				$content .= '</tr>';
			}

			$content .= '</table>';

		}

		return $content;
	}

	public function getLogErrors() {
		/*$cmd = t3lib_div::_GP('cmd');
		if ($cmd === 'delete') {
			$delete = t3lib_div::_GP('delete');
			$GLOBALS['TYPO3_DB']->exec_DELETEquery(
				'tx_realurl_errorlog',
				'url_hash=' . mysql_real_escape_string($delete)
			);
		}*/

		// query
		$query = array();
		$query['SELECT'] = 'COUNT(*) AS "nb",details,tstamp';
		$query['FROM'] = 'sys_log';
		$query['WHERE'] = 'error=1 OR error=2';
		$query['GROUPBY'] = 'details,tstamp';
		$query['ORDERBY'] = 'nb DESC,tstamp DESC';
		$query['LIMIT'] = '';

		$orderby = t3lib_div::_GP('orderby');
		if ($orderby !== NULL) {
			$query['ORDERBY'] = $orderby;
		}

		// items
		$items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			$query['SELECT'],
			$query['FROM'],
			$query['WHERE'],
			$query['GROUPBY'],
			$query['ORDERBY'],
			$query['LIMIT']
		);

		// Page browser
		$pointer = t3lib_div::_GP('pointer');
		$limit = ($pointer !== null) ? $pointer . ',' . $this->nbElementsPerPage : '0,' . $this->nbElementsPerPage;
		$current = ($pointer !== null) ? intval($pointer) : 0;
		$pageBrowser = self::pluginsRenderListNavigation(count($items), $this->nbElementsPerPage, $current);
		$itemsBrowser = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			$query['SELECT'],
			$query['FROM'],
			$query['WHERE'],
			$query['GROUPBY'],
			$query['ORDERBY'],
			$limit
		);


		$content = '';

		if (count($itemsBrowser) > 0) {

			$content .= $pageBrowser;

			$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
			$content .= '<tr class="t3-row-header"><td colspan="10">';
			$content .= $GLOBALS['LANG']->getLL('logerrors_description');
			$content .= '</td></tr>';
			$content .= '<tr class="c-headLine">';
			$content .= '<td class="cell" width="90">' . $GLOBALS['LANG']->getLL('counter');
			$content .= '&nbsp;&nbsp;<a href="' . $this->baseURL . '&orderby=nb%20DESC,tstamp%20DESC"><img width="7" height="4" alt="" src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/reddown.gif"></a>';
			$content .= '&nbsp;&nbsp;<a href="' . $this->baseURL . '&orderby=nb%20ASC,tstamp%20DESC"><img width="7" height="4" alt="" src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/redup.gif"></a>';
			$content .= '</td>';
			$content .= '<td class="cell" width="150">' . $GLOBALS['LANG']->getLL('tstamp');
			$content .= '&nbsp;&nbsp;<a href="' . $this->baseURL . '&orderby=tstamp%20DESC,nb%20DESC"><img width="7" height="4" alt="" src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/reddown.gif"></a>';
			$content .= '&nbsp;&nbsp;<a href="' . $this->baseURL . '&orderby=tstamp%20ASC,nb%20DESC"><img width="7" height="4" alt="" src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/redup.gif"></a>';
			$content .= '</td>';
			$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('error') . '</td>';
			$content .= '</tr>';

			foreach ($itemsBrowser as $itemKey => $itemValue) {
				$content .= '<tr class="db_list_normal">';
				//$actionURL = $this->baseURL . '&cmd=delete&delete=' . $itemValue['url_hash'];
				//$action = '<a href="' . $actionURL . '"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/garbage.gif"/></a>';
				//$content .= '<td class="cell">' . $action . '</td>';
				$content .= '<td class="cell">' . $itemValue['nb'] . '</td>';
				$content .= '<td class="cell">' . date('d/m/Y H:i:s', $itemValue['tstamp']) . '</td>';
				$content .= '<td class="cell">' . htmlentities($itemValue['details']) . '</td>';
				$content .= '</tr>';
			}

			$content .= '</table>';

		}

		return $content;
	}

	public function displayWebsitesConf() {
		$content = '';
		$items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'uid, title',
			'pages',
			'is_siteroot = 1 AND deleted = 0 AND hidden = 0 AND pid != -1',
			'', '', '',
			'uid'
		);
		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="7">';
		$content .= $GLOBALS['LANG']->getLL('websitesconf_description');
		$content .= '</td></tr>';
		$content .= '<tr class="c-headLine">';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('pid') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('pagetitle') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('domains') . '</td>';
		$content .= '<td class="cell">sys_template</td>';
		$content .= '<td class="cell">config.baseURL</td>';
		$content .= '</tr>';
		if (!empty($items)) {
			foreach ($items as $itemKey => $itemValue) {
				$domainRecords = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'uid, pid, domainName',
					'sys_domain',
					'pid IN(' . $itemValue['uid'] . ') AND redirectTo=\'\' AND hidden=0',
					'',
					'sorting'
				);
				$content .= '<tr class="db_list_normal">';
				$content .= '<td class="cell">' . $itemValue['uid'] . '</td>';
				$content .= '<td class="cell"><img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/i/pages.gif"/> ' . $itemValue['title'] . '</td>';
				$content .= '<td class="cell">';
				foreach ($domainRecords as $domain) {
					$content .= '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/i/domain.gif"/> ' . $domain['domainName'] . '<br/>';
				}
				$content .= '</td>';
				$templates = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'uid,title,root',
					'sys_template',
					'pid IN(' . $itemValue['uid'] . ') AND deleted=0 AND hidden=0',
					'',
					'sorting'
				);
				$content .= '<td class="cell">';
				foreach ($templates as $template) {
					$content .= '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/t3skin/icons/gfx/i/template.gif"/> ' . $template['title'] . ' [uid=' . $template['uid'] . ',root=' . $template['root'] . ']<br/>';
				}
				$content .= '</td>';
				$tmpl = t3lib_div::makeInstance("t3lib_tsparser_ext");
				$tmpl->tt_track = 0;
				$tmpl->init();
				$sys_page = t3lib_div::makeInstance("t3lib_pageSelect");
				$rootLine = $sys_page->getRootLine($itemValue['uid']);
				$tmpl->runThroughTemplates($rootLine, 0);
				$tmpl->generateConfig();
				$content .= '<td class="cell">' . $tmpl->setup['config.']['baseURL'] . '</td>';
				$content .= '</tr>';
			}
		}
		$content .= '</table>';
		return $content;
	}

}

?>