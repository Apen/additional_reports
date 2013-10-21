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
class tx_additionalreports_util {
	/**
	 * Define all the reports
	 *
	 * @return array
	 */
	public function getReportsList() {
		$reports = array(
			'eid', 'clikeys', 'plugins', 'xclass', 'hooks', 'status', 'ajax', 'logerrors', 'websitesconf', 'dbcheck', 'realurlerrors', 'extensions'
		);

		if (self::intFromVer(TYPO3_version) >= 4005000) {
			$reports[] = 'extdirect';
		}

		return $reports;
	}

	/**
	 * Get base url of the report (use to generate links)
	 *
	 * @return string url
	 */
	public function getBaseUrl() {
		// since 6.0> extbase is using by reports module
		$baseUrl = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'mod.php?';
		$parameters = array();
		if (self::intFromVer(TYPO3_version) < 6000000) {
			$parameters[] = 'M=tools_txreportsM1';
		} else {
			if (tx_additionalreports_util::intFromVer(TYPO3_version) < 6002000) {
				$vars = t3lib_div::_GET('tx_reports_tools_reportstxreportsm1');
				$parameters[] = 'M=tools_ReportsTxreportsm1';
				$parameters[] = 'tx_reports_tools_reportstxreportsm1%5Bextension%5D=additional_reports';
				$parameters[] = 'tx_reports_tools_reportstxreportsm1%5Breport%5D=' . $vars['report'];
				$parameters[] = 'tx_reports_tools_reportstxreportsm1%5Baction%5D=detail';
				$parameters[] = 'tx_reports_tools_reportstxreportsm1%5Bcontroller%5D=Report';
			} else {
				$vars = t3lib_div::_GET('tx_reports_system_reportstxreportsm1');
				$parameters[] = 'M=system_ReportsTxreportsm1';
				$parameters[] = 'tx_reports_system_reportstxreportsm1%5Bextension%5D=additional_reports';
				$parameters[] = 'tx_reports_system_reportstxreportsm1%5Breport%5D=' . $vars['report'];
				$parameters[] = 'tx_reports_system_reportstxreportsm1%5Baction%5D=detail';
				$parameters[] = 'tx_reports_system_reportstxreportsm1%5Bcontroller%5D=Report';
			}
		}
		return $baseUrl . implode('&', $parameters);
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
	 * Generates a list of Page-uid's from $id
	 *
	 * @param  int    $id
	 * @param  int    $depth
	 * @param  int    $begin
	 * @param  string $permsClause
	 * @return string
	 */
	public function getTreeList($id, $depth, $begin = 0, $permsClause = '1=1') {
		$depth = intval($depth);
		$begin = intval($begin);
		$id = intval($id);
		if ($begin == 0) {
			$theList = $id;
		} else {
			$theList = '';
		}
		if ($id && $depth > 0) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'pages', 'pid=' . $id . ' ' . t3lib_BEfunc::deleteClause('pages') . ' AND ' . $permsClause);
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
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'pages', 'uid IN (' . $listOfUids . ') AND ' . $where);
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

	/**
	 * Returns an integer from a three part version number, eg '4.12.3' -> 4012003
	 *
	 * @param    string $verNumberStr number on format x.x.x
	 * @return   integer   Integer version of version number (where each part can count to 999)
	 */
	public static function intFromVer($verNumberStr) {
		$verParts = explode('.', $verNumberStr);
		return intval((int)$verParts[0] . str_pad((int)$verParts[1], 3, '0', STR_PAD_LEFT) . str_pad((int)$verParts[2], 3, '0', STR_PAD_LEFT));
	}

	/**
	 * Splits a version range into an array.
	 *
	 * If a single version number is given, it is considered a minimum value.
	 * If a dash is found, the numbers left and right are considered as minimum and maximum. Empty values are allowed.
	 *
	 * @param    string $ver A string with a version range.
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
	 * Gathers all extensions in $path
	 *
	 * @param    string $path     Absolute path to local, global or system extensions
	 * @param    array  $dbSchema array with all the tables
	 * @return    array        "Returns" content by reference
	 */
	public static function getInstExtList($path, $dbSchema) {
		$list = array();
		if (@is_dir($path)) {
			$extList = t3lib_div::get_dirs($path);
			if (is_array($extList)) {
				foreach ($extList as $extKey) {
					if (@is_file($path . $extKey . '/ext_emconf.php')) {
						$emConf = self::includeEMCONF($path . $extKey . '/ext_emconf.php', $extKey);
						if (is_array($emConf)) {
							$currentExt = array();
							$currentExt['extkey'] = $extKey;
							$currentExt['installed'] = t3lib_extMgm::isLoaded($extKey);
							$currentExt['EM_CONF'] = $emConf;
							$currentExt['files'] = t3lib_div::getFilesInDir($path . $extKey, '', 0, '', NULL);
							$currentExt['lastversion'] = tx_additionalreports_util::checkExtensionUpdate($currentExt);
							$currentExt['affectedfiles'] = tx_additionalreports_util::getExtAffectedFiles($currentExt);
							$currentExt['icon'] = tx_additionalreports_util::getExtIcon($extKey);

							// db infos
							$fdFile = array();
							$updateStatements = array();
							tx_additionalreports_util::getExtSqlUpdateStatements($currentExt, $dbSchema, $fdFile, $updateStatements);
							$currentExt['fdfile'] = $fdFile;
							$currentExt['updatestatements'] = $updateStatements;

							if ($currentExt['installed']) {
								if ($currentExt['lastversion']) {
									$list['ter'][$extKey] = $currentExt;
								} else {
									$list['dev'][$extKey] = $currentExt;
								}
							} else {
								$list['unloaded'][$extKey] = $currentExt;
							}
						}
					}
				}
			}
		}
		return $list;
	}

	/**
	 * Returns the $EM_CONF array from an extensions ext_emconf.php file
	 *
	 * @param    string $path    Absolute path to EMCONF file.
	 * @param    string $_EXTKEY Extension key.
	 * @return    array        EMconf array values.
	 */
	public static function includeEMCONF($path, $_EXTKEY) {
		$EM_CONF = NULL;
		include($path);
		return $EM_CONF[$_EXTKEY];
	}

	/**
	 * Get last version information for an extkey
	 *
	 * @param array $extInfo
	 * @return array        EMconf array values.
	 */
	public static function checkExtensionUpdate($extInfo) {
		if (self::intFromVer(TYPO3_version) < 6000000) {
			$lastVersion = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'cache_extensions', 'extkey="' . $extInfo['extkey'] . '" AND lastversion=1');
			if ($lastVersion) {
				$lastVersion[0]['updatedate'] = date('d/m/Y', $lastVersion[0]['lastuploaddate']);
				return $lastVersion[0];
			}
		} else {
			$lastVersion = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tx_extensionmanager_domain_model_extension', 'extension_key="' . $extInfo['extkey'] . '" AND current_version=1');
			if ($lastVersion) {
				$lastVersion[0]['updatedate'] = date('d/m/Y', $lastVersion[0]['last_updated']);
				return $lastVersion[0];
			}
		}
		return NULL;
	}

	/**
	 * Compares two arrays with MD5-hash values for analysis of which files has changed.
	 *
	 * @param    array $current Current values
	 * @param    array $past    Past values
	 * @return    array        Affected files
	 */
	public static function findMD5ArrayDiff($current, $past) {
		if (!is_array($current)) {
			$current = array();
		}
		if (!is_array($past)) {
			$past = array();
		}
		$filesInCommon = array_intersect($current, $past);
		$diff1 = array_keys(array_diff($past, $filesInCommon));
		$diff2 = array_keys(array_diff($current, $filesInCommon));
		$affectedFiles = array_unique(array_merge($diff1, $diff2));
		return $affectedFiles;
	}

	/**
	 * Get all all files and md5 to check modified files
	 *
	 * @param array $extInfo
	 * @return array
	 */
	public function getFilesMDArray($extInfo) {
		$filesMD5Array = array();
		$fileArr = array();
		$extPath = self::typePath($extInfo['type']) . $extInfo['extkey'] . '/';
		$fileArr = t3lib_div::getAllFilesAndFoldersInPath($fileArr, $extPath, '', 0, 99, $GLOBALS['TYPO3_CONF_VARS']['EXT']['excludeForPackaging']);
		foreach ($fileArr as $file) {
			$relFileName = substr($file, strlen($extPath));
			if ($relFileName != 'ext_emconf.php') {
				$content = t3lib_div::getUrl($file);
				$filesMD5Array[$relFileName] = substr(md5($content), 0, 4);
			}
		}
		return $filesMD5Array;
	}

	/**
	 * Get all modified files
	 *
	 * @param array $extInfo
	 * @return array
	 */
	public static function getExtAffectedFiles($extInfo) {
		$currentMd5Array = self::getFilesMDArray($extInfo);
		return self::findMD5ArrayDiff($currentMd5Array, unserialize($extInfo['EM_CONF']['_md5_values_when_last_written']));
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
		if (tx_additionalreports_util::intFromVer(TYPO3_version) < 6002000) {
			require_once(PATH_t3lib . 'class.t3lib_page.php');
		}
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

		$installClass = self::getInstallSqlClass();
		$instObj = new $installClass();
		$fdDb = self::getDatabaseSchema();

		if ($tblFileContent) {
			$fileContent = implode(chr(10), $instObj->getStatementArray($tblFileContent, 1, '^CREATE TABLE '));

			// just support for old version
			if (method_exists($installClass, 'getFieldDefinitions_fileContent') === TRUE) {
				$fdFile = $instObj->getFieldDefinitions_fileContent($fileContent);
			} else {
				$fdFile = $instObj->getFieldDefinitions_sqlContent($fileContent);
			}

			$diff = $instObj->getDatabaseExtra($fdFile, $fdDb);
			$updateStatements = $instObj->getUpdateSuggestions($diff);
			$diff = $instObj->getDatabaseExtra($fdDb, $fdFile);
			$removeStatements = $instObj->getUpdateSuggestions($diff, 'remove');

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
	 * Get the sql statements of an extension define in ext_tables.sql
	 *
	 * @param string $extInfo
	 * @param array  $dbSchema
	 * @param array  &$fdFile
	 * @param array  &$updateStatements
	 */
	public static function getExtSqlUpdateStatements($extInfo, $dbSchema, &$fdFile, &$updateStatements) {
		$installClass = self::getInstallSqlClass();

		if (is_array($extInfo['files']) && in_array('ext_tables.sql', $extInfo['files'])) {
			$fileContent = t3lib_div::getUrl(self::getExtPath($extInfo['extkey'], $extInfo['type']) . 'ext_tables.sql');
		}

		$instObj = new $installClass();

		// just support for old version < 4.5
		if (method_exists($installClass, 'getFieldDefinitions_fileContent') === TRUE) {
			$fdFile = $instObj->getFieldDefinitions_fileContent($fileContent);
		} else {
			$fdFile = $instObj->getFieldDefinitions_sqlContent($fileContent);
		}

		$diff = $instObj->getDatabaseExtra($fdFile, $dbSchema);
		$updateStatements = $instObj->getUpdateSuggestions($diff);
	}

	/**
	 * Get the install class name (for compatibility)
	 *
	 * @return string
	 */
	public function getInstallSqlClass() {
		$installClass = 't3lib_install';

		if (self::intFromVer(TYPO3_version) >= 4006000) {
			$installClass = 't3lib_install_Sql';
		}

		return $installClass;
	}

	/**
	 * Get the entire database schema
	 *
	 * @return array
	 */
	public static function getDatabaseSchema() {
		$installClass = self::getInstallSqlClass();
		$instObj = new $installClass();
		return $instObj->getFieldDefinitions_database(TYPO3_db);
	}

	/**
	 * Compare 2 versions of an extension
	 *
	 * @param string $depV
	 * @return string
	 */
	public static function versionCompare($depV) {
		$t3version = TYPO3_version;
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
	 * @param int $extKey
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
	 * @param   string $label
	 * @param   string $value
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
		$content = '';
		foreach ($array as $value) {
			$content .= '' . $value . '<br/>';
		}
		$content .= '';
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
	 * Get all the different plugins
	 *
	 * @param string $where
	 * @return array
	 */
	public static function getAllDifferentPlugins($where) {
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'DISTINCT tt_content.list_type',
			'tt_content,pages',
			'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.deleted=0 AND pages.deleted=0 ' . $where . 'AND tt_content.CType=\'list\' AND tt_content.list_type<>""',
			'',
			'tt_content.list_type'
		);
	}

	/**
	 * Get all the different plugins (html select)
	 *
	 * @param boolean $displayHidden
	 * @return array
	 */
	public static function getAllDifferentPluginsSelect($displayHidden) {
		$where = ($displayHidden === TRUE) ? '' : ' AND tt_content.hidden=0 AND pages.hidden=0 ';
		$getFiltersCat = t3lib_div::_GP('filtersCat');
		$pluginsList = self::getAllDifferentPlugins($where);
		$filterCat = '';

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

		$listUrlOrig = tx_additionalreports_util::getBaseUrl() . '&display=' . tx_additionalreports_util::getPluginsDisplayMode();

		$content = '<select name="filtersCat" id="filtersCat">' . $filterCat . '</select>';
		$content .= '<a href="#"  onClick="jumpToUrl(\'' . $listUrlOrig;
		$content .= '&filtersCat=\'+document.getElementById(\'filtersCat\').value);">';
		$content .= '&nbsp;<img width="16" height="16" title="" alt="" src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR');
		$content .= '../typo3conf/ext/additional_reports/res/images/refresh_n.gif"></a>';

		return $content;
	}

	/**
	 * Get all the different ctypes
	 *
	 * @param string $where
	 * @return array
	 */
	public static function getAllDifferentCtypes($where) {
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'DISTINCT tt_content.CType',
			'tt_content,pages',
			'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.deleted=0 AND pages.deleted=0 ' . $where . 'AND tt_content.CType<>\'list\'',
			'',
			'tt_content.list_type'
		);
	}

	/**
	 * Get all the different ctypes (html select)
	 *
	 * @param boolean $displayHidden
	 * @return array
	 */
	public static function getAllDifferentCtypesSelect($displayHidden) {
		$where = ($displayHidden === TRUE) ? '' : ' AND tt_content.hidden=0 AND pages.hidden=0 ';
		$getFiltersCat = t3lib_div::_GP('filtersCat');
		$pluginsList = self::getAllDifferentCtypes($where);
		$filterCat = '';

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

		$listUrlOrig = tx_additionalreports_util::getBaseUrl() . '&display=' . tx_additionalreports_util::getPluginsDisplayMode();

		$content = '<select name="filtersCat" id="filtersCat">' . $filterCat . '</select>';
		$content .= '<a href="#"  onClick="jumpToUrl(\'' . $listUrlOrig;
		$content .= '&filtersCat=\'+document.getElementById(\'filtersCat\').value);">';
		$content .= '&nbsp;<img width="16" height="16" title="" alt="" src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR');
		$content .= '../typo3conf/ext/additional_reports/res/images/refresh_n.gif"></a>';

		return $content;
	}

	/**
	 * Get all the usage of a all the plugins
	 *
	 * @param string $where
	 * @param string $limit
	 * @return array
	 */
	public static function getAllPlugins($where, $limit = '') {
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'DISTINCT tt_content.list_type,tt_content.pid,tt_content.uid,pages.title,pages.hidden as "hiddenpages",tt_content.hidden as "hiddentt_content"',
			'tt_content,pages',
			'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.deleted=0 AND pages.deleted=0 ' . $where . 'AND tt_content.CType=\'list\'',
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
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'DISTINCT tt_content.CType,tt_content.pid,tt_content.uid,pages.title,pages.hidden as "hiddenpages",tt_content.hidden as "hiddentt_content"',
			'tt_content,pages',
			'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.deleted=0 AND pages.deleted=0 ' . $where . 'AND tt_content.CType<>\'list\'',
			'',
			'tt_content.CType,tt_content.pid',
			$limit
		);
	}

	/**
	 * Return a php array of autoload classes
	 *
	 * @param string $identifier
	 * @return mixed|null
	 */
	public static function getAutoloadXlassFile($identifier) {
		$file = PATH_site . 'typo3temp/Cache/Code/cache_phpcode/' . $identifier . '.php';
		if (is_file($file)) {
			return require($file);
		} else {
			return NULL;
		}
	}

	/**
	 * Return all the XCLASS from autoload class
	 *
	 * @return array|null
	 */
	public static function getAutoloadXlass() {
		$identifier = 'autoload_' . sha1(TYPO3_version . PATH_site . 'autoload');
		$classes = self::getAutoloadXlassFile($identifier);
		if ($classes === NULL) {
			return NULL;
		}
		$xclass = array();
		foreach ($classes as $class => $file) {
			if ((substr($class, 0, 3) === 'ux_') && ($file !== NULL)) {
				$xclass[$class] = $file;
			}
		}
		return $xclass;
	}

	/**
	 * Return an array with all versions infos
	 *
	 * @return array
	 */
	public static function getJsonVersionInfos() {
		return json_decode(t3lib_div::getUrl('http://get.typo3.org/json'), TRUE);
	}

	/**
	 * Return an array with current version infos
	 *
	 * @return array
	 */
	public static function getCurrentVersionInfos($jsonVersions) {
		$currentVersion = explode('.', TYPO3_version);
		return $jsonVersions[$currentVersion[0] . '.' . $currentVersion[1]]['releases'][TYPO3_version];
	}

	/**
	 * Return an array with current branch infos
	 *
	 * @return array
	 */
	public static function getCurrentBranchInfos($jsonVersions) {
		$currentVersion = explode('.', TYPO3_version);
		return reset($jsonVersions[$currentVersion[0] . '.' . $currentVersion[1]]['releases']);
	}

	/**
	 * Return an array with latest stable infos
	 *
	 * @return array
	 */
	public static function getLatestStableInfos($jsonVersions) {
		$currentVersion = explode('.', $jsonVersions['latest_stable']);
		return $jsonVersions[$currentVersion[0] . '.' . $currentVersion[1]]['releases'][$jsonVersions['latest_stable']];
	}

	/**
	 * Return an array with latest LTS infos
	 *
	 * @return array
	 */
	public static function getLatestLtsInfos($jsonVersions) {
		$currentVersion = explode('.', $jsonVersions['latest_lts']);
		return $jsonVersions[$currentVersion[0] . '.' . $currentVersion[1]]['releases'][$jsonVersions['latest_lts']];
	}

	/**
	 * Return the display mode
	 *
	 * @return string
	 */
	public static function getPluginsDisplayMode() {
		$displayMode = NULL;

		// Check the display mode
		$display = t3lib_div::_GP('display');
		if ($display !== NULL) {
			$GLOBALS['BE_USER']->setAndSaveSessionData('additional_reports_menu', $display);
			$displayMode = $display;
		}

		// Check the session
		$sessionDisplay = $GLOBALS['BE_USER']->getSessionData('additional_reports_menu');
		if ($sessionDisplay !== NULL) {
			$displayMode = $sessionDisplay;
		}

		// force default reports to history value
		if ($displayMode == 1) {
			$displayMode = 5;
		}

		return $displayMode;
	}

}

?>
