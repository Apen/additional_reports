<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Yohann CERDAN <cerdanyohann@yahoo.fr>
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
 * ViewHelper to check if a variable is in a list
 *
 * Example
 * <AdditionalReports:inList list="{AdditionalReports:session(index:'agenda', identifier:'dates')}" item="{eventDate.filtre}">...</AdditionalReports:inList>
 *
 * @package    TYPO3
 * @subpackage AdditionalReports
 */
class Tx_AdditionalReports_ViewHelpers_ContentInfosViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper {

    public function initializeArguments() {
        $this->registerArgument('item', 'array', 'Current item array', FALSE, NULL);
        $this->registerArgument('as', 'string', 'Name of the items array', FALSE, NULL);
        $this->registerArgument('plugin', 'boolean', 'Is it a plugin?', FALSE, NULL);
        $this->registerArgument('ctype', 'boolean', 'Is it a CType?', FALSE, NULL);
    }


    /**
     * Renders else-child or else-argument if variable $item is in $list
     *
     * @param string $list
     * @param string $item
     * @return string
     */
    public function render() {
        $item = $this->arguments['item'];
        $as = $this->arguments['as'];
        $plugin = $this->arguments['plugin'];
        $ctype = $this->arguments['ctype'];

        // plugin
        if ($plugin === TRUE) {
            foreach ($GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'] as $itemKey => $itemValue) {
                if (trim($itemValue[1]) == $item['list_type']) {
                    preg_match('/EXT:(.*?)\//', $itemValue[0], $ext);
                    preg_match('/^LLL:(EXT:.*?):(.*)/', $itemValue[0], $llfile);
                    $localLang = \TYPO3\CMS\Core\Utility\GeneralUtility::readLLfile($llfile[1], $GLOBALS['LANG']->lang);
                    $item['iconext'] = tx_additionalreports_util::getExtIcon($ext[1]);
                    $item['extension'] = $ext[1];
                    $item['plugin'] = $GLOBALS['LANG']->getLLL($llfile[2], $localLang) . ' (' . $item['list_type'] . ')';
                } else {
                    $item['plugin'] = $item['list_type'];
                }
            }
        }

        // CType
        if ($ctype === TRUE) {
            foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $itemValue) {
                if ($itemValue[1] != '--div--') {
                    if (trim($itemValue[1]) == $item['CType']) {
                        preg_match('/^LLL:(EXT:.*?):(.*)/', $itemValue[0], $llfile);
                        $localLang = \TYPO3\CMS\Core\Utility\GeneralUtility::readLLfile($llfile[1], $GLOBALS['LANG']->lang);
                        $item['iconext'] = tx_additionalreports_util::getContentTypeIcon($itemValue[2]);
                        $item['ctype'] = $GLOBALS['LANG']->getLLL($llfile[2], $localLang) . ' (' . $item['CType'] . ')';
                    } else {
                        $item['ctype'] = $item['CType'];
                    }
                }
            }
        }

        $item = array_merge($item, tx_additionalreports_main::getContentInfos($item));

        if ($this->templateVariableContainer->exists($as)) {
            $this->templateVariableContainer->remove($as);
        }
        $this->templateVariableContainer->add($as, $item);
    }

}

?>