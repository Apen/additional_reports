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
 * Utility class
 *
 * @author        CERDAN Yohann <cerdanyohann@yahoo.fr>
 * @package        TYPO3
 */

class tx_additionalreports_util
{
	public function getSqlUpdateStatements() {
		$tblFileContent = t3lib_div::getUrl(PATH_t3lib . 'stddb/tables.sql');
		foreach ($GLOBALS['TYPO3_LOADED_EXT'] as $loadedExtConf) {
			if (is_array($loadedExtConf) && $loadedExtConf['ext_tables.sql']) {
				$tblFileContent .= chr(10) . chr(10) . chr(10) . chr(10) . t3lib_div::getUrl($loadedExtConf['ext_tables.sql']);
			}
		}
		if ($tblFileContent) {
			if (t3lib_div::int_from_ver(TYPO3_version) <= 4005000) {
				require_once(PATH_t3lib . "class.t3lib_install.php");
				$instObj = new t3lib_install;
				$fileContent = implode(chr(10), $instObj->getStatementArray($tblFileContent, 1, '^CREATE TABLE '));
				if (method_exists('t3lib_install', 'getFieldDefinitions_fileContent') === TRUE) {
					$fdFile = $instObj->getFieldDefinitions_fileContent($fileContent);
				} else {
					$fdFile = $instObj->getFieldDefinitions_sqlContent($fileContent);
				}
				$fdDb = $instObj->getFieldDefinitions_database(TYPO3_db);
				$diff = $instObj->getDatabaseExtra($fdFile, $fdDb);
				$updateStatements = $instObj->getUpdateSuggestions($diff);
				$diff = $instObj->getDatabaseExtra($fdDb, $fdFile);
				$removeStatements = $instObj->getUpdateSuggestions($diff, 'remove');
			} else {
				// just for the 4.5 version and 4.6.0 ...
				if (t3lib_div::int_from_ver(TYPO3_version) <= 4006000) {
					$instObj = new t3lib_install;
					$fileContent = implode(chr(10), $instObj->getStatementArray($tblFileContent, 1, '^CREATE TABLE '));
					$fdFile = $instObj->getFieldDefinitions_fileContent($fileContent);
					$fdDb = $instObj->getFieldDefinitions_database(TYPO3_db);
					$diff = $instObj->getDatabaseExtra($fdFile, $fdDb);
					$updateStatements = $instObj->getUpdateSuggestions($diff);
					$diff = $instObj->getDatabaseExtra($fdDb, $fdFile);
					$removeStatements = $instObj->getUpdateSuggestions($diff, 'remove');
				} else {
					$instObj = new t3lib_install_Sql;
					$fileContent = implode(chr(10), $instObj->getStatementArray($tblFileContent, 1, '^CREATE TABLE '));
					$fdFile = $instObj->getFieldDefinitions_fileContent($fileContent);
					$fdDb = $instObj->getFieldDefinitions_database(TYPO3_db);
					$diff = $instObj->getDatabaseExtra($fdFile, $fdDb);
					$updateStatements = $instObj->getUpdateSuggestions($diff);
					$diff = $instObj->getDatabaseExtra($fdDb, $fdFile);
					$removeStatements = $instObj->getUpdateSuggestions($diff, 'remove');
				}
			}
			return array('update' => $updateStatements, 'remove' => $removeStatements);
		} else {
			return array('update' => NULL, 'remove' => NULL);
		}
	}

	public function getTreeList($id, $depth, $begin = 0, $perms_clause) {
		$depth = intval($depth);
		$begin = intval($begin);
		$id = intval($id);
		if ($begin == 0) {
			$theList = $id;
		} else {
			$theList = '';
		}
		if ($id && $depth > 0) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'uid',
				'pages',
					'pid=' . $id . ' ' . t3lib_BEfunc::deleteClause('pages') . ' AND ' . $perms_clause
			);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				if ($begin <= 0) {
					$theList .= ',' . $row['uid'];
				}
				if ($depth > 1) {
					$theList .= self::getTreeList($row['uid'], $depth - 1, $begin - 1, $perms_clause);
				}
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
		}
		return $theList;
	}

	public function getCountPagesUids($listOfUids, $where = '1=1') {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'pages', 'uid IN (' . $listOfUids . ') AND ' . $where);
		$count = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $count;
	}

	public function isUsedInTV($uid, $pid) {
		$apiObj = t3lib_div::makeInstance('tx_templavoila_api', 'pages');
		$rootElementRecord = t3lib_BEfunc::getRecordWSOL('pages', $pid, '*');
		$contentTreeData = $apiObj->getContentTree('pages', $rootElementRecord);
		$usedUids = array_keys($contentTreeData['contentElementUsage']);
		if (t3lib_div::inList(implode(',', $usedUids), $uid)) {
			return TRUE;
		} else {
			return FALSE;
		}
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
		if (t3lib_div::int_from_ver(TYPO3_version) <= 4005000) {
			require_once($GLOBALS['BACK_PATH'] . 'mod/tools/em/class.em_index.php');
			$em = t3lib_div::makeInstance('SC_mod_tools_em_index');
			$em->init();
			$cat = $em->defaultCategories;
			$em->getInstExtList($path, $items, $cat, 'L');
		} else {
			require_once($GLOBALS['BACK_PATH'] . 'sysext/em/classes/extensions/class.tx_em_extensions_list.php');
			require_once($GLOBALS['BACK_PATH'] . 'sysext/em/classes/extensions/class.tx_em_extensions_details.php');
			require_once($GLOBALS['BACK_PATH'] . 'sysext/em/classes/tools/class.tx_em_tools_xmlhandler.php');
			$em = t3lib_div::makeInstance('tx_em_Extensions_List');
			$cat = tx_em_Tools::getDefaultCategory();
			$em->getInstExtList($path, $items, $cat, 'L');
		}
		return $em;
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
		} else {
			return NULL;
		}
	}

	public function getExtAffectedFiles($em, $extKey, $extInfo, &$affectedFiles, &$lastVersion) {
		if (t3lib_div::int_from_ver(TYPO3_version) <= 4005000) {
			$currentMd5Array = $em->serverExtensionMD5Array($extKey, $extInfo);
			$affectedFiles = $em->findMD5ArrayDiff($currentMd5Array, unserialize($extInfo['EM_CONF']['_md5_values_when_last_written']));
			$lastVersion = tx_additionalreports_util::checkMAJ($em, $extKey);
		} else {
			$emDetails = t3lib_div::makeInstance('tx_em_Extensions_Details');
			$emTools = t3lib_div::makeInstance('tx_em_Tools_XmlHandler');
			$currentMd5Array = $emDetails->serverExtensionMD5Array($extKey, $extInfo);
			$affectedFiles = tx_em_Tools::findMD5ArrayDiff($currentMd5Array, unserialize($extInfo['EM_CONF']['_md5_values_when_last_written']));
			$lastVersion = tx_additionalreports_util::checkMAJ($emTools, $extKey);
		}
	}

	public function typePath($type) {
		if ($type === 'S') {
			return PATH_typo3 . 'sysext/';
		} elseif ($type === 'G') {
			return PATH_typo3 . 'ext/';
		} elseif ($type === 'L') {
			return PATH_typo3conf . 'ext/';
		}
		return PATH_typo3conf . 'ext/';
	}

	public function getExtPath($extKey, $type = 'L', $returnWithoutExtKey = FALSE) {
		$typePath = tx_additionalreports_util::typePath($type);
		if ($typePath) {
			$path = $typePath . ($returnWithoutExtKey ? '' : $extKey . '/');
			return $path;
		} else {
			return '';
		}
	}

	public function getExtSqlUpdateStatements($em, $extKey, $extInfo, &$fdFile, &$updateStatements) {
		if (t3lib_div::int_from_ver(TYPO3_version) <= 4005000) {
			$instObj = new t3lib_install;
			if (is_array($extInfo['files']) && in_array('ext_tables.sql', $extInfo['files'])) {
				$fileContent = t3lib_div::getUrl($em->getExtPath($extKey, $extInfo['type']) . 'ext_tables.sql');
				if (method_exists('t3lib_install', 'getFieldDefinitions_fileContent') === TRUE) {
					$fdFile = $instObj->getFieldDefinitions_fileContent($fileContent);
				} else {
					$fdFile = $instObj->getFieldDefinitions_sqlContent($fileContent);
				}
				$fdDb = $instObj->getFieldDefinitions_database(TYPO3_db);
				$diff = $instObj->getDatabaseExtra($fdFile, $fdDb);
				$updateStatements = $instObj->getUpdateSuggestions($diff);
			}
		} else {
			// just for the 4.5 version...
			if (t3lib_div::int_from_ver(TYPO3_version) <= 4006000) {
				$instObj = new t3lib_install;
				if (is_array($extInfo['files']) && in_array('ext_tables.sql', $extInfo['files'])) {
					$fileContent = t3lib_div::getUrl(tx_em_Tools::getExtPath($extKey, $extInfo['type']) . 'ext_tables.sql');
					$fdFile = $instObj->getFieldDefinitions_fileContent($fileContent);
					$fdDb = $instObj->getFieldDefinitions_database(TYPO3_db);
					$diff = $instObj->getDatabaseExtra($fdFile, $fdDb);
					$updateStatements = $instObj->getUpdateSuggestions($diff);
				}
			} else {
				$instObj = new t3lib_install_Sql;
				if (is_array($extInfo['files']) && in_array('ext_tables.sql', $extInfo['files'])) {
					$fileContent = t3lib_div::getUrl(tx_em_Tools::getExtPath($extKey, $extInfo['type']) . 'ext_tables.sql');
					$fdFile = $instObj->getFieldDefinitions_fileContent($fileContent);
					$fdDb = $instObj->getFieldDefinitions_database(TYPO3_db);
					$diff = $instObj->getDatabaseExtra($fdFile, $fdDb);
					$updateStatements = $instObj->getUpdateSuggestions($diff);
				}
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
			$versionRange = tx_additionalreports_util::splitVersionRange($depV);
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

	public static function getExtensionVersion($key) {
		$EM_CONF = array();
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

}

?>