<?php

namespace Sng\AdditionalReports\ViewHelpers;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ViewHelper to get all infos on a plugin or content
 *
 * Example
 * <ar:contentInfos item="{item}" as="item" ctype="TRUE"/>
 */
class ContentInfosViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('item', 'array', 'Current item array', false, null);
        $this->registerArgument('as', 'string', 'Name of the items array', false, null);
        $this->registerArgument('plugin', 'boolean', 'Is it a plugin?', false, null);
        $this->registerArgument('ctype', 'boolean', 'Is it a CType?', false, null);
    }

    /**
     * Renders else-child or else-argument if variable $item is in $list
     *
     * @param string $list
     * @param string $item
     * @return string
     */
    public function render()
    {
        $item = $this->arguments['item'];
        $as = $this->arguments['as'];
        $plugin = $this->arguments['plugin'];
        $ctype = $this->arguments['ctype'];

        $languageFactory = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Localization\LocalizationFactory::class);

        // plugin
        if ($plugin === true) {
            foreach ($GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'] as $itemKey => $itemValue) {
                if (trim($itemValue[1]) == $item['list_type']) {
                    preg_match('/EXT:(.*?)\//', $itemValue[0], $ext);
                    preg_match('/^LLL:(EXT:.*?):(.*)/', $itemValue[0], $llfile);
                    $localLang = $languageFactory->getParsedData($llfile[1], $GLOBALS['LANG']->lang);
                    $item['iconext'] = \Sng\AdditionalReports\Utility::getExtIcon($ext[1]);
                    $item['extension'] = $ext[1];
                    $item['plugin'] = $GLOBALS['LANG']->getLLL($llfile[2], $localLang) . ' (' . $item['list_type'] . ')';
                } else {
                    $item['plugin'] = $item['list_type'];
                }
            }
        }

        // CType
        if ($ctype === true) {
            foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $itemValue) {
                if ($itemValue[1] != '--div--') {
                    if (trim($itemValue[1]) == $item['CType']) {
                        preg_match('/^LLL:(EXT:.*?):(.*)/', $itemValue[0], $llfile);
                        $localLang = $languageFactory->getParsedData($llfile[1], $GLOBALS['LANG']->lang);
                        $item['iconext'] = \Sng\AdditionalReports\Utility::getContentTypeIcon($itemValue[2]);
                        $item['ctype'] = $GLOBALS['LANG']->getLLL($llfile[2], $localLang) . ' (' . $item['CType'] . ')';
                    } else {
                        $item['ctype'] = $item['CType'];
                    }
                }
            }
        }

        $item = array_merge($item, \Sng\AdditionalReports\Utility::getContentInfos($item));

        if ($this->templateVariableContainer->exists($as)) {
            $this->templateVariableContainer->remove($as);
        }
        $this->templateVariableContainer->add($as, $item);
    }
}
