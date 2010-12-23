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

class tx_additionalreports_status implements tx_reports_Report {

    /**
     * Back-reference to the calling reports module
     *
     * @var    tx_reports_Module    $reportObject
     */

    protected $reportObject;

    /**
     * Constructor for class tx_additionalreports_status
     *
     * @param    tx_reports_Module    Back-reference to the calling reports module
     */

    public function __construct(tx_reports_Module $reportObject) {
        $this->reportObject = $reportObject;
        $GLOBALS['LANG']->includeLLFile('EXT:additional_reports/locallang.xml');
    }

    /**
     * This method renders the report
     *
     * @return    string    The status report as HTML
     */

    public function getReport() {
        $content = '';
        $this->reportObject->doc->getPageRenderer()->addCssFile(t3lib_extMgm::extRelPath('additional_reports') . 'tx_additionalreports.css');
        $content .= '<p class="help">' . $GLOBALS['LANG']->getLL('status_description') . '</p>';
        $content .= $this->displayStatus();
        return $content;
    }

    protected function displayStatus() {
        $content = '';
        // Typo3
        $content .= '<h2 id="reportsTypo3" class="section-header expanded">TYPO3 :</h2>';
        $content .= '<div>';
        $content .= $this->writeInformation($GLOBALS['LANG']->getLL('status_sitename'), $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']);
        $content .= $this->writeInformation($GLOBALS['LANG']->getLL('status_version'), TYPO3_version);
        $content .= $this->writeInformation($GLOBALS['LANG']->getLL('status_path'), PATH_site);
        $content .= $this->writeInformation('TYPO3_db', TYPO3_db);
        $content .= $this->writeInformation('TYPO3_db_username', TYPO3_db_username);
        $content .= $this->writeInformation('TYPO3_db_host', TYPO3_db_host);
        if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path'] != '') {
            $cmd = t3lib_div::imageMagickCommand('convert', '-version');
            exec($cmd, $ret);
            $content .= $this->writeInformation($GLOBALS['LANG']->getLL('status_im'), $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_path'] . ' (' . $ret[0] . ')');
        }
        $content .= '<div class="typo3-message message-information">';
        $content .= '<div class="header-container">';
        $content .= '<div class="message-header message-left">' . $GLOBALS['LANG']->getLL('status_loadedextensions') . '</div>';
        $content .= '<div class="message-header message-right">';
        $content .= '<ul>';
        foreach (explode(',', $GLOBALS['TYPO3_CONF_VARS']['EXT']['extList']) as $extension) {
            $content .= '<li>' . $extension . ' (' . $this->getExtensionVersion($extension) . ')</li>';
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
        $content .= $this->writeInformation($GLOBALS['LANG']->getLL('status_version'), phpversion());
        $content .= $this->writeInformation('memory_limit', ini_get('memory_limit'));
        $content .= $this->writeInformation('max_execution_time', ini_get('max_execution_time'));
        $content .= $this->writeInformation('post_max_size', ini_get('post_max_size'));
        $content .= $this->writeInformation('upload_max_filesize', ini_get('upload_max_filesize'));
        $content .= '<div class="typo3-message message-information">';
        $content .= '<div class="header-container">';
        $content .= '<div class="message-header message-left">' . $GLOBALS['LANG']->getLL('status_loadedextensions') . '</div>';
        $content .= '<div class="message-header message-right">';
        $content .= '<ul>';
        foreach (get_loaded_extensions() as $extension) {
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
		if (function_exists('apache_get_version')) {
			$content .= '<h2 id="reportsApache" class="section-header expanded">Apache :</h2>';
			$content .= '<div>';
			$content .= $this->writeInformation($GLOBALS['LANG']->getLL('status_version'), apache_get_version());
			$content .= $this->writeInformationList($GLOBALS['LANG']->getLL('status_loadedextensions'), apache_get_modules());
			$content .= '</div>';
		}
        // MySQL
        $content .= '<h2 id="reportsMySQL" class="section-header expanded">MySQL :</h2>';
        $content .= '<div>';
        $content .= $this->writeInformation('Version', mysql_get_server_info());
		$items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('default_character_set_name, default_collation_name', 'information_schema.schemata', 'schema_name = \'' . TYPO3_db . '\'');
		$content .= $this->writeInformation('default_character_set_name', $items[0]['default_character_set_name']);
		$content .= $this->writeInformation('default_collation_name', $items[0]['default_collation_name']);
        $items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('table_name, engine,table_collation,table_rows', 'information_schema.tables ', 'table_schema = \'' . TYPO3_db . '\'', '', 'table_name');
        $content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist" width="100%">';
        $content .= '<tr class="t3-row-header"><td colspan="4">TYPO3_db</td></tr>';
        $content .= '<tr class="c-headLine">';
        $content .= '<td class="cell">table_name</td>';
        $content .= '<td class="cell">engine</td>';
        $content .= '<td class="cell">table_collation</td>';
        $content .= '<td class="cell">table_rows</td>';
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
		if (count($crontab)>0) {
			$crontabString = '';
			foreach ($crontab as $cron) {
				$crontabString .= $cron.'<br />';
			}
		}
        $content .= $this->writeInformation('Crontab', $crontabString);
        $content .= '</div>';
        return $content;
    }

    protected function writeInformation($label, $value) {
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

    protected function writeInformationList($label, $array) {
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

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/additional_reports/reports_status/class.tx_additionalreports_status.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/additional_reports/reports_status/class.tx_additionalreports_status.php']);
}

?>