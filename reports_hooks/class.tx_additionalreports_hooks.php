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

class tx_additionalreports_hooks implements tx_reports_Report {

    /**
     * Back-reference to the calling reports module
     *
     * @var    tx_reports_Module    $reportObject
     */

    protected $reportObject;

    /**
     * Constructor for class tx_additionalreports_hooks
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
        $content .= '<p class="help">' . $GLOBALS['LANG']->getLL('hooks_description') . '</p>';
        $content .= $this->displayHooks();
        return $content;
    }

    protected function displayHooks() {
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
        $tempCached = $this->getCacheFilePrefix() . '_ext_localconf.php';
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

    function getCacheFilePrefix() {
        $extensionCacheBehaviour = intval($GLOBALS['TYPO3_CONF_VARS']['EXT']['extCache']);

        // Caching of extensions is disabled when install tool is used:
        if (!$usePlainValue && defined('TYPO3_enterInstallScript') && TYPO3_enterInstallScript) {
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


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/additional_reports/reports_hooks/class.tx_additionalreports_hooks.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/additional_reports/reports_hooks/class.tx_additionalreports_hooks.php']);
}

?>