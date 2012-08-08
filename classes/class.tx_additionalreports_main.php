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
 * This class provides methods to generate the reports
 *
 * @author         CERDAN Yohann <cerdanyohann@yahoo.fr>
 * @package        TYPO3
 */
class tx_additionalreports_main
{
	/**
	 * Get the global css path
	 *
	 * @return string
	 */
	public static function getCss() {
		return t3lib_extMgm::extRelPath('additional_reports') . 'res/css/tx_additionalreports.css';
	}

	/**
	 * Generate the xclass report
	 *
	 * @return string HTML code
	 */
	public function displayXclass() {
		$template = new tx_additionalreports_templating();
		$template->initTemplate('typo3conf/ext/additional_reports/res/templates/xclass.html');

		$content = '';

		$xclassList = array(
			'BE' => $GLOBALS['TYPO3_CONF_VARS']['BE']['XCLASS'],
			'FE' => $GLOBALS['TYPO3_CONF_VARS']['FE']['XCLASS']
		);

		if (tx_additionalreports_util::intFromVer(TYPO3_version) >= 6000000) {
			$xclassList['autoload'] = tx_additionalreports_util::getAutoloadXlass();
		}

		foreach ($xclassList as $keyXclass => $items) {
			$markersArray = array();

			if ($keyXclass == 'FE') {
				$markersArray['###LLL:TITLE###'] = 'Frontend';
			}
			if ($keyXclass == 'BE') {
				$markersArray['###LLL:TITLE###'] = 'Backend';
			}
			if ($keyXclass == 'autoload') {
				if ($xclassList['autoload'] === NULL) {
					$content .= tx_additionalreports_util::writeInformation(
						$GLOBALS['LANG']->getLL('careful'),
						$GLOBALS['LANG']->getLL('xclasscarefuldesc')
					);
				}
				$markersArray['###LLL:TITLE###'] = 'Autoload XCLASS (6.0)';
			}

			$markersArray['###LLL:NAME###'] = $GLOBALS['LANG']->getLL('name');
			$markersArray['###LLL:PATH###'] = $GLOBALS['LANG']->getLL('path');

			if (count($items) > 0) {
				$markersArrayTemp = array();
				foreach ($items as $itemKey => $itemValue) {
					$markersArrayTemp[] = array(
						'###NAME###' => $itemKey,
						'###PATH###' => $itemValue
					);
				}
				$markersArray['###REPORTS_XCLASS_OBJECT###'] = $template->renderAllTemplate(
					$markersArrayTemp, '###REPORTS_XCLASS_OBJECT###'
				);
			} else {
				$markersArray['###REPORTS_XCLASS_OBJECT###'] = $template->renderAllTemplate(
					array('###NORESULTS###' => $GLOBALS['LANG']->getLL('noresults')),
					'###REPORTS_XCLASS_NORESULTS###'
				);
			}

			$content .= $template->renderAllTemplate($markersArray, '###REPORTS_XCLASS###');
		}

		return $content;
	}

	/**
	 * Generate the ajax report
	 *
	 * @return string HTML code
	 */
	public function displayAjax() {
		$template = new tx_additionalreports_templating();
		$template->initTemplate('typo3conf/ext/additional_reports/res/templates/ajax.html');

		$items                           = $GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX'];
		$markersArray                    = array();
		$markersArray['###LLL:TITLE###'] = $GLOBALS['LANG']->getLL('ajax_description');
		$markersArray['###LLL:NAME###']  = $GLOBALS['LANG']->getLL('name');
		$markersArray['###LLL:PATH###']  = $GLOBALS['LANG']->getLL('path');

		if (count($items) > 0) {
			$markersArrayTemp = array();
			foreach ($items as $itemKey => $itemValue) {
				$markersArrayTemp[] = array(
					'###NAME###' => $itemKey,
					'###PATH###' => $itemValue
				);
			}
			$markersArray['###REPORTS_AJAX_OBJECT###'] = $template->renderAllTemplate(
				$markersArrayTemp, '###REPORTS_AJAX_OBJECT###'
			);
		} else {
			return $GLOBALS['LANG']->getLL('noresults');
		}

		return $template->renderAllTemplate($markersArray, '###REPORTS_AJAX###');
	}

	/**
	 * Generate the cli keys report
	 *
	 * @return string HTML code
	 */
	public function displayCliKeys() {
		$template = new tx_additionalreports_templating();
		$template->initTemplate('typo3conf/ext/additional_reports/res/templates/clikeys.html');

		$items                               = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys'];
		$markersArray                        = array();
		$markersArray['###LLL:TITLE###']     = $GLOBALS['LANG']->getLL('clikeys_description');
		$markersArray['###LLL:EXTENSION###'] = $GLOBALS['LANG']->getLL('extension');
		$markersArray['###LLL:NAME###']      = $GLOBALS['LANG']->getLL('name');
		$markersArray['###LLL:PATH###']      = $GLOBALS['LANG']->getLL('path');
		$markersArray['###LLL:USER###']      = $GLOBALS['LANG']->getLL('user');

		if (count($items) > 0) {
			$markersArrayTemp = array();
			foreach ($items as $itemKey => $itemValue) {
				preg_match('/EXT:(.*?)\//', $itemValue[0], $ext);
				$markersArrayTemp[] = array(
					'###ICONEXT###'   => tx_additionalreports_util::getExtIcon($ext[1]),
					'###EXTENSION###' => $ext[1],
					'###NAME###'      => $itemKey,
					'###PATH###'      => $itemValue[0],
					'###USER###'      => $itemValue[1]
				);
			}
			$markersArray['###REPORTS_CLIKEYS_OBJECT###'] = $template->renderAllTemplate(
				$markersArrayTemp, '###REPORTS_CLIKEYS_OBJECT###'
			);
		} else {
			return $GLOBALS['LANG']->getLL('noresults');
		}

		return $template->renderAllTemplate($markersArray, '###REPORTS_CLIKEYS###');
	}

	/**
	 * Generate the eid report
	 *
	 * @return string HTML code
	 */
	public function displayEid() {
		$template = new tx_additionalreports_templating();
		$template->initTemplate('typo3conf/ext/additional_reports/res/templates/eid.html');

		$items                               = $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include'];
		$markersArray                        = array();
		$markersArray['###LLL:TITLE###']     = $GLOBALS['LANG']->getLL('eid_description');
		$markersArray['###LLL:EXTENSION###'] = $GLOBALS['LANG']->getLL('extension');
		$markersArray['###LLL:NAME###']      = $GLOBALS['LANG']->getLL('name');
		$markersArray['###LLL:PATH###']      = $GLOBALS['LANG']->getLL('path');

		if (count($items) > 0) {
			$markersArrayTemp = array();
			foreach ($items as $itemKey => $itemValue) {
				preg_match('/EXT:(.*?)\//', $itemValue, $ext);
				if (t3lib_extMgm::isLoaded($ext[1])) {
					$markersArrayTemp[] = array(
						'###ICONEXT###'   => tx_additionalreports_util::getExtIcon($ext[1]),
						'###EXTENSION###' => $ext[1],
						'###NAME###'      => $itemKey,
						'###PATH###'      => $itemValue
					);
				}
			}
			$markersArray['###REPORTS_EID_OBJECT###'] = $template->renderAllTemplate(
				$markersArrayTemp, '###REPORTS_EID_OBJECT###'
			);
		} else {
			return $GLOBALS['LANG']->getLL('noresults');
		}

		return $template->renderAllTemplate($markersArray, '###REPORTS_EID###');
	}

	/**
	 * Generate the ext direct report
	 *
	 * @return string HTML code
	 */
	public function displayExtDirect() {
		$template = new tx_additionalreports_templating();
		$template->initTemplate('typo3conf/ext/additional_reports/res/templates/extdirect.html');

		$items                           = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ExtDirect'];
		$markersArray                    = array();
		$markersArray['###LLL:TITLE###'] = $GLOBALS['LANG']->getLL('extdirect_description');
		$markersArray['###LLL:NAME###']  = $GLOBALS['LANG']->getLL('name');
		$markersArray['###LLL:PATH###']  = $GLOBALS['LANG']->getLL('path');

		if (count($items) > 0) {
			$markersArrayTemp = array();
			foreach ($items as $itemKey => $itemValue) {
				$markersArrayTemp[] = array(
					'###NAME###'      => $itemKey,
					'###PATH###'      => tx_additionalreports_util::viewArray($itemValue)
				);
			}
			$markersArray['###REPORTS_EXTDIRECT_OBJECT###'] = $template->renderAllTemplate(
				$markersArrayTemp, '###REPORTS_EXTDIRECT_OBJECT###'
			);
		} else {
			return $GLOBALS['LANG']->getLL('noresults');
		}

		return $template->renderAllTemplate($markersArray, '###REPORTS_EXTDIRECT###');
	}

	/**
	 * Generate the loaded extension report
	 *
	 * @return string HTML code
	 */
	public function displayExtensions() {
		$template = new tx_additionalreports_templating();
		$template->initTemplate('typo3conf/ext/additional_reports/res/templates/extensions.html');

		$content            = '';
		$path               = PATH_typo3conf . 'ext/';
		$items              = array();
		$itemsDev           = array();
		$itemsUnloaded      = array();
		$extensionsToUpdate = 0;
		$extensionsDev      = 0;
		$extensionsLoaded   = 0;
		$extensionsModified = 0;

		$em = tx_additionalreports_util::getExtList($path, $items);

		/********************************* loaded extension(s) *********************************/

		if (count($items) > 0) {
			$markersArrayTemp = array();
			foreach ($items as $itemKey => $itemValue) {
				if (t3lib_extMgm::isLoaded($itemKey)) {
					$extensionsLoaded++;

					$extKey           = $itemKey;
					$extInfo          = $itemValue;
					$fdFile           = array();
					$updateStatements = array();
					$affectedFiles    = array();
					$lastVersion      = '';

					tx_additionalreports_util::getExtAffectedFiles($em, $extKey, $extInfo, $affectedFiles, $lastVersion);
					tx_additionalreports_util::getExtSqlUpdateStatements($em, $extKey, $extInfo, $fdFile, $updateStatements);

					if (!$lastVersion) {
						$itemsDev[$itemKey] = $itemValue;
						$extensionsDev++;
					} else {
						$markersArrayExtension                        = array();
						$markersArrayExtension['###ICONEXT###']       = tx_additionalreports_util::getExtIcon($extKey);
						$markersArrayExtension['###EXTENSION###']     = $extKey;
						$markersArrayExtension['###EXTENSIONLINK###'] = '<a href="#" onclick="';
						$markersArrayExtension['###EXTENSIONLINK###'] .= tx_additionalreports_util::goToModuleEm($extKey) . '">';
						$markersArrayExtension['###EXTENSIONLINK###'] .= tx_additionalreports_util::getIconZoom() . '</a>';
						$markersArrayExtension['###VERSION###']      = $itemValue['EM_CONF']['version'];
						$markersArrayExtension['###VERSIONCHECK###'] = tx_additionalreports_util::versionCompare(
							$itemValue['EM_CONF']['constraints']['depends']['typo3']
						);

						// need extension update ?
						$updateDate = date('d/m/Y', $lastVersion['lastuploaddate']);
						if (version_compare($itemValue['EM_CONF']['version'], $lastVersion['version'], '<')) {
							$extensionsToUpdate++;
							$markersArrayExtension['###VERSIONLAST###'] = '<span style="color:green;font-weight:bold;">';
							$markersArrayExtension['###VERSIONLAST###'] .= $lastVersion['version'] . '&nbsp;(' . $updateDate . ')</span>';

						} else {
							$markersArrayExtension['###VERSIONLAST###'] = $lastVersion['version'] . '&nbsp;(' . $updateDate . ')';
						}

						$markersArrayExtension['###DOWNLOADS###'] = $lastVersion['alldownloadcounter'];

						// show db
						$dumpTf1 = '';
						$dumpTf2 = '';
						if (count($fdFile) > 0) {
							$id      = 'sql' . $extKey;
							$dumpTf1 = count($fdFile) . ' ' . $GLOBALS['LANG']->getLL('extensions_tablesmodified');
							$dumpTf2 = tx_additionalreports_util::writePopUp(
								$id, $extKey, tx_additionalreports_util::viewArray($fdFile)
							);
						}
						$markersArrayExtension['###TABLES###']     = $dumpTf1;
						$markersArrayExtension['###TABLESLINK###'] = $dumpTf2;

						// need db update
						if (count($updateStatements) > 0) {
							$markersArrayExtension['###TABLESINTEGRITY###'] = $GLOBALS['LANG']->getLL('yes');
						} else {
							$markersArrayExtension['###TABLESINTEGRITY###'] = $GLOBALS['LANG']->getLL('no');
						}

						// need extconf update
						$absPath = tx_additionalreports_util::getExtPath($extKey, $extInfo['type']);
						if (is_file($absPath . 'ext_conf_template.txt')) {
							$configTemplate = t3lib_div::getUrl($absPath . 'ext_conf_template.txt');
							/** @var $tsparserObj t3lib_TSparser */
							$tsparserObj = t3lib_div::makeInstance('t3lib_TSparser');
							$tsparserObj->parse($configTemplate);
							$arr      = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$extKey]);
							$arr      = is_array($arr) ? $arr : array();
							$diffConf = array_diff_key($tsparserObj->setup, $arr);
							if (isset($diffConf['updateMessage'])) {
								unset($diffConf['updateMessage']);
							}
							if (count($diffConf) > 0) {
								$id    = 'extconf' . $extKey;
								$datas = '<span style="color:white;">Diff : </span>' . tx_additionalreports_util::viewArray(
									$diffConf
								);
								$datas .= '<span style="color:white;">$GLOBALS[\'TYPO3_CONF_VARS\'][\'EXT\'][\'extConf\'][\'' . $extKey . '\'] : </span>';
								$datas .= tx_additionalreports_util::viewArray($arr);
								$datas .= '<span style="color:white;">ext_conf_template.txt : </span>';
								$datas .= tx_additionalreports_util::viewArray($tsparserObj->setup);
								$dumpExtConf                                  = tx_additionalreports_util::writePopUp(
									$id, $extKey, $datas
								);
								$markersArrayExtension['###CONFINTEGRITY###'] = $GLOBALS['LANG']->getLL(
									'yes'
								) . '&nbsp;&nbsp;' . $dumpExtConf;
							} else {
								$markersArrayExtension['###CONFINTEGRITY###'] = $GLOBALS['LANG']->getLL('no');
							}
						} else {
							$markersArrayExtension['###CONFINTEGRITY###'] = $GLOBALS['LANG']->getLL('no');
						}

						// modified files
						if (count($affectedFiles) > 0) {
							$extensionsModified++;
							$id = 'files' . $extKey;

							$contentUl = '<div style="display:none;" id="' . $id . '"><ul>';
							foreach ($affectedFiles as $affectedFile) {
								$compareUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
								$compareUrl .= 'index.php?eID=additional_reports_compareFiles';
								$compareUrl .= '&extKey=' . $extKey . '&extFile=' . $affectedFile . '&extVersion=' . $itemValue['EM_CONF']['version'];
								$contentUl .= '<li><a rel="shadowbox;height=600;width=800;" href = "' . $compareUrl . '" target = "_blank"';
								$contentUl .= 'title="' . $affectedFile . ' : ' . $extKey . ' ' . $itemValue['EM_CONF']['version'] . '" > ';
								$contentUl .= $affectedFile . '</a></li>';
							}
							$contentUl .= '</ul>';
							$contentUl .= '</div>';
							$markersArrayExtension['###FILES###']     = count($affectedFiles) . ' ' . $GLOBALS['LANG']->getLL(
								'extensions_filesmodified'
							) . $contentUl;
							$markersArrayExtension['###FILESLINK###'] = '<input type="button" onclick="$(\'' . $id . '\').toggle();" value="+"/>';
						} else {
							$markersArrayExtension['###FILES###']     = '&nbsp;';
							$markersArrayExtension['###FILESLINK###'] = '&nbsp;';
						}

						$content .= '</tr>';
						$markersArrayTemp[] = $markersArrayExtension;
					}
				} else {
					$itemsUnloaded[$itemKey] = $itemValue;
				}

			}
			$markersArray['###REPORTS_EXTENSIONS_OBJECT###'] = $template->renderAllTemplate(
				$markersArrayTemp, '###REPORTS_EXTENSIONS_OBJECT###'
			);
		} else {
			$markersArray['###REPORTS_EXTENSIONS_OBJECT###'] = $template->renderAllTemplate(
				array('###NORESULTS###' => $GLOBALS['LANG']->getLL('noresults')),
				'###REPORTS_EXTENSIONS_NORESULTS###'
			);
		}

		$markersArray['###LLL:TITLELOADED###']     = $extensionsLoaded - count($itemsDev) . ' ' . $GLOBALS['LANG']->getLL(
			'extensions_ter'
		);
		$markersArray['###LLL:TITLEDEV###']        = count($itemsDev) . ' ' . $GLOBALS['LANG']->getLL('extensions_dev');
		$markersArray['###LLL:TITLEULOADED###']    = count($itemsUnloaded) . ' ' . $GLOBALS['LANG']->getLL('extensions_unloaded');
		$markersArray['###LLL:EXTENSION###']       = $GLOBALS['LANG']->getLL('extension');
		$markersArray['###LLL:VERSION###']         = $GLOBALS['LANG']->getLL('status_version');
		$markersArray['###LLL:VERSIONCHECK###']    = $GLOBALS['LANG']->getLL('versioncheck');
		$markersArray['###LLL:VERSIONLAST###']     = $GLOBALS['LANG']->getLL('status_lastversion');
		$markersArray['###LLL:DOWNLOADS###']       = $GLOBALS['LANG']->getLL('downloads');
		$markersArray['###LLL:TABLES###']          = $GLOBALS['LANG']->getLL('extensions_tables');
		$markersArray['###LLL:TABLESINTEGRITY###'] = $GLOBALS['LANG']->getLL('extensions_tablesintegrity');
		$markersArray['###LLL:CONFINTEGRITY###']   = $GLOBALS['LANG']->getLL('extensions_confintegrity');
		$markersArray['###LLL:FILES###']           = $GLOBALS['LANG']->getLL('extensions_files');

		/********************************* specific developpment(s) *********************************/

		$markersArrayTemp = array();

		if (count($itemsDev) > 0) {
			foreach ($itemsDev as $itemKey => $itemValue) {
				if (t3lib_extMgm::isLoaded($itemKey)) {
					$markersArrayExtension = array();
					$extKey                = $itemKey;
					$extInfo               = $itemValue;
					$fdFile                = array();
					$updateStatements      = array();

					tx_additionalreports_util::getExtSqlUpdateStatements($em, $extKey, $extInfo, $fdFile, $updateStatements);

					$markersArrayExtension['###ICONEXT###']       = tx_additionalreports_util::getExtIcon($extKey);
					$markersArrayExtension['###EXTENSION###']     = $extKey;
					$markersArrayExtension['###EXTENSIONLINK###'] = '<a href="#" onclick="' . tx_additionalreports_util::goToModuleEm(
						$extKey
					) . '">';
					$markersArrayExtension['###EXTENSIONLINK###'] .= tx_additionalreports_util::getIconZoom() . '</a>';
					$markersArrayExtension['###VERSION###'] = $itemValue['EM_CONF']['version'];

					// show db
					$dumpTf1 = '';
					$dumpTf2 = '';
					if (count($fdFile) > 0) {
						$id      = 'sql' . $extKey;
						$dumpTf1 = count($fdFile) . ' ' . $GLOBALS['LANG']->getLL('extensions_tablesmodified');
						$dumpTf2 = tx_additionalreports_util::writePopUp(
							$id, $extKey, tx_additionalreports_util::viewArray($fdFile)
						);
					}
					$markersArrayExtension['###TABLES###']     = $dumpTf1;
					$markersArrayExtension['###TABLESLINK###'] = $dumpTf2;

					// need db update
					if (count($updateStatements) > 0) {
						$markersArrayExtension['###TABLESINTEGRITY###'] = '<span style="color:red;font-weight:bold;">' .
							$GLOBALS['LANG']->getLL('yes') . '</span>';
					} else {
						$markersArrayExtension['###TABLESINTEGRITY###'] = $GLOBALS['LANG']->getLL('no');
					}

					$markersArrayTemp[] = $markersArrayExtension;
				}
			}
			$markersArray['###REPORTS_EXTENSIONS_OBJECTDEV###'] = $template->renderAllTemplate(
				$markersArrayTemp, '###REPORTS_EXTENSIONS_OBJECTDEV###'
			);
		} else {
			$markersArray['###REPORTS_EXTENSIONS_OBJECTDEV###'] = $template->renderAllTemplate(
				array('###NORESULTS###' => $GLOBALS['LANG']->getLL('noresults')),
				'###REPORTS_EXTENSIONS_NORESULTS###'
			);
		}

		/********************************* unloaded extension(s) *********************************/

		$markersArrayTemp = array();

		if (count($itemsUnloaded) > 0) {
			foreach ($itemsUnloaded as $itemKey => $itemValue) {
				$markersArrayExtension = array();
				$extKey                = $itemKey;
				$extInfo               = $itemValue;
				$fdFile                = array();
				$updateStatements      = array();

				tx_additionalreports_util::getExtSqlUpdateStatements($em, $extKey, $extInfo, $fdFile, $updateStatements);

				$markersArrayExtension['###ICONEXT###']       = tx_additionalreports_util::getExtIcon($extKey);
				$markersArrayExtension['###EXTENSION###']     = $extKey;
				$markersArrayExtension['###EXTENSIONLINK###'] = '<a href="#" onclick="' . tx_additionalreports_util::goToModuleEm(
					$extKey
				) . '">';
				$markersArrayExtension['###EXTENSIONLINK###'] .= tx_additionalreports_util::getIconZoom() . '</a>';
				$markersArrayExtension['###VERSION###'] = $itemValue['EM_CONF']['version'];

				// show db
				$dumpTf1 = '';
				$dumpTf2 = '';
				if (count($fdFile) > 0) {
					$id      = 'sql' . $extKey;
					$dumpTf1 = count($fdFile) . ' ' . $GLOBALS['LANG']->getLL('extensions_tablesmodified');
					$dumpTf2 = tx_additionalreports_util::writePopUp($id, $extKey, tx_additionalreports_util::viewArray($fdFile));
				}
				$markersArrayExtension['###TABLES###']     = $dumpTf1;
				$markersArrayExtension['###TABLESLINK###'] = $dumpTf2;

				$markersArrayTemp[] = $markersArrayExtension;
			}
			$markersArray['###REPORTS_EXTENSIONS_OBJECTUNLOADED###'] = $template->renderAllTemplate(
				$markersArrayTemp, '###REPORTS_EXTENSIONS_OBJECTUNLOADED###'
			);
		} else {
			$markersArray['###REPORTS_EXTENSIONS_OBJECTUNLOADED###'] = $template->renderAllTemplate(
				array('###NORESULTS###' => $GLOBALS['LANG']->getLL('noresults')),
				'###REPORTS_EXTENSIONS_NORESULTS###'
			);
		}

		$addContent = '';
		$addContent .= $extensionsLoaded . ' ' . $GLOBALS['LANG']->getLL('extensions_extensions');
		$addContent .= '<br/>';
		$addContent .= $extensionsLoaded - count($itemsDev) . ' ' . $GLOBALS['LANG']->getLL('extensions_ter');
		$addContent .= '  /  ';
		$addContent .= $extensionsDev . ' ' . $GLOBALS['LANG']->getLL('extensions_dev');
		$addContent .= '<br/>';
		$addContent .= $extensionsToUpdate . ' ' . $GLOBALS['LANG']->getLL('extensions_toupdate');
		$addContent .= '  /  ';
		$addContent .= $extensionsModified . ' ' . $GLOBALS['LANG']->getLL('extensions_extensionsmodified');
		$addContentItem = tx_additionalreports_util::writeInformation(
			$GLOBALS['LANG']->getLL('pluginsmode5') . '<br/>' . $GLOBALS['LANG']->getLL('extensions_updateter') . '', $addContent
		);

		$content = $addContentItem . $template->renderAllTemplate($markersArray, '###REPORTS_EXTENSIONS###');

		return $content;
	}

	/**
	 * Generate the hooks report
	 *
	 * @return string HTML code
	 */
	public function displayHooks() {
		$template = new tx_additionalreports_templating();
		$template->initTemplate('typo3conf/ext/additional_reports/res/templates/hooks.html');

		$markersArray                        = array();
		$markersArray['###LLL:TITLE###']     = $GLOBALS['LANG']->getLL('hooks_core');
		$markersArray['###LLL:COREFILE###']  = $GLOBALS['LANG']->getLL('hooks_corefile');
		$markersArray['###LLL:NAME###']      = $GLOBALS['LANG']->getLL('hooks_name');
		$markersArray['###LLL:FILE###']      = $GLOBALS['LANG']->getLL('hooks_file');
		$markersArray['###LLL:TITLEEXT###']  = $GLOBALS['LANG']->getLL('hooks_extension');
		$markersArray['###LLL:EXTENSION###'] = $GLOBALS['LANG']->getLL('extension');
		$markersArray['###LLL:LINE###']      = $GLOBALS['LANG']->getLL('hooks_line');

		// core hooks
		$items = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'];

		if (count($items) > 0) {
			$markersArrayTemp = array();
			foreach ($items as $itemKey => $itemValue) {
				if (preg_match('/.*?\/.*?\.php/', $itemKey, $matches)) {
					foreach ($itemValue as $hookName => $hookList) {
						$markersArrayTemp[] = array(
							'###COREFILE###'  => $itemKey,
							'###NAME###'      => $hookName,
							'###FILE###'      => tx_additionalreports_util::viewArray($hookList)
						);
					}
				}
			}

			$markersArray['###REPORTS_HOOKS_OBJECT###'] = $template->renderAllTemplate(
				$markersArrayTemp, '###REPORTS_HOOKS_OBJECT###'
			);
		} else {
			$markersArray['###REPORTS_HOOKS_OBJECT###'] = $template->renderAllTemplate(
				array('###NORESULTS###' => $GLOBALS['LANG']->getLL('noresults')),
				'###REPORTS_HOOKS_NORESULTS###'
			);
		}

		// extension hooks (we read the temp_CACHED and look for $EXTCONF modification)
		$tempCached = tx_additionalreports_util::getCacheFilePrefix() . '_ext_localconf.php';
		$items      = array();
		if (is_file(PATH_site . 'typo3conf/' . $tempCached)) {
			$handle    = fopen(PATH_site . 'typo3conf/' . $tempCached, 'r');
			$extension = '';
			if ($handle) {
				while (!feof($handle)) {
					$buffer = fgets($handle);
					if ($extension != '') {
						if (preg_match("/\['EXTCONF'\]\['(.*?)'\](.*?)\s*=/", $buffer, $matches)) {
							if ($matches[1] != $extension) {
								$items[] = array($extension, $matches[1] . ' --> ' . $matches[2]);
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

		if (count($items) > 0) {
			$markersArrayTemp = array();
			foreach ($items as $itemKey => $itemValue) {
				$markersArrayTemp[] = array(
					'###EXTENSION###'  => $itemValue[0],
					'###LINE###'       => $itemValue[1]
				);
			}
			$markersArray['###REPORTS_HOOKS_OBJECTEXT###'] = $template->renderAllTemplate(
				$markersArrayTemp, '###REPORTS_HOOKS_OBJECTEXT###'
			);
		} else {
			$markersArray['###REPORTS_HOOKS_OBJECTEXT###'] = $template->renderAllTemplate(
				array('###NORESULTS###' => $GLOBALS['LANG']->getLL('noresults')),
				'###REPORTS_HOOKS_NORESULTS###'
			);
		}

		return $template->renderAllTemplate($markersArray, '###REPORTS_HOOKS###');
	}

	/**
	 * Generate the global status report
	 *
	 * @return string HTML code
	 */
	public function displayStatus() {
		$template = new tx_additionalreports_templating();
		$template->initTemplate('typo3conf/ext/additional_reports/res/templates/status.html');

		$markersArray = array();

		// TYPO3
		$content = tx_additionalreports_util::writeInformation(
			$GLOBALS['LANG']->getLL('status_sitename'), $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']
		);
		$content .= tx_additionalreports_util::writeInformation($GLOBALS['LANG']->getLL('status_version'), TYPO3_version);
		$content .= tx_additionalreports_util::writeInformation($GLOBALS['LANG']->getLL('status_path'), PATH_site);
		$content .= tx_additionalreports_util::writeInformation('TYPO3_db', TYPO3_db);
		$content .= tx_additionalreports_util::writeInformation('TYPO3_db_username', TYPO3_db_username);
		$content .= tx_additionalreports_util::writeInformation('TYPO3_db_host', TYPO3_db_host);
		if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path'] != '') {
			$cmd = t3lib_div::imageMagickCommand('convert', '-version');
			exec($cmd, $ret);
			$content .= tx_additionalreports_util::writeInformation(
				$GLOBALS['LANG']->getLL('status_im'), $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path'] . ' (' . $ret[0] . ')'
			);
		}
		$content .= tx_additionalreports_util::writeInformation(
			'forceCharset', $GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset']
		);
		$content .= tx_additionalreports_util::writeInformation(
			'setDBinit', $GLOBALS['TYPO3_CONF_VARS']['SYS']['setDBinit']
		);
		$content .= tx_additionalreports_util::writeInformation(
			'no_pconnect', $GLOBALS['TYPO3_CONF_VARS']['SYS']['no_pconnect']
		);
		$content .= tx_additionalreports_util::writeInformation(
			'displayErrors', $GLOBALS['TYPO3_CONF_VARS']['SYS']['displayErrors']
		);
		$content .= tx_additionalreports_util::writeInformation(
			'maxFileSize', $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize']
		);
		$extensions = explode(',', $GLOBALS['TYPO3_CONF_VARS']['EXT']['extList']);
		sort($extensions);
		foreach ($extensions as $aKey => $extension) {
			$extensions[$aKey] = $extension . ' (' . tx_additionalreports_util::getExtensionVersion($extension) . ')';
		}
		$content .= tx_additionalreports_util::writeInformationList(
			$GLOBALS['LANG']->getLL('status_loadedextensions'), $extensions
		);

		$markersArray['###TYPO3###'] = $content;

		// PHP
		$content = tx_additionalreports_util::writeInformation($GLOBALS['LANG']->getLL('status_version'), phpversion());
		$content .= tx_additionalreports_util::writeInformation('memory_limit', ini_get('memory_limit'));
		$content .= tx_additionalreports_util::writeInformation('max_execution_time', ini_get('max_execution_time'));
		$content .= tx_additionalreports_util::writeInformation('post_max_size', ini_get('post_max_size'));
		$content .= tx_additionalreports_util::writeInformation('upload_max_filesize', ini_get('upload_max_filesize'));
		$content .= tx_additionalreports_util::writeInformation('display_errors', ini_get('display_errors'));
		$content .= tx_additionalreports_util::writeInformation('error_reporting', ini_get('error_reporting'));
		if (function_exists('posix_getpwuid') && function_exists('posix_getgrgid')) {
			$apacheUser  = posix_getpwuid(posix_getuid());
			$apacheGroup = posix_getgrgid(posix_getgid());
			$content .= tx_additionalreports_util::writeInformation(
				'Apache user', $apacheUser['name'] . ' (' . $apacheUser['uid'] . ')'
			);
			$content .= tx_additionalreports_util::writeInformation(
				'Apache group', $apacheGroup['name'] . ' (' . $apacheGroup['gid'] . ')'
			);
		}
		$extensions = array_map('strtolower', get_loaded_extensions());
		natcasesort($extensions);
		$content .= tx_additionalreports_util::writeInformationList(
			$GLOBALS['LANG']->getLL('status_loadedextensions'), $extensions
		);
		$markersArray['###PHP###'] = $content;

		// Apache
		if (function_exists('apache_get_version') && function_exists('apache_get_modules')) {
			$extensions = apache_get_modules();
			natcasesort($extensions);
			$content = tx_additionalreports_util::writeInformation(
				$GLOBALS['LANG']->getLL('status_version'), apache_get_version()
			);
			$content .= tx_additionalreports_util::writeInformationList(
				$GLOBALS['LANG']->getLL('status_loadedextensions'), $extensions
			);
			$markersArray['###APACHE###'] = $content;
		} else {
			$markersArray['###APACHE###'] = $GLOBALS['LANG']->getLL('noresults');
		}

		// MySQL
		$content = tx_additionalreports_util::writeInformation('Version', mysql_get_server_info());
		$items   = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'default_character_set_name, default_collation_name',
			'information_schema.schemata',
			'schema_name = \'' . TYPO3_db . '\''
		);
		$content .= tx_additionalreports_util::writeInformation(
			'default_character_set_name', $items[0]['default_character_set_name']
		);
		$content .= tx_additionalreports_util::writeInformation('default_collation_name', $items[0]['default_collation_name']);
		$content .= tx_additionalreports_util::writeInformation(
			'query_cache', tx_additionalreports_util::getMySqlCacheInformations()
		);
		$content .= tx_additionalreports_util::writeInformation(
			'character_set', tx_additionalreports_util::getMySqlCharacterSet()
		);

		// TYPO3 database
		$items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'table_name, engine, table_collation, table_rows, ((data_length+index_length)/1024/1024) as "size"',
			'information_schema.tables',
			'table_schema = \'' . TYPO3_db . '\'', '', 'table_name'
		);

		$markersTablesList                = array();
		$markersTablesListObj             = array();
		$markersTablesList['###TITLE###'] = TYPO3_db . ' - ' . count($items) . ' tables';
		$size                             = 0;

		foreach ($items as $itemKey => $itemValue) {
			$markersTablesListObj[] = array(
				'###NAME###'          => $itemValue['table_name'],
				'###ENGINE###'        => $itemValue['engine'],
				'###COLLATION###'     => $itemValue['table_collation'],
				'###ROWS###'          => $itemValue['table_rows'],
				'###SIZE###'          => round($itemValue['size'], 2),
			);
			$size += round($itemValue['size'], 2);
		}

		$markersTablesList['###TABLESLIST_OBJECT###'] = $template->renderAllTemplate(
			$markersTablesListObj, '###TABLESLIST_OBJECT###'
		);
		$markersTablesList['###TOTALSIZE###']         = round($size, 2);
		$content .= $template->renderAllTemplate($markersTablesList, '###TABLESLIST###');
		$markersArray['###MYSQL###'] = $content;

		// Crontab
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
		$content                       = tx_additionalreports_util::writeInformation('Crontab', $crontabString);
		$markersArray['###CRONTAB###'] = $content;

		return $template->renderAllTemplate($markersArray, '###REPORTS_STATUS###');
	}

	/**
	 * Generate the plugins and ctypes report
	 *
	 * @return string HTML code
	 */
	public function displayPlugins() {
		$url = $this->baseURL;

		$template = new tx_additionalreports_templating();
		$template->initTemplate('typo3conf/ext/additional_reports/res/templates/plugins.html');

		$markersArray              = array();
		$markersArray['###URL###'] = $url;

		$markersArray['###CHECKEDPLUGINSMODE1###'] = ($this->display == 1) ? ' checked="checked"' : '';
		$markersArray['###LLL:MODE1###']           = $GLOBALS['LANG']->getLL('pluginsmode1');

		$markersArray['###CHECKEDPLUGINSMODE2###'] = ($this->display == 2) ? ' checked="checked"' : '';
		$markersArray['###LLL:MODE2###']           = $GLOBALS['LANG']->getLL('pluginsmode2');

		$markersArray['###CHECKEDPLUGINSMODE3###'] = ($this->display == 3) ? ' checked="checked"' : '';
		$markersArray['###LLL:MODE3###']           = $GLOBALS['LANG']->getLL('pluginsmode3');

		$markersArray['###CHECKEDPLUGINSMODE4###'] = ($this->display == 4) ? ' checked="checked"' : '';
		$markersArray['###LLL:MODE4###']           = $GLOBALS['LANG']->getLL('pluginsmode4');

		$markersArray['###CHECKEDPLUGINSMODE5###'] = ($this->display == 5) ? ' checked="checked"' : '';
		$markersArray['###LLL:MODE5###']           = $GLOBALS['LANG']->getLL('pluginsmode5');

		$markersArray['###CHECKEDPLUGINSMODE6###'] = ($this->display == 6) ? ' checked="checked"' : '';
		$markersArray['###LLL:MODE6###']           = $GLOBALS['LANG']->getLL('pluginsmode4hidden');

		$markersArray['###CHECKEDPLUGINSMODE7###'] = ($this->display == 7) ? ' checked="checked"' : '';
		$markersArray['###LLL:MODE7###']           = $GLOBALS['LANG']->getLL('pluginsmode3hidden');

		$markersArray['###LLL:CAUTION###'] = tx_additionalreports_util::writeInformation(
			$GLOBALS['LANG']->getLL('careful'),
			$GLOBALS['LANG']->getLL('carefuldesc')
		);

		$content = $template->renderAllTemplate($markersArray, '###REPORTS_PLUGINS_MENU###');

		switch ($this->display) {
			case 1 :
				$content .= self::getAllPlugins();
				break;
			case 2 :
				$content .= self::getAllCtypes();
				break;
			case 3 :
				$content .= self::getAllUsedCtypes();
				break;
			case 4 :
				$content .= self::getAllUsedPlugins();
				break;
			case 5 :
				$content .= self::getSummary();
				break;
			case 6 :
				$content .= self::getAllUsedPlugins(TRUE);
				break;
			case 7 :
				$content .= self::getAllUsedCtypes(TRUE);
				break;
			default:
				$content .= self::getSummary();
				break;
		}

		return $content;
	}

	/**
	 * Generate the plugins report
	 *
	 * @return string HTML code
	 */
	public function getAllPlugins() {
		$template = new tx_additionalreports_templating();
		$template->initTemplate('typo3conf/ext/additional_reports/res/templates/plugins.html');

		$content = '';

		$items                               = $GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'];
		$markersArray                        = array();
		$markersArray['###LLL:TITLE###']     = $GLOBALS['LANG']->getLL('pluginsmode1');
		$markersArray['###LLL:EXTENSION###'] = $GLOBALS['LANG']->getLL('extension');
		$markersArray['###LLL:PLUGIN###']    = $GLOBALS['LANG']->getLL('plugin');
		$markersArray['###LLL:EMINFO###']    = $GLOBALS['LANG']->getLL('eminfo');
		$markersArray['###LLL:USED###']      = $GLOBALS['LANG']->getLL('used');

		if (count($items) > 0) {
			$markersArrayTemp = array();
			foreach ($items as $itemKey => $itemValue) {
				if (trim($itemValue[1]) != '') {
					$markersTemp = array();

					preg_match('/EXT:(.*?)\//', $itemValue[0], $ext);
					preg_match('/^LLL:(EXT:.*?):(.*)/', $itemValue[0], $llfile);
					$localLang = t3lib_div::readLLfile($llfile[1], $GLOBALS['LANG']->lang);

					$markersTemp['###ICONEXT###']   = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . $itemValue[2];
					$markersTemp['###EXTENSION###'] = $ext[1];
					$markersTemp['###PLUGIN###']    = $GLOBALS['LANG']->getLLL(
						$llfile[2], $localLang
					) . ' (' . $itemValue[1] . ')';
					$markersTemp['###EMINFO###']    = '<a href="#" onclick="' . tx_additionalreports_util::goToModuleEm(
						$ext[1]
					) . '">';
					$markersTemp['###EMINFO###'] .= tx_additionalreports_util::getIconZoom() . '</a>';

					if (($count = tx_additionalreports_util::checkPluginIsUsed($itemValue[1], 'all')) > 0) {
						$markersTemp['###USED###']      = $GLOBALS['LANG']->getLL('yes') . ' (' . $count . ')';
						$markersTemp['###USEDCLASS###'] = ' typo3-message message-ok';
					} else {
						$markersTemp['###USED###']      = $GLOBALS['LANG']->getLL('no') . ' (' . $count . ')';
						$markersTemp['###USEDCLASS###'] = ' typo3-message message-error';
					}

					if (($count = tx_additionalreports_util::checkPluginIsUsed($itemValue[1], 'hidden')) > 0) {
						$markersTemp['###USEDHIDDEN###']      = $GLOBALS['LANG']->getLL('yes') . ' (' . $count . ')';
						$markersTemp['###USEDHIDDENCLASS###'] = ' typo3-message message-ok';
					} else {
						$markersTemp['###USEDHIDDEN###']      = $GLOBALS['LANG']->getLL('no') . ' (' . $count . ')';
						$markersTemp['###USEDHIDDENCLASS###'] = ' typo3-message message-error';
					}

					if (($count = tx_additionalreports_util::checkPluginIsUsed($itemValue[1], 'deleted')) > 0) {
						$markersTemp['###USEDDELETE###']      = $GLOBALS['LANG']->getLL('yes') . ' (' . $count . ')';
						$markersTemp['###USEDDELETECLASS###'] = ' typo3-message message-ok';
					} else {
						$markersTemp['###USEDDELETE###']      = $GLOBALS['LANG']->getLL('no') . ' (' . $count . ')';
						$markersTemp['###USEDDELETECLASS###'] = ' typo3-message message-error';
					}

					$markersArrayTemp[] = $markersTemp;
				}
			}
			$markersArray['###REPORTS_PLUGINS_ALLPLUGINS_OBJECT###'] = $template->renderAllTemplate(
				$markersArrayTemp, '###REPORTS_PLUGINS_ALLPLUGINS_OBJECT###'
			);
		} else {
			$markersArray['###REPORTS_PLUGINS_ALLPLUGINS_OBJECT###'] = $template->renderAllTemplate(
				array('###NORESULTS###' => $GLOBALS['LANG']->getLL('noresults')),
				'###REPORTS_PLUGINS_NORESULTS###'
			);
		}

		$content .= $template->renderAllTemplate($markersArray, '###REPORTS_PLUGINS_ALLPLUGINS###');

		return $content;
	}

	/**
	 * Generate the ctypes report
	 *
	 * @return string HTML code
	 */
	public function getAllCtypes() {
		$template = new tx_additionalreports_templating();
		$template->initTemplate('typo3conf/ext/additional_reports/res/templates/plugins.html');

		$content = '';

		$items                               = $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'];
		$markersArray                        = array();
		$markersArray['###LLL:TITLE###']     = $GLOBALS['LANG']->getLL('pluginsmode2');
		$markersArray['###LLL:EXTENSION###'] = $GLOBALS['LANG']->getLL('ctype');
		$markersArray['###LLL:USED###']      = $GLOBALS['LANG']->getLL('used');

		if (count($items) > 0) {
			$markersArrayTemp = array();
			foreach ($items as $itemKey => $itemValue) {
				if ($itemValue[1] != '--div--') {
					$markersTemp = array();

					preg_match('/^LLL:(EXT:.*?):(.*)/', $itemValue[0], $llfile);
					$localLang = t3lib_div::readLLfile($llfile[1], $GLOBALS['LANG']->lang);

					if ($itemValue[2] != '') {
						if (is_file(PATH_site . 'typo3/sysext/t3skin/icons/gfx/' . $itemValue[2])) {
							$markersTemp['###ICONEXT###'] = t3lib_div::getIndpEnv(
								'TYPO3_REQUEST_DIR'
							) . 'sysext/t3skin/icons/gfx/' . $itemValue[2];
						} elseif (preg_match('/^\.\./', $itemValue[2], $temp)) {
							$markersTemp['###ICONEXT###'] = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . $itemValue[2];
						} elseif (preg_match('/^EXT:(.*)$/', $itemValue[2], $temp)) {
							$markersTemp['###ICONEXT###'] = t3lib_div::getIndpEnv(
								'TYPO3_REQUEST_DIR'
							) . '../typo3conf/ext/' . $temp[1];
						}
					}

					$markersTemp['###EXTENSION###'] = $GLOBALS['LANG']->getLLL(
						$llfile[2], $localLang
					) . ' (' . $itemValue[1] . ')';

					if (($count = tx_additionalreports_util::checkCtypeIsUsed($itemValue[1], 'all')) > 0) {
						$markersTemp['###USED###']      = $GLOBALS['LANG']->getLL('yes') . ' (' . $count . ')';
						$markersTemp['###USEDCLASS###'] = ' typo3-message message-ok';
					} else {
						$markersTemp['###USED###']      = $GLOBALS['LANG']->getLL('no') . ' (' . $count . ')';
						$markersTemp['###USEDCLASS###'] = ' typo3-message message-error';
					}

					if (($count = tx_additionalreports_util::checkCtypeIsUsed($itemValue[1], 'hidden')) > 0) {
						$markersTemp['###USEDHIDDEN###']      = $GLOBALS['LANG']->getLL('yes') . ' (' . $count . ')';
						$markersTemp['###USEDHIDDENCLASS###'] = ' typo3-message message-ok';
					} else {
						$markersTemp['###USEDHIDDEN###']      = $GLOBALS['LANG']->getLL('no') . ' (' . $count . ')';
						$markersTemp['###USEDHIDDENCLASS###'] = ' typo3-message message-error';
					}

					if (($count = tx_additionalreports_util::checkCtypeIsUsed($itemValue[1], 'deleted')) > 0) {
						$markersTemp['###USEDDELETE###']      = $GLOBALS['LANG']->getLL('yes') . ' (' . $count . ')';
						$markersTemp['###USEDDELETECLASS###'] = ' typo3-message message-ok';
					} else {
						$markersTemp['###USEDDELETE###']      = $GLOBALS['LANG']->getLL('no') . ' (' . $count . ')';
						$markersTemp['###USEDDELETECLASS###'] = ' typo3-message message-error';
					}

					$markersArrayTemp[] = $markersTemp;
				}
			}
			$markersArray['###REPORTS_PLUGINS_ALLCTYPES_OBJECT###'] = $template->renderAllTemplate(
				$markersArrayTemp, '###REPORTS_PLUGINS_ALLCTYPES_OBJECT###'
			);
		} else {
			$markersArray['###REPORTS_PLUGINS_ALLCTYPES_OBJECT###'] = $template->renderAllTemplate(
				array('###NORESULTS###' => $GLOBALS['LANG']->getLL('noresults')),
				'###REPORTS_PLUGINS_NORESULTS###'
			);
		}

		$content .= $template->renderAllTemplate($markersArray, '###REPORTS_PLUGINS_ALLCTYPES###');

		return $content;
	}

	/**
	 * Generate the used plugins report
	 *
	 * @param boolean $displayHidden
	 * @return string HTML code
	 */
	public function getAllUsedPlugins($displayHidden = FALSE) {
		$template = new tx_additionalreports_templating();
		$template->initTemplate('typo3conf/ext/additional_reports/res/templates/plugins.html');
		$markersArray = array();

		$plugins       = array();
		$getFiltersCat = t3lib_div::_GP('filtersCat');

		foreach ($GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'] as $itemKey => $itemValue) {
			if (trim($itemValue[1]) != '') {
				$plugins[$itemValue[1]] = $itemValue;
			}
		}

		$addhidden = ($displayHidden === TRUE) ? '' : ' AND tt_content.hidden=0 AND pages.hidden=0 ';
		$addWhere  = (($getFiltersCat !== NULL) && ($getFiltersCat != 'all')) ?
			' AND tt_content.list_type=\'' . $getFiltersCat . '\'' :
			'';

		// html select cat
		$this->filtersCat .= tx_additionalreports_util::getAllDifferentPluginsSelect($addhidden, $getFiltersCat);

		// All items
		$items = tx_additionalreports_util::getAllPlugins($addhidden . $addWhere);

		// Page browser
		$pointer      = t3lib_div::_GP('pointer');
		$limit        = ($pointer !== NULL) ? $pointer . ',' . $this->nbElementsPerPage : '0,' . $this->nbElementsPerPage;
		$current      = ($pointer !== NULL) ? intval($pointer) : 0;
		$pageBrowser  = self::pluginsRenderListNavigation(count($items), $this->nbElementsPerPage, $current);
		$itemsBrowser = tx_additionalreports_util::getAllPlugins($addhidden . $addWhere, $limit);

		$markersArray['###PAGEBROWSER###']   = $pageBrowser;
		$markersArray['###LLL:TITLE###']     = $GLOBALS['LANG']->getLL('pluginsmode4');
		$markersArray['###LLL:EXTENSION###'] = $GLOBALS['LANG']->getLL('extension');
		$markersArray['###LLL:PLUGIN###']    = $GLOBALS['LANG']->getLL('plugin');
		$markersArray['###LLL:DOMAIN###']    = $GLOBALS['LANG']->getLL('domain');
		$markersArray['###LLL:PID###']       = $GLOBALS['LANG']->getLL('pid');
		$markersArray['###LLL:UID###']       = $GLOBALS['LANG']->getLL('uid');
		$markersArray['###LLL:PAGETITLE###'] = $GLOBALS['LANG']->getLL('pagetitle');
		$markersArray['###LLL:USEDTV###']    = $GLOBALS['LANG']->getLL('tvused');
		$markersArray['###LLL:PREVIEW###']   = $GLOBALS['LANG']->getLL('preview');

		if (t3lib_extMgm::isLoaded('templavoila') && class_exists('tx_templavoila_api')) {
			$markersArray['###LLL:PAGE###'] = 'Page TV';

		} else {
			$markersArray['###LLL:PAGE###'] = 'Page';
		}

		$markersArrayTemp = array();

		foreach ($itemsBrowser as $itemKey => $itemValue) {
			$markersExt = array();
			preg_match('/EXT:(.*?)\//', $plugins[$itemValue['list_type']][0], $ext);
			preg_match('/^LLL:(EXT:.*?):(.*)/', $plugins[$itemValue['list_type']][0], $llfile);
			$localLang = t3lib_div::readLLfile($llfile[1], $GLOBALS['LANG']->lang);
			$domain    = tx_additionalreports_util::getDomain($itemValue['pid']);

			if ($plugins[trim($ext[1])]) {
				$markersExt['###ICONEXT###'] = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . $plugins[trim($ext[1])][2];
			} else {
				if ($ext) {
					$markersExt['###ICONEXT###'] = tx_additionalreports_util::getExtIcon($ext[1]);
				} else {
					$markersExt['###ICONEXT###'] = '';
				}
			}

			$markersExt['###EXTENSION###'] = $ext[1];
			$markersExt['###PLUGIN###']    = $GLOBALS['LANG']->getLLL(
				$llfile[2], $localLang
			) . ' (' . $itemValue['list_type'] . ')';
			$markersExt['###DOMAIN###']    = tx_additionalreports_util::getIconDomain() . $domain;

			$iconPage    = ($itemValue['hiddenpages'] == 0)
				? tx_additionalreports_util::getIconPage()
				: tx_additionalreports_util::getIconPage(TRUE);
			$iconContent = ($itemValue['hiddentt_content'] == 0)
				? tx_additionalreports_util::getIconContent()
				: tx_additionalreports_util::getIconContent(TRUE);

			$markersExt['###PID###']       = $iconPage . ' ' . $itemValue['pid'];
			$markersExt['###UID###']       = $iconContent . ' ' . $itemValue['uid'];
			$markersExt['###PAGETITLE###'] = $itemValue['title'];

			if (t3lib_extMgm::isLoaded('templavoila') && class_exists('tx_templavoila_api')) {

				$linkAtt = array(
					'href'    => '#',
					'title'   => $GLOBALS['LANG']->getLL('switch'),
					'onclick' => tx_additionalreports_util::goToModuleList($itemValue['pid'])
				);

				$markersExt['###DB###'] = tx_additionalreports_util::generateLink(
					$linkAtt, tx_additionalreports_util::getIconWebList()
				);

				$linkAtt = array(
					'href'    => tx_additionalreports_util::goToModuleList($itemValue['pid'], TRUE),
					'target'  => '_blank',
					'title'   => $GLOBALS['LANG']->getLL('newwindow')
				);

				$markersExt['###DB###'] .= tx_additionalreports_util::generateLink(
					$linkAtt, tx_additionalreports_util::getIconWebList()
				);

				$linkAtt = array(
					'href'    => '#',
					'title'   => $GLOBALS['LANG']->getLL('switch'),
					'onclick' => tx_additionalreports_util::goToModulePageTv($itemValue['pid'])
				);

				$markersExt['###PAGE###'] = tx_additionalreports_util::generateLink(
					$linkAtt, tx_additionalreports_util::getIconWebPage()
				);

				$linkAtt = array(
					'href'    => tx_additionalreports_util::goToModulePageTv($itemValue['pid'], TRUE),
					'target'  => '_blank',
					'title'   => $GLOBALS['LANG']->getLL('newwindow')
				);

				$markersExt['###PAGE###'] .= tx_additionalreports_util::generateLink(
					$linkAtt, tx_additionalreports_util::getIconWebPage()
				);

				if (tx_additionalreports_util::isUsedInTv($itemValue['uid'], $itemValue['pid'])) {
					$markersExt['###USEDTV###']      = $GLOBALS['LANG']->getLL('yes');
					$markersExt['###USEDTVCLASS###'] = ' typo3-message message-ok';
				} else {
					$markersExt['###USEDTV###']      = $GLOBALS['LANG']->getLL('no');
					$markersExt['###USEDTVCLASS###'] = ' typo3-message message-error';
				}
			} else {
				$markersExt['###USEDTV###']           = '';
				$markersExt['###USEDTVCLASS###']      = '';
				$markersArray['###DISPLAY_USEDTV###'] = '';
				$markersExt['###DISPLAY_USEDTV###']   = '';

				$linkAtt = array(
					'href'    => '#',
					'title'   => $GLOBALS['LANG']->getLL('switch'),
					'onclick' => tx_additionalreports_util::goToModuleList($itemValue['pid'])
				);

				$markersExt['###DB###'] = tx_additionalreports_util::generateLink(
					$linkAtt, tx_additionalreports_util::getIconWebList()
				);

				$linkAtt = array(
					'href'    => tx_additionalreports_util::goToModuleList($itemValue['pid'], TRUE),
					'target'  => '_blank',
					'title'   => $GLOBALS['LANG']->getLL('newwindow')
				);

				$markersExt['###DB###'] .= tx_additionalreports_util::generateLink(
					$linkAtt, tx_additionalreports_util::getIconWebList()
				);

				$linkAtt = array(
					'href'    => '#',
					'title'   => $GLOBALS['LANG']->getLL('switch'),
					'onclick' => tx_additionalreports_util::goToModulePage($itemValue['pid'])
				);

				$markersExt['###PAGE###'] = tx_additionalreports_util::generateLink(
					$linkAtt, tx_additionalreports_util::getIconWebPage()
				);

				$linkAtt = array(
					'href'    => tx_additionalreports_util::goToModulePage($itemValue['pid'], TRUE),
					'target'  => '_blank',
					'title'   => $GLOBALS['LANG']->getLL('newwindow')
				);

				$markersExt['###PAGE###'] .= tx_additionalreports_util::generateLink(
					$linkAtt, tx_additionalreports_util::getIconWebPage()
				);
			}

			$markersExt['###PREVIEW###'] = '<a target="_blank" href="http://' . $domain . '/index.php?id=' . $itemValue['pid'] . '">';
			$markersExt['###PREVIEW###'] .= tx_additionalreports_util::getIconZoom();
			$markersExt['###PREVIEW###'] .= '</a>';

			$markersArrayTemp[] = $markersExt;
		}

		$markersArray['###REPORTS_PLUGINS_USEDPLUGINS_OBJECT###'] = $template->renderAllTemplate(
			$markersArrayTemp, '###REPORTS_PLUGINS_USEDPLUGINS_OBJECT###'
		);

		$content = $template->renderAllTemplate($markersArray, '###REPORTS_PLUGINS_USEDPLUGINS###');

		return $content;
	}

	/**
	 * Generate the used ctypes    report
	 *
	 * @param boolean $displayHidden
	 * @return string HTML code
	 */
	public function getAllUsedCtypes($displayHidden = FALSE) {
		$template = new tx_additionalreports_templating();
		$template->initTemplate('typo3conf/ext/additional_reports/res/templates/plugins.html');
		$markersArray = array();

		$ctypes        = array();
		$getFiltersCat = t3lib_div::_GP('filtersCat');

		foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $itemKey => $itemValue) {
			if ($itemValue[1] != '--div--') {
				$ctypes[$itemValue[1]] = $itemValue;
			}
		}

		$addhidden = ($displayHidden === TRUE) ? '' : ' AND tt_content.hidden=0 AND pages.hidden=0 ';
		$addWhere  = (($getFiltersCat !== NULL) && ($getFiltersCat != 'all')) ?
			' AND tt_content.CType=\'' . $getFiltersCat . '\'' :
			'';

		// html select cat
		$this->filtersCat .= tx_additionalreports_util::getAllDifferentCtypesSelect($addhidden, $getFiltersCat);

		// All items
		$items = tx_additionalreports_util::getAllCtypes($addhidden . $addWhere);

		// Page browser
		$pointer      = t3lib_div::_GP('pointer');
		$limit        = ($pointer !== NULL) ? $pointer . ',' . $this->nbElementsPerPage : '0,' . $this->nbElementsPerPage;
		$current      = ($pointer !== NULL) ? intval($pointer) : 0;
		$pageBrowser  = self::pluginsRenderListNavigation(count($items), $this->nbElementsPerPage, $current);
		$itemsBrowser = tx_additionalreports_util::getAllCtypes($addhidden . $addWhere, $limit);

		$markersArray['###PAGEBROWSER###']   = $pageBrowser;
		$markersArray['###LLL:TITLE###']     = $GLOBALS['LANG']->getLL('pluginsmode3');
		$markersArray['###LLL:CTYPE###']     = $GLOBALS['LANG']->getLL('ctype');
		$markersArray['###LLL:DOMAIN###']    = $GLOBALS['LANG']->getLL('domain');
		$markersArray['###LLL:PID###']       = $GLOBALS['LANG']->getLL('pid');
		$markersArray['###LLL:UID###']       = $GLOBALS['LANG']->getLL('uid');
		$markersArray['###LLL:PAGETITLE###'] = $GLOBALS['LANG']->getLL('pagetitle');
		$markersArray['###LLL:USEDTV###']    = $GLOBALS['LANG']->getLL('tvused');
		$markersArray['###LLL:PREVIEW###']   = $GLOBALS['LANG']->getLL('preview');

		if (t3lib_extMgm::isLoaded('templavoila') && class_exists('tx_templavoila_api')) {
			$markersArray['###LLL:PAGE###'] = 'Page TV';
		} else {
			$markersArray['###LLL:PAGE###'] = 'Page';
		}

		$markersArrayTemp = array();

		foreach ($itemsBrowser as $itemKey => $itemValue) {
			$markersExt = array();
			preg_match('/^LLL:(EXT:.*?):(.*)/', $ctypes[$itemValue['CType']][0], $llfile);
			$localLang = t3lib_div::readLLfile($llfile[1], $GLOBALS['LANG']->lang);
			$domain    = tx_additionalreports_util::getDomain($itemValue['pid']);

			if ($ctypes[$itemValue['CType']][2] != '') {
				if (is_file(PATH_site . 'typo3/sysext/t3skin/icons/gfx/' . $ctypes[$itemValue['CType']][2])) {
					$markersExt['###ICONEXT###'] = t3lib_div::getIndpEnv(
						'TYPO3_REQUEST_DIR'
					) . 'sysext/t3skin/icons/gfx/' . $ctypes[$itemValue['CType']][2];
				} elseif (preg_match('/^\.\./', $ctypes[$itemValue['CType']][2], $temp)) {
					$markersExt['###ICONEXT###'] = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . $ctypes[$itemValue['CType']][2];
				} elseif (preg_match('/^EXT:(.*)$/', $ctypes[$itemValue['CType']][2], $temp)) {
					$markersExt['###ICONEXT###'] = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . '../typo3conf/ext/' . $temp[1];
				}
			}

			$markersExt['###CTYPE###']  = $GLOBALS['LANG']->getLLL($llfile[2], $localLang) . ' (' . $itemValue['CType'] . ')';
			$markersExt['###DOMAIN###'] = tx_additionalreports_util::getIconDomain() . $domain;

			$iconPage    = ($itemValue['hiddenpages'] == 0)
				? tx_additionalreports_util::getIconPage()
				: tx_additionalreports_util::getIconPage(TRUE);
			$iconContent = ($itemValue['hiddentt_content'] == 0)
				? tx_additionalreports_util::getIconContent()
				: tx_additionalreports_util::getIconContent(TRUE);

			$markersExt['###PID###']       = $iconPage . ' ' . $itemValue['pid'];
			$markersExt['###UID###']       = $iconContent . ' ' . $itemValue['uid'];
			$markersExt['###PAGETITLE###'] = $itemValue['title'];

			if (t3lib_extMgm::isLoaded('templavoila') && class_exists('tx_templavoila_api')) {

				$linkAtt = array(
					'href'    => '#',
					'title'   => $GLOBALS['LANG']->getLL('switch'),
					'onclick' => tx_additionalreports_util::goToModuleList($itemValue['pid'])
				);

				$markersExt['###DB###'] = tx_additionalreports_util::generateLink(
					$linkAtt, tx_additionalreports_util::getIconWebList()
				);

				$linkAtt = array(
					'href'    => tx_additionalreports_util::goToModuleList($itemValue['pid'], TRUE),
					'target'  => '_blank',
					'title'   => $GLOBALS['LANG']->getLL('newwindow')
				);

				$markersExt['###DB###'] .= tx_additionalreports_util::generateLink(
					$linkAtt, tx_additionalreports_util::getIconWebList()
				);

				$linkAtt = array(
					'href'    => '#',
					'title'   => $GLOBALS['LANG']->getLL('switch'),
					'onclick' => tx_additionalreports_util::goToModulePageTv($itemValue['pid'])
				);

				$markersExt['###PAGE###'] = tx_additionalreports_util::generateLink(
					$linkAtt, tx_additionalreports_util::getIconWebPage()
				);

				$linkAtt = array(
					'href'    => tx_additionalreports_util::goToModulePageTv($itemValue['pid'], TRUE),
					'target'  => '_blank',
					'title'   => $GLOBALS['LANG']->getLL('newwindow')
				);

				$markersExt['###PAGE###'] .= tx_additionalreports_util::generateLink(
					$linkAtt, tx_additionalreports_util::getIconWebPage()
				);

				if (tx_additionalreports_util::isUsedInTv($itemValue['uid'], $itemValue['pid'])) {
					$markersExt['###USEDTV###']      = $GLOBALS['LANG']->getLL('yes');
					$markersExt['###USEDTVCLASS###'] = ' typo3-message message-ok';
				} else {
					$markersExt['###USEDTV###']      = $GLOBALS['LANG']->getLL('no');
					$markersExt['###USEDTVCLASS###'] = ' typo3-message message-error';
				}
			} else {
				$markersExt['###USEDTV###']           = '';
				$markersExt['###USEDTVCLASS###']      = '';
				$markersArray['###DISPLAY_USEDTV###'] = '';
				$markersExt['###DISPLAY_USEDTV###']   = '';

				$linkAtt = array(
					'href'    => '#',
					'title'   => $GLOBALS['LANG']->getLL('switch'),
					'onclick' => tx_additionalreports_util::goToModuleList($itemValue['pid'])
				);

				$markersExt['###DB###'] = tx_additionalreports_util::generateLink(
					$linkAtt, tx_additionalreports_util::getIconWebList()
				);

				$linkAtt = array(
					'href'    => tx_additionalreports_util::goToModuleList($itemValue['pid'], TRUE),
					'target'  => '_blank',
					'title'   => $GLOBALS['LANG']->getLL('newwindow')
				);

				$markersExt['###DB###'] .= tx_additionalreports_util::generateLink(
					$linkAtt, tx_additionalreports_util::getIconWebList()
				);

				$linkAtt = array(
					'href'    => '#',
					'title'   => $GLOBALS['LANG']->getLL('switch'),
					'onclick' => tx_additionalreports_util::goToModulePage($itemValue['pid'])
				);

				$markersExt['###PAGE###'] = tx_additionalreports_util::generateLink(
					$linkAtt, tx_additionalreports_util::getIconWebPage()
				);

				$linkAtt = array(
					'href'    => tx_additionalreports_util::goToModulePage($itemValue['pid'], TRUE),
					'target'  => '_blank',
					'title'   => $GLOBALS['LANG']->getLL('newwindow')
				);

				$markersExt['###PAGE###'] .= tx_additionalreports_util::generateLink(
					$linkAtt, tx_additionalreports_util::getIconWebPage()
				);
			}

			$markersExt['###PREVIEW###'] = '<a target="_blank" href="http://' . $domain . '/index.php?id=' . $itemValue['pid'] . '">';
			$markersExt['###PREVIEW###'] .= tx_additionalreports_util::getIconZoom();
			$markersExt['###PREVIEW###'] .= '</a>';

			$markersArrayTemp[] = $markersExt;
		}
		$markersArray['###REPORTS_PLUGINS_USEDCTYPES_OBJECT###'] = $template->renderAllTemplate(
			$markersArrayTemp, '###REPORTS_PLUGINS_USEDCTYPES_OBJECT###'
		);

		$content = $template->renderAllTemplate($markersArray, '###REPORTS_PLUGINS_USEDCTYPES###');

		return $content;
	}

	/**
	 * Generate the summary of the plugins and ctypes report
	 *
	 * @return string HTML code
	 */
	public function getSummary() {
		$template = new tx_additionalreports_templating();
		$template->initTemplate('typo3conf/ext/additional_reports/res/templates/plugins.html');
		$markersArray = array();

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

		$itemsCount = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'COUNT( tt_content.uid ) as "nb"', 'tt_content,pages',
			'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.hidden=0 ' .
				'AND tt_content.deleted=0 AND pages.hidden=0 AND pages.deleted=0'
		);

		$items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'tt_content.CType,tt_content.list_type,count(*) as "nb"',
			'tt_content,pages',
			'tt_content.pid=pages.uid AND pages.pid>=0 AND tt_content.hidden=0 ' .
				'AND tt_content.deleted=0 AND pages.hidden=0 AND pages.deleted=0',
			'tt_content.CType,tt_content.list_type',
			'nb DESC'
		);


		$markersArray['###LLL:TITLE###']      = $GLOBALS['LANG']->getLL('pluginsmode5');
		$markersArray['###LLL:CONTENT###']    = $GLOBALS['LANG']->getLL('content');
		$markersArray['###LLL:REFERENCES###'] = $GLOBALS['LANG']->getLL('references');

		$markersArrayTemp = array();

		foreach ($items as $itemKey => $itemValue) {
			$markersTemp = array();

			if ($itemValue['CType'] == 'list') {
				preg_match('/EXT:(.*?)\//', $plugins[$itemValue['list_type']][0], $ext);
				preg_match('/^LLL:(EXT:.*?):(.*)/', $plugins[$itemValue['list_type']][0], $llfile);
				$localLang = t3lib_div::readLLfile($llfile[1], $GLOBALS['LANG']->lang);
				if ($plugins[$itemValue['list_type']][2]) {
					$markersTemp['###ICONEXT###'] = t3lib_div::getIndpEnv(
						'TYPO3_REQUEST_DIR'
					) . $plugins[$itemValue['list_type']][2];
				} else {
					$markersTemp['###ICONEXT###'] = '';
				}
				$markersTemp['###CONTENT###'] = $GLOBALS['LANG']->getLLL(
					$llfile[2], $localLang
				) . ' (' . $itemValue['list_type'] . ')';
			} else {
				preg_match('/^LLL:(EXT:.*?):(.*)/', $ctypes[$itemValue['CType']][0], $llfile);
				$localLang = t3lib_div::readLLfile($llfile[1], $GLOBALS['LANG']->lang);
				if (is_file(PATH_site . '/typo3/sysext/t3skin/icons/gfx/' . $ctypes[$itemValue['CType']][2])) {
					$markersTemp['###ICONEXT###'] = t3lib_div::getIndpEnv(
						'TYPO3_REQUEST_DIR'
					) . 'sysext/t3skin/icons/gfx/' . $ctypes[$itemValue['CType']][2];
				} elseif (preg_match('/^\.\./', $ctypes[$itemValue['CType']][2], $temp)) {
					$markersTemp['###ICONEXT###'] = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . $ctypes[$itemValue['CType']][2];
				} elseif (preg_match('/^EXT:(.*)$/', $ctypes[$itemValue['CType']][2], $temp)) {
					$markersTemp['###ICONEXT###'] = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . '../typo3conf/ext/' . $temp[1];
				} else {
					$markersTemp['###ICONEXT###'] = '';
				}
				$markersTemp['###CONTENT###'] = $GLOBALS['LANG']->getLLL(
					$llfile[2], $localLang
				) . ' (' . $itemValue['CType'] . ')';
			}

			$markersTemp['###REFERENCES###'] = $itemValue['nb'];
			$markersTemp['###POURC###']      = round((($itemValue['nb'] * 100) / $itemsCount[0]['nb']), 2);

			$markersArrayTemp[] = $markersTemp;
		}

		$markersArray['###REPORTS_PLUGINS_SUMMARY_OBJECT###'] = $template->renderAllTemplate(
			$markersArrayTemp, '###REPORTS_PLUGINS_SUMMARY_OBJECT###'
		);

		$content = $template->renderAllTemplate($markersArray, '###REPORTS_PLUGINS_SUMMARY###');

		return $content;
	}

	/**
	 * Creates a page browser for tables with many records
	 *
	 * @param int $totalItems
	 * @param int $iLimit
	 * @param int $firstElementNumber
	 * @return string
	 */
	public function pluginsRenderListNavigation($totalItems, $iLimit, $firstElementNumber) {
		$totalPages    = ceil($totalItems / $iLimit);
		$currentPage   = floor(($firstElementNumber + 1) / $iLimit) + 1;
		$content       = '';
		$returnContent = '';

		if ($totalPages >= 1) {
			$first       = $previous = $next = $last = $reload = '';
			$listUrlOrig = $this->baseURL . '&display=' . $this->display;
			$listUrl     = $this->baseURL . '&display=' . $this->display . '&nbPerPage=' . $this->nbElementsPerPage;

			if (($getFiltersCat = t3lib_div::_GP('filtersCat')) !== NULL) {
				$listUrl .= '&filtersCat=' . $getFiltersCat;
			}

			if (($orderby = t3lib_div::_GP('orderby')) !== NULL) {
				$listUrl .= '&orderby=' . $orderby;
			}

			// First
			if ($currentPage > 1) {
				$labelFirst = $GLOBALS['LANG']->getLL('first');
				$first      = '<a href="' . $listUrl . '&pointer=0">';
				$first .= '<img width="16" height="16" title="' . $labelFirst . '" alt="' . $labelFirst . '" ';
				$first .= 'src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR');
				$first .= '../typo3conf/ext/additional_reports/res/images/control_first.gif"></a>';
			} else {
				$first = '<img width="16" height="16" title="" alt="" ';
				$first .= 'src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR');
				$first .= '../typo3conf/ext/additional_reports/res/images/control_first_disabled.gif">';
			}

			// Previous
			if (($currentPage - 1) > 0) {
				$labelPrevious = $GLOBALS['LANG']->getLL('previous');
				$previous      = '<a href="' . $listUrl . '&pointer=' . (($currentPage - 2) * $iLimit) . '">';
				$previous .= '<img width="16" height="16" title="' . $labelPrevious . '" alt="' . $labelPrevious . '" ';
				$previous .= 'src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR');
				$previous .= '../typo3conf/ext/additional_reports/res/images/control_previous.gif"></a>';
			} else {
				$previous = '<img width="16" height="16" title="" alt="" ';
				$previous .= 'src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR');
				$previous .= '../typo3conf/ext/additional_reports/res/images/control_previous_disabled.gif">';
			}

			// Next
			if (($currentPage + 1) <= $totalPages) {
				$labelNext = $GLOBALS['LANG']->getLL('next');
				$next      = '<a href="' . $listUrl . '&pointer=' . (($currentPage) * $iLimit) . '">';
				$next .= '<img width="16" height="16" title="' . $labelNext . '" alt="' . $labelNext . '" ';
				$next .= 'src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR');
				$next .= '../typo3conf/ext/additional_reports/res/images/control_next.gif"></a>';
			} else {
				$next = '<img width="16" height="16" title="" alt="" ';
				$next .= 'src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR');
				$next .= '../typo3conf/ext/additional_reports/res/images/control_next_disabled.gif">';
			}

			// Last
			if ($currentPage != $totalPages) {
				$labelLast = $GLOBALS['LANG']->getLL('last');
				$last      = '<a href="' . $listUrl . '&pointer=' . (($totalPages - 1) * $iLimit) . '">';
				$last .= '<img width="16" height="16" title="' . $labelLast . '" alt="' . $labelLast . '" ';
				$last .= 'src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR');
				$last .= '../typo3conf/ext/additional_reports/res/images/control_last.gif"></a>';
			} else {
				$last = '<img width="16" height="16" title="" alt="" ';
				$last .= 'src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR');
				$last .= '../typo3conf/ext/additional_reports/res/images/control_last_disabled.gif">';
			}

			$pageNumberInput = '<span>' . $currentPage . '</span>';
			$pageIndicator   = '<span class="pageIndicator">';
			$pageIndicator .= sprintf($GLOBALS['LANG']->getLL('pageIndicator'), $pageNumberInput, $totalPages) . '</span>';

			if ($totalItems > ($firstElementNumber + $iLimit)) {
				$lastElementNumber = $firstElementNumber + $iLimit;
			} else {
				$lastElementNumber = $totalItems;
			}

			$rangeIndicator = '<span class="pageIndicator">';
			$rangeIndicator .= sprintf($GLOBALS['LANG']->getLL('rangeIndicator'), $firstElementNumber + 1, $lastElementNumber);
			$rangeIndicator .= ' / ' . $totalItems . '</span>';

			// nb per page, filter and reload
			$reload = '<input type="text" name="nbPerPage" id="nbPerPage" size="5" value="' . $this->nbElementsPerPage . '"/> / page ';

			if ($getFiltersCat !== NULL) {
				$reload .= '<a href="#"  onClick="jumpToUrl(\'' . $listUrlOrig;
				$reload .= '&nbPerPage=\'+document.getElementById(\'nbPerPage\').value+\'&filtersCat=' . $getFiltersCat . '\');">';
			} else {
				$reload .= '<a href="#"  onClick="jumpToUrl(\'' . $listUrlOrig;
				$reload .= '&nbPerPage=\'+document.getElementById(\'nbPerPage\').value);">';
			}

			$reload .= '<img width="16" height="16" title="" alt="" src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR');
			$reload .= '../typo3conf/ext/additional_reports/res/images/refresh_n.gif"></a>';

			if ($this->filtersCat != '') {
				$reload .= '<span class="bar">&nbsp;</span>';
				$reload .= $GLOBALS['LANG']->getLL('filterByCat');
				$reload .= '&nbsp' . $this->filtersCat;
				$reload .= '<a href="#"  onClick="jumpToUrl(\'' . $listUrlOrig;
				$reload .= '&nbPerPage=\'+document.getElementById(\'nbPerPage\').value+\'';
				$reload .= '&filtersCat=\'+document.getElementById(\'filtersCat\').value);">';
				$reload .= '&nbsp;<img width="16" height="16" title="" alt="" src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR');
				$reload .= '../typo3conf/ext/additional_reports/res/images/refresh_n.gif"></a>';
			}

			$content .= '<div id="typo3-dblist-pagination">' . $first . $previous . '<span class="bar">&nbsp;</span>';
			$content .= $rangeIndicator . '<span class="bar">&nbsp;</span>' . $pageIndicator . '<span class="bar">&nbsp;</span>';
			$content .= $next . $last . '<span class="bar">&nbsp;</span>' . $reload . '</div>';

			$returnContent = $content;
		}
		return $returnContent;
	}

	/**
	 * Generate the realurl report
	 *
	 * @return string HTML code
	 */
	public function displayRealUrlErrors() {
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
		$query            = array();
		$query['SELECT']  = 'url_hash,url,error,last_referer,counter,cr_date,tstamp';
		$query['FROM']    = 'tx_realurl_errorlog';
		$query['WHERE']   = '';
		$query['GROUPBY'] = '';
		$query['ORDERBY'] = 'counter DESC';
		$query['LIMIT']   = '';

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
		$pointer      = t3lib_div::_GP('pointer');
		$limit        = ($pointer !== NULL) ? $pointer . ',' . $this->nbElementsPerPage : '0,' . $this->nbElementsPerPage;
		$current      = ($pointer !== NULL) ? intval($pointer) : 0;
		$pageBrowser  = self::pluginsRenderListNavigation(count($items), $this->nbElementsPerPage, $current);
		$itemsBrowser = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			$query['SELECT'],
			$query['FROM'],
			$query['WHERE'],
			$query['GROUPBY'],
			$query['ORDERBY'],
			$limit
		);

		$template = new tx_additionalreports_templating();
		$template->initTemplate('typo3conf/ext/additional_reports/res/templates/realurlerrors.html');
		$markersArray = array();

		$markersArray['###PAGEBROWSER###']      = '';
		$markersArray['###LLL:TITLE###']        = $GLOBALS['LANG']->getLL('realurlerrors_description');
		$markersArray['###LLL:ERROR###']        = $GLOBALS['LANG']->getLL('error');
		$markersArray['###LLL:COUNTER###']      = $GLOBALS['LANG']->getLL('counter');
		$markersArray['###LLL:CRDATE###']       = $GLOBALS['LANG']->getLL('crdate');
		$markersArray['###LLL:TSTAMP###']       = $GLOBALS['LANG']->getLL('tstamp');
		$markersArray['###LLL:LAST_REFERER###'] = $GLOBALS['LANG']->getLL('last_referer');

		if (count($itemsBrowser) > 0) {
			$markersArray['###PAGEBROWSER###'] = $pageBrowser;

			foreach ($itemsBrowser as $itemKey => $itemValue) {
				$actionUrl = $this->baseURL . '&cmd=delete&delete=' . $itemValue['url_hash'];
				$action    = '<a href="' . $actionUrl . '">';
				$action .= '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR');
				$action .= 'sysext/t3skin/icons/gfx/garbage.gif"/></a>';

				$markersArrayTemp[] = array(
					'###FLUSH###'        => $action,
					'###URL###'          => $itemValue['url'],
					'###ERROR###'        => $itemValue['error'],
					'###COUNTER###'      => $itemValue['counter'],
					'###CRDATE###'       => date('d/m/Y H:i:s', $itemValue['cr_date']),
					'###TSTAMP###'       => date('d/m/Y H:i:s', $itemValue['tstamp']),
					'###LAST_REFERER###' => $itemValue['last_referer'],
				);
			}

			$markersArray['###REPORTS_REALURLERRORS_OBJECT###'] = $template->renderAllTemplate(
				$markersArrayTemp, '###REPORTS_REALURLERRORS_OBJECT###'
			);

		} else {
			$markersArray['###REPORTS_REALURLERRORS_OBJECT###'] = $template->renderAllTemplate(
				array('###NORESULTS###' => $GLOBALS['LANG']->getLL('noresults')),
				'###REPORTS_REALURLERRORS_NORESULTS###'
			);
		}

		return $template->renderAllTemplate($markersArray, '###REPORTS_REALURLERRORS###');
	}

	/**
	 * Generate the log error report
	 *
	 * @return string HTML code
	 */
	public function displayLogErrors() {

		// query
		$query            = array();
		$query['SELECT']  = 'COUNT(*) AS "nb",details,MAX(tstamp) as "tstamp"';
		$query['FROM']    = 'sys_log';
		$query['WHERE']   = 'error>0';
		$query['GROUPBY'] = 'details';
		$query['ORDERBY'] = 'nb DESC,tstamp DESC';
		$query['LIMIT']   = '';

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
		$pointer      = t3lib_div::_GP('pointer');
		$limit        = ($pointer !== NULL) ? $pointer . ',' . $this->nbElementsPerPage : '0,' . $this->nbElementsPerPage;
		$current      = ($pointer !== NULL) ? intval($pointer) : 0;
		$pageBrowser  = self::pluginsRenderListNavigation(count($items), $this->nbElementsPerPage, $current);
		$itemsBrowser = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			$query['SELECT'],
			$query['FROM'],
			$query['WHERE'],
			$query['GROUPBY'],
			$query['ORDERBY'],
			$limit
		);

		$content = tx_additionalreports_util::writeInformation(
			$GLOBALS['LANG']->getLL('flushalllog'), 'DELETE FROM sys_log WHERE error>0;'
		);

		$template = new tx_additionalreports_templating();
		$template->initTemplate('typo3conf/ext/additional_reports/res/templates/logerrors.html');
		$markersArray = array();

		$markersArray['###PAGEBROWSER###'] = '';
		$markersArray['###LLL:TITLE###']   = $GLOBALS['LANG']->getLL('logerrors_description');
		$markersArray['###LLL:ERROR###']   = $GLOBALS['LANG']->getLL('error');

		$markersArray['###LLL:COUNTER###'] = $GLOBALS['LANG']->getLL('counter');
		$markersArray['###LLL:COUNTER###'] .= '&nbsp;&nbsp;<a href="' . $this->baseURL . '&orderby=nb%20DESC,tstamp%20DESC">';
		$markersArray['###LLL:COUNTER###'] .= '<img alt="" src="' . t3lib_div::getIndpEnv(
			'TYPO3_REQUEST_DIR'
		) . 'sysext/t3skin/icons/gfx/reddown.gif"></a>';
		$markersArray['###LLL:COUNTER###'] .= '&nbsp;&nbsp;<a href="' . $this->baseURL . '&orderby=nb%20ASC,tstamp%20DESC">';
		$markersArray['###LLL:COUNTER###'] .= '<img alt="" src="' . t3lib_div::getIndpEnv(
			'TYPO3_REQUEST_DIR'
		) . 'sysext/t3skin/icons/gfx/redup.gif"></a>';

		$markersArray['###LLL:TSTAMP###'] = $GLOBALS['LANG']->getLL('tstamp');
		$markersArray['###LLL:TSTAMP###'] .= '&nbsp;&nbsp;<a href="' . $this->baseURL . '&orderby=tstamp%20DESC,nb%20DESC">';
		$markersArray['###LLL:TSTAMP###'] .= '<img width="7" height="4" alt="" src="' . t3lib_div::getIndpEnv(
			'TYPO3_REQUEST_DIR'
		) . 'sysext/t3skin/icons/gfx/reddown.gif"></a>';
		$markersArray['###LLL:TSTAMP###'] .= '&nbsp;&nbsp;<a href="' . $this->baseURL . '&orderby=tstamp%20ASC,nb%20DESC">';
		$markersArray['###LLL:TSTAMP###'] .= '<img width="7" height="4" alt="" src="' . t3lib_div::getIndpEnv(
			'TYPO3_REQUEST_DIR'
		) . 'sysext/t3skin/icons/gfx/redup.gif"></a>';

		if (count($itemsBrowser) > 0) {

			$markersArray['###PAGEBROWSER###'] = $pageBrowser;

			$markersArrayTemp = array();

			foreach ($itemsBrowser as $itemKey => $itemValue) {
				$deleteStatement = '<br/><img src="' . t3lib_div::getIndpEnv(
					'TYPO3_REQUEST_DIR'
				) . 'sysext/t3skin/icons/gfx/garbage.gif"/>  DELETE FROM sys_log WHERE error>0 AND details = "' . htmlentities(
					mysql_real_escape_string($itemValue['details'])
				) . '";';

				$markersArrayTemp[] = array(
					'###COUNTER###' => $itemValue['nb'],
					'###TSTAMP###'  => date('d/m/Y H:i:s', $itemValue['tstamp']),
					'###ERROR###'   => htmlentities($itemValue['details']) . $deleteStatement
				);
			}

			$markersArray['###REPORTS_LOGERRORS_OBJECT###'] = $template->renderAllTemplate(
				$markersArrayTemp, '###REPORTS_LOGERRORS_OBJECT###'
			);

		} else {
			$markersArray['###REPORTS_LOGERRORS_OBJECT###'] = $template->renderAllTemplate(
				array('###NORESULTS###' => $GLOBALS['LANG']->getLL('noresults')),
				'###REPORTS_LOGERRORS_NORESULTS###'
			);
		}

		$content .= $template->renderAllTemplate($markersArray, '###REPORTS_LOGERRORS###');

		return $content;
	}

	/**
	 * Generate the website conf report
	 *
	 * @return string HTML code
	 */
	public function displayWebsitesConf() {
		$template = new tx_additionalreports_templating();
		$template->initTemplate('typo3conf/ext/additional_reports/res/templates/websiteconf.html');
		$markersArray = array();

		$items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'uid, title',
			'pages',
			'is_siteroot = 1 AND deleted = 0 AND hidden = 0 AND pid != -1',
			'', '', '',
			'uid'
		);

		$markersArray['###LLL:TITLE###']     = $GLOBALS['LANG']->getLL('websitesconf_description');
		$markersArray['###LLL:PID###']       = $GLOBALS['LANG']->getLL('pid');
		$markersArray['###LLL:PAGETITLE###'] = $GLOBALS['LANG']->getLL('pagetitle');
		$markersArray['###LLL:DOMAINS###']   = $GLOBALS['LANG']->getLL('domains');

		if (!empty($items)) {
			$markersArrayTemp = array();
			foreach ($items as $itemKey => $itemValue) {
				$markersObj = array();

				$domainRecords = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'uid, pid, domainName',
					'sys_domain',
					'pid IN(' . $itemValue['uid'] . ') AND hidden=0',
					'',
					'sorting'
				);

				$markersObj['###PID###']       = $itemValue['uid'];
				$markersObj['###PAGETITLE###'] = tx_additionalreports_util::getIconPage() . $itemValue['title'];
				$markersObj['###DOMAINS###']   = '';
				$markersObj['###TEMPLATE###']  = '';

				foreach ($domainRecords as $domain) {
					$markersObj['###DOMAINS###'] .= tx_additionalreports_util::getIconDomain() . $domain['domainName'] . '<br/>';
				}

				$templates = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
					'uid,title,root',
					'sys_template',
					'pid IN(' . $itemValue['uid'] . ') AND deleted=0 AND hidden=0',
					'',
					'sorting'
				);


				foreach ($templates as $templateObj) {
					$markersObj['###TEMPLATE###'] .= '<img src="' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR');
					$markersObj['###TEMPLATE###'] .= 'sysext/t3skin/icons/gfx/i/template.gif"/> ' . $templateObj['title'] . ' ';
					$markersObj['###TEMPLATE###'] .= '[uid=' . $templateObj['uid'] . ',root=' . $templateObj['root'] . ']<br/>';
				}

				// baseurl
				$tmpl           = t3lib_div::makeInstance('t3lib_tsparser_ext');
				$tmpl->tt_track = 0;
				$tmpl->init();
				$tmpl->runThroughTemplates(tx_additionalreports_util::getRootLine($itemValue['uid']), 0);
				$tmpl->generateConfig();
				$markersObj['###BASEURL###'] = $tmpl->setup['config.']['baseURL'];

				// count pages
				$list                              = tx_additionalreports_util::getTreeList($itemValue['uid'], 99, 0, '1=1');
				$listArray                         = explode(',', $list);
				$markersObj['###PAGES###']         = (count($listArray) - 1);
				$markersObj['###PAGESHIDDEN###']   = (tx_additionalreports_util::getCountPagesUids($list, 'hidden=1'));
				$markersObj['###PAGESNOSEARCH###'] = (tx_additionalreports_util::getCountPagesUids($list, 'no_search=1'));

				$markersArrayTemp[] = $markersObj;
			}

			$markersArray['###REPORTS_WEBSITE_OBJECT###'] = $template->renderAllTemplate(
				$markersArrayTemp, '###REPORTS_WEBSITE_OBJECT###'
			);
		} else {
			$markersArray['###REPORTS_WEBSITE_OBJECT###'] = $template->renderAllTemplate(
				array('###NORESULTS###' => $GLOBALS['LANG']->getLL('noresults')),
				'###REPORTS_WEBSITE_NORESULTS###'
			);
		}

		return $template->renderAllTemplate($markersArray, '###REPORTS_WEBSITE###');
	}

	/**
	 * Generate the dbcheck report
	 *
	 * @return string HTML code
	 */
	public function displayDbCheck() {
		$template = new tx_additionalreports_templating();
		$template->initTemplate('typo3conf/ext/additional_reports/res/templates/dbcheck.html');

		$sqlStatements = tx_additionalreports_util::getSqlUpdateStatements();
		$content       = '';

		if (!empty($sqlStatements['update']['add'])) {
			$markersArray                    = array();
			$markersArray['###LLL:TITLE###'] = 'Add fields';
			$markersArrayTemp                = array();
			foreach ($sqlStatements['update']['add'] as $itemKey => $itemValue) {
				$markersArrayTemp[] = array('###VALUE###' => $itemValue);
			}
			$markersArray['###REPORTS_DBCHECK_OBJECT###'] = $template->renderAllTemplate(
				$markersArrayTemp, '###REPORTS_DBCHECK_OBJECT###'
			);
			$content .= $template->renderAllTemplate($markersArray, '###REPORTS_DBCHECK###');
		}

		if (!empty($sqlStatements['update']['change'])) {
			$markersArray                    = array();
			$markersArray['###LLL:TITLE###'] = 'Changing fields';
			$markersArrayTemp                = array();
			foreach ($sqlStatements['update']['change'] as $itemKey => $itemValue) {
				if (isset($sqlStatements['update']['change_currentValue'][$itemKey])) {
					$markersArrayTemp[] = array(
						'###VALUE###' => $itemValue . ' -- [current: ' . $sqlStatements['update']['change_currentValue'][$itemKey] . ']'
					);
				} else {
					$markersArrayTemp[] = array('###VALUE###' => $itemValue);
				}
			}
			$markersArray['###REPORTS_DBCHECK_OBJECT###'] = $template->renderAllTemplate(
				$markersArrayTemp, '###REPORTS_DBCHECK_OBJECT###'
			);
			$content .= $template->renderAllTemplate($markersArray, '###REPORTS_DBCHECK###');
		}

		if (!empty($sqlStatements['remove']['change'])) {
			$markersArray                    = array();
			$markersArray['###LLL:TITLE###'] = 'Remove unused fields (rename with prefix)';
			$markersArrayTemp                = array();
			foreach ($sqlStatements['remove']['change'] as $itemKey => $itemValue) {
				$markersArrayTemp[] = array('###VALUE###' => $itemValue);
			}
			$markersArray['###REPORTS_DBCHECK_OBJECT###'] = $template->renderAllTemplate(
				$markersArrayTemp, '###REPORTS_DBCHECK_OBJECT###'
			);
			$content .= $template->renderAllTemplate($markersArray, '###REPORTS_DBCHECK###');
		}

		if (!empty($sqlStatements['remove']['drop'])) {
			$markersArray                    = array();
			$markersArray['###LLL:TITLE###'] = 'Drop fields (really!)';
			$markersArrayTemp                = array();
			foreach ($sqlStatements['remove']['drop'] as $itemKey => $itemValue) {
				$markersArrayTemp[] = array('###VALUE###' => $itemValue);
			}
			$markersArray['###REPORTS_DBCHECK_OBJECT###'] = $template->renderAllTemplate(
				$markersArrayTemp, '###REPORTS_DBCHECK_OBJECT###'
			);
			$content .= $template->renderAllTemplate($markersArray, '###REPORTS_DBCHECK###');
		}

		if (!empty($sqlStatements['update']['create_table'])) {
			$markersArray                    = array();
			$markersArray['###LLL:TITLE###'] = 'Add tables';
			$markersArrayTemp                = array();
			foreach ($sqlStatements['update']['create_table'] as $itemKey => $itemValue) {
				$markersArrayTemp[] = array('###VALUE###' => $itemValue);
			}
			$markersArray['###REPORTS_DBCHECK_OBJECT###'] = $template->renderAllTemplate(
				$markersArrayTemp, '###REPORTS_DBCHECK_OBJECT###'
			);
			$content .= $template->renderAllTemplate($markersArray, '###REPORTS_DBCHECK###');
		}

		if (!empty($sqlStatements['remove']['change_table'])) {
			$markersArray                    = array();
			$markersArray['###LLL:TITLE###'] = 'Removing tables (rename with prefix)';
			$markersArrayTemp                = array();
			foreach ($sqlStatements['remove']['change_table'] as $itemKey => $itemValue) {
				if (!empty($sqlStatements['remove']['tables_count'][$itemKey])) {
					$markersArrayTemp[] = array(
						'###VALUE###' => $itemValue . ' -- [' . $sqlStatements['remove']['tables_count'][$itemKey] . ']'
					);
				} else {
					$markersArrayTemp[] = array('###VALUE###' => $itemValue . ' -- [empty]');
				}
			}
			$markersArray['###REPORTS_DBCHECK_OBJECT###'] = $template->renderAllTemplate(
				$markersArrayTemp, '###REPORTS_DBCHECK_OBJECT###'
			);
			$content .= $template->renderAllTemplate($markersArray, '###REPORTS_DBCHECK###');
		}

		if (!empty($sqlStatements['remove']['drop_table'])) {
			$markersArray                    = array();
			$markersArray['###LLL:TITLE###'] = 'Drop tables (really!)';
			$markersArrayTemp                = array();
			foreach ($sqlStatements['remove']['drop_table'] as $itemKey => $itemValue) {
				if (!empty($sqlStatements['remove']['tables_count'][$itemKey])) {
					$markersArrayTemp[] = array(
						'###VALUE###' => $itemValue . ' -- [' . $sqlStatements['remove']['tables_count'][$itemKey] . ']'
					);
				} else {
					$markersArrayTemp[] = array('###VALUE###' => $itemValue . ' -- [empty]');
				}
			}
			$markersArray['###REPORTS_DBCHECK_OBJECT###'] = $template->renderAllTemplate(
				$markersArrayTemp, '###REPORTS_DBCHECK_OBJECT###'
			);
			$content .= $template->renderAllTemplate($markersArray, '###REPORTS_DBCHECK###');
		}

		// dump sql structure
		$items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'table_name',
			'information_schema.tables',
			'table_schema = \'' . TYPO3_db . '\'', '', 'table_name'
		);

		$sqlStructure = '';

		foreach ($items as $table) {
			$resSqlDump = $GLOBALS['TYPO3_DB']->sql_query('SHOW CREATE TABLE ' . $table['table_name']);
			$sqlDump    = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resSqlDump);
			$sqlStructure .= $sqlDump['Create Table'] . "\r\n\r\n";
			$GLOBALS['TYPO3_DB']->sql_free_result($resSqlDump);
		}

		$content .= '<h3 class="uppercase">Dump SQL Structure (md5:' . md5($sqlStructure) . ')</h3>';
		$content .= '<textarea style="width:100%;height:200px;">' . $sqlStructure . '</textarea>';

		return $content;
	}

}

?>