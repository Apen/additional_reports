<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 CERDAN Yohann <cerdanyohann@yahoo.fr>
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
 * @author         CERDAN Yohann <cerdanyohann@yahoo.fr>
 * @package        TYPO3
 */
class tx_additionalreports_util
{
	/**
	 * Define all the reports
	 *
	 * @return array
	 */
	public function getReportsList() {
		$reports = array(
			'eid', 'clikeys', 'plugins', 'xclass', 'hooks', 'status', 'ajax', 'extensions', 'logerrors', 'websitesconf',
			'dbcheck', 'realurlerrors'
		);

		if (self::intFromVer(TYPO3_version) >= 4005000) {
			$reports[] = 'extdirect';
		}

		return $reports;
	}

	/**
	 * Define all the sub modules
	 *
	 * @return array
	 */
	public function getSubModules() {
		return array(
			'displayAjax'          => $GLOBALS['LANG']->getLL('ajax_title'),
			'displayEid'           => $GLOBALS['LANG']->getLL('eid_title'),
			'displayCliKeys'       => $GLOBALS['LANG']->getLL('clikeys_title'),
			'displayPlugins'       => $GLOBALS['LANG']->getLL('plugins_title'),
			'displayXclass'        => $GLOBALS['LANG']->getLL('xclass_title'),
			'displayHooks'         => $GLOBALS['LANG']->getLL('hooks_title'),
			'displayStatus'        => $GLOBALS['LANG']->getLL('status_title'),
			'displayExtensions'    => $GLOBALS['LANG']->getLL('extensions_title'),
			'displayRealUrlErrors' => $GLOBALS['LANG']->getLL('realurlerrors_title'),
			'displayLogErrors'     => $GLOBALS['LANG']->getLL('logerrors_title'),
			'displayWebsitesConf'  => $GLOBALS['LANG']->getLL('websitesconf_title'),
			'displayDbCheck'       => $GLOBALS['LANG']->getLL('dbcheck_title'),
		);
	}

	/**
	 * Get the update statement of the database
	 *
	 * @return array
	 */
	public function getSqlUpdateStatements() {
		$tblFileContent = t3lib_div::getUrl(PATH_t3lib . 'stddb/tables.sql');
		foreach ($GLOBALS['TYPO3_LOADED_EXT'] as $loadedExtConf) {
			if (is_array($loadedExtConf) && $loadedExtConf['ext_tables.sql']) {
				$tblFileContent .= chr(10) . chr(10) . chr(10) . chr(10) . t3lib_div::getUrl($loadedExtConf['ext_tables.sql']);
			}
		}
		// include cache tables form 4.6>=
		if (self::intFromVer(TYPO3_version) >= 4006000) {
			$tblFileContent .= t3lib_cache::getDatabaseTableDefinitions();
		}
		if ($tblFileContent) {
			if (self::intFromVer(TYPO3_version) <= 4005000) {
				require_once(PATH_t3lib . 'class.t3lib_install.php');
				$instObj     = new t3lib_install;
				$fileContent = implode(chr(10), $instObj->getStatementArray($tblFileContent, 1, '^CREATE TABLE '));
				if (method_exists('t3lib_install', 'getFieldDefinitions_fileContent') === TRUE) {
					$fdFile = $instObj->getFieldDefinitions_fileContent($fileContent);
				} else {
					$fdFile = $instObj->getFieldDefinitions_sqlContent($fileContent);
				}
				$fdDb             = $instObj->getFieldDefinitions_database(TYPO3_db);
				$diff             = $instObj->getDatabaseExtra($fdFile, $fdDb);
				$updateStatements = $instObj->getUpdateSuggestions($diff);
				$diff             = $instObj->getDatabaseExtra($fdDb, $fdFile);
				$removeStatements = $instObj->getUpdateSuggestions($diff, 'remove');
			} else {
				// just for the 4.5 version and 4.6.0 ...
				if (self::intFromVer(TYPO3_version) <= 4006000) {
					$instObj          = new t3lib_install;
					$fileContent      = implode(chr(10), $instObj->getStatementArray($tblFileContent, 1, '^CREATE TABLE '));
					$fdFile           = $instObj->getFieldDefinitions_fileContent($fileContent);
					$fdDb             = $instObj->getFieldDefinitions_database(TYPO3_db);
					$diff             = $instObj->getDatabaseExtra($fdFile, $fdDb);
					$updateStatements = $instObj->getUpdateSuggestions($diff);
					$diff             = $instObj->getDatabaseExtra($fdDb, $fdFile);
					$removeStatements = $instObj->getUpdateSuggestions($diff, 'remove');
				} else {
					$instObj          = new t3lib_install_Sql;
					$fileContent      = implode(chr(10), $instObj->getStatementArray($tblFileContent, 1, '^CREATE TABLE '));
					$fdFile           = $instObj->getFieldDefinitions_fileContent($fileContent);
					$fdDb             = $instObj->getFieldDefinitions_database(TYPO3_db);
					$diff             = $instObj->getDatabaseExtra($fdFile, $fdDb);
					$updateStatements = $instObj->getUpdateSuggestions($diff);
					$diff             = $instObj->getDatabaseExtra($fdDb, $fdFile);
					$removeStatements = $instObj->getUpdateSuggestions($diff, 'remove');
				}
			}
			return array(
				'update' => $updateStatements,
				'remove' => $removeStatements
			);
		} else {
			return array(
				'update' => NULL,
				'remove' => NULL
			);
		}
	}

	/**
	 * Generates a list of Page-uid's from $id
	 *
	 * @param  int      $id
	 * @param  int      $depth
	 * @param  int      $begin
	 * @param  string   $permsClause
	 * @return string
	 */
	public function getTreeList($id, $depth, $begin = 0, $permsClause = '1=1') {
		$depth = intval($depth);
		$begin = intval($begin);
		$id    = intval($id);
		if ($begin == 0) {
			$theList = $id;
		} else {
			$theList = '';
		}
		if ($id && $depth > 0) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'uid',
				'pages',
				'pid=' . $id . ' ' . t3lib_BEfunc::deleteClause('pages') . ' AND ' . $permsClause
			);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				if ($begin <= 0) {
					$theList .= ',' . $row['uid'];
				}
				if ($depth > 1) {
					$theList .= self::getTreeList($row['uid'], $depth - 1, $begin - 1, $permsClause);
				}
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
		}
		return $theList;
	}

	/**
	 * Count page uids in a list given (validating the condition)
	 *
	 * @param string $listOfUids
	 * @param string $where
	 * @return int
	 */
	public function getCountPagesUids($listOfUids, $where = '1=1') {
		$res   = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'pages', 'uid IN (' . $listOfUids . ') AND ' . $where);
		$count = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $count;
	}

	/**
	 * Check if a content is used by TemplaVoila
	 *
	 * @param int $uid
	 * @param int $pid
	 * @return bool
	 */
	public static function isUsedInTv($uid, $pid) {
		$apiObj            = t3lib_div::makeInstance('tx_templavoila_api', 'pages');
		$rootElementRecord = t3lib_BEfunc::getRecordWSOL('pages', $pid, '*');
		$contentTreeData   = $apiObj->getContentTree('pages', $rootElementRecord);
		$usedUids          = array_keys($contentTreeData['contentElementUsage']);
		if (t3lib_div::inList(implode(',', $usedUids), $uid)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Returns an integer from a three part version number, eg '4.12.3' -> 4012003
	 *
	 * @param    string    $verNumberStr  number on format x.x.x
	 * @return   integer   Integer version of version number (where each part can count to 999)
	 */
	public function intFromVer($verNumberStr) {
		$verParts = explode('.', $verNumberStr);
		return intval(
			(int)$verParts[0] . str_pad((int)$verParts[1], 3, '0', STR_PAD_LEFT) . str_pad(
				(int)$verParts[2], 3, '0', STR_PAD_LEFT
			)
		);
	}

	/**
	 * Splits a version range into an array.
	 *
	 * If a single version number is given, it is considered a minimum value.
	 * If a dash is found, the numbers left and right are considered as minimum and maximum. Empty values are allowed.
	 *
	 * @param    string        $ver A string with a version range.
	 * @return   array
	 */
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

	/**
	 * Get the extension list
	 *
	 * @param string $path    path of the dir extension
	 * @param array  &$items  fill this array with all the extension
	 * @return object this is an EM object to manipulate the extension manager
	 */
	public static function getExtList($path, &$items) {
		if (self::intFromVer(TYPO3_version) <= 4005000) {
			require_once($GLOBALS['BACK_PATH'] . 'mod/tools/em/class.em_index.php');
			$em = t3lib_div::makeInstance('SC_mod_tools_em_index');
			$em->init();
			$cat = $em->defaultCategories;
			$em->getInstExtList($path, $items, $cat, 'L');
		} else {
			require_once($GLOBALS['BACK_PATH'] . 'sysext/em/classes/extensions/class.tx_em_extensions_list.php');
			require_once($GLOBALS['BACK_PATH'] . 'sysext/em/classes/extensions/class.tx_em_extensions_details.php');
			require_once($GLOBALS['BACK_PATH'] . 'sysext/em/classes/tools/class.tx_em_tools_xmlhandler.php');
			$em  = t3lib_div::makeInstance('tx_em_Extensions_List');
			$cat = tx_em_Tools::getDefaultCategory();
			$em->getInstExtList($path, $items, $cat, 'L');
		}
		return $em;
	}

	/**
	 * Return the last version number of an extension
	 *
	 * @param object $em
	 * @param string $name
	 * @return string
	 */
	public function checkUpdate($em, $name) {
		if (self::intFromVer(TYPO3_version) <= 4005000) {
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

	/**
	 * Check files that are modified directly without xclass or hook
	 *
	 * @param object       $em
	 * @param string       $extKey
	 * @param array        $extInfo
	 * @param array        &$affectedFiles
	 * @param array        &$lastVersion
	 */
	public static function getExtAffectedFiles($em, $extKey, $extInfo, &$affectedFiles, &$lastVersion) {
		if (self::intFromVer(TYPO3_version) <= 4005000) {
			$currentMd5Array = $em->serverExtensionMD5Array($extKey, $extInfo);
			$affectedFiles   = $em->findMD5ArrayDiff(
				$currentMd5Array, unserialize($extInfo['EM_CONF']['_md5_values_when_last_written'])
			);
			$lastVersion     = self::checkUpdate($em, $extKey);
		} else {
			$emDetails       = t3lib_div::makeInstance('tx_em_Extensions_Details');
			$emTools         = t3lib_div::makeInstance('tx_em_Tools_XmlHandler');
			$currentMd5Array = $emDetails->serverExtensionMD5Array($extKey, $extInfo);
			$affectedFiles   = tx_em_Tools::findMD5ArrayDiff(
				$currentMd5Array,
				unserialize($extInfo['EM_CONF']['_md5_values_when_last_written'])
			);
			$lastVersion     = self::checkUpdate($emTools, $extKey);
		}
	}

	/**
	 * Get the extension path for a given type
	 *
	 * @param string $type
	 * @return string
	 */
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

	/**
	 * Get the HTTP icon path of an extension
	 *
	 * @param string $extKey
	 * @return string
	 */
	public static function getExtIcon($extKey) {
		$extType = self::getExtensionType($extKey);
		return t3lib_div::getIndpEnv('TYPO3_SITE_URL') . $extType['siteRelPath'] . 'ext_icon.gif';
	}

	/**
	 * Get the icon path of zoom icon
	 *
	 * @return string
	 */
	public static function getIconZoom() {
		$typo3Dir = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR');
		return '<img src="' . $typo3Dir . 'sysext/t3skin/icons/gfx/zoom.gif"/>';
	}

	/**
	 * Get the icon path of zoom icon
	 *
	 * @return string
	 */
	public static function getIconDomain() {
		$typo3Dir = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR');
		return '<img src="' . $typo3Dir . 'sysext/t3skin/icons/gfx/i/domain.gif"/>';
	}

	/**
	 * Get the icon path of web page icon
	 *
	 * @return string
	 */
	public static function getIconWebPage() {
		$typo3Dir = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR');
		return '<img src="' . $typo3Dir . 'sysext/t3skin/icons/module_web_layout.gif"/>';
	}

	/**
	 * Get the icon path of web list icon
	 *
	 * @return string
	 */
	public static function getIconWebList() {
		$typo3Dir = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR');
		return '<img src="' . $typo3Dir . 'sysext/t3skin/icons/module_web_list.gif"/>';
	}

	/**
	 * Get the icon path of page icon
	 *
	 * @param boolean $hidden
	 * @return string
	 */
	public static function getIconPage($hidden = FALSE) {
		$typo3Dir = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR');
		if ($hidden === TRUE) {
			return '<img src="' . $typo3Dir . 'sysext/t3skin/icons/gfx/i/pages__h.gif"/>';
		} else {
			return '<img src="' . $typo3Dir . 'sysext/t3skin/icons/gfx/i/pages.gif"/>';
		}
	}

	/**
	 * Get the icon path of content icon
	 *
	 * @param boolean $hidden
	 * @return string
	 */
	public static function getIconContent($hidden = FALSE) {
		$typo3Dir = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR');
		if ($hidden === TRUE) {
			return '<img src="' . $typo3Dir . 'sysext/t3skin/icons/gfx/i/tt_content__h.gif"/>';
		} else {
			return '<img src="' . $typo3Dir . 'sysext/t3skin/icons/gfx/i/tt_content.gif"/>';
		}
	}

	/**
	 * Get the type and the path of an extension
	 *
	 * @param string $extKey
	 * @return array
	 */
	public function getExtensionType($extKey) {
		if (@is_dir(PATH_typo3conf . 'ext/' . $extKey . '/')) {
			return array(
				'type'         => 'L',
				'siteRelPath'  => 'typo3conf/ext/' . $extKey . '/',
				'typo3RelPath' => '../typo3conf/ext/' . $extKey . '/'
			);
		} elseif (@is_dir(PATH_typo3 . 'ext/' . $extKey . '/')) {
			return array(
				'type'         => 'G',
				'siteRelPath'  => TYPO3_mainDir . 'ext/' . $extKey . '/',
				'typo3RelPath' => 'ext/' . $extKey . '/'
			);
		} elseif (@is_dir(PATH_typo3 . 'sysext/' . $extKey . '/')) {
			return array(
				'type'         => 'S',
				'siteRelPath'  => TYPO3_mainDir . 'sysext/' . $extKey . '/',
				'typo3RelPath' => 'sysext/' . $extKey . '/'
			);
		}
	}

	/**
	 * Get rootline by page uid
	 *
	 * @param int $pageUid
	 * @return mixed
	 */
	public static function getRootLine($pageUid) {
		require_once(PATH_t3lib . 'class.t3lib_page.php');
		$sysPage = t3lib_div::makeInstance('t3lib_pageSelect');
		return $sysPage->getRootLine($pageUid);
	}

	/**
	 * Get principal domain by page uid
	 *
	 * @param int $pageUid
	 * @return mixed
	 */
	public static function getDomain($pageUid) {
		$domain = t3lib_BEfunc::firstDomainRecord(self::getRootLine($pageUid));
		if ($domain === NULL) {
			$domain = t3lib_div::getIndpEnv('TYPO3_HOST_ONLY');
		}
		return $domain;
	}

	/**
	 * Get the absolute path of an extension
	 *
	 * @param string $extKey
	 * @param string $type
	 * @param bool   $returnWithoutExtKey
	 * @return string
	 */
	public static function getExtPath($extKey, $type = 'L', $returnWithoutExtKey = FALSE) {
		$typePath = self::typePath($type);
		if ($typePath) {
			$path = $typePath . ($returnWithoutExtKey ? '' : $extKey . '/');
			return $path;
		} else {
			return '';
		}
	}

	/**
	 * Get the sql statements of an extension define in ext_tables.sql
	 *
	 * @param object $em
	 * @param string $extKey
	 * @param array  $extInfo
	 * @param array  &$fdFile
	 * @param array  &$updateStatements
	 */
	public static function getExtSqlUpdateStatements($em, $extKey, $extInfo, &$fdFile, &$updateStatements) {
		if (self::intFromVer(TYPO3_version) <= 4005000) {
			$instObj = new t3lib_install;
			if (is_array($extInfo['files']) && in_array('ext_tables.sql', $extInfo['files'])) {
				$fileContent = t3lib_div::getUrl($em->getExtPath($extKey, $extInfo['type']) . 'ext_tables.sql');
				if (method_exists('t3lib_install', 'getFieldDefinitions_fileContent') === TRUE) {
					$fdFile = $instObj->getFieldDefinitions_fileContent($fileContent);
				} else {
					$fdFile = $instObj->getFieldDefinitions_sqlContent($fileContent);
				}
				$fdDb             = $instObj->getFieldDefinitions_database(TYPO3_db);
				$diff             = $instObj->getDatabaseExtra($fdFile, $fdDb);
				$updateStatements = $instObj->getUpdateSuggestions($diff);
			}
		} else {
			// just for the 4.5 version...
			if (self::intFromVer(TYPO3_version) <= 4006000) {
				$instObj = new t3lib_install;
				if (is_array($extInfo['files']) && in_array('ext_tables.sql', $extInfo['files'])) {
					$fileContent      = t3lib_div::getUrl(tx_em_Tools::getExtPath($extKey, $extInfo['type']) . 'ext_tables.sql');
					$fdFile           = $instObj->getFieldDefinitions_fileContent($fileContent);
					$fdDb             = $instObj->getFieldDefinitions_database(TYPO3_db);
					$diff             = $instObj->getDatabaseExtra($fdFile, $fdDb);
					$updateStatements = $instObj->getUpdateSuggestions($diff);
				}
			} else {
				$instObj = new t3lib_install_Sql;
				if (is_array($extInfo['files']) && in_array('ext_tables.sql', $extInfo['files'])) {
					$fileContent      = t3lib_div::getUrl(tx_em_Tools::getExtPath($extKey, $extInfo['type']) . 'ext_tables.sql');
					$fdFile           = $instObj->getFieldDefinitions_fileContent($fileContent);
					$fdDb             = $instObj->getFieldDefinitions_database(TYPO3_db);
					$diff             = $instObj->getDatabaseExtra($fdFile, $fdDb);
					$updateStatements = $instObj->getUpdateSuggestions($diff);
				}
			}
		}
	}

	/**
	 * Compare 2 versions of an extension
	 *
	 * @param string $depV
	 * @return string
	 */
	public static function versionCompare($depV) {
		$t3version = TYPO3_version;
		if (
			stripos($t3version, '-dev') ||
			stripos($t3version, '-alpha') ||
			stripos($t3version, '-beta') ||
			stripos($t3version, '-RC')
		) {
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
				$msg    = $GLOBALS['LANG']->getLL('nottested') . ' (' . $depV . ')';
			} else {
				$status = 1;
				$msg    = 'OK';
			}
		} else {
			$status = 3;
			$msg    = $GLOBALS['LANG']->getLL('unknown');
		}

		switch ($status) {
			case 0:
				$msg = '<span style="color:red;font-weight:bold;" title="' . $msg . '">KO</span>';
				break;
			case 1:
				$msg = '<span style="color:green;font-weight:bold;" title="' . $msg . '">OK</span>';
				break;
			case 2:
				$msg = '<span style="color:orange;font-weight:bold;" title="' . $msg . '">' . $GLOBALS['LANG']->getLL(
					'nottested'
				) . '</span>';
				break;
			case 3:
				$msg = '<span style="color:orange;font-weight:bold;" title="' . $msg . '">' . $GLOBALS['LANG']->getLL(
					'unknown'
				) . '</span>';
				break;
			default:
				$msg = '<span style="color:red;font-weight:bold;" title="' . $msg . '">KO</span>';
				break;
		}

		return $msg;
	}

	/**
	 * Print a debug of an array
	 *
	 * @param array $arrayIn
	 * @return string
	 */
	public static function viewArray($arrayIn) {
		if (is_array($arrayIn)) {
			$result = '<table class="debug" border="1" cellpadding="0" cellspacing="0" bgcolor="white" width="100%">';
			if (count($arrayIn) == 0) {
				$result .= '<tr><td><strong>EMPTY!</strong></td></tr>';
			} else {
				foreach ($arrayIn as $key => $val) {
					$result .= '<tr><td>' . htmlspecialchars((string)$key) . '</td><td class="debugvar">';
					if (is_array($val)) {
						$result .= self::viewArray($val);
					} elseif (is_object($val)) {
						$string = get_class($val);
						if (method_exists($val, '__toString')) {
							$string .= ': ' . (string)$val;
						}
						$result .= nl2br(htmlspecialchars($string)) . '<br />';
					} else {
						if (gettype($val) == 'object') {
							$string = 'Unknown object';
						} else {
							$string = (string)$val;
						}
						$result .= nl2br(htmlspecialchars($string)) . '<br />';
					}
					$result .= '</td></tr>';
				}
			}
			$result .= '</table>';
		} else {
			$result = '<table class="debug" border="0" cellpadding="0" cellspacing="0" bgcolor="white">';
			$result .= '<tr><td class="debugvar">' . nl2br(htmlspecialchars((string)$arrayIn)) . '</td></tr></table>';
		}
		return $result;
	}

	/**
	 * Return a link to the module list
	 *
	 * @param int  $uid
	 * @param bool $urlOnly
	 * @return string
	 */
	public static function goToModuleList($uid, $urlOnly = FALSE) {
		$url = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'db_list.php?id=' . $uid;
		if ($urlOnly === TRUE) {
			return $url;
		} else {
			return 'top.nextLoadModuleUrl=\'' . $url . '\';top.goToModule(\'web_list\');';
		}
	}

	/**
	 * Return a link to the module page
	 *
	 * @param int  $uid
	 * @param bool $urlOnly
	 * @return string
	 */
	public static function goToModulePage($uid, $urlOnly = FALSE) {
		$url = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'sysext/cms/layout/db_layout.php?id=' . $uid;
		if ($urlOnly === TRUE) {
			return $url;
		} else {
			return 'top.nextLoadModuleUrl=\'' . $url . '\';top.goToModule(\'web_layout\');';
		}
	}

	/**
	 * Return a link to the module page (with TV)
	 *
	 * @param int  $uid
	 * @param bool $urlOnly
	 * @return string
	 */
	public static function goToModulePageTv($uid, $urlOnly = FALSE) {
		$url = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . '../typo3conf/ext/templavoila/mod1/index.php?id=' . $uid;
		if ($urlOnly === TRUE) {
			return $url;
		} else {
			return 'top.nextLoadModuleUrl=\'' . $url . '\';top.goToModule(\'web_txtemplavoilaM1\');';
		}
	}

	/**
	 * Return a link to the module EM
	 *
	 * @param int  $extKey
	 * @return string
	 */
	public static function goToModuleEm($extKey) {
		return 'top.goToModule(\'tools_em\', 1, \'CMD[showExt]=' . $extKey . '&SET[singleDetails]=info\');';
	}

	/**
	 * Return a <a...>...</a> code
	 *
	 * @param array  $att
	 * @param string $content
	 * @return string
	 */
	public static function generateLink($att = array(), $content = '') {
		$attList = '';
		foreach ($att as $attKey => $attValue) {
			$attList .= ' ' . $attKey . '="' . $attValue . '"';
		}
		return '<a' . $attList . '>' . $content . '</a>';
	}

	/**
	 * Get the version of a given extension
	 *
	 * @param string $key
	 * @return string
	 * @throws InvalidArgumentException
	 */
	public static function getExtensionVersion($key) {
		$EM_CONF = array();
		if (!is_string($key) || empty($key)) {
			throw new InvalidArgumentException('Extension key must be a non-empty string.');
		}
		if (!t3lib_extMgm::isLoaded($key)) {
			return '';
		}

		// need for the next include
		$_EXTKEY = $key;
		include(t3lib_extMgm::extPath($key) . 'ext_emconf.php');

		return $EM_CONF[$key]['version'];
	}

	/**
	 * Get the name of the temp_CACHED files
	 *
	 * @return string
	 */
	public static function getCacheFilePrefix() {
		$extensionCacheBehaviour = intval($GLOBALS['TYPO3_CONF_VARS']['EXT']['extCache']);

		// Caching of extensions is disabled when install tool is used:
		if (defined('TYPO3_enterInstallScript') && TYPO3_enterInstallScript) {
			$extensionCacheBehaviour = 0;
		}

		$cacheFileSuffix = (TYPO3_MODE == 'FE' ? '_FE' : '');
		$cacheFilePrefix = 'temp_CACHED' . $cacheFileSuffix;

		if ($extensionCacheBehaviour == 1) {
			$cacheFilePrefix .= '_ps' . substr(t3lib_div::shortMD5(PATH_site . '|' . $GLOBALS['TYPO_VERSION']), 0, 4);
		}

		return $cacheFilePrefix;
	}

	/**
	 * Get informations about the mysql cache
	 *
	 * @return string HTML code
	 */
	public static function getMySqlCacheInformations() {
		$queryCache = '';

		$res = $GLOBALS['TYPO3_DB']->sql_query('SHOW VARIABLES LIKE "%query_cache%";');
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$queryCache .= $row['Variable_name'] . ' : ' . $row['Value'] . '<br />';
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		$res = $GLOBALS['TYPO3_DB']->sql_query('SHOW STATUS LIKE "%Qcache%";');
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$queryCache .= $row['Variable_name'] . ' : ' . $row['Value'] . '<br />';
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		return $queryCache;
	}

	/**
	 * Get informations about the mysql character_set
	 *
	 * @return string HTML code
	 */
	public static function getMySqlCharacterSet() {
		$sqlEncoding = '';

		$res = $GLOBALS['TYPO3_DB']->sql_query('SHOW VARIABLES LIKE "%character%";');
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$sqlEncoding .= $row['Variable_name'] . ' : ' . $row['Value'] . '<br />';
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		return $sqlEncoding;
	}

	/**
	 * Generate a special formated div (with icon)
	 *
	 * @param   string     $label
	 * @param   string     $value
	 * @return  string HTML code
	 */
	public static function writeInformation($label, $value) {
		return '
			<div class="typo3-message message-information">
				<div class="header-container">
					<div class="message-header message-left">' . $label . '</div>
					<div class="message-header message-right">' . $value . '&nbsp;</div>
				</div>
				<div class="message-body"></div>
			</div>
		';
	}

	/**
	 * Generate a formated list
	 *
	 * @param string $label
	 * @param array  $array
	 * @return string HTML code
	 */
	public static function writeInformationList($label, $array) {
		$content = '<ul>';
		foreach ($array as $value) {
			$content .= '<li>' . $value . '</li>';
		}
		$content .= '</ul>';
		return self::writeInformation($label, $content);
	}

	/**
	 * Open a popup with div content
	 *
	 * @param string $divId
	 * @param string $title
	 * @param string $hideContent
	 * @return string HTML code
	 */
	public static function writePopUp($divId, $title, $hideContent) {
		$js = 'Shadowbox.open({content:\'<div>\'+$(\'' . $divId . '\').innerHTML';
		$js .= '+\'</div>\',player:\'html\',title:\'' . $title . '\',height:600,width:800});';
		$content = '<input type="button" onclick="' . $js . '" value="+"/>';
		$content .= '<div style="display:none;" id="' . $divId . '">' . $hideContent . '</div>';
		return $content;
	}

	/**
	 * Return the use number of a plugin
	 *
	 * @param string $key
	 * @param string $mode
	 * @return int
	 */
	public static function checkPluginIsUsed($key, $mode = 'all') {
		$select = 'tt_content.list_type,tt_content.pid,pages.title';
		$from   = 'tt_content,pages';

		switch ($mode) {
			default:
			case 'all':
				$where = 'tt_content.pid=pages.uid AND tt_content.hidden=0 AND tt_content.deleted=0 ';
				$where .= 'AND pages.hidden=0 AND pages.deleted=0 AND tt_content.CType=\'list\' ';
				$where .= 'AND tt_content.list_type=\'' . $key . '\'';
				break;
			case 'hidden':
				$where = 'tt_content.pid=pages.uid AND (tt_content.hidden=1 OR pages.hidden=1) AND tt_content.deleted=0 ';
				$where .= 'AND pages.deleted=0 AND tt_content.CType=\'list\' AND tt_content.list_type=\'' . $key . '\'';
				break;
			case 'deleted':
				$where = 'tt_content.pid=pages.uid AND (tt_content.deleted=1 OR pages.deleted=1) ';
				$where .= 'AND tt_content.CType=\'list\' AND tt_content.list_type=\'' . $key . '\'';
				break;
		}

		if (t3lib_extMgm::isLoaded('templavoila')) {
			$where .= self::getTvFlexWhere();
		}

		$groupBy = '';
		$orderBy = 'tt_content.list_type';

		$items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($select, $from, $where, $groupBy, $orderBy);
		return count($items);
	}

	/**
	 * Return the use number of a ctype
	 *
	 * @param string $key
	 * @param string $mode
	 * @return int
	 */
	public static function checkCtypeIsUsed($key, $mode = 'all') {
		$select = 'tt_content.CType,tt_content.pid,pages.title';
		$from   = 'tt_content,pages';

		switch ($mode) {
			default:
			case 'all':
				$where = 'tt_content.pid=pages.uid AND tt_content.hidden=0 AND tt_content.deleted=0 ';
				$where .= 'AND pages.hidden=0 AND pages.deleted=0 AND tt_content.CType=\'' . $key . '\'';
				break;
			case 'hidden':
				$where = 'tt_content.pid=pages.uid AND (tt_content.hidden=1 OR pages.hidden=0) AND tt_content.deleted=0 ';
				$where .= 'AND pages.deleted=0 AND tt_content.CType=\'' . $key . '\'';
				break;
			case 'deleted':
				$where = 'tt_content.pid=pages.uid AND (tt_content.deleted=1 OR pages.deleted=1) AND tt_content.CType=\'' . $key . '\'';
				break;
		}

		if (t3lib_extMgm::isLoaded('templavoila')) {
			$where .= self::getTvFlexWhere();
		}

		$groupBy = '';
		$orderBy = 'tt_content.CType';

		$items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($select, $from, $where, $groupBy, $orderBy);
		return count($items);
	}

	/**
	 * Get sql where for templavoila flex (to check content is in teh flexform)
	 *
	 * @return string
	 */
	public static function getTvFlexWhere() {
		$where = " ";
		$where .= "AND (";
		$where .= "pages.tx_templavoila_flex REGEXP concat('<value index=\"vDEF\">',tt_content.uid,'</value>')";
		$where .= "OR pages.tx_templavoila_flex REGEXP concat('<value index=\"vDEF\">',tt_content.uid,',.*</value>')";
		$where .= "OR pages.tx_templavoila_flex REGEXP concat('<value index=\"vDEF\">.*,',tt_content.uid,'</value>')";
		$where .= "OR pages.tx_templavoila_flex REGEXP concat('<value index=\"vDEF\">.*,',tt_content.uid,',.*</value>')";
		$where .= ")";
		return $where;
	}

	/**
	 * Get all the different plugins
	 *
	 * @param string $where
	 * @return array
	 */
	public static function getAllDifferentPlugins($where) {
		if (t3lib_extMgm::isLoaded('templavoila')) {
			$where .= self::getTvFlexWhere();
		}
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'DISTINCT tt_content.list_type',
			'tt_content,pages',
			'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.deleted=0 ' .
				'AND pages.deleted=0 ' . $where . 'AND tt_content.CType=\'list\'',
			'',
			'tt_content.list_type'
		);
	}

	/**
	 * Get all the different plugins (html select)
	 *
	 * @param string $where
	 * @param string $getFiltersCat
	 * @return array
	 */
	public static function getAllDifferentPluginsSelect($where, $getFiltersCat) {
		$pluginsList = self::getAllDifferentPlugins($where);
		$filterCat   = '';

		if ($getFiltersCat == 'all') {
			$filterCat .= '<option value="all" selected="selected">' . $GLOBALS['LANG']->getLL('all') . '</option>';
		} else {
			$filterCat .= '<option value="all">' . $GLOBALS['LANG']->getLL('all') . '</option>';
		}

		foreach ($pluginsList as $pluginsElement) {
			if (($getFiltersCat == $pluginsElement['list_type']) && ($getFiltersCat !== NULL)) {
				$filterCat .= '<option value="' . $pluginsElement['list_type'] . '" selected="selected">';
				$filterCat .= $pluginsElement['list_type'] . '</option>';
			} else {
				$filterCat .= '<option value="' . $pluginsElement['list_type'] . '">' . $pluginsElement['list_type'] . '</option>';
			}
		}

		return '<select name="filtersCat" id="filtersCat">' . $filterCat . '</select>';
	}

	/**
	 * Get all the different ctypes
	 *
	 * @param string $where
	 * @return array
	 */
	public static function getAllDifferentCtypes($where) {
		if (t3lib_extMgm::isLoaded('templavoila')) {
			$where .= self::getTvFlexWhere();
		}
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'DISTINCT tt_content.CType',
			'tt_content,pages',
			'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.deleted=0 ' .
				'AND pages.deleted=0 ' . $where . 'AND tt_content.CType<>\'list\'',
			'',
			'tt_content.list_type'
		);
	}

	/**
	 * Get all the different ctypes (html select)
	 *
	 * @param string $where
	 * @param string $getFiltersCat
	 * @return array
	 */
	public static function getAllDifferentCtypesSelect($where, $getFiltersCat) {
		$pluginsList = self::getAllDifferentCtypes($where);
		$filterCat   = '';

		if ($getFiltersCat == 'all') {
			$filterCat .= '<option value="all" selected="selected">' . $GLOBALS['LANG']->getLL('all') . '</option>';
		} else {
			$filterCat .= '<option value="all">' . $GLOBALS['LANG']->getLL('all') . '</option>';
		}

		foreach ($pluginsList as $pluginsElement) {
			if (($getFiltersCat == $pluginsElement['CType']) && ($getFiltersCat !== NULL)) {
				$filterCat .= '<option value="' . $pluginsElement['CType'] . '" selected="selected">';
				$filterCat .= $pluginsElement['CType'] . '</option>';
			} else {
				$filterCat .= '<option value="' . $pluginsElement['CType'] . '">' . $pluginsElement['CType'] . '</option>';
			}
		}

		return '<select name="filtersCat" id="filtersCat">' . $filterCat . '</select>';
	}

	/**
	 * Get all the usage of a all the plugins
	 *
	 * @param string $where
	 * @param string $limit
	 * @return array
	 */
	public static function getAllPlugins($where, $limit = '') {
		if (t3lib_extMgm::isLoaded('templavoila')) {
			$where .= self::getTvFlexWhere();
		}
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'DISTINCT tt_content.list_type,tt_content.pid,tt_content.uid,' .
				'pages.title,pages.hidden as "hiddenpages",tt_content.hidden as "hiddentt_content"',
			'tt_content,pages',
			'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.deleted=0 ' .
				'AND pages.deleted=0 ' . $where . 'AND tt_content.CType=\'list\'',
			'',
			'tt_content.list_type,tt_content.pid',
			$limit
		);
	}

	/**
	 * Get all the usage of a all the ctypes
	 *
	 * @param string $where
	 * @param string $limit
	 * @return array
	 */
	public static function getAllCtypes($where, $limit = '') {
		if (t3lib_extMgm::isLoaded('templavoila')) {
			$where .= self::getTvFlexWhere();
		}
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'DISTINCT tt_content.CType,tt_content.pid,tt_content.uid,pages.title,' .
				'pages.hidden as "hiddenpages",tt_content.hidden as "hiddentt_content"',
			'tt_content,pages',
			'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.deleted=0 ' .
				'AND pages.deleted=0 ' . $where . 'AND tt_content.CType<>\'list\'',
			'',
			'tt_content.CType,tt_content.pid',
			$limit
		);
	}


}

?>
